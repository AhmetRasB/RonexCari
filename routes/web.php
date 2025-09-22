<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;

// Sales Controllers
use App\Http\Controllers\Sales\CustomerController;
use App\Http\Controllers\Sales\InvoiceController;
use App\Http\Controllers\Sales\OrderController;
use App\Http\Controllers\Sales\QuoteController;

// Purchases Controllers
use App\Http\Controllers\Purchases\SupplierController;

// Products Controllers
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Products\ServiceController;

// Expenses Controllers
use App\Http\Controllers\Expenses\ExpenseController;
use App\Http\Controllers\Expenses\EmployeeController;

// Management Controllers
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Management\RoleController;
use App\Http\Controllers\Management\EmployeeController as ManagementEmployeeController;

// Finance Controllers

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// API Route for series default sizes
Route::get('api/products/series-default-sizes', [\App\Http\Controllers\Products\ProductSeriesController::class, 'getDefaultSizes'])->name('products.series.default-sizes');


// Account selection routes
Route::get('/account/select', [AccountController::class, 'select'])->middleware(['auth', 'verified'])->name('account.select');
Route::post('/account/select', [AccountController::class, 'store'])->middleware(['auth', 'verified'])->name('account.store');
Route::post('/account/switch', [AccountController::class, 'switch'])->middleware(['auth', 'verified'])->name('account.switch');
Route::get('/account/manage', [AccountController::class, 'manage'])->middleware(['auth', 'verified'])->name('account.manage');
Route::put('/account/{account}', [AccountController::class, 'update'])->middleware(['auth', 'verified'])->name('account.update');

// Product Series Routes (with auth middleware only)
Route::middleware(['auth'])->group(function () {
    Route::prefix('products')->name('products.')->group(function () {
        Route::resource('series', \App\Http\Controllers\Products\ProductSeriesController::class);
        
        // Fixed Series Settings Routes
        Route::get('fixed-series-settings', [\App\Http\Controllers\Products\FixedSeriesSettingController::class, 'index'])->name('fixed-series-settings.index');
        Route::get('fixed-series-settings/{fixedSeriesSetting}/edit', [\App\Http\Controllers\Products\FixedSeriesSettingController::class, 'edit'])->name('fixed-series-settings.edit');
        Route::put('fixed-series-settings/{fixedSeriesSetting}', [\App\Http\Controllers\Products\FixedSeriesSettingController::class, 'update'])->name('fixed-series-settings.update');
        Route::post('fixed-series-settings/create-defaults', [\App\Http\Controllers\Products\FixedSeriesSettingController::class, 'createDefaults'])->name('fixed-series-settings.create-defaults');
    });
});

