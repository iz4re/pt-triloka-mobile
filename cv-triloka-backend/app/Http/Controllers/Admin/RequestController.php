<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Display a listing of project requests
     */
    public function index(Request $request)
    {
        $query = ProjectRequest::with('klien');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('request_number', 'like', '%' . $request->search . '%')
                  ->orWhere('title', 'like', '%' . $request->search . '%')
                  ->orWhereHas('klien', function ($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('request_type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.requests.index', compact('requests'));
    }

    /**
     * Display the specified project request
     */
    public function show($id)
    {
        $request = ProjectRequest::with(['klien', 'documents'])->findOrFail($id);
        
        return view('admin.requests.show', compact('request'));
    }

    /**
     * Update project request status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,quoted,negotiating,approved,rejected',
            'admin_notes' => 'nullable|string'
        ]);

        $projectRequest = ProjectRequest::findOrFail($id);
        
        // Prevent editing if project is locked (invoice paid)
        if ($projectRequest->isLocked()) {
            return back()->with('error', 'Cannot update - project is locked because invoice has been paid');
        }
        
        $projectRequest->update($validated);

        return back()->with('success', 'Status updated successfully!');
    }
}
