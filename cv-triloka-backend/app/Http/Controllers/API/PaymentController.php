<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Payment::with(['invoice.klien', 'creator']);

        // Role-based filtering
        if ($user->isKlien()) {
            $query->whereHas('invoice', function($q) use ($user) {
                $q->where('klien_id', $user->id);
            });
        }

        $payments = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,check,other',
            'notes' => 'nullable|string',
            'proof_image' => 'nullable|image|max:2048', // max 2MB
        ]);

        $invoice = Invoice::find($request->invoice_id);

        // Check access for klien
        if ($user->isKlien() && $invoice->klien_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid',
            ], 400);
        }

        // Check remaining balance
        $remainingBalance = $invoice->remainingBalance();
        if ($request->amount > $remainingBalance) {
            return response()->json([
                'success' => false,
                'message' => "Payment amount exceeds remaining balance of Rp " . number_format($remainingBalance, 0, ',', '.'),
            ], 400);
        }

        // Handle image upload
        $proofImagePath = null;
        if ($request->hasFile('proof_image')) {
            $proofImagePath = $request->file('proof_image')->store('payment_proofs', 'public');
        }

        // Create payment
        $payment = Payment::create([
            'invoice_id' => $request->invoice_id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'proof_image' => $proofImagePath,
            'created_by' => $user->id,
        ]);

        // Create notification for admin
        if ($user->isKlien()) {
            Notification::createFor(
                $invoice->creator,
                'payment_received',
                'Payment Received',
                "{$user->name} made a payment of Rp " . number_format($payment->amount, 0, ',', '.') . " for invoice {$invoice->invoice_number}",
                $payment
            );
        }

        ActivityLog::log('create_payment', "Payment {$payment->payment_number} created for invoice {$invoice->invoice_number}", $payment, $user);

        return response()->json([
            'success' => true,
            'message' => 'Payment created successfully',
            'data' => $payment->load(['invoice', 'creator']),
        ], 201);
    }

    /**
     * Display the specified payment
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $payment = Payment::with(['invoice.klien', 'creator'])->find($id);

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

    /**
     * Get payments for specific invoice
     */
    public function byInvoice(Request $request, $invoiceId)
    {
        $user = $request->user();
        
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        // Check access
        if ($user->isKlien() && $invoice->klien_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $payments = $invoice->payments()->with('creator')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'invoice' => $invoice,
                'payments' => $payments,
                'total_paid' => $payments->sum('amount'),
                'remaining_balance' => $invoice->remainingBalance(),
            ],
        ]);
    }

    /**
     * Remove the specified payment (admin only)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can delete payments',
            ], 403);
        }

        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        // Delete proof image if exists
        if ($payment->proof_image) {
            Storage::disk('public')->delete($payment->proof_image);
        }

        // Update invoice status back to unpaid if needed
        $invoice = $payment->invoice;
        $paymentNumber = $payment->payment_number;
        
        $payment->delete();

        // Recheck invoice status after deletion
        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid < $invoice->total && $invoice->status === 'paid') {
            $invoice->update([
                'status' => 'unpaid',
                'paid_at' => null,
            ]);
        }

        ActivityLog::log('delete_payment', "Payment {$paymentNumber} deleted", null, $user);

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully',
        ]);
    }
}
