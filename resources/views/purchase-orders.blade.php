<x-layout>
    <x-sidebar/>
    <div class="container w-auto ml-64 px-10 py-8 flex flex-col items-center content-start">
        <div x-data="{ close() { $refs.dialogRef.close() } }" class="container">
            <!-- SEARCH BAR AND CREATE BUTTON -->
            <div class="container flex items-center place-content-between">
                <div class="container flex items-center place-content-between">
                    <x-searchBar placeholder="Search purchase orders..." />
                    <form action="{{ route('purchase-orders.index') }}" method="GET" id="statusFilterForm" class="mr-auto ml-4">
                        <select name="status" class="truncate w-36 px-3 py-2 border rounded-md border-black" onchange="document.getElementById('statusFilterForm').submit()">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Confirmed" {{ request('status') === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="Delivered" {{ request('status') === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select> 
                    </form>
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
                                <option value="Cash on Delivery">Cash on Delivery</option>
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
                            <td class="truncate px-2 py-2 text-center">{{ $purchaseOrder->deliveryDate }}
                                <br>
                                <a href="/delivery-management" class="text-xs text-gray-500 underline uppercase rounded my-2 py-1 px-2 transition-all ease-in-out duration-100">
                                    Manage Delivery
                                </a>
                            </td>
                            <td class="truncate px-2 py-2 text-center">
                                <span class="px-2 py-1 text-sm font-semibold rounded-full 
                                    @if($purchaseOrder->orderStatus === 'Pending') text-yellow-400 bg-yellow-300/40
                                    @elseif($purchaseOrder->orderStatus === 'Confirmed') text-teal-400 bg-teal-200/40
                                    @elseif($purchaseOrder->orderStatus === 'Delivered') text-button-save bg-button-save/40
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
                                                        @if($purchaseOrder->orderStatus === 'Pending') text-yellow-400 bg-yellow-300/40
                                                        @elseif($purchaseOrder->orderStatus === 'Confirmed') text-teal-400 bg-teal-200/40
                                                        @elseif($purchaseOrder->orderStatus === 'Delivered') text-button-save bg-button-save/40
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
                                                            <td class="px-2 py-2">₱{{ number_format($item->unitPrice, 2) }}</td>
                                                            <td class="px-2 py-2">₱{{ number_format($item->totalAmount, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <!-- FORM BUTTONS AND DIALOGS -->
                                        <div class="container col-span-4 gap-x-4 place-content-start w-full flex items-start content-center px-6">

                                            <!-- EDIT BUTTON: Opens edit dialog -->
                                            <button 
                                                @click="$refs['editDialog{{ $purchaseOrder->id }}'].showModal()" 
                                                class="flex w-24 place-content-center rounded-md bg-button-create/70 px-3 py-2 text-blue-50 font-semibold items-center content-center hover:bg-button-create/70 transition-all duration-100 ease-in">
                                                Edit
                                            </button>

                                            <!-- DELETE BUTTON: Opens delete dialog -->
                                            <x-closeBtn @click="$refs['deleteDialog{{ $purchaseOrder->id }}'].showModal()">Delete</x-closeBtn>

                                            <!-- CLOSE BUTTON: Closes view details dialog -->
                                            <button 
                                                @click="$refs['viewDetailsDialog{{ $purchaseOrder->id }}'].close()" 
                                                class="flex rounded-md ml-auto font-semibold bg-gray-400 px-6 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition-all duration-100 ease-in">
                                                Close
                                            </button>

                                            <!-- EDIT DIALOG -->
                                            <dialog x-ref="editDialog{{ $purchaseOrder->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">
                                                    Update {{ $purchaseOrder->orderNumber }}
                                                </h1>
                                                <div class="container px-3 py-4">
                                                    <form action="{{ route('purchase-orders.update', $purchaseOrder->id) }}" method="POST" class="px-6 py-4 container grid grid-cols-4 gap-x-8 gap-y-6">
                                                        @csrf
                                                        @method('PUT')

                                                        <!-- Supplier -->
                                                        <div class="container text-start flex col-span-2 flex-col">
                                                            <label for="supplierId">Supplier</label>
                                                            <select name="supplierId" class="w-full px-3 py-2 border rounded-sm" required disabled>
                                                                <option value="{{ $purchaseOrder->supplier->id }}" selected>
                                                                    {{ $purchaseOrder->supplier->supplierName }}
                                                                </option>
                                                            </select>
                                                            <input type="hidden" name="supplierId" value="{{ $purchaseOrder->supplier->id }}">
                                                        </div>

                                                        <!-- Payment Terms -->
                                                        <div class="container text-start flex col-span-2 w-full flex-col">
                                                            <label for="paymentTerms">Payment Terms</label>
                                                            <select name="paymentTerms" class="px-3 py-2 border rounded-sm border-black" required>
                                                                <option value="Online" {{ $purchaseOrder->paymentTerms === 'Online' ? 'selected' : '' }}>Online</option>
                                                                <option value="Cash on Delivery" {{ $purchaseOrder->paymentTerms === 'Cash on Delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                                                            </select>
                                                        </div>

                                                        <!-- Order Status -->
                                                        <div class="container text-start flex col-span-2 w-full flex-col">
                                                            <label for="orderStatus">Order Status</label>
                                                            <select name="orderStatus" class="px-3 py-2 border rounded-sm border-black" required>
                                                                <option value="Pending" {{ $purchaseOrder->orderStatus === 'Pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="Confirmed" {{ $purchaseOrder->orderStatus === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                                <option value="Delivered" {{ $purchaseOrder->orderStatus === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                                                <option value="Cancelled" {{ $purchaseOrder->orderStatus === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                            </select>
                                                        </div>

                                                        <!-- Delivery Date -->
                                                        <x-form-input label="Expected Delivery Date" name="deliveryDate" type="date"
                                                            value="{{ \Carbon\Carbon::parse($purchaseOrder->deliveryDate)->format('Y-m-d') }}" 
                                                            min="{{ date('Y-m-d') }}"
                                                            class="col-span-2" required />

                                                        <!-- Items Table -->
                                                        <table class="col-span-4 w-full text-sm text-left p-2 my-3 text-gray-500">
                                                            <thead class="text-xs uppercase rounded-lg bg-main text-white px-4 py-2">
                                                                <tr>
                                                                    <th class="px-4 py-2">#</th>
                                                                    <th class="px-4 py-2">Product Name</th>
                                                                    <th class="px-4 py-2">Quantity</th>
                                                                    <th class="px-4 py-2">Unit Price</th>
                                                                    <th class="px-4 py-2">Total Amount</th>
                                                                    <th class="px-4 py-2">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($purchaseOrder->items as $index => $item)
                                                                    <tr class="bg-white border-b hover:bg-gray-50">
                                                                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                                                                        <td class="px-4 py-2">
                                                                            <input type="text" name="items[{{ $item->id }}][productName]" value="{{ $item->productName }}" class="border rounded px-2 py-1 w-full">
                                                                        </td>
                                                                        <td class="px-4 py-2">
                                                                            <input type="number" name="items[{{ $item->id }}][quantity]" value="{{ $item->quantity }}" min="1" class="border rounded px-2 py-1 w-20">
                                                                        </td>
                                                                        <td class="px-4 py-2">
                                                                            <input type="number" step="0.01" name="items[{{ $item->id }}][unitPrice]" value="{{ $item->unitPrice }}" class="border rounded px-2 py-1 w-24">
                                                                        </td>
                                                                        <td class="px-4 py-2">
                                                                            <input type="number" step="0.01" name="items[{{ $item->id }}][totalAmount]" value="{{ $item->totalAmount }}" readonly class="border rounded px-2 py-1 w-24 bg-gray-100 cursor-not-allowed">
                                                                        </td>
                                                                        <td class="px-4 py-2">
                                                                            <x-deleteBtn/>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">
                                                                            No products added to this order.
                                                                        </td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>

                                                        <!-- Footer Buttons -->
                                                        <div class="container col-span-4 gap-x-4 place-content-end w-full flex items-end content-center px-6">
                                                            <x-form-input label="Grand Total:" name="totalAmount" type="text" disabled
                                                                value="₱{{ number_format($purchaseOrder->totalAmount, 2) }}"
                                                                class="max-w-32 mr-auto text-lg font-semibold" />

                                                            <button type="button" @click="$refs['editDialog{{ $purchaseOrder->id }}'].close()" class="mr-2 px-4 py-2 rounded bg-gray-400 hover:bg-gray-300 text-white duration-200 transition-all ease-in-out ">
                                                                Cancel
                                                            </button>

                                                            <x-saveBtn>Save</x-saveBtn>
                                                        </div>
                                                    </form>
                                                </div>
                                            </dialog>

                                            <!-- DELETE DIALOG -->
                                            <dialog x-ref="deleteDialog{{ $purchaseOrder->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Delete Purchase Order?</h1>
                                                <div class="container px-3 py-4">
                                                    <form action="{{ route('purchase-orders.destroy', $purchaseOrder->id) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div>
                                                            <p>Are you sure you want to delete this purchase order?</p>
                                                        </div>
                                                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                                            <button type="button" @click="$refs['deleteDialog{{ $purchaseOrder->id }}'].close()" class="mr-2 px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
                                                            <x-closeBtn>Delete</x-closeBtn>
                                                        </div>
                                                    </form>
                                                </div>
                                            </dialog>

                                        </div>

                                    </div>
                                </dialog>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 px-4 py-2 bg-gray-50 ">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    </div>
</x-layout>