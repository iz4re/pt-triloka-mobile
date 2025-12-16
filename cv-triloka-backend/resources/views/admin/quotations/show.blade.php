@extends('admin.layouts.app')

@section('page-title', 'Quotation Detail')

@section('content')
<div class="mb-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $quotation->quotation_number }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $quotation->projectRequest->title }}</p>
            </div>
            <div>
                @php
                    $statusColors = [
                        'draft' => 'bg-gray-100 text-gray-800',
                        'sent' => 'bg-blue-100 text-blue-800',
                        'approved' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'revised' => 'bg-yellow-100 text-yellow-800',
                        'expired' => 'bg-gray-100 text-gray-800',
                    ];
                @endphp
                <span class="px-4 py-2 text-sm font-semibold rounded-full {{ $statusColors[$quotation->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst($quotation->status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content - 2 columns -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Quotation Information -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b" style="background-color: #6C5DD3;">
                    <h3 class="text-lg font-semibold text-white">Quotation Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Client</p>
                            <p class="font-semibold text-gray-900">{{ $quotation->projectRequest->klien->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Project</p>
                            <p class="font-semibold text-gray-900">{{ $quotation->projectRequest->title }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Created Date</p>
                            <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Valid Until</p>
                            <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y') }}</p>
                            @if($quotation->isExpired())
                                <span class="text-xs text-red-600 font-medium">Expired</span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Version</p>
                            <p class="font-semibold text-gray-900">{{ $quotation->version }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Created At</p>
                            <p class="font-semibold text-gray-900">{{ $quotation->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @if($quotation->notes)
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-sm text-gray-600 mb-2">Notes</p>
                        <p class="text-gray-800">{{ $quotation->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b bg-green-600">
                    <h3 class="text-lg font-semibold text-white">Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($quotation->items as $index => $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium text-gray-900">{{ $item->item_name }}</div>
                                    @if($item->description)
                                        <div class="text-xs text-gray-500 mt-1">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800">
                                        {{ ucfirst($item->category) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->unit }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar - 1 column -->
        <div class="space-y-6">
            <!-- Financial Summary -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b bg-blue-600">
                    <h3 class="text-lg font-semibold text-white">Summary</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax ({{ $quotation->tax }}%):</span>
                            <span class="font-semibold">Rp {{ number_format($quotation->subtotal * ($quotation->tax / 100), 0, ',', '.') }}</span>
                        </div>
                        @if($quotation->discount > 0)
                        <div class="flex justify-between text-green-600">
                            <span>Discount:</span>
                            <span class="font-semibold">-Rp {{ number_format($quotation->discount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="pt-3 border-t flex justify-between items-center">
                            <span class="text-lg font-semibold">TOTAL:</span>
                            <span class="text-2xl font-bold" style="color: #6C5DD3;">
                                Rp {{ number_format($quotation->total, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b bg-gray-700">
                    <h3 class="text-lg font-semibold text-white">Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    @if($quotation->status === 'draft')
                        <form action="{{ route('admin.quotations.updateStatus', $quotation->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="sent">
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-paper-plane mr-2"></i>Send to Client
                            </button>
                        </form>
                    @endif

                    @if($quotation->status === 'sent' || $quotation->status === 'revised')
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                            <i class="fas fa-clock text-blue-600 mb-2"></i>
                            <p class="text-sm text-blue-800">Waiting for client response</p>
                        </div>
                    @endif

                    @if($quotation->status === 'approved')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-3">
                            <div class="flex items-center text-green-800">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span class="font-semibold">Approved by Client</span>
                            </div>
                        </div>
                        <form action="{{ route('admin.quotations.createInvoice', $quotation->id) }}" method="POST" 
                              onsubmit="return confirm('Create invoice from this quotation?\n\nTotal: Rp {{ number_format($quotation->total, 0, ',', '.') }}')">
                            @csrf
                            <button type="submit" class="w-full px-4 py-3 text-white rounded-lg hover:opacity-90 transition font-semibold" style="background-color: #6C5DD3;">
                                <i class="fas fa-file-invoice mr-2"></i>Create Invoice
                            </button>
                        </form>
                    @endif

                    @if($quotation->status === 'rejected')
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                            <i class="fas fa-times-circle text-red-600 mb-2"></i>
                            <p class="text-sm text-red-800 font-semibold">Rejected by Client</p>
                        </div>
                    @endif

                    <div class="pt-3 border-t">
                        <form action="{{ route('admin.quotations.destroy', $quotation->id) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this quotation?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                <i class="fas fa-trash mr-2"></i>Delete Quotation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
