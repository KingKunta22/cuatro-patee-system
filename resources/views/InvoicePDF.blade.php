<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Purchase Order</title>
	<style>
		@page { 
			size: portrait; 
			margin: 0.2in; }

		html, body { 
			margin: 0.2in; 
			padding: 0; 
			width: 100%; 
		}
		* { 
			box-sizing: border-box; 
			font-family: 'Poppins', sans-serif; 
		}
		body { 
			font-family: 'Poppins', sans-serif; 
			padding: 0;
			margin: 0;
		}
		table { 
			width: 100%; 
			border-collapse: collapse; 
		}
		.border { 
			border: 1px solid #e5e7eb; 
		}
		.text-right { 
			text-align: right; 
		}
		.text-left { 
			text-align: left; 
		}
		.text-center { 
			text-align: center; 
		}
		.text-xs { 
			font-size: 0.60rem; 
		}
		.text-sm { 
			font-size: 0.8rem; 
		}
		.font-semibold { 
			font-weight: 600; 
		}
		.uppercase { 
			text-transform: uppercase; 
		}
		.leading-tight { 
			line-height: 1.15; 
		}
		.grid { 
			display: grid; 
		}
		.grid-cols-2 { 
			grid-template-columns: 1fr 1fr; 
		}
		.items-start { 
			align-items: flex-start; 
		}
		.gap-2 { 
			gap: 0.5rem; 
		}
		.p-2 { 
			padding: 0.5rem; 
		}
		.px-2 { 
			padding-left: 0.5rem; 
			padding-right: 0.5rem; 
		}
		.py-1 { 
			padding-top: 0.25rem; 
			padding-bottom: 0.25rem; 
		}
		.mt-2 { 
			margin-top: 0.5rem; 
		}
		.mb-1 { 
			margin-bottom: 0.25rem; 
		}
		.mb-2 { 
			margin-bottom: 0.5rem; 
		}
		.w-full {
			width: 100%;
		}
		.flex {
			display: flex;
		}
		.justify-between {
			justify-content: space-between;
		}
	</style>
</head>
<body class="text-sm leading-tight">
@php
	// Normalize incoming data so the view accepts both shapes
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

	$buyerName = "Cuatro Patee";
	$buyerAddress = "Don Jose Avila St., Capitol Site, Cebu City";
	$buyerEmail = "cuatropatee777@gmail.com";
	$buyerContact = "09670939434";

	$poNumber = $po->orderNumber ?? 'PO-XXXX';
	if (isset($po->created_at)) {
		$poDate = is_string($po->created_at)
			? $po->created_at
			: (method_exists($po->created_at, 'format') ? $po->created_at->format('m-d-Y') : (string) $po->created_at);
	} else {
		$poDate = isset($date) ? (is_string($date) ? $date : (method_exists($date, 'format') ? $date->format('m-d-Y') : (string) $date)) : now()->format('m-d-Y');
	}
	$paymentTerms = $po->paymentTerms ?? ($paymentTerms ?? '');
	$deliveryDate = $po->deliveryDate ?? ($deliveryDate ?? '');
	$items = collect($po->items ?? []);
	$maxRows = 5;
@endphp
	<div class="w-full">
		<div class="text-center mb-2">
			<div class="uppercase font-semibold" style="font-size: 1.1rem;">Purchase Order</div>
		</div>

		<!-- Top Section: Supplier (left) and Buyer (right) -->
		<div class="grid grid-cols-2 gap-2">
            <div>
                <div class="font-semibold mb-1">Supplier</div>
                <div class="text-xs">{{ $supplierName }}</div>
                @if($supplierAddress)
                    <div class="text-xs">{{ $supplierAddress }}</div>
                @endif
                @if($supplierEmail)
                    <div class="text-xs">{{ $supplierEmail }}</div>
                @endif
                @if($supplierContact)
                    <div class="text-xs">{{ $supplierContact }}</div>
                @endif
				<div class="mt-2 text-xs"><span class="font-semibold">PO Number</span>: {{ $poNumber }}</div>
            </div>

            <div class="text-right">
                <div class="font-semibold mb-1">Buyer</div>
                <div class="text-xs">{{ $buyerName }} Pet Shop</div>
				<div class="text-xs">{{ $buyerAddress }}</div>
				<div class="text-xs">{{ $buyerEmail }}</div>
				<div class="text-xs">{{ $buyerContact }}</div>
				<div class="text-xs"><span class="font-semibold">PO Date</span>: {{ $poDate }}</div>
            </div>
        </div>

		<!-- Items Table -->
		<div class="mt-2">
			<table class="text-xs">
				<thead>
					<tr>
						<th class="border p-2 text-left" style="width: 12%;">Quantity</th>
						<th class="border p-2 text-left">Product Name</th>
						<th class="border p-2 text-right" style="width: 18%;">Unit Price</th>
						<th class="border p-2 text-right" style="width: 18%;">Amount</th>
					</tr>
				</thead>
				<tbody>
					@foreach($items->take($maxRows) as $it)
						@php
							$q = is_array($it) ? ($it['quantity'] ?? 0) : ($it->quantity ?? 0);
							$name = is_array($it) ? ($it['productName'] ?? '') : ($it->productName ?? '');
							$unit = is_array($it) ? ($it['unitPrice'] ?? 0) : ($it->unitPrice ?? 0);
							$amt = is_array($it) ? (($it['totalAmount'] ?? ($q * $unit))) : ($it->totalAmount ?? ($q * $unit));
						@endphp
						@if(($q ?? 0) || ($name !== '') || ($unit ?? 0) || ($amt ?? 0))
						<tr>
							<td class="border px-2 py-1">{{ $q }}</td>
							<td class="border px-2 py-1">{{ $name }}</td>
							<td class="border px-2 py-1 text-right">PHP {{ number_format((float) $unit, 2) }}</td>
							<td class="border px-2 py-1 text-right">PHP {{ number_format((float) $amt, 2) }}</td>
						</tr>
						@endif
					@endforeach
					<tr>
						<td class="border px-2 py-1 font-semibold" colspan="3">Total</td>
						<td class="border px-2 py-1 text-right font-semibold">
							PHP {{ number_format((float) ($po->totalAmount ?? $items->sum(function($i){ return is_array($i) ? ($i['totalAmount'] ?? 0) : ($i->totalAmount ?? 0); })), 2) }}
						</td>
					</tr>
				</tbody>
			</table>

			<!-- Bottom Section: Delivery info (left) and Payment info (right) -->
			<div class="grid grid-cols-2 gap-2 mt-2">
				<!-- Left Side: Delivery Address and Delivery Date -->
				<div class="text-left">
					<div class="text-xs font-semibold">Delivery Address</div>
					<div class="text-xs">Don Jose Avila St., Capitol Site, Cebu City</div>
					<div class="mt-2 text-xs"><span class="font-semibold">Delivery Date</span>: {{ $deliveryDate ?: '—' }}</div>
				</div>

				<!-- Right Side: Payment Terms, Authorized by, and Date -->
				<div class="text-right">
					<div class="text-xs"><span class="font-semibold">Payment Terms</span>: {{ $paymentTerms ?: '—' }}</div>
					<div class="mt-2 text-xs"><span class="font-semibold">Authorised by</span>: {{ auth()->user()->name ?? '—' }}</div>
					<div class="text-xs"><span class="font-semibold">Date</span>: {{ now()->format('m-d-Y') }}</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>