@extends('admin.layouts.app')

@section('page-title', 'Request #' . $request->request_number)

@section('content')
<!-- Back Button -->
<div class="mb-4">
    <a href="{{ route('admin.requests.index') }}" class="text-sm text-purple-600 hover:text-purple-800">
        ← Back to Requests
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Request Info Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $request->title }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $request->request_number }}</p>
                </div>
                @php
                    $statusColors = [
                        'pending' => 'bg-orange-100 text-orange-800',
                        'quoted' => 'bg-blue-100 text-blue-800',
                        'negotiating' => 'bg-yellow-100 text-yellow-800',
                        'approved' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                    ];
                @endphp
                <span class="px-3 py-1 text-sm font-medium rounded {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst($request->status) }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                <div>
                    <span class="text-gray-500">Type:</span>
                    <span class="ml-2 font-medium">{{ ucfirst($request->request_type) }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Created:</span>
                    <span class="ml-2 font-medium">{{ $request->created_at->format('d M Y') }}</span>
                </div>
            </div>

            <div class="border-t pt-4">
                <h3 class="font-semibold text-gray-900 mb-2">Description</h3>
                <p class="text-gray-600 whitespace-pre-wrap">{{ $request->description }}</p>
            </div>
        </div>

        <!-- Project Details -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Project Details</h3>
            <dl class="grid grid-cols-1 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Location</dt>
                    <dd class="mt-1 font-medium">{{ $request->location ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Expected Budget</dt>
                    <dd class="mt-1 font-medium">
                        @if($request->expected_budget)
                            Rp {{ number_format($request->expected_budget, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Timeline</dt>
                    <dd class="mt-1 font-medium">{{ $request->expected_timeline ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Documents -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">
                Uploaded Documents ({{ $request->documents->count() }})
            </h3>
            
            @if($request->documents->isEmpty())
                <p class="text-gray-500 text-sm">No documents uploaded yet</p>
            @else
                <div class="space-y-3">
                    @foreach($request->documents as $document)
                        <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                            <div class="flex items-center flex-1 min-w-0">
                                <!-- File Icon -->
                                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded bg-purple-100">
                                    @if(in_array(strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']))
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                </div>
                                
                                <div class="ml-3 flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ basename($document->file_path) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ ucfirst($document->document_type) }} • 
                                        {{ $document->created_at->format('d M Y') }}
                                    </p>
                                    @if($document->description)
                                        <p class="text-xs text-gray-600 mt-1">{{ $document->description }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <a href="{{ asset('storage/' . $document->file_path) }}" 
                               target="_blank"
                               class="ml-4 px-3 py-1 text-sm text-purple-600 hover:text-purple-800 font-medium">
                                View
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Admin Notes -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Admin Notes</h3>
            <form method="POST" action="{{ route('admin.requests.updateStatus', $request->id) }}">
                @csrf
                <input type="hidden" name="status" value="{{ $request->status }}">
                <textarea name="admin_notes" rows="4" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                    placeholder="Add internal notes about this request...">{{ $request->admin_notes }}</textarea>
                <button type="submit" class="mt-2 px-4 py-2 text-white rounded-md hover:opacity-90" style="background-color: #6C5DD3;">
                    Save Notes
                </button>
            </form>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Client Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Client Information</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-500">Name</p>
                    <p class="font-medium">{{ $request->klien->name }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Email</p>
                    <p class="font-medium">{{ $request->klien->email }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Phone</p>
                    <p class="font-medium">{{ $request->klien->phone ?? '-' }}</p>
                </div>
                @if($request->klien->company_name)
                    <div>
                        <p class="text-gray-500">Company</p>
                        <p class="font-medium">{{ $request->klien->company_name }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Update Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Update Status</h3>
            
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.requests.updateStatus', $request->id) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="quoted" {{ $request->status == 'quoted' ? 'selected' : '' }}>Quoted</option>
                            <option value="negotiating" {{ $request->status == 'negotiating' ? 'selected' : '' }}>Negotiating</option>
                            <option value="approved" {{ $request->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $request->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full px-4 py-2 text-white rounded-md hover:opacity-90" style="background-color: #6C5DD3;">
                        Update Status
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <form method="POST" action="{{ route('admin.survey-invoice.create', $request->id) }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 text-left text-sm font-medium text-white rounded-md hover:opacity-90" style="background-color: #10B981;">
                        💰 Create Survey Invoice (Rp 500k)
                    </button>
                </form>
                <button class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 rounded-md">
                    📧 Email Client
                </button>
                <a href="{{ route('admin.quotations.create', $request->id) }}" 
                   class="block w-full px-4 py-2 text-left text-sm text-white rounded-md hover:opacity-90" 
                   style="background-color: #6C5DD3;">
                    📄 Create Quotation
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
