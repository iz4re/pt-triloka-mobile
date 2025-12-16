<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            color: #333;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #6C5DD3;
        }
        .company-info h1 {
            color: #6C5DD3;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }
        .invoice-number {
            font-size: 18px;
            color: #666;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .client-info, .invoice-info {
            width: 48%;
        }
        h3 {
            font-size: 14px;
            color: #6C5DD3;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-row {
            margin-bottom: 5px;
            font-size: 14px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        thead {
            background-color: #6C5DD3;
            color: white;
        }
        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        th.right {
            text-align: right;
        }
        tbody tr {
            border-bottom: 1px solid #ddd;
        }
        td {
            padding: 12px;
        }
        td.right {
            text-align: right;
        }
        .totals {
            width: 350px;
            margin-left: auto;
            margin-bottom: 30px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        .total-row.grand {
            border-top: 2px solid #333;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 18px;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-paid {
            background-color: #10B981;
            color: white;
        }
        .status-unpaid {
            background-color: #F59E0B;
            color: white;
        }
        .status-overdue {
            background-color: #EF4444;
            color: white;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #6C5DD3;
        }
        .notes h3 {
            margin-bottom: 10px;
        }
        @media print {
            body {
                padding: 20px;
            }
            .no-print {
                display: none;
            }
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background-color: #6C5DD3;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        .print-button:hover {
            background-color: #5a4db8;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Print Invoice</button>

    <!-- Invoice Header -->
    <div class="invoice-header">
        <div class="company-info">
            <h1>CV Triloka</h1>
            <p>Financial & Operational System</p>
        </div>
        <div class="invoice-title">
            <h2>INVOICE</h2>
            <p class="invoice-number">{{ $invoice->invoice_number }}</p>
            <p>
                <span class="status-badge status-{{ $invoice->status }}">
                    {{ strtoupper($invoice->status) }}
                </span>
            </p>
        </div>
    </div>

    <!-- Invoice Details -->
    <div class="invoice-details">
        <div class="client-info">
            <h3>Bill To:</h3>
            <div class="info-row"><strong>{{ $invoice->klien->name }}</strong></div>
            @if($invoice->klien->company_name)
                <div class="info-row">{{ $invoice->klien->company_name }}</div>
            @endif
            <div class="info-row">{{ $invoice->klien->email }}</div>
            <div class="info-row">{{ $invoice->klien->phone ?? '-' }}</div>
            @if($invoice->klien->address)
                <div class="info-row">{{ $invoice->klien->address }}</div>
            @endif
        </div>
        <div class="invoice-info">
            <h3>Invoice Details:</h3>
            <div class="info-row">
                <span class="info-label">Invoice Date:</span>
                {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d F Y') }}
            </div>
            <div class="info-row">
                <span class="info-label">Due Date:</span>
                {{ \Carbon\Carbon::parse($invoice->due_date)->format('d F Y') }}
            </div>
            @if($invoice->paid_at)
                <div class="info-row">
                    <span class="info-label">Paid On:</span>
                    {{ \Carbon\Carbon::parse($invoice->paid_at)->format('d F Y') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Invoice Items -->
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
        </div>
        @if($invoice->tax > 0)
            <div class="total-row">
                <span>Tax:</span>
                <span>Rp {{ number_format($invoice->tax, 0, ',', '.') }}</span>
            </div>
        @endif
        @if($invoice->discount > 0)
            <div class="total-row">
                <span>Discount:</span>
                <span style="color: #EF4444;">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="total-row grand">
            <span>Total:</span>
            <span>Rp {{ number_format($invoice->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Notes -->
    @if($invoice->notes)
        <div class="notes">
            <h3>Notes:</h3>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif
</body>
</html>
