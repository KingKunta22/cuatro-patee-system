<section class="border w-full rounded-md border-solid border-black my-3 shadow-sm">
    <table class="w-full border-collapse">
        <thead class="bg-main text-white">
            <tr>
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
                    
                    // Set status based on defective items
                    if ($hasDefective) {
                        $status = 'Pending';
                        $statusClass = 'bg-yellow-100 text-yellow-800';
                        $statusIcon = '⚠️'; // Exclamation mark
                    } else {
                        $status = 'Completed';
                        $statusClass = 'bg-green-100 text-green-800';
                        $statusIcon = '✅';
                    }
                @endphp
                <tr class="hover:bg-gray-50 transition">
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
                        <span class="{{ $statusClass }} px-2 py-1 rounded-full text-xs">
                            {{ $statusIcon }} {{ $status }}
                        </span>
                    </td>
                    <td class="px-2 py-2 text-center">
                        <button @click="$refs['poDetails{{ $po->id }}'].showModal()" 
                            class="flex rounded-md bg-gray-400 px-3 py-2 text-white items-center content-center hover:bg-gray-400/70 transition duration-100 ease-in font-semibold">
                            View Details
                        </button>
                    </td>
                </tr>

                <!-- PO Details Modal -->
                <x-modal.createModal x-ref="poDetails{{ $po->id }}">
                    <x-slot:dialogTitle>PO Details: {{ $po->orderNumber }}</x-slot:dialogTitle>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-6 mb-6">
                            <div>
                                <h4 class="font-semibold mb-2">Order Information</h4>
                                <p><strong>Supplier:</strong> {{ $po->supplier->supplierName ?? 'N/A' }}</p>
                                <p><strong>Order Date:</strong> {{ $po->created_at->format('M d, Y') }}</p>
                                <p><strong>Status:</strong> {{ $po->orderStatus }}</p>
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
                        <div class="border rounded-lg overflow-hidden mb-6">
                            <table class="w-full">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Product</th>
                                        <th class="px-3 py-2 text-center">Ordered Qty</th>
                                        <th class="px-3 py-2 text-center">Good Qty</th>
                                        <th class="px-3 py-2 text-center">Defective Qty</th>
                                        <th class="px-3 py-2 text-center">Defect Type</th>
                                        <th class="px-3 py-2 text-center">Status</th>
                                        <th class="px-3 py-2 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($po->items as $item)
                                        @php
                                            $itemGoodCount = $item->inventory ? $item->inventory->productStock : 0;
                                            $itemDefectiveCount = $item->badItems->sum('item_count');
                                            $itemDefectType = $item->badItems->first() ? $item->badItems->first()->quality_status : '';
                                            $itemStatus = $itemDefectiveCount > 0 ? 'Pending' : 'Completed';
                                            $itemStatusClass = $itemDefectiveCount > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
                                        @endphp
                                        <tr class="border-b">
                                            <td class="px-3 py-2">{{ $item->productName }}</td>
                                            <td class="px-3 py-2 text-center">{{ $item->quantity }}</td>
                                            <td class="px-3 py-2 text-center text-green-600">{{ $itemGoodCount }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @if($itemDefectiveCount > 0)
                                                    <span class="text-red-600">{{ $itemDefectiveCount }}</span>
                                                @else
                                                    <span class="text-gray-500">0</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                @if($itemDefectType)
                                                    <span class="text-red-500 text-sm">{{ $itemDefectType }}</span>
                                                @else
                                                    <span class="text-gray-400 text-sm">-</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <span class="{{ $itemStatusClass }} px-2 py-1 rounded-full text-xs">
                                                    {{ $itemStatus }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                @if($itemDefectiveCount > 0)
                                                    <button @click="$refs['updateDefectiveStatus{{ $item->id }}'].showModal()"
                                                            class="text-blue-600 hover:text-blue-800 text-sm">
                                                        Update Status
                                                    </button>
                                                @else
                                                    <span class="text-gray-400 text-sm">No action needed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end gap-4 mt-6">
                            <x-form.closeBtn @click="$refs['poDetails{{ $po->id }}'].close()">Close</x-form.closeBtn>
                        </div>
                    </div>
                </x-modal.createModal>

                <!-- Update Defective Status Modal (for individual items) -->
                @foreach($po->items as $item)
                    @if($item->badItems->sum('item_count') > 0)
                    <x-modal.createModal x-ref="updateDefectiveStatus{{ $item->id }}">
                        <x-slot:dialogTitle>Update Defective Items Status</x-slot:dialogTitle>
                        
                        <form action="{{ route('reports.update-defective-status', $item->id) }}" method="POST" class="p-6">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Product</label>
                                <p class="font-semibold">{{ $item->productName }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-1">Defective Items</label>
                                @php
                                    $defectiveCount = $item->badItems->sum('item_count');
                                    $defectType = $item->badItems->first()->quality_status;
                                @endphp
                                <p class="text-red-600 font-semibold">{{ $defectiveCount }} items ({{ $defectType }})</p>
                            </div>
                            
                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium mb-1">Status</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border rounded-md border-black" required>
                                    <option value="Pending" {{ $item->badItems->first()->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Reviewed" {{ $item->badItems->first()->status == 'Reviewed' ? 'selected' : '' }}>Reviewed</option>
                                    <option value="Reported" {{ $item->badItems->first()->status == 'Reported' ? 'selected' : '' }}>Reported to Supplier</option>
                                    <option value="Resolved" {{ $item->badItems->first()->status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium mb-1">Notes</label>
                                <textarea name="notes" id="notes" class="w-full px-3 py-2 border rounded-md border-black" rows="3" 
                                          placeholder="Add any notes about the defective items...">{{ $item->badItems->first()->notes }}</textarea>
                            </div>
                            
                            <div class="flex justify-end gap-4">
                                <x-form.closeBtn type="button" @click="$refs['updateDefectiveStatus{{ $item->id }}'].close()">Cancel</x-form.closeBtn>
                                <x-form.saveBtn type="submit">Update Status</x-form.saveBtn>
                            </div>
                        </form>
                    </x-modal.createModal>
                    @endif
                @endforeach
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-gray-500">
                        No purchase orders found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4 px-4 py-2 bg-gray-50 border-t">
        {{ $purchaseOrders->links() }}
    </div>
</section>