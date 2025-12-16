<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestDocument;  // FIXED: was Document
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents
     */
    public function index(Request $request)
    {
        $query = RequestDocument::with(['projectRequest.klien']);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('file_path', 'like', '%' . $request->search . '%')
                  ->orWhereHas('projectRequest', function ($q) use ($request) {
                      $q->where('request_number', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('document_type', $request->type);
        }

        // Filter by verification status
        if ($request->filled('verified')) {
            if ($request->verified === 'yes') {
                $query->where('verification_status', 'verified');
            } else {
                $query->where('verification_status', '!=', 'verified');
            }
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.documents.index', compact('documents'));
    }

    /**
     * Display the specified document
     */
    public function show($id)
    {
        $document = RequestDocument::with(['projectRequest.klien'])->findOrFail($id);
        
        return view('admin.documents.show', compact('document'));
    }

    /**
     * Update document verification status
     */
    public function updateVerification(Request $request, $id)
    {
        $validated = $request->validate([
            'verification_status' => 'required|in:pending,verified,rejected',
            'verification_notes' => 'nullable|string'
        ]);

        $document = RequestDocument::findOrFail($id);
        
        $updateData = ['verification_status' => $validated['verification_status']];
        
        if ($validated['verification_status'] === 'verified') {
            $updateData['verified_by'] = auth()->id();
            $updateData['verified_at'] = now();
        }
        
        $document->update($updateData);

        return back()->with('success', 'Document verification updated successfully!');
    }

    /**
     * Download document file
     */
    public function download($id)
    {
        $document = RequestDocument::findOrFail($id);
        
        $filePath = storage_path('app/' . $document->file_path);
        
        if (!file_exists($filePath)) {
            return back()->with('error', 'File not found.');
        }
        
        return response()->download($filePath);
    }
}
