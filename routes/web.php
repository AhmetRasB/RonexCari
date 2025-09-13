<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

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

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
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
        // Invoice actions
        Route::post('invoices/{invoice}/approve', [InvoiceController::class, 'approve'])->name('invoices.approve');
        Route::post('invoices/{invoice}/revert-draft', [InvoiceController::class, 'revertDraft'])->name('invoices.revertDraft');
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
        // Purchase invoice actions
        Route::post('invoices/{invoice}/approve', [\App\Http\Controllers\Purchases\InvoiceController::class, 'approve'])->name('invoices.approve');
        Route::post('invoices/{invoice}/revert-draft', [\App\Http\Controllers\Purchases\InvoiceController::class, 'revertDraft'])->name('invoices.revertDraft');
    });

    // Products Routes
    Route::resource('products', ProductController::class);
    
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
    });
});

require __DIR__.'/auth.php';