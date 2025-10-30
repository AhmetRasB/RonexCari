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
    
    /**
     * Get current account ID
     * Admin kullanıcılar için null döner (tüm hesapları görebilir)
     */
    private function getCurrentAccountId()
    {
        // Admin kullanıcılar tüm hesapları görebilir
        if (auth()->user()->isAdmin()) {
            return null;
        }
        return session('current_account_id');
    }

    public function index()
    {
        // Define time periods
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOf6Months = Carbon::now()->subMonths(6)->startOfMonth();
        $startOfYear = Carbon::now()->startOfYear();
        
        // Get current account ID (admin sees all, others see only their account)
        $accountId = $this->getCurrentAccountId();

        // Clear cache to get fresh data every time
        $this->currencyService->clearCache();
        
        // Get current exchange rates (fresh)
        $exchangeRates = $this->currencyService->getExchangeRates();
        $exchangeRates['TRY'] = 1; // Add TRY as base currency
        // Precompute numeric rates for raw SQL usage
        $usdRate = (float) ($exchangeRates['USD'] ?? 1);
        $eurRate = (float) ($exchangeRates['EUR'] ?? 1);
        
        // Get test rates for all API versions (tolerate API issues)
        try {
            $testRates = $this->currencyService->getTestRates();
            if (!is_array($testRates)) {
                $testRates = [];
            }
        } catch (\Throwable $e) {
            \Log::warning('Currency test rates unavailable', [
                'error' => $e->getMessage()
            ]);
            $testRates = [];
        }

        // === SALES ANALYTICS ===
        
        // Sales summary for different periods - convert all to TRY equivalent (ONLY APPROVED)
        $salesToday = $this->calculateTotalInTRY(
            Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->whereDate('created_at', $today)->get(['total_amount', 'currency']),
            $exchangeRates
        );
        $salesThisWeek = $this->calculateTotalInTRY(
            Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->whereBetween('created_at', [$startOfWeek, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );
        $salesThisMonth = $this->calculateTotalInTRY(
            Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('created_at', [$startOfMonth, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );
        $salesLast6Months = $this->calculateTotalInTRY(
            Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('created_at', [$startOf6Months, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );

        // Sales by currency for different periods (ONLY APPROVED)
        $salesTodayByCurrency = $this->getSalesByCurrency(
            Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereDate('created_at', $today)->get(['total_amount', 'currency'])
        );
        $salesThisWeekByCurrency = $this->getSalesByCurrency(
            Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('created_at', [$startOfWeek, Carbon::now()])->get(['total_amount', 'currency'])
        );
        $salesThisMonthByCurrency = $this->getSalesByCurrency(
            Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('created_at', [$startOfMonth, Carbon::now()])->get(['total_amount', 'currency'])
        );
        $salesLast6MonthsByCurrency = $this->getSalesByCurrency(
            Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('created_at', [$startOf6Months, Carbon::now()])->get(['total_amount', 'currency'])
        );
        
        // Sales count for different periods (ONLY APPROVED)
        $salesCountToday = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereDate('created_at', $today)->count();
        $salesCountWeek = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('created_at', [$startOfWeek, Carbon::now()])->count();
        $salesCountMonth = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('created_at', [$startOfMonth, Carbon::now()])->count();
        $salesCount6Months = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('created_at', [$startOf6Months, Carbon::now()])->count();

        // === TOP 10 MOST SOLD PRODUCTS (ONLY FROM APPROVED INVOICES) ===
        $topSellingProducts = InvoiceItem::select('invoice_items.product_service_name')
            ->leftJoin('products', 'invoice_items.product_id', '=', 'products.id')
            ->leftJoin('product_series', 'invoice_items.product_id', '=', 'product_series.id')
            ->selectRaw('SUM(invoice_items.quantity) as total_quantity')
            ->selectRaw('SUM(invoice_items.line_total) as total_revenue')
            ->selectRaw('SUM(COALESCE(
                products.cost,
                product_series.cost,
                (SELECT p.cost FROM products p WHERE p.name = invoice_items.product_service_name LIMIT 1),
                (SELECT ps.cost FROM product_series ps WHERE ps.name = invoice_items.product_service_name LIMIT 1),
                (
                SELECT COALESCE(pii.unit_price * (
                    CASE WHEN pii.unit_currency = "USD" THEN '.$usdRate.' 
                         WHEN pii.unit_currency = "EUR" THEN '.$eurRate.' 
                         ELSE 1 END
                ), 0)
                FROM purchase_invoice_items pii
                JOIN purchase_invoices pi ON pi.id = pii.purchase_invoice_id
                WHERE (pii.product_id = invoice_items.product_id OR pii.product_service_name = invoice_items.product_service_name)
                ORDER BY pi.created_at DESC
                LIMIT 1
                ),
                0
            ) * COALESCE(invoice_items.quantity, 0)) as total_cost')
            ->selectRaw('COUNT(*) as sale_count')
            ->whereHas('invoice', function($query) use ($accountId) {
                $query->when($accountId !== null, function($q) use ($accountId) {
                    return $q->where('account_id', $accountId);
                });
            })
            ->groupBy('invoice_items.product_service_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // === PROFIT/LOSS ANALYSIS ===
        
        // Monthly profit/loss calculation - convert all to TRY equivalent (ONLY APPROVED)
        $monthlyRevenue = $this->calculateTotalInTRY(
            Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('created_at', [$startOfMonth, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );
        
        // Use COGS (sold quantity * product cost) instead of purchases
        $usdRate = $exchangeRates['USD'] ?? 1;
        $eurRate = $exchangeRates['EUR'] ?? 1;

        $monthlyPurchases = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->leftJoin('products', 'invoice_items.product_id', '=', 'products.id')
            ->leftJoin('product_series', 'invoice_items.product_id', '=', 'product_series.id')
            ->when($accountId !== null, function($query) use ($accountId) {
                return $query->where('invoices.account_id', $accountId);
            })
            ->whereBetween('invoices.created_at', [$startOfMonth, Carbon::now()])
            ->sum(DB::raw(
                'COALESCE(
                    products.cost,
                    product_series.cost,
                    (SELECT p.cost FROM products p WHERE p.name = invoice_items.product_service_name LIMIT 1),
                    (SELECT ps.cost FROM product_series ps WHERE ps.name = invoice_items.product_service_name LIMIT 1),
                    (
                    SELECT COALESCE(pii.unit_price * (
                        CASE WHEN pii.unit_currency = "USD" THEN '.$usdRate.' 
                             WHEN pii.unit_currency = "EUR" THEN '.$eurRate.' 
                             ELSE 1 END
                    ), 0)
                    FROM purchase_invoice_items pii
                    JOIN purchase_invoices pi ON pi.id = pii.purchase_invoice_id
                    WHERE (pii.product_id = invoice_items.product_id OR pii.product_service_name = invoice_items.product_service_name)
                    ORDER BY pi.created_at DESC
                    LIMIT 1
                    ),
                    0
                ) * COALESCE(invoice_items.quantity, 0)'
            ));
        
        $monthlyExpenses = Expense::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('expense_date', [$startOfMonth, Carbon::now()])
            ->sum('amount'); // Expenses are always in TRY

        // Add salary payments to monthly expenses
        $monthlySalaryPayments = \App\Models\SalaryPayment::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('payment_date', [$startOfMonth, Carbon::now()])
            ->sum('amount'); // Salary payments are always in TRY

        $monthlyTotalExpenses = $monthlyExpenses + $monthlySalaryPayments;
        
        $monthlyProfit = $monthlyRevenue - $monthlyPurchases - $monthlyTotalExpenses;
        
        // 6 months profit/loss calculation - convert all to TRY equivalent (ONLY APPROVED)
        $sixMonthRevenue = $this->calculateTotalInTRY(
            Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('created_at', [$startOf6Months, Carbon::now()])->get(['total_amount', 'currency']),
            $exchangeRates
        );
        
        $sixMonthPurchases = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->leftJoin('products', 'invoice_items.product_id', '=', 'products.id')
            ->leftJoin('product_series', 'invoice_items.product_id', '=', 'product_series.id')
            ->when($accountId !== null, function($query) use ($accountId) {
                return $query->where('invoices.account_id', $accountId);
            })
            ->whereBetween('invoices.created_at', [$startOf6Months, Carbon::now()])
            ->sum(DB::raw(
                'COALESCE(
                    products.cost,
                    product_series.cost,
                    (SELECT p.cost FROM products p WHERE p.name = invoice_items.product_service_name LIMIT 1),
                    (SELECT ps.cost FROM product_series ps WHERE ps.name = invoice_items.product_service_name LIMIT 1),
                    (
                    SELECT COALESCE(pii.unit_price * (
                        CASE WHEN pii.unit_currency = "USD" THEN '.$usdRate.' 
                             WHEN pii.unit_currency = "EUR" THEN '.$eurRate.' 
                             ELSE 1 END
                    ), 0)
                    FROM purchase_invoice_items pii
                    JOIN purchase_invoices pi ON pi.id = pii.purchase_invoice_id
                    WHERE (pii.product_id = invoice_items.product_id OR pii.product_service_name = invoice_items.product_service_name)
                    ORDER BY pi.created_at DESC
                    LIMIT 1
                    ),
                    0
                ) * COALESCE(invoice_items.quantity, 0)'
            ));
        
        $sixMonthExpenses = Expense::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('expense_date', [$startOf6Months, Carbon::now()])
            ->sum('amount'); // Expenses are always in TRY

        // Add salary payments to 6-month expenses
        $sixMonthSalaryPayments = \App\Models\SalaryPayment::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('payment_date', [$startOf6Months, Carbon::now()])
            ->sum('amount'); // Salary payments are always in TRY

        $sixMonthTotalExpenses = $sixMonthExpenses + $sixMonthSalaryPayments;
        
        $sixMonthProfit = $sixMonthRevenue - $sixMonthPurchases - $sixMonthTotalExpenses;

        // === FINANCIAL SUMMARY ===
        
        // Collections summary - convert all to TRY equivalent
        $collectionsThisMonth = $this->calculateTotalInTRY(
            Collection::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('transaction_date', [$startOfMonth, Carbon::now()])->get(['amount as total_amount', 'currency']),
            $exchangeRates
        );
        $unpaidInvoicesTotal = $this->calculateTotalInTRY(
            Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->where('payment_completed', false)->get(['total_amount', 'currency']),
            $exchangeRates
        );

        // Collections by currency
        $collectionsThisMonthByCurrency = $this->getSalesByCurrency(
            Collection::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('transaction_date', [$startOfMonth, Carbon::now()])->get(['amount as total_amount', 'currency'])
        );
        
        // Customer and supplier balances
        $customerDebtTry = Customer::sum('balance_try');
        $customerDebtUsd = Customer::sum('balance_usd');
        $customerDebtEur = Customer::sum('balance_eur');
        
        // Exclude supplier debts from the reports per business requirement
        $supplierDebtTry = 0;
        $supplierDebtUsd = 0;
        $supplierDebtEur = 0;

        // === CHARTS DATA ===
        
        // Monthly sales chart (last 12 months)
        $monthlySalesData = [];
        $monthlyLabels = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            $monthlySalesData[] = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereYear('created_at', $month->year)
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
            $weeklySalesData[] = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('created_at', [$weekStart, $weekEnd])->sum('total_amount');
        }

        // Revenue by currency (this month)
        $monthlyRevenueByCurrency = $salesThisMonthByCurrency;

        // === TOP CUSTOMERS ===
        $topCustomers = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->select('customer_id')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->with('customer:id,name,company_name')
            ->groupBy('customer_id')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        // === UPCOMING DUE INVOICES ===
        $upcomingDueInvoices = Invoice::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->where('payment_completed', false)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [Carbon::now(), Carbon::now()->addDays(30)])
            ->with('customer:id,name,company_name')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // === EXPENSE ANALYSIS ===
        // Get regular expenses
        $regularExpenses = Expense::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('expense_date', [$startOfMonth, Carbon::now()])
            ->selectRaw('name, SUM(amount) as total_amount')
            ->groupBy('name')
            ->get();

        // Get salary payments as expenses
        $salaryExpenses = \App\Models\SalaryPayment::when($accountId !== null, function($query) use ($accountId) {
                return $query->where('account_id', $accountId);
            })->when($accountId === null, function($query) {
                return $query;
            })->whereBetween('payment_date', [$startOfMonth, Carbon::now()])
            ->with('employee')
            ->get()
            ->groupBy(function($item) {
                return 'Maaş - ' . $item->employee->name;
            })
            ->map(function($group) {
                return (object)[
                    'name' => $group->first()->employee->name . ' Maaşı',
                    'total_amount' => $group->sum('amount')
                ];
            });

        // Combine regular expenses and salary expenses
        $monthlyExpensesByCategory = $regularExpenses->concat($salaryExpenses)
            ->sortByDesc('total_amount')
            ->take(10);

        // === BRANCH STATISTICS (FOR ADMIN USERS) ===
        $branchStatistics = null;
        if (auth()->user()->isAdmin()) {
            $branchStatistics = $this->getBranchStatistics($startOfMonth, $exchangeRates);
        }

        return view('reports.index', compact(
            // Sales data
            'salesToday', 'salesThisWeek', 'salesThisMonth', 'salesLast6Months',
            'salesCountToday', 'salesCountWeek', 'salesCountMonth', 'salesCount6Months',
            
            // Sales by currency
            'salesTodayByCurrency', 'salesThisWeekByCurrency', 'salesThisMonthByCurrency', 'salesLast6MonthsByCurrency',
            
            // Products data
            'topSellingProducts',
            
            // Profit/Loss data
            'monthlyRevenue', 'monthlyPurchases', 'monthlyExpenses', 'monthlyTotalExpenses', 'monthlyProfit',
            'sixMonthRevenue', 'sixMonthPurchases', 'sixMonthExpenses', 'sixMonthTotalExpenses', 'sixMonthProfit',
            
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
            
            // Branch statistics
            'branchStatistics',
            
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

    /**
     * Get branch statistics for admin users
     */
    private function getBranchStatistics($startOfMonth, $exchangeRates)
    {
        $usdRate = (float) ($exchangeRates['USD'] ?? 1);
        $eurRate = (float) ($exchangeRates['EUR'] ?? 1);
        $branches = \App\Models\Account::where('is_active', true)->get();
        $branchStats = [];

        foreach ($branches as $branch) {
            // Sales for this branch
            $branchSales = Invoice::where('account_id', $branch->id)
                ->whereBetween('created_at', [$startOfMonth, Carbon::now()])
                ->get(['total_amount', 'currency']);
            
            $branchSalesTRY = $this->calculateTotalInTRY($branchSales, $exchangeRates);
            $branchSalesByCurrency = $this->getSalesByCurrency($branchSales);
            $branchSalesCount = $branchSales->count();

            // COGS for this branch (sold quantity * product cost)
            $branchPurchasesTRY = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->leftJoin('products', 'invoice_items.product_id', '=', 'products.id')
                ->leftJoin('product_series', 'invoice_items.product_id', '=', 'product_series.id')
                ->where('invoices.account_id', $branch->id)
                ->whereBetween('invoices.created_at', [$startOfMonth, Carbon::now()])
                ->sum(DB::raw(
                    'COALESCE(
                        products.cost,
                        product_series.cost,
                        (SELECT p.cost FROM products p WHERE p.name = invoice_items.product_service_name LIMIT 1),
                        (SELECT ps.cost FROM product_series ps WHERE ps.name = invoice_items.product_service_name LIMIT 1),
                        (
                        SELECT COALESCE(pii.unit_price * (
                            CASE WHEN pii.unit_currency = "USD" THEN '.$usdRate.' 
                                 WHEN pii.unit_currency = "EUR" THEN '.$eurRate.' 
                                 ELSE 1 END
                        ), 0)
                        FROM purchase_invoice_items pii
                        JOIN purchase_invoices pi ON pi.id = pii.purchase_invoice_id
                        WHERE (pii.product_id = invoice_items.product_id OR pii.product_service_name = invoice_items.product_service_name)
                        ORDER BY pi.created_at DESC
                        LIMIT 1
                        ),
                        0
                    ) * COALESCE(invoice_items.quantity, 0)'
                ));

            // Expenses for this branch
            $branchExpenses = Expense::where('account_id', $branch->id)
                ->whereBetween('expense_date', [$startOfMonth, Carbon::now()])
                ->sum('amount');

            // Salary payments for this branch
            $branchSalaryPayments = \App\Models\SalaryPayment::where('account_id', $branch->id)
                ->whereBetween('payment_date', [$startOfMonth, Carbon::now()])
                ->sum('amount');

            // Total expenses including salary payments
            $branchTotalExpenses = $branchExpenses + $branchSalaryPayments;

            // Profit calculation
            $branchProfit = $branchSalesTRY - $branchPurchasesTRY - $branchTotalExpenses;

            // Collections for this branch
            $branchCollections = Collection::where('account_id', $branch->id)
                ->whereBetween('transaction_date', [$startOfMonth, Carbon::now()])
                ->get(['amount as total_amount', 'currency']);
            
            $branchCollectionsTRY = $this->calculateTotalInTRY($branchCollections, $exchangeRates);

            // Unpaid invoices for this branch
            $branchUnpaidInvoices = Invoice::where('account_id', $branch->id)
                ->where('payment_completed', false)
                ->get(['total_amount', 'currency']);
            
            $branchUnpaidInvoicesTRY = $this->calculateTotalInTRY($branchUnpaidInvoices, $exchangeRates);

            // Top customers for this branch
            $branchTopCustomers = Invoice::where('account_id', $branch->id)
                ->select('customer_id')
                ->selectRaw('SUM(total_amount) as total_amount')
                ->with('customer:id,name,company_name')
                ->groupBy('customer_id')
                ->orderByDesc('total_amount')
                ->limit(5)
                ->get();

            // Top products for this branch
            $branchTopProducts = InvoiceItem::select('product_service_name')
                ->selectRaw('SUM(quantity) as total_quantity')
                ->selectRaw('SUM(line_total) as total_revenue')
                ->selectRaw('COUNT(*) as sale_count')
                ->whereHas('invoice', function($query) use ($branch) {
                    $query->where('account_id', $branch->id);
                })
                ->groupBy('product_service_name')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get();

            $branchStats[] = [
                'branch' => $branch,
                'sales' => [
                    'total_try' => $branchSalesTRY,
                    'by_currency' => $branchSalesByCurrency,
                    'count' => $branchSalesCount
                ],
                'purchases' => [
                    'total_try' => $branchPurchasesTRY
                ],
                'expenses' => $branchTotalExpenses,
                'profit' => $branchProfit,
                'collections' => $branchCollectionsTRY,
                'unpaid_invoices' => $branchUnpaidInvoicesTRY,
                'top_customers' => $branchTopCustomers,
                'top_products' => $branchTopProducts
            ];
        }

        return $branchStats;
    }
}
