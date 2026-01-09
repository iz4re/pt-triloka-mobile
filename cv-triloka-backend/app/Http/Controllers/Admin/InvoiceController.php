<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with('klien');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('klien', function ($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by invoice type (survey/project)
        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(20);

        return view('admin.invoices.index', compact('invoices'));
    }

    /**
     * Create invoice from approved quotation
     */
    public function createFromQuotation($quotationId)
    {
        $quotation = \App\Models\Quotation::with(['projectRequest', 'items'])->findOrFail($quotationId);
        
        // Validate quotation is approved
        if ($quotation->status !== 'approved') {
            return redirect()->back()->with('error', 'Only approved quotations can be converted to invoices.');
        }
        
        // Check if invoice already exists for this quotation
        $existingInvoice = Invoice::where('quotation_id', $quotation->id)->first();
        if ($existingInvoice) {
            return redirect()->route('admin.invoices.show', $existingInvoice->id)
                ->with('info', 'Invoice already exists for this quotation.');
        }
        
        // Create invoice
        $invoice = Invoice::create([
            'klien_id' => $quotation->projectRequest->user_id,
            'quotation_id' => $quotation->id,
            'invoice_type' => 'project',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => $quotation->subtotal,
            'tax' => $quotation->subtotal * ($quotation->tax / 100),
            'discount' => $quotation->discount,
            'total' => $quotation->total,
            'status' => 'unpaid',
            'created_by' => auth()->id(),
        ]);
        
        // Copy items from quotation to invoice
        foreach ($quotation->items as $quotationItem) {
            \App\Models\InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_name' => $quotationItem->item_name,
                'description' => $quotationItem->description,
                'quantity' => $quotationItem->quantity,
                'unit' => $quotationItem->unit,
                'unit_price' => $quotationItem->unit_price,
                'subtotal' => $quotationItem->subtotal,
            ]);
        }
        
        // Log activity
        \App\Models\ActivityLog::log(
            'create_invoice', 
            "Invoice {$invoice->invoice_number} created from quotation {$quotation->quotation_number}",
            $invoice,
            auth()->user()
        );
        
        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully from quotation!');
    }

    /**
     * Display the specified invoice
     */
    public function show($id)
    {
        $invoice = Invoice::with(['klien', 'items', 'payments'])->findOrFail($id);
        
        return view('admin.invoices.show', compact('invoice'));
    }
}
