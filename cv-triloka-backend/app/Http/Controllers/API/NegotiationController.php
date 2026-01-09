<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Negotiation;
use Illuminate\Http\Request;

class NegotiationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Negotiation::with('sender');

        if ($user->role === 'klien') {
            $query->where('sender_id', $user->id);
        }

        $negotiations = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $negotiations
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'quotation_id' => 'required|integer|exists:quotations,id',
                'message' => 'required|string|min:10',
                'counter_amount' => 'required|numeric|min:1'
            ]);

            $negotiation = Negotiation::create([
                'quotation_id' => $validated['quotation_id'],
                'sender_id' => $request->user()->id,
                'sender_type' => $request->user()->role,
                'message' => $validated['message'],
                'counter_amount' => $validated['counter_amount'],
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Negotiation created successfully',
                'data' => $negotiation
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected'
        ]);

        $negotiation = Negotiation::findOrFail($id);

        $negotiation->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Negotiation updated'
        ]);
    }
}
