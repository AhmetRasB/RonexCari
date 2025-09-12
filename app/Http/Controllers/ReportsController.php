<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PurchaseInvoice;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Expense;
use App\Services\CurrencyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index()
    {
        // Define time periods
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOf6Months = Carbon::now()->subMonths(6)->startOfMonth();
        $startOfYear = Carbon::now()->startOfYear();

        // Clear cache to get fresh data every time
        $this->currencyService->clearCache();
        
        // Get current exchange rates (fresh)
        $exchangeRates = $this->currencyService->getExchangeRates();
        $exchangeRates['TRY'] = 1; // Add TRY as base currency
        
        // Get test rates for all API versions
        $testRates = $this->currencyService->getTestRates();

        // === SALES ANALYTICS ===
        
        // Sales summary for different periods - convert all to TRY equivalent (ONLY APPROVED)
        $salesToday = $this->calculateTotalInTRY(
            Invoice::where('status', 'approved')->whereDate('created_at', $today)->get(['total_amount', 'currency']),
            $exchangeRates
        );
        $salesThisWeek = $this->calculateTotalInTRY(
            Invoice::where('status', 'approved')->whereBetween('created_at', [$startOfWeek, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );
        $salesThisMonth = $this->calculateTotalInTRY(
            Invoice::where('status', 'approved')->whereBetween('created_at', [$startOfMonth, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );
        $salesLast6Months = $this->calculateTotalInTRY(
            Invoice::where('status', 'approved')->whereBetween('created_at', [$startOf6Months, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );

        // Sales by currency for different periods (ONLY APPROVED)
        $salesTodayByCurrency = $this->getSalesByCurrency(
            Invoice::where('status', 'approved')->whereDate('created_at', $today)->get(['total_amount', 'currency'])
        );
        $salesThisWeekByCurrency = $this->getSalesByCurrency(
            Invoice::where('status', 'approved')->whereBetween('created_at', [$startOfWeek, Carbon::now()])->get(['total_amount', 'currency'])
        );
        $salesThisMonthByCurrency = $this->getSalesByCurrency(
            Invoice::where('status', 'approved')->whereBetween('created_at', [$startOfMonth, Carbon::now()])->get(['total_amount', 'currency'])
        );
        $salesLast6MonthsByCurrency = $this->getSalesByCurrency(
            Invoice::where('status', 'approved')->whereBetween('created_at', [$startOf6Months, Carbon::now()])->get(['total_amount', 'currency'])
        );
        
        // Sales count for different periods (ONLY APPROVED)
        $salesCountToday = Invoice::where('status', 'approved')->whereDate('created_at', $today)->count();
        $salesCountWeek = Invoice::where('status', 'approved')->whereBetween('created_at', [$startOfWeek, Carbon::now()])->count();
        $salesCountMonth = Invoice::where('status', 'approved')->whereBetween('created_at', [$startOfMonth, Carbon::now()])->count();
        $salesCount6Months = Invoice::where('status', 'approved')->whereBetween('created_at', [$startOf6Months, Carbon::now()])->count();

        // === TOP 10 MOST SOLD PRODUCTS (ONLY FROM APPROVED INVOICES) ===
        $topSellingProducts = InvoiceItem::select('product_service_name')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('SUM(line_total) as total_revenue')
            ->selectRaw('COUNT(*) as sale_count')
            ->whereHas('invoice', function($query) {
                $query->where('status', 'approved');
            })
            ->groupBy('product_service_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // === PROFIT/LOSS ANALYSIS ===
        
        // Monthly profit/loss calculation - convert all to TRY equivalent (ONLY APPROVED)
        $monthlyRevenue = $this->calculateTotalInTRY(
            Invoice::where('status', 'approved')->whereBetween('created_at', [$startOfMonth, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );
        
        $monthlyPurchases = $this->calculateTotalInTRY(
            PurchaseInvoice::where('status', 'approved')->whereBetween('created_at', [$startOfMonth, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );
        
        $monthlyExpenses = Expense::whereBetween('expense_date', [$startOfMonth, Carbon::now()])
            ->sum('amount'); // Expenses are always in TRY
        
        $monthlyProfit = $monthlyRevenue - $monthlyPurchases - $monthlyExpenses;
        
        // 6 months profit/loss calculation - convert all to TRY equivalent (ONLY APPROVED)
        $sixMonthRevenue = $this->calculateTotalInTRY(
            Invoice::where('status', 'approved')->whereBetween('created_at', [$startOf6Months, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );
        
        $sixMonthPurchases = $this->calculateTotalInTRY(
            PurchaseInvoice::where('status', 'approved')->whereBetween('created_at', [$startOf6Months, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );
        
        $sixMonthExpenses = Expense::whereBetween('expense_date', [$startOf6Months, Carbon::now()])
            ->sum('amount'); // Expenses are always in TRY
        
        $sixMonthProfit = $sixMonthRevenue - $sixMonthPurchases - $sixMonthExpenses;

        // === FINANCIAL SUMMARY ===
        
        // Collections summary - convert all to TRY equivalent
        $collectionsThisMonth = $this->calculateTotalInTRY(
            Collection::whereBetween('transaction_date', [$startOfMonth, Carbon::now()])->get(['amount as total_amount', 'currency']),
            $exchangeRates
        );
        $unpaidInvoicesTotal = $this->calculateTotalInTRY(
            Invoice::where('status', 'approved')->where('payment_completed', false)->get(['total_amount', 'currency']),
            $exchangeRates
        );

        // Collections by currency
        $collectionsThisMonthByCurrency = $this->getSalesByCurrency(
            Collection::whereBetween('transaction_date', [$startOfMonth, Carbon::now()])->get(['amount as total_amount', 'currency'])
        );
        
        // Customer and supplier balances
        $customerDebtTry = Customer::sum('balance_try');
        $customerDebtUsd = Customer::sum('balance_usd');
        $customerDebtEur = Customer::sum('balance_eur');
        
        $supplierDebtTry = PurchaseInvoice::where('status', 'approved')->where('currency', 'TRY')->where('payment_completed', false)->sum('total_amount');
        $supplierDebtUsd = PurchaseInvoice::where('status', 'approved')->where('currency', 'USD')->where('payment_completed', false)->sum('total_amount');
        $supplierDebtEur = PurchaseInvoice::where('status', 'approved')->where('currency', 'EUR')->where('payment_completed', false)->sum('total_amount');

        // === CHARTS DATA ===
        
        // Monthly sales chart (last 12 months)
        $monthlySalesData = [];
        $monthlyLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $monthlySalesData[] = Invoice::where('status', 'approved')->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_amount');
        }
        
        // Weekly sales chart (last 12 weeks)
        $weeklySalesData = [];
        $weeklyLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            $weeklyLabels[] = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');
            $weeklySalesData[] = Invoice::where('status', 'approved')->whereBetween('created_at', [$weekStart, $weekEnd])->sum('total_amount');
        }

        // Revenue by currency (this month)
        $monthlyRevenueByCurrency = $salesThisMonthByCurrency;

        // === TOP CUSTOMERS ===
        $topCustomers = Invoice::select('customer_id')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->with('customer:id,name,company_name')
            ->groupBy('customer_id')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        // === UPCOMING DUE INVOICES ===
        $upcomingDueInvoices = Invoice::where('payment_completed', false)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [Carbon::now(), Carbon::now()->addDays(30)])
            ->with('customer:id,name,company_name')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // === EXPENSE ANALYSIS ===
        $monthlyExpensesByCategory = Expense::whereBetween('expense_date', [$startOfMonth, Carbon::now()])
            ->selectRaw('name, SUM(amount) as total_amount')
            ->groupBy('name')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        return view('reports.index', compact(
            // Sales data
            'salesToday', 'salesThisWeek', 'salesThisMonth', 'salesLast6Months',
            'salesCountToday', 'salesCountWeek', 'salesCountMonth', 'salesCount6Months',
            
            // Sales by currency
            'salesTodayByCurrency', 'salesThisWeekByCurrency', 'salesThisMonthByCurrency', 'salesLast6MonthsByCurrency',
            
            // Products data
            'topSellingProducts',
            
            // Profit/Loss data
            'monthlyRevenue', 'monthlyPurchases', 'monthlyExpenses', 'monthlyProfit',
            'sixMonthRevenue', 'sixMonthPurchases', 'sixMonthExpenses', 'sixMonthProfit',
            
            // Financial data
            'collectionsThisMonth', 'collectionsThisMonthByCurrency', 'unpaidInvoicesTotal',
            'customerDebtTry', 'customerDebtUsd', 'customerDebtEur',
            'supplierDebtTry', 'supplierDebtUsd', 'supplierDebtEur',
            
            // Charts data
            'monthlySalesData', 'monthlyLabels',
            'weeklySalesData', 'weeklyLabels',
            'monthlyRevenueByCurrency',
            
            // Additional data
            'topCustomers', 'upcomingDueInvoices', 'monthlyExpensesByCategory',
            
            // Exchange rates
            'exchangeRates', 'testRates'
        ));
    }

    /**
     * Calculate total amount in TRY from mixed currency collection
     */
    private function calculateTotalInTRY($items, $exchangeRates)
    {
        $total = 0;
        foreach ($items as $item) {
            $amount = $item->total_amount;
            $currency = $item->currency ?? 'TRY';
            
            if ($currency === 'TRY') {
                $total += $amount;
            } else {
                $rate = $exchangeRates[$currency] ?? 1;
                $total += $amount * $rate;
            }
        }
        return $total;
    }

    /**
     * Get sales grouped by currency
     */
    private function getSalesByCurrency($items)
    {
        $byCurrency = ['TRY' => 0, 'USD' => 0, 'EUR' => 0];
        
        foreach ($items as $item) {
            $currency = $item->currency ?? 'TRY';
            $amount = $item->total_amount ?? 0;
            
            if (isset($byCurrency[$currency])) {
                $byCurrency[$currency] += $amount;
            }
        }
        
        return $byCurrency;
    }
}
