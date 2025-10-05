<!DOCTYPE html>
<html>
<head>
    <title>Inventory Report</title>
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
        .product-header {
            background: #e8f4fd !important;
            font-weight: bold;
            border-bottom: 2px solid #2C3747;
        }
        .product-batches-row {
            background: #ffffff;
            border-bottom: 3px solid #e5e7eb;
        }
        .batches-table {
            width: 100%;
            margin: 0;
            font-size: 9px;
            border: none;
        }
        .batches-table th {
            background: #4C7B8F !important;
            font-size: 9px;
            padding: 6px 8px;
            border: 1px solid #ddd;
        }
        .batches-table td {
            padding: 6px 8px;
            font-size: 9px;
            border: 1px solid #ddd;
        }
        .product-name {
            font-weight: 600;
            color: #1f2937;
        }
        .sku {
            font-family: 'Courier New', monospace;
            color: #6b7280;
            font-size: 9px;
        }
        .stock-high { color: #059669; font-weight: bold; }
        .stock-medium { color: #d97706; font-weight: bold; }
        .stock-low { color: #dc2626; font-weight: bold; }
        .expiring-soon { color: #dc2626; font-weight: bold; }
        .cost-price { color: #7c3aed; font-weight: bold; }
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
        <h1>Inventory Report</h1>
        <div class="meta">
            Inventory Management System
        </div>
    </div>
    
    <!-- Report Information -->
    <div class="report-info">
        <p><strong>Report Period:</strong> {{ $timePeriod === 'all' ? 'All Time' : ucfirst(str_replace('last', 'Last ', $timePeriod)) }}</p>
        <p><strong>Generated On:</strong> {{ now()->format('F d, Y \a\t h:i A') }}</p>
        <p><strong>Total Products:</strong> {{ $products->count() }}</p>
    </div>

    <!-- Inventory Table -->
    <table>
        <thead>
            <tr>
                <th width="20%">Product</th>
                <th width="10%">SKU</th>
                <th width="12%">Brand</th>
                <th width="12%">Category</th>
                <th width="8%">Total Stock</th>
                <th width="38%">Batches</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            @php
                $totalStock = $product->batches->sum('quantity');
                $stockClass = $totalStock > 10 ? 'stock-high' : ($totalStock > 0 ? 'stock-medium' : 'stock-low');
            @endphp
            
            <!-- Product Header Row -->
            <tr class="product-header">
                <td class="product-name">{{ $product->productName }}</td>
                <td class="sku">{{ $product->productSKU ?? 'N/A' }}</td>
                <td>{{ $product->brand->brandName ?? 'N/A' }}</td>
                <td>{{ $product->category->categoryName ?? 'N/A' }}</td>
                <td class="{{ $stockClass }}">{{ $totalStock }}</td>
                <td>
                    <!-- Batches will be shown in the same cell -->
                    <table class="batches-table">
                        <thead>
                            <tr>
                                <th width="20%">Batch No.</th>
                                <th width="15%">Quantity</th>
                                <th width="20%">Cost Price</th>
                                <th width="25%">Expiration</th>
                                <th width="20%">Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->batches as $batch)
                            @php
                                $isExpiring = $batch->expiration_date && \Carbon\Carbon::parse($batch->expiration_date)->lt(now()->addDays(30));
                            @endphp
                            <tr>
                                <td>{{ $batch->batch_number ?? 'N/A' }}</td>
                                <td>{{ $batch->quantity }}</td>
                                <td class="cost-price">₱{{ number_format($batch->cost_price, 2) }}</td>
                                <td class="{{ $isExpiring ? 'expiring-soon' : '' }}">
                                    {{ $batch->expiration_date ? \Carbon\Carbon::parse($batch->expiration_date)->format('M d, Y') : 'N/A' }}
                                </td>
                                <td>{{ $batch->purchaseOrder->orderNumber ?? 'Manual' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
            <!-- Spacing row -->
            <tr class="product-batches-row">
                <td colspan="6" style="padding: 8px; background: #f8f9fa;"></td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="no-data">
                        No inventory products found for the selected period.
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