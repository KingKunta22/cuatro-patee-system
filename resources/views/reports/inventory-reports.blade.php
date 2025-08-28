<section class="border w-full rounded-md border-solid border-black my-3 shadow-sm">
    <table class="w-full border-collapse">
        <thead class="bg-main text-white">
            <tr>
                <th class="px-4 py-3 text-center">Product Code</th>
                <th class="px-4 py-3 text-center">Product Name</th>
                <th class="px-4 py-3 text-center">Category</th>
                <th class="px-4 py-3 text-center">Brand</th>
                <th class="px-4 py-3 text-center">Stock</th>
                <th class="px-4 py-3 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr class="hover:bg-gray-50 transition">
                <td class="px-2 py-2 text-center"></td>
                <td class="px-2 py-2 text-center"></td>
                <td class="px-2 py-2 text-center"></td>
                <td class="px-2 py-2 text-center"></td>
                <td class="px-2 py-2 text-center"></td>
                <td class="px-2 py-2 text-center"></td>
            </tr>
            <tr>
                <td colspan="6" class="text-center py-4 text-gray-500">
                    No inventory records found.
                </td>
            </tr>
        </tbody>
    </table>

    {{-- <div class="mt-4 px-4 py-2 bg-gray-50 border-t">
        {{ $inventories->links() }}
    </div> --}}
</section>
