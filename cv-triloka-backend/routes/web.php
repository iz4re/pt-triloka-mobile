<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Admin Panel Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (login page only)
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    });
    
    // Login POST (no middleware to allow auth attempt)
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.post');

    // Authenticated admin routes
    Route::middleware('auth')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        
        // Admin-only routes (requires AdminAccess middleware)
        Route::middleware(App\Http\Middleware\AdminAccess::class)->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
            
            // Project Requests
            Route::get('requests', [App\Http\Controllers\Admin\RequestController::class, 'index'])->name('requests.index');
            Route::get('requests/{id}', [App\Http\Controllers\Admin\RequestController::class, 'show'])->name('requests.show');
            Route::post('requests/{id}/status', [App\Http\Controllers\Admin\RequestController::class, 'updateStatus'])->name('requests.updateStatus');
            
            // Invoices (Read-Only)
            Route::get('invoices', [App\Http\Controllers\Admin\InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('invoices/{id}', [App\Http\Controllers\Admin\InvoiceController::class, 'show'])->name('invoices.show');
            
            // Documents
            Route::get('documents', [App\Http\Controllers\Admin\DocumentController::class, 'index'])->name('documents.index');
            Route::get('documents/{id}', [App\Http\Controllers\Admin\DocumentController::class, 'show'])->name('documents.show');
            Route::post('documents/{id}/verify', [App\Http\Controllers\Admin\DocumentController::class, 'updateVerification'])->name('documents.verify');
            Route::get('documents/{id}/download', [App\Http\Controllers\Admin\DocumentController::class, 'download'])->name('documents.download');
            
            // Payments
            Route::get('payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
            Route::get('payments/{id}', [App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('payments.show');
            Route::post('payments/{id}/verify', [App\Http\Controllers\Admin\PaymentController::class, 'verify'])->name('payments.verify');
            Route::post('payments/{id}/reject', [App\Http\Controllers\Admin\PaymentController::class, 'reject'])->name('payments.reject');
            
            // Quotations
            Route::get('quotations', [App\Http\Controllers\Admin\QuotationController::class, 'index'])->name('quotations.index');
            Route::get('requests/{requestId}/quotations/create', [App\Http\Controllers\Admin\QuotationController::class, 'create'])->name('quotations.create');
            Route::post('quotations', [App\Http\Controllers\Admin\QuotationController::class, 'store'])->name('quotations.store');
            Route::get('quotations/{id}', [App\Http\Controllers\Admin\QuotationController::class, 'show'])->name('quotations.show');
            Route::post('quotations/{id}/status', [App\Http\Controllers\Admin\QuotationController::class, 'updateStatus'])->name('quotations.updateStatus');
            Route::delete('quotations/{id}', [App\Http\Controllers\Admin\QuotationController::class, 'destroy'])->name('quotations.destroy');
            Route::post('quotations/{id}/create-invoice', [App\Http\Controllers\Admin\InvoiceController::class, 'createFromQuotation'])->name('quotations.createInvoice');
            
            // Exports
            Route::get('export/invoice/{id}/print', [App\Http\Controllers\Admin\ExportController::class, 'invoicePrint'])->name('export.invoice.print');
            Route::get('export/requests/csv', [App\Http\Controllers\Admin\ExportController::class, 'requestsCSV'])->name('export.requests.csv');
            Route::get('export/invoices/csv', [App\Http\Controllers\Admin\ExportController::class, 'invoicesCSV'])->name('export.invoices.csv');
            
            // Future routes for resources
        });
    });
});
