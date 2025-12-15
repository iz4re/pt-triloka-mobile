@extends('admin.layouts.guest')

@section('title', 'Admin Login')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo & Title -->
        <div class="text-center">
            <h1 class="text-4xl font-bold" style="color: #6C5DD3;">CV Triloka</h1>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Admin Panel
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Sign in to manage your business
            </p>
        </div>

        <!-- Login Form -->
        <div class="mt-8 bg-white py-8 px-6 shadow rounded-lg">
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-6">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email address
                    </label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required
                            value="{{ old('email') }}"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all"
                        style="background-color: #6C5DD3;">
                        Sign in
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} CV Triloka. All rights reserved.
        </p>
    </div>
</div>
@endsection
