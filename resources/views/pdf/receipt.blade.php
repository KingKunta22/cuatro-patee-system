<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $sale->invoice_number }}</title>
    <style>
        body { 
            font-family: monospace, Arial, sans-serif; 
            margin: 30px; 
            font-size: 14px;
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .store-name {
            font-weight: bold;
            font-size: 40px;
            margin-bottom: 5px;
        }
        .store-address {
            font-size: 13px;
            margin-bottom: 15px;
        }
        .receipt-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .invoice-info { 
            margin-bottom: 20px; 
        }
        .invoice-info p { 
            margin: 2px 0; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        table th, table td { 
            padding: 8px; 
            text-align: left; 
            border-bottom: 1px solid #000;
        }
        table th { 
            font-weight: bold;
            border-bottom: 2px solid #000; 
        }
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 12px; 
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="store-name">CUATRO PATEE</div>
        <div class="store-address">Don Jose Avila St., Capitol Site, Cebu City</div>
        <div class="receipt-title">SALES RECEIPT</div>
        <div>Invoice: {{ $sale->invoice_number }}</div>
    </div>
    
    <div class="invoice-info">
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}</p>
        <p><strong>Processed By:</strong> {{ $sale->processed_by }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Product</th>
                <th style="width: 15%;">Qty</th>
                <th style="width: 20%;">Price</th>
                <th style="width: 25%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>₱{{ number_format($item->unit_price, 2) }}</td>
                <td>₱{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Thank you for shopping at Cuatro Patee!</p>
        <p>Generated on: {{ now()->format('M d, Y h:i A') }}</p>
    </div>
</body>
</html>
