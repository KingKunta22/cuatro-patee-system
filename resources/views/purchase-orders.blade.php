<x-layout>
    <x-sidebar/>
    <div class="container w-auto ml-64 px-10 py-6 flex flex-col items-center content-start">
        <div x-data="{ close() { $refs.dialogRef.close() } }" class="container">

            <!-- SUCCESS MESSAGE POPUP -->
            @if(session('download_pdf'))
                <div id="pdf-success-message" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                    <p>Purchase order saved successfully! PDF download will start automatically.</p>
                </div>
            @endif
            
            @if(session('success'))
                <div id="success-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <!-- AUTO-HIDE SUCCESS MESSAGES -->
            <script>
                // Hide success messages after 3 seconds
                document.addEventListener('DOMContentLoaded', function() {
                    const pdfMessage = document.getElementById('pdf-success-message');
                    const successMessage = document.getElementById('success-message');
                    
                    if (pdfMessage) {
                        setTimeout(() => {
                            pdfMessage.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                            pdfMessage.style.opacity = '0';
                            pdfMessage.style.transform = 'translate(-50%, -20px)';
                            setTimeout(() => {
                                pdfMessage.remove();
                            }, 500);
                        }, 3000);
                    }
                    
                    if (successMessage) {
                        setTimeout(() => {
                            successMessage.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                            successMessage.style.opacity = '0';
                            successMessage.style.transform = 'translate(-50%, -20px)';
                            setTimeout(() => {
                                successMessage.remove();
                            }, 500);
                        }, 3000);
                    }
                });
            </script>

            @if(session('error'))
            <div id="error-message" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-red-100 border border-red-400 text-red-700 rounded shadow-lg">
                <p>{{ session('error') }}</p>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const errorMessage = document.getElementById('error-message');
                    if (errorMessage) {
                        setTimeout(() => {
                            errorMessage.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                            errorMessage.style.opacity = '0';
                            errorMessage.style.transform = 'translate(-50%, -20px)';
                            setTimeout(() => {
                                errorMessage.remove();
                            }, 500);
                        }, 3000);
                    }
                });
            </script>
            @endif



            
            <!-- SEARCH BAR AND CREATE BUTTON -->
            <div class="container flex items-center place-content-between">
                <div class="container flex items-center place-content-between">

                    <!-- SEPARATE SEARCH/FILTER FORM - WON'T AFFECT OTHER FORMS -->
                    <form action="{{ route('purchase-orders.index') }}" method="GET" id="statusFilterForm" class="mr-auto flex">

                        <!-- Simple Search Input -->
                        <div class="relative">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}"
                                placeholder="Search orders..." 
                                autocomplete="off"
                                class="pl-10 pr-4 py-2 border border-black rounded-md w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Search Button -->
                        <button type="submit" class="px-4 mx-3 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            Search
                        </button>

                        <!-- Clear Button (only show when filters are active) -->
                        @if(request('search') )
                            <a href="{{ route('purchase-orders.index') }}" class="px-4 mx-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Clear
                            </a>
                        @endif
                    </form>

                    <!-- ADD NEW ORDER BUTTON -->
                   <x-form.createBtn @click="$refs.dialogRef.showModal()">Add New Order</x-form.createBtn>

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
                            <select name="supplierId" class="w-full px-3 py-2 border rounded-sm border-black" required @if($lockedSupplierId) disabled @endif>
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
                        <x-form.form-input label="Product Name" name="productName" type="text" class="col-span-2" value="" required/>
                        <div class="container text-start flex col-span-2 w-full flex-col">
                            <label for="paymentTerms">Payment Terms</label>
                            <select name="paymentTerms" class="px-3 py-2 border rounded-sm border-black" required @if($lockedSupplierId) disabled @endif>
                                <option value="" disabled selected>Select Payment Terms</option>
                                <option value="Online" @selected(session('purchase_order_items.0.paymentTerms') === 'Online')>Online</option>
                                <option value="Cash on Delivery" @selected(session('purchase_order_items.0.paymentTerms') === 'Cash on Delivery')>Cash on Delivery</option>
                            </select>
                            @if($lockedSupplierId)
                                <input type="hidden" name="paymentTerms" value="{{ session('purchase_order_items.0.paymentTerms') }}">
                            @endif
                        </div>
                        
                        <!-- EXPECTED DELIVERY DATE (Lockable) -->
                        <div class="container text-start flex col-span-2 flex-col">
                            <label for="deliveryDate">Expected Delivery Date</label>
                            <input type="date" name="deliveryDate" value="{{ session('purchase_order_items.0.deliveryDate', '') }}" class=" border-black w-full px-3 py-2 border rounded-sm" required min="{{ date('Y-m-d') }}" @if($lockedSupplierId) readonly @endif />
                            @if($lockedSupplierId)
                                <input type="hidden" name="deliveryDate" value="{{ session('purchase_order_items.0.deliveryDate') }}">
                            @endif
                        </div>

                        <x-form.form-input label="Unit Price (₱)" name="unitPrice" type="number" step="0.01" value="" required />
                        <x-form.form-input label="Quantity" name="quantity" type="number" value="" required/>

                        <!-- MEASUREMENT PER ITEM DROPDOWN -->
                        <div class="container text-start flex col-span-2 w-full flex-col">
                            <label for="itemMeasurement">Measurement per item</label>
                            <select name="itemMeasurement" class="px-3 py-2 border rounded-sm border-black" required>
                                <option value="" disabled selected>Select Measurement</option>
                                <option value="kilogram">kilogram (kg)</option>
                                <option value="gram">gram (g)</option>
                                <option value="liter">liter (L)</option>
                                <option value="milliliter">milliliter (mL)</option>
                                <option value="pcs">pieces (pcs)</option>
                                <option value="set">set</option>
                                <option value="pair">pair</option>
                                <option value="pack">pack</option>
                            </select>
                        </div>


                        <!-- Gets the total amount automatically from added items inside the session -->
                        @php
                            $totalAmount = collect(session('purchase_order_items', []))->sum('totalAmount');
                        @endphp
                        <x-form.form-input label="GRAND TOTAL:" class="font-semibold col-span-2" name="totalAmount" type="text" readonly value="₱{{ number_format($totalAmount, 2) }}"/>




                        <!-- ADD BUTTON FOR ADDING ITEMS TO SESSION -->
                        <div class="flex items-end content-center place-content-center w-full">
                            <button type="submit" class= 'bg-teal-500/70 px-4 py-2 rounded text-white hover:bg-teal-500 w-full'>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7 pr-2 inline">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Add Item
                            </button>
                        </div>

                        <!-- AUTO PDF DOWNLOAD SCRIPT -->
                        @if(session('download_pdf'))
                        <script>
                            window.onload = function() {
                                // Automatically trigger PDF download
                                window.open('{{ route("purchase-orders.download-pdf", session("download_pdf")) }}', '_blank');
                            };
                        </script>
                        @endif


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
                                        <th class="bg-main px-2 py-2 text-sm">Measurement</th>
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
                                        <td class="px-2 py-2 text-center">{{ $item['itemMeasurement'] }}</td>
                                        <td class="px-2 py-2 text-center">₱{{ number_format($item['totalAmount'], 2) }}</td>
                                        <td class="px-2 py-2 text-center">
                                            <form action="{{ route('purchase-orders.remove-item', $index) }}" method="POST" class="flex place-content-center">
                                                @csrf
                                                @method('DELETE')
                                                <button>
                                                    <x-form.deleteBtn />
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


                    <!-- FORM BUTTONS (SAYOP NI KAY DI DAPAT IWRAP UG ANOTHER FORM ANG BUTTONS FOR A SPECIFIC FORM :D) -->
                    <div class="flex justify-end items-center w-full px-6 relative">
    
                        <!-- Cancel button in its own form -->
                        <form action="{{ route('purchase-orders.clearSession') }}" id="cancelForm" method="POST">
                            @csrf
                            <button type="button" @click="$refs.confirmCancel.showModal()" class="flex place-content-center rounded-md bg-button-delete px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in">
                                Cancel
                            </button>

                        </form>
                    
                        <!-- Save form -->
                        <form action="{{ route('purchase-orders.store') }}" id="saveForm" method="POST" class="flex items-center space-x-6">
                            @csrf
                    
                            <div class="absolute bottom-2 left-0 flex flex-row">

                                <label class="flex items-center space-x-1">
                                    <input type="checkbox" name="savePDF">
                                    <span>Save as PDF</span>
                                </label>

                                <label class="flex items-center space-x-1 ml-4">
                                    <input type="checkbox" name="sendEmail">
                                    <span>Send Email</span>
                                </label>

                            </div>
                    
                        <x-form.saveBtn type="button" 
                            @click="{{ count(session('purchase_order_items', [])) > 0 ? '$refs.confirmSubmit.showModal()' : 'Toast.error(\'Add at least one item first before proceeding\')' }}">
                            Save
                        </x-form.saveBtn>

                        </form>


                        <!-- CONFIRM CANCEL/SAVE MODALS -->
                        <x-modal.createModal x-ref="confirmCancel">

                            <x-slot:dialogTitle>Confirm Cancel?</x-slot:dialogTitle>

                            <h1 class="text-xl px-4 py-3">Are you sure you want to cancel this purchase order?</h1>
                            <div class="container flex w-full flex-row items-center content-end place-content-end px-4 py-3">
                                <button type="button" @click="$refs.confirmCancel.close()" class="mr-3 flex place-content-center rounded-md bg-button-delete px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in">
                                    Cancel
                                </button>
                                <x-form.saveBtn type="submit" form="cancelForm">Confirm</x-form.saveBtn>
                            </div>

                        </x-modal.createModal>

                        <x-modal.createModal x-ref="confirmSubmit">

                            <x-slot:dialogTitle>Confirm Save?</x-slot:dialogTitle>

                            <h1 class="text-xl px-4 py-3">Are you sure you want to save this purchase order?</h1>
                            <div class="container flex w-full flex-row items-center content-end place-content-end px-4 py-3">
                                <button type="button" @click="$refs.confirmSubmit.close()" class="mr-3 flex place-content-center rounded-md bg-button-delete px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in">
                                    Cancel
                                </button>
                                <x-form.saveBtn type="submit" form="saveForm">Confirm</x-form.saveBtn>
                            </div>

                        </x-modal.createModal>

                    </div>
                </div>
            </dialog>
        </div>


        <!-- TABLE FOR PURCHASE ORDER DETAILS -->
        <div class="border w-full rounded-md border-solid border-black mt-6 mb-2">
            <table class="w-full table-fixed">
                <thead class="rounded-lg bg-main text-white px-4 py-3">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-3">Order Number</th>
                        <th class=" bg-main px-4 py-3">Supplier Name</th>
                        <th class=" bg-main px-4 py-3">Date</th>
                        <th class=" bg-main px-4 py-3">Items</th>
                        <th class=" bg-main px-4 py-3">Total</th>
                        <th class=" bg-main px-4 py-3">Action</th>
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
                            
                            <!-- VIEW DETAILS ACTIONS -->
                            <td class="truncate py-3 max-w-32 px-2 text-center place-content-center">

                                <!-- VIEW PURCHASE ORDER DETAILS BUTTON FOR PURCHASE ORDER DETAILS FORM -->
                                <button @click="$refs['viewDetailsDialog{{ $purchaseOrder->id }}'].showModal()" class="flex rounded-md mx-auto bg-gray-400 px-3 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition:all duration-100 ease-in font-semibold">View Details</button>
                                
                                <!-- VIEW PURCHASE ORDER DETAILS FORM -->
                                <dialog x-ref="viewDetailsDialog{{ $purchaseOrder->id }}">
                                    <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Purchase Order Details</h1>

                                    <!-- CONTAINER FOR EVERYTHING INSIDE THE VIEW DETAILS MODAL -->
                                    <div class="container px-3 py-4">

                                        {{-- Purchase Order Details --}}
                                        <div class="px-6 py-4 container grid grid-cols-4 gap-5 text-start">
                                            <div class="bg-gray-50 p-3 rounded-md col-span-2">
                                                <label class="font-semibold">Order Number</label>
                                                <p>{{ $purchaseOrder->orderNumber }}</p>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded-md col-span-2">
                                                <label class="font-semibold">Supplier Name</label>
                                                <p>{{ $purchaseOrder->supplier->supplierName }}</p>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded-md col-span-2">
                                                <label class="font-semibold">Payment Terms</label>
                                                <p>{{ ucfirst($purchaseOrder->paymentTerms) }}</p>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded-md col-span-2">
                                                <label class="font-semibold">Expected Delivery Date</label>
                                                <p>{{ \Carbon\Carbon::parse($purchaseOrder->deliveryDate)->format('F d, Y') }}</p>
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
                                                        <th class="px-2 py-2 text-sm">Measurement</th>
                                                        <th class="px-2 py-2 text-sm">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($purchaseOrder->items as $item)
                                                        <tr class="border-b text-center">
                                                            <td class="px-2 py-2">{{ $item->productName }}</td>
                                                            <td class="px-2 py-2">{{ $item->quantity }}</td>
                                                            <td class="px-2 py-2">₱{{ number_format($item->unitPrice, 2) }}</td>
                                                            <td class="px-2 py-2">{{ $item->itemMeasurement }}</td>
                                                            <td class="px-2 py-2">₱{{ number_format($item->totalAmount, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    
                                        <div class="col-span-4 flex items-center place-content-end mb-4 mx-6 pb-4 px-8 border-b-2 border-black">
                                            <label class="font-bold mr-2 uppercase">Grand Total</label>
                                            <p>₱{{ number_format($purchaseOrder->totalAmount, 2) }}</p>
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
                                            <x-form.closeBtn @click="$refs['deleteDialog{{ $purchaseOrder->id }}'].showModal()">Delete</x-form.closeBtn>

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

                                                        <!-- Delivery Date -->
                                                        <x-form.form-input label="Expected Delivery Date" name="deliveryDate" type="date"
                                                            value="{{ \Carbon\Carbon::parse($purchaseOrder->deliveryDate)->format('Y-m-d') }}" 
                                                            min="{{ date('Y-m-d') }}"
                                                            class="col-span-2" required />
                                                            
                                                        <!-- Total Amount -->
                                                        <x-form.form-input label="Grand Total:" name="totalAmount" type="text" disabled
                                                            value="₱{{ number_format($purchaseOrder->totalAmount, 2) }}"
                                                            class="col-span-2" />

                                                        <!-- Items Table -->
                                                        <table class="col-span-4 w-full text-sm text-left p-2 my-3 text-gray-500">
                                                            <thead class="text-xs uppercase rounded-lg bg-main text-white px-4 py-2">
                                                                <tr>
                                                                    <th class="px-2 py-1">#</th>
                                                                    <th class="px-4 py-2">Product Name</th>
                                                                    <th class="px-1 py-1">Quantity</th>
                                                                    <th class="px-2 py-1">Unit Price (₱)</th>
                                                                    <th class="px-4 py-2">Measurement</th>
                                                                    <th class="px-4 py-2">Total Amount (₱)</th>
                                                                    <th class="px-2 py-1">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($purchaseOrder->items as $index => $item)
                                                                    <tr class="bg-white border-b hover:bg-gray-50">
                                                                        <td class="px-2 py-1">{{ $index + 1 }}</td>
                                                                        <td class="px-4 py-2">
                                                                            <input type="text" name="items[{{ $item->id }}][productName]" value="{{ $item->productName }}" class="border rounded px-2 py-1 w-full">
                                                                        </td>
                                                                        <td class="px-1 py-1">
                                                                            <input type="number" name="items[{{ $item->id }}][quantity]" value="{{ $item->quantity }}" min="1" class="border rounded px-2 py-1 w-20">
                                                                        </td>
                                                                        <td class="px-2 py-1">
                                                                            <input type="number" step="0.01" name="items[{{ $item->id }}][unitPrice]" value="{{ $item->unitPrice }}" class="border rounded px-2 py-1 w-24">
                                                                        </td>
                                                                        <td class="px-4 py-2">
                                                                            <input type="text" name="items[{{ $item->id }}][itemMeasurement]" value="{{ $item->itemMeasurement }}" readonly class="border rounded px-2 py-1 w-24">
                                                                        </td>
                                                                        <td class="px-4 py-2">
                                                                            <input type="number" step="0.01" name="items[{{ $item->id }}][totalAmount]" value="{{ $item->totalAmount }}" readonly class="border rounded px-2 py-1 w-24 bg-gray-100 cursor-not-allowed">
                                                                        </td>
                                                                        <td class="px-2 py-1 flex place-content-center">
                                                                            <button type="submit" name="remove_item" value="{{ $item->id }}" 
                                                                                    onclick="return confirm('Are you sure you want to remove this item?')">
                                                                                <x-form.deleteBtn/>
                                                                            </button>
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

                                                            <button type="button" @click="$refs['editDialog{{ $purchaseOrder->id }}'].close()" class="mr-2 px-4 py-2 rounded bg-gray-400 hover:bg-gray-300 text-white duration-200 transition-all ease-in-out ">
                                                                Cancel
                                                            </button>

                                                            <x-form.saveBtn name="action" value="update">Save</x-form.saveBtn>
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
                                                            <p class="text-lg">Are you sure you want to delete this purchase order?</p>
                                                            <p class="text-xs text-gray-600 mt-2">This action cannot be undone. All items associated with this purchase order will also be deleted.</p>
                                                        </div>
                                                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                                            <button type="button" @click="$refs['deleteDialog{{ $purchaseOrder->id }}'].close()" class="mr-2 px-4 py-2 rounded text-white bg-gray-300 hover:bg-gray-400">
                                                                Cancel
                                                            </button>
                                                            <button type="submit" name="" value="" class="flex place-content-center rounded-md bg-button-delete px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in">
                                                                Delete
                                                            </button>
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