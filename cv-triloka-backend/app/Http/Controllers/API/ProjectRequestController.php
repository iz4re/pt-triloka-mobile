<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProjectRequest;
use App\Models\RequestDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectRequestController extends Controller
{
    /**
     * Get all project requests for authenticated user
     */
    public function index(Request $request)
    {
        $query = ProjectRequest::where('klien_id', $request->user()->id)
            ->with(['klien', 'documents']) 
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $projectRequests = $query->get();

        return response()->json([
            'success' => true,
            'data' => $projectRequests
        ]);
    }

    /**
     * Create new project request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:construction,renovation,supply,contractor,other',
            'description' => 'required|string',
            'location' => 'required|string',
            'expected_budget' => 'nullable|numeric|min:0',
            'expected_timeline' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $projectRequest = ProjectRequest::create([
            'klien_id' => $request->user()->id,
            // 'klien_id' => $request->user()->id,  
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description,
            'location' => $request->location,
            'expected_budget' => $request->expected_budget,
            'expected_timeline' => $request->expected_timeline,
            'status' => 'pending',
        ]);

        // Load relationships
        $projectRequest->load(['klien', 'documents']); 

        return response()->json([
            'success' => true,
            'message' => 'Project request created successfully',
            'data' => $projectRequest
        ], 201);
    }

    /**
     * Get project request detail
     */
    public function show(Request $request, $id)
    {
        $projectRequest = ProjectRequest::with(['klien', 'documents']) 
            ->find($id);

        if (!$projectRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Project request not found'
            ], 404);
        }

        // Check if user owns this request
        if ($projectRequest->klien_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $projectRequest
        ]);
    }

    /**
     * Update project request (only if status is pending)
     */
    public function update(Request $request, $id)
    {
        $projectRequest = ProjectRequest::find($id);

        if (!$projectRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Project request not found'
            ], 404);
        }

        // Check ownership
        if ($projectRequest->klien_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Only allow editing if status is pending
        if ($projectRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit request with status: ' . $projectRequest->status
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:construction,renovation,supply,contractor,other',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string',
            'expected_budget' => 'nullable|numeric|min:0',
            'expected_timeline' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $projectRequest->update($request->only([
            'title',
            'type',
            'description',
            'location',
            'expected_budget',
            'expected_timeline'
        ]));

        $projectRequest->load(['klien', 'documents']);

        return response()->json([
            'success' => true,
            'message' => 'Project request updated successfully',
            'data' => $projectRequest
        ]);
    }

    /**
     * Delete project request (only if status is pending)
     */
    public function destroy(Request $request, $id)
    {
        $projectRequest = ProjectRequest::find($id);

        if (!$projectRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Project request not found'
            ], 404);
        }

        // Check ownership
        if ($projectRequest->klien_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Only allow deletion if status is pending
        if ($projectRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete request with status: ' . $projectRequest->status
            ], 400);
        }

        $projectRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project request deleted successfully'
        ]);
    }

    /**
     * Upload document for project request
     */
    public function uploadDocument(Request $request, $id)
    {
        $projectRequest = ProjectRequest::find($id);

        if (!$projectRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Project request not found'
            ], 404);
        }

        // Check ownership
        if ($projectRequest->klien_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
            'document_type' => 'required|in:ktp,npwp,drawing,rab,permit,photo,other',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('request-documents', $fileName, 'public');

        $document = RequestDocument::create([
            // 'request_id' => $projectRequest->id,
            'project_request_id' => $projectRequest->id,
            'document_type' => $request->document_type,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
            'verification_status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'data' => $document
        ], 201);
    }

    /**
     * Delete uploaded document
     */
    public function deleteDocument(Request $request, $documentId)
    {
        $document = RequestDocument::with('projectRequest')->find($documentId);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }

        // Check ownership through project request
        if ($document->projectRequest->klien_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully'
        ]);
    }
}
