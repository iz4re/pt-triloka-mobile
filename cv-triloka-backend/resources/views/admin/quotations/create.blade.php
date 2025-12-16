@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.requests.index') }}">Requests</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.requests.show', $projectRequest->id) }}">{{ $projectRequest->request_number }}</a></li>
            <li class="breadcrumb-item active">Create Quotation</li>
        </ol>
    </nav>

    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Create Quotation</h3>
        <a href="{{ route('admin.requests.show', $projectRequest->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Project Info -->
    <div class="card mb-4">
        <div class="card-body bg-light">
            <div class="row">
                <div class="col-md-6">
                    <strong>Project:</strong> {{ $projectRequest->title }}<br>
                    <small class="text-muted">{{ $projectRequest->request_number }}</small>
                </div>
                <div class="col-md-6 text-right">
                    <strong>Client:</strong> {{ $projectRequest->klien->name }}<br>
                    <strong>Budget:</strong> <span class="text-success">Rp {{ number_format($projectRequest->expected_budget, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quotation Form -->
    <form action="{{ route('admin.quotations.store') }}" method="POST" id="quotationForm">
        @csrf
        <input type="hidden" name="project_request_id" value="{{ $projectRequest->id }}">

        <div class="row">
            <!-- Items Column -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Items</h5>
                            <button type="button" class="btn btn-light btn-sm" onclick="addItem()">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                    </div>
                    <div class="card-body" id="items-container" style="background:#f8f9fa;">
                        <!-- Items will be added here -->
                    </div>
                </div>
            </div>

            <!-- Summary Column -->
            <div class="col-md-4">
                <!-- Summary Card -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tax (%)</label>
                            <input type="number" name="tax" class="form-control" value="10" step="0.01" min="0" onchange="calculateTotal()">
                        </div>
                        
                        <div class="form-group">
                            <label>Discount (Rp)</label>
                            <input type="number" name="discount" class="form-control" value="0" step="1000" min="0" onchange="calculateTotal()">
                        </div>
                        
                        <hr>
                        
                        <table class="table table-sm">
                            <tr>
                                <td>Subtotal:</td>
                                <td class="text-right"><strong id="subtotal-display">Rp 0</strong></td>
                            </tr>
                            <tr>
                                <td>Tax:</td>
                                <td class="text-right"><strong id="tax-display">Rp 0</strong></td>
                            </tr>
                            <tr>
                                <td>Discount:</td>
                                <td class="text-right text-danger"><strong id="discount-display">Rp 0</strong></td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>TOTAL:</strong></td>
                                <td class="text-right"><h5 id="total-display" class="text-primary mb-0">Rp 0</h5></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Settings Card -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Valid Until <span class="text-danger">*</span></label>
                            <input type="date" name="valid_until" class="form-control" required 
                                   value="{{ date('Y-m-d', strtotime('+14 days')) }}" 
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            <small class="text-muted">Default: 14 days</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <button type="submit" class="btn btn-success btn-block btn-lg mb-2">
                    <i class="fas fa-save"></i> Create Quotation
                </button>
                <a href="{{ route('admin.requests.show', $projectRequest->id) }}" class="btn btn-secondary btn-block">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Item Template -->
<template id="item-template">
    <div class="card mb-3 item-card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <strong>Item <span class="item-number"></span></strong>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="items[INDEX][item_name]" class="form-control" required placeholder="Enter item name">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="items[INDEX][category]" class="form-control">
                            <option value="material">Material</option>
                            <option value="labor">Labor</option>
                            <option value="equipment">Equipment</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="items[INDEX][quantity]" class="form-control item-quantity" 
                               value="1" step="0.01" min="0.01" required onchange="calculateItemSubtotal(this)">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Unit <span class="text-danger">*</span></label>
                        <input type="text" name="items[INDEX][unit]" class="form-control" value="pcs" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Unit Price (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="items[INDEX][unit_price]" class="form-control item-price" 
                               value="0" step="1000" min="0" required onchange="calculateItemSubtotal(this)">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="items[INDEX][description]" class="form-control" rows="2" placeholder="Optional description..."></textarea>
            </div>
            
            <div class="bg-light p-2 text-right rounded">
                <strong>Subtotal: <span class="item-subtotal">Rp 0</span></strong>
            </div>
        </div>
    </div>
</template>

<style>
/* Consistent Form Styling */
.form-control {
    height: 38px;
    font-size: 14px;
}

.form-control[type="date"],
.form-control[type="number"],
input.form-control,
select.form-control {
    height: 38px;
    padding: 6px 12px;
}

textarea.form-control {
    padding: 6px 12px;
}

label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

.form-group {
    margin-bottom: 15px;
}

.card {
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card-header {
    padding: 12px 15px;
    border-radius: 4px 4px 0 0 !important;
}

.card-header h5 {
    font-size: 16px;
    font-weight: 600;
}

.card-body {
    padding: 15px;
}

.item-card {
    border-left: 3px solid #4e73df;
}

.btn {
    font-size: 14px;
    font-weight: 600;
}

.btn-lg {
    padding: 12px 16px;
    font-size: 16px;
}

.table-sm td {
    padding: 6px 8px;
    font-size: 14px;
}

small.text-muted {
    font-size: 12px;
}

.breadcrumb {
    padding: 10px 15px;
    font-size: 14px;
}
</style>

<script>
let itemIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    addItem();
    addItem();
    addItem();
});

function addItem() {
    const template = document.getElementById('item-template');
    const container = document.getElementById('items-container');
    const clone = template.content.cloneNode(true);
    
    const html = clone.querySelector('.item-card').outerHTML.replace(/INDEX/g, itemIndex);
    container.insertAdjacentHTML('beforeend', html);
    
    updateItemNumbers();
    itemIndex++;
}

function removeItem(button) {
    button.closest('.item-card').remove();
    updateItemNumbers();
    calculateTotal();
}

function updateItemNumbers() {
    document.querySelectorAll('.item-number').forEach((el, index) => {
        el.textContent = index + 1;
    });
}

function calculateItemSubtotal(input) {
    const card = input.closest('.item-card');
    const quantity = parseFloat(card.querySelector('.item-quantity').value) || 0;
    const price = parseFloat(card.querySelector('.item-price').value) || 0;
    const subtotal = quantity * price;
    
    card.querySelector('.item-subtotal').textContent = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
    calculateTotal();
}

function calculateTotal() {
    let subtotal = 0;
    
    document.querySelectorAll('.item-card').forEach(card => {
        const quantity = parseFloat(card.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(card.querySelector('.item-price').value) || 0;
        subtotal += quantity * price;
    });
    
    const taxPercent = parseFloat(document.querySelector('input[name="tax"]').value) || 0;
    const discount = parseFloat(document.querySelector('input[name="discount"]').value) || 0;
    
    const taxAmount = subtotal * (taxPercent / 100);
    const total = subtotal + taxAmount - discount;
    
    document.getElementById('subtotal-display').textContent = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
    document.getElementById('tax-display').textContent = 'Rp ' + Math.round(taxAmount).toLocaleString('id-ID');
    document.getElementById('discount-display').textContent = 'Rp ' + Math.round(discount).toLocaleString('id-ID');
    document.getElementById('total-display').textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
}

document.getElementById('quotationForm').addEventListener('submit', function(e) {
    const itemsCount = document.querySelectorAll('.item-card').length;
    if (itemsCount === 0) {
        e.preventDefault();
        alert('Please add at least one item.');
        return false;
    }
});
</script>
@endsection
