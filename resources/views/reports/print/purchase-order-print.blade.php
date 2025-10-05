<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order Report</title>
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
            padding: 12px 10px;
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
        .po-header {
            background: #e8f4fd !important;
            font-weight: bold;
            border-bottom: 2px solid #2C3747;
        }
        .po-items-row {
            background: #ffffff;
            border-bottom: 3px solid #e5e7eb;
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
        .order-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #1f2937;
        }
        .supplier-name {
            font-weight: 500;
            color: #1f2937;
        }
        .status-delivered { color: #059669; font-weight: bold; }
        .status-confirmed { color: #2563eb; font-weight: bold; }
        .status-pending { color: #d97706; font-weight: bold; }
        .status-cancelled { color: #dc2626; font-weight: bold; }
        .notes-section {
            background: #fefce8;
            padding: 10px;
            margin: 8px 0;
            border-radius: 4px;
            border-left: 3px solid #f59e0b;
        }
        .note-item {
            margin: 4px 0;
            padding: 4px 0;
            border-bottom: 1px solid #fef3c7;
        }
        .note-item:last-child {
            border-bottom: none;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Purchase Order Report</h1>
        <div class="meta">
            Inventory Management System
        </div>
    </div>
    
    <!-- Report Information -->
    <div class="report-info">
        <p><strong>Report Period:</strong> {{ $timePeriod === 'all' ? 'All Time' : ucfirst(str_replace('last', 'Last ', $timePeriod)) }}</p>
        <p><strong>Generated On:</strong> {{ now()->format('F d, Y \a\t h:i A') }}</p>
        <p><strong>Total Purchase Orders:</strong> {{ $purchaseOrders->count() }}</p>
    </div>

    <!-- Purchase Orders Table -->
    <table>
        <thead>
            <tr>
                <th width="12%">Order Number</th>
                <th width="15%">Supplier</th>
                <th width="10%">Order Date</th>
                <th width="12%">Status</th>
                <th width="8%">Total Items</th>
                <th width="43%">Order Items</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseOrders as $po)
            @php
                $delivery = $po->deliveries->first();
                $totalItems = $po->items->sum('quantity');
                $goodItemsCount = 0;
                $defectiveCount = 0;
                
                foreach ($po->items as $item) {
                    $itemGoodCount = \App\Models\ProductBatch::where('purchase_order_item_id', $item->id)->sum('quantity');
                    $goodItemsCount += $itemGoodCount;
                    $defectiveCount += $item->badItems->sum('item_count');
                }
            @endphp
            
            <!-- PO Header Row -->
            <tr class="po-header">
                <td class="order-number">{{ $po->orderNumber }}</td>
                <td class="supplier-name">{{ $po->supplier->supplierName ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($po->created_at)->format('M d, Y') }}</td>
                <td class="status-{{ strtolower($delivery->orderStatus ?? 'pending') }}">
                    {{ $delivery->orderStatus ?? 'Pending' }}
                </td>
                <td>{{ $totalItems }} items</td>
                <td>
                    <!-- Items will be shown in the same cell -->
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th width="45%">Product</th>
                                <th width="12%">Ordered</th>
                                <th width="12%">Good</th>
                                <th width="12%">Defective</th>
                                <th width="19%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($po->items as $item)
                            @php
                                $itemGoodCount = \App\Models\ProductBatch::where('purchase_order_item_id', $item->id)->sum('quantity');
                                $itemDefectiveCount = $item->badItems->sum('item_count');
                                $itemDefectType = $item->badItems->first() ? $item->badItems->first()->quality_status : '';
                            @endphp
                            <tr>
                                <td>{{ $item->productName }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $itemGoodCount }}</td>
                                <td>{{ $itemDefectiveCount ?: '0' }}</td>
                                <td>
                                    @if($itemDefectiveCount > 0)
                                        <span style="color: #dc2626; font-weight: bold;">Needs Review</span>
                                    @else
                                        <span style="color: #059669; font-weight: bold;">Completed</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Notes Section -->
                    @if($po->notes->count() > 0)
                    <div class="notes-section">
                        <strong>Notes:</strong>
                        @foreach($po->notes as $note)
                        <div class="note-item">
                            <small>{{ $note->created_at->format('M d, Y') }}: {{ $note->note }}</small>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </td>
            </tr>
            <!-- Spacing row -->
            <tr class="po-items-row">
                <td colspan="6" style="padding: 8px; background: #f8f9fa;"></td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="no-data">
                        No purchase orders found for the selected period.
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
        window.onload = function() {
            window.focus();
            setTimeout(function() {
                window.print();
                setTimeout(function() {
                    window.close();
                }, 500);
            }, 500);
        };
    </script>
</body>
</html>