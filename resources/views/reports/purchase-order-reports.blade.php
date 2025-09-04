<section class="border w-full rounded-md border-solid border-black my-3 shadow-sm">
    <table class="w-full table-fixed">
        <thead class="rounded-lg bg-main text-white px-4 py-3">
            <tr class="rounded-lg">
                <th class="px-4 py-3 text-center">PO Number</th>
                <th class="px-4 py-3 text-center">Supplier</th>
                <th class="px-4 py-3 text-center">Date Received</th>
                <th class="px-4 py-3 text-center">Total Items</th>
                <th class="px-4 py-3 text-center">Good Items</th>
                <th class="px-4 py-3 text-center">Bad Items</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($purchaseOrders as $po)
                @php
                    // Calculate totals for this PO
                    $totalItems = $po->items->sum('quantity');
                    $goodItemsCount = 0;
                    $defectiveCount = 0;
                    $hasDefective = false;
                    
                    foreach ($po->items as $item) {
                        $goodItemsCount += $item->inventory ? $item->inventory->productStock : 0;
                        $itemDefectiveCount = $item->badItems->sum('item_count');
                        $defectiveCount += $itemDefectiveCount;
                        
                        if ($itemDefectiveCount > 0) {
                            $hasDefective = true;
                        }
                    }
                @endphp
                <tr class="border-b">
                    <td class="px-2 py-2 text-center font-semibold">{{ $po->orderNumber }}</td>
                    <td class="px-2 py-2 text-center">{{ $po->supplier->supplierName ?? 'N/A' }}</td>
                    <td class="px-2 py-2 text-center">
                        @if($po->deliveries->first())
                            {{ $po->deliveries->first()->created_at->format('M d, Y') }}
                        @else
                            <span class="text-gray-400">Not delivered</span>
                        @endif
                    </td>
                    <td class="px-2 py-2 text-center">{{ $totalItems }}</td>
                    <td class="px-2 py-2 text-center text-green-600 font-semibold">{{ $goodItemsCount }}</td>
                    <td class="px-2 py-2 text-center">
                        @if($defectiveCount > 0)
                            <span class="text-red-600 font-semibold">
                                {{ $defectiveCount }}
                            </span>
                        @else
                            <span class="text-gray-500">0</span>
                        @endif
                    </td>
                    <td class="px-2 py-2 text-center">
                        @if($hasDefective)
                            <span class=" text-sm font-semibold text-yellow-600 bg-yellow-100 px-2 py-1 rounded-xl">Pending Review</span>
                        @else
                            <span class=" text-sm font-semibold text-green-600 bg-green-100 px-2 py-1 rounded-xl">Completed</span>
                        @endif
                    </td>
                    <td class="px-2 py-2 text-center flex place-content-center">
                        <button onclick="document.getElementById('poDetails{{ $po->id }}').showModal()" 
                            class="flex rounded-md bg-gray-400 px-3 py-2 text-white items-center content-center hover:bg-gray-400/70 transition duration-100 ease-in font-semibold">
                            View Details
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-gray-500">
                        No purchase orders found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4 px-4 py-2 bg-gray-50">
        {{ $purchaseOrders->links() }}
    </div>
</section>
