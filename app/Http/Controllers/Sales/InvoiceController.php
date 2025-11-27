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
        // Tekli ürünler kaldırıldı - artık sadece seri ürünler ve hizmetler kullanılıyor
        $products = collect([]);
        
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

        // Basic validation and item sanity checks
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

        // Validate items: non-service rows must have product_id; require color when product/series has variants
        foreach (($validated['items'] ?? []) as $i => $it) {
            $rowNo = $i + 1;
            $type = $it['type'] ?? 'product';
            if ($type !== 'service') {
                $pidRaw = $it['product_id'] ?? null;
                $pid = $pidRaw ? (int) str_replace(['product_', 'series_', 'service_'], '', (string)$pidRaw) : null;
                if (!$pid) {
                    return back()->withInput()->with('error', "Satır {$rowNo}: Lütfen bir ürün/seri seçin.");
                }
                // If product has color variants, enforce color selection
                if ($type === 'product') {
                    $p = \App\Models\Product::withCount('colorVariants')->find($pid);
                    if ($p && $p->color_variants_count > 0 && empty($it['color_variant_id'])) {
                        return back()->withInput()->with('error', "Satır {$rowNo}: Lütfen renk seçin.");
                    }
                } elseif ($type === 'series') {
                    $s = \App\Models\ProductSeries::withCount('colorVariants')->find($pid);
                    if ($s && $s->color_variants_count > 0 && empty($it['color_variant_id'])) {
                        return back()->withInput()->with('error', "Satır {$rowNo}: Lütfen seri rengi seçin.");
                    }
                }
            }
        }

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
            
            foreach ($validated['items'] as $item) {
                $quantityVal = (float) ($item['quantity'] ?? 0);
                $unitPriceVal = (float) ($item['unit_price'] ?? 0);
                $discountRateVal = (float) ($item['discount_rate'] ?? 0);
                $taxRateVal = (float) ($item['tax_rate'] ?? 0);

                $lineTotal = $quantityVal * $unitPriceVal;
                // İndirim artık sabit tutar olarak geldiği için yüzdeye çevirmiyoruz
                $discountAmount = $discountRateVal; // Sabit tutar
                $lineTotalAfterDiscount = max(0, $lineTotal - $discountAmount);
                
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
                // İndirim artık sabit tutar olarak geldiği için yüzdeye çevirmiyoruz
                $discountAmount = $discountRate; // Sabit tutar
                $lineTotalAfterDiscount = max(0, $lineTotal - $discountAmount);
                
                // Clean product_id for database storage
                $cleanProductId = $item['product_id'] ?? null;
                if ($cleanProductId) {
                    $cleanProductId = str_replace(['product_', 'series_', 'service_'], '', $cleanProductId);
                }

                // Resolve selected color name from variant id if not provided
                $selectedColorName = $item['selected_color'] ?? null;
                if (empty($selectedColorName) && !empty($item['color_variant_id'])) {
                    $itemType = $item['type'] ?? 'product';
                    if ($itemType === 'series') {
                        $v = \App\Models\ProductSeriesColorVariant::find($item['color_variant_id']);
                        if ($v) { $selectedColorName = $v->color; }
                    } elseif ($itemType === 'product') {
                        $v = \App\Models\ProductColorVariant::find($item['color_variant_id']);
                        if ($v) { $selectedColorName = $v->color; }
                    }
                }
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_service_name' => $item['product_service_name'],
                    'description' => $item['description'] ?? null,
                    'selected_color' => $selectedColorName,
                    'color_variant_id' => $item['color_variant_id'] ?? null,
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
                        // Seri ürün stok düşümü: Kullanıcı adet girer; doğrudan adet düşülür (çarpım yok)
                        $series = \App\Models\ProductSeries::with(['colorVariants','seriesItems'])->find($productId);
                        if ($series) {
                            $unitsToChange = (int) $quantity;
                            // Color variant seçilmişse (öncelik ID), o rengin stokunu düşür
                            if ($colorVariantId) {
                                $colorVariant = $series->colorVariants()->where('id', $colorVariantId)->first();
                                if ($colorVariant) {
                                    \Log::info('Deducting stock from series color variant', [
                                        'series_name' => $series->name,
                                        'color' => $colorVariant->color,
                                        'current_stock' => $colorVariant->stock_quantity,
                                        'deducting_units' => $unitsToChange
                                    ]);
                                    if ($colorVariant->stock_quantity < $unitsToChange) {
                                        throw new \Exception("Yetersiz {$colorVariant->color} renk stoku! {$series->name} için {$colorVariant->color} stoku: {$colorVariant->stock_quantity} adet, istenen: {$unitsToChange} adet");
                                    }
                                    $colorVariant->decrement('stock_quantity', $unitsToChange);
                                    // Ana seri stokunu güncelle
                                    $series->refresh();
                                    $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                    $series->save();
                                }
                            } else {
                                if ($series->stock_quantity < $unitsToChange) {
                                    throw new \Exception("Yetersiz stok! {$series->name} için stok: {$series->stock_quantity} adet, istenen: {$unitsToChange} adet");
                                }
                                $series->decrement('stock_quantity', $unitsToChange);
                                // Varyant varsa parent'ı yeniden hesapla
                                $series->refresh();
                                if ($series->colorVariants && $series->colorVariants->count() > 0) {
                                    $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                    $series->save();
                                }
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
        // Tekli ürünler kaldırıldı - artık sadece seri ürünler ve hizmetler kullanılıyor
        $products = collect([]);
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
                // İndirim artık sabit tutar olarak geldiği için yüzdeye çevirmiyoruz
                $discountAmount = $discountRateVal; // Sabit tutar
                $lineTotalAfterDiscount = max(0, $lineTotal - $discountAmount);
                
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
                // İndirim artık sabit tutar olarak geldiği için yüzdeye çevirmiyoruz
                $discountAmount = $discountRate; // Sabit tutar
                $lineTotalAfterDiscount = max(0, $lineTotal - $discountAmount);
                
                // Clean product_id
                $cleanProductId = $item['product_id'] ?? null;
                if ($cleanProductId) {
                    $cleanProductId = str_replace(['product_', 'series_', 'service_'], '', $cleanProductId);
                }

                // Resolve selected color name from variant id if not provided (edit flow)
                $selectedColorName = $item['selected_color'] ?? null;
                if (empty($selectedColorName) && !empty($item['color_variant_id'])) {
                    $itemType = $item['type'] ?? 'product';
                    if ($itemType === 'series') {
                        $v = \App\Models\ProductSeriesColorVariant::find($item['color_variant_id']);
                        if ($v) { $selectedColorName = $v->color; }
                    } elseif ($itemType === 'product') {
                        $v = \App\Models\ProductColorVariant::find($item['color_variant_id']);
                        if ($v) { $selectedColorName = $v->color; }
                    }
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
                    'selected_color' => $selectedColorName,
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
                                // Always recompute parent stock from variants if any exist
                                $series->refresh();
                                if ($series->colorVariants && $series->colorVariants->count() > 0) {
                                    $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                    $series->save();
                                }
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
        try {
            DB::beginTransaction();
            
            \Log::info('Invoice deletion started', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
                'total_amount' => $invoice->total_amount,
                'currency' => $invoice->currency,
                'payment_completed' => $invoice->payment_completed
            ]);
            
            // Load invoice items with relationships
            $invoice->load('items');
            
            // 1. Restore stock for sold items (is_return = false)
            // 2. Deduct stock for returned items (is_return = true) - because returns added stock back
            foreach ($invoice->items as $item) {
                if ($item->product_id && $item->quantity > 0) {
                    if ($item->product_type === 'series') {
                        $series = \App\Models\ProductSeries::with('colorVariants')->find($item->product_id);
                        if ($series) {
                            if ($item->is_return) {
                                // Return item: deduct stock (because return added stock back)
                                if ($item->color_variant_id) {
                                    $colorVariant = $series->colorVariants()->where('id', $item->color_variant_id)->first();
                                    if ($colorVariant) {
                                        $colorVariant->decrement('stock_quantity', $item->quantity);
                                        $series->refresh();
                                        $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                        $series->save();
                                        \Log::info('Stock deducted for return item deletion', [
                                            'series_id' => $series->id,
                                            'color_variant_id' => $item->color_variant_id,
                                            'quantity' => $item->quantity
                                        ]);
                                    }
                                } else {
                                    $series->decrement('stock_quantity', $item->quantity);
                                    \Log::info('Stock deducted for return item deletion', [
                                        'series_id' => $series->id,
                                        'quantity' => $item->quantity
                                    ]);
                                }
                            } else {
                                // Normal item: restore stock
                                if ($item->color_variant_id) {
                                    $colorVariant = $series->colorVariants()->where('id', $item->color_variant_id)->first();
                                    if ($colorVariant) {
                                        $colorVariant->increment('stock_quantity', $item->quantity);
                                        $series->refresh();
                                        $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                        $series->save();
                                        \Log::info('Stock restored for normal item deletion', [
                                            'series_id' => $series->id,
                                            'color_variant_id' => $item->color_variant_id,
                                            'quantity' => $item->quantity
                                        ]);
                                    }
                                } else {
                                    $series->increment('stock_quantity', $item->quantity);
                                    \Log::info('Stock restored for normal item deletion', [
                                        'series_id' => $series->id,
                                        'quantity' => $item->quantity
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            
            // 3. Update customer balance (subtract invoice amount if payment was not completed)
            if (!$invoice->payment_completed && $invoice->customer_id) {
                $customer = \App\Models\Customer::find($invoice->customer_id);
                if ($customer) {
                    $currency = $invoice->currency;
                    $totalAmount = $invoice->total_amount;
                    
                    // Subtract from balance based on currency
                    switch ($currency) {
                        case 'TRY':
                            $customer->decrement('balance_try', $totalAmount);
                            break;
                        case 'USD':
                            $customer->decrement('balance_usd', $totalAmount);
                            break;
                        case 'EUR':
                            $customer->decrement('balance_eur', $totalAmount);
                            break;
                    }
                    
                    // Also update the legacy balance field
                    $customer->decrement('balance', $totalAmount);
                    
                    \Log::info('Customer balance decreased for invoice deletion', [
                        'customer_id' => $customer->id,
                        'amount' => $totalAmount,
                        'currency' => $currency
                    ]);
                }
            }
            
            // 4. Delete related exchanges (if this invoice is part of an exchange)
            \App\Models\Exchange::where('original_invoice_id', $invoice->id)
                ->orWhere('new_invoice_id', $invoice->id)
                ->delete();
            
            // 5. Delete the invoice (this will cascade delete invoice items)
        $invoice->delete();
            
            DB::commit();
            
            \Log::info('Invoice deleted successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);
            
        return redirect()->route('sales.invoices.index')
            ->with('success', 'Fatura başarıyla silindi.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Invoice deletion failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->with('error', 'Fatura silinirken bir hata oluştu: ' . $e->getMessage());
        }
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

            // Try exact match on series color-variant barcode (scanner use-case)
            $matchedSeriesId = null;
            $matchedVariantId = null;
            try {
                $mv = \App\Models\ProductSeriesColorVariant::query()
                    ->when($currentAccountId !== null, function($q) use ($currentAccountId) {
                        $q->whereHas('productSeries', function($qq) use ($currentAccountId) {
                            $qq->where('account_id', $currentAccountId);
                        });
                    })
                    ->where('barcode', $query)
                    ->first();
                if ($mv) {
                    $matchedSeriesId = $mv->product_series_id;
                    $matchedVariantId = $mv->id;
                }
            } catch (\Throwable $e) {
                \Log::warning('Variant barcode lookup failed', ['error' => $e->getMessage()]);
            }
        
        // Tekli ürünler kaldırıldı - sadece seri ürünler ve hizmetler gösteriliyor
        $products = collect([]);
        
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
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhereHas('colorVariants', function($qq) use ($query) {
                          $qq->where('color', 'like', "%{$query}%")
                             ->orWhere('barcode', 'like', "%{$query}%");
                      });
            })
            ->select('id', 'name', 'sku', 'category', 'brand', 'price', 'price_currency', 'cost', 'cost_currency', 'series_size', 'stock_quantity')
            ->get()
                ->map(function ($series) use ($matchedSeriesId, $matchedVariantId) {
                $colorVariants = $series->colorVariants->map(function($variant) {
                    return [
                        'id' => $variant->id,
                        'color' => $variant->color,
                            'barcode' => $variant->barcode,
                            'stock_quantity' => $variant->stock_quantity,
                    ];
                });
                
                // Seri boyutunu hesapla: kayıtlı değer; yoksa seri içeriğindeki miktarların toplamı; o da yoksa beden sayısı
                $seriesItemsSum = $series->seriesItems->sum('quantity_per_series');
                $seriesSize = $series->series_size ?: ($seriesItemsSum ?: $series->seriesItems->count());
                
                return [
                    'id' => 'series_' . $series->id,
                    'name' => $series->name . ($seriesSize > 0 ? ' (' . $seriesSize . 'li Seri)' : ''),
                    'product_code' => $series->sku,
                    'category' => $series->category,
                    'brand' => $series->brand,
                    'size' => $seriesSize > 0 ? $seriesSize . 'li Seri' : 'Seri Boyutu Belirlenmemiş',
                    'color' => null,
                    'price' => $series->price,
                    'purchase_price' => $series->cost,
                    'vat_rate' => 20, // Default VAT rate
                    // Display currency for UI should follow selling price currency
                    'currency' => $series->price_currency ?? 'TRY',
                    'type' => 'series',
                    'stock_quantity' => $series->stock_quantity,
                    'series_size' => $series->series_size,
                    'sizes' => $series->seriesItems->pluck('size')->toArray(),
                    'has_color_variants' => $colorVariants->count() > 0,
                        'color_variants' => $colorVariants,
                        'preferred_color_variant_id' => ($matchedSeriesId === $series->id) ? $matchedVariantId : null,
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
        
        // Combine and return (tekli ürünler hariç - sadece seri ürünler ve hizmetler)
        $results = $productSeries->concat($services)->take(20);
        
        \Log::info('Search results:', [
            'query' => $query,
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
     * Add return item to invoice
     */
    public function addReturn(Request $request, Invoice $invoice)
    {
        \Log::info('Add return item called', [
            'invoice_id' => $invoice->id,
            'request_data' => $request->all()
        ]);
        
        try {
            $validated = $request->validate([
                'invoice_item_id' => 'required|integer|exists:invoice_items,id',
                'quantity' => 'required|numeric|min:0.01',
            ]);
            
            DB::beginTransaction();

            // Orijinal invoice item'ı bul ve kilitle (yarış durumlarını önlemek için)
            $originalItem = \App\Models\InvoiceItem::where('id', $validated['invoice_item_id'])->lockForUpdate()->firstOrFail();
            
            // Orijinal item'ın iade edilmediğinden emin ol
            if ($originalItem->is_return) {
                throw new \Exception('Bu satır zaten bir iade satırıdır.');
            }
            
            // İade miktarı kontrolü
            $availableQty = (float) $originalItem->quantity;
            if ($validated['quantity'] > $availableQty) {
                throw new \Exception("İade miktarı orijinal miktardan fazla olamaz. Maksimum: {$availableQty}");
            }
            
            // Validasyon genişletilmiş
            $validated = array_merge($validated, [
                'product_id' => $originalItem->product_id,
                'type' => $originalItem->product_type ?? 'product',
                'unit_price' => $originalItem->unit_price,
                'tax_rate' => $originalItem->tax_rate,
                'discount_rate' => $originalItem->discount_rate ?? 0,
                'selected_color' => $originalItem->selected_color,
                'color_variant_id' => $originalItem->color_variant_id,
                'description' => $request->input('description', 'İade - ' . $originalItem->product_service_name)
            ]);
            
            // Normalize/auto-correct product type by probing actual records
            try {
                $probingSeries = \App\Models\ProductSeries::find($validated['product_id']);
                $probingProduct = \App\Models\Product::find($validated['product_id']);
                if ($validated['type'] !== 'series' && $probingSeries && !$probingProduct) {
                    // Mis-typed series as product: fix it up to ensure stock restoration works
                    $validated['type'] = 'series';
                } elseif ($validated['type'] !== 'product' && $probingProduct && !$probingSeries) {
                    $validated['type'] = 'product';
                }
            } catch (\Throwable $e) {
                // ignore probing failures; continue with provided type
            }

            // Calculate return item totals (negative)
            $quantity = (float) $validated['quantity'];
            $unitPrice = (float) $validated['unit_price'];
            $taxRate = (float) ($validated['tax_rate'] ?? 0);
            $discount = (float) ($validated['discount_rate'] ?? 0);
            
            $lineTotal = $quantity * $unitPrice;
            $discountAmount = $discount;
            $lineTotalAfterDiscount = max(0, $lineTotal - $discountAmount);
            $taxAmount = $lineTotalAfterDiscount * ($taxRate / 100);
            $itemTotal = $lineTotalAfterDiscount + $taxAmount;
            
            // Get product name
            $productName = '';
            if ($validated['type'] === 'product') {
                $product = \App\Models\Product::find($validated['product_id']);
                $productName = $product ? $product->name : 'Ürün';
            } elseif ($validated['type'] === 'series') {
                $series = \App\Models\ProductSeries::find($validated['product_id']);
                $productName = $series ? $series->name : 'Seri Ürün';
            } else {
                $service = \App\Models\Service::find($validated['product_id']);
                $productName = $service ? $service->name : 'Hizmet';
            }
            
            // Create return item (with negative values)
            $returnItem = \App\Models\InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_service_name' => $productName,
                'description' => $validated['description'] ?? 'İade',
                'selected_color' => $validated['selected_color'] ?? null,
                'product_id' => $validated['product_id'],
                'color_variant_id' => $validated['color_variant_id'] ?? null,
                'product_type' => $validated['type'],
                'quantity' => $quantity,
                'unit' => 'Ad',
                'unit_price' => $unitPrice,
                'unit_currency' => $invoice->currency,
                'tax_rate' => $taxRate,
                'discount_rate' => $discount,
                'line_total' => -$itemTotal, // Negative for return
                'sort_order' => (($invoice->items()->max('sort_order')) ?? 0) + 1,
                'is_return' => true
            ]);
            
            \Log::info('Return item created', ['return_item_id' => $returnItem->id, 'total' => -$itemTotal]);
            
            // Reduce original item quantity (subtract returned quantity from original item)
            $remainingQuantity = $originalItem->quantity - $quantity;
            if ($remainingQuantity <= 0) {
                // If all quantity is returned, mark original item as fully returned
                $originalItem->update(['quantity' => 0]);
                \Log::info('Original item quantity set to 0 (fully returned)', [
                    'original_item_id' => $originalItem->id,
                    'returned_quantity' => $quantity
                ]);
            } else {
                // Update original item quantity
                $originalItem->update(['quantity' => $remainingQuantity]);
                // Recalculate original item line total
                $originalLineTotal = $remainingQuantity * $unitPrice;
                $originalDiscountAmount = $discount;
                $originalLineTotalAfterDiscount = max(0, $originalLineTotal - $originalDiscountAmount);
                $originalTaxAmount = $originalLineTotalAfterDiscount * ($taxRate / 100);
                $originalItemTotal = $originalLineTotalAfterDiscount + $originalTaxAmount;
                $originalItem->update(['line_total' => $originalItemTotal]);
                \Log::info('Original item quantity reduced', [
                    'original_item_id' => $originalItem->id,
                    'old_quantity' => $originalItem->quantity + $quantity,
                    'new_quantity' => $remainingQuantity,
                    'returned_quantity' => $quantity
                ]);
            }
            
            // Get old invoice total BEFORE recalculation (for customer balance update)
            $oldInvoiceTotal = $invoice->total_amount ?? 0;
            
            // Recalculate invoice totals based on all items (including returns)
            $invoice->refresh();
            $invoiceSubtotal = $invoice->items()->where('is_return', false)->sum('line_total');
            $invoiceVat = $invoice->items()->where('is_return', false)->get()->sum(function($item) {
                return $item->line_total * (($item->tax_rate ?? 0) / 100);
            });
            // Subtract return items totals
            $returnsSubtotal = $invoice->items()->where('is_return', true)->sum('line_total');
            $returnsVat = $invoice->items()->where('is_return', true)->get()->sum(function($item) {
                return abs($item->line_total) * (($item->tax_rate ?? 0) / 100);
            });
            $newSubtotal = $invoiceSubtotal + $returnsSubtotal; // returnsSubtotal is already negative
            $newVatAmount = $invoiceVat - $returnsVat; // subtract return VAT
            $newTotalAmount = $newSubtotal + $newVatAmount;
            
            $invoice->update([
                'subtotal' => max(0, $newSubtotal),
                'vat_amount' => max(0, $newVatAmount),
                'total_amount' => max(0, $newTotalAmount)
            ]);
            
            \Log::info('Invoice totals updated for return', [
                'old_total' => $oldInvoiceTotal,
                'new_total' => $newTotalAmount,
                'invoice_total_change' => $newTotalAmount - $oldInvoiceTotal,
                'return_item_total' => $itemTotal
            ]);
            
            // Add stock back
            if ($validated['type'] === 'product') {
                $product = \App\Models\Product::with('colorVariants')->find($validated['product_id']);
                if ($product) {
                    $handled = false;
                    // Prefer variant by explicit id
                    if (!empty($validated['color_variant_id'])) {
                        $colorVariant = \App\Models\ProductColorVariant::find($validated['color_variant_id']);
                        if ($colorVariant) {
                            $colorVariant->increment('stock_quantity', $quantity);
                            $handled = true;
                            \Log::info('Color variant stock increased', [
                                'color_variant_id' => $colorVariant->id,
                                'quantity' => $quantity
                            ]);
                        }
                    }
                    // If no id, try resolve by selected color name
                    if (!$handled && !empty($validated['selected_color']) && $product->colorVariants && $product->colorVariants->count() > 0) {
                        $resolved = $product->colorVariants()->where('color', $validated['selected_color'])->first();
                        if ($resolved) {
                            $resolved->increment('stock_quantity', $quantity);
                            $handled = true;
                            \Log::info('Color variant stock increased by name', [
                                'color' => $validated['selected_color'],
                                'quantity' => $quantity
                            ]);
                        }
                    }
                    // Fallback: increment product stock
                    if (!$handled) {
                        $product->increment('stock_quantity', $quantity);
                        \Log::info('Product stock increased (fallback)', [
                            'product_id' => $product->id,
                            'quantity' => $quantity
                        ]);
                    }
                }
            } elseif ($validated['type'] === 'series') {
            } elseif ($validated['type'] === 'series') {
                $series = \App\Models\ProductSeries::with('colorVariants')->find($validated['product_id']);
                if ($series) {
                    // Seri ürünlerde iade miktarı birebir alınır (çarpan uygulanmaz)
                    $unitsToChange = (int) $quantity;

                    // Prefer exact variant id; otherwise try resolving by selected_color
                    $variantId = $validated['color_variant_id'] ?? null;
                    if (!$variantId && !empty($validated['selected_color'])) {
                        $resolved = $series->colorVariants()->where('color', $validated['selected_color'])->first();
                        if ($resolved) {
                            $variantId = $resolved->id;
                        }
                    }

                    if ($variantId) {
                        $seriesColorVariant = \App\Models\ProductSeriesColorVariant::find($variantId);
                        if ($seriesColorVariant) {
                            $seriesColorVariant->increment('stock_quantity', $unitsToChange);
                            // Update parent series stock to reflect sum of variants
                            $series->refresh();
                            $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                            $series->save();
                            \Log::info('Series color variant stock increased', [
                                'series_color_variant_id' => $seriesColorVariant->id,
                                'quantity' => $unitsToChange
                            ]);
                        }
                    } else {
                        // No color variants or unable to resolve: update parent stock directly
                        $series->increment('stock_quantity', $unitsToChange);
                        // If variants exist, recompute parent from variants (this will keep totals consistent)
                        $series->refresh();
                        if ($series->colorVariants && $series->colorVariants->count() > 0) {
                            $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                            $series->save();
                        }
                        \Log::info('Series stock increased (no variant)', [
                            'series_id' => $series->id,
                            'quantity' => $unitsToChange
                        ]);
                    }
                }
            } else {
                // Final safety net: try series branch if id belongs to a series but type wasn't set properly
                $series = \App\Models\ProductSeries::with('colorVariants')->find($validated['product_id']);
                if ($series) {
                    $unitsToChange = (int) $quantity;
                    $variantId = $validated['color_variant_id'] ?? null;
                    if (!$variantId && !empty($validated['selected_color'])) {
                        $resolved = $series->colorVariants()->where('color', $validated['selected_color'])->first();
                        if ($resolved) {
                            $variantId = $resolved->id;
                        }
                    }
                    if ($variantId) {
                        $seriesColorVariant = \App\Models\ProductSeriesColorVariant::find($variantId);
                        if ($seriesColorVariant) {
                            $seriesColorVariant->increment('stock_quantity', $unitsToChange);
                            $series->refresh();
                            $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                            $series->save();
                            \Log::warning('Return: corrected type to series during stock restore', [
                                'original_type' => $validated['type'],
                                'series_id' => $series->id,
                                'series_color_variant_id' => $seriesColorVariant->id,
                                'quantity' => $unitsToChange
                            ]);
                        }
                    } else {
                        $series->increment('stock_quantity', $unitsToChange);
                        $series->refresh();
                        if ($series->colorVariants && $series->colorVariants->count() > 0) {
                            $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                            $series->save();
                        }
                        \Log::warning('Return: corrected type to series (no variant) during stock restore', [
                            'original_type' => $validated['type'],
                            'series_id' => $series->id,
                            'quantity' => $unitsToChange
                        ]);
                    }
                }
            }
            // Services don't have stock
            
            // Update customer balance based on invoice total change
            // The correct approach: Update customer balance to match the new invoice total
            // Since invoice total changed from old_total to new_total, customer balance should change by the difference
            if (!$invoice->payment_completed && $invoice->customer) {
                $customer = $invoice->customer;
                $currency = $invoice->currency;
                
                // Calculate the change in invoice total
                // This is the amount we need to adjust customer balance
                $invoiceTotalChange = $newTotalAmount - $oldInvoiceTotal;
                
                // Get current balances before update
                $currentBalance = $customer->balance ?? 0;
                $currentCurrencyBalance = match($currency) {
                    'TRY' => $customer->balance_try ?? 0,
                    'USD' => $customer->balance_usd ?? 0,
                    'EUR' => $customer->balance_eur ?? 0,
                    default => 0
                };
                
                \Log::info('Customer balance update for return', [
                    'customer_id' => $customer->id,
                    'currency' => $currency,
                    'old_invoice_total' => $oldInvoiceTotal,
                    'new_invoice_total' => $newTotalAmount,
                    'invoice_total_change' => $invoiceTotalChange,
                    'current_balance' => $currentBalance,
                    'current_currency_balance' => $currentCurrencyBalance,
                    'return_item_total' => $itemTotal,
                ]);
                
                // Update balance based on invoice total change
                // If invoice total decreased, customer owes less (balance decreases)
                // If invoice total increased, customer owes more (balance increases)
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
                
                \Log::info('Customer balance updated after return', [
                    'customer_id' => $customer->id,
                    'currency' => $currency,
                    'old_balance' => $currentBalance,
                    'new_balance' => $newBalance,
                    'old_currency_balance' => $currentCurrencyBalance,
                    'new_currency_balance' => $newCurrencyBalance,
                    'invoice_total_change' => $invoiceTotalChange,
                    'new_invoice_total' => $newTotalAmount,
                    'explanation' => $invoiceTotalChange >= 0 
                        ? 'Invoice total increased by ' . abs($invoiceTotalChange) . ', customer debt increased'
                        : 'Invoice total decreased by ' . abs($invoiceTotalChange) . ', customer debt decreased'
                ]);
            }
            
            DB::commit();
            
            \Log::info('Return item added successfully', [
                'invoice_id' => $invoice->id,
                'return_item_id' => $returnItem->id
            ]);
            
            return redirect()->route('sales.invoices.show', $invoice)
                ->with('success', 'İade başarıyla eklendi.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Add return item failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->with('error', 'İade eklenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Show print view for invoice
     */
    public function print(Invoice $invoice)
    {
        $lang = request()->get('lang', 'tr');
        $translations = $this->getInvoicePrintTranslations($lang);
        return view('sales.invoices.print', compact('invoice', 'translations', 'lang'));
    }

    // Actions removed - invoices are now directly approved

    private function getInvoicePrintTranslations(string $lang): array
    {
        $lang = strtolower($lang);
        $tr = [
            'invoice' => 'Sipariş Fişi',
            'invoice_no' => 'Fatura #',
            'invoice_date' => 'Sipariş Tarihi',
            'due_date' => 'Vade Tarihi',
            'time' => 'Saat',
            'billed_to' => 'Sipariş Fişi',
            'name' => 'Ad Soyad',
            'company' => 'Şirket',
            'address' => 'Adres',
            'phone' => 'Telefon',
            'email' => 'E-posta',
            'currency' => 'Para Birimi',
            'status' => 'Durum',
            'paid' => 'Tahsilat Yapıldı',
            'draft' => 'Taslak',
            'sent' => 'Gönderildi',
            'paid_status' => 'Ödendi',
            'overdue' => 'Vadesi Geçti',
            'cancelled' => 'İptal',
            'no' => 'Sıra',
            'product_service' => 'Ürün/Hizmet',
            'description' => 'Açıklama',
            'quantity' => 'Miktar',
            'unit_price' => 'Birim Fiyat',
            'vat' => 'KDV %',
            'discount' => 'İndirim',
            'total' => 'Toplam',
            'subtotal' => 'Ara Toplam',
            'vat_amount' => 'KDV',
            'grand_total' => 'Genel Toplam',
            'thank_you' => 'İşleminiz için teşekkür ederiz!',
            'customer_signature' => 'Müşteri İmzası',
            'authorized_signature' => 'Yetkili İmzası',
        ];
        $en = [
            'invoice' => 'Order Receipt',
            'invoice_no' => 'Invoice #',
            'invoice_date' => 'Order Date',
            'due_date' => 'Due Date',
            'time' => 'Time',
            'billed_to' => 'Order Receipt',
            'name' => 'Name',
            'company' => 'Company',
            'address' => 'Address',
            'phone' => 'Phone',
            'email' => 'Email',
            'currency' => 'Currency',
            'status' => 'Status',
            'paid' => 'Paid',
            'draft' => 'Draft',
            'sent' => 'Sent',
            'paid_status' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
            'no' => 'No',
            'product_service' => 'Product/Service',
            'description' => 'Description',
            'quantity' => 'Quantity',
            'unit_price' => 'Unit Price',
            'vat' => 'VAT %',
            'discount' => 'Discount',
            'total' => 'Total',
            'subtotal' => 'Subtotal',
            'vat_amount' => 'VAT',
            'grand_total' => 'Grand Total',
            'thank_you' => 'Thank you for your business!',
            'customer_signature' => 'Customer Signature',
            'authorized_signature' => 'Authorized Signature',
        ];
        $ar = [
            'invoice' => 'سند الطلب',
            'invoice_no' => 'رقم الفاتورة',
            'invoice_date' => 'تاريخ الطلب',
            'due_date' => 'تاريخ الاستحقاق',
            'time' => 'الوقت',
            'billed_to' => 'سند الطلب',
            'name' => 'الاسم',
            'company' => 'الشركة',
            'address' => 'العنوان',
            'phone' => 'الهاتف',
            'email' => 'البريد الإلكتروني',
            'currency' => 'العملة',
            'status' => 'الحالة',
            'paid' => 'مدفوع',
            'draft' => 'مسودة',
            'sent' => 'مرسلة',
            'paid_status' => 'مدفوع',
            'overdue' => 'متأخر',
            'cancelled' => 'ملغاة',
            'no' => 'رقم',
            'product_service' => 'المنتج/الخدمة',
            'description' => 'الوصف',
            'quantity' => 'الكمية',
            'unit_price' => 'سعر الوحدة',
            'vat' => 'ضريبة %',
            'discount' => 'خصم',
            'total' => 'الإجمالي',
            'subtotal' => 'الإجمالي الفرعي',
            'vat_amount' => 'الضريبة',
            'grand_total' => 'الإجمالي الكلي',
            'thank_you' => 'شكرًا لتعاملكم معنا!',
            'customer_signature' => 'توقيع الزبون',
            'authorized_signature' => 'توقيع المخول',
        ];
        $ru = [
            'invoice' => 'Квитанция заказа',
            'invoice_no' => 'Счёт №',
            'invoice_date' => 'Дата заказа',
            'due_date' => 'Срок оплаты',
            'time' => 'Время',
            'billed_to' => 'Квитанция заказа',
            'name' => 'Имя',
            'company' => 'Компания',
            'address' => 'Адрес',
            'phone' => 'Телефон',
            'email' => 'E-mail',
            'currency' => 'Валюта',
            'status' => 'Статус',
            'paid' => 'Оплачено',
            'draft' => 'Черновик',
            'sent' => 'Отправлено',
            'paid_status' => 'Оплачено',
            'overdue' => 'Просрочено',
            'cancelled' => 'Отменено',
            'no' => '№',
            'product_service' => 'Товар/Услуга',
            'description' => 'Описание',
            'quantity' => 'Кол-во',
            'unit_price' => 'Цена',
            'vat' => 'НДС %',
            'discount' => 'Скидка',
            'total' => 'Итого',
            'subtotal' => 'Промежуточный итог',
            'vat_amount' => 'НДС',
            'grand_total' => 'Итого к оплате',
            'thank_you' => 'Спасибо за сотрудничество!',
            'customer_signature' => 'Подпись клиента',
            'authorized_signature' => 'Подпись ответственного',
        ];
        return match($lang){
            'en' => $en,
            'ar' => $ar,
            'ru' => $ru,
            default => $tr,
        };
    }

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
            
            DB::beginTransaction();
            
            $invoices = Invoice::with('items')->whereIn('id', $ids)->get();
            $deletedCount = 0;
            
            foreach ($invoices as $invoice) {
                // Load invoice items with relationships
                $invoice->load('items');
                
                // 1. Restore stock for sold items (is_return = false)
                // 2. Deduct stock for returned items (is_return = true) - because returns added stock back
                foreach ($invoice->items as $item) {
                    if ($item->product_id && $item->quantity > 0) {
                        if ($item->product_type === 'series') {
                            $series = \App\Models\ProductSeries::with('colorVariants')->find($item->product_id);
                            if ($series) {
                                if ($item->is_return) {
                                    // Return item: deduct stock (because return added stock back)
                                    if ($item->color_variant_id) {
                                        $colorVariant = $series->colorVariants()->where('id', $item->color_variant_id)->first();
                                        if ($colorVariant) {
                                            $colorVariant->decrement('stock_quantity', $item->quantity);
                                            $series->refresh();
                                            $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                            $series->save();
                                        }
                                    } else {
                                        $series->decrement('stock_quantity', $item->quantity);
                                    }
                                } else {
                                    // Normal item: restore stock
                                    if ($item->color_variant_id) {
                                        $colorVariant = $series->colorVariants()->where('id', $item->color_variant_id)->first();
                                        if ($colorVariant) {
                                            $colorVariant->increment('stock_quantity', $item->quantity);
                                            $series->refresh();
                                            $series->stock_quantity = $series->colorVariants->sum('stock_quantity');
                                            $series->save();
                                        }
                                    } else {
                                        $series->increment('stock_quantity', $item->quantity);
                                    }
                                }
                            }
                        }
                    }
                }
                
                // 3. Update customer balance (subtract invoice amount if payment was not completed)
                if (!$invoice->payment_completed && $invoice->customer_id) {
                    $customer = \App\Models\Customer::find($invoice->customer_id);
                    if ($customer) {
                        $currency = $invoice->currency;
                        $totalAmount = $invoice->total_amount;
                        
                        // Subtract from balance based on currency
                        switch ($currency) {
                            case 'TRY':
                                $customer->decrement('balance_try', $totalAmount);
                                break;
                            case 'USD':
                                $customer->decrement('balance_usd', $totalAmount);
                                break;
                            case 'EUR':
                                $customer->decrement('balance_eur', $totalAmount);
                                break;
                        }
                        
                        // Also update the legacy balance field
                        $customer->decrement('balance', $totalAmount);
                    }
                }
                
                // 4. Delete related exchanges (if this invoice is part of an exchange)
                \App\Models\Exchange::where('original_invoice_id', $invoice->id)
                    ->orWhere('new_invoice_id', $invoice->id)
                    ->delete();
                
                // Delete the invoice
                $invoice->delete();
                $deletedCount++;
            }
            
            DB::commit();
            
            \Log::info('Bulk invoice deletion completed', [
                'deleted_count' => $deletedCount,
                'total_requested' => count($ids)
            ]);
            
            return redirect()->route('sales.invoices.index')
                ->with('success', $deletedCount . ' fatura başarıyla silindi');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Bulk delete error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Silme işlemi sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }

}
