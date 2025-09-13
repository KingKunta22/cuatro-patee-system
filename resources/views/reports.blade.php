<x-layout>
    <x-sidebar/>
    <div x-data="{ activeTab: '{{ request('tab', 'product') }}' }" 
         class="container w-auto ml-64 px-10 py-6 flex flex-col items-center content-start">
        
        <!-- SUCCESS MESSAGE POPUP -->
        @if(session('download_pdf'))
            <div id="pdf-success-message" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                <p>Purchase order saved successfully! PDF download will start automatically.</p>
            </div>
        @endif
        
        @if(session('success'))
            <div id="success-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- AUTO-HIDE SUCCESS MESSAGES -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                ['pdf-success-message','success-message'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        setTimeout(() => {
                            el.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                            el.style.opacity = '0';
                            el.style.transform = 'translate(-50%, -20px)';
                            setTimeout(() => el.remove(), 500);
                        }, 3000);
                    }
                });
            });
        </script>
        
        <!-- SEARCH BAR AND CREATE BUTTON -->
        <div class="w-full flex items-center justify-between mb-4">
            <!-- SEARCH BAR -->
            <form action="" method="GET" class="flex items-center gap-4 mr-auto">
                <input type="hidden" name="timePeriod" value="{{ $timePeriod ?? 'all' }}">
                <input type="hidden" name="tab" x-model="activeTab">
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search reports..." 
                        class="pl-10 pr-4 py-2 border border-black rounded-md w-64"
                        autocomplete="off"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Search
                </button>

                @if(request('search'))
                    <a href="{{ route('reports.index', ['tab' => request('tab', 'product'), 'timePeriod' => $timePeriod ?? 'all']) }}" 
                       class="text-white px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                        Clear
                    </a>
                @endif
            </form>

            <!-- RIGHT SIDE: Time Period Dropdown -->
            <div class="relative">
                <form method="GET" class="flex">
                    <input type="hidden" name="tab" x-model="activeTab">
                    <select name="timePeriod" class="px-3 py-2 border rounded-md border-black w-48 appearance-none max-h-[200px] overflow-y-auto" onchange="this.form.submit()">
                        <option value="all" {{ ($timePeriod ?? 'all') == 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ ($timePeriod ?? 'all') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="lastWeek" {{ ($timePeriod ?? 'all') == 'lastWeek' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="lastMonth" {{ ($timePeriod ?? 'all') == 'lastMonth' ? 'selected' : '' }}>Last Month</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- NAVIGATION TABS -->
        <div class="w-full flex items-center justify-between bg-white rounded-lg pb-2 pt-4">
            <!-- TAB BUTTONS -->
            <div class="flex space-x-2">
                <a href="#"
                @click.prevent="activeTab = 'product'; updateUrl('product')"
                :class="activeTab === 'product' ? 'bg-main text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="w-40 text-center font-bold text-xs py-3 uppercase rounded transition">
                    Product Movements
                </a>
                <a href="#"
                @click.prevent="activeTab = 'sales'; updateUrl('sales')"
                :class="activeTab === 'sales' ? 'bg-main text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="w-40 text-center font-bold text-xs py-3 uppercase rounded transition">
                    Sales Reports
                </a>
                <a href="#"
                @click.prevent="activeTab = 'inventory'; updateUrl('inventory')"
                :class="activeTab === 'inventory' ? 'bg-main text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="w-40 text-center font-bold text-xs py-3 uppercase rounded transition">
                    Inventory Reports
                </a>
                <a href="#"
                @click.prevent="activeTab = 'po'; updateUrl('po')"
                :class="activeTab === 'po' ? 'bg-main text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="w-40 text-center font-bold text-xs py-3 uppercase rounded transition">
                    PO Reports
                </a>
            </div>

            <!-- PAGE TITLE -->
            <div class="font-bold text-xl uppercase text-gray-800 ml-6 whitespace-nowrap"
                x-text="activeTab === 'product' ? 'Product Movements Report'
                    : activeTab === 'sales' ? 'Sales Report'
                    : activeTab === 'inventory' ? 'Inventory Report'
                    : 'Purchase Order Report'">
            </div>
        </div>

        <!-- MAIN CONTENT (TAB SWITCHING with x-show) -->
        <div class="bg-white rounded-lg w-full h-full">
            <div x-show="activeTab === 'product'">
                @include('reports.product-movement-reports', [
                    'movementsPaginator' => $productMovements['movementsPaginator'],
                    'totalStockIn' => $productMovements['totalStockIn'],
                    'totalStockOut' => $productMovements['totalStockOut'],
                    'totalRevenue' => $productMovements['totalRevenue'],
                    'totalCost' => $productMovements['totalCost'],
                    'totalProfit' => $productMovements['totalProfit'],
                    'timePeriod' => $timePeriod,
                    'currentPage' => request('product_page', 1)
                ])
            </div>

            <div x-show="activeTab === 'sales'">
                @include('reports.sales-reports', [
                    'timePeriod' => $timePeriod ?? 'all',
                    'sales' => $sales
                ])
            </div>

            <div x-show="activeTab === 'inventory'">
                @include('reports.inventory-reports', [
                    'inventories' => $inventories, 
                    'timePeriod' => $timePeriod ?? 'all'
                ])
            </div>

            <div x-show="activeTab === 'po'">
                @include('reports.purchase-order-reports', [
                    'purchaseOrders' => $purchaseOrders, 
                    'timePeriod' => $timePeriod ?? 'all'
                ])
            </div>
        </div>

        <!-- URL update script -->
        <script>
            function updateUrl(tab) {
                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                // Preserve timePeriod parameter if it exists
                if (!url.searchParams.has('timePeriod')) {
                    url.searchParams.set('timePeriod', '{{ $timePeriod ?? 'all' }}');
                }
                window.history.replaceState({}, '', url);
            }
            
            // Initialize tab from URL parameter
            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);
                const tab = urlParams.get('tab');
                if (tab) {
                    // Set the active tab based on URL parameter
                    Alpine.data('tabState', () => ({
                        activeTab: tab
                    }));
                }
            });
        </script>
                
        <!-- ========================================= -->
        <!----------------ALL MODALS SECTION ------------>
        <!-- ======================================== --->
        
        @foreach($purchaseOrders as $po)
            @php
                // Calculate totals for this PO
                $totalItems = $po->items->sum('quantity');
                $goodItemsCount = 0;
                $defectiveCount = 0;
                $hasDefective = false;
                $hasNotes = $po->notes->count() > 0;
                
                foreach ($po->items as $item) {
                    $goodItemsCount += $item->inventory ? $item->inventory->productStock : 0;
                    $itemDefectiveCount = $item->badItems->sum('item_count');
                    $defectiveCount += $itemDefectiveCount;
                    
                    if ($itemDefectiveCount > 0) {
                        $hasDefective = true;
                    }
                }

                // Determine status based on defects and notes
                $status = $hasDefective ? ($hasNotes ? 'Reviewed' : 'Pending Review') : 'Completed';
                $statusClass = $hasDefective ? ($hasNotes ? 'text-green-600 bg-green-100' : 'text-yellow-600 bg-yellow-100') : 'text-green-600 bg-green-100';
            @endphp


            <!---------------- MODALS FROM PO REPORTS SECTION ------------>

            <!-- PO Details Modal -->
            <x-modal.createModal x-ref="poDetails{{ $po->id }}">
                <x-slot:dialogTitle>PO Details: {{ $po->orderNumber }}</x-slot:dialogTitle>
                
                <div class="px-6 py-4">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-100 rounded p-4">
                            <h4 class="font-bold mb-1 text-lg">Order Information</h4>
                            <p><strong>Supplier:</strong> {{ $po->supplier->supplierName ?? 'N/A' }}</p>
                            <p><strong>Order Date:</strong>
                                @php
                                    $delivery = $po->deliveries->first();
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
                            </p>
                            <p><strong>Status:</strong> 
                                <span class="font-semibold text-sm {{ $statusClass }} px-2 py-1 rounded-xl">
                                    {{ $status }}
                                </span>
                            </p>
                        </div>
                        <div class="bg-gray-100 rounded p-4">
                            <h4 class="font-bold mb-2 text-lg">Delivery Summary</h4>
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

                    <h4 class="font-bold mb-3">Items Breakdown</h4>
                    <div class="border rounded-lg mb-6">
                        <table class="w-full text-sm">
                            <thead class="rounded-lg bg-main text-white">
                                <tr>
                                    <th class="px-4 py-3 text-center">Product</th>
                                    <th class="px-4 py-3 text-center">Ordered</th>
                                    <th class="px-4 py-3 text-center">Good</th>
                                    <th class="px-4 py-3 text-center">Defective</th>
                                    <th class="px-4 py-3 text-center">Defect</th>
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
                                        
                                        // Determine item status
                                        $itemStatus = $itemDefectiveCount > 0 ? 
                                            ($hasNotes ? 'Reviewed' : 'Pending') : 
                                            'Completed';
                                        $itemStatusClass = $itemDefectiveCount > 0 ? 
                                            ($hasNotes ? 'text-green-600 bg-green-100' : 'text-yellow-600 bg-yellow-100') : 
                                            'text-green-600 bg-green-100';
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
                                                <span class="text-red-500 text-sm capitalize">{{ $itemDefectType }}</span>
                                            @else
                                                <span class="text-gray-400 text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2 text-center">
                                            <span class="text-xs font-semibold {{ $itemStatusClass }} px-2 py-1 rounded-xl">
                                                {{ $itemStatus }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Notes Section -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-bold">Notes</h4>
                            <button onclick="document.getElementById('addNoteModal{{ $po->id }}').showModal()" 
                                    class="px-3 py-1 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600">
                                Add Note
                            </button>
                        </div>
                        
                        @if($po->notes->count() > 0)
                            <div class="border rounded-lg p-4 bg-gray-50 max-h-40 overflow-y-auto">
                                @foreach($po->notes as $note)
                                    <div class="mb-3 pb-3 border-b last:border-b-0 last:mb-0 last:pb-0">
                                        <div class="flex justify-between items-start">
                                            <p class="text-sm">{{ $note->note }}</p>
                                            <div class="flex space-x-2">
                                                <form action="{{ route('po-notes.destroy', $note->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-500 hover:text-red-700 text-xs"
                                                            onclick="return confirm('Are you sure you want to delete this note?')">
                                                        <x-form.deleteBtn/>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">{{ $note->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">No notes added yet.</p>
                        @endif
                    </div>
                </div>
            </x-modal.createModal>

            <!-- Add Note Modal -->
            <x-modal.createModal x-ref="addNoteModal{{ $po->id }}" id="addNoteModal{{ $po->id }}">
                <x-slot:dialogTitle>Add Note for PO: {{ $po->orderNumber }}</x-slot:dialogTitle>
                
                <form action="{{ route('po-notes.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">
                    
                    <div class="px-6 py-4">
                        <div class="mb-4">
                            <label for="note{{ $po->id }}" class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                            <textarea name="note" id="note{{ $po->id }}" rows="4" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                            </textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <x-form.closeBtn type="button" @click="$refs.addNoteModal{{ $po->id }}.close()">Cancel</x-form.closeBtn>
                            <x-form.saveBtn type="submit">Add Note</x-form.saveBtn>
                        </div>
                    </div>
                </form>
            </x-modal.createModal>
        @endforeach
    </div>
</x-layout>
