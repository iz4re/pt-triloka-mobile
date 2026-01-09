<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negotiation;
use App\Models\Quotation;
use Illuminate\Http\Request;

class NegotiationController extends Controller
{
    /**
     * Display all negotiations
     */
    public function index(Request $request)
    {
        $query = Negotiation::with(['quotation.projectRequest.klien', 'sender'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by quotation number or client name
        if ($request->filled('search')) {
            $query->whereHas('quotation', function ($q) use ($request) {
                $q->where('quotation_number', 'like', '%' . $request->search . '%');
            })->orWhereHas('sender', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $negotiations = $query->paginate(20);

        return view('admin.negotiations.index', compact('negotiations'));
    }

    /**
     * Accept negotiation and create revised quotation
     */
    public function accept($id)
    {
        $negotiation = Negotiation::with('quotation')->findOrFail($id);

        if ($negotiation->status !== 'pending') {
            return back()->with('error', 'Negotiation already processed');
        }

        // Update negotiation status
        $negotiation->update(['status' => 'accepted']);

        // Update quotation with counter amount and status
        $negotiation->quotation->update([
            'total' => $negotiation->counter_amount,
            'subtotal' => $negotiation->counter_amount, // Simplified - adjust as needed
            'status' => 'revised',
            'version' => $negotiation->quotation->version + 1,
        ]);

        return back()->with('success', 'Negotiation accepted and quotation updated!');
    }

    /**
     * Reject negotiation
     */
    public function reject($id)
    {
        $negotiation = Negotiation::findOrFail($id);

        if ($negotiation->status !== 'pending') {
            return back()->with('error', 'Negotiation already processed');
        }

        // Update negotiation status
        $negotiation->update(['status' => 'rejected']);

        return back()->with('success', 'Negotiation rejected');
    }
}
