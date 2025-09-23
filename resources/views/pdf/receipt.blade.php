<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .invoice-info { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .totals { float: right; width: 300px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SALES RECEIPT</h1>
        <h2>Invoice: {{ $sale->invoice_number }}</h2>
    </div>
    
    <div class="invoice-info">
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y h:i A') }}</p>
        <p><strong>Customer:</strong> {{ $sale->customer_name }}</p>
        <p><strong>Processed By:</strong> System</p>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Batch</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->productBatch->batch_number ?? 'N/A' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>₱{{ number_format($item->unit_price, 2) }}</td>
                <td>₱{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="totals">
        <p><strong>Subtotal: ₱{{ number_format($sale->total_amount, 2) }}</strong></p>
        <p><strong>Cash Received: ₱{{ number_format($sale->cash_received, 2) }}</strong></p>
        <p><strong>Change: ₱{{ number_format($sale->change, 2) }}</strong></p>
    </div>
    
    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Generated on: {{ now()->format('M d, Y h:i A') }}</p>
    </div>
</body>
</html>