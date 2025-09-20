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
        $products = Product::where('is_active', true)->get();
        
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
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_service_name' => $item['product_service_name'],
                    'description' => $item['description'] ?? null,
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
                
                if ($productId && $quantity > 0) {
                    if ($type === 'product') {
                        // Normal ürün stok düşümü
                        $product = \App\Models\Product::find($productId);
                        if ($product) {
                            if ($product->stock_quantity < $quantity) {
                                throw new \Exception("Yetersiz stok! {$product->name} için stok: {$product->stock_quantity}, istenen: {$quantity}");
                            }
                            $product->decrement('stock_quantity', $quantity);
                        }
                    } elseif ($type === 'series') {
                        // Seri ürün stok düşümü (1 seri = 1 adet)
                        $series = \App\Models\ProductSeries::find($productId);
                        if ($series) {
                            if ($series->stock_quantity < $quantity) {
                                throw new \Exception("Yetersiz seri stoku! {$series->name} için stok: {$series->stock_quantity} seri, istenen: {$quantity} seri");
                            }
                            $series->decrement('stock_quantity', $quantity);
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
        return view('sales.invoices.show', compact('invoice'));
    }

    /**
     * Display the invoice preview for printing.
     */
    public function preview(Invoice $invoice)
    {
        $invoice->load(['customer', 'items']);
        return view('sales.invoices.preview', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $customers = Customer::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $invoice->load('items');
        
        return view('sales.invoices.edit', compact('invoice', 'customers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Similar to store method but for updating
        // Implementation would be similar to store with update logic
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
        
        // Search in products - search in all relevant fields
        $products = \App\Models\Product::where('is_active', true)
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
                return [
                    'id' => 'product_' . $product->id,
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
                    'stock_quantity' => $product->stock_quantity
                ];
            });
        
        // Search in product series - search in all relevant fields
        $productSeries = \App\Models\ProductSeries::with('seriesItems')
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'sku', 'category', 'brand', 'price', 'cost', 'series_size', 'stock_quantity')
            ->limit(10)
            ->get()
            ->map(function ($series) {
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
                    'sizes' => $series->seriesItems->pluck('size')->toArray()
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
}
