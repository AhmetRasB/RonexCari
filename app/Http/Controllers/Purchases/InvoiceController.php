<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Supplier;
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
            $invoices = PurchaseInvoice::with(['supplier', 'account', 'user'])
                ->latest()
                ->paginate(10);
        } else {
            $invoices = PurchaseInvoice::with(['supplier', 'account', 'user'])
                ->where('account_id', $currentAccountId)
                ->latest()
                ->paginate(10);
        }

        // Her fatura için ödeme kontrolü yap (şimdilik devre dışı - payments tablosu ile entegre edilecek)
        foreach ($invoices as $invoice) {
            // TODO: Implement proper payment tracking with payments table
            $invoice->payments_amount = 0;
            $invoice->has_payments = false;
        }

        return view('purchases.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
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
            ->get();

        return view('purchases.invoices.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Purchase Invoice store method called', [
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
            'supplier_id' => $request->input('supplier_id'),
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
            // Generate robust, unique invoice number for purchases
            $prefix = 'ALI-' . date('Y') . '-';
            $lastNumber = PurchaseInvoice::where('invoice_number', 'like', $prefix . '%')
                ->orderByDesc('invoice_number')
                ->value('invoice_number');
            $sequence = 1;
            if ($lastNumber) {
                $sequence = (int) substr($lastNumber, -6) + 1;
            }
            $invoiceNumber = $prefix . str_pad($sequence, 6, '0', STR_PAD_LEFT);
            while (PurchaseInvoice::where('invoice_number', $invoiceNumber)->exists()) {
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

            // Create invoice
            $invoice = PurchaseInvoice::create([
                'account_id' => $accountId,
                'user_id' => $userId,
                'invoice_number' => $invoiceNumber,
                'supplier_id' => $validated['supplier_id'],
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
                $unitCurrency = $item['unit_currency'] ?? $validated['currency'];

                $lineTotal = $quantity * $unitPrice;
                $discountAmount = $lineTotal * ($discountRate / 100);
                $lineTotalAfterDiscount = $lineTotal - $discountAmount;

                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_service_name' => $item['product_service_name'],
                    'description' => $item['description'] ?? null,
                    'selected_color' => $item['selected_color'] ?? null,
                    'product_id' => $item['product_id'] ?? null,
                    'product_type' => $item['type'] ?? 'product',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice, // Already converted by JavaScript
                    'unit_currency' => $unitCurrency,
                    'tax_rate' => $taxRate,
                    'discount_rate' => $discountRate,
                    'line_total' => $lineTotalAfterDiscount,
                    'sort_order' => $index
                ]);
            }

            // Stok artırma işlemi
            foreach ($validated['items'] as $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $productId = $item['product_id'] ?? null;
                $type = $item['type'] ?? 'product';
                $colorVariantId = $item['color_variant_id'] ?? null;

                if ($productId && $quantity > 0) {
                    if ($type === 'product') {
                        // Normal ürün stok artırma
                        $product = \App\Models\Product::find($productId);
                        if ($product) {
                            // Color variant seçilmişse (öncelik ID), o rengin stokunu artır
                            if ($colorVariantId) {
                                $colorVariant = $product->colorVariants()->where('id', $colorVariantId)->first();
                                if ($colorVariant) {
                                    $colorVariant->increment('stock_quantity', $quantity);
                                }
                            } else if (!empty($item['selected_color']) && $product->colorVariants->count() > 0) {
                                $colorVariant = $product->colorVariants()->where('color', $item['selected_color'])->first();
                                if ($colorVariant) {
                                    $colorVariant->increment('stock_quantity', $quantity);
                                }
                            } else {
                                // Color variant yoksa ana ürünün stokunu artır
                                $product->increment('stock_quantity', $quantity);
                            }

                            // Ana ürünün initial_stock'unu da güncelle
                            $product->increment('initial_stock', $quantity);
                        }
                    } elseif ($type === 'series') {
                        // Seri ürün stok artırma
                        $series = \App\Models\ProductSeries::find($productId);
                        if ($series) {
                            $series->increment('stock_quantity', $quantity);
                        }
                    }
                    // Service'ler için stok artırma yok
                }
            }

            // Handle supplier balance if payment is not completed
            if (!$validated['payment_completed']) {
                $supplier = \App\Models\Supplier::find($validated['supplier_id']);
                if ($supplier) {
                    $currency = $validated['currency'];

                    // Update balance based on currency
                    switch ($currency) {
                        case 'TRY':
                            $supplier->increment('balance_try', $totalAmount);
                            break;
                        case 'USD':
                            $supplier->increment('balance_usd', $totalAmount);
                            break;
                        case 'EUR':
                            $supplier->increment('balance_eur', $totalAmount);
                            break;
                    }

                    // Also update the legacy balance field for backward compatibility
                    $supplier->increment('balance', $totalAmount);
                }
            }

            DB::commit();

            \Log::info('Purchase Invoice stored successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoiceNumber,
                'total_amount' => $totalAmount
            ]);

            return redirect()->route('purchases.invoices.show', $invoice)
                ->with('success', 'Alış faturası başarıyla oluşturuldu.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase Invoice store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return back()->withInput()
                ->with('error', 'Alış faturası oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'items']);
        return view('purchases.invoices.show', compact('invoice'));
    }

    /**
     * Display the invoice preview for printing.
     */
    public function preview(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'items']);
        return view('purchases.invoices.preview', compact('invoice'));
    }

    /**
     * Print the invoice.
     */
    public function print(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'items']);
        return view('purchases.invoices.print', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'items']);
        $suppliers = Supplier::where('is_active', true)->get();
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
            ->get();

        return view('purchases.invoices.edit', compact('invoice', 'suppliers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseInvoice $invoice)
    {
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'invoice_date' => 'required|date',
                'invoice_time' => 'required|date_format:H:i',
                'due_date' => 'required|date',
                'currency' => 'required|string|in:TRY,USD,EUR',
                'vat_status' => 'required|in:included,excluded',
                'description' => 'nullable|string|max:1000',
                'payment_completed' => 'boolean',
                'items' => 'required|array|min:1|max:50',
                'items.*.product_service_name' => 'required|string|max:255',
                'items.*.description' => 'nullable|string|max:500',
                'items.*.quantity' => 'required|numeric|min:0.01|max:999999.99',
                'items.*.unit_price' => 'required|numeric|min:0|max:999999.99',
                'items.*.unit_currency' => 'required|string|in:TRY,USD,EUR',
                'items.*.tax_rate' => 'required|numeric|min:0|max:100',
                'items.*.discount_rate' => 'required|numeric|min:0|max:100',
            ]);

            DB::beginTransaction();

            // Update invoice
            $invoice->update([
                'supplier_id' => $validated['supplier_id'],
                'invoice_date' => $validated['invoice_date'],
                'invoice_time' => $validated['invoice_time'],
                'due_date' => $validated['due_date'],
                'currency' => $validated['currency'],
                'vat_status' => $validated['vat_status'],
                'description' => $validated['description'],
                'payment_completed' => $validated['payment_completed'] ?? false,
            ]);

            // Delete existing items
            $invoice->items()->delete();

            // Process new items
            $subtotal = 0;
            $totalVat = 0;

            foreach ($validated['items'] as $index => $item) {
                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $taxRate = (float) $item['tax_rate'];
                $discountRate = (float) $item['discount_rate'];

                // Calculate line total
                $lineSubtotal = $quantity * $unitPrice;
                $discountAmount = $lineSubtotal * ($discountRate / 100);
                $lineSubtotalAfterDiscount = $lineSubtotal - $discountAmount;

                $vatAmount = $lineSubtotalAfterDiscount * ($taxRate / 100);
                $lineTotal = $lineSubtotalAfterDiscount + $vatAmount;

                $subtotal += $lineSubtotalAfterDiscount;
                $totalVat += $vatAmount;

                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_service_name' => $item['product_service_name'],
                    'description' => $item['description'],
                    'selected_color' => $item['selected_color'] ?? null,
                    'product_id' => $item['product_id'] ?? null,
                    'product_type' => $item['type'] ?? 'product',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_currency' => $item['unit_currency'],
                    'tax_rate' => $taxRate,
                    'discount_rate' => $discountRate,
                    'line_total' => $lineTotal,
                    'sort_order' => $index + 1
                ]);
            }

            // Update invoice totals
            $invoice->update([
                'subtotal' => $subtotal,
                'vat_amount' => $totalVat,
                'total_amount' => $subtotal + $totalVat
            ]);

            DB::commit();

            return redirect()->route('purchases.invoices.show', $invoice)
                ->with('success', 'Alış faturası başarıyla güncellendi.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Purchase Invoice update failed', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return back()->withInput()
                ->with('error', 'Alış faturası güncellenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseInvoice $invoice)
    {
        try {
            $invoiceNumber = $invoice->invoice_number;
            $invoice->delete();

            return redirect()->route('purchases.invoices.index')
                ->with('success', "Alış faturası {$invoiceNumber} başarıyla silindi.");

        } catch (\Exception $e) {
            \Log::error('Purchase Invoice delete failed', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);

            return back()->with('error', 'Alış faturası silinirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Search suppliers for AJAX requests
     */
    public function searchSuppliers(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suppliers = Supplier::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('company_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'company_name', 'email', 'phone')
            ->limit(10)
            ->get();

        return response()->json($suppliers);
    }

    /**
     * Mark invoice as paid
     */
    public function markPaid(PurchaseInvoice $invoice)
    {
        try {
            $invoice->payment_completed = true;
            // If status column exists on model/table, set to paid as well
            if (\Schema::hasColumn('purchase_invoices', 'status')) {
                $invoice->status = 'paid';
            }
            $invoice->save();

            return redirect()
                ->back()
                ->with('success', 'Alış faturası ödemesi tamamlandı olarak işaretlendi.');
        } catch (\Throwable $e) {
            \Log::error('markPaid failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()
                ->back()
                ->with('error', 'Ödeme işaretlenemedi: ' . $e->getMessage());
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

    /**
     * Search products for AJAX requests
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $currentAccountId = session('current_account_id');
        $products = Product::with('colorVariants')
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
                  ->orWhere('product_code', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('size', 'like', "%{$query}%")
                  ->orWhere('color', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%")
                  ->orWhere('supplier_code', 'like', "%{$query}%")
                  ->orWhere('gtip_code', 'like', "%{$query}%")
                  ->orWhere('class_code', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'product_code', 'category', 'brand', 'size', 'color', 'sale_price', 'purchase_price', 'vat_rate', 'currency')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                $hasColorVariants = $product->colorVariants->count() > 0;

                return [
                    'id' => 'product_' . $product->id,
                    'name' => $product->name,
                    'product_code' => $product->product_code,
                    'category' => $product->category,
                    'brand' => $product->brand,
                    'size' => $product->size,
                    'color' => $product->color,
                    'price' => $product->purchase_price ?: $product->sale_price,
                    'purchase_price' => $product->purchase_price,
                    'vat_rate' => $product->vat_rate,
                    'currency' => $product->currency,
                    'type' => 'product',
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

        return response()->json($products);
    }




    // Actions removed - invoices are now directly approved

    public function bulkDelete(Request $request)
    {
        try {
            $ids = json_decode($request->input('ids'), true);
            if (empty($ids) || !is_array($ids)) {
                return redirect()->back()->with('error', 'Geçersiz seçim');
            }
            $deletedCount = \App\Models\PurchaseInvoice::whereIn('id', $ids)->delete();
            return redirect()->route('purchases.invoices.index')->with('success', $deletedCount . ' alış faturası başarıyla silindi');
        } catch (\Exception $e) {
            \Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Silme işlemi sırasında bir hata oluştu');
        }
    }
}
