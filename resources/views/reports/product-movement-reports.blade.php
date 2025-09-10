@php
    // Fallback to ensure variables exist
    $movementsPaginator = $movementsPaginator ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1);
    $totalStockIn = $totalStockIn ?? 0;
    $totalStockOut = $totalStockOut ?? 0;
    $totalRevenue = $totalRevenue ?? 0;
    $totalCost = $totalCost ?? 0;
    $totalProfit = $totalProfit ?? 0;
@endphp

<!-- REVENUE AND STOCK STATS -->
<div class="container flex items-start justify-start place-content-start w-auto gap-x-4 text-white mr-auto mb-4 pl-0 p-4">
    <div class="container flex flex-col px-6 py-3 w-48 text-start rounded-md bg-[#5C717B]">
        <span class="font-semibold text-xl">{{ number_format($totalStockIn) }}</span>
        <span class="text-xs">Total Stock In</span>
    </div>
    <div class="container flex flex-col px-6 py-3 w-48 text-start rounded-md bg-[#2C3747]">
        <span class="font-semibold text-xl">{{ number_format($totalStockOut) }}</span>
        <span class="text-xs">Total Stock Out</span>
    </div>
    <div class="container flex flex-col px-6 py-3 w-48 text-start rounded-md bg-[#5C717B]">
        <span class="font-semibold text-xl">₱{{ number_format($totalRevenue, 2) }}</span>
        <span class="text-xs">Total Revenue</span>
    </div>
    <div class="container flex flex-col px-6 py-3 w-48 text-start rounded-md bg-[#2C3747]">
        <span class="font-semibold text-xl">₱{{ number_format($totalProfit, 2) }}</span>
        <span class="text-xs">Total Profit</span>
    </div>
    <div class="container flex flex-col px-6 py-3 w-48 text-start rounded-md bg-[#5C717B]">
        <span class="font-semibold text-xl">₱{{ number_format($totalCost, 2) }}</span>
        <span class="text-xs">Total Cost</span>
    </div>
</div>

<section class="border w-full rounded-md border-solid border-black my-3 shadow-sm">
    <table class="w-full border-collapse table-fixed">
        <thead class="bg-main text-white">
            <tr>
                <th class="px-4 py-3 text-center">Date</th>
                <th class="px-4 py-3 text-center">Reference No.</th>
                <th class="px-4 py-3 text-center">Product</th>
                <th class="px-4 py-3 text-center">Quantity</th>
                <th class="px-4 py-3 text-center">Type</th>
                <th class="px-4 py-3 text-center">Remarks</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($movementsPaginator as $movement)
                <tr>
                    <td class="px-2 py-2 text-center">{{ \Carbon\Carbon::parse($movement['date'])->format('M d, Y') }}</td>
                    <td class="px-2 py-2 text-center">{{ $movement['reference_number'] }}</td>
                    <td class="px-2 py-2 text-center truncate">{{ $movement['product_name'] }}</td>
                    <td class="px-2 py-2 text-center {{ $movement['quantity'] < 0 ? 'text-red-600 font-semibold' : 'text-green-600 font-semibold' }}">
                        {{ $movement['quantity'] > 0 ? '+' : '' }}{{ $movement['quantity'] }}
                    </td>
                    <td class="px-2 py-2 text-center">
                        <span class="text-xs font-semibold px-2 py-1 rounded-xl 
                            {{ $movement['type'] === 'inflow' ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100' }}">
                            {{ ucfirst($movement['type']) }}
                        </span>
                    </td>
                    <td class="px-2 py-2 text-center">{{ $movement['remarks'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">
                        No product movements found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($movementsPaginator->total() > 0)
        <div class="mt-4 px-4 py-2 bg-gray-50 border-t">
            {{ $movementsPaginator->links() }}
        </div>
    @endif
</section>