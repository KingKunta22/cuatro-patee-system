<!DOCTYPE html>
<html>
<head>
    <title>Product Movements Report</title>
    <style>
        @page { 
            size: A4 landscape; 
            margin: 10mm; 
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            color: #111827;
            margin: 0;
            padding: 0;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px;
            border-bottom: 2px solid #4C7B8F;
            padding-bottom: 15px;
        }
        .header h1 { 
            font-size: 20px; 
            margin: 0 0 8px 0;
            font-weight: bold;
            color: #111827;
        }
        .header .meta { 
            font-size: 11px; 
            color: #6b7280;
        }
        .stats-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .stat-box {
            flex: 1;
            padding: 12px;
            border-radius: 6px;
            color: white;
            text-align: left;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        .stat-label {
            font-size: 11px;
            margin: 0;
            opacity: 0.9;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 10px;
            page-break-inside: auto;
        }
        th, td { 
            border: 1px solid #d1d5db; 
            padding: 6px 8px; 
            text-align: left;
        }
        th { 
            background: #4C7B8F !important; 
            color: white; 
            font-weight: bold;
            position: sticky;
            top: 0;
        }
        tr { 
            page-break-inside: avoid;
            page-break-after: auto;
        }
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .negative { 
            color: #dc2626;
            font-weight: bold;
        }
        .positive { 
            color: #16a34a;
            font-weight: bold;
        }
        .type-badge {
            font-size: 9px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 10px;
            text-transform: uppercase;
        }
        .type-inflow {
            background: #dcfce7;
            color: #166534;
        }
        .type-outflow {
            background: #fef2f2;
            color: #991b1b;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Product Movements Report</h1>
        <div class="meta">
            Generated: {{ now()->format('M d, Y h:i A') }} | 
            Period: {{ $timePeriod === 'all' ? 'All Time' : ucfirst(str_replace('last', 'Last ', $timePeriod)) }} |
            Total Records: {{ count($movements) }}
        </div>
    </div>
    
    <!-- Stats Summary -->
    <div class="stats-container">
        <div class="stat-box" style="background: #5C717B;">
            <p class="stat-value">{{ number_format($totalStockIn) }}</p>
            <p class="stat-label">Total Stock In</p>
        </div>
        <div class="stat-box" style="background: #2C3747;">
            <p class="stat-value">{{ number_format($totalStockOut) }}</p>
            <p class="stat-label">Total Stock Out</p>
        </div>
        <div class="stat-box" style="background: #059669;">
            <p class="stat-value">₱{{ number_format($totalRevenue, 2) }}</p>
            <p class="stat-label">Total Revenue</p>
        </div>
        <div class="stat-box" style="background: #dc2626;">
            <p class="stat-value">₱{{ number_format($totalCost, 2) }}</p>
            <p class="stat-label">Total Cost</p>
        </div>
        <div class="stat-box" style="background: {{ $totalProfit >= 0 ? '#059669' : '#dc2626' }};">
            <p class="stat-value">₱{{ number_format($totalProfit, 2) }}</p>
            <p class="stat-label">Total Profit</p>
        </div>
    </div>

    <!-- Movements Table -->
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference No.</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Type</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
            <tr>
                <td>{{ \Carbon\Carbon::parse($movement['date'])->format('M d, Y') }}</td>
                <td>{{ $movement['reference_number'] }}</td>
                <td>{{ $movement['product_name'] }}</td>
                <td class="{{ $movement['quantity'] < 0 ? 'negative' : 'positive' }}">
                    {{ $movement['quantity'] > 0 ? '+' : '' }}{{ $movement['quantity'] }}
                </td>
                <td>
                    <span class="type-badge {{ $movement['type'] === 'inflow' ? 'type-inflow' : 'type-outflow' }}">
                        {{ ucfirst($movement['type']) }}
                    </span>
                </td>
                <td>{{ $movement['remarks'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">
                    No product movements found for the selected period.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated by Inventory Management System | Page 1 of 1
    </div>

    <script>
        // Auto-print and close when loaded
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