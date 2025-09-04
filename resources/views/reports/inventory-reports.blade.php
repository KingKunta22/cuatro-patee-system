<section class="border w-full rounded-md border-solid border-black my-3 shadow-sm">
    <table class="w-full border-collapse">
        <thead class="bg-main text-white table-fixed">
            <tr>
                <th class="px-4 py-3 text-center">Product SKU</th>
                <th class="px-4 py-3 text-center">Product Name</th>
                <th class="px-4 py-3 text-center">Category</th>
                <th class="px-4 py-3 text-center">Brand</th>
                <th class="px-4 py-3 text-center">Stock</th>
                <th class="px-4 py-3 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($inventories as $inventory)
                @php
                    // Determine status based on stock level
                    $status = 'Active Stock';
                    $statusClass = 'bg-green-100 text-green-600';
                    
                    if ($inventory->productStock == 0) {
                        $status = 'Out of stock';
                        $statusClass = 'bg-red-100 text-red-600';
                    } elseif ($inventory->productStock <= 10) {
                        $status = 'Low stock';
                        $statusClass = 'bg-yellow-100 text-yellow-600';
                    }
                @endphp
                
                <tr>
                    <td class="px-2 py-2 text-center">{{ $inventory->productSKU ?? 'N/A' }}</td>
                    <td class="px-2 py-2 text-center">{{ $inventory->productName ?? 'N/A' }}</td>
                    <td class="px-2 py-2 text-center">{{ $inventory->category->categoryName ?? $inventory->productCategory ?? 'N/A' }}</td>
                    <td class="px-2 py-2 text-center">{{ $inventory->brand->brandName ?? $inventory->productBrand ?? 'N/A' }}</td>
                    <td class="px-2 py-2 text-center font-semibold">{{ $inventory->productStock }}</td>
                    <td class="px-2 py-2 text-center">
                        <span class="text-sm font-semibold px-2 py-1 rounded-xl {{ $statusClass }}">
                            {{ $status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">
                        No inventory records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4 px-4 py-2 bg-gray-50 border-t">
        {{ $inventories->links() }}
    </div>
</section>
