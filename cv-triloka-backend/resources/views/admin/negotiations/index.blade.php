@extends('admin.layouts.app')

@section('page-title', 'Negotiations')

@section('content')
<!-- Success/Error Messages -->
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" action="{{ route('admin.negotiations.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-5">
                <input type="text" name="search" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" 
                       placeholder="Search quotation or client..." 
                       value="{{ request('search') }}">
            </div>
            <div class="md:col-span-3">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full px-4 py-2 text-white rounded-lg hover:opacity-90 transition" style="background-color: #6C5DD3;">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
            </div>
            <div class="md:col-span-2">
                <a href="{{ route('admin.negotiations.index') }}" class="block w-full px-4 py-2 text-center bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Negotiations List -->
@if($negotiations->count() > 0)
    <div class="grid grid-cols-1 gap-4">
        @foreach($negotiations as $negotiation)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex flex-wrap justify-between items-start mb-4">
                        <div class="flex-1 min-w-0 mr-4">
                            <div class="flex items-center gap-3 mb-2">
                                <a href="{{ route('admin.quotations.show', $negotiation->quotation_id) }}" 
                                   class="text-lg font-semibold hover:underline"
                                   style="color: #6C5DD3;">
                                    {{ $negotiation->quotation->quotation_number }}
                                </a>
                                @if($negotiation->status === 'pending')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                         Pending
                                    </span>
                                @elseif($negotiation->status === 'accepted')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Accepted
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                         Rejected
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center text-sm text-gray-600 gap-4">
                                <span> {{ $negotiation->sender->name }}</span>
                                <span> {{ $negotiation->created_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Price Comparison -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-600 mb-1">Original Price</p>
                            <p class="text-lg font-semibold text-gray-900">
                                Rp {{ number_format($negotiation->quotation->total, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-xs text-gray-600 mb-1">Counter Offer</p>
                            <p class="text-lg font-bold text-blue-600">
                                Rp {{ number_format($negotiation->counter_amount, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <p class="text-xs text-gray-600 mb-1">Difference</p>
                            <p class="text-lg font-semibold" style="color: {{ $negotiation->counter_amount < $negotiation->quotation->total ? '#EF4444' : '#10B981' }}">
                                {{ number_format((($negotiation->counter_amount - $negotiation->quotation->total) / $negotiation->quotation->total) * 100, 1) }}%
                            </p>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-xs text-gray-600 mb-2"> Message:</p>
                        <p class="text-sm text-gray-800 italic">"{{ $negotiation->message }}"</p>
                    </div>

                    <!-- Actions -->
                    @if($negotiation->status === 'pending')
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('admin.negotiations.accept', $negotiation->id) }}" class="flex-1">
                                @csrf
                                <button type="submit" 
                                        class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold"
                                        onclick="return confirm('Accept this negotiation?\n\nQuotation will be updated to Rp {{ number_format($negotiation->counter_amount, 0, ',', '.') }}')">
                                     Accept & Update Quotation
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.negotiations.reject', $negotiation->id) }}" class="flex-1">
                                @csrf
                                <button type="submit" 
                                        class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold"
                                        onclick="return confirm('Reject this negotiation?')">
                                     Reject
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-center py-2 text-gray-500 font-medium">
                            Already {{ ucfirst($negotiation->status) }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $negotiations->links() }}
    </div>
@else
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Negotiations Found</h3>
        <p class="text-gray-500">There are no negotiation requests at the moment.</p>
    </div>
@endif
@endsection
