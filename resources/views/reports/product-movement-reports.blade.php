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
                    No product movements found.
                </td>
            </tr>
        </tbody>
    </table>

    {{-- <div class="mt-4 px-4 py-2 bg-gray-50 border-t">
        {{ $movements->links() }}
    </div> --}}
</section>
