<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Exchange;
use App\Models\ExchangeItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExchangeController extends Controller
{
    /**
     * Show the form for creating an exchange
     */
    public function create(Invoice $invoice)
    {
        $invoice->load(['customer', 'items' => function($query) {
            $query->where('is_return', false);
        }]);
        
        return view('sales.exchanges.create', compact('invoice'));
    }

    /**
     * Store a newly created exchange
     */
    public function store(Request $request, Invoice $invoice)
    {
        Log::info('=== EXCHANGE STORE METHOD CALLED ===', [
            'step' => 'method_start',
            'invoice_param_type' => gettype($invoice),
            'invoice_param_class' => get_class($invoice),
            'invoice_param_id' => $invoice->id ?? 'no_id',
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'request_all_keys' => array_keys($request->all()),
            'memory_usage' => memory_get_usage(true),
        ]);
        
        $originalInvoice = null;
        
        try {
            Log::info('Step 1: Assigning invoice to originalInvoice', [
                'step' => 'assign_invoice',
                'invoice_exists' => isset($invoice),
                'invoice_class' => get_class($invoice),
                'invoice_id' => $invoice->id ?? 'no_id',
            ]);
            
                $originalInvoice = $invoice;
            
            Log::info('Step 2: originalInvoice assigned', [
                'step' => 'invoice_assigned',
                'originalInvoice_exists' => isset($originalInvoice),
                'originalInvoice_type' => gettype($originalInvoice),
                'originalInvoice_id' => $originalInvoice->id ?? 'no_id',
                'originalInvoice_number' => $originalInvoice->invoice_number ?? 'no_number',
            ]);

            // Validate original invoice exists and has ID
            if (!$originalInvoice || !$originalInvoice->id) {
                Log::error('Step 3: Validation failed - Original invoice is null or has no ID', [
                    'step' => 'invoice_validation_failed',
                    'originalInvoice_exists' => isset($originalInvoice),
                    'originalInvoice_is_null' => is_null($originalInvoice),
                    'originalInvoice_id' => $originalInvoice->id ?? 'no_id'
                ]);
                return back()->withInput()
                    ->with('error', 'Orijinal fatura bulunamadı!');
            }

            Log::info('Step 4: Invoice validated successfully', [
                'step' => 'invoice_validated',
                'original_invoice_id' => $originalInvoice->id,
                'original_invoice_customer_id' => $originalInvoice->customer_id,
                'original_invoice_number' => $originalInvoice->invoice_number,
            ]);
            
            Log::info('Step 5: Starting request validation', [
                'step' => 'request_validation_start',
                'request_data_keys' => array_keys($request->all()),
                'exchange_items_count' => count($request->input('exchange_items', [])),
                'new_items_count' => count($request->input('new_items', [])),
            ]);
            
            $validated = $request->validate([
                'exchange_items' => 'required|array|min:1',
                'exchange_items.*.original_item_id' => 'required|integer|exists:invoice_items,id',
                'exchange_items.*.exchange_quantity' => 'nullable|numeric|min:0.01',
                'exchange_items.*.new_item' => 'nullable|array',
                'new_items' => 'nullable|array',
                'customer_id' => 'required|integer|exists:customers,id',
                'currency' => 'required|in:TRY,USD,EUR',
                'vat_status' => 'required|in:included,excluded',
                'invoice_date' => 'required|date',
                'invoice_time' => 'required|string',
                'due_date' => 'required|date',
                'description' => 'nullable|string|max:1000',
                'payment_completed' => 'nullable|boolean',
            ]);

            Log::info('Step 6: Request validation passed', [
                'step' => 'request_validation_passed',
                'validated_keys' => array_keys($validated),
                'exchange_items_count' => count($validated['exchange_items'] ?? []),
                'new_items_count' => count($validated['new_items'] ?? []),
                'originalInvoice_id' => $originalInvoice->id ?? 'not_set',
            ]);

            Log::info('Step 7: Starting database transaction', [
                'step' => 'db_transaction_start',
                'originalInvoice_id' => $originalInvoice->id,
            ]);

            DB::beginTransaction();

            $accountId = session('current_account_id') ?? $originalInvoice->account_id;
            $userId = auth()->id() ?? $originalInvoice->user_id;

            // Calculate totals for exchange items (will be added to same invoice)
            $subtotal = 0;
            $vatAmount = 0;
            $exchangeAmount = 0; // Total price difference

            // Process exchange items and new items
            $allItems = [];
            
            // Add new items from exchange_items (replacement items)
            foreach ($validated['exchange_items'] as $exchangeItemData) {
                if (isset($exchangeItemData['new_item']) && !empty($exchangeItemData['new_item'])) {
                    $allItems[] = $exchangeItemData['new_item'];
                }
            }
            
            // Add standalone new items
            if (isset($validated['new_items'])) {
                foreach ($validated['new_items'] as $newItem) {
                    $allItems[] = $newItem;
                }
            }

            // Calculate totals from all items
            foreach ($allItems as $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $discountRate = (float) ($item['discount_rate'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? 0);

                $lineTotal = $quantity * $unitPrice;
                $discountAmount = (float) ($item['discount_rate'] ?? 0); // Fixed amount discount
                $lineTotalAfterDiscount = max(0, $lineTotal - $discountAmount);
                
                if ($validated['vat_status'] === 'included') {
                    $vatAmount += $lineTotalAfterDiscount * ($taxRate / 100);
                }
                
                $subtotal += $lineTotalAfterDiscount;
            }

            // Calculate exchange amount (difference between old and new)
            // Original items are being returned (refund to customer)
            // New items are being added (charge to customer)
            // Net balance adjustment = originalTotal - newTotal
            // (positive = customer gets refund, negative = customer owes more)
            $originalSubtotal = 0;
            $originalVatAmount = 0;
            $newTotal = $subtotal + $vatAmount;

            // Store original item data BEFORE any modifications
            $originalItemsData = [];
            foreach ($validated['exchange_items'] as $exchangeItemData) {
                $originalItem = InvoiceItem::findOrFail($exchangeItemData['original_item_id']);
                
                // Store the ORIGINAL values BEFORE any quantity reduction
                // This ensures we calculate based on what was actually charged to the customer
                $originalItemsData[] = [
                    'item' => $originalItem,
                    'original_line_total' => (float) ($originalItem->line_total ?? 0),
                    'original_quantity' => (float) ($originalItem->quantity ?? 0),
                    'exchange_quantity' => (float) ($exchangeItemData['exchange_quantity'] ?? $originalItem->quantity ?? 0),
                    'unit_price' => (float) ($originalItem->unit_price ?? 0),
                    'discount_rate' => (float) ($originalItem->discount_rate ?? 0),
                    'tax_rate' => (float) ($originalItem->tax_rate ?? 0),
                ];
            }
            
            // Calculate exchange amount using original item data
            foreach ($originalItemsData as $itemData) {
                $exchangeQuantity = $itemData['exchange_quantity'];
                $originalQuantity = $itemData['original_quantity'];
                $originalLineTotal = $itemData['original_line_total'];
                
                // If quantity is 0, we need to reconstruct from exchange_quantity
                if ($originalQuantity <= 0 && $exchangeQuantity > 0) {
                    // Item was already fully exchanged, use exchange_quantity to calculate proportionally
                    // We'll estimate based on unit_price and exchange_quantity
                    $estimatedLineTotal = ($exchangeQuantity * $itemData['unit_price']) - $itemData['discount_rate'];
                    $proportionalLineTotal = max(0, $estimatedLineTotal);
                } elseif ($originalQuantity > 0) {
                    // Calculate proportional line_total based on exchange quantity
                    // Use the original line_total proportionally
                    if ($exchangeQuantity >= $originalQuantity) {
                        // Full exchange - use full line_total
                        $proportionalLineTotal = $originalLineTotal;
                    } else {
                        // Partial exchange - calculate proportionally
                        $proportionalLineTotal = ($exchangeQuantity / $originalQuantity) * $originalLineTotal;
                    }
                } else {
                    // Fallback: use current line_total if available
                    $proportionalLineTotal = $originalLineTotal > 0 ? $originalLineTotal : 0;
                }
                
                $originalSubtotal += abs($proportionalLineTotal);
                
                // Calculate VAT on original items if VAT is included
                if ($validated['vat_status'] === 'included' && $itemData['tax_rate'] > 0) {
                    // Calculate VAT proportionally based on the proportional line_total
                    $originalVatAmount += abs($proportionalLineTotal) * ($itemData['tax_rate'] / 100);
                }
                
                Log::info('Exchange item calculation', [
                    'original_item_id' => $itemData['item']->id,
                    'original_line_total' => $originalLineTotal,
                    'original_quantity' => $originalQuantity,
                    'exchange_quantity' => $exchangeQuantity,
                    'proportional_line_total' => $proportionalLineTotal,
                    'vat_rate' => $itemData['tax_rate'],
                    'unit_price' => $itemData['unit_price'],
                ]);
            }

            // Calculate original total including VAT
            $originalTotal = $originalSubtotal + $originalVatAmount;

            // Exchange amount for customer balance adjustment
            // Positive = refund (decrease debt), Negative = additional charge (increase debt)
            $exchangeAmount = $originalTotal - $newTotal;
            
            Log::info('Exchange amount calculation', [
                'step' => 'calculate_exchange_amount',
                'originalSubtotal' => $originalSubtotal,
                'originalVatAmount' => $originalVatAmount,
                'originalTotal' => $originalTotal,
                'newSubtotal' => $subtotal,
                'newVatAmount' => $vatAmount,
                'newTotal' => $newTotal,
                'exchangeAmount' => $exchangeAmount,
                'vat_status' => $validated['vat_status'],
                'explanation' => $exchangeAmount >= 0 
                    ? 'Customer gets refund of ' . abs($exchangeAmount)
                    : 'Customer owes additional ' . abs($exchangeAmount)
            ]);

            // Get customer_id from validated request or original invoice
            $customerId = $validated['customer_id'] ?? $originalInvoice->customer_id;
            
            // Validate customer_id is not null
            if (!$customerId) {
                throw new \Exception('Müşteri ID bulunamadı! Lütfen müşteri bilgisini kontrol edin.');
            }
            
            // Add exchange items to the same invoice (original invoice)
            // Get current max sort_order to append new items
            Log::info('Step 7.1: Getting max sort_order for invoice items', [
                'step' => 'get_max_sort_order',
                'originalInvoice_exists' => isset($originalInvoice),
                'originalInvoice_id' => $originalInvoice->id ?? 'not_set',
            ]);
            
            $maxSortOrder = $originalInvoice->items()->max('sort_order') ?? -1;
            
            Log::info('Step 7.2: Starting to create invoice items', [
                'step' => 'create_invoice_items',
                'maxSortOrder' => $maxSortOrder,
                'allItems_count' => count($allItems),
                'originalInvoice_id' => $originalInvoice->id,
            ]);
            
            foreach ($allItems as $index => $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? 0);
                $discountRate = (float) ($item['discount_rate'] ?? 0);
                
                $lineTotal = $quantity * $unitPrice;
                $discountAmount = (float) ($item['discount_rate'] ?? 0);
                $lineTotalAfterDiscount = max(0, $lineTotal - $discountAmount);
                
                $cleanProductId = $item['product_id'] ?? null;
                if ($cleanProductId) {
                    $cleanProductId = str_replace(['product_', 'series_', 'service_'], '', $cleanProductId);
                }
                
                // Create exchange item description like return items: "Değişim - [ürün adı]"
                $exchangeDescription = 'Değişim - ' . $item['product_service_name'];
                if (!empty($item['description'])) {
                    $exchangeDescription .= ' - ' . $item['description'];
                }
                
                InvoiceItem::create([
                    'invoice_id' => $originalInvoice->id,
                    'product_service_name' => $item['product_service_name'],
                    'description' => $exchangeDescription,
                    'selected_color' => $item['selected_color'] ?? null,
                    'color_variant_id' => $item['color_variant_id'] ?? null,
                    'product_id' => $cleanProductId,
                    'product_type' => $item['type'] ?? 'product',
                    'quantity' => $quantity,
                    'unit' => 'Ad',
                    'unit_price' => $unitPrice,
                    'unit_currency' => $validated['currency'],
                    'tax_rate' => $taxRate,
                    'discount_rate' => $discountRate,
                    'line_total' => $lineTotalAfterDiscount,
                    'sort_order' => $maxSortOrder + $index + 1,
                    'is_return' => false
                ]);
            }
            
            // Update invoice description (totals will be recalculated after item quantity reductions)
            Log::info('Step 7.3: Updating invoice description', [
                'step' => 'update_invoice_description',
                'originalInvoice_exists' => isset($originalInvoice),
                'originalInvoice_id' => $originalInvoice->id ?? 'not_set',
            ]);
            
            $originalInvoice->refresh();
            
            Log::info('Step 7.4: Invoice refreshed, updating description', [
                'step' => 'invoice_refreshed',
                'originalInvoice_id' => $originalInvoice->id,
            ]);
            
            $originalInvoice->update([
                'description' => ($originalInvoice->description ?? '') . ' | Değişim: ' . ($validated['description'] ?? 'Değişim yapıldı')
            ]);

            // Validate required IDs before creating exchange
            Log::info('Step 7.5: Validating invoice ID before creating exchange', [
                'step' => 'validate_invoice_id',
                'originalInvoice_exists' => isset($originalInvoice),
                'originalInvoice_id' => $originalInvoice->id ?? 'not_set',
            ]);
            
            if (!$originalInvoice->id) {
                throw new \Exception('Fatura ID bulunamadı! Original: ' . ($originalInvoice->id ?? 'null'));
            }

            // Create exchange record (new_invoice_id = original_invoice_id, same invoice)
            Log::info('Step 7.6: Preparing exchange data', [
                'step' => 'prepare_exchange_data',
                'originalInvoice_id' => $originalInvoice->id,
                'accountId' => $accountId,
                'userId' => $userId,
                'exchangeAmount' => $exchangeAmount,
            ]);
            
            $exchangeData = [
                'account_id' => $accountId,
                'original_invoice_id' => (int) $originalInvoice->id,
                'new_invoice_id' => (int) $originalInvoice->id, // Same invoice
                'user_id' => $userId,
                'exchange_amount' => $exchangeAmount,
                'notes' => $validated['description'] ?? null
            ];
            
            Log::info('Creating exchange record', $exchangeData);
            
            $exchange = Exchange::create($exchangeData);
            
            if (!$exchange || !$exchange->id) {
                throw new \Exception('Exchange kaydı oluşturulamadı!');
            }
            
            Log::info('Exchange record created', ['exchange_id' => $exchange->id]);

            // Create exchange items and reduce original invoice item quantities
            foreach ($validated['exchange_items'] as $exchangeItemData) {
                $originalItem = InvoiceItem::findOrFail($exchangeItemData['original_item_id']);
                // Get original quantity BEFORE any modifications (refresh from DB)
                $originalItem->refresh();
                $originalItemQuantity = $originalItem->quantity;
                
                // Use exchange_quantity if provided, otherwise use original quantity
                $exchangeQuantity = $exchangeItemData['exchange_quantity'] ?? $originalItemQuantity;
                // Ensure exchange quantity doesn't exceed original
                $exchangeQuantity = min($exchangeQuantity, $originalItemQuantity);
                
                // Validate exchange quantity
                if ($exchangeQuantity <= 0) {
                    throw new \Exception('Değişim miktarı 0\'dan büyük olmalıdır!');
                }
                
                // Calculate original amount with exchange quantity
                $originalAmount = $exchangeQuantity * $originalItem->unit_price;
                
                // Reduce original item quantity (subtract exchanged quantity from original item)
                $remainingQuantity = $originalItem->quantity - $exchangeQuantity;
                if ($remainingQuantity <= 0) {
                    // If all quantity is exchanged, mark original item as fully exchanged
                    $originalItem->update(['quantity' => 0]);
                    Log::info('Original item quantity set to 0 (fully exchanged)', [
                        'original_item_id' => $originalItem->id,
                        'exchanged_quantity' => $exchangeQuantity
                    ]);
                } else {
                    // Update original item quantity
                    $originalItem->update(['quantity' => $remainingQuantity]);
                    // Recalculate original item line total (without VAT, VAT is calculated at invoice level)
                    $originalLineTotal = $remainingQuantity * $originalItem->unit_price;
                    $originalDiscountAmount = $originalItem->discount_rate ?? 0;
                    $originalLineTotalAfterDiscount = max(0, $originalLineTotal - $originalDiscountAmount);
                    $originalItem->update(['line_total' => $originalLineTotalAfterDiscount]);
                    
                    Log::info('Original item quantity reduced for exchange', [
                        'original_item_id' => $originalItem->id,
                        'old_quantity' => $originalItem->quantity + $exchangeQuantity,
                        'new_quantity' => $remainingQuantity,
                        'exchanged_quantity' => $exchangeQuantity
                    ]);
                }
                
                $newItemId = null;
                $newAmount = 0;

                if (isset($exchangeItemData['new_item']) && !empty($exchangeItemData['new_item'])) {
                    // Find the corresponding new item (by product name - exchange items have "Değişim -" prefix)
                    $newItem = InvoiceItem::where('invoice_id', $originalInvoice->id)
                        ->where('product_service_name', $exchangeItemData['new_item']['product_service_name'])
                        ->where('description', 'like', 'Değişim -%')
                        ->orderByDesc('id')
                        ->first();
                    if ($newItem) {
                        $newItemId = $newItem->id;
                        $newAmount = $newItem->line_total;
                    }
                }

                $difference = $newAmount - abs($originalAmount);

                ExchangeItem::create([
                    'exchange_id' => $exchange->id,
                    'original_item_id' => $originalItem->id,
                    'new_item_id' => $newItemId,
                    'original_amount' => abs($originalAmount),
                    'new_amount' => $newAmount,
                    'difference' => $difference,
                    'notes' => null
                ]);
            }

            // Recalculate original invoice totals after all exchanges (once, outside the loop)
            // This ensures totals are accurate after quantity reductions and new item additions
            Log::info('Step 8: Recalculating invoice totals', [
                'step' => 'recalculate_totals',
                'originalInvoice_exists' => isset($originalInvoice),
                'originalInvoice_id' => $originalInvoice->id ?? 'not_set',
            ]);
            
            // Get old invoice total BEFORE recalculation (for customer balance update)
            $oldInvoiceTotal = $originalInvoice->total_amount ?? 0;
            
            $originalInvoice->refresh();
            
            Log::info('Step 8.1: Invoice refreshed, calculating subtotal', [
                'step' => 'calculate_subtotal',
                'originalInvoice_id' => $originalInvoice->id,
                'old_invoice_total' => $oldInvoiceTotal,
            ]);
            
            $originalInvoiceSubtotal = $originalInvoice->items()->where('is_return', false)->sum('line_total');
            
            Log::info('Step 8.2: Subtotal calculated, calculating VAT', [
                'step' => 'calculate_vat',
                'originalInvoice_id' => $originalInvoice->id,
                'originalInvoice_vat_status' => $originalInvoice->vat_status ?? 'not_set',
                'subtotal' => $originalInvoiceSubtotal,
            ]);
            
            // Fix: Use closure with 'use' to properly capture $originalInvoice
            $vatStatus = $originalInvoice->vat_status;
            $originalInvoiceVat = $originalInvoice->items()->where('is_return', false)->get()->sum(function($item) use ($vatStatus) {
                if ($vatStatus === 'included') {
                    return $item->line_total * (($item->tax_rate ?? 0) / 100);
                }
                return 0;
            });
            
            Log::info('Step 8.3: VAT calculated, calculating total', [
                'step' => 'calculate_total',
                'subtotal' => $originalInvoiceSubtotal,
                'vat' => $originalInvoiceVat,
            ]);
            
            $originalInvoiceTotal = $originalInvoiceSubtotal + $originalInvoiceVat;
            $originalInvoice->update([
                'subtotal' => max(0, $originalInvoiceSubtotal),
                'vat_amount' => max(0, $originalInvoiceVat),
                'total_amount' => max(0, $originalInvoiceTotal)
            ]);
            
            Log::info('Original invoice totals recalculated after exchange', [
                'original_invoice_id' => $originalInvoice->id,
                'old_invoice_total' => $oldInvoiceTotal,
                'new_subtotal' => $originalInvoiceSubtotal,
                'new_vat' => $originalInvoiceVat,
                'new_total' => $originalInvoiceTotal,
                'invoice_total_change' => $originalInvoiceTotal - $oldInvoiceTotal,
            ]);

            // Stock management - add back old items, deduct new items
            foreach ($validated['exchange_items'] as $exchangeItemData) {
                // Get exchange quantity from the validated data
                $originalItemId = $exchangeItemData['original_item_id'];
                $originalItem = InvoiceItem::findOrFail($originalItemId);
                
                // Get the exchange quantity that was used earlier (before quantity reduction)
                // We need to use the same quantity that was used for reducing the item quantity
                $exchangeQuantity = $exchangeItemData['exchange_quantity'] ?? 0;
                
                // If exchange_quantity is not provided, skip this item
                if (empty($exchangeQuantity) || $exchangeQuantity <= 0) {
                    continue;
                }
                
                // Add back original item stock (only the exchanged quantity)
                if ($originalItem->product_type === 'product') {
                    // Product stock restore (same as return) - single products are not supported anymore
                } elseif ($originalItem->product_type === 'series') {
                    $series = \App\Models\ProductSeries::with('colorVariants')->find($originalItem->product_id);
                    if ($series) {
                        // Seri ürünlerde değişim miktarı birebir alınır (çarpan uygulanmaz)
                        $unitsToChange = (int) $exchangeQuantity;
                        if ($originalItem->color_variant_id) {
                            $colorVariant = $series->colorVariants()->where('id', $originalItem->color_variant_id)->first();
                            if ($colorVariant) {
                                $colorVariant->increment('stock_quantity', $unitsToChange);
                                $series->refresh();
                                $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                $series->save();
                            }
                        } else {
                            $series->increment('stock_quantity', $unitsToChange);
                            // Recompute parent stock from variants if any exist
                            $series->refresh();
                            if ($series->colorVariants && $series->colorVariants->count() > 0) {
                                $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                $series->save();
                            }
                        }
                    }
                }
            }

            // Deduct stock for new items
            foreach ($allItems as $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $productId = $item['product_id'] ?? null;
                $type = $item['type'] ?? 'series';
                $colorVariantId = $item['color_variant_id'] ?? null;
                
                if ($productId) {
                    $cleanProductId = str_replace(['product_', 'series_', 'service_'], '', $productId);
                    
                    if ($type === 'series') {
                        $series = \App\Models\ProductSeries::with('colorVariants')->find($cleanProductId);
                        if ($series) {
                            // Seri ürünlerde yeni ürün düşümü birebir miktar üzerinden yapılır
                            $unitsToChange = (int) $quantity;
                            if ($colorVariantId) {
                                $colorVariant = $series->colorVariants()->where('id', $colorVariantId)->first();
                                if ($colorVariant) {
                                    if ($colorVariant->stock_quantity < $unitsToChange) {
                                        throw new \Exception("Yetersiz stok! {$series->name} ({$colorVariant->color}) için stok: {$colorVariant->stock_quantity}, istenen: {$unitsToChange}");
                                    }
                                    $colorVariant->decrement('stock_quantity', $unitsToChange);
                                    $series->refresh();
                                    $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                    $series->save();
                                }
                            } else {
                                if ($series->stock_quantity < $unitsToChange) {
                                    throw new \Exception("Yetersiz stok! {$series->name} için stok: {$series->stock_quantity}, istenen: {$unitsToChange}");
                                }
                                $series->decrement('stock_quantity', $unitsToChange);
                                // Recompute parent stock from variants if any exist
                                $series->refresh();
                                if ($series->colorVariants && $series->colorVariants->count() > 0) {
                                    $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                    $series->save();
                                }
                            }
                        }
                    }
                }
            }

            // Update customer balance based on invoice total change
            // The correct approach: Update customer balance to match the new invoice total
            // Since invoice total changed from old_total to new_total, customer balance should change by the difference
            if (!$validated['payment_completed']) {
                Log::info('Step 9: Updating customer balance based on invoice total change', [
                    'step' => 'update_customer_balance',
                    'customer_id' => $customerId,
                    'old_invoice_total' => $oldInvoiceTotal,
                    'new_invoice_total' => $originalInvoiceTotal,
                    'invoice_total_change' => $originalInvoiceTotal - $oldInvoiceTotal,
                    'exchange_amount_calculated' => $exchangeAmount,
                    'original_subtotal' => $originalSubtotal,
                    'original_vat' => $originalVatAmount,
                    'original_total' => $originalTotal,
                    'new_subtotal' => $subtotal,
                    'new_vat' => $vatAmount,
                    'new_total' => $newTotal,
                ]);
                
                $customer = Customer::find($customerId);
                if (!$customer) {
                    throw new \Exception('Müşteri bulunamadı!');
                }
                $currency = $validated['currency'];
                
                // Get current balances before update
                $currentBalance = $customer->balance ?? 0;
                $currentCurrencyBalance = match($currency) {
                    'TRY' => $customer->balance_try ?? 0,
                    'USD' => $customer->balance_usd ?? 0,
                    'EUR' => $customer->balance_eur ?? 0,
                    default => 0
                };
                
                Log::info('Customer balance before exchange update', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'currency' => $currency,
                    'current_balance' => $currentBalance,
                    'current_currency_balance' => $currentCurrencyBalance,
                    'old_invoice_total' => $oldInvoiceTotal,
                    'new_invoice_total' => $originalInvoiceTotal,
                ]);
                
                // Calculate the change in invoice total
                // This is the amount we need to adjust customer balance
                $invoiceTotalChange = $originalInvoiceTotal - $oldInvoiceTotal;
                
                // Update balance based on invoice total change
                // If invoice total increased, customer owes more (balance increases)
                // If invoice total decreased, customer owes less (balance decreases)
                switch ($currency) {
                    case 'TRY':
                        $customer->increment('balance_try', $invoiceTotalChange);
                        break;
                    case 'USD':
                        $customer->increment('balance_usd', $invoiceTotalChange);
                        break;
                    case 'EUR':
                        $customer->increment('balance_eur', $invoiceTotalChange);
                        break;
                }
                // Also update legacy balance field
                $customer->increment('balance', $invoiceTotalChange);
                
                // Refresh to get updated values
                $customer->refresh();
                
                $newBalance = $customer->balance ?? 0;
                $newCurrencyBalance = match($currency) {
                    'TRY' => $customer->balance_try ?? 0,
                    'USD' => $customer->balance_usd ?? 0,
                    'EUR' => $customer->balance_eur ?? 0,
                    default => 0
                };
                
                Log::info('Customer balance after exchange update', [
                    'customer_id' => $customer->id,
                    'currency' => $currency,
                    'old_balance' => $currentBalance,
                    'new_balance' => $newBalance,
                    'old_currency_balance' => $currentCurrencyBalance,
                    'new_currency_balance' => $newCurrencyBalance,
                    'invoice_total_change' => $invoiceTotalChange,
                    'new_invoice_total' => $originalInvoiceTotal,
                    'explanation' => $invoiceTotalChange >= 0 
                        ? 'Invoice total increased by ' . abs($invoiceTotalChange) . ', customer debt increased'
                        : 'Invoice total decreased by ' . abs($invoiceTotalChange) . ', customer debt decreased'
                ]);
            } else {
                Log::info('Skipping customer balance update - payment completed', [
                    'step' => 'skip_balance_update',
                    'payment_completed' => true
                ]);
            }

            DB::commit();

            Log::info('Exchange created successfully', [
                'exchange_id' => $exchange->id,
                'invoice_id' => $originalInvoice->id,
                'exchange_amount' => $exchangeAmount
            ]);

            return redirect()->route('sales.invoices.show', $originalInvoice)
                ->with('success', 'Değişim başarıyla eklendi. Fatura #' . $originalInvoice->invoice_number);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('=== VALIDATION EXCEPTION CAUGHT ===', [
                'step' => 'validation_exception',
                'exception_type' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'errors' => $e->errors(),
                'originalInvoice_exists' => isset($originalInvoice),
                'originalInvoice_is_null' => !isset($originalInvoice) || is_null($originalInvoice),
                'originalInvoice_type' => isset($originalInvoice) ? gettype($originalInvoice) : 'not_set',
                'originalInvoice_id' => (isset($originalInvoice) && $originalInvoice) ? ($originalInvoice->id ?? 'no_id') : 'not_loaded',
                'invoice_param_exists' => isset($invoice),
                'invoice_param_type' => isset($invoice) ? gettype($invoice) : 'not_set',
                'invoice_param_id' => isset($invoice) ? ($invoice->id ?? 'no_id') : 'not_set',
                'request_data_keys' => array_keys($request->all()),
                'memory_usage' => memory_get_usage(true),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if (DB::transactionLevel() > 0) {
                Log::info('Rolling back transaction due to validation error');
                DB::rollBack();
            }
            
            \Log::channel('single')->error('Exchange validation error (detailed):', [
                'errors' => $e->errors(),
                'original_invoice_exists' => isset($originalInvoice),
                'original_invoice' => (isset($originalInvoice) && $originalInvoice) ? 'loaded' : 'not_loaded',
                'exception_message' => $e->getMessage(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
            ]);
            
            return back()->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Form doğrulama hatası! Lütfen tüm alanları kontrol edin.');
                
        } catch (\Exception $e) {
            Log::error('=== GENERAL EXCEPTION CAUGHT ===', [
                'step' => 'general_exception',
                'exception_type' => get_class($e),
                'exception_message' => $e->getMessage(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
                'originalInvoice_exists' => isset($originalInvoice),
                'originalInvoice_is_null' => !isset($originalInvoice) || is_null($originalInvoice),
                'originalInvoice_type' => isset($originalInvoice) ? gettype($originalInvoice) : 'not_set',
                'originalInvoice_class' => isset($originalInvoice) ? get_class($originalInvoice) : 'not_set',
                'originalInvoice_id' => (isset($originalInvoice) && $originalInvoice) ? ($originalInvoice->id ?? 'no_id') : 'not_loaded',
                'invoice_param_exists' => isset($invoice),
                'invoice_param_type' => isset($invoice) ? gettype($invoice) : 'not_set',
                'invoice_param_class' => isset($invoice) ? get_class($invoice) : 'not_set',
                'invoice_param_id' => isset($invoice) ? ($invoice->id ?? 'no_id') : 'not_set',
                'memory_usage' => memory_get_usage(true),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if (DB::transactionLevel() > 0) {
                Log::info('Rolling back transaction due to general exception');
                DB::rollBack();
            }
            
            $errorDetails = [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'original_invoice_exists' => isset($originalInvoice),
                'original_invoice_type' => isset($originalInvoice) ? gettype($originalInvoice) : 'not_set',
                'original_invoice_id' => (isset($originalInvoice) && $originalInvoice) ? ($originalInvoice->id ?? 'no_id') : 'not_loaded',
                'invoice_param_exists' => isset($invoice),
                'invoice_param_type' => isset($invoice) ? gettype($invoice) : 'not_set',
                'invoice_param_id' => isset($invoice) ? ($invoice->id ?? 'no_id') : 'not_set',
            ];
            
            Log::error('Exchange creation failed (detailed)', $errorDetails);
            
            // Console'a da yazdır
            \Log::channel('single')->error('Exchange creation error (detailed):', $errorDetails);
            error_log('Exchange Error (detailed): ' . json_encode($errorDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            return back()->withInput()
                ->with('error', 'Değişim oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }
}
