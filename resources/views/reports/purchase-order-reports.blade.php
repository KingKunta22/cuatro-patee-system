<section class="border w-full rounded-md border-solid border-black my-3 shadow-sm">
    <table class="w-full">
        <thead class="rounded-lg bg-main text-white px-4 py-3">
            <tr class="rounded-lg">
                <th class="px-4 py-3 text-center">PO Number</th>
                <th class="px-4 py-3 text-center">Supplier</th>
                <th class="px-4 py-3 text-center">Date Received</th>
                <th class="px-4 py-3 text-center">Total Items</th>
                <th class="px-4 py-3 text-center">Good Items</th>
                <th class="px-4 py-3 text-center">Defective Items</th>
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
                            <span class="text-yellow-600 bg-yellow-100 px-2 py-1 rounded-xl">Pending Review</span>
                        @else
                            <span class="text-green-600 bg-green-100 px-2 py-1 rounded-xl">Completed</span>
                        @endif
                    </td>
                    <td class="px-2 py-2 text-center">
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

<!-- MODALS SECTION -->
@foreach($purchaseOrders as $po)
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

    <!-- PO Details Modal -->
    <x-modal.createModal x-ref="poDetails{{ $po->id }}">
        <x-slot:dialogTitle>PO Details: {{ $po->orderNumber }}</x-slot:dialogTitle>
        
        <div class="p-6">
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h4 class="font-semibold mb-2">Order Information</h4>
                    <p><strong>Supplier:</strong> {{ $po->supplier->supplierName ?? 'N/A' }}</p>
                    <p><strong>Order Date:</strong> {{ $po->created_at->format('M d, Y') }}</p>
                    <p><strong>Status:</strong> 
                        @if($hasDefective)
                            <span class="text-yellow-600 bg-yellow-100 px-2 py-1 rounded-xl">Pending Review</span>
                        @else
                            <span class="text-green-600 bg-green-100 px-2 py-1 rounded-xl">Completed</span>
                        @endif
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-2">Delivery Summary</h4>
                    <p><strong>Total Items:</strong> {{ $totalItems }}</p>
                    <p><strong>Good Items:</strong> <span class="text-green-600">{{ $goodItemsCount }}</span></p>
                    <p><strong>Defective Items:</strong> 
                        @if($defectiveCount > 0)
                            <span class="text-red-600">{{ $defectiveCount }}</span>
                        @else
                            <span class="text-gray-500">0</span>
                        @endif
                    </p>
                </div>
            </div>

            <h4 class="font-semibold mb-3">Items Breakdown</h4>
            <div class="border rounded-lg mb-6">
                <table class="w-full">
                    <thead class="rounded-lg bg-main text-white">
                        <tr>
                            <th class="px-4 py-3 text-center">Product</th>
                            <th class="px-4 py-3 text-center">Ordered</th>
                            <th class="px-4 py-3 text-center">Good</th>
                            <th class="px-4 py-3 text-center">Defective</th>
                            <th class="px-4 py-3 text-center">Defect Type</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($po->items as $item)
                            @php
                                $itemGoodCount = $item->inventory ? $item->inventory->productStock : 0;
                                $itemDefectiveCount = $item->badItems->sum('item_count');
                                $itemDefectType = $item->badItems->first() ? $item->badItems->first()->quality_status : '';
                                $badItem = $item->badItems->first();
                            @endphp
                            <tr class="border-b">
                                <td class="px-2 py-2 text-center">{{ $item->productName }}</td>
                                <td class="px-2 py-2 text-center">{{ $item->quantity }}</td>
                                <td class="px-2 py-2 text-center text-green-600 font-semibold">{{ $itemGoodCount }}</td>
                                <td class="px-2 py-2 text-center">
                                    @if($itemDefectiveCount > 0)
                                        <span class="text-red-600 font-semibold">{{ $itemDefectiveCount }}</span>
                                    @else
                                        <span class="text-gray-500">0</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @if($itemDefectType)
                                        <span class="text-red-500 text-sm">{{ $itemDefectType }}</span>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @if($itemDefectiveCount > 0)
                                        <span class="text-yellow-600 bg-yellow-100 px-2 py-1 rounded-xl">
                                            {{ $badItem->status }}
                                        </span>
                                    @else
                                        <span class="text-green-600 bg-green-100 px-2 py-1 rounded-xl">Completed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-form.closeBtn @click="$refs.poDetails{{ $po->id }}.close()">Close</x-form.closeBtn>
        </div>
    </x-modal.createModal>
@endforeach