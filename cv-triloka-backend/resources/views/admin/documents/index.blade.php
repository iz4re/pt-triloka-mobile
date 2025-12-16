@extends('admin.layouts.app')

@section('page-title', 'Documents')

@section('content')
<!-- Filters -->
<div class="bg-white rounded-lg shadow mb-6">
    <form method="GET" action="{{ route('admin.documents.index') }}" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Request # or filename..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
            </div>

            <!-- Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All Types</option>
                    <option value="drawing" {{ request('type') == 'drawing' ? 'selected' : '' }}>Drawing</option>
                    <option value="specification" {{ request('type') == 'specification' ? 'selected' : '' }}>Specification</option>
                    <option value="photo" {{ request('type') == 'photo' ? 'selected' : '' }}>Photo</option>
                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <!-- Verified Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="verified" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All Status</option>
                    <option value="yes" {{ request('verified') == 'yes' ? 'selected' : '' }}>Verified</option>
                    <option value="no" {{ request('verified') == 'no' ? 'selected' : '' }}>Unverified</option>
                </select>
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <button type="submit" class="px-4 py-2 text-white rounded-md hover:opacity-90" style="background-color: #6C5DD3;">
                Apply Filters
            </button>
            <a href="{{ route('admin.documents.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Documents Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($documents as $document)
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
            <!-- Document Preview -->
            <div class="h-48 bg-gray-100 flex items-center justify-center">
                @php
                    $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                @endphp
                
                @if($isImage)
                    <img src="{{ asset('storage/' . $document->file_path) }}" 
                         alt="{{ basename($document->file_path) }}"
                         class="w-full h-48 object-cover">
                @else
                    <div class="text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 uppercase">{{ $extension }}</p>
                    </div>
                @endif
                
                <!-- Verification Badge -->
                @if($document->verification_status === 'verified')
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">
                            ✓ Verified
                        </span>
                    </div>
                @endif
            </div>

            <!-- Document Info -->
            <div class="p-4">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-900 truncate flex-1">
                        {{ basename($document->file_path) }}
                    </h3>
                    <span class="ml-2 px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                        {{ ucfirst($document->document_type) }}
                    </span>
                </div>
                
                @if($document->description)
                    <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ $document->description }}</p>
                @endif
                
                <div class="text-xs text-gray-500 mb-3">
                    <p>{{ $document->projectRequest->request_number }}</p>
                    <p>{{ $document->created_at->format('d M Y') }}</p>
                </div>
                
                <a href="{{ route('admin.documents.show', $document->id) }}" 
                   class="block w-full px-4 py-2 text-center text-sm text-white rounded-md hover:opacity-90"
                   style="background-color: #6C5DD3;">
                    View Details
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-lg shadow p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-500">No documents found</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($documents->hasPages())
    <div class="mt-6">
        {{ $documents->links() }}
    </div>
@endif

<!-- Stats Summary -->
<div class="mt-6 text-sm text-gray-600">
    Showing {{ $documents->firstItem() ?? 0 }} to {{ $documents->lastItem() ?? 0 }} of {{ $documents->total() }} documents
</div>
@endsection
