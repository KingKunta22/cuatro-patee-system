<!-- REVENUE STATS -->
<div class="container flex items-start justify-start place-content-start w-auto gap-x-4 text-white mr-auto mb-4 pl-0 p-4">
    <div class="container flex flex-col px-6 py-3 w-64 text-start rounded-md bg-[#5C717B]">
        <span class="font-semibold text-xl">₱{{ number_format($totalRevenue, 2) }}</span>
        <span class="text-xs">Total Revenue</span>
    </div>
    <div class="container flex flex-col px-6 py-3 w-64 text-start rounded-md bg-[#2C3747]">
        <span class="font-semibold text-xl">₱{{ number_format($totalProfit, 2) }}</span>
        <span class="text-xs">Total Profit</span>
    </div>
    <div class="container flex flex-col px-6 py-3 w-64 text-start rounded-md bg-[#5C717B]">
        <span class="font-semibold text-xl">₱{{ number_format($totalCost, 2) }}</span>
        <span class="text-xs">Total Cost</span>
    </div>
</div>

<section class="border w-full rounded-md border-solid border-black my-3 shadow-sm">
    <table class="w-full border-collapse table-fixed">
        <thead class="bg-main text-white">
            <tr>
                <th class="px-4 py-3 text-center">Invoice No.</th>
                <th class="px-4 py-3 text-center">Customer</th>
                <th class="px-4 py-3 text-center">Date</th>
                <th class="px-4 py-3 text-center">Items Count</th>
                <th class="px-4 py-3 text-center">Total Amount</th>
                <th class="px-4 py-3 text-center">Processed By</th>
                <th class="px-4 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($sales as $sale)
                <tr>
                    <td class="px-2 py-2 text-center truncate">{{ $sale->invoice_number }}</td>
                    <td class="px-2 py-2 text-center truncate">{{ $sale->customer_name ?? 'Walk-in Customer' }}</td>
                    <td class="px-2 py-2 text-center truncate">{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}</td>
                    <td class="px-2 py-2 text-center truncate">{{ $sale->items->count() }} items</td>
                    <td class="px-2 py-2 text-center truncate">₱{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="px-2 py-2 text-center truncate">{{ $sale->processed_by }}</td>
                    <td class="truncate px-2 py-2 text-center flex place-content-center">
                        <button onclick="document.getElementById('viewSaleDetails{{ $sale->id }}').showModal()" 
                            class="flex rounded-md bg-gray-400 px-2 py-1 text-sm w-auto text-white items-center content-center 
                                hover:bg-gray-400/70 transition:all duration-100 ease-in font-semibold">
                            View Details
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-gray-500">
                        No sales records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4 px-4 py-2 bg-gray-50 border-t">
        {{ $sales->appends(['tab' => 'sales'])->links() }}
    </div>
</section>

@foreach($sales as $sale)
    <!-- VIEW DETAILS MODAL -->
    <x-modal.createModal x-ref="viewSaleDetails{{ $sale->id }}" id="viewSaleDetails{{ $sale->id }}">
        <x-slot:dialogTitle>Sale Details: {{ $sale->invoice_number }}</x-slot:dialogTitle>
        
        <div class="container px-3 pt-4 pb-0">
            <div class="grid grid-cols-2 gap-3 px-4">
                <!-- Sale Information -->
                <div class="col-span-3">
                    <h2 class="text-xl font-bold mb-4">Sale Information</h2>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-gray-50 p-3 rounded-md">
                            <p class="font-semibold text-md">Invoice Number</p>
                            <p class="text-sm">{{ $sale->invoice_number }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <p class="font-semibold text-md">Sale Date</p>
                            <p class="text-sm">{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <p class="font-semibold text-md">Customer Name</p>
                            <p class="text-sm">{{ $sale->customer_name ?? 'Walk-in Customer' }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <p class="font-semibold text-md">Items Count</p>
                            <p class="text-sm">{{ $sale->items->count() }} items</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <p class="font-semibold text-md">Total Amount</p>
                            <p class="text-sm">₱{{ number_format($sale->total_amount, 2) }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <p class="font-semibold text-main">Processed By</p>
                            <p class="text-sm">{{ $sale->processed_by }}</p>
                        </div>
                    </div>
                </div>

                <!-- Items in this sale -->
                <div class="col-span-2">
                    <h2 class="text-xl font-bold mb-4">Items Sold</h2>
                    <div class="border w-auto rounded-md border-solid border-black p-3 my-4">
                        <table class="w-full">
                            <thead class="rounded-lg bg-main text-white px-4 py-2">
                                <tr class="rounded-lg">
                                    <th class="bg-main px-2 py-2 text-sm">Product</th>
                                    <th class="bg-main px-2 py-2 text-sm">Quantity</th>
                                    <th class="bg-main px-2 py-2 text-sm">Unit Price</th>
                                    <th class="bg-main px-2 py-2 text-sm">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $saleItem)
                                <tr class="border-b">
                                    <td class="px-2 py-2 text-center">{{ $saleItem->inventory->productName ?? $saleItem->product_name ?? 'N/A' }}</td>
                                    <td class="px-2 py-2 text-center">{{ $saleItem->quantity }} {{ $saleItem->inventory->productItemMeasurement ?? '' }}</td>
                                    <td class="px-2 py-2 text-center">₱{{ number_format($saleItem->unit_price, 2) }}</td>
                                    <td class="px-2 py-2 text-center">₱{{ number_format($saleItem->total_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- Total Row -->
                    <div class="col-span-4 flex items-center place-content-end mb-4 mx-6 pb-4 px-8 border-b-2 border-black">
                        <label class="font-bold mr-2 uppercase">Grand Total:</label>
                        <p>₱{{ number_format($sale->total_amount, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Only Close button for reports -->
            <div class="flex justify-end items-center w-full px-6 py-4 border-t">
                <button 
                    @click="$refs['viewSaleDetails{{ $sale->id }}'].close()" 
                    class="flex rounded-md font-semibold bg-gray-400 px-6 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition-all duration-100 ease-in">
                    Close
                </button>
            </div>
        </div>
    </x-modal.createModal>
@endforeach