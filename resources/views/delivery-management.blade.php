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
        <section class="container flex flex-col items-center place-content-start">

            <!-- SEARCH BAR AND FILTERS - SEPARATE FORM TO AVOID CONFLICTS -->
            <div class="container flex items-center place-content-start gap-4 mb-1">
                <!-- SEPARATE SEARCH/FILTER FORM - WON'T AFFECT OTHER FORMS -->
                <form action="{{ route('delivery-management.index') }}" method="GET" class="flex items-center gap-4 mr-auto">
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
        <section class="border w-full rounded-md border-solid border-black my-3">
            <table class="w-full">
                <thead class="rounded-lg bg-main text-white px-4 py-3">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-3">PO Number</th>
                        <th class=" bg-main px-4 py-3">Order Date</th>
                        <th class=" bg-main px-4 py-3">Expected Date</th>
                        <th class=" bg-main px-4 py-3">Lead Time</th>
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
                        <td class="truncate px-2 py-3 text-center">
                            @php
                                $orderDate = \Carbon\Carbon::parse($order->created_at)->startOfDay();
                                $deliveryDate = \Carbon\Carbon::parse($order->deliveryDate)->startOfDay();
                                $daysDifference = $orderDate->diffInDays($deliveryDate);
                                $isOverdue = $deliveryDate->isPast();
                            @endphp
                            
                            {{ $daysDifference }} days
                            ({{ $deliveryDate->diffForHumans($orderDate) }})
                            @if($isOverdue)
                                <span class="text-red-500 text-xs">(Overdue)</span>
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
                        <td class="truncate px-2 py-2 text-center" title="">ACTION</td>
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

    </main>
</x-layout>
