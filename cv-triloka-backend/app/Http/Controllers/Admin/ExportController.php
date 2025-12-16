<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\ProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    /**
     * Export invoice as printable HTML
     */
    public function invoicePrint($id)
    {
        $invoice = Invoice::with(['klien', 'items', 'payments'])->findOrFail($id);
        
        return view('admin.exports.invoice-print', compact('invoice'));
    }

    /**
     * Export requests data as CSV
     */
    public function requestsCSV(Request $request)
    {
        $query = ProjectRequest::with('klien');

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('request_type', $request->type);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        $filename = 'project-requests-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($requests) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Request Number',
                'Client',
                'Title',
                'Type',
                'Status',
                'Location',
                'Budget',
                'Timeline',
                'Date Created'
            ]);

            // Data
            foreach ($requests as $req) {
                fputcsv($file, [
                    $req->request_number,
                    $req->klien->name,
                    $req->title,
                    $req->request_type,
                    $req->status,
                    $req->location ?? '-',
                    $req->expected_budget ?? 0,
                    $req->expected_timeline ?? '-',
                    $req->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export invoices data as CSV
     */
    public function invoicesCSV(Request $request)
    {
        $query = Invoice::with('klien');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->get();

        $filename = 'invoices-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($invoices) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Invoice Number',
                'Invoice Type',
                'Client',
                'Invoice Date',
                'Due Date',
                'Subtotal',
                'Tax',
                'Discount',
                'Total',
                'Status',
                'Paid At'
            ]);

            // Data
            foreach ($invoices as $inv) {
                fputcsv($file, [
                    $inv->invoice_number,
                    ucfirst($inv->invoice_type ?? 'project'),
                    $inv->klien->name,
                    $inv->invoice_date,
                    $inv->due_date,
                    $inv->subtotal,
                    $inv->tax,
                    $inv->discount,
                    $inv->total,
                    $inv->status,
                    $inv->paid_at ?? '-'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
