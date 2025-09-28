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
use App\Http\Controllers\BarcodeController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// API Route for series default sizes
Route::get('api/products/series-default-sizes', [\App\Http\Controllers\Products\ProductSeriesController::class, 'getDefaultSizes'])->name('products.series.default-sizes');


// Account selection routes
Route::get('/account/select', [AccountController::class, 'select'])->middleware(['auth'])->name('account.select');
Route::post('/account/select', [AccountController::class, 'store'])->middleware(['auth'])->name('account.store');
Route::post('/account/switch', [AccountController::class, 'switch'])->middleware(['auth'])->name('account.switch');
Route::get('/account/manage', [AccountController::class, 'manage'])->middleware(['auth'])->name('account.manage');
Route::put('/account/{account}', [AccountController::class, 'update'])->middleware(['auth'])->name('account.update');

// Product Series Routes (with auth middleware only)
Route::middleware(['auth'])->group(function () {
    Route::prefix('products')->name('products.')->group(function () {
        Route::resource('series', \App\Http\Controllers\Products\ProductSeriesController::class);
        
        // Series quick stock update
        Route::post('series/{series}/quick-stock', [\App\Http\Controllers\Products\ProductSeriesController::class, 'quickStockUpdate'])->name('series.quick-stock');
        
        // Series barcode generation
        Route::post('series/{series}/barcodes/generate', [\App\Http\Controllers\Products\ProductSeriesController::class, 'generateBarcodes'])->name('series.barcodes.generate');
        
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
        Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        // Invoice actions removed - invoices are now directly approved
    });

    // Purchases Routes
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::resource('invoices', \App\Http\Controllers\Purchases\InvoiceController::class);
        Route::resource('delivery-notes', \App\Http\Controllers\Purchases\DeliveryNoteController::class);
        Route::resource('orders', \App\Http\Controllers\Purchases\OrderController::class);
        
        
        // Additional purchase invoice routes
        Route::get('invoices/search/suppliers', [\App\Http\Controllers\Purchases\InvoiceController::class, 'searchSuppliers'])->name('invoices.search.suppliers');
        Route::get('invoices/search/products', [\App\Http\Controllers\Purchases\InvoiceController::class, 'searchProducts'])->name('invoices.search.products');
        Route::get('invoices/{invoice}/preview', [\App\Http\Controllers\Purchases\InvoiceController::class, 'preview'])->name('invoices.preview');
        Route::get('invoices/currency/rates', [\App\Http\Controllers\Purchases\InvoiceController::class, 'getCurrencyRates'])->name('invoices.currency.rates');
        Route::get('invoices/{invoice}/print', [\App\Http\Controllers\Purchases\InvoiceController::class, 'print'])->name('invoices.print');
        Route::post('invoices/{invoice}/mark-paid', [\App\Http\Controllers\Purchases\InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        // Purchase invoice actions removed - invoices are now directly approved
    });

    // Products Routes
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/quick-stock', [ProductController::class, 'quickStockUpdate'])->name('products.quick-stock');
    Route::post('products/series/{series}/quick-stock', [\App\Http\Controllers\Products\ProductSeriesController::class, 'quickStockUpdate'])->name('products.series.quick-stock');
    Route::get('products/test-critical-stock', [ProductController::class, 'testCriticalStock'])->name('products.test-critical-stock');
    
    // Barcode Section
    Route::get('/barcodes', [BarcodeController::class, 'index'])->name('barcode.index');
    Route::post('/barcodes/preview', [BarcodeController::class, 'preview'])->name('barcode.preview');
    Route::get('/barcodes/test', [BarcodeController::class, 'test'])->name('barcode.test');
    Route::get('/barcodes/lookup', [BarcodeController::class, 'lookupByBarcode'])->name('barcode.lookup');
    
    
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
        Route::resource('supplier-payments', \App\Http\Controllers\Finance\SupplierPaymentController::class);
        
        // Search routes
        Route::get('collections/search/customers', [\App\Http\Controllers\Finance\CollectionController::class, 'searchCustomers'])->name('collections.search.customers');
        Route::get('supplier-payments/search/suppliers', [\App\Http\Controllers\Finance\SupplierPaymentController::class, 'searchSuppliers'])->name('supplier-payments.search.suppliers');
        
        // Print routes
        Route::get('collections/{collection}/print', [\App\Http\Controllers\Finance\CollectionController::class, 'print'])->name('collections.print');
        Route::get('supplier-payments/{supplierPayment}/print', [\App\Http\Controllers\Finance\SupplierPaymentController::class, 'print'])->name('supplier-payments.print');
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