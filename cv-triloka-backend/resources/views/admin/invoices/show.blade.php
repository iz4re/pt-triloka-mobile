@extends('admin.layouts.app')

@section('page-title', 'Invoice #' . $invoice->invoice_number)

@section('content')
<!-- Back Button -->
<div class="mb-4">
    <a href="{{ route('admin.invoices.index') }}" class="text-sm text-purple-600 hover:text-purple-800">
        ← Back to Invoices
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Invoice Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $invoice->invoice_number }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Invoice Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</p>
                </div>
                <div class="flex gap-2">
                    @if($invoice->invoice_type === 'survey')
                        <span class="px-3 py-1 text-sm font-medium rounded bg-orange-100 text-orange-800">
                            💰 Survey Fee
                        </span>
                    @endif
                    @php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-800',
                            'unpaid' => 'bg-orange-100 text-orange-800',
                            'paid' => 'bg-green-100 text-green-800',
                            'overdue' => 'bg-red-100 text-red-800',
                            'cancelled' => 'bg-gray-100 text-gray-800',
                        ];
                    @endphp
                    <span class="px-3 py-1 text-sm font-medium rounded {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm border-t pt-4">
                <div>
                    <p class="text-gray-500">Due Date</p>
                    <p class="font-medium">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</p>
                </div>
                @if($invoice->paid_at)
                    <div>
                        <p class="text-gray-500">Paid On</p>
                        <p class="font-medium">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Invoice Items</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($invoice->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->description }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 text-sm">No items</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="mt-6 border-t pt-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-medium">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($invoice->tax > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax:</span>
                        <span class="font-medium">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($invoice->discount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">
                            Discount:
                            @if($invoice->is_survey_fee_applied)
                                <span class="text-xs text-green-600">(incl. Survey Fee)</span>
                            @endif
                        </span>
                        <span class="font-medium text-red-600">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-lg font-bold pt-2 border-t">
                    <span>Total:</span>
                    <span>Rp {{ number_format($invoice->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Payment History</h3>
            
            @if($invoice->payments && $invoice->payments->count() > 0)
                <div class="space-y-3">
                    @foreach($invoice->payments as $payment)
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y H:i') }} • 
                                    {{ ucfirst($payment->payment_method) }}
                                </p>
                                @if($payment->notes)
                                    <p class="text-xs text-gray-600 mt-1">{{ $payment->notes }}</p>
                                @endif
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded {{ $payment->status == 'verified' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No payments recorded yet</p>
            @endif
        </div>

        <!-- Notes -->
        @if($invoice->notes)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Notes</h3>
                <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $invoice->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Client Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Client Information</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-500">Name</p>
                    <p class="font-medium">{{ $invoice->klien->name }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Email</p>
                    <p class="font-medium">{{ $invoice->klien->email }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Phone</p>
                    <p class="font-medium">{{ $invoice->klien->phone ?? '-' }}</p>
                </div>
                @if($invoice->klien->company_name)
                    <div>
                        <p class="text-gray-500">Company</p>
                        <p class="font-medium">{{ $invoice->klien->company_name }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.export.invoice.print', $invoice->id) }}" 
                   target="_blank"
                   class="block w-full px-4 py-2 text-center text-white rounded-md hover:opacity-90"
                   style="background-color: #6C5DD3;">
                    🖨️ Print Invoice
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
