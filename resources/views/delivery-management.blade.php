<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 pt-5 pb-3 flex flex-col items-center content-start">

        <!-- SUCCESS MESSAGE CONTAINER AND STATEMENT -->
        @if(session('success'))
            <div id="success-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- ERROR MESSAGE CONTAINER AND STATEMENT -->
        @if(session('error'))
            <div id="error-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-red-100 border border-red-400 text-red-700 rounded shadow-lg">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <!-- AUTO-HIDE MESSAGES -->
        <script>
            // Hide success/error messages after 3 seconds
            document.addEventListener('DOMContentLoaded', function() {
                const successMessage = document.getElementById('success-message');
                const errorMessage = document.getElementById('error-message');
                
                if (successMessage) {
                    setTimeout(() => {
                        successMessage.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                        successMessage.style.opacity = '0';
                        successMessage.style.transform = 'translate(-50%, -20px)';
                        setTimeout(() => {
                            successMessage.remove();
                        }, 500);
                    }, 3000);
                }
                
                if (errorMessage) {
                    setTimeout(() => {
                        errorMessage.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                        errorMessage.style.opacity = '0';
                        errorMessage.style.transform = 'translate(-50%, -20px)';
                        setTimeout(() => {
                            errorMessage.remove();
                        }, 500);
                    }, 3000);
                }
            });
        </script>

        <!-- CONTAINER OUTSIDE THE TABLE -->
        <section class="container flex flex-col items-center place-content-start mt-2">

            <!-- SEARCH BAR AND FILTERS -->
            <div class="container flex items-center place-content-start gap-4 mb-1">
                <form action="{{ route('delivery-management.index') }}" method="GET" id="statusFilterForm" class="flex items-center gap-4 mr-auto">
                    <!-- Simple Search Input -->
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Search delivery..." 
                            class="pl-10 pr-4 py-2 border border-black rounded-md w-64"
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <select name="status" class="truncate w-36 px-2 py-2 border rounded-md border-black" onchange="document.getElementById('statusFilterForm').submit()">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Confirmed" {{ request('status') === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="Delivered" {{ request('status') === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select> 

                    <!-- Search Button -->
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        Search
                    </button>

                    <!-- Clear Button -->
                    @if(request('search') || (request('status') && request('status') !== 'all'))                        
                        <a href="{{ route('delivery-management.index') }}" class="text-white px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </section>

        <!-- CONTAINER FOR TABLE DETAILS -->
        <section class="border w-full rounded-md border-solid border-black my-6">
            <table class="w-full table-fixed">
                <thead class="rounded-lg bg-main text-white px-4 py-3">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-3">Delivery Number</th>
                        <th class=" bg-main px-4 py-3">Order Date</th>
                        <th class=" bg-main px-4 py-3">Expected Date</th>
                        <th class=" bg-main px-4 py-3">Lead Time</th>
                        <th class=" bg-main px-4 py-3">ETA</th>
                        <th class=" bg-main px-4 py-3">Status</th>
                        <th class=" bg-main px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach( $purchaseOrder as $order)
                    @php
                        $orderDate = \Carbon\Carbon::parse($order->created_at)->startOfDay();
                        $deliveryDate = \Carbon\Carbon::parse($order->deliveryDate)->startOfDay();
                        $daysLeft = now()->startOfDay()->diffInDays($deliveryDate, false);
                        
                        // SAFE CHECK: Get delivery status with fallback
                        $delivery = $order->deliveries->first();
                        $deliveryStatus = $delivery ? $delivery->orderStatus : 'Pending';
                        $deliveryId = $delivery ? $delivery->deliveryId : 'N/A';
                        
                        $isDelayed = $daysLeft < 0 && $deliveryStatus !== 'Delivered';
                        $displayStatus = $isDelayed ? 'Delayed' : $deliveryStatus;
                    @endphp
                    <tr class="border-b">
                        <td class="truncate px-2 py-3 text-center" title="{{ $deliveryId }}">
                            {{ $deliveryId }}
                        </td>
                        <td class="truncate px-2 py-3 text-center" 
                            title="{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}">
                            {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}
                        </td>
                        <td class="truncate px-2 py-3 text-center" 
                            title="{{ \Carbon\Carbon::parse($order->deliveryDate)->format('M d, Y') }}">
                            {{ \Carbon\Carbon::parse($order->deliveryDate)->format('M d, Y') }}
                        </td>

                        {{-- Lead Time Column --}}
                        <td class="truncate px-2 py-3 text-center">
                            @php
                                $leadTime = $orderDate->diffInDays($deliveryDate);
                            @endphp
                            {{ $leadTime }} {{ \Illuminate\Support\Str::plural('day', $leadTime) }}
                        </td>

                        {{-- Estimated Time of Arrival Column --}}
                        <td class="truncate px-2 py-3 text-center">
                            @if($deliveryStatus === 'Delivered')
                                <span class="text-green-600">Delivered</span>
                            @elseif($daysLeft > 0)
                                <span>{{ $daysLeft }} {{ \Illuminate\Support\Str::plural('day', $daysLeft) }} left</span>
                            @elseif($daysLeft === 0)
                                <span class="text-yellow-600 text-xs">Today</span>
                            @else
                                <span class="text-red-600">{{ abs($daysLeft) }} {{ \Illuminate\Support\Str::plural('day', abs($daysLeft)) }} delayed</span>
                            @endif
                        </td>

                        {{-- Delivery Status Column --}}
                        <td class="truncate px-2 py-3 text-center" title="">                                
                            <span class="px-2 py-1 text-sm font-semibold rounded-full 
                                    @if($isDelayed) text-red-400 bg-red-300/40
                                    @elseif($displayStatus === 'Pending') text-yellow-400 bg-yellow-300/40
                                    @elseif($displayStatus === 'Confirmed') text-teal-400 bg-teal-200/40
                                    @elseif($displayStatus === 'Delivered') text-button-save bg-button-save/40
                                    @else text-button-delete bg-button-delete/30  @endif"
                                    title="This order is {{ $displayStatus }}">
                                    {{ $displayStatus }}
                            </span>
                        </td>

                        <td class="truncate px-2 py-2 text-center flex place-content-center" title="">
                            <button @click="$refs['viewOrderDetails{{ $order->id }}'].showModal()" class="flex rounded-md bg-gray-400 px-3 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition:all duration-100 ease-in font-semibold">
                                View Details
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- PAGINATION VIEW -->
            <div class="mt-1 px-4 py-2 bg-gray-50">
                {{ $purchaseOrder->appends(request()->except('page'))->links() }}
            </div>
        </section>

        <!-- MODALS SECTION -->
        @foreach( $purchaseOrder as $order)
        @php
            $orderDate = \Carbon\Carbon::parse($order->created_at)->startOfDay();
            $deliveryDate = \Carbon\Carbon::parse($order->deliveryDate)->startOfDay();
            $today = \Carbon\Carbon::now()->startOfDay();
            
            // SAFE CHECK: Get delivery status with fallback
            $delivery = $order->deliveries->first();
            $deliveryStatus = $delivery ? $delivery->orderStatus : 'Pending';
            $deliveryId = $delivery ? $delivery->deliveryId : 'N/A';
            
            $totalDays = max(1, $orderDate->diffInDays($deliveryDate));
            $daysPassed = $orderDate->diffInDays($today);
            $daysRemaining = $today->diffInDays($deliveryDate, false);
            
            $isDelivered = $deliveryStatus === 'Delivered';
            $isDelayed = !$isDelivered && $daysRemaining < 0;
            
            $phaseInterval = ceil($totalDays / 4);
            
            if ($isDelivered) {
                $percentage = 100;
                $currentPhase = 4;
            } elseif ($isDelayed) {
                $percentage = 100;
                $currentPhase = 4;
            } else {
                $percentage = min(100, max(0, ($daysPassed / $totalDays) * 100));
                
                if ($daysPassed >= ($phaseInterval * 3)) {
                    $currentPhase = 4;
                } elseif ($daysPassed >= ($phaseInterval * 2)) {
                    $currentPhase = 3;
                } elseif ($daysPassed >= $phaseInterval) {
                    $currentPhase = 2;
                } else {
                    $currentPhase = 1;
                }
            }
            
            $phases = [
                1 => ['name' => 'Order Placed', 'desc' => 'Order has been received by the system'],
                2 => ['name' => 'Packaging', 'desc' => 'Order is being prepared and packaged'],
                3 => ['name' => 'Shipped', 'desc' => 'Order has left the warehouse'], 
                4 => ['name' => 'Delivered', 'desc' => 'Order successfully delivered']
            ];
        @endphp
        <x-modal.createModal x-ref="viewOrderDetails{{ $order->id}}" class="w-4/5">
            <x-slot:dialogTitle>DELIVERY ID: {{ $deliveryId }}</x-slot:dialogTitle>            
            <div class="container">
                <div class="container grid grid-cols-6 p-4">
                    {{-- ORDER DETAILS SECTION --}}
                    <div class="container col-span-2 px-6 py-1">
                        <h1 class="flex items-start font-bold text-lg mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                            Order Details
                        </h1>
                        
                        {{-- Order Information Boxes --}}
                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <div class="bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-sm">ORDER DATE</p>
                                <p class="text-sm">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y')}}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-sm">DELIVERY DATE</p>
                                <p class="text-sm">{{ \Carbon\Carbon::parse($order->deliveryDate)->format('M d, Y') }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-sm">PAYMENT TERMS</p>
                                <p class="text-sm">{{ $order->paymentTerms}}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-md text-center">
                                <p class="font-semibold text-sm">TOTAL AMOUNT</p>
                                <p class="text-sm font-semibold">₱{{ number_format($order->totalAmount, 2) }} ({{ $order->items->sum('quantity') }})</p>
                            </div>
                        </div>

                        {{-- Items Table --}}
                        <div class="border w-full rounded-md border-solid border-black p-1 mb-4">
                            <table class="w-full">
                                <thead class="rounded-lg bg-main text-white">
                                    <tr>
                                        <th class="px-1 py-1 text-sm">Item Name</th>
                                        <th class="px-1 py-1 text-sm">Qty</th>
                                        <th class="px-1 py-1 text-sm">Price</th>
                                        <th class="px-1 py-1 text-sm">Measure</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr class="border-b">
                                        <td class="px-2 py-2 text-center text-xs">{{ $item->productName }}</td>
                                        <td class="px-2 py-2 text-center text-xs">{{ $item->quantity }}</td>
                                        <td class="px-2 py-2 text-center text-xs">₱{{ number_format($item->unitPrice, 2) }}</td>
                                        <td class="px-2 py-2 text-center text-xs">{{ $item->itemMeasurement }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Order Status Form -->
                        <form action="{{ route('delivery-management.updateStatus')}}" method="POST" class="w-full">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            
                            <div class="w-full">
                                <label for="orderStatus{{ $order->id }}" class="block text-sm font-medium mb-1">Order Status</label>
                                <select name="status" id="orderStatus{{ $order->id }}" class="w-full px-3 py-2 border rounded-sm border-black" required>
                                    <option value="Pending" {{ $deliveryStatus === 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Confirmed" {{ $deliveryStatus === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="Delivered" {{ $deliveryStatus === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="Cancelled" {{ $deliveryStatus === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                
                                <button type="submit" class="mt-2 w-full px-4 py-2  bg-button-save text-white rounded-md hover:bg-green-600 transition-colors duration-200">
                                    Update Status
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- SUPPLIER INFORMATION SECTION --}}
                    <div class="container col-span-2 px-6 py-1 border-x-gray border-x-2">
                        <h1 class="flex items-start font-bold text-lg mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            Supplier Information
                        </h1>

                        <div class="grid grid-cols-1 gap-3 mb-5">
                            <div class="bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-md pb-2">FROM</p>
                                <p class="pl-2 text-sm">{{ $order->supplier->supplierAddress}}</p>
                                <p class="pl-2 text-sm font-md">{{ $order->supplier->supplierName }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-md pb-2">TO</p>
                                <p class="pl-2 text-sm">Don Jose Avila Street</p>
                                <p class="pl-2 text-sm font-md">Cuatro Patee</p>
                            </div>
                        </div>
                    </div>

                    {{-- ESTIMATED PROGRESS SECTION --}}
                    <div class="container col-span-2 px-6 py-1">
                        <h1 class="flex items-start font-semibold text-lg mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                            Delivery Progress
                        </h1>

                        <!-- Progress Bar -->
                        <div class="mb-5">
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span>Order Date: {{ $orderDate->format('M d, Y') }}</span>
                                <span>Expected: {{ $deliveryDate->format('M d, Y') }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                                <div class="h-2.5 rounded-full {{ $isDelivered ? 'bg-green-600' : ($isDelayed ? 'bg-red-600' : 'bg-blue-600') }}" 
                                    style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>{{ $daysPassed }} day{{ $daysPassed != 1 ? 's' : '' }} passed</span>
                                @if($isDelivered)
                                    <span class="text-green-600">Delivered</span>
                                @elseif($isDelayed)
                                    <span class="text-red-600">{{ abs($daysRemaining) }} day{{ abs($daysRemaining) != 1 ? 's' : '' }} delayed</span>
                                @else
                                    <span>{{ $daysRemaining }} day{{ $daysRemaining != 1 ? 's' : '' }} remaining</span>
                                @endif
                            </div>
                        </div>

                        <!-- Phase Indicators -->
                        <div class="relative mb-6">
                            <div class="absolute left-0 right-0 top-3 h-0.5 bg-gray-200"></div>
                            <div class="absolute left-0 top-3 h-0.5 {{ $isDelivered ? 'bg-green-600' : ($isDelayed ? 'bg-red-600' : 'bg-blue-600') }}" 
                                style="width: {{ $percentage }}%"></div>
                            
                            <div class="flex justify-between relative">
                                @for($i = 1; $i <= 4; $i++)
                                    <div class="flex flex-col items-center">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs z-10
                                            {{ $i <= $currentPhase ? ($isDelivered ? 'bg-green-600 text-white' : ($isDelayed ? 'bg-red-600 text-white' : 'bg-blue-600 text-white')) : 'bg-gray-200 text-gray-600' }}">
                                            {{ $i }}
                                        </div>
                                        <div class="text-center mt-2 w-20">
                                            <p class="text-xs font-medium {{ $i <= $currentPhase ? ($isDelivered ? 'text-green-600' : ($isDelayed ? 'text-red-600' : 'text-blue-600')) : 'text-gray-500' }}">
                                                {{ $phases[$i]['name'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Current Status -->
                        <div class="p-4 rounded-lg border mb-4 {{ $isDelivered ? 'bg-green-50 border-green-200' : ($isDelayed ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-100') }}">
                            <p class="text-sm font-semibold mb-1 {{ $isDelivered ? 'text-green-800' : ($isDelayed ? 'text-red-800' : 'text-blue-800') }}">Current Status</p>
                            
                            @if($isDelivered)
                                <p class="text-md font-medium text-green-900">{{ $phases[4]['name'] }}</p>
                                <p class="text-sm text-green-700 mt-1">{{ $phases[4]['desc'] }}</p>
                                <p class="text-xs text-green-600 mt-2">Delivery completed on {{ $deliveryDate->format('M d, Y') }}</p>
                            @elseif($isDelayed)
                                <p class="text-md font-medium text-red-900">Delayed</p>
                                <p class="text-sm text-red-700 mt-1">Expected delivery was {{ abs($daysRemaining) }} day{{ abs($daysRemaining) != 1 ? 's' : '' }} ago</p>
                                <p class="text-xs text-red-600 mt-2">Please contact the supplier for an update</p>
                            @else
                                <p class="text-md font-medium text-blue-900">{{ $phases[$currentPhase]['name'] }}</p>
                                <p class="text-sm text-blue-700 mt-1">{{ $phases[$currentPhase]['desc'] }}</p>
                                <p class="text-xs text-blue-600 mt-2">
                                    Estimated delivery in {{ $daysRemaining }} day{{ $daysRemaining != 1 ? 's' : '' }}
                                    ({{ $deliveryDate->format('M d, Y') }})
                                </p>
                            @endif
                        </div>

                        <!-- Note -->
                        <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                            <p class="text-xs text-yellow-800 font-medium mb-1">Please Note:</p>
                            <p class="text-xs text-yellow-700">
                                This progress tracker shows estimated timelines based on order and expected delivery dates. 
                                The status shown here is for planning purposes only. The actual delivery status is determined
                                by the "Status" field in the order details, which may be updated by your team.
                            </p>
                            <p class="text-xs text-yellow-700 mt-1">
                                Progress is divided into 4 equal phases based on the lead time between order and expected delivery dates.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </x-modal.createModal>
        @endforeach
    </main>
</x-layout>