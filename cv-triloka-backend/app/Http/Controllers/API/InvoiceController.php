<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Invoice::with(['klien', 'creator', 'items', 'payments']);

        // Role-based filtering
        if ($user->isKlien()) {
            $query->where('klien_id', $user->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by invoice number or klien name
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('klien', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $invoices = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can create invoices',
            ], 403);
        }

        $request->validate([
            'klien_id' => 'required|exists:users,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.item_name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $tax = $request->tax ?? 0;
            $discount = $request->discount ?? 0;
            $total = $subtotal + $tax - $discount;

            // Create invoice
            $invoice = Invoice::create([
                'klien_id' => $request->klien_id,
                'created_by' => $user->id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'status' => 'unpaid',
                'notes' => $request->notes,
            ]);

            // Create invoice items
            foreach ($request->items as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $itemData['item_id'] ?? null,
                    'item_name' => $itemData['item_name'],
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
            }

            DB::commit();

            ActivityLog::log('create_invoice', "Invoice {$invoice->invoice_number} created for klien {$invoice->klien->name}", $invoice, $user);

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => $invoice->load(['klien', 'creator', 'items']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $invoice = Invoice::with(['klien', 'creator', 'items', 'payments'])->find($id);

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

        // Prepare response with VA fields
        $invoiceData = $invoice->toArray();
        $invoiceData['va_number'] = $invoice->va_number;
        $invoiceData['va_bank'] = $invoice->va_bank;
        $invoiceData['va_expires_at'] = $invoice->va_expires_at;

        return response()->json([
            'success' => true,
            'data' => $invoiceData,
        ]);
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can update invoices',
            ], 403);
        }

        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update paid invoice',
            ], 400);
        }

        $request->validate([
            'invoice_date' => 'sometimes|date',
            'due_date' => 'sometimes|date',
            'notes' => 'nullable|string',
            'status' => 'sometimes|in:draft,unpaid,cancelled',
        ]);

        $invoice->update($request->only(['invoice_date', 'due_date', 'notes', 'status']));

        ActivityLog::log('update_invoice', "Invoice {$invoice->invoice_number} updated", $invoice, $user);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully',
            'data' => $invoice->load(['klien', 'creator', 'items']),
        ]);
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admin can delete invoices',
            ], 403);
        }

        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        if ($invoice->payments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete invoice with existing payments',
            ], 400);
        }

        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        ActivityLog::log('delete_invoice', "Invoice {$invoiceNumber} deleted", null, $user);

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully',
        ]);
    }

    /**
     * Get overdue invoices
     */
    public function overdue(Request $request)
    {
        $user = $request->user();
        
        $query = Invoice::with(['klien', 'payments'])
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->whereDate('due_date', '<', now());

        if ($user->isKlien()) {
            $query->where('klien_id', $user->id);
        }

        $overdueInvoices = $query->get();

        // Auto-update status to overdue
        foreach ($overdueInvoices as $invoice) {
            if ($invoice->status !== 'overdue') {
                $invoice->update(['status' => 'overdue']);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $overdueInvoices,
        ]);
    }
}
