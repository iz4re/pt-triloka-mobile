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
            
            // Future routes for resources
            // Route::resource('users', UserController::class);
        });
    });
});
