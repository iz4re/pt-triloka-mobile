<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\ProjectRequest;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    /**
     * Display quotations list
     */
    public function index(Request $request)
    {
        $query = Quotation::with(['projectRequest.klien', 'items']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('quotation_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('projectRequest', function ($q) use ($request) {
                      $q->where('request_number', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $quotations = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.quotations.index', compact('quotations'));
    }

    /**
     * Show create form
     */
    public function create($requestId)
    {
        $projectRequest = ProjectRequest::with(['klien', 'documents'])->findOrFail($requestId);
        
        // Prevent creating quotation if project is locked
        if ($projectRequest->isLocked()) {
            return redirect()->route('admin.requests.show', $requestId)
                ->with('error', 'Cannot create quotation - project is locked because invoice has been paid');
        }
        
        return view('admin.quotations.create', compact('projectRequest'));
    }

    /**
     * Store quotation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_request_id' => 'required|exists:project_requests,id',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'valid_until' => 'required|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.category' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        // Create quotation
        $quotation = Quotation::create([
            'project_request_id' => $validated['project_request_id'],
            // 'quotation_date' => now()->toDateString(),
            'tax' => $validated['tax'] ?? 0,
            'discount' => $validated['discount'] ?? 0,
            'notes' => $validated['notes'],
            'valid_until' => $validated['valid_until'],
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        // Create items
        foreach ($validated['items'] as $itemData) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'item_name' => $itemData['item_name'],
                'category' => $itemData['category'] ?? 'other',
                'quantity' => $itemData['quantity'],
                'unit' => $itemData['unit'],
                'unit_price' => $itemData['unit_price'],
                'description' => $itemData['description'] ?? null,
            ]);
        }

        // Calculate total (done automatically by model events)
        
        return redirect()->route('admin.quotations.show', $quotation->id)
            ->with('success', 'Quotation created successfully!');
    }

    /**
     * Show quotation detail
     */
    public function show($id)
    {
        $quotation = Quotation::with(['projectRequest.klien', 'items', 'creator'])->findOrFail($id);
        
        return view('admin.quotations.show', compact('quotation'));
    }

    /**
     * Update quotation status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,sent,approved,rejected,revised',
        ]);

        $quotation = Quotation::findOrFail($id);
        $quotation->update(['status' => $validated['status']]);

        // If approved, update project request status
        if ($validated['status'] === 'approved') {
            $quotation->projectRequest->update(['status' => 'approved']);
        }

        return back()->with('success', 'Quotation status updated!');
    }

    /**
     * Delete quotation
     */
    public function destroy($id)
    {
        $quotation = Quotation::findOrFail($id);
        $quotation->delete();

        return redirect()->route('admin.quotations.index')
            ->with('success', 'Quotation deleted successfully!');
    }
}
