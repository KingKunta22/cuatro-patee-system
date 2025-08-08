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
                                All
                            </option>
                            <option class="truncate w-36" value="">
                                Pending
                            </option>
                            <option class="truncate w-36" value="">
                                Delivered
                            </option>
                            <option class="truncate w-36" value="">
                                Cancelled
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
                    @if(session('keep_modal_open'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            document.querySelector('[x-ref="dialogRef"]').showModal();
                        });
                    </script>
                    @endif
                    <form action="{{ route('purchase-orders.add-item') }}" method="POST" class="px-6 py-4 container grid grid-cols-4 gap-x-8 gap-y-6">
                        @csrf
                        <div class="container text-start flex col-span-2 flex-col">
                            <label for="supplierId">Supplier</label>
                            <select name="supplierId" class="w-full px-3 py-2 border rounded-sm" required @if($lockedSupplierId) disabled @endif>
                            <option value="" disabled selected>Choose Supplier</option>
                                @foreach($supplierNames as $supplier)
                                    <option value="{{ $supplier->id }}" @selected($lockedSupplierId == $supplier->id)>
                                        {{ $supplier->supplierName }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Hidden field ensures value submits when disabled -->
                            @if($lockedSupplierId)
                                <input type="hidden" name="supplierId" value="{{ $lockedSupplierId }}">
                            @endif
                        </div>
                        <x-form-input label="Product Name" name="productName" type="text" class="col-span-2" value="" required/>
                        <div class="container text-start flex col-span-2 w-full flex-col">
                            <label for="paymentTerms">Payment Terms</label>
                            <select name="paymentTerms" class="px-3 py-2 border rounded-sm border-black" required>
                                <option value="" disabled selected>Select Payment Terms</option>
                                <option value="Online">Online</option>
                                <option value="COD">COD</option>
                            </select>
                        </div>
                        <x-form-input label="Unit Price" name="unitPrice" type="number" step="0.01" value="" required />
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
                                            <form action="{{ route('purchase-orders.remove-item', $index) }}" method="POST" class="flex place-content-center">
                                                @csrf
                                                @method('DELETE')
                                                <button>
                                                    <x-deleteBtn />
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
                        <form action="{{ route('purchase-orders.clearSession') }}" method="POST">
                            @csrf
                            <x-closeBtn @click="close()">Cancel</x-closeBtn>
                        </form>
                         <form action="{{ route('purchase-orders.store') }}" method="POST">
                            @csrf
                             <x-saveBtn>Save</x-saveBtn>
                        </form>
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
                <tbody>
                    @foreach($purchaseOrders as $purchaseOrder)
                        <tr class="border-b" x-data="{ closeEdit() { $refs['editDialog{{ $supplier->id }}'].close() }, 
                            closeDelete() { $refs['deleteDialog{{ $purchaseOrder->id }}'].close() } }">
                            <td class="truncate px-2 py-2 text-center" title="{{ $purchaseOrder->orderNumber }}">{{ $purchaseOrder->orderNumber }}</td>
                            <td class="truncate px-2 py-2 text-center" title="{{ $purchaseOrder->supplier->supplierName }}">
                                <!-- Accesses the supplier name -->
                                {{ $purchaseOrder->supplier->supplierName}} 
                            </td>
                            <td class="truncate px-2 py-2 text-center">{{ $purchaseOrder->created_at->format('m-d-Y') }}</td>
                            <td class="truncate px-2 py-2 text-center">
                                @foreach($purchaseOrder->items as $item)
                                    {{ $item->productName }} ({{ $item->quantity }})
                                    @if(!$loop->last)<br>@endif
                                @endforeach
                            </td>
                            <td class="truncate px-2 py-2 text-center">₱{{ number_format($purchaseOrder->totalAmount, 2) }}</td>
                            <td class="truncate px-2 py-2 text-center">{{ $purchaseOrder->deliveryDate }}</td>
                            <td class="truncate px-2 py-2 text-center">
                                <span class="px-2 py-1 text-sm font-semibold rounded-full 
                                    @if($purchaseOrder->orderStatus === 'Pending') text-yellow-400 bg-yellow-300/30
                                    @elseif($purchaseOrder->orderStatus === 'Completed') text-button-save bg-button-save/30
                                    @else text-button-delete bg-button-delete/30  @endif">
                                    {{ $purchaseOrder->orderStatus }}
                                </span>
                            </td>
                            
                            <!-- VIEW DETAILS ACTIONS -->
                            <td class="truncate py-3 max-w-32 px-2 text-center place-content-center">

                                <!-- VIEW PURCHASE ORDER DETAILS BUTTON FOR PURCHASE ORDER DETAILS FORM -->
                                <button @click="$refs['viewDetailsDialog{{ $purchaseOrder->id }}'].showModal()" class="flex rounded-md bg-gray-400 px-3 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition:all duration-100 ease-in font-semibold">View Details</button>
                                
                                <!-- VIEW PURCHASE ORDER DETAILS FORM -->
                                <dialog x-ref="viewDetailsDialog{{ $purchaseOrder->id }}">
                                    <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Purchase Order Details</h1>

                                    <!-- CONTAINER FOR EVERYTHING INSIDE THE VIEW DETAILS MODAL -->
                                    <div class="container px-3 py-4">

                                        <!-- ADD ORDER FORM -->
                                        {{-- Purchase Order Details --}}
                                        <div class="px-6 py-4 container grid grid-cols-4 gap-x-8 gap-y-6">
                                            <div class="col-span-2">
                                                <label class="font-semibold">Order Number</label>
                                                <p>{{ $purchaseOrder->orderNumber }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <label class="font-semibold">Supplier Name</label>
                                                <p>{{ $purchaseOrder->supplier->supplierName }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <label class="font-semibold">Payment Terms</label>
                                                <p>{{ ucfirst($purchaseOrder->paymentTerms) }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <label class="font-semibold">Expected Delivery Date</label>
                                                <p>{{ \Carbon\Carbon::parse($purchaseOrder->deliveryDate)->format('F d, Y') }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <label class="font-semibold">Grand Total</label>
                                                <p>₱{{ number_format($purchaseOrder->totalAmount, 2) }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <label class="font-semibold">Status</label>
                                                <div class="truncate px-2 py-2 text-center">
                                                    <span class="px-2 py-1 text-sm font-semibold rounded-full 
                                                        @if($purchaseOrder->orderStatus === 'Pending') text-yellow-400 bg-yellow-300/30
                                                        @elseif($purchaseOrder->orderStatus === 'Completed') text-button-save bg-button-save/30
                                                        @else text-button-delete bg-button-delete/30  @endif">
                                                        {{ $purchaseOrder->orderStatus }}
                                                    </span>
                                                </div>
                                            </div>

                                        </div>

                                        {{-- Items Table --}}
                                        <div class="border w-auto rounded-md border-solid border-black p-3 my-4 mx-6">
                                            <table class="w-full">
                                                <thead class="bg-main text-white">
                                                    <tr>
                                                        <th class="px-2 py-2 text-sm">Items</th>
                                                        <th class="px-2 py-2 text-sm">Quantity</th>
                                                        <th class="px-2 py-2 text-sm">Unit Price</th>
                                                        <th class="px-2 py-2 text-sm">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($purchaseOrder->items as $item)
                                                        <tr class="border-b text-center">
                                                            <td class="px-2 py-2">{{ $item->productName }}</td>
                                                            <td class="px-2 py-2">{{ $item->quantity }}</td>
                                                            <td class="px-2 py-2">{{ number_format($item->unitPrice, 2) }}</td>
                                                            <td class="px-2 py-2">{{ number_format($item->totalAmount, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        
                                        <!-- FORM BUTTONS -->
                                        <div class="container col-span-4 gap-x-4 place-content-start w-full flex items-start content-center px-6">
                                            
                                            <!-- EDIT BUTTON FOR EDIT FORM -->
                                            <button @click="$refs['editDialog{{ $purchaseOrder->id }}'].showModal()" class="flex w-24 place-content-center rounded-md bg-button-create/70 px-3 py-2 text-blue-50 font-semibold 0 items-center content-center hover:bg-button-create/70 transition:all duration-100 ease-in">Edit</button>
                                            <!-- EDIT FORM -->
                                            <dialog x-ref="editDialog{{ $purchaseOrder->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Update Purchase Order</h1>
                                                <div class="container px-3 py-4">
                                                    <!-- ADD ORDER FORM -->
                                                    @if(session('keep_modal_open'))
                                                    <script>
                                                        document.addEventListener('DOMContentLoaded', function() {
                                                            document.querySelector('[x-ref="dialogRef"]').showModal();
                                                        });
                                                    </script>
                                                    @endif
                                                    <form action="{{ route('purchase-orders.add-item') }}" method="POST" class="px-6 py-4 container grid grid-cols-4 gap-x-8 gap-y-6">
                                                        @csrf
                                                        <div class="container text-start flex col-span-2 flex-col">
                                                            <label for="supplierId">Supplier</label>
                                                            <select name="supplierId" class="w-full px-3 py-2 border rounded-sm" required @if($lockedSupplierId) disabled @endif>
                                                            <option value="" disabled selected>Choose Supplier</option>
                                                                @foreach($supplierNames as $supplier)
                                                                    <option value="{{ $supplier->id }}" @selected($lockedSupplierId == $supplier->id)>
                                                                        {{ $supplier->supplierName }}
                                                                    </option>
                                                                @endforeach
                                                            </select>

                                                            <!-- Hidden field ensures value submits when disabled -->
                                                            @if($lockedSupplierId)
                                                                <input type="hidden" name="supplierId" value="{{ $lockedSupplierId }}">
                                                            @endif
                                                        </div>
                                                        <x-form-input label="Product Name" name="productName" type="text" class="col-span-2" value="" required/>
                                                        <div class="container text-start flex col-span-2 w-full flex-col">
                                                            <label for="paymentTerms">Payment Terms</label>
                                                            <select name="paymentTerms" class="px-3 py-2 border rounded-sm border-black" required>
                                                                <option value="" disabled selected>Select Payment Terms</option>
                                                                <option value="Online">Online</option>
                                                                <option value="COD">COD</option>
                                                            </select>
                                                        </div>
                                                        <x-form-input label="Unit Price" name="unitPrice" type="number" step="0.01" value="" required />
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
                                                                            <form action="{{ route('purchase-orders.remove-item', $index) }}" method="POST" class="flex place-content-center">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button>
                                                                                    <x-deleteBtn />
                                                                                </button>
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <!-- EMPTY STATE -->
                                                        <div class="border w-auto rounded-md border-solid border-black p-3 my-4 mx-6">
                                                            <div class="text-center py-8 text-gray-500">
                                                                <p>No items added yet. Add items above to preview your order.</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <!-- FORM BUTTONS FOR EDIT FORM-->
                                                    <div class="container col-span-4 gap-x-4 place-content-end w-full flex items-end content-center px-6">
                                                        <form action="{{ route('purchase-orders.clearSession') }}" method="POST">
                                                            @csrf
                                                            <x-closeBtn @click="close()">Cancel</x-closeBtn>
                                                        </form>
                                                        <form action="{{ route('purchase-orders.store') }}" method="POST">
                                                            @csrf
                                                            <x-saveBtn>Save</x-saveBtn>
                                                        </form>
                                                    </div>
                                                </div>
                                            </dialog>
                                                                                

                                            <!--DELETE BUTTON FOR DELETE FORM -->
                                            <x-closeBtn @click="$refs['deleteDialog{{ $purchaseOrder->id }}'].showModal()">Delete</x-closeBtn>
                                            <!-- DELETE FORM -->
                                            <dialog x-ref="deleteDialog{{ $purchaseOrder->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Delete Supplier?</h1>
                                                <div class="container px-3 py-4">
                                                    <form action="{{ route('purchase-orders.destroy', $purchaseOrder->id) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div>
                                                            <h1>Are you sure you want to delete this purchase order?</h1>
                                                        </div>
                                                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                                            <x-closeBtn type="button" @click="closeDelete()">Cancel</x-closeBtn>
                                                            <x-saveBtn>Delete</x-saveBtn>
                                                        </div>
                                                    </form>
                                                </div>
                                            </dialog>


                                            <!-- CLOSE BUTTON -->
                                            <button @click="$refs['viewDetailsDialog{{ $purchaseOrder->id }}'].close()" class="flex rounded-md ml-auto font-semibold bg-gray-400 px-6 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition:all duration-100 ease-in">Close</button>


                                        </div>
                                    </div>
                                </dialog>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layout>