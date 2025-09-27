<section class="border w-full rounded-md border-solid border-black my-3 shadow-sm">

    <table class="w-full border-collapse">
        <thead class="bg-main text-white table-fixed">
            <tr>
                <th class="px-4 py-3 text-center">Product Name</th>
                <th class="px-4 py-3 text-center">Category</th>
                <th class="px-4 py-3 text-center">SKU</th>
                <th class="px-4 py-3 text-center">Brand</th>
                <th class="px-4 py-3 text-center">Price</th>
                <th class="px-4 py-3 text-center">Stock</th>
                <th class="px-4 py-3 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($products as $product)
                @php
                    // Use the computed total stock from Product model
                    $totalStock = $product->total_stock;
                    $earliestExpiry = $product->earliest_expiration_date;
                @endphp
                <tr class="border-b">
                    <td class="px-2 py-2 text-center truncate" title="{{ $product->productName }}">
                        {{ $product->productName }}
                    </td>
                    <td class="px-2 py-2 text-center truncate" title="{{ $product->category->productCategory ?? 'N/A' }}">
                        {{ $product->category->productCategory ?? 'N/A' }}
                    </td>
                    <td class="px-2 py-2 text-center truncate" title="{{ $product->productSKU }}">
                        {{ $product->productSKU }}
                    </td>
                    <td class="px-2 py-2 text-center truncate" title="{{ $product->brand->productBrand ?? 'N/A' }}">
                        {{ $product->brand->productBrand ?? 'N/A' }}
                    </td>
                    <td class="px-2 py-2 text-center truncate" title="₱{{ number_format($product->productSellingPrice, 2) }}">
                        ₱{{ number_format($product->productSellingPrice, 2) }}
                    </td>
                    <td class="px-2 py-2 text-center truncate" title="{{ $totalStock }}">
                        {{ $totalStock }}
                    </td>
                    <td class="px-2 py-2 text-center truncate text-sm font-semibold">
                        @if ($totalStock == 0)
                            <span class="text-red-600 bg-red-100 px-2 py-1 rounded-xl" title="This item is out of stock (0 units available)">
                                Out of Stock
                            </span>
                        @elseif ($totalStock <= 10)
                            <span class="text-yellow-600 bg-yellow-100 px-2 py-1 rounded-xl" title="This item has low stock (only {{ $totalStock }} units left)">
                                Low Stock
                            </span>
                        @else
                            <span class="text-green-600 bg-green-100 px-2 py-1 rounded-xl">Active Stock</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-gray-500">
                        No products found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4 px-4 py-2 bg-gray-50">
        {{ $products->appends(['tab' => 'inventory'])->links() }}
    </div>
</section>