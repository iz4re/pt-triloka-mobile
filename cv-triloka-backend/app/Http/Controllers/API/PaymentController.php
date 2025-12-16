<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Submit payment with proof image
     */
    public function submitPayment(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,check,other',
            'notes' => 'nullable|string',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);

        // Check invoice exists and belongs to user
        $invoice = Invoice::find($validated['invoice_id']);
        
        if ($user->isKlien() && $invoice->klien_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this invoice',
            ], 403);
        }

        // Store proof image
        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $proofPath = $request->file('proof_image')->store('payments', 'public');
        }

        // Create payment with pending status
        $payment = Payment::create([
            'invoice_id' => $validated['invoice_id'],
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'proof_image' => $proofPath,
            'created_by' => $user->id,
            'status' => 'pending', // Admin needs to verify
        ]);

        ActivityLog::log('submit_payment', "Payment {$payment->payment_number} submitted for invoice {$invoice->invoice_number}", $payment, $user);

        return response()->json([
            'success' => true,
            'message' => 'Payment submitted successfully. Waiting for admin verification.',
            'data' => $payment->load('invoice'),
        ], 201);
    }

    /**
     * Get payment history for user's invoices
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Payment::with(['invoice.klien']);

        // Filter by user's invoices only
        if ($user->isKlien()) {
            $query->whereHas('invoice', function($q) use ($user) {
                $q->where('klien_id', $user->id);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Get payment detail
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $payment = Payment::with(['invoice.klien'])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        // Check access
        if ($user->isKlien() && $payment->invoice->klien_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $payment,
        ]);
    }
}
