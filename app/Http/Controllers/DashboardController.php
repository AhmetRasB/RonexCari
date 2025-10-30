<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\PurchaseInvoice;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get current account ID from session
     * Tüm kullanıcılar (admin dahil) seçili hesaba göre filtreleme yapar
     */
    private function getCurrentAccountId()
    {
        return session('current_account_id');
    }


    public function index()
    {
        // KPI'lar
        $stats = $this->getDashboardStats();
        
        // Get current account ID
        $accountId = $this->getCurrentAccountId();
        
        // Kritik stok uyarıları - Normal ürünler
        $lowStockProductsQuery = Product::whereNotNull('critical_stock')
            ->where('critical_stock', '>', 0)
            ->whereColumn('initial_stock', '<=', 'critical_stock');
            
        // Filter by account
        if ($accountId !== null) {
            $lowStockProductsQuery->where('account_id', $accountId);
        }
        
        $lowStockProducts = $lowStockProductsQuery->orderBy('initial_stock')
            ->limit(10)
            ->get(['id','name','initial_stock','critical_stock','category']);
            
        // Color variants kritik stok uyarıları
        $lowStockColorVariants = collect();
        if ($accountId) {
            $colorVariantsQuery = \App\Models\ProductColorVariant::whereHas('product', function($query) use ($accountId) {
                $query->where('account_id', $accountId);
            })
            ->whereNotNull('critical_stock')
            ->where('critical_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'critical_stock')
            ->where('is_active', true);
            
            $lowStockColorVariants = $colorVariantsQuery->with('product:id,name,category')
                ->orderBy('stock_quantity')
                ->limit(10)
                ->get();
        }
            
        // Debug: RONEX1 için kritik stok uyarılarını logla
        if ($accountId) {
            $account = \App\Models\Account::find($accountId);
            if ($account && $account->code === 'RONEX1') {
                // Tüm RONEX1 ürünlerini kontrol et
                $allRonex1Products = \App\Models\Product::where('account_id', $accountId)->get(['id', 'name', 'initial_stock', 'critical_stock', 'category']);
                
                \Log::info('RONEX1 Kritik Stok Debug', [
                    'account_id' => $accountId,
                    'account_code' => $account->code,
                    'all_products_count' => $allRonex1Products->count(),
                    'all_products' => $allRonex1Products->toArray(),
                    'low_stock_count' => $lowStockProducts->count(),
                    'low_stock_products' => $lowStockProducts->toArray(),
                    'color_variants_count' => $lowStockColorVariants->count(),
                    'color_variants' => $lowStockColorVariants->toArray()
                ]);
            }
        }
            
        // Kritik stok uyarıları - Seri ürünler
        $lowStockSeriesQuery = \App\Models\ProductSeries::whereNotNull('critical_stock')
            ->where('critical_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'critical_stock');
            
        // Filter by account
        if ($accountId !== null) {
            $lowStockSeriesQuery->where('account_id', $accountId);
        }
        
        $lowStockSeries = $lowStockSeriesQuery->orderBy('stock_quantity')
            ->limit(10)
            ->get(['id','name','stock_quantity','critical_stock','series_size','category']);
            
        // Seri ürün renk varyantları kritik stok uyarıları
        $lowStockSeriesColorVariants = collect();
        if ($accountId) {
            $seriesColorVariantsQuery = \App\Models\ProductSeriesColorVariant::whereHas('productSeries', function($query) use ($accountId) {
                $query->where('account_id', $accountId);
            })
            ->whereNotNull('critical_stock')
            ->where('critical_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'critical_stock');
            
            $lowStockSeriesColorVariants = $seriesColorVariantsQuery->with('productSeries:id,name,category')
                ->orderBy('stock_quantity')
                ->limit(10)
                ->get(['id','product_series_id','color','stock_quantity','critical_stock']);
        }

        // Yaklaşan ve vadesi geçmiş tahsilatlar (TR saatiyle bugün dahil + 7 gün ileri)
        $todayTr = Carbon::now('Europe/Istanbul')->startOfDay();
        $in7Tr = Carbon::now('Europe/Istanbul')->addDays(7)->endOfDay();
        
        $accountId = $this->getCurrentAccountId();
        
        // Satış faturaları: vadesi geçmiş TÜM faturalar + önümüzdeki 7 gün
        $dueAndOverdueSales = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->whereNotNull('due_date')
            ->where(function($q) {
                $q->where('payment_completed', false)
                  ->orWhereNull('payment_completed');
            })
            ->when(\Schema::hasColumn('invoices', 'status'), function($q) {
                $q->where(function($w) {
                    $w->whereNull('status')
                      ->orWhere('status', '!=', 'paid');
                });
            })
            ->where('due_date', '<=', $in7Tr)
            ->with('customer')
            ->orderBy('due_date')
            ->limit(50)
            ->get();

        $duePurchases = PurchaseInvoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$todayTr, $in7Tr])
            ->where('payment_completed', false)
            ->with('supplier')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Tahsilat yapılması gereken müşteriler (vadesi yaklaşan veya geçmiş ve bakiyesi olan)
        $dueOrOverdueCustomerIds = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->where(function($q) {
                $q->where('payment_completed', false)
                  ->orWhereNull('payment_completed');
            })
            ->when(\Schema::hasColumn('invoices', 'status'), function($q) {
                $q->where(function($w) {
                    $w->whereNull('status')
                      ->orWhere('status', '!=', 'paid');
                });
            })
            ->whereNotNull('due_date')
            ->where(function($q) use ($todayTr, $in7Tr) {
                $q->whereBetween('due_date', [$todayTr, $in7Tr])
                  ->orWhere('due_date', '<', $todayTr);
            })
            ->pluck('customer_id')
            ->unique()
            ->values();

        // En yakın vade tarihini müşteri bazında almak için harita oluştur
        $nearestDuePerCustomer = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->where('payment_completed', false)
            ->whereNotNull('due_date')
            ->whereIn('customer_id', $dueOrOverdueCustomerIds)
            ->select('customer_id', 'due_date')
            ->orderBy('due_date')
            ->get()
            ->groupBy('customer_id')
            ->map(function($group) {
                return $group->min('due_date');
            });

        $customersToCollect = \App\Models\Customer::whereIn('id', $dueOrOverdueCustomerIds)
            ->where(function($q) {
                $q->where('balance_try', '>', 0)
                  ->orWhere('balance_usd', '>', 0)
                  ->orWhere('balance_eur', '>', 0);
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function($customer) use ($nearestDuePerCustomer) {
                $customer->nearest_due_date = $nearestDuePerCustomer->get($customer->id);
                return $customer;
            });

        // Son faturalar
        $recentInvoices = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // En çok satan ürünler - invoice_items'da product_service_name ile eşleştirme
        $topProductsQuery = \DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->when($accountId !== null, function($query) use ($accountId) {
                return $query->where('invoices.account_id', $accountId);
            })
            ->whereMonth('invoices.created_at', Carbon::now()->month);
            
        // Filter by account - join with products table to filter by account_id
        if ($accountId !== null) {
            $topProductsQuery->join('products', function($join) use ($accountId) {
                $join->on('invoice_items.product_service_name', '=', 'products.name')
                     ->where('products.account_id', $accountId);
            });
        }
        
        $topProductsQuery = $topProductsQuery->select('invoice_items.product_service_name')
            ->selectRaw('SUM(invoice_items.quantity) as total_sold')
            ->groupBy('invoice_items.product_service_name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        $topProducts = $topProductsQuery->map(function($item) {
            // Product tablosundan eşleşen ürünü bulmaya çalış
            $product = Product::where('name', 'LIKE', '%' . $item->product_service_name . '%')->first();
            if (!$product) {
                // Eşleşen ürün yoksa genel bir obje oluştur
                $product = (object) [
                    'id' => null,
                    'name' => $item->product_service_name,
                    'sale_price' => 0
                ];
            }
            $product->invoice_items_count = $item->total_sold;
            return $product;
        });

        return view('dashboard.index', compact(
            'stats', 'lowStockProducts', 'lowStockColorVariants', 'lowStockSeries', 'lowStockSeriesColorVariants', 'dueAndOverdueSales', 'duePurchases', 
            'recentInvoices', 'topProducts', 'customersToCollect'
        ));
    }

    private function getDashboardStats()
    {
        $currentMonth = Carbon::now()->month;
        $previousMonth = Carbon::now()->subMonth()->month;
        $accountId = $this->getCurrentAccountId();
        
        
        // Bu ay satışlar - Para birimlerine göre ayrı
        $thisMonthSalesTRY = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->whereMonth('created_at', $currentMonth)
            ->where('currency', 'TRY')
            ->sum('total_amount');
            
        $thisMonthSalesUSD = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->whereMonth('created_at', $currentMonth)
            ->where('currency', 'USD')
            ->sum('total_amount');
            
        $thisMonthSalesEUR = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->whereMonth('created_at', $currentMonth)
            ->where('currency', 'EUR')
            ->sum('total_amount');
            
        // Geçen ay satışlar - Para birimlerine göre ayrı
        $lastMonthSalesTRY = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->whereMonth('created_at', $previousMonth)
            ->where('currency', 'TRY')
            ->sum('total_amount');
            
        $lastMonthSalesUSD = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->whereMonth('created_at', $previousMonth)
            ->where('currency', 'USD')
            ->sum('total_amount');
            
        $lastMonthSalesEUR = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->whereMonth('created_at', $previousMonth)
            ->where('currency', 'EUR')
            ->sum('total_amount');
            
        // Bu ay alışlar - Para birimlerine göre ayrı
        $thisMonthPurchasesTRY = PurchaseInvoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->whereMonth('created_at', $currentMonth)
            ->where('currency', 'TRY')
            ->sum('total_amount');
            
        $thisMonthPurchasesUSD = PurchaseInvoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->whereMonth('created_at', $currentMonth)
            ->where('currency', 'USD')
            ->sum('total_amount');
            
        $thisMonthPurchasesEUR = PurchaseInvoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->whereMonth('created_at', $currentMonth)
            ->where('currency', 'EUR')
            ->sum('total_amount');
            
        // Toplam müşteri sayısı - Hesap bazında filtreleme yok, tüm müşteriler
        $totalCustomers = \App\Models\Customer::count();
        
        // Bu ay yeni müşteriler - Hesap bazında filtreleme yok, tüm müşteriler
        $newCustomers = \App\Models\Customer::whereMonth('created_at', $currentMonth)->count();
        
        // Toplam ürün sayısı - Account bazında
        $totalProductsQuery = Product::query();
        if ($accountId !== null) {
            $totalProductsQuery->where('account_id', $accountId);
        }
        $totalProducts = $totalProductsQuery->count();
        
        // Kritik stok uyarı sayısı - Account bazında (normal ürünler + color variants)
        $criticalStockQuery = Product::whereNotNull('critical_stock')
            ->where('critical_stock', '>', 0)
            ->whereColumn('initial_stock', '<=', 'critical_stock');
        if ($accountId !== null) {
            $criticalStockQuery->where('account_id', $accountId);
        }
        $criticalStockCount = $criticalStockQuery->count();
        
        // Color variants kritik stok sayısını da ekle
        if ($accountId !== null) {
            $colorVariantsCriticalCount = \App\Models\ProductColorVariant::whereHas('product', function($query) use ($accountId) {
                $query->where('account_id', $accountId);
            })
            ->whereNotNull('critical_stock')
            ->where('critical_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'critical_stock')
            ->where('is_active', true)
            ->count();
            
            $criticalStockCount += $colorVariantsCriticalCount;
        }
        
        // Seri ürünler kritik stok sayısını da ekle
        $seriesCriticalCount = \App\Models\ProductSeries::whereNotNull('critical_stock')
            ->where('critical_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'critical_stock');
        if ($accountId !== null) {
            $seriesCriticalCount->where('account_id', $accountId);
        }
        $criticalStockCount += $seriesCriticalCount->count();
        
        // Seri ürün renk varyantları kritik stok sayısını da ekle
        if ($accountId !== null) {
            $seriesColorVariantsCriticalCount = \App\Models\ProductSeriesColorVariant::whereHas('productSeries', function($query) use ($accountId) {
                $query->where('account_id', $accountId);
            })
            ->whereNotNull('critical_stock')
            ->where('critical_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'critical_stock')
            ->where('is_active', true)
            ->count();
            
            $criticalStockCount += $seriesColorVariantsCriticalCount;
        }
            
        // Ödenmemiş faturalar
        $unpaidInvoices = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->where('payment_completed', false)
            ->sum('total_amount');
            
        // Vadesi geçmiş faturalar
        $overdueInvoices = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })
            ->where('payment_completed', false)
            ->where('due_date', '<', Carbon::today())
            ->count();

        return [
            // Bu ay satışlar - Para birimlerine göre ayrı
            'thisMonthSalesTRY' => $thisMonthSalesTRY,
            'thisMonthSalesUSD' => $thisMonthSalesUSD,
            'thisMonthSalesEUR' => $thisMonthSalesEUR,
            
            // Geçen ay satışlar - Para birimlerine göre ayrı
            'lastMonthSalesTRY' => $lastMonthSalesTRY,
            'lastMonthSalesUSD' => $lastMonthSalesUSD,
            'lastMonthSalesEUR' => $lastMonthSalesEUR,
            
            // Bu ay alışlar - Para birimlerine göre ayrı
            'thisMonthPurchasesTRY' => $thisMonthPurchasesTRY,
            'thisMonthPurchasesUSD' => $thisMonthPurchasesUSD,
            'thisMonthPurchasesEUR' => $thisMonthPurchasesEUR,
            
            // Büyüme oranları
            'salesGrowthTRY' => $lastMonthSalesTRY > 0 ? (($thisMonthSalesTRY - $lastMonthSalesTRY) / $lastMonthSalesTRY) * 100 : 0,
            'salesGrowthUSD' => $lastMonthSalesUSD > 0 ? (($thisMonthSalesUSD - $lastMonthSalesUSD) / $lastMonthSalesUSD) * 100 : 0,
            'salesGrowthEUR' => $lastMonthSalesEUR > 0 ? (($thisMonthSalesEUR - $lastMonthSalesEUR) / $lastMonthSalesEUR) * 100 : 0,
            
            'totalCustomers' => $totalCustomers,
            'newCustomers' => $newCustomers,
            'totalProducts' => $totalProducts,
            'criticalStockCount' => $criticalStockCount,
            'unpaidInvoices' => $unpaidInvoices,
            'overdueInvoices' => $overdueInvoices,
        ];
    }
    
    public function index2()
    {
        return view('dashboard/index2');
    }
    
    public function index3()
    {
        return view('dashboard/index3');
    }
    
    public function index4()
    {
        return view('dashboard/index4');
    }
    
    public function index5()
    {
        return view('dashboard/index5');
    }
    
    public function index6()
    {
        return view('dashboard/index6');
    }
    
    public function index7()
    {
        return view('dashboard/index7');
    }
    
    public function index8()
    {
        return view('dashboard/index8');
    }
    
    public function index9()
    {
        return view('dashboard/index9');
    }
    
    public function index10()
    {
        return view('dashboard/index10');
    }

    
}
