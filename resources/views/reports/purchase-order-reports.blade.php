<section class="border w-full rounded-md border-solid border-black my-3 shadow-sm">
    <table class="w-full border-collapse">
        <thead class="bg-main text-white">
            <tr>
                <th class="px-4 py-3 text-center">PO Number</th>
                <th class="px-4 py-3 text-center">Supplier</th>
                <th class="px-4 py-3 text-center">Date</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-center">Total</th>
                <th class="px-4 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr class="hover:bg-gray-50 transition">
                <td class="px-2 py-2 text-center"></td>
                <td class="px-2 py-2 text-center"></td>
                <td class="px-2 py-2 text-center"></td>
                <td class="px-2 py-2 text-center"></span>
                </td>
                <td class="px-2 py-2 text-center"></td>
                <td class="truncate px-2 py-2 text-center flex justify-center items-center">
                    <button @click=""
                        class="flex rounded-md bg-gray-400 px-3 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition:all duration-100 ease-in font-semibold">
                        View Details
                    </button>
                </td>
            </tr>
            <tr>
                <td colspan="6" class="text-center py-4 text-gray-500">
                    No purchase orders found.
                </td>
            </tr>
        </tbody>
    </table>

    {{-- <div class="mt-4 px-4 py-2 bg-gray-50 border-t">
        {{ $purchaseOrders->links() }}
    </div> --}}
</section>
