<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <style>
        @page { 
            size: A4 landscape; 
            margin: 15mm; 
        }
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 12px; 
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 25px;
            border-bottom: 3px solid #2C3747;
            padding-bottom: 15px;
        }
        .header h1 { 
            font-size: 24px; 
            margin: 0 0 5px 0;
            font-weight: bold;
            color: #2C3747;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .meta { 
            font-size: 12px; 
            color: #666;
            margin-bottom: 3px;
        }
        .report-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #2C3747;
        }
        .report-info p {
            margin: 2px 0;
            font-size: 11px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 10px;
            page-break-inside: auto;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px 10px; /* Increased padding for better spacing */
            text-align: left;
            vertical-align: top;
        }
        th { 
            background: #2C3747 !important; 
            color: white; 
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr { 
            page-break-inside: avoid;
            page-break-after: auto;
        }
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .sale-header {
            background: #e8f4fd !important;
            font-weight: bold;
            border-bottom: 2px solid #2C3747; /* Thicker border for separation */
        }
        .sale-items-row {
            background: #ffffff;
            border-bottom: 3px solid #e5e7eb; /* Space between sales */
        }
        .items-table {
            width: 100%;
            margin: 0;
            font-size: 9px;
            border: none;
        }
        .items-table th {
            background: #4C7B8F !important;
            font-size: 9px;
            padding: 6px 8px;
            border: 1px solid #ddd;
        }
        .items-table td {
            padding: 6px 8px;
            font-size: 9px;
            border: 1px solid #ddd;
        }
        .items-table tr:last-child td {
            border-bottom: 2px solid #4C7B8F; /* Visual separation for items */
        }
        .invoice-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #1f2937;
        }
        .amount {
            font-weight: bold;
            color: #059669;
        }
        .cashier {
            font-style: italic;
            color: #6b7280;
        }
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
        }
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
            font-style: italic;
            background: #f9fafb;
            border-radius: 6px;
            margin: 20px 0;
        }
        .items-column {
            padding: 8px !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report</h1>
        <div class="meta">
            Inventory Management System
        </div>
    </div>
    
    <!-- Report Information -->
    <div class="report-info">
        <p><strong>Report Period:</strong> {{ $timePeriod === 'all' ? 'All Time' : ucfirst(str_replace('last', 'Last ', $timePeriod)) }}</p>
        <p><strong>Generated On:</strong> {{ now()->format('F d, Y \a\t h:i A') }}</p>
        <p><strong>Total Sales:</strong> {{ $sales->count() }} transactions</p>
        <p><strong>Total Revenue:</strong> PHP {{ number_format($totalRevenue, 2) }} | 
           <strong>Total Cost:</strong> PHP {{ number_format($totalCost, 2) }} | 
           <strong>Total Profit:</strong> PHP {{ number_format($totalProfit, 2) }}</p>
    </div>

    <!-- Sales Table -->
    <table>
        <thead>
            <tr>
                <th width="12%">Invoice No.</th>
                <th width="10%">Date</th>
                <th width="15%">Processed By</th>
                <th width="8%">Items Count</th>
                <th width="10%">Total Amount</th>
                <th width="45%">Items Sold</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <!-- Sale Header Row -->
            <tr class="sale-header">
                <td class="invoice-number">{{ $sale->invoice_number }}</td>
                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}</td>
                <td class="cashier">{{ $sale->user->name ?? 'System' }}</td>
                <td>{{ $sale->items->count() }} items</td>
                <td class="amount">PHP {{ number_format($sale->total_amount, 2) }}</td>
                <td class="items-column">
                    <!-- Items will be shown in the same cell but structured -->
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th width="60%">Product</th>
                                <th width="15%">Qty</th>
                                <th width="25%">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>PHP {{ number_format($item->unit_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
            <!-- Spacing row for better separation -->
            <tr class="sale-items-row">
                <td colspan="6" style="padding: 8px; background: #f8f9fa;"></td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="no-data">
                        No sales records found for the selected period.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated by Inventory Management System • Confidential Business Document • Page 1 of 1
    </div>

    <script>
        // Auto-print when loaded
        window.onload = function() {
            window.focus();
            setTimeout(function() {
                window.print();
                // Close window after print
                setTimeout(function() {
                    window.close();
                }, 500);
            }, 500);
        };
    </script>
</body>
</html>