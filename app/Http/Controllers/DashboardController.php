<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\PurchaseInvoice;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // KPI'lar
        $stats = $this->getDashboardStats();
        
        // Kritik stok uyarıları
        $lowStockProducts = Product::whereNotNull('critical_stock')
            ->where('critical_stock', '>', 0)
            ->whereColumn('initial_stock', '<=', 'critical_stock')
            ->orderBy('initial_stock')
            ->limit(10)
            ->get(['id','name','initial_stock','critical_stock']);

        // Yaklaşan vadeli tahsilatlar (7 gün içinde)
        $now = Carbon::today();
        $in7 = Carbon::today()->addDays(7);
        
        $dueSales = Invoice::where('status', 'approved')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$now, $in7])
            ->where('payment_completed', false)
            ->with('customer')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $duePurchases = PurchaseInvoice::where('status', 'approved')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$now, $in7])
            ->where('payment_completed', false)
            ->with('supplier')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Son faturalar
        $recentInvoices = Invoice::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // En çok satan ürünler - invoice_items'da product_service_name ile eşleştirme
        $topProductsQuery = \DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereMonth('invoices.created_at', Carbon::now()->month)
            ->where('invoices.status', 'approved')
            ->select('invoice_items.product_service_name')
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
            'stats', 'lowStockProducts', 'dueSales', 'duePurchases', 
            'recentInvoices', 'topProducts'
        ));
    }

    private function getDashboardStats()
    {
        $currentMonth = Carbon::now()->month;
        $previousMonth = Carbon::now()->subMonth()->month;
        
        // Bu ay satışlar - Para birimlerine göre ayrı
        $thisMonthSalesTRY = Invoice::whereMonth('created_at', $currentMonth)
            ->where('status', 'approved')
            ->where('currency', 'TRY')
            ->sum('total_amount');
            
        $thisMonthSalesUSD = Invoice::whereMonth('created_at', $currentMonth)
            ->where('status', 'approved')
            ->where('currency', 'USD')
            ->sum('total_amount');
            
        $thisMonthSalesEUR = Invoice::whereMonth('created_at', $currentMonth)
            ->where('status', 'approved')
            ->where('currency', 'EUR')
            ->sum('total_amount');
            
        // Geçen ay satışlar - Para birimlerine göre ayrı
        $lastMonthSalesTRY = Invoice::whereMonth('created_at', $previousMonth)
            ->where('status', 'approved')
            ->where('currency', 'TRY')
            ->sum('total_amount');
            
        $lastMonthSalesUSD = Invoice::whereMonth('created_at', $previousMonth)
            ->where('status', 'approved')
            ->where('currency', 'USD')
            ->sum('total_amount');
            
        $lastMonthSalesEUR = Invoice::whereMonth('created_at', $previousMonth)
            ->where('status', 'approved')
            ->where('currency', 'EUR')
            ->sum('total_amount');
            
        // Bu ay alışlar - Para birimlerine göre ayrı
        $thisMonthPurchasesTRY = PurchaseInvoice::whereMonth('created_at', $currentMonth)
            ->where('status', 'approved')
            ->where('currency', 'TRY')
            ->sum('total_amount');
            
        $thisMonthPurchasesUSD = PurchaseInvoice::whereMonth('created_at', $currentMonth)
            ->where('status', 'approved')
            ->where('currency', 'USD')
            ->sum('total_amount');
            
        $thisMonthPurchasesEUR = PurchaseInvoice::whereMonth('created_at', $currentMonth)
            ->where('status', 'approved')
            ->where('currency', 'EUR')
            ->sum('total_amount');
            
        // Toplam müşteri sayısı
        $totalCustomers = \App\Models\Customer::count();
        
        // Bu ay yeni müşteriler
        $newCustomers = \App\Models\Customer::whereMonth('created_at', $currentMonth)->count();
        
        // Toplam ürün sayısı
        $totalProducts = Product::count();
        
        // Kritik stok uyarı sayısı
        $criticalStockCount = Product::whereNotNull('critical_stock')
            ->where('critical_stock', '>', 0)
            ->whereColumn('initial_stock', '<=', 'critical_stock')
            ->count();
            
        // Ödenmemiş faturalar
        $unpaidInvoices = Invoice::where('status', 'approved')
            ->where('payment_completed', false)
            ->sum('total_amount');
            
        // Vadesi geçmiş faturalar
        $overdueInvoices = Invoice::where('status', 'approved')
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
