<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuotationController extends Controller
{
    /**
     * Get quotations for authenticated user
     */
    public function index(Request $request)
    {
        // Get the authenticated user
        $userId = $request->user()->id;

        // Log for debugging
        Log::info('Fetching quotations for user', ['user_id' => $userId]);

        // Query quotations - check BOTH user_id AND klien_id for compatibility
        // Query quotations
        $query = Quotation::whereHas('projectRequest', function ($q) use ($userId) {
            $q->where('klien_id', $userId);
        })->with(['projectRequest', 'items']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $quotations = $query->orderBy('created_at', 'desc')->get();

        // Log the count
        Log::info('Quotations found', ['count' => $quotations->count()]);

        return response()->json([
            'success' => true,
            'data' => $quotations
        ]);
    }

    /**
     * Get quotation detail
     */
    public function show(Request $request, $id)
    {
        $quotation = Quotation::with(['projectRequest', 'items', 'negotiations.sender'])->find($id);

        if (!$quotation) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation not found'
            ], 404);
        }

        $userId = $request->user()->id;

        // Check authorization - check both user_id and klien_id
        $isAuthorized = $quotation->projectRequest->klien_id == $userId;

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $quotation
        ]);
    }

    /**
     * Approve quotation
     */
    public function approve(Request $request, $id)
    {
        $quotation = Quotation::with('projectRequest')->find($id);

        if (!$quotation) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation not found'
            ], 404);
        }

        $userId = $request->user()->id;

        // Check authorization
        $isAuthorized = $quotation->projectRequest->klien_id == $userId;

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if quotation can be approved
        if (!in_array($quotation->status, ['sent', 'revised'])) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation cannot be approved in current status'
            ], 400);
        }

        // Check if expired
        if ($quotation->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation has expired'
            ], 400);
        }

        // Update quotation status
        $quotation->update(['status' => 'approved']);

        // Update project request status
        $quotation->projectRequest->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Quotation approved successfully',
            'data' => $quotation->fresh('projectRequest', 'items')
        ]);
    }

    /**
     * Reject quotation
     */
    public function reject(Request $request, $id)
    {
        $quotation = Quotation::with('projectRequest')->find($id);

        if (!$quotation) {
            return response()->json([
                'success' => false,
                'message' => 'Quotation not found'
            ], 404);
        }

        $userId = $request->user()->id;

        // Check authorization
        $isAuthorized = $quotation->projectRequest->klien_id == $userId;

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Update quotation status
        $quotation->update(['status' => 'rejected']);

        // Optionally update project request status
        $quotation->projectRequest->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Quotation rejected successfully',
            'data' => $quotation->fresh('projectRequest', 'items')
        ]);
    }
}
