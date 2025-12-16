<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProjectRequest;
use Illuminate\Http\Request;

class SurveyInvoiceController extends Controller
{
    /**
     * Create survey invoice for a project request
     */
    public function create(Request $request, $requestId)
    {
        $projectRequest = ProjectRequest::findOrFail($requestId);

        // Check if survey invoice already exists
        $existingSurvey = Invoice::where('klien_id', $projectRequest->klien_id)
            ->where('invoice_type', 'survey')
            ->whereHas('items', function($q) use ($requestId) {
                $q->where('description', 'like', "%Request #$requestId%");
            })
            ->first();

        if ($existingSurvey) {
            return redirect()->back()->with('error', 'Survey invoice already exists for this request!');
        }

        // Create survey invoice
        $surveyFee = Invoice::getSurveyFeeAmount();
        
        $invoice = Invoice::create([
            'klien_id' => $projectRequest->klien_id,
            'created_by' => auth()->id(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'subtotal' => $surveyFee,
            'tax' => 0,
            'discount' => 0,
            'total' => $surveyFee,
            'status' => 'unpaid',
            'invoice_type' => 'survey',
            'notes' => "Survey & Consultation Fee for Project Request: {$projectRequest->title}",
        ]);

        // Create invoice item
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Survey & Consultation Fee',
            'description' => "Site survey and consultation for Request #{$projectRequest->id}: {$projectRequest->title}",
            'quantity' => 1,
            'unit_price' => $surveyFee,
            'subtotal' => $surveyFee,
        ]);

        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Survey invoice created successfully!');
    }

    /**
     * Check if survey fee is paid for a request
     */
    public function checkSurveyFeePaid($requestId)
    {
        $projectRequest = ProjectRequest::findOrFail($requestId);

        $paidSurveyInvoice = Invoice::where('klien_id', $projectRequest->klien_id)
            ->where('invoice_type', 'survey')
            ->where('status', 'paid')
            ->whereHas('items', function($q) use ($requestId) {
                $q->where('description', 'like', "%Request #$requestId%");
            })
            ->first();

        return response()->json([
            'paid' => $paidSurveyInvoice !== null,
            'invoice' => $paidSurveyInvoice,
        ]);
    }
}
