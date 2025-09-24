<section class="border w-full rounded-md border-solid border-black my-3 shadow-sm">
    <table class="w-full table-fixed">
        <thead class="rounded-lg bg-main text-white px-4 py-3">
            <tr class="rounded-lg">
                <th class="px-4 py-3 text-center">PO Number</th>
                <th class="px-4 py-3 text-center">Supplier</th>
                <th class="px-4 py-3 text-center">Date</th>
                <th class="px-4 py-3 text-center">Total Items</th>
                <th class="px-4 py-3 text-center">Good Items</th>
                <th class="px-4 py-3 text-center">Bad Items</th>
                <th class="px-4 py-3 text-center truncate" title="Delivery Status">Status</th>
                <th class="px-4 py-3 text-center truncate" title="Report Status">Report</th>
                <th class="px-4 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($purchaseOrders as $po)
                @php
                    // Calculate totals for this PO using batches instead of inventory
                    $totalItems = $po->items->sum('quantity');
                    $goodItemsCount = 0;
                    $defectiveCount = 0;
                    $hasDefective = false;
                    $hasNotes = $po->notes->count() > 0;
                    
                    foreach ($po->items as $item) {
                        // Calculate good items from batches linked to this PO item
                        $itemGoodCount = $item->productBatches->sum('quantity');
                        $goodItemsCount += $itemGoodCount;
                        
                        $itemDefectiveCount = $item->badItems->sum('item_count');
                        $defectiveCount += $itemDefectiveCount;
                        
                        if ($itemDefectiveCount > 0) {
                            $hasDefective = true;
                        }
                    }

                    // Get delivery status
                    $delivery = $po->deliveries->first();
                    $deliveryStatus = $delivery ? $delivery->orderStatus : 'Pending';
                    
                    // Check if confirmed PO is delayed
                    $isDelayed = false;
                    if ($deliveryStatus === 'Confirmed') {
                        $expectedDate = \Carbon\Carbon::parse($po->deliveryDate)->startOfDay();
                        $isDelayed = now()->startOfDay()->greaterThan($expectedDate);
                    }

                    // Determine report status based on your requirements
                    if ($deliveryStatus === 'Cancelled' || ($deliveryStatus === 'Confirmed' && $isDelayed)) {
                        // For cancelled or delayed orders, check if notes exist
                        $status = $hasNotes ? 'Reviewed' : 'Pending Review';
                        $statusClass = $hasNotes ? 'text-blue-600 bg-blue-100' : 'text-yellow-600 bg-yellow-100';
                    } elseif ($hasDefective) {
                        $status = $hasNotes ? 'Reviewed' : 'Pending Review';
                        $statusClass = $hasNotes ? 'text-blue-600 bg-blue-100' : 'text-yellow-600 bg-yellow-100';
                    } else {
                        $status = 'Good Condition';
                        $statusClass = 'text-green-600 bg-green-100';
                    }

                    $deliveryStatusClass = match($deliveryStatus) {
                        'Delivered' => 'text-green-600 bg-green-100',
                        'Confirmed' => $isDelayed ? 'text-red-600 bg-red-100' : 'text-blue-600 bg-blue-100',
                        'Cancelled' => 'text-red-600 bg-red-100',
                        default => 'text-yellow-600 bg-yellow-100'
                    };
                @endphp
                <tr class="border-b">
                    <!-- Purchase Order Number column -->
                    <td class="px-2 py-2 text-center truncate" title="PO Number {{ $po->orderNumber }}">
                        {{ $po->orderNumber }}
                    </td>
                    <!-- Supplier Name column -->
                    <td class="px-2 py-2 text-center truncate" title="The supplier from PO">
                        {{ $po->supplier->supplierName ?? 'N/A' }}
                    </td>
                    <!-- Date Received column -->
                    <td class="px-2 py-2 text-center truncate" title="Date item was delivered">
                        @php
                            $deliveryDate = null;
                            
                            if ($delivery && $delivery->orderStatus === 'Delivered') {
                                // Convert string to Carbon object if needed
                                $deliveryDate = is_string($delivery->status_updated_at) 
                                    ? \Carbon\Carbon::parse($delivery->status_updated_at)
                                    : $delivery->status_updated_at;
                            }
                        @endphp
                        
                        @if($deliveryDate)
                            {{ $deliveryDate->format('M d, Y') }}
                        @else
                            <span class="text-gray-400">Not delivered</span>
                        @endif
                    </td>
                    <!-- Total Items column -->
                    <td class="px-2 py-2 text-center truncate" title="{{ $totalItems }} total items from purchase order">
                        {{ $totalItems }}
                    </td>
                    <!-- Good Items Items column -->
                    <td class="px-2 py-2 text-center truncate text-green-600 font-semibold" title="{{ $goodItemsCount }} items in good condition">
                        {{ $goodItemsCount }}
                    </td>
                    <!-- Defect column -->
                    <td class="px-2 py-2 text-center truncate" title="{{ $defectiveCount }} defective items">
                        @if($defectiveCount > 0)
                            <span class="text-red-600 font-semibold">
                                {{ $defectiveCount }}
                            </span>
                        @else
                            <span class="text-gray-500">0</span>
                        @endif
                    </td>
                    <!-- Delivery Status column -->
                    <td class="px-2 py-2 text-center truncate" title="Delivery Status: {{ $deliveryStatus }}">
                        <span class="text-xs font-semibold {{ $deliveryStatusClass }} px-2 py-1 rounded-xl">
                            @if($deliveryStatus === 'Confirmed' && $isDelayed)
                                Delayed
                            @else
                                {{ $deliveryStatus }}
                            @endif
                        </span>
                    </td>
                    <!-- Report Status column -->
                    <td class="px-2 py-2 text-center truncate" title="Report Status: {{ $status }}">
                        <span class="text-xs font-semibold {{ $statusClass }} px-2 py-1 rounded-xl">
                            {{ $status }}
                        </span>
                    </td>
                    <!-- View Details Button -->
                    <td class="px-2 py-2 text-center flex place-content-center" title="Click here to view more details">
                        <button onclick="document.getElementById('poDetails{{ $po->id }}').showModal()" 
                            class="flex rounded-md bg-gray-400 px-2 py-1 text-sm text-white items-center content-center hover:bg-gray-400/70 transition duration-100 ease-in font-semibold">
                            View Details
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-gray-500">
                        No purchase orders found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4 px-4 py-2 bg-gray-50">
        {{ $purchaseOrders->appends(['tab' => 'po'])->links() }}
    </div>
</section>