@extends('admin.layouts.app')

@section('page-title', $user->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-purple-600 hover:text-purple-800">
        ← Back to Users
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- User Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $user->email }}</p>
                </div>
                <span class="px-3 py-1 text-sm font-medium rounded {{ $user->role == 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>

            <dl class="grid grid-cols-1 gap-4 text-sm border-t pt-4">
                <div>
                    <dt class="text-gray-500">Phone</dt>
                    <dd class="mt-1 font-medium">{{ $user->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Company</dt>
                    <dd class="mt-1 font-medium">{{ $user->company_name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Address</dt>
                    <dd class="mt-1 font-medium">{{ $user->address ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Member Since</dt>
                    <dd class="mt-1 font-medium">{{ $user->created_at->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Statistics (if client) -->
        @if($user->role == 'klien')
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Activity Statistics</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-3xl font-bold text-purple-600">{{ $user->project_requests_count }}</p>
                        <p class="text-sm text-gray-600 mt-1">Project Requests</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-3xl font-bold text-green-600">{{ $user->invoices_count }}</p>
                        <p class="text-sm text-gray-600 mt-1">Invoices</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.users.edit', $user->id) }}" 
                   class="block w-full px-4 py-2 text-center text-white rounded-md hover:opacity-90" 
                   style="background-color: #6C5DD3;">
                    Edit User
                </a>
                @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Delete User
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
