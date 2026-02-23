<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReturnController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');


Route::post('/login', [LoginController::class, 'handleLogin'])->name('login')->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::prefix('users')->as('users.')->middleware('permission:users')->controller(UserController::class)->group(function () {
        Route::get('/', 'Index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('/{id}/destroy', 'destroy')->name('destroy');
        Route::post('/ganti-password', 'gantiPassword')->name('ganti-password');
    });
    //master-data.kategori.index
    //master-data.kategori/index
    Route::prefix('master-data')->as('master-data.')->middleware('permission:master-data,master-data.kategori,master-data.product,master-data.unit,master-data.supplier,master-data.customer')->group(function () {
        Route::prefix('kategori')->as('kategori.')->controller(KategoriController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}/destroy', 'destroy')->name('destroy');
        });
        Route::prefix('product')->as('product.')->controller(ProductController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}/destroy', 'destroy')->name('destroy');
        });
        Route::resource('unit', \App\Http\Controllers\UnitController::class)->except(['create', 'edit', 'show']);
        Route::resource('supplier', \App\Http\Controllers\SupplierController::class)->except(['create', 'edit', 'show']);
        Route::resource('customer', \App\Http\Controllers\CustomerController::class)->except(['create', 'edit']);
        Route::get('customer/{id}/prices', [\App\Http\Controllers\CustomerController::class, 'getPrices'])->name('customer.getPrices');
        Route::post('customer/{id}/prices', [\App\Http\Controllers\CustomerController::class, 'storePrices'])->name('customer.storePrices');
    });
 
    // Piutang (Receivables)
    Route::prefix('receivable')->name('receivable.')->middleware('permission:receivable')->controller(\App\Http\Controllers\ReceivableController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/opening-balance', 'openingBalance')->name('opening-balance');
        Route::post('/opening-balance', 'storeOpeningBalance')->name('opening-balance.store');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/payment', 'storePayment')->name('payment.store');
        Route::get('/payment/{transactionId}/history', 'history')->name('payment.history');
        Route::get('/payment/{transactionId}/print', 'printPaymentHistory')->name('payment.print');
        Route::get('/{customerId}/print-all', 'printCustomerFullHistory')->name('print-all');
        Route::get('/payment/{transactionId}/print-raw', 'printRawPayment')->name('payment.print-raw');
    });

    // Hutang (Payables)
    Route::prefix('payable')->name('payable.')->middleware('permission:payable')->controller(\App\Http\Controllers\PayableController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/payment', 'storePayment')->name('payment.store');
        Route::get('/payment/{purchaseId}/history', 'history')->name('payment.history');
    });
 
        // Transactions
        Route::prefix('transaction')->name('transaction.')->group(function () {
             Route::get('/purchase/opening-balance', [\App\Http\Controllers\PurchaseController::class, 'openingBalance'])->name('purchase.opening-balance')->middleware('permission:transaction.purchase.create');
             Route::post('/purchase/opening-balance', [\App\Http\Controllers\PurchaseController::class, 'storeOpeningBalance'])->name('purchase.opening-balance.store')->middleware('permission:transaction.purchase.create');
             Route::get('/purchase/{id}/print-raw', [\App\Http\Controllers\PurchaseController::class, 'printRawPurchase'])->name('purchase.print-raw')->middleware('permission:transaction.purchase.create');
             Route::resource('purchase', \App\Http\Controllers\PurchaseController::class)->middleware('permission:transaction.purchase.create,transaction.purchase.index');
             
             // Sales
             Route::get('/sales', [TransactionController::class, 'index'])->name('sales.index');
             Route::get('/sales/create', [TransactionController::class, 'create'])->name('sales.create');
             Route::post('/sales', [TransactionController::class, 'store'])->name('sales.store');
             Route::get('/sales/{id}', [TransactionController::class, 'show'])->name('sales.show');
             Route::get('/sales/{id}/print', [TransactionController::class, 'print'])->name('sales.print');
             Route::get('/sales/{id}/print-raw', [TransactionController::class, 'printRaw'])->name('sales.print-raw');
             Route::get('/sales/{id}/invoice', [TransactionController::class, 'invoice'])->name('sales.invoice');
             Route::delete('/sales/{id}', [TransactionController::class, 'destroy'])->name('sales.destroy');
             
             // QZ Tray Security Routes
             Route::get('/qz/certificate', [TransactionController::class, 'qzCertificate'])->name('qz.certificate');
             Route::post('/qz/sign', [TransactionController::class, 'qzSign'])->name('qz.sign');
             
             // QZ Setup (Download CA)
             Route::get('/qz/setup', [TransactionController::class, 'setupQZ'])->name('qz.setup');
             Route::get('/qz/download-ca', [TransactionController::class, 'downloadCA'])->name('qz.download-ca');
        });
 
        Route::get('return/search', [ReturnController::class, 'searchTransaction'])->name('return.search');
        Route::resource('return', ReturnController::class)->except(['show', 'edit', 'update']);
 
        // Reports
        Route::get('/report/profit-loss', [\App\Http\Controllers\ReportController::class, 'profitLoss'])->name('report.profit-loss')->middleware('permission:report,report.profit-loss');
    // Stock Mutation
    Route::prefix('stock/mutation')->name('stock.mutation.')->middleware('permission:stock-mutation')->controller(\App\Http\Controllers\StockMutationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
    });
    
    // Anggaran (Budget)
    Route::prefix('budget')->name('budget.')->middleware('permission:budget,budget.salary,budget.operational')->group(function () {
        Route::resource('salary', \App\Http\Controllers\SalaryController::class)->except(['create', 'show', 'edit']);
        Route::resource('operational', \App\Http\Controllers\OperationalCostController::class)->except(['create', 'show', 'edit']);
    });

});
