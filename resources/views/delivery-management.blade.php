<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 pt-5 pb-3 flex flex-col items-center content-start">

        <!-- SUCCESS MESSAGE CONTAINER AND STATEMENT -->
        @if(session('success'))
            <div id="success-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- AUTO-HIDE SUCCESS MESSAGES -->
        <script>
            // Hide success messages after 3 seconds
            document.addEventListener('DOMContentLoaded', function() {
                const successMessage = document.getElementById('success-message');
                
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
            });
        </script>

        <!-- CONTAINER OUTSIDE THE TABLE -->
        <section class="container flex flex-col items-center place-content-start mt-2">

            <!-- SEARCH BAR AND FILTERS - SEPARATE FORM TO AVOID CONFLICTS -->
            <div class="container flex items-center place-content-start gap-4 mb-1">
                <!-- SEPARATE SEARCH/FILTER FORM - WON'T AFFECT OTHER FORMS -->
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

                    <!-- Clear Button (only show when filters are active) -->
                    @if(request('search'))
                        <a href="{{ route('delivery-management.index') }}" class="text-white px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                            Clear
                        </a>
                    @endif
                </form>

            </div>
        </section>

        <!-- CONTAINER FOR TABLE DETAILS -->
        <section class="border w-full rounded-md border-solid border-black my-6">
            <table class="w-full">
                <thead class="rounded-lg bg-main text-white px-4 py-3">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-3">PO Number</th>
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
                    <tr class="border-b">
                        <td class="truncate px-2 py-3 text-center" title="{{ $order->orderNumber }}">{{ $order->orderNumber }}</td>
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
                                $orderDate = \Carbon\Carbon::parse($order->created_at)->startOfDay();
                                $deliveryDate = \Carbon\Carbon::parse($order->deliveryDate)->startOfDay();

                                // Lead time (order placed → expected delivery)
                                $leadTime = $orderDate->diffInDays($deliveryDate);

                                // Days left/delayed (today → expected delivery)
                                $daysLeft = now()->startOfDay()->diffInDays($deliveryDate, false);
                            @endphp

                            {{ $leadTime }} {{ \Illuminate\Support\Str::plural('day', $leadTime) }}
                        </td>

                        {{-- Delivery Status Column --}}
                        <td class="truncate px-2 py-3 text-center">
                            @if($daysLeft > 0)
                                <span>{{ $daysLeft }} {{ \Illuminate\Support\Str::plural('day', $daysLeft) }} left</span>
                            @elseif($daysLeft === 0)
                                <span class="text-yellow-600 text-xs">Today</span>
                            @else
                                <span class="text-red-600">{{ abs($daysLeft) }} {{ \Illuminate\Support\Str::plural('day', abs($daysLeft)) }} delayed</span>
                            @endif
                        </td>

                        <td class="truncate px-2 py-3 text-center" title="">                                
                            <span class="px-2 py-1 text-sm font-semibold rounded-full 
                                    @if($order->orderStatus === 'Pending') text-yellow-400 bg-yellow-300/40
                                    @elseif($order->orderStatus === 'Confirmed') text-teal-400 bg-teal-200/40
                                    @elseif($order->orderStatus === 'Delivered') text-button-save bg-button-save/40
                                    @else text-button-delete bg-button-delete/30  @endif"
                                    title="This order is {{ $order->orderStatus }}">
                                    {{ $order->orderStatus }}
                                </span>
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

        <!-- ============================================ -->
        <!----------------- MODALS SECTION ----------------->
        <!-- ============================================ -->
        
        @foreach( $purchaseOrder as $order)
        <x-modal.createModal x-ref="viewOrderDetails{{ $order->id}}" class="w-4/5">
            <x-slot:dialogTitle>ORDER ID: {{ $order->orderNumber}}</x-slot:dialogTitle>
            
            <div class="container">

                <!-- MAIN INFORMATION -->
                <div class="container grid grid-cols-6">
                    <div class="container col-span-2 px-6 py-1">
                        <h1 class="flex items-start font-semibold">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                            </span>Order Details
                        </h1>
                        
                        <div class="container font-semibold grid grid-cols-4 items-start justify-center my-5 text-sm gap-y-3">
                            <div class="container flex flex-col gap-y-1 col-span-2">
                                <span class='text-gray-500'>ORDER DATE</span>
                                <span class='font-normal'>{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y')}}</span>
                            </div>

                            <div class="container flex flex-col gap-y-1 col-span-2">
                                <span class='text-gray-500'>DELIVERY DATE</span>
                                <span class='font-normal'>{{ \Carbon\Carbon::parse($order->deliveryDate)->format('M d, Y') }}</span>
                            </div>
                            <div class="container flex flex-col gap-y-1 col-span-2">
                                <span class='text-gray-500'>PAYMENT TERMS</span>
                                <span class='font-normal'>{{ $order->paymentTerms}}</span>
                            </div>

                            <div class="container flex flex-col gap-y-1 col-span-2">
                                <span class='text-gray-500'>QUANTITY</span>
                                <span class='font-normal'>
                                    {{ $order->items->pluck('quantity')->implode(', ') }}
                                </span>
                            </div>

                            <div class="container flex flex-col gap-y-1 col-span-2">
                                <span class='text-gray-500'>ITEM MEASUREMENT</span>
                                <span class='font-normal'>
                                    {{ $order->items->pluck('itemMeasurement')->implode(', ') }}
                                </span>
                            </div>

                            <div class="container flex flex-col gap-y-1 col-span-2">
                                <span class='text-gray-500'>ITEM NAMES</span>
                                <div class='font-normal flex flex-col'>
                                    @foreach($order->items as $item)
                                        <span>{{ $item->productName }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="container flex flex-col gap-y-1 col-span-2">
                                <span class='text-gray-500'>UNIT PRICE</span>
                                <div class='font-normal flex flex-col'>
                                    @foreach($order->items as $item)
                                        <span>₱{{ number_format($item->unitPrice, 2) }}</span>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="container flex flex-col gap-y-1 col-span-2">
                                <span class='text-gray-500'>TOTAL</span>
                                <span class='font-semibold'>₱{{ number_format($order->totalAmount, 2) }}</span>
                            </div>
                        </div>

                    </div>
                    <div class="container col-span-2 px-6 py-1 border-x-gray border-x-2">
                        <h1 class="flex items-start font-semibold">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </span>
                            Supplier Information
                        </h1>

                        <div class="container font-semibold flex flex-col items-start justify-center my-5 text-sm gap-y-3">
                            <div class="container flex flex-col gap-y-1">
                                <span class='text-gray-500'>FROM</span>
                                <span>{{ $order->supplier->supplierName }}</span>
                                <span class='font-normal'>{{ $order->supplier->supplierAddress}}</span>
                            </div>
                            <div class="container flex flex-col gap-y-1">
                                <span class='text-gray-500'>TO</span>
                                <span>Cuatro Patee</span>
                                <span class='font-normal'>Don Jose Avila Street</span>
                            </div>

                        </div>

                    </div>
                    <div class="container col-span-2 px-6 py-1">
                        <h1 class="flex items-start font-semibold">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                            </span>Estimated Progress</h1>

                            <div class="container font-semibold flex flex-col items-start justify-center my-5 text-sm gap-y-3">

                            </div>

                            <!-- NOTE PARA DI MAMISLEAD AND USERS -->
                            <p class="text-xs text-gray-600 mt-1 italic">
                                <strong>Note:</strong> The lead time and delivery progress shown are estimates based on the order's planned schedule. 
                                Actual delivery dates may vary, as updates from suppliers are not always available. 
                                Please treat the progress indicators as a guideline rather than a confirmed status.
                            </p>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="flex justify-between items-center gap-x-4 px-6 pb-4 mt-4 border-t pt-4">
                    <!-- EDIT BUTTON: Opens edit dialog -->
                    <button 
                        @click=""
                        class="flex w-24 place-content-center rounded-md bg-button-create/70 px-3 py-2 text-blue-50 font-semibold items-center content-center hover:bg-button-create/60 transition-all duration-100 ease-in">
                        Edit
                    </button>

                    <!-- DELETE BUTTON: Opens delete dialog -->
                    <x-form.closeBtn @click="">Delete</x-form.closeBtn>

                    <!-- CLOSE BUTTON: Closes view details dialog -->
                    <button 
                        @click="$refs[viewOrderDetails{{ $order-> id}}.close()]" 
                        class="flex rounded-md ml-auto font-semibold bg-gray-400 px-6 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition-all duration-100 ease-in">
                        Close
                    </button>

                </div>
            </div>
        </x-modal.createModal>
        @endforeach

    </main>
</x-layout>
