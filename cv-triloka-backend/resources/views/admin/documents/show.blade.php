@extends('admin.layouts.app')

@section('page-title', 'Document Preview')

@section('content')
<!-- Back Button -->
<div class="mb-4">
    <a href="{{ route('admin.documents.index') }}" class="text-sm text-purple-600 hover:text-purple-800">
        ← Back to Documents
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Document Preview -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ basename($document->file_path) }}</h2>
            
            @php
                $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                $isPDF = $extension === 'pdf';
            @endphp

            <!-- Preview Area -->
            <div class="border rounded-lg overflow-hidden">
                @if($isImage)
                    <img src="{{ asset('storage/' . $document->file_path) }}" 
                         alt="{{ basename($document->file_path) }}"
                         class="w-full">
                @elseif($isPDF)
                    <iframe src="{{ asset('storage/' . $document->file_path) }}" 
                            class="w-full" 
                            style="min-height: 600px;">
                    </iframe>
                @else
                    <div class="flex flex-col items-center justify-center py-16 bg-gray-50">
                        <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-500 mb-4">Preview not available for this file type ({{ strtoupper($extension) }})</p>
                        <a href="{{ asset('storage/' . $document->file_path) }}" 
                           download 
                           class="px-4 py-2 text-white rounded-md hover:opacity-90" 
                           style="background-color: #6C5DD3;">
                            Download File
                        </a>
                    </div>
                @endif
            </div>

            <!-- Description -->
            @if($document->description)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
                    <p class="text-sm text-gray-600">{{ $document->description }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Document Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Document Information</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Type</dt>
                    <dd class="mt-1">
                        <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                            {{ ucfirst($document->document_type) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Request Number</dt>
                    <dd class="mt-1 font-medium">
                        <a href="{{ route('admin.requests.show', $document->project_request_id) }}" 
                           class="text-purple-600 hover:text-purple-800">
                            {{ $document->projectRequest->request_number }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Uploaded By</dt>
                    <dd class="mt-1 font-medium">{{ $document->projectRequest->klien->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Upload Date</dt>
                    <dd class="mt-1 font-medium">{{ $document->created_at->format('d M Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">File Size</dt>
                    <dd class="mt-1 font-medium">
                        @php
                            $filePath = storage_path('app/public/' . $document->file_path);
                            $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                        @endphp
                        {{ number_format($fileSize / 1024, 2) }} KB
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Verification Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Verification</h3>

            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.documents.verify', $document->id) }}">
                @csrf
                
                <div class="mb-4">
                    <div class="flex items-center p-3 rounded-lg {{ $document->verification_status === 'verified' ? 'bg-green-50' : 'bg-gray-50' }}">
                        <input type="radio" name="verification_status" value="verified" id="verified" 
                               {{ $document->verification_status === 'verified' ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-600">
                        <label for="verified" class="ml-2 text-sm font-medium {{ $document->verification_status === 'verified' ? 'text-green-800' : 'text-gray-700' }}">
                            ✓ Verified
                        </label>
                    </div>
                    <div class="flex items-center p-3 rounded-lg mt-2 {{ $document->verification_status === 'pending' ? 'bg-orange-50' : 'bg-gray-50' }}">
                        <input type="radio" name="verification_status" value="pending" id="pending"
                               {{ $document->verification_status === 'pending' ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-600">
                        <label for="pending" class="ml-2 text-sm font-medium {{ $document->verification_status === 'pending' ? 'text-orange-800' : 'text-gray-700' }}">
                            ⚠ Pending
                        </label>
                    </div>
                    <div class="flex items-center p-3 rounded-lg mt-2 {{ $document->verification_status === 'rejected' ? 'bg-red-50' : 'bg-gray-50' }}">
                        <input type="radio" name="verification_status" value="rejected" id="rejected"
                               {{ $document->verification_status === 'rejected' ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-600">
                        <label for="rejected" class="ml-2 text-sm font-medium {{ $document->verification_status === 'rejected' ? 'text-red-800' : 'text-gray-700' }}">
                            ✗ Rejected
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="verification_notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Add verification notes (optional)..."></textarea>
                </div>

                <button type="submit" class="w-full px-4 py-2 text-white rounded-md hover:opacity-90" 
                        style="background-color: #6C5DD3;">
                    Update Verification
                </button>
            </form>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ asset('storage/' . $document->file_path) }}" 
                   target="_blank"
                   class="block w-full px-4 py-2 text-center bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Open in New Tab
                </a>
                <a href="{{ asset('storage/' . $document->file_path) }}" 
                   download
                   class="block w-full px-4 py-2 text-center bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Download File
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
