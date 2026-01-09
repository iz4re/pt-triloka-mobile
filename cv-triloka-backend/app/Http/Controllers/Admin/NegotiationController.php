<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negotiation;
use App\Models\Quotation;
use App\Models\Notification;
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
    public function accept(Request $request, $id)
    {
        $negotiation = Negotiation::with('quotation')->findOrFail($id);

        if ($negotiation->status !== 'pending') {
            return back()->with('error', 'Negotiation already processed');
        }

        // Update negotiation status
        $negotiation->update([
            'status' => 'accepted',
            'admin_notes' => $request->admin_notes
        ]);

        // Update quotation with counter amount and status
        $negotiation->quotation->update([
            'total' => $negotiation->counter_amount,
            'subtotal' => $negotiation->counter_amount, // Simplified - adjust as needed
            'status' => 'revised',
            'version' => $negotiation->quotation->version + 1,
        ]);
        
        $negotiation->load('quotation.projectRequest.klien');
        $client = $negotiation->quotation->projectRequest->klien;
        
        if ($client) {
            Notification::createFor(
                $client,
                'negotiation_accepted',
                'Negosiasi Disetujui',
                "Penawaran harga Anda untuk {$negotiation->quotation->quotation_number} telah disetujui admin.",
                $negotiation
            );
        }

        return back()->with('success', 'Negotiation accepted and quotation updated!');
    }

    /**
     * Reject negotiation
     */
    public function reject(Request $request, $id)
    {
        $negotiation = Negotiation::findOrFail($id);

        if ($negotiation->status !== 'pending') {
            return back()->with('error', 'Negotiation already processed');
        }

        // Update negotiation status
        $negotiation->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes
        ]);
        
        $negotiation->load('quotation.projectRequest.klien');
        $client = $negotiation->quotation->projectRequest->klien;
        
        if ($client) {
            Notification::createFor(
                $client,
                'negotiation_rejected',
                'Negosiasi Ditolak',
                "Penawaran harga Anda untuk {$negotiation->quotation->quotation_number} ditolak oleh admin.",
                $negotiation
            );
        }

        return back()->with('success', 'Negotiation rejected');
    }
}
