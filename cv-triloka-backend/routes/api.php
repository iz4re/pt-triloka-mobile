<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ProjectRequestController;
use App\Http\Controllers\API\QuotationController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    
    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    
    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);
    Route::get('/invoices/status/overdue', [InvoiceController::class, 'overdue']);
    
    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments/submit', [PaymentController::class, 'submitPayment']); // Submit with proof
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    
    // Quotations
    Route::get('/quotations', [QuotationController::class, 'index']);
    Route::get('/quotations/{id}', [QuotationController::class, 'show']);
    Route::post('/quotations/{id}/approve', [QuotationController::class, 'approve']);
    Route::post('/quotations/{id}/reject', [QuotationController::class, 'reject']);
    
    // Debug endpoint (remove in production)
    Route::get('/debug/quotations', function(Request $request) {
        $userId = $request->user()->id;
        $quotations = \App\Models\Quotation::with('projectRequest')->get();
        
        return response()->json([
            'current_user_id' => $userId,
            'total_quotations' => $quotations->count(),
            'quotations' => $quotations->map(function($q) use ($userId) {
                return [
                    'id' => $q->id,
                    'number' => $q->quotation_number,
                    'status' => $q->status,
                    'project_id' => $q->project_request_id,
                    'project_user_id' => $q->projectRequest->user_id ?? null,
                    'project_klien_id' => $q->projectRequest->klien_id ?? null,
                    'matches_user' => ($q->projectRequest->user_id == $userId || $q->projectRequest->klien_id == $userId),
                ];
            })
        ]);
    });
    
    // Items (Inventory)
    Route::get('/items', [ItemController::class, 'index']);
    Route::post('/items', [ItemController::class, 'store']);
    Route::get('/items/{id}', [ItemController::class, 'show']);
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);
    Route::get('/items/status/low-stock', [ItemController::class, 'lowStock']);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Project Requests
    Route::get('/project-requests', [ProjectRequestController::class, 'index']);
    Route::post('/project-requests', [ProjectRequestController::class, 'store']);
    Route::get('/project-requests/{id}', [ProjectRequestController::class, 'show']);
    Route::put('/project-requests/{id}', [ProjectRequestController::class, 'update']);
    Route::delete('/project-requests/{id}', [ProjectRequestController::class, 'destroy']);
    
    // Request Documents
    Route::post('/project-requests/{id}/documents', [ProjectRequestController::class, 'uploadDocument']);
    Route::delete('/request-documents/{id}', [ProjectRequestController::class, 'deleteDocument']);
});
