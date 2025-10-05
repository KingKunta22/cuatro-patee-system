<!DOCTYPE html>
<html>
<head>
    <title>Product Movements Report</title>
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
            margin-top: 10px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px 10px; 
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
        tbody tr:hover {
            background-color: #e9ecef;
        }
        .quantity-in {
            color: #059669;
            font-weight: bold;
            font-size: 11px;
        }
        .quantity-out {
            color: #dc2626;
            font-weight: bold;
            font-size: 11px;
        }
        .type-badge {
            font-size: 9px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 12px;
            text-transform: uppercase;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }
        .type-inflow {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .type-outflow {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .product-name {
            font-weight: 500;
            color: #1f2937;
        }
        .reference-number {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            color: #6b7280;
        }
        .remarks {
            font-style: italic;
            color: #6b7280;
            font-size: 9px;
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
        .date-cell {
            white-space: nowrap;
            font-size: 9px;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Product Movements Report</h1>
        <div class="meta">
            Inventory Management System
        </div>
    </div>
    
    <!-- Report Information -->
    <div class="report-info">
        <p><strong>Report Period:</strong> {{ $timePeriod === 'all' ? 'All Time' : ucfirst(str_replace('last', 'Last ', $timePeriod)) }}</p>
        <p><strong>Generated On:</strong> {{ now()->format('F d, Y \a\t h:i A') }}</p>
        <p><strong>Total Movements:</strong> {{ count($movements) }} records</p>
    </div>

    <!-- Movements Table -->
    <table>
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="15%">Reference Number</th>
                <th width="28%">Product Name</th>
                <th width="10%">Quantity</th>
                <th width="10%">Type</th>
                <th width="25%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
            <tr>
                <td class="date-cell">
                    {{ \Carbon\Carbon::parse($movement['date'])->format('M d, Y') }}<br>
                    <small>{{ \Carbon\Carbon::parse($movement['date'])->format('h:i A') }}</small>
                </td>
                <td class="reference-number">
                    {{ $movement['reference_number'] }}
                </td>
                <td class="product-name">
                    {{ $movement['product_name'] }}
                </td>
                <td class="{{ $movement['quantity'] < 0 ? 'quantity-out' : 'quantity-in' }}">
                    {{ $movement['quantity'] > 0 ? '+' : '' }}{{ number_format($movement['quantity']) }}
                </td>
                <td>
                    <span class="type-badge {{ $movement['type'] === 'inflow' ? 'type-inflow' : 'type-outflow' }}">
                        {{ ucfirst($movement['type']) }}
                    </span>
                </td>
                <td class="remarks">
                    {{ $movement['remarks'] }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="no-data">
                        No product movements found for the selected period.
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
                // Optional: close window after print
                setTimeout(function() {
                    window.close();
                }, 500);
            }, 500);
        };
    </script>
</body>
</html>