<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display pending payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice.klien']);

        // Filter by status (default: pending)
        $status = $request->filled('status') ? $request->status : 'pending';
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Search by invoice number
        if ($request->filled('search')) {
            $query->whereHas('invoice', function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.payments.index', compact('payments', 'status'));
    }

    /**
     * Show payment detail
     */
    public function show($id)
    {
        $payment = Payment::with(['invoice.klien'])->findOrFail($id);
        
        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Verify payment
     */
    public function verify($id)
    {
        $payment = Payment::with('invoice')->findOrFail($id);
        
        $payment->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        // Update invoice status to paid
        $invoice = $payment->invoice;
        if ($invoice && $invoice->status !== 'paid') {
            $invoice->update(['status' => 'paid']);
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment verified and invoice marked as paid!');
    }

    /**
     * Reject payment
     */
    public function reject(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        $payment->update([
            'status' => 'rejected',
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment rejected.');
    }
}
