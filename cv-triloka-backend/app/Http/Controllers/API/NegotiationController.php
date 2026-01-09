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
        $request->validate([
            'quotation_id' => 'required|integer',
            'message' => 'required|string',
            'counter_amount' => 'nullable|numeric'
        ]);

        $negotiation = Negotiation::create([
            'quotation_id' => $request->quotation_id,
            'sender_id' => $request->user()->id,
            'sender_type' => $request->user()->role,
            'message' => $request->message,
            'counter_amount' => $request->counter_amount,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'data' => $negotiation
        ]);
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
