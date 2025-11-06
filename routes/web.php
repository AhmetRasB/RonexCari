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


// Account selection routes
Route::get('/account/select', [AccountController::class, 'select'])->middleware(['auth'])->name('account.select');
Route::post('/account/select', [AccountController::class, 'store'])->middleware(['auth'])->name('account.store');
Route::post('/account/switch', [AccountController::class, 'switch'])->middleware(['auth'])->name('account.switch');
Route::get('/account/manage', [AccountController::class, 'manage'])->middleware(['auth'])->name('account.manage');
Route::put('/account/{account}', [AccountController::class, 'update'])->middleware(['auth'])->name('account.update');

// Product Series Routes (with auth and account selection middleware)
Route::middleware(['auth', 'account.selection'])->group(function () {
    Route::prefix('products')->name('products.')->group(function () {
        Route::resource('series', \App\Http\Controllers\Products\ProductSeriesController::class);
        
        // Series quick stock update
        Route::post('series/{series}/quick-stock', [\App\Http\Controllers\Products\ProductSeriesController::class, 'quickStockUpdate'])->name('series.quick-stock');
        
        // Series barcode generation
        Route::post('series/{series}/barcodes/generate', [\App\Http\Controllers\Products\ProductSeriesController::class, 'generateBarcodes'])->name('series.barcodes.generate');
        
        // Series add size
        Route::post('series/{series}/add-size', [\App\Http\Controllers\Products\ProductSeriesController::class, 'addSize'])->name('series.add-size');
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
        Route::post('customers/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulk-delete');
        Route::resource('invoices', InvoiceController::class);
        Route::post('invoices/bulk-delete', [InvoiceController::class, 'bulkDelete'])->name('invoices.bulk-delete');
        Route::resource('orders', OrderController::class);
        Route::resource('quotes', QuoteController::class);
        
        // Additional invoice routes
        Route::get('invoices/search/customers', [InvoiceController::class, 'searchCustomers'])->name('invoices.search.customers');
        Route::get('invoices/search/products', [InvoiceController::class, 'searchProducts'])->name('invoices.search.products');
        Route::post('invoices/{invoice}/add-return', [InvoiceController::class, 'addReturn'])->name('invoices.add-return');
        Route::get('invoices/{invoice}/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
        
        // Exchange routes
        Route::get('exchanges/{invoice}/create', [\App\Http\Controllers\Sales\ExchangeController::class, 'create'])->name('exchanges.create');
        Route::post('exchanges/{invoice}/store', [\App\Http\Controllers\Sales\ExchangeController::class, 'store'])->name('exchanges.store');
        Route::get('invoices/currency/rates', [InvoiceController::class, 'getCurrencyRates'])->name('invoices.currency.rates');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        // Invoice actions removed - invoices are now directly approved
    });

    // Purchases Routes
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::post('suppliers/bulk-delete', [SupplierController::class, 'bulkDelete'])->name('suppliers.bulk-delete');
        Route::resource('invoices', \App\Http\Controllers\Purchases\InvoiceController::class);
        Route::post('invoices/bulk-delete', [\App\Http\Controllers\Purchases\InvoiceController::class, 'bulkDelete'])->name('invoices.bulk-delete');
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
    Route::post('products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::post('products/series/bulk-delete', [\App\Http\Controllers\Products\ProductSeriesController::class, 'bulkDelete'])->name('products.series.bulk-delete');
    Route::post('products/{product}/quick-stock', [ProductController::class, 'quickStockUpdate'])->name('products.quick-stock');
    Route::post('products/series/{series}/quick-stock', [\App\Http\Controllers\Products\ProductSeriesController::class, 'quickStockUpdate'])->name('products.series.quick-stock');
    // Product Categories (per account) - resource under separate prefix
    Route::resource('product-categories', \App\Http\Controllers\Products\ProductCategoryController::class)
        ->parameters(['product-categories' => 'productCategory'])
        ->names([
            'index' => 'products.categories.index',
            'create' => 'products.categories.create',
            'store' => 'products.categories.store',
            'edit' => 'products.categories.edit',
            'update' => 'products.categories.update',
            'destroy' => 'products.categories.destroy',
        ])->except(['show']);

    // Product Brands (per account)
    Route::resource('product-brands', \App\Http\Controllers\Products\ProductBrandController::class)
        ->parameters(['product-brands' => 'productBrand'])
        ->names([
            'index' => 'products.brands.index',
            'create' => 'products.brands.create',
            'store' => 'products.brands.store',
            'edit' => 'products.brands.edit',
            'update' => 'products.brands.update',
            'destroy' => 'products.brands.destroy',
        ])->except(['show']);
    Route::get('product-brands/search', [\App\Http\Controllers\Products\ProductBrandController::class, 'search'])->name('products.brands.search');
    
    // Labels & Printing
    Route::get('/print/labels/zpl', [\App\Http\Controllers\PrintLabelController::class, 'zpl'])->name('print.labels.zpl');
    Route::get('/print/labels/zpl-by-color', [\App\Http\Controllers\PrintLabelController::class, 'zplByColor'])->name('print.labels.zpl-by-color');
    Route::get('/print/labels/preview-by-color', [\App\Http\Controllers\PrintLabelController::class, 'previewPngByColor'])->name('print.labels.preview-by-color');
    Route::post('/print/labels/download-colors-zip', [\App\Http\Controllers\PrintLabelController::class, 'downloadColorsZip'])->name('print.labels.download-colors-zip');
    Route::get('/print/labels/csv', [\App\Http\Controllers\PrintLabelController::class, 'csv'])->name('print.labels.csv');
    Route::get('/print/labels/btxml', [\App\Http\Controllers\PrintLabelController::class, 'btxml'])->name('print.labels.btxml');
    Route::get('/print/labels/preview', [\App\Http\Controllers\PrintLabelController::class, 'previewPng'])->name('print.labels.preview');
    Route::post('/print/labels/pdf', [\App\Http\Controllers\PrintLabelController::class, 'exportPdf'])->name('print.labels.pdf');
    Route::get('/print/labels/qr', [\App\Http\Controllers\PrintLabelController::class, 'generateQr'])->name('print.labels.qr');
    
    // Barcode Section
    Route::get('/barcodes', [BarcodeController::class, 'index'])->name('barcode.index');
    Route::post('/barcodes/preview', [BarcodeController::class, 'preview'])->name('barcode.preview');
    Route::get('/barcodes/lookup', [BarcodeController::class, 'lookupByBarcode'])->name('barcode.lookup');
    
    // API: Get colors for a series
    Route::get('/api/series/{id}/colors', function($id) {
        $series = \App\Models\ProductSeries::with('colorVariants')->find($id);
        if (!$series) {
            return response()->json(['error' => 'Series not found'], 404);
        }
        $colors = $series->colorVariants->pluck('color')->filter()->values()->all();
        return response()->json(['colors' => $colors]);
    })->name('api.series.colors');
    
    
    // Services Routes (separate)
    Route::resource('services', ServiceController::class);

    // Expenses Routes
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::resource('expenses', ExpenseController::class);
        Route::post('expenses/bulk-delete', [ExpenseController::class, 'bulkDelete'])->name('expenses.bulk-delete');
        Route::resource('employees', EmployeeController::class);
        
        // Employee salary payment
        Route::post('employees/{employee}/pay-salary', [EmployeeController::class, 'paySalary'])->name('employees.paySalary');
    });

    // Finance Routes
    Route::prefix('finance')->name('finance.')->group(function () {
        // Search routes - Resource routes'tan önce olmalı
        Route::get('collections/search/customers', [\App\Http\Controllers\Finance\CollectionController::class, 'searchCustomers'])->name('collections.search.customers');
        Route::get('collections/get-balances', [\App\Http\Controllers\Finance\CollectionController::class, 'getCustomerBalances'])->name('collections.get.balances');
        Route::get('supplier-payments/search/suppliers', [\App\Http\Controllers\Finance\SupplierPaymentController::class, 'searchSuppliers'])->name('supplier-payments.search.suppliers');
        
        // Print routes
        Route::get('collections/{collection}/print', [\App\Http\Controllers\Finance\CollectionController::class, 'print'])->name('collections.print');
        Route::get('supplier-payments/{supplierPayment}/print', [\App\Http\Controllers\Finance\SupplierPaymentController::class, 'print'])->name('supplier-payments.print');
        
        // Resource routes
        Route::resource('collections', \App\Http\Controllers\Finance\CollectionController::class);
        Route::post('collections/bulk-delete', [\App\Http\Controllers\Finance\CollectionController::class, 'bulkDelete'])->name('collections.bulk-delete');
        Route::resource('supplier-payments', \App\Http\Controllers\Finance\SupplierPaymentController::class);
        Route::post('supplier-payments/bulk-delete', [\App\Http\Controllers\Finance\SupplierPaymentController::class, 'bulkDelete'])->name('supplier-payments.bulk-delete');
    });

    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportsController::class, 'index'])->name('index');
    });

    // Management Routes (Admin and God Mode only)
    Route::prefix('management')->name('management.')->middleware(['auth'])->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::resource('roles', RoleController::class)->only(['index']);
        Route::resource('employees', ManagementEmployeeController::class);
        Route::post('employees/bulk-delete', [ManagementEmployeeController::class, 'bulkDelete'])->name('employees.bulk-delete');
        
        // Salary payment routes
        Route::get('employees/{employee}/salary-payments/create', [\App\Http\Controllers\Management\SalaryPaymentController::class, 'create'])->name('employees.salary-payments.create');
        Route::post('employees/{employee}/salary-payments', [\App\Http\Controllers\Management\SalaryPaymentController::class, 'store'])->name('employees.salary-payments.store');
        Route::get('employees/{employee}/salary-payments', [\App\Http\Controllers\Management\SalaryPaymentController::class, 'show'])->name('employees.salary-payments.show');
        Route::get('employees/{employee}/remaining-salary', [\App\Http\Controllers\Management\SalaryPaymentController::class, 'getRemainingSalary'])->name('employees.remaining-salary');
        Route::get('employees/{employee}/total-remaining-salary', [\App\Http\Controllers\Management\SalaryPaymentController::class, 'getTotalRemainingSalary'])->name('employees.total-remaining-salary');
    });
});

require __DIR__.'/auth.php';