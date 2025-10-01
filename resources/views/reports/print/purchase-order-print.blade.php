<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order Report</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4C7B8F; padding-bottom: 15px; }
        .header h1 { font-size: 20px; margin: 0 0 8px 0; font-weight: bold; }
        .header .meta { font-size: 11px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; page-break-inside: auto; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #4C7B8F !important; color: white; font-weight: bold; }
        .po-details { margin: 10px 0; padding: 10px; background: #f9fafb; border-radius: 4px; }
        .items-table { width: 100%; margin: 5px 0; font-size: 9px; }
        .notes-section { margin: 10px 0; padding: 8px; background: #f3f4f6; border-radius: 4px; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Purchase Order Report</h1>
        <div class="meta">
            Generated: {{ now()->format('M d, Y h:i A') }} | 
            Period: {{ $timePeriod === 'all' ? 'All Time' : ucfirst(str_replace('last', 'Last ', $timePeriod)) }} |
            Total POs: {{ $purchaseOrders->count() }}
        </div>
    </div>

    <!-- Purchase Orders with Full Details -->
    @foreach($purchaseOrders as $po)
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
    
    <div class="po-details">
        <!-- PO Header -->
        <table>
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Delivery Status</th>
                    <th>Total Items</th>
                    <th>Good Items</th>
                    <th>Defective Items</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $po->orderNumber }}</td>
                    <td>{{ $po->supplier->supplierName ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($po->created_at)->format('M d, Y') }}</td>
                    <td>{{ $delivery->orderStatus ?? 'Pending' }}</td>
                    <td>{{ $totalItems }}</td>
                    <td>{{ $goodItemsCount }}</td>
                    <td>{{ $defectiveCount }}</td>
                </tr>
            </tbody>
        </table>
        
        <!-- PO Items -->
        <h4 style="margin: 10px 0 5px 0; font-size: 11px;">Items:</h4>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Ordered Qty</th>
                    <th>Good Qty</th>
                    <th>Defective Qty</th>
                    <th>Defect Type</th>
                    <th>Status</th>
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
                    <td>{{ $itemDefectiveCount }}</td>
                    <td>{{ $itemDefectType ?: '-' }}</td>
                    <td>
                        @if($itemDefectiveCount > 0)
                            Needs Review
                        @else
                            Completed
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Notes Section -->
        @if($po->notes->count() > 0)
        <div class="notes-section">
            <h4 style="margin: 0 0 5px 0; font-size: 11px;">Notes:</h4>
            @foreach($po->notes as $note)
            <p style="margin: 2px 0; font-size: 9px;">
                <strong>{{ $note->created_at->format('M d, Y h:i A') }}:</strong> {{ $note->note }}
            </p>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach

    <div class="footer">
        Generated by Inventory Management System | Page 1 of 1
    </div>
</body>
</html>