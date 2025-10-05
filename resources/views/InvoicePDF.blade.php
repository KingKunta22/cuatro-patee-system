<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $po->orderNumber ?? 'PO' }}</title>
    <style>
        @page { 
            size: A4 portrait; 
            margin: 15mm; 
        }
        body { 
            font-family: 'Arial', sans-serif; 
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.4;
            font-size: 12px;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px solid #2C3747;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0 0 5px 0;
            font-weight: bold;
            color: #2C3747;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .subtitle {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        .company-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid #2C3747;
        }
        .company-sidebyside {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .company-from, .company-to {
            flex: 1;
            line-height: 1.5;
        }
        .company-from strong, .company-to strong {
            display: block;
            margin-bottom: 5px;
            color: #2C3747;
            font-size: 13px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-section {
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #fafafa;
        }
        .info-section h3 {
            margin: 0 0 8px 0;
            font-size: 13px;
            font-weight: bold;
            color: #2C3747;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 4px;
        }
        .info-item {
            margin: 4px 0;
            font-size: 11px;
        }
        .info-label {
            font-weight: 600;
            color: #374151;
        }
        .info-value {
            color: #6b7280;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
        }
        th {
            background: #2C3747 !important;
            color: white;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 6px;
            border: 1px solid #ddd;
        }
        td {
            padding: 8px 6px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .amount {
            font-weight: bold;
            color: #059669;
        }
        .total-row {
            background: #e8f4fd !important;
            font-weight: bold;
            border-top: 2px solid #2C3747;
        }
        .total-row td {
            font-size: 11px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
        }
        .authorization-section {
            text-align: right;
            margin-top: 25px;
            padding: 10px;
        }
        .authorization-label {
            font-size: 11px;
            font-weight: 600;
            color: #374151;
        }
        .order-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #1f2937;
            font-size: 13px;
        }
        .supplier-name {
            font-weight: 600;
            color: #1f2937;
        }
    </style>
</head>
<body>
    @php
        $po = $order ?? null;
        if (!$po && (isset($orderNumber) || isset($items) || isset($supplier))) {
            $po = (object) [
                'orderNumber' => $orderNumber ?? 'PO-DEMO',
                'created_at' => isset($date) ? $date : now(),
                'paymentTerms' => $paymentTerms ?? null,
                'deliveryDate' => $deliveryDate ?? null,
                'totalAmount' => $totalAmount ?? (is_iterable($items ?? []) ? collect($items)->sum(function($i){return ($i['totalAmount'] ?? 0);}) : 0),
                'orderStatus' => $orderStatus ?? null,
                'items' => collect($items ?? []),
                'supplier' => (object) ($supplier ?? []),
            ];
        }

        $supplier = $po->supplier ?? null;
        $supplierName = $supplier->supplierName ?? ($supplier['supplierName'] ?? '');
        $supplierAddress = $supplier->supplierAddress ?? ($supplier['supplierAddress'] ?? '');
        $supplierEmail = $supplier->supplierEmailAddress ?? ($supplier['supplierEmailAddress'] ?? '');
        $supplierContact = $supplier->supplierContactNumber ?? ($supplier['supplierContactNumber'] ?? '');

        $buyerName = "Cuatro Patee Pet Shop";
        $buyerAddress = "Don Jose Avila St., Capitol Site, Cebu City";
        $buyerEmail = "cuatropatee777@gmail.com";
        $buyerContact = "09670939434";

        $poNumber = $po->orderNumber ?? 'PO-XXXX';
        if (isset($po->created_at)) {
            $poDate = is_string($po->created_at)
                ? \Carbon\Carbon::parse($po->created_at)->format('F d, Y')
                : (method_exists($po->created_at, 'format') ? $po->created_at->format('F d, Y') : (string) $po->created_at);
        } else {
            $poDate = isset($date) ? (is_string($date) ? \Carbon\Carbon::parse($date)->format('F d, Y') : (method_exists($date, 'format') ? $date->format('F d, Y') : (string) $date)) : now()->format('F d, Y');
        }
        $paymentTerms = $po->paymentTerms ?? ($paymentTerms ?? '');
        $deliveryDate = $po->deliveryDate ?? ($deliveryDate ?? '');
        $items = collect($po->items ?? []);
    @endphp

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Purchase Order</h1>
            <div class="subtitle">Official Purchase Order Document</div>
        </div>

        <!-- Company Information - Side by Side -->
        <div class="company-info">
            <div class="company-sidebyside">
                <div class="company-from">
                    <strong>From:</strong>
                    {{ $buyerName }}<br>
                    {{ $buyerAddress }}<br>
                    {{ $buyerEmail }}<br>
                    {{ $buyerContact }}
                </div>
                <div class="company-to">
                    <strong>To:</strong>
                    {{ $supplierName }}<br>
                    @if($supplierAddress){{ $supplierAddress }}<br>@endif
                    @if($supplierEmail){{ $supplierEmail }}<br>@endif
                    @if($supplierContact){{ $supplierContact }}@endif
                </div>
            </div>
        </div>

        <!-- Order Information -->
        <div class="info-grid">
            <div class="info-section">
                <h3>Order Details</h3>
                <div class="info-item">
                    <span class="info-label">PO Number:</span>
                    <span class="info-value order-number">{{ $poNumber }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Order Date:</span>
                    <span class="info-value">{{ $poDate }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Delivery Date:</span>
                    <span class="info-value">{{ $deliveryDate ? \Carbon\Carbon::parse($deliveryDate)->format('F d, Y') : 'To be confirmed' }}</span>
                </div>
            </div>
            
            <div class="info-section">
                <h3>Payment & Delivery</h3>
                <div class="info-item">
                    <span class="info-label">Payment Terms:</span>
                    <span class="info-value">{{ $paymentTerms ?: 'To be confirmed' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Delivery Address:</span>
                    <span class="info-value">{{ $buyerAddress }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th width="8%" class="text-center">#</th>
                    <th width="52%" class="text-left">Product Description</th>
                    <th width="10%" class="text-center">Quantity</th>
                    <th width="15%" class="text-right">Unit Price</th>
                    <th width="15%" class="text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $it)
                    @php
                        $q = is_array($it) ? ($it['quantity'] ?? 0) : ($it->quantity ?? 0);
                        $name = is_array($it) ? ($it['productName'] ?? '') : ($it->productName ?? '');
                        $unit = is_array($it) ? ($it['unitPrice'] ?? 0) : ($it->unitPrice ?? 0);
                        $amt = is_array($it) ? (($it['totalAmount'] ?? ($q * $unit))) : ($it->totalAmount ?? ($q * $unit));
                        $measurement = is_array($it) ? ($it['itemMeasurement'] ?? '') : ($it->itemMeasurement ?? '');
                    @endphp
                    @if(($q ?? 0) || ($name !== '') || ($unit ?? 0) || ($amt ?? 0))
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $name }}</strong>
                            @if($measurement)
                            <br><small style="color: #6b7280;">Unit: {{ $measurement }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($q) }}</td>
                        <td class="text-right amount">PHP {{ number_format((float) $unit, 2) }}</td>
                        <td class="text-right amount">PHP {{ number_format((float) $amt, 2) }}</td>
                    </tr>
                    @endif
                @endforeach
                
                <!-- Total Row -->
                <tr class="total-row">
                    <td colspan="4" class="text-right"><strong>GRAND TOTAL</strong></td>
                    <td class="text-right amount">
                        PHP {{ number_format((float) ($po->totalAmount ?? $items->sum(function($i){ return is_array($i) ? ($i['totalAmount'] ?? 0) : ($i->totalAmount ?? 0); })), 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Authorization Section -->
        <div class="authorization-section">
            <div class="authorization-label">
                Authorised by: {{ auth()->user()->name ?? 'System' }}<br>
                Date: {{ now()->format('m-d-Y') }}
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="text-center">
                <strong>{{ $buyerName }}</strong> • {{ $buyerAddress }} • {{ $buyerEmail }} • {{ $buyerContact }}<br>
                Generated on {{ now()->format('F d, Y \a\t h:i A') }} • This is an computer-generated document
            </div>
        </div>
    </div>

    <script>
        // Auto-print when loaded
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