<x-layout>
    <x-sidebar/>
    <div class="container w-auto ml-64 px-10 py-8 flex flex-col items-center content-start">
        <div x-data="{ close() { $refs.dialogRef.close() } }" class="container">
            <!-- SEARCH BAR AND CREATE BUTTON -->
            <div class="container flex items-center place-content-between">
                <div class="container flex items-center place-content-between">
                    <x-searchBar placeholder="Search purchase orders..." />
                    <select name="status" class="truncate w-36 px-3 py-2 border ml-4 mr-auto rounded-md border-black">
                            <option class="truncate w-36" value="">
                                Status
                            </option>
                    </select> 
                   <x-createBtn @click="$refs.dialogRef.showModal()">Add New Order</x-createBtn>
                </div>
            </div>
            <!--Modal Form -->
            <dialog x-ref="dialogRef" class="w-1/2 my-auto shadow-2xl rounded-md">
                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Add Order</h1>
                <div class="container px-3 py-4">
                    <!-- ADD ORDER FORM -->
                    <form action="{{ route('purchase-orders.add-item') }}" method="POST" class="px-6 py-4 container grid grid-cols-4 gap-x-8 gap-y-6">
                        @csrf
                        <div class="container text-start flex col-span-2 flex-col">
                            <label for="supplierId">Supplier</label>
                            <select name="supplierId" class="w-full px-3 py-2 border mr-auto rounded-sm border-black" required>
                                <option value="" disabled selected>Choose Supplier</option>
                                @foreach($supplierNames as $supplierName)
                                    <option value="{{ $supplierName->id }}">
                                        {{ $supplierName->supplierName }}
                                    </option>
                                @endforeach
                            </select> 
                        </div>
                        <x-form-input label="Product Name" name="productName" type="text" class="col-span-2" value="" required/>
                        <div class="container text-start flex col-span-2 w-full flex-col">
                            <label for="paymentTerms">Payment Terms</label>
                            <select name="paymentTerms" class="px-3 py-2 border rounded-sm border-black" required>
                                <option value="" disabled selected>Select Payment Terms</option>
                                <option value="Online">Online</option>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                            </select>
                        </div>
                        <x-form-input label="Unit Price" name="unitPrice" type="number" step="0.01" value="" required/>
                        <x-form-input label="Quantity" name="quantity" type="number" value="" required/>
                        <x-form-input label="Expected Delivery Date" name="deliveryDate" type="date" value="" class="col-span-2" required/>
                        <x-form-input label="Total" name="totalAmount" type="number" disabled value=""/>
                        <div class="flex content-between items-end w-full ">
                            <button type="submit" class='bg-button-delete/70 px-4 py-2 rounded text-white hover:bg-button-delete'>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 pr-2 inline">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Add
                            </button>
                        </div>
                    </form>

                    <!-- PREVIEW TABLE FOR ADDED ORDERS -->
                    @if(session('purchase_order_items'))
                        <div class="border w-auto rounded-md border-solid border-black p-3 my-4 mx-6">
                            <table class="w-full">
                                <thead class="rounded-lg bg-main text-white px-4 py-2">
                                    <tr class="rounded-lg">
                                        <th class="bg-main px-2 py-2 text-sm">Items</th>
                                        <th class="bg-main px-2 py-2 text-sm">Quantity</th>
                                        <th class="bg-main px-2 py-2 text-sm">Unit Price</th>
                                        <th class="bg-main px-2 py-2 text-sm">Total</th>
                                        <th class="bg-main px-2 py-2 text-sm">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(session('purchase_order_items') as $index => $item)
                                        <tr class="border-b">
                                            <td class="px-2 py-2 text-center">{{ $item['productName'] }}</td>
                                            <td class="px-2 py-2 text-center">{{ $item['quantity'] }}</td>
                                            <td class="px-2 py-2 text-center">₱{{ number_format($item['unitPrice'], 2) }}</td>
                                            <td class="px-2 py-2 text-center">₱{{ number_format($item['totalAmount'], 2) }}</td>
                                            <td class="px-2 py-2 text-center">
                                                <form action="{{ route('purchase-orders.remove-item', $index) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 px-2 py-1 rounded">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-7.82 0c-1.18.037-2.09 1.022-2.09 2.201v.916m15.5 0a48.108 48.108 0 00-3.478-.397m-7.5 0c-.41 0-.806.018-1.186.052" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            <!-- SAVE BUTTON FOR COMPLETE ORDER -->
                            <div class="mt-4 flex justify-end">
                                <form action="{{ route('purchase-orders.store') }}" method="POST">
                                    @csrf
                                    <x-saveBtn>Save Complete Order</x-saveBtn>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- EMPTY STATE -->
                        <div class="border w-auto rounded-md border-solid border-black p-3 my-4 mx-6">
                            <div class="text-center py-8 text-gray-500">
                                <p>No items added yet. Add items above to preview your order.</p>
                            </div>
                        </div>
                    @endif

                    <!-- FORM BUTTONS -->
                    <div class="container col-span-4 gap-x-4 place-content-end w-full flex items-end content-center px-6">
                        <x-closeBtn @click="close()">Cancel</x-closeBtn>
                    </div>
                </div>
            </dialog>
        </div>
        <!-- TABLE FOR PURCHASE ORDER DETAILS -->
        <div class="border w-full rounded-md border-solid border-black p-3 my-8">
            <table class="w-full">
                <thead class="rounded-lg bg-main text-white px-4 py-2">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-2">Order Number</th>
                        <th class=" bg-main px-4 py-2">Supplier Name</th>
                        <th class=" bg-main px-4 py-2">Date</th>
                        <th class=" bg-main px-4 py-2">Items</th>
                        <th class=" bg-main px-4 py-2">Total</th>
                        <th class=" bg-main px-4 py-2">Expected Delivery Date</th>
                        <th class=" bg-main px-4 py-2">Status</th>
                        <th class=" bg-main px-4 py-2">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</x-layout>