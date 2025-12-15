@extends('admin.layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total Requests (This Month) -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(108, 93, 211, 0.1);">
                    <svg class="w-6 h-6" style="color: #6C5DD3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Requests This Month</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_requests'] }}</p>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-orange-100">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pending Requests</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_requests'] }}</p>
            </div>
        </div>
    </div>

    <!-- Total Clients -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Clients</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_clients'] }}</p>
            </div>
        </div>
    </div>

    <!-- Revenue This Month -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Revenue This Month</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Requests -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-bold text-gray-900">Recent Requests</h2>
        </div>
        <div class="divide-y">
            @forelse($recentRequests as $request)
                <div class="px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $request->title }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $request->klien->name }} • {{ $request->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="ml-2 px-2 py-1 text-xs font-medium rounded {{ $request->status == 'pending' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No recent requests
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-bold text-gray-900">Pending Approvals</h2>
        </div>
        <div class="divide-y">
            @forelse($pendingApprovals as $request)
                <div class="px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $request->title }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $request->klien->name }} • {{ $request->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <button class="ml-2 px-3 py-1 text-xs font-medium text-white rounded hover:opacity-90" style="background-color: #6C5DD3;">
                            Review
                        </button>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No pending approvals
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
