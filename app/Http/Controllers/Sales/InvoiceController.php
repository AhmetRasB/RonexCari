<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Product;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentAccountId = session('current_account_id');
        
        // Admin kullanıcılar tüm hesapları görebilir
        if (auth()->user()->isAdmin()) {
            $invoices = Invoice::with(['customer', 'account', 'user'])
                ->latest()
                ->paginate(10);
        } else {
            $invoices = Invoice::with(['customer', 'account', 'user'])
                ->where('account_id', $currentAccountId)
                ->latest()
                ->paginate(10);
        }
        
        // Her fatura için tahsilat kontrolü yap
        foreach ($invoices as $invoice) {
            $collectionsForInvoice = \App\Models\Collection::where('customer_id', $invoice->customer_id)
                ->where('currency', $invoice->currency)
                ->where('transaction_date', '>=', $invoice->created_at)
                ->sum('amount');
            
            $invoice->collections_amount = $collectionsForInvoice;
            $invoice->has_collections = $collectionsForInvoice > 0;
        }
        
        return view('sales.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)->get();
        $currentAccountId = session('current_account_id');
        $products = Product::where('is_active', true)
            ->with('colorVariants') // Load color variants
            ->when($currentAccountId !== null, function($q) use ($currentAccountId) {
                $q->where('account_id', $currentAccountId);
            })
            ->when($currentAccountId === null, function($q) {
                // Eğer hesap seçili değilse, tüm ürünleri getir
                $q->whereNotNull('account_id');
            })
            ->orderBy('id', 'asc') // ID'ye göre sırala
            ->get();
        
        return view('sales.invoices.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Invoice store method called', [
            'request_data' => $request->all(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);
        
        // Get account_id with fallback
        $accountId = session('current_account_id');
        if (!$accountId) {
            // Fallback: get first active account
            $account = \App\Models\Account::active()->first();
            $accountId = $account ? $account->id : 1; // Default to Ronex1
        }

        // Get user_id with fallback
        $userId = auth()->id();
        if (!$userId) {
            // Fallback: get first user
            $user = \App\Models\User::first();
            $userId = $user ? $user->id : 1;
        }

        // TEMP: Skip validation per user request
        $validated = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'customer_id' => $request->input('customer_id'),
            'invoice_date' => $request->input('invoice_date', date('Y-m-d')),
            'invoice_time' => $request->input('invoice_time', date('H:i')),
            'due_date' => $request->input('due_date', date('Y-m-d')),
            'currency' => $request->input('currency', 'TRY'),
            'vat_status' => $request->input('vat_status', 'included'),
            'description' => $request->input('description'),
            'payment_completed' => (bool) $request->input('payment_completed', false),
            'items' => $request->input('items', []),
        ];

        DB::beginTransaction();
        try {
            // Generate robust, unique invoice number
            $prefix = 'INV-' . date('Y') . '-';
            $lastNumber = Invoice::where('invoice_number', 'like', $prefix . '%')
                ->orderByDesc('invoice_number')
                ->value('invoice_number');
            $sequence = 1;
            if ($lastNumber) {
                $sequence = (int) substr($lastNumber, -6) + 1;
            }
            $invoiceNumber = $prefix . str_pad($sequence, 6, '0', STR_PAD_LEFT);
            // Ensure uniqueness in edge cases
            while (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
                $sequence++;
                $invoiceNumber = $prefix . str_pad($sequence, 6, '0', STR_PAD_LEFT);
            }
            
            // Calculate totals
            $subtotal = 0;
            $vatAmount = 0;
            
            foreach ($validated['items'] as $item) {+
                $quantityVal = (float) ($item['quantity'] ?? 0);
                $unitPriceVal = (float) ($item['unit_price'] ?? 0);
                $discountRateVal = (float) ($item['discount_rate'] ?? 0);
                $taxRateVal = (float) ($item['tax_rate'] ?? 0);

                $lineTotal = $quantityVal * $unitPriceVal;
                $discountAmount = $lineTotal * ($discountRateVal / 100);
                $lineTotalAfterDiscount = $lineTotal - $discountAmount;
                
                if ($validated['vat_status'] === 'included') {
                    $vatAmount += $lineTotalAfterDiscount * ($taxRateVal / 100);
                }
                
                $subtotal += $lineTotalAfterDiscount;
            }
            
            $totalAmount = $subtotal + $vatAmount;
            // Get account_id and user_id with fallbacks
            $accountId = session('current_account_id');
            $userId = auth()->id();
            
            // Fallback for account_id if not in session
            if (!$accountId) {
                $accountId = \App\Models\Account::active()->first()?->id ?? 1;
            }
            
            // Fallback for user_id if not authenticated
            if (!$userId) {
                $userId = \App\Models\User::first()?->id ?? 1;
            }
            
            $invoice = Invoice::create([
                'account_id' => $accountId,
                'user_id' => $userId,
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'invoice_time' => $validated['invoice_time'],
                'due_date' => $validated['due_date'],
                'currency' => $validated['currency'],
                'vat_status' => $validated['vat_status'],
                'description' => $validated['description'],
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
                'payment_completed' => $validated['payment_completed'] ?? false
            ]);
            
            // Create invoice items
            foreach ($validated['items'] as $index => $item) {
                // Unit price is already converted by JavaScript
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? 0);
                $discountRate = (float) ($item['discount_rate'] ?? 0);
                
                $lineTotal = $quantity * $unitPrice;
                $discountAmount = $lineTotal * ($discountRate / 100);
                $lineTotalAfterDiscount = $lineTotal - $discountAmount;
                
                // Clean product_id for database storage
                $cleanProductId = $item['product_id'] ?? null;
                if ($cleanProductId) {
                    $cleanProductId = str_replace(['product_', 'series_'], '', $cleanProductId);
                }
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_service_name' => $item['product_service_name'],
                    'description' => $item['description'] ?? null,
                    'selected_color' => $item['selected_color'] ?? null,
                    'product_id' => $cleanProductId,
                    'product_type' => $item['type'] ?? 'product',
                    'quantity' => $quantity,
                    'unit' => 'Ad', // Default unit
                    'unit_price' => $unitPrice, // Already converted by JavaScript
                    'tax_rate' => $taxRate,
                    'discount_rate' => $discountRate,
                    'line_total' => $lineTotalAfterDiscount,
                    'sort_order' => $index
                ]);
            }
            
            // STOK DÜŞÜMÜ - ATOMIC OPERATION
            foreach ($validated['items'] as $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $productId = $item['product_id'] ?? null;
                $type = $item['type'] ?? 'product';
                $colorVariantId = $item['color_variant_id'] ?? null;
                $selectedColor = $item['selected_color'] ?? null;
                
                // Remove prefix from product_id if exists
                if ($productId && $type === 'series' && strpos($productId, 'series_') === 0) {
                    $productId = str_replace('series_', '', $productId);
                } elseif ($productId && $type === 'product' && strpos($productId, 'product_') === 0) {
                    $productId = str_replace('product_', '', $productId);
                }
                
                \Log::info('Processing stock deduction for item', [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'type' => $type,
                    'color_variant_id' => $colorVariantId,
                    'selected_color' => $selectedColor
                ]);
                
                if ($productId && $quantity > 0) {
                    if ($type === 'product') {
                        // Normal ürün stok düşümü
                        $product = \App\Models\Product::with('colorVariants')->find($productId);
                        if ($product) {
                            // Color variant seçilmişse (öncelik ID), o rengin stokunu düşür
                            if ($colorVariantId) {
                                $colorVariant = $product->colorVariants()->where('id', $colorVariantId)->first();
                                if ($colorVariant) {
                                    \Log::info('Deducting stock from color variant', [
                                        'product_name' => $product->name,
                                        'color' => $colorVariant->color,
                                        'current_stock' => $colorVariant->stock_quantity,
                                        'quantity_to_deduct' => $quantity
                                    ]);
                                    
                                    if ($colorVariant->stock_quantity < $quantity) {
                                        throw new \Exception("Yetersiz renk stoku! {$product->name} ({$colorVariant->color}) için stok: {$colorVariant->stock_quantity}, istenen: {$quantity}");
                                    }
                                    $colorVariant->decrement('stock_quantity', $quantity);
                                    
                                    \Log::info('Stock deducted successfully', [
                                        'new_stock' => $colorVariant->fresh()->stock_quantity
                                    ]);
                                }
                            } else if (!empty($item['selected_color']) && $product->colorVariants->count() > 0) {
                                $colorVariant = $product->colorVariants()->where('color', $item['selected_color'])->first();
                                if ($colorVariant) {
                                    if ($colorVariant->stock_quantity < $quantity) {
                                        throw new \Exception("Yetersiz renk stoku! {$product->name} ({$colorVariant->color}) için stok: {$colorVariant->stock_quantity}, istenen: {$quantity}");
                                    }
                                    $colorVariant->decrement('stock_quantity', $quantity);
                                }
                            } else {
                                // Color variant yoksa ana ürünün stokunu düşür
                                if ($product->stock_quantity < $quantity) {
                                    throw new \Exception("Yetersiz stok! {$product->name} için stok: {$product->stock_quantity}, istenen: {$quantity}");
                                }
                                $product->decrement('stock_quantity', $quantity);
                            }
                            
                            // Ana ürünün initial_stock'unu da güncelle
                            $product->decrement('initial_stock', $quantity);
                        }
                    } elseif ($type === 'series') {
                        // Seri ürün stok düşümü (1 seri = 1 adet)
                        $series = \App\Models\ProductSeries::with('colorVariants')->find($productId);
                        if ($series) {
                            // Color variant seçilmişse (öncelik ID), o rengin stokunu düşür
                            if ($colorVariantId) {
                                $colorVariant = $series->colorVariants()->where('id', $colorVariantId)->first();
                                if ($colorVariant) {
                                    \Log::info('Deducting stock from series color variant', [
                                        'series_name' => $series->name,
                                        'color' => $colorVariant->color,
                                        'current_stock' => $colorVariant->stock_quantity,
                                        'deducting' => $quantity
                                    ]);
                                    
                                    if ($colorVariant->stock_quantity < $quantity) {
                                        throw new \Exception("Yetersiz {$colorVariant->color} renk stoku! {$series->name} için {$colorVariant->color} stoku: {$colorVariant->stock_quantity} seri, istenen: {$quantity} seri");
                                    }
                                    
                                    $colorVariant->decrement('stock_quantity', $quantity);
                                    
                                    // Ana seri stokunu da güncelle
                                    $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                    $series->save();
                                }
                            } else {
                                // Renk seçilmemişse ana seri stokunu düşür
                                if ($series->stock_quantity < $quantity) {
                                    throw new \Exception("Yetersiz seri stoku! {$series->name} için stok: {$series->stock_quantity} seri, istenen: {$quantity} seri");
                                }
                                $series->decrement('stock_quantity', $quantity);
                            }
                        }
                    }
                    // Service'ler için stok düşümü yok
                }
            }
            
            // Handle customer balance if payment is not completed
            if (!$validated['payment_completed']) {
                $customer = \App\Models\Customer::find($validated['customer_id']);
                if ($customer) {
                    $currency = $validated['currency'];
                    
                    // Update balance based on currency
                    switch ($currency) {
                        case 'TRY':
                            $customer->increment('balance_try', $totalAmount);
                            break;
                        case 'USD':
                            $customer->increment('balance_usd', $totalAmount);
                            break;
                        case 'EUR':
                            $customer->increment('balance_eur', $totalAmount);
                            break;
                    }
                    
                    // Also update the legacy balance field for backward compatibility
                    $customer->increment('balance', $totalAmount);
                }
            }
            
            DB::commit();
            
            \Log::info('Invoice created successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
                'total_amount' => $invoice->total_amount,
                'currency' => $invoice->currency,
                'items_count' => count($validated['items'])
            ]);
            
            return redirect()->route('sales.invoices.index')
                ->with('success', 'Fatura başarıyla oluşturuldu.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'validated_data' => $validated ?? null
            ]);
            
            return back()->withInput()
                ->with('error', 'Fatura oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);
        
        // Customer null ise uyarı ver
        if (!$invoice->customer) {
            \Log::warning('Invoice customer is null', [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id
            ]);
        }
        
        return view('sales.invoices.show', compact('invoice'));
    }

    /**
     * Display the invoice preview for printing.
     */
    public function preview(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);
        
        // Customer null ise uyarı ver
        if (!$invoice->customer) {
            \Log::warning('Invoice customer is null in preview', [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id
            ]);
        }
        
        return view('sales.invoices.preview', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $customers = Customer::where('is_active', true)->get();
        $currentAccountId = session('current_account_id');
        $products = Product::where('is_active', true)
            ->with('colorVariants') // Load color variants like in create method
            ->when($currentAccountId !== null, function($q) use ($currentAccountId) {
                $q->where('account_id', $currentAccountId);
            })
            ->when($currentAccountId === null, function($q) {
                // Eğer hesap seçili değilse, tüm ürünleri getir
                $q->whereNotNull('account_id');
            })
            ->orderBy('id', 'asc') // ID'ye göre sırala
            ->get();
        $invoice->load('items');
        
        return view('sales.invoices.edit', compact('invoice', 'customers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        \Log::info('Invoice update method called', [
            'invoice_id' => $invoice->id,
            'request_data' => $request->all()
        ]);
        
        // Debug: Log all form fields
        \Log::info('Form fields received:', [
            'customer_id' => $request->input('customer_id'),
            'invoice_date' => $request->input('invoice_date'),
            'invoice_time' => $request->input('invoice_time'),
            'due_date' => $request->input('due_date'),
            'currency' => $request->input('currency'),
            'vat_status' => $request->input('vat_status'),
            'description' => $request->input('description'),
            'payment_completed' => $request->input('payment_completed'),
            'items_count' => count($request->input('items', [])),
            'items' => $request->input('items', [])
        ]);
        
        // Get account_id with fallback
        $accountId = session('current_account_id');
        if (!$accountId) {
            $account = \App\Models\Account::active()->first();
            $accountId = $account ? $account->id : 1;
        }

        // Get user_id with fallback
        $userId = auth()->id();
        if (!$userId) {
            $user = \App\Models\User::first();
            $userId = $user ? $user->id : 1;
        }

        $validated = [
            'customer_id' => $request->input('customer_id'),
            'invoice_date' => $request->input('invoice_date', date('Y-m-d')),
            'invoice_time' => $request->input('invoice_time', date('H:i')),
            'due_date' => $request->input('due_date', date('Y-m-d')),
            'currency' => $request->input('currency', 'TRY'),
            'vat_status' => $request->input('vat_status', 'included'),
            'description' => $request->input('description'),
            'payment_completed' => (bool) $request->input('payment_completed', false),
            'items' => $request->input('items', []),
        ];

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $vatAmount = 0;
            
            foreach ($validated['items'] as $item) {
                $quantityVal = (float) ($item['quantity'] ?? 0);
                $unitPriceVal = (float) ($item['unit_price'] ?? 0);
                $discountRateVal = (float) ($item['discount_rate'] ?? 0);
                $taxRateVal = (float) ($item['tax_rate'] ?? 0);

                $lineTotal = $quantityVal * $unitPriceVal;
                $discountAmount = $lineTotal * ($discountRateVal / 100);
                $lineTotalAfterDiscount = $lineTotal - $discountAmount;
                
                if ($validated['vat_status'] === 'included') {
                    $vatAmount += $lineTotalAfterDiscount * ($taxRateVal / 100);
                }
                
                $subtotal += $lineTotalAfterDiscount;
            }
            
            $totalAmount = $subtotal + $vatAmount;
            
            // RESTORE STOCK FROM OLD ITEMS BEFORE DELETING
            $oldItems = $invoice->items()->get();
            foreach ($oldItems as $oldItem) {
                if ($oldItem->product_id && $oldItem->quantity > 0) {
                    if ($oldItem->product_type === 'product') {
                        $product = \App\Models\Product::with('colorVariants')->find($oldItem->product_id);
                        if ($product) {
                            if ($oldItem->color_variant_id) {
                                $colorVariant = $product->colorVariants()->find($oldItem->color_variant_id);
                                if ($colorVariant) {
                                    $colorVariant->increment('stock_quantity', $oldItem->quantity);
                                }
                            } elseif ($oldItem->selected_color && $product->colorVariants->count() > 0) {
                                $colorVariant = $product->colorVariants()->where('color', $oldItem->selected_color)->first();
                                if ($colorVariant) {
                                    $colorVariant->increment('stock_quantity', $oldItem->quantity);
                                }
                            } else {
                                $product->increment('stock_quantity', $oldItem->quantity);
                            }
                            $product->increment('initial_stock', $oldItem->quantity);
                        }
                    } elseif ($oldItem->product_type === 'series') {
                        $series = \App\Models\ProductSeries::with('colorVariants')->find($oldItem->product_id);
                        if ($series) {
                            if ($oldItem->color_variant_id) {
                                $colorVariant = $series->colorVariants()->find($oldItem->color_variant_id);
                                if ($colorVariant) {
                                    $colorVariant->increment('stock_quantity', $oldItem->quantity);
                                    $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                    $series->save();
                                }
                            } else {
                                $series->increment('stock_quantity', $oldItem->quantity);
                            }
                        }
                    }
                }
            }
            
            // Update invoice
            $invoice->update([
                'customer_id' => $validated['customer_id'],
                'invoice_date' => $validated['invoice_date'],
                'invoice_time' => $validated['invoice_time'],
                'due_date' => $validated['due_date'],
                'currency' => $validated['currency'],
                'vat_status' => $validated['vat_status'],
                'description' => $validated['description'],
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
                'payment_completed' => $validated['payment_completed'] ?? false
            ]);
            
            // Delete existing items
            $invoice->items()->delete();
            
            // Create new invoice items
            foreach ($validated['items'] as $index => $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $taxRate = (float) ($item['tax_rate'] ?? 0);
                $discountRate = (float) ($item['discount_rate'] ?? 0);
                
                $lineTotal = $quantity * $unitPrice;
                $discountAmount = $lineTotal * ($discountRate / 100);
                $lineTotalAfterDiscount = $lineTotal - $discountAmount;
                
                // Clean product_id
                $cleanProductId = $item['product_id'] ?? null;
                if ($cleanProductId) {
                    $cleanProductId = str_replace(['product_', 'series_'], '', $cleanProductId);
                }
                
                // Debug: Log item data
                \Log::info("Creating invoice item {$index}:", [
                    'product_service_name' => $item['product_service_name'],
                    'description' => $item['description'] ?? null,
                    'selected_color' => $item['selected_color'] ?? null,
                    'product_id' => $cleanProductId,
                    'color_variant_id' => $item['color_variant_id'] ?? null,
                    'product_type' => $item['type'] ?? 'product',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_currency' => $item['unit_currency'] ?? 'TRY',
                    'tax_rate' => $taxRate,
                    'discount_rate' => $discountRate,
                    'line_total' => $lineTotalAfterDiscount
                ]);
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_service_name' => $item['product_service_name'],
                    'description' => $item['description'] ?? null,
                    'selected_color' => $item['selected_color'] ?? null,
                    'product_id' => $cleanProductId,
                    'color_variant_id' => $item['color_variant_id'] ?? null,
                    'product_type' => $item['type'] ?? 'product',
                    'quantity' => $quantity,
                    'unit' => 'Ad',
                    'unit_price' => $unitPrice,
                    'unit_currency' => $item['unit_currency'] ?? 'TRY',
                    'tax_rate' => $taxRate,
                    'discount_rate' => $discountRate,
                    'line_total' => $lineTotalAfterDiscount,
                    'sort_order' => $index
                ]);
            }
            
            // STOK DÜŞÜMÜ - ATOMIC OPERATION (same as create)
            foreach ($validated['items'] as $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $productId = $item['product_id'] ?? null;
                $type = $item['type'] ?? 'product';
                $colorVariantId = $item['color_variant_id'] ?? null;
                $selectedColor = $item['selected_color'] ?? null;
                
                // Remove prefix from product_id if exists
                if ($productId && $type === 'series' && strpos($productId, 'series_') === 0) {
                    $productId = str_replace('series_', '', $productId);
                } elseif ($productId && $type === 'product' && strpos($productId, 'product_') === 0) {
                    $productId = str_replace('product_', '', $productId);
                }
                
                \Log::info('Processing stock deduction for item in update', [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'type' => $type,
                    'color_variant_id' => $colorVariantId,
                    'selected_color' => $selectedColor
                ]);
                
                if ($productId && $quantity > 0) {
                    if ($type === 'product') {
                        // Normal ürün stok düşümü
                        $product = \App\Models\Product::with('colorVariants')->find($productId);
                        if ($product) {
                            // Color variant seçilmişse (öncelik ID), o rengin stokunu düşür
                            if ($colorVariantId) {
                                $colorVariant = $product->colorVariants()->where('id', $colorVariantId)->first();
                                if ($colorVariant) {
                                    if ($colorVariant->stock_quantity < $quantity) {
                                        throw new \Exception("Yetersiz renk stoku! {$product->name} ({$colorVariant->color}) için stok: {$colorVariant->stock_quantity}, istenen: {$quantity}");
                                    }
                                    $colorVariant->decrement('stock_quantity', $quantity);
                                }
                            } else if (!empty($item['selected_color']) && $product->colorVariants->count() > 0) {
                                $colorVariant = $product->colorVariants()->where('color', $item['selected_color'])->first();
                                if ($colorVariant) {
                                    if ($colorVariant->stock_quantity < $quantity) {
                                        throw new \Exception("Yetersiz renk stoku! {$product->name} ({$colorVariant->color}) için stok: {$colorVariant->stock_quantity}, istenen: {$quantity}");
                                    }
                                    $colorVariant->decrement('stock_quantity', $quantity);
                                }
                            } else {
                                // Color variant yoksa ana ürünün stokunu düşür
                                if ($product->stock_quantity < $quantity) {
                                    throw new \Exception("Yetersiz stok! {$product->name} için stok: {$product->stock_quantity}, istenen: {$quantity}");
                                }
                                $product->decrement('stock_quantity', $quantity);
                            }
                            
                            // Ana ürünün initial_stock'unu da güncelle
                            $product->decrement('initial_stock', $quantity);
                        }
                    } elseif ($type === 'series') {
                        // Seri ürün stok düşümü (1 seri = 1 adet)
                        $series = \App\Models\ProductSeries::with('colorVariants')->find($productId);
                        if ($series) {
                            // Color variant seçilmişse (öncelik ID), o rengin stokunu düşür
                            if ($colorVariantId) {
                                $colorVariant = $series->colorVariants()->where('id', $colorVariantId)->first();
                                if ($colorVariant) {
                                    if ($colorVariant->stock_quantity < $quantity) {
                                        throw new \Exception("Yetersiz {$colorVariant->color} renk stoku! {$series->name} için {$colorVariant->color} stoku: {$colorVariant->stock_quantity} seri, istenen: {$quantity} seri");
                                    }
                                    
                                    $colorVariant->decrement('stock_quantity', $quantity);
                                    
                                    // Ana seri stokunu da güncelle
                                    $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                    $series->save();
                                }
                            } else {
                                // Renk seçilmemişse ana seri stokunu düşür
                                if ($series->stock_quantity < $quantity) {
                                    throw new \Exception("Yetersiz seri stoku! {$series->name} için stok: {$series->stock_quantity} seri, istenen: {$quantity} seri");
                                }
                                $series->decrement('stock_quantity', $quantity);
                            }
                        }
                    }
                    // Service'ler için stok düşümü yok
                }
            }
            
            DB::commit();
            
            // Refresh invoice with relationships
            $invoice->refresh();
            $invoice->load(['customer', 'items']);
            
            \Log::info('Invoice updated successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);
            
            return redirect()->route('sales.invoices.show', $invoice->id)
                ->with('success', 'Fatura başarıyla güncellendi.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Invoice update failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()
                ->with('error', 'Fatura güncellenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('sales.invoices.index')
            ->with('success', 'Fatura başarıyla silindi.');
    }

    /**
     * Search customers for autocomplete
     */
    public function searchCustomers(Request $request)
    {
        try {
            $query = $request->get('q', '');
            if (strlen($query) < 2) {
                return response()->json([]);
            }
            $customers = Customer::where('is_active', true)
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('company_name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get(['id', 'name', 'company_name', 'email', 'phone']);
            return response()->json($customers);
        } catch (\Throwable $e) {
            \Log::error('searchCustomers failed', ['error' => $e->getMessage(), 'q' => $request->get('q')]);
            return response()->json(['message' => 'Server Error'], 500);
        }
    }

    /**
     * Search products and services for autocomplete
     */
    public function searchProducts(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }
            $currentAccountId = session('current_account_id');
        
        // Search in products - search in all relevant fields
        $products = \App\Models\Product::with('colorVariants')
            ->where('is_active', true)
            ->when($currentAccountId !== null, function($q) use ($currentAccountId) {
                $q->where('account_id', $currentAccountId);
            })
            ->when($currentAccountId === null, function($q) {
                // Eğer hesap seçili değilse, tüm ürünleri getir
                $q->whereNotNull('account_id');
            })
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('size', 'like', "%{$query}%")
                  ->orWhere('color', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'sku', 'category', 'brand', 'size', 'color', 'price', 'cost', 'stock_quantity')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $hasColorVariants = $product->colorVariants->count() > 0;
                
                return [
                    'id' => 'product_' . $product->id,
                    'product_id' => $product->id, // Add product_id for backend processing
                    'name' => $product->name,
                    'product_code' => $product->sku,
                    'category' => $product->category,
                    'brand' => $product->brand,
                    'size' => $product->size,
                    'color' => $product->color,
                    'price' => $product->price,
                    'purchase_price' => $product->cost,
                    'vat_rate' => 20, // Default VAT rate
                    'currency' => 'TRY',
                    'type' => 'product',
                    'stock_quantity' => $hasColorVariants ? $product->total_stock : $product->stock_quantity,
                    'has_color_variants' => $hasColorVariants,
                    'color_variants' => $hasColorVariants ? $product->colorVariants->map(function($variant) {
                        return [
                            'id' => $variant->id,
                            'color' => $variant->color,
                            'stock_quantity' => $variant->stock_quantity,
                            'critical_stock' => $variant->critical_stock
                        ];
                    })->toArray() : []
                ];
            });
        
        // Search in product series - search in all relevant fields
        $productSeries = \App\Models\ProductSeries::with(['seriesItems', 'colorVariants'])
            ->where('is_active', true)
            ->when($currentAccountId !== null, function($q) use ($currentAccountId) {
                $q->where('account_id', $currentAccountId);
            })
            ->when($currentAccountId === null, function($q) {
                // Eğer hesap seçili değilse, tüm ürünleri getir
                $q->whereNotNull('account_id');
            })
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'sku', 'category', 'brand', 'price', 'cost', 'series_size', 'stock_quantity')
            ->limit(10)
            ->get()
            ->map(function ($series) {
                $colorVariants = $series->colorVariants->map(function($variant) {
                    return [
                        'id' => $variant->id,
                        'color' => $variant->color,
                        'stock_quantity' => $variant->stock_quantity
                    ];
                });
                
                return [
                    'id' => 'series_' . $series->id,
                    'name' => $series->name . ' (' . $series->series_size . 'li Seri)',
                    'product_code' => $series->sku,
                    'category' => $series->category,
                    'brand' => $series->brand,
                    'size' => $series->series_size . 'li Seri',
                    'color' => null,
                    'price' => $series->price,
                    'purchase_price' => $series->cost,
                    'vat_rate' => 20, // Default VAT rate
                    'currency' => 'TRY',
                    'type' => 'series',
                    'stock_quantity' => $series->stock_quantity,
                    'series_size' => $series->series_size,
                    'sizes' => $series->seriesItems->pluck('size')->toArray(),
                    'has_color_variants' => $colorVariants->count() > 0,
                    'color_variants' => $colorVariants
                ];
            });
        
        // Search in services - search in all relevant fields
        $services = \App\Models\Service::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'code', 'category', 'price')
            ->limit(10)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => 'service_' . $service->id,
                    'name' => $service->name,
                    'product_code' => $service->code,
                    'category' => $service->category,
                    'brand' => null,
                    'size' => null,
                    'color' => null,
                    'price' => $service->price,
                    'purchase_price' => null,
                    'vat_rate' => 20, // Default VAT rate
                    'currency' => 'TRY',
                    'type' => 'service'
                ];
            });
        
        // Combine and return
        $results = $products->concat($productSeries)->concat($services)->take(20);
        
        \Log::info('Search results:', [
            'query' => $query,
            'products_count' => $products->count(),
            'series_count' => $productSeries->count(),
            'services_count' => $services->count(),
            'total_results' => $results->count()
        ]);
        
        return response()->json($results);
        } catch (\Throwable $e) {
            \Log::error('searchProducts failed', ['error' => $e->getMessage(), 'q' => $request->get('q')]);
            return response()->json(['message' => 'Server Error'], 500);
        }
    }

    /**
     * Show print view for invoice
     */
    public function print(Invoice $invoice)
    {
        return view('sales.invoices.print', compact('invoice'));
    }

    // Actions removed - invoices are now directly approved

    /**
     * Mark invoice as paid (tahsilat yapıldı)
     */
    public function markPaid(Invoice $invoice)
    {
        try {
            $invoice->payment_completed = true;
            // If status column exists on model/table, set to paid as well
            if (\Schema::hasColumn('invoices', 'status')) {
                $invoice->status = 'paid';
            }
            $invoice->save();

            return redirect()
                ->back()
                ->with('success', 'Fatura tahsilatı tamamlandı olarak işaretlendi.');
        } catch (\Throwable $e) {
            \Log::error('markPaid failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()
                ->back()
                ->with('error', 'Tahsilat işaretlenemedi: ' . $e->getMessage());
        }
    }

    /**
     * Get current currency exchange rates
     */
    public function getCurrencyRates()
    {
        try {
            $rates = $this->currencyService->getExchangeRates();
            return response()->json([
                'success' => true,
                'rates' => $rates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Döviz kurları alınamadı',
                'rates' => $this->currencyService->getFallbackRates()
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = json_decode($request->input('ids'), true);
            if (empty($ids) || !is_array($ids)) {
                return redirect()->back()->with('error', 'Geçersiz seçim');
            }
            $deletedCount = Invoice::whereIn('id', $ids)->delete();
            return redirect()->route('sales.invoices.index')->with('success', $deletedCount . ' fatura başarıyla silindi');
        } catch (\Exception $e) {
            \Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Silme işlemi sırasında bir hata oluştu');
        }
    }

}
