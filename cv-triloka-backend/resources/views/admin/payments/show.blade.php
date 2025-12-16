@extends('admin.layouts.app')

@section('page-title', 'Payment Verification')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.payments.index') }}" class="text-sm text-purple-600 hover:text-purple-800">
        ← Back to Payments
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Payment Proof -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Payment Proof</h2>
            
            @if($payment->proof_image)
                <div class="border rounded-lg overflow-hidden">
                    <img src="{{ asset('storage/' . $payment->proof_image) }}" 
                         alt="Payment Proof"
                         class="w-full">
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 bg-gray-50 border rounded-lg">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-gray-500">No payment proof uploaded</p>
                </div>
            @endif
        </div>

        <!-- Payment Details Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Payment Details</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Payment Number</dt>
                    <dd class="mt-1 font-medium">{{ $payment->payment_number }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Payment Date</dt>
                    <dd class="mt-1 font-medium">{{ $payment->payment_date->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Amount</dt>
                    <dd class="mt-1 font-medium text-lg text-purple-600">Rp {{ number_format($payment->amount, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Method</dt>
                    <dd class="mt-1">
                        <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                            {{ ucfirst($payment->payment_method) }}
                        </span>
                    </dd>
                </div>
            </dl>

            @if($payment->notes)
                <div class="mt-4 pt-4 border-t">
                    <dt class="text-gray-500 text-sm">Notes</dt>
                    <dd class="mt-1">{{ $payment->notes }}</dd>
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Invoice Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Invoice Information</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Invoice #</dt>
                    <dd class="mt-1 font-medium">
                        <a href="{{ route('admin.invoices.show', $payment->invoice->id) }}" 
                           class="text-purple-600 hover:text-purple-800">
                            {{ $payment->invoice->invoice_number }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Invoice Total</dt>
                    <dd class="mt-1 font-medium">Rp {{ number_format($payment->invoice->total, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">VA Number</dt>
                    <dd class="mt-1 font-mono font-medium text-green-600">
                        {{ $payment->invoice->va_number ?? 'Not generated' }}
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Client Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Client</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Name</dt>
                    <dd class="mt-1 font-medium">{{ $payment->invoice->klien->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="mt-1 font-medium">{{ $payment->invoice->klien->email }}</dd>
                </div>
            </dl>
        </div>

        <!-- Verification Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
            
            @if($payment->status === 'pending')
                <div class="space-y-2">
                    <form action="{{ route('admin.payments.verify', $payment->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                onclick="return confirm('Verify this payment?')">
                            ✓ Verify Payment
                        </button>
                    </form>

                    <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST">
                        @csrf
                        <textarea name="notes" rows="2" 
                                  placeholder="Rejection reason (optional)"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md mb-2 focus:outline-none focus:ring-red-500 focus:border-red-500"></textarea>
                        <button type="submit" 
                                class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                onclick="return confirm('Reject this payment?')">
                            ✗ Reject Payment
                        </button>
                    </form>
                </div>
            @else
                <div class="text-center p-4 rounded-lg {{ $payment->status === 'verified' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }}">
                    <p class="font-medium">
                        {{ $payment->status === 'verified' ? '✓ Payment Verified' : '✗ Payment Rejected' }}
                    </p>
                    @if($payment->verified_at)
                        <p class="text-xs mt-1">
                            {{ $payment->verified_at->format('d M Y H:i') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