Route::middleware(['auth', 'account.selection'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'getNotifications'])->name('notifications.get');

    // Sales Routes
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::resource('invoices', InvoiceController::class);
        Route::resource('orders', OrderController::class);
        Route::resource('quotes', QuoteController::class);
        
        // Additional invoice routes
        Route::get('invoices/search/customers', [InvoiceController::class, 'searchCustomers'])->name('invoices.search.customers');
        Route::get('invoices/search/products', [InvoiceController::class, 'searchProducts'])->name('invoices.search.products');
        Route::get('invoices/{invoice}/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
        Route::get('invoices/currency/rates', [InvoiceController::class, 'getCurrencyRates'])->name('invoices.currency.rates');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        // Invoice actions removed - invoices are now directly approved
    });

    // Purchases Routes
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::resource('invoices', \App\Http\Controllers\Purchases\InvoiceController::class);
        Route::resource('delivery-notes', \App\Http\Controllers\Purchases\DeliveryNoteController::class);
        Route::resource('orders', \App\Http\Controllers\Purchases\OrderController::class);
        
        // Supplier payment
        Route::post('suppliers/{supplier}/make-payment', [SupplierController::class, 'makePayment'])->name('suppliers.makePayment');
        
        // Additional purchase invoice routes
        Route::get('invoices/search/suppliers', [\App\Http\Controllers\Purchases\InvoiceController::class, 'searchSuppliers'])->name('invoices.search.suppliers');
        Route::get('invoices/search/products', [\App\Http\Controllers\Purchases\InvoiceController::class, 'searchProducts'])->name('invoices.search.products');
        Route::get('invoices/{invoice}/preview', [\App\Http\Controllers\Purchases\InvoiceController::class, 'preview'])->name('invoices.preview');
        Route::get('invoices/currency/rates', [\App\Http\Controllers\Purchases\InvoiceController::class, 'getCurrencyRates'])->name('invoices.currency.rates');
        Route::get('invoices/{invoice}/print', [\App\Http\Controllers\Purchases\InvoiceController::class, 'print'])->name('invoices.print');
        // Purchase invoice actions removed - invoices are now directly approved
    });

    // Products Routes
    Route::resource('products', ProductController::class);
    // Products QR/lookup
    // Products API lookup for QR/barcode/SKU
    Route::get('api/products/lookup', [ProductController::class, 'lookup'])->name('products.lookup');
    // QR preview page
    Route::get('products/qr/preview', [ProductController::class, 'quickView'])->name('products.qr.preview');
    
    
    // Services Routes (separate)
    Route::resource('services', ServiceController::class);

    // Expenses Routes
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::resource('expenses', ExpenseController::class);
        Route::resource('employees', EmployeeController::class);
        
        // Employee salary payment
        Route::post('employees/{employee}/pay-salary', [EmployeeController::class, 'paySalary'])->name('employees.paySalary');
    });

    // Finance Routes
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::resource('collections', \App\Http\Controllers\Finance\CollectionController::class);
        
        // Search routes
        Route::get('collections/search/customers', [\App\Http\Controllers\Finance\CollectionController::class, 'searchCustomers'])->name('collections.search.customers');
        
        // Print route
        Route::get('collections/{collection}/print', [\App\Http\Controllers\Finance\CollectionController::class, 'print'])->name('collections.print');
    });

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportsController::class, 'index'])->name('index');
        Route::get('/test-currency', function(\Illuminate\Http\Request $request) {
            $currencyService = app('App\Services\CurrencyService');
            
            // Check if manual rates are being set
            if ($request->get('manual')) {
                $usdRate = (float) $request->get('usd', 41.29);
                $eurRate = (float) $request->get('eur', 48.55);
                
                // Clear cache first
                $currencyService->clearCache();
                
                // Set manual rates
                $rates = $currencyService->setManualRates($usdRate, $eurRate);
                
                return response()->json([
                    'success' => true,
                    'rates' => $rates,
                    'timestamp' => now()->toDateTimeString(),
                    'message' => 'Manual currency rates set successfully'
                ]);
            }
            
            // Regular test - get current rates and test results
            $rates = $currencyService->getExchangeRates();
            $testRates = $currencyService->getTestRates();
            
            return response()->json([
                'success' => true,
                'current_rates' => $rates,
                'test_results' => $testRates,
                'timestamp' => now()->toDateTimeString(),
                'message' => 'Currency API test completed'
            ]);
        })->name('test-currency');
    });

    // Management Routes (Admin and God Mode only)
    Route::prefix('management')->name('management.')->middleware(['auth'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class)->only(['index']);
        Route::resource('employees', ManagementEmployeeController::class);
        
        // Salary payment routes
        Route::get('employees/{employee}/salary-payments/create', [\App\Http\Controllers\Management\SalaryPaymentController::class, 'create'])->name('employees.salary-payments.create');
        Route::post('employees/{employee}/salary-payments', [\App\Http\Controllers\Management\SalaryPaymentController::class, 'store'])->name('employees.salary-payments.store');
        Route::get('employees/{employee}/salary-payments', [\App\Http\Controllers\Management\SalaryPaymentController::class, 'show'])->name('employees.salary-payments.show');
        Route::get('employees/{employee}/remaining-salary', [\App\Http\Controllers\Management\SalaryPaymentController::class, 'getRemainingSalary'])->name('employees.remaining-salary');
        Route::get('employees/{employee}/total-remaining-salary', [\App\Http\Controllers\Management\SalaryPaymentController::class, 'getTotalRemainingSalary'])->name('employees.total-remaining-salary');
    });
});

require __DIR__.'/auth.php';