@if($errors->any())
    <div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-red-100 border border-red-400 text-red-700 rounded shadow-lg">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 pt-6 pb-3 flex flex-col items-center content-start">

        <!-- SUCCESS MESSAGE CONTAINER AND STATEMENT -->
        @if(session('success'))
            <div id="success-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- AUTO-HIDE SUCCESS MESSAGES -->
        <script>
            // Hide success messages after 3 seconds
            document.addEventListener('DOMContentLoaded', function() {
                const successMessage = document.getElementById('success-message');
                
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

        <!-- REVENUE STATS -->
        <section class="container flex flex-col items-center place-content-start">

            <!-- SEARCH BAR -->
            <div class="container flex items-center place-content-start gap-4 mb-4">
                <form action="{{ route('sales.index') }}" method="GET" class="flex items-center gap-4 mr-auto">
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Search sales..." 
                            class="pl-10 pr-4 py-2 border border-black rounded-md w-64"
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        Search
                    </button>

                    @if(request('search'))
                        <a href="{{ route('sales.index') }}" class="text-white px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                            Clear
                        </a>
                    @endif
                </form>

                <x-form.createBtn @click="$refs.addSalesRef.showModal()">Add New Sale</x-form.createBtn>
            </div>
        </section>

        <!-- SALES TABLE -->
        <section class="border w-full rounded-md border-solid border-black my-3">
            <table class="w-full table-fixed">
                <thead class="rounded-lg bg-main text-white px-4 py-3">
                    <tr class="rounded-lg">
                        <th class="bg-main px-4 py-3">Invoice Number</th>
                        <th class="bg-main px-4 py-3">Date</th>
                        <th class="bg-main px-4 py-3">Total Amount</th>
                        <th class="bg-main px-4 py-3">Items</th>
                        <th class="bg-main px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr class="border-b">
                        <td class="px-2 py-2 text-center">{{ $sale->invoice_number }}</td>
                        <td class="px-2 py-2 text-center">{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}</td>
                        <td class="px-2 py-2 text-center">₱{{ number_format($sale->total_amount, 2) }}</td>
                        <td class="px-2 py-2 text-center">{{ $sale->items->count() }} items</td>
                        <td class="truncate px-2 py-2 text-center flex justify-center items-center">
                            <button @click="$refs['viewSaleDetails{{ $sale->id }}'].showModal()" class="flex rounded-md bg-gray-400 px-3 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition:all duration-100 ease-in font-semibold">
                                View Details
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500">
                            No sales records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- PAGINATION -->
            <div class="mt-4 px-4 py-2 bg-gray-50">
                {{ $sales->links() }}
            </div>
        </section>

        <!-- ============================================ -->
        <!----------------- MODALS SECTION ----------------->
        <!-- ============================================ -->

        <!-- ADD SALES MODAL -->
        <x-modal.createModal x-ref="addSalesRef">
            <x-slot:dialogTitle>Add Sale</x-slot:dialogTitle>
            <div class="container">
                <!-- ADD ORDER FORM -->
                <form action="{{ route('sales.store') }}" method="POST" id="addSales" 
                    class="px-6 py-4 container grid grid-cols-7 gap-x-8 gap-y-6" novalidate>
                    @csrf
                    
                    <!-- Product Search (Custom Dropdown with AlpineJS) -->
                    <div 
                        x-data="{
                            open: false,
                            search: '',
                            products: {{ Js::from($products) }},
                            filtered() {
                                return this.products.filter(p => 
                                    p.productName.toLowerCase().includes(this.search.toLowerCase()) ||
                                    p.productSKU.toLowerCase().includes(this.search.toLowerCase())
                                )
                            },
                            select(product) {
                                this.search = product.productName + ' (' + product.productSKU + ')'
                                this.open = false

                                // Get available batches for this product
                                const availableBatches = product.batches.filter(batch => batch.quantity > 0);
                                const totalStock = availableBatches.reduce((sum, batch) => sum + batch.quantity, 0);
                                
                                // Fill form fields - FIXED: Use selectedProductId instead of selectedInventoryId
                                document.getElementById('selectedProductId').value = product.id
                                document.querySelector('[name=productSKU]').value = product.productSKU
                                document.querySelector('[name=productBrand]').value = product.brand?.productBrand || 'N/A'
                                document.querySelector('[name=itemMeasurement]').value = product.productItemMeasurement
                                document.querySelector('[name=availableStocks]').value = totalStock
                                document.querySelector('[name=salesPrice]').setAttribute('data-base-price', product.productSellingPrice)
                                document.querySelector('[name=salesPrice]').value = parseFloat(product.productSellingPrice).toFixed(2)
                                document.querySelector('[name=quantity]').setAttribute('max', totalStock)
                                document.querySelector('[name=quantity]').value = '1'
                                
                                // Store batches for later selection
                                document.getElementById('productBatches').value = JSON.stringify(availableBatches);
                                
                                calculateAmount();
                            }
                        }"
                        class="relative w-full col-span-3"
                    >
                        <label for="productName" class="text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input 
                            id="productName"
                            type="text"
                            x-model="search"
                            @focus="open = true"
                            @input="open = true"
                            @click.outside="open = false"
                            placeholder="Type to search products..."
                            class="px-3 py-2 border border-gray-300 w-full transition-all duration-200 shadow-sm"
                            autocomplete="off"
                        >

                        <!-- Dropdown -->
                        <div 
                            x-show="open && filtered().length > 0" 
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                        >
                            <template x-for="product in filtered()" :key="product.id">
                                <div 
                                    @click="select(product)" 
                                    class="p-3 cursor-pointer hover:bg-blue-50 border-b last:border-b-0"
                                >
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="font-medium text-gray-900" x-text="product.productName"></div>
                                            <div class="text-sm text-gray-500 mt-1">
                                                <span class="text-gray-600" x-text="product.productBrand"></span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-semibold text-blue-600">₱<span x-text="parseFloat(product.productSellingPrice).toFixed(2)"></span></div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Stock: 
                                                <span :class="product.productStock > 0 ? 'text-green-600' : 'text-red-600'" x-text="product.productStock"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Hidden inventory ID -->
                        <input type="hidden" id="productBatches" name="product_batches">
                        <input type="hidden" id="selectedProductId" name="product_id">
                    </div>

                    <!-- Other inputs (unchanged) -->
                    <x-form.form-input label="Product SKU" name="productSKU" type="text" class="col-span-2" readonly/>
                    <x-form.form-input label="Product Brand" name="productBrand" type="text" class="col-span-2" readonly/>
                    <x-form.form-input label="Stocks" name="availableStocks" type="number" class="col-span-1" readonly/>
                    <x-form.form-input label="UOM" name="itemMeasurement" type="text" class="col-span-1" readonly/>
                    <x-form.form-input label="Quantity" name="quantity" type="number" value="1" min="1" class="col-span-1" required oninput="calculateAmount()"/>
                    <x-form.form-input label="Unit Price (₱)" name="salesPrice" type="number" step="0.01" value="0.00" class="col-span-2" readonly/>

                    <!-- Add this after product selection -->
                    <div id="batchSelection" class="hidden col-span-3">
                        <label class="text-sm font-medium text-gray-700 mb-1">Batch Selection</label>
                        <div id="batchOptions" class="border rounded p-2 max-h-32 overflow-y-auto">
                            <!-- Batch options will be populated here -->
                        </div>
                    </div>

                    
                    <!-- Hidden fields for sale items -->
                    <div id="saleItemsContainer" class="hidden"></div>
    
                    <div class="px-5 py-2 w-full font-bold border border-black text-2xl uppercase col-span-5 flex flex-row items-between place-content-between">
                        <span>Total:</span>
                        <span id="cartTotal" class="ml-2">₱0.00</span>
                    </div>

                    <!-- ADD BUTTON FOR ADDING ITEMS TO SESSION -->
                    <div class="flex items-center place-content-end w-full col-start-6 col-span-2">
                        <button type="button" onclick="addToCart()" class= 'bg-teal-500/70 px-3 py-2 rounded text-white hover:bg-teal-500 w-full'>
                            Add
                        </button>
                    </div>

                    <!-- PREVIEW TABLE FOR ADDED SALES/PRODUCTS -->
                    <div class="border w-auto rounded-md border-solid border-black col-span-7">
                        <table class="w-full">
                            <thead class="rounded-lg bg-main text-white px-2 py-1">
                                <tr class="rounded-lg text-sm">
                                    <th class="bg-main px-2 py-2">Item/s</th>
                                    <th class="bg-main px-2 py-2">Quantity</th>
                                    <th class="bg-main px-2 py-2">Price</th>
                                    <th class="bg-main px-2 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                                <!-- Cart items will be added here dynamically -->
                                <tr id="emptyCartMessage">
                                    <td colspan="4" class="text-center py-4 text-gray-500">
                                        No items added yet. Add items above to preview your order.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Payment Section -->
                    <div class="col-span-7 flex justify-center gap-12">
                        <x-form.form-input 
                            label="Cash on Hand (₱)" 
                            name="salesCash" 
                            id="salesCash" 
                            type="number" 
                            step="0.01" 
                            value="" 
                            class="w-64"
                            required 
                            oninput="calculateChange()"
                        />
                        <x-form.form-input 
                            label="Change (₱)" 
                            name="salesChange" 
                            id="salesChange" 
                            type="number" 
                            step="0.01" 
                            value="0.00" 
                            class="w-64" 
                            readonly
                        />
                    </div>


                    <!-- FORM BUTTONS -->
                    <div class="flex justify-end items-center w-full relative col-span-7">

                        <button type="button" @click="$refs.confirmSalesCancel.showModal()" class="flex place-content-center rounded-md bg-button-delete mr-2 px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in">
                            Cancel
                        </button>

                        <div class="absolute bottom-2 left-0 flex flex-row">

                            <label class="flex items-center space-x-1 cursor-pointer">
                                <input type="checkbox" name="salesDownload">
                                <span>Download</span>
                            </label>

                            <label class="flex items-center space-x-1 ml-4 cursor-pointer">
                                <input type="checkbox" name="salesPrint">
                                <span>Print</span>
                            </label>

                        </div>
                
                        <x-form.saveBtn type="submit">Save</x-form.saveBtn>

                    </div>

                </form>
            </div>
        </x-modal.createModal>

        <!-- CONFIRM CANCEL/SAVE MODALS -->
        <x-modal.createModal x-ref="confirmSalesCancel">
            <x-slot:dialogTitle>Confirm Cancel?</x-slot:dialogTitle>
            <h1 class="text-xl px-4 py-3">Are you sure you want to cancel this sale?</h1>
            <div class="container flex w-full flex-row items-center content-end place-content-end px-4 py-3">
                <button type="button" @click="$refs.confirmSalesCancel.close()" class="mr-3 flex place-content-center rounded-md bg-button-delete px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in">
                    Cancel
                </button>
                <x-form.saveBtn type="button" 
                        @click="$refs.addSalesRef.close(); 
                        $refs.confirmSalesCancel.close(); 
                        resetForm()">
                        Confirm
                </x-form.saveBtn>
            </div>
        </x-modal.createModal>


        <!-- VIEW DETAILS MODAL -->
        @foreach($sales as $sale)
        <x-modal.createModal x-ref="viewSaleDetails{{ $sale->id }}">
            <x-slot:dialogTitle>Sale Details: {{ $sale->invoice_number }}</x-slot:dialogTitle>
            
            <div class="container px-3 pt-4 pb-0">
                <div class="grid grid-cols-2 gap-3 px-4">
                    <!-- Sale Information -->
                    <div class="col-span-3">
                        <h2 class="text-xl font-bold mb-4">Sale Information</h2>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-md">Invoice Number</p>
                                <p class="text-sm">{{ $sale->invoice_number }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-md">Sale Date</p>
                                <p class="text-sm">{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-md">Items Count</p>
                                <p class="text-sm">{{ $sale->items->count() }} items</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-md">Total Amount</p>
                                <p class="text-sm">₱{{ number_format($sale->total_amount, 2) }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-main">Processed By</p>
                                <p class="text-sm">{{ $sale->employee->name ?? 'System' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Items in this sale - Updated table styling -->
                    <div class="col-span-2">
                        <h2 class="text-xl font-bold mb-4">Items Sold</h2>
                        <div class="border w-auto rounded-md border-solid border-black p-3 my-4">
                            <table class="w-full">
                                <thead class="rounded-lg bg-main text-white px-4 py-2">
                                    <tr class="rounded-lg">
                                        <th class="bg-main px-2 py-2 text-sm">Product</th>
                                        <th class="bg-main px-2 py-2 text-sm">Quantity</th>
                                        <th class="bg-main px-2 py-2 text-sm">Unit Price</th>
                                        <th class="bg-main px-2 py-2 text-sm">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items as $saleItem)
                                    <tr class="border-b">
                                        <td class="px-2 py-2 text-center">
                                            {{ $saleItem->product->productName ?? $saleItem->product_name }}
                                            @if($saleItem->product_batch_id)
                                                <br><small class="text-gray-500">Batch: {{ $saleItem->productBatch->batch_number ?? 'N/A' }}</small>
                                            @endif
                                        </td>
                                        <td class="px-2 py-2 text-center">{{ $saleItem->quantity }} {{ $saleItem->inventory->productItemMeasurement ?? '' }}</td>
                                        <td class="px-2 py-2 text-center">₱{{ number_format($saleItem->unit_price, 2) }}</td>
                                        <td class="px-2 py-2 text-center">₱{{ number_format($saleItem->total_price, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Total Row -->
                        <div class="col-span-4 flex items-center place-content-end mb-4 mx-6 pb-4 px-8 border-b-2 border-black">
                            <label class="font-bold mr-2 uppercase">Grand Total:</label>
                            <p>₱{{ number_format($sale->total_amount, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- ACTION BUTTONS - Updated layout with checkboxes on left, action buttons on right -->
                <div class="flex justify-between items-center w-full px-6 py-4 border-t">
                    <!-- Checkboxes on left -->
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center space-x-1">
                            <input type="checkbox" name="downloadPDF">
                            <span>Download</span>
                        </label>
                        <label class="flex items-center space-x-1">
                            <input type="checkbox" name="print">
                            <span>Print</span>
                        </label>
                    </div>

                    <!-- Action buttons on right (Edit, Delete, Close) -->
                    <div class="flex gap-4">
                        <!-- EDIT BUTTON: Opens edit dialog -->
                        <button 
                            @click="$refs['editDialog{{ $sale->id }}'].showModal()" 
                            class="flex w-24 place-content-center rounded-md bg-button-create/70 px-3 py-2 text-blue-50 font-semibold items-center content-center hover:bg-button-create/70 transition-all duration-100 ease-in">
                            Edit
                        </button>

                        <!-- DELETE BUTTON: Opens delete dialog -->
                        <x-form.closeBtn @click="$refs['deleteDialog{{ $sale->id }}'].showModal()">Delete</x-form.closeBtn>

                        <!-- CLOSE BUTTON: Closes view details dialog -->
                        <button 
                            @click="$refs['viewSaleDetails{{ $sale->id }}'].close()" 
                            class="flex rounded-md ml-auto font-semibold bg-gray-400 px-6 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition-all duration-100 ease-in">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </x-modal.createModal>
        @endforeach



        <!-- EDIT DIALOG -->
        @foreach($sales as $sale)
        <x-modal.createModal x-ref="editDialog{{ $sale->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
            <x-slot:dialogTitle>Update Sale: {{ $sale->invoice_number }}</x-slot:dialogTitle>

            <div class="container px-3 py-4">
                <form action="{{ route('sales.update', $sale->id) }}" method="POST" class="px-6 py-4 container grid grid-cols-4 gap-x-8 gap-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Sale Date -->
                    <x-form.form-input label="Sale Date" name="sale_date" type="date"
                        value="{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}" 
                        class="col-span-2" required />

                    <!-- Total Amount -->
                    <x-form.form-input label="Total Amount:" name="total_amount" id="editTotalAmount" type="text" 
                        value="₱{{ number_format($sale->total_amount, 2) }}"
                        class="col-span-2" readonly />

                    <!-- Items Table -->
                    <div class="col-span-4">
                        <h3 class="text-lg font-semibold mb-3">Items</h3>
                        <table class="w-full text-sm text-left text-gray-500 border overflow-hidden">
                            <thead class="text-xs uppercase bg-main text-white">
                                <tr>
                                    <th class="px-4 py-3">Product</th>
                                    <th class="px-2 py-3">Quantity</th>
                                    <th class="px-2 py-3">Unit Price (₱)</th>
                                    <th class="px-2 py-3">Total (₱)</th>
                                    <th class="px-2 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->items as $index => $item)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <input type="text" value="{{ $item->inventory->productName ?? 'N/A' }}" 
                                                class="border-0 bg-transparent px-2 py-1 w-full" readonly>
                                            <input type="hidden" name="items[{{ $item->id }}][inventory_id]" value="{{ $item->inventory_id }}">
                                        </td>
                                        <td class="px-2 py-3">
                                            <input type="number" name="items[{{ $item->id }}][quantity]" 
                                                value="{{ $item->quantity }}" min="1" 
                                                class="border rounded px-2 py-1 w-20" onchange="updateItemTotal(this)">
                                        </td>
                                        <td class="px-2 py-3">
                                            <input type="number" step="0.01" name="items[{{ $item->id }}][unit_price]" 
                                                value="{{ $item->unit_price }}" 
                                                class="border rounded px-2 py-1 w-24" onchange="updateItemTotal(this)">
                                        </td>
                                        <td class="px-2 py-3">
                                            <input type="text" value="₱{{ number_format($item->total_price, 2) }}" 
                                                class="border rounded px-2 py-1 w-24 bg-gray-100 cursor-not-allowed" readonly>
                                        </td>
                                        <td class="px-2 py-3 flex justify-center">
                                            <button type="button" onclick="removeSaleItem(this)" 
                                                class="text-red-600 hover:text-red-800">
                                                <x-form.deleteBtn/>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                                            No items in this sale.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="container col-span-4 gap-x-4 place-content-end w-full flex items-end content-center px-6 pt-4">
                        <button type="button" @click="$refs['editDialog{{ $sale->id }}'].close()" 
                            class="mr-2 px-4 py-2 rounded bg-gray-400 hover:bg-gray-300 text-white duration-200 transition-all ease-in-out">
                            Cancel
                        </button>
                        <x-form.saveBtn>Update</x-form.saveBtn>
                    </div>
                </form>
            </div>
        </x-modal.createModal>
        @endforeach



        <!-- DELETE DIALOG -->
        @foreach($sales as $sale)
        <x-modal.createModal x-ref="deleteDialog{{ $sale->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
            <x-slot:dialogTitle>Delete Sale?</x-slot:dialogTitle>
            <div class="container px-2 py-2">
                <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="px-6 container">
                    @csrf
                    @method('DELETE')
                    <div class="mb-4 py-6">
                        <p class="text-lg">Are you sure you want to delete sale <strong>{{ $sale->invoice_number }}</strong>?</p>
                        <p class="text-xs text-gray-600 mt-2">This action cannot be undone. All items associated with this sale will also be deleted.</p>
                    </div>
                    <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                        <button type="button" @click="$refs['deleteDialog{{ $sale->id }}'].close()" class="mr-2 px-4 py-2 rounded text-white bg-gray-300 hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" name="" value="" class="flex place-content-center rounded-md bg-button-delete px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in">
                            Delete
                        </button>
                    </div>
                </form>
            </div>
        </x-modal.createModal>
        @endforeach




        <!-- ================================================================================== -->
        <!----------------JAVASCRIPT FUNCTIONS FOR PRODUCT SELECTION AND FORM HANDLING ----------->
        <!-- ================================================================================= --->

        <script>
            // Track if items have been added to cart
            let itemsAdded = false;
            
            // Function to format numbers with commas
            function formatNumberWithCommas(number) {
                return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            // Function to calculate amount to pay (quantity * unit price)
            function calculateAmount() {
                const quantity = parseFloat(document.querySelector('[name="quantity"]').value) || 0;
                const basePriceElement = document.querySelector('[name="salesPrice"]');
                const basePriceValue = basePriceElement.getAttribute('data-base-price');
                const basePrice = basePriceValue ? parseFloat(basePriceValue) : 0;
                const amount = quantity * basePrice;
                
                // Kept number plain in input, no commas to accept more than 999.99 values
                document.querySelector('[name="salesPrice"]').value = amount.toFixed(2);
                calculateChange();
            }

            // Function to calculate change (cash - total cart amount)
            function calculateChange() {
                const cashInput = document.querySelector('[name="salesCash"]');
                const changeInput = document.querySelector('[name="salesChange"]');

                // Only calculate if cash is entered
                if (cashInput.value && cashInput.value.trim() !== '') {
                    const cash = parseFloat(cashInput.value) || 0;
                    const change = cash - cartTotal;
                    // FIX: keep number plain in input, no commas
                    changeInput.value = Math.max(0, change).toFixed(2);
                } else {
                    changeInput.value = '0.00';
                }
            }
            
            
            // Store original product data for stock management
            let originalProducts = {{ Js::from($products) }};
            let currentProducts = JSON.parse(JSON.stringify(originalProducts));
            
            // Function to update available stocks in UI
            function updateAvailableStocksUI(inventoryId, quantityAdded) {
                // Find the product in our current products data
                const productIndex = currentProducts.findIndex(p => p.id == inventoryId);
                
                if (productIndex !== -1) {
                    // Update the product stock
                    currentProducts[productIndex].productStock -= quantityAdded;
                    
                    // Update the stock field if this product is currently selected
                    const selectedInventoryId = document.getElementById('selectedInventoryId').value;
                    if (selectedInventoryId == inventoryId) {
                        document.querySelector('[name="availableStocks"]').value = currentProducts[productIndex].productStock;
                        // Also update the max quantity
                        document.querySelector('[name="quantity"]').setAttribute('max', currentProducts[productIndex].productStock);
                    }
                    
                    // Update Alpine data for dropdown display
                    updateAlpineStockData(inventoryId, currentProducts[productIndex].productStock);
                }
            }
            
            // Function to update AlpineJS data for stock display
            function updateAlpineStockData(inventoryId, newStock) {
                try {
                    const alpineElement = document.querySelector('[x-data]');
                    if (alpineElement && alpineElement.__x && alpineElement.__x.$data) {
                        const alpineData = alpineElement.__x.$data;
                        const alpineProductIndex = alpineData.products.findIndex(p => p.id == inventoryId);
                        if (alpineProductIndex !== -1) {
                            alpineData.products[alpineProductIndex].productStock = newStock;
                            // Force Alpine to update by triggering a reactive update
                            alpineData.search = alpineData.search + ' ';
                            setTimeout(() => {
                                alpineData.search = alpineData.search.trim();
                            }, 10);
                        }
                    }
                } catch (e) {
                    console.log('Could not update Alpine data:', e);
                }
            }
            
            // Cart array to store added items
            let cart = [];
            let cartTotal = 0;
            
            // Function to add product to cart
            function addToCart() {
                const productId = document.getElementById('selectedProductId').value;
                const batchesData = JSON.parse(document.getElementById('productBatches').value || '[]');
                const quantity = parseFloat(document.querySelector('[name="quantity"]').value) || 0;
                const basePrice = parseFloat(document.querySelector('[name="salesPrice"]').getAttribute('data-base-price') || 0);

                if (!productId || quantity <= 0 || batchesData.length === 0) {
                    Toast.error('Please select a product with available stock');
                    return;
                }

                // Implement FIFO/FEFO batch selection logic
                const selectedBatches = selectBatchesForSale(batchesData, quantity);

                if (!selectedBatches) {
                    Toast.error('Not enough stock available');
                    return;
                }

                // Get product details
                const productName = document.getElementById('productName').value;
                const productOriginal = originalProducts.find(p => p.id == productId);
                
                if (!productOriginal) {
                    Toast.error('Product not found');
                    return;
                }

                // Calculate total quantity across all batches
                const totalBatchQuantity = selectedBatches.reduce((sum, batch) => sum + batch.quantityToSell, 0);
                
                if (totalBatchQuantity !== quantity) {
                    Toast.error('Batch allocation error');
                    return;
                }

                // Find if item already in cart
                const existingItemIndex = cart.findIndex(item => item.product_id === productId);
                
                if (existingItemIndex >= 0) {
                    // Update existing item
                    cart[existingItemIndex].quantity += quantity;
                    cart[existingItemIndex].total = cart[existingItemIndex].quantity * basePrice;
                    cart[existingItemIndex].batches = selectedBatches;
                } else {
                    // Add new item to cart
                    const itemTotal = basePrice * quantity;
                    cart.push({
                        product_id: productId,
                        product_batch_id: selectedBatches[0].id, // Main batch ID
                        name: productName,
                        product_name: productName,
                        quantity: quantity,
                        price: basePrice,
                        total: itemTotal,
                        batches: selectedBatches // Store all batches used
                    });
                }

                // Update UI for available stocks
                updateAvailableStocksUI(productId, quantity);

                // Update cart total
                cartTotal = cart.reduce((sum, item) => sum + item.total, 0);
                document.getElementById('cartTotal').textContent = '₱' + formatNumberWithCommas(cartTotal.toFixed(2));

                // Update displays and hidden inputs
                updateCartDisplay();
                updateSaleItemsForm();

                itemsAdded = true;

                // Reset product selection fields
                resetProductSelection();

                calculateChange();
            }

            // Function to reset product selection fields
            function resetProductSelection() {
                document.getElementById('productName').value = '';
                document.getElementById('selectedProductId').value = '';
                document.querySelector('[name="productSKU"]').value = '';
                document.querySelector('[name="productBrand"]').value = '';
                document.querySelector('[name="itemMeasurement"]').value = '';
                document.querySelector('[name="availableStocks"]').value = '';
                document.querySelector('[name="quantity"]').value = '1';
                document.querySelector('[name="quantity"]').removeAttribute('max');
                document.querySelector('[name="salesPrice"]').value = '0.00';
                document.querySelector('[name="salesPrice"]').setAttribute('data-base-price', '0');
                document.getElementById('productBatches').value = '';
            }

            // Function to select batches using FIFO/FEFO
            function selectBatchesForSale(batches, requiredQuantity) {
                // Sort batches by expiration date (FEFO) or creation date (FIFO)
                const sortedBatches = batches.sort((a, b) => new Date(a.expiration_date) - new Date(b.expiration_date));
                
                let remainingQuantity = requiredQuantity;
                const selectedBatches = [];
                
                for (const batch of sortedBatches) {
                    if (remainingQuantity <= 0) break;
                    
                    const quantityFromBatch = Math.min(batch.quantity, remainingQuantity);
                    selectedBatches.push({
                        ...batch,
                        quantityToSell: quantityFromBatch
                    });
                    
                    remainingQuantity -= quantityFromBatch;
                }
                
                return remainingQuantity === 0 ? selectedBatches : null;
            }

            
            // Function to update cart display
            function updateCartDisplay() {
                const cartItemsContainer = document.getElementById('cartItems');
                const emptyCartMessage = document.getElementById('emptyCartMessage');
                
                // Remove empty message if items exist
                if (cart.length > 0 && emptyCartMessage) {
                    emptyCartMessage.remove();
                }
                
                // Clear current items
                cartItemsContainer.innerHTML = '';
                
                // Add items to cart
                cart.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.className = 'border-b';
                    row.innerHTML = `
                        <td class="px-2 py-2 text-center">${item.name}</td>
                        <td class="px-2 py-2 text-center">${item.quantity}</td>
                        <td class="px-2 py-2 text-center">₱${formatNumberWithCommas(item.total.toFixed(2))}</td>
                        <td class="px-2 py-2 text-center">
                            <button type="button" onclick="removeFromCart(${index})" class="text-red-600 hover:text-red-800">
                                <x-form.deleteBtn />
                            </button>
                        </td>
                    `;
                    cartItemsContainer.appendChild(row);
                });
                
                // Add empty message if cart is empty
                if (cart.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.id = 'emptyCartMessage';
                    emptyRow.innerHTML = `
                        <td colspan="4" class="text-center py-4 text-gray-500">
                            No items added yet. Add items above to preview your order.
                        </td>
                    `;
                    cartItemsContainer.appendChild(emptyRow);
                }
            }
            
            // Function to update hidden form fields for sale items
            function updateSaleItemsForm() {
                const container = document.getElementById('saleItemsContainer');
                container.innerHTML = '';
                
                let itemIndex = 0;
                
                cart.forEach((item) => {
                    // For each batch in the item, create a separate form input set
                    item.batches.forEach((batch, batchIndex) => {
                        const productIdInput = document.createElement('input');
                        productIdInput.type = 'hidden';
                        productIdInput.name = `items[${itemIndex}][product_id]`;
                        productIdInput.value = item.product_id;
                        
                        const productBatchIdInput = document.createElement('input');
                        productBatchIdInput.type = 'hidden';
                        productBatchIdInput.name = `items[${itemIndex}][product_batch_id]`;
                        productBatchIdInput.value = batch.id;
                        
                        const productNameInput = document.createElement('input');
                        productNameInput.type = 'hidden';
                        productNameInput.name = `items[${itemIndex}][product_name]`;
                        productNameInput.value = item.product_name;
                        
                        const quantityInput = document.createElement('input');
                        quantityInput.type = 'hidden';
                        quantityInput.name = `items[${itemIndex}][quantity]`;
                        quantityInput.value = batch.quantityToSell;
                        
                        const priceInput = document.createElement('input');
                        priceInput.type = 'hidden';
                        priceInput.name = `items[${itemIndex}][price]`;
                        priceInput.value = item.price;
                        
                        container.appendChild(productIdInput);
                        container.appendChild(productBatchIdInput);
                        container.appendChild(productNameInput);
                        container.appendChild(quantityInput);
                        container.appendChild(priceInput);
                        
                        itemIndex++;
                    });
                });
            }

            // Function to remove item from cart
            function removeFromCart(index) {
                const removedItem = cart[index];
                
                // Subtract from total
                cartTotal -= removedItem.total;
                document.getElementById('cartTotal').textContent = '₱' + formatNumberWithCommas(cartTotal.toFixed(2));
                
                // Remove from cart
                cart.splice(index, 1);
                
                // Update UI for available stocks (add back the quantity)
                const productIndex = currentProducts.findIndex(p => p.id == removedItem.product_id);
                if (productIndex !== -1) {
                    currentProducts[productIndex].totalStock += removedItem.quantity;
                    
                    // Update the stock field if this product is currently selected
                    const selectedProductId = document.getElementById('selectedProductId').value;
                    if (selectedProductId == removedItem.product_id) {
                        document.querySelector('[name="availableStocks"]').value = currentProducts[productIndex].totalStock;
                    }
                    
                    // Update Alpine data
                    updateAlpineStockData(removedItem.product_id, currentProducts[productIndex].totalStock);
                }
                
                // Update display
                updateCartDisplay();
                updateSaleItemsForm();
                
                // Recalculate change
                calculateChange();
            }
            
            // Function to validate form before submission
            function validateFormBeforeSubmit() {
                const salesCash = document.getElementById('salesCash').value;
                
                if (!salesCash || parseFloat(salesCash) <= 0) {
                    Toast.error('Please enter a valid cash amount before saving');
                    return false;
                }
                
                const cash = parseFloat(salesCash);
                
                if (cash < cartTotal) {
                    Toast.error('Cash amount cannot be less than the total amount to pay');
                    return false;
                }
                
                if (cart.length === 0) {
                    Toast.error('Please add at least one item to the cart');
                    return false;
                }
                
                return true;
            }
            
            // Function to reset the form properly
            function resetForm() {
                // Reset payment fields
                document.querySelector('[name="salesCash"]').value = '';
                document.querySelector('[name="salesChange"]').value = '0.00';
                
                // Reset product selection fields - FIXED: Use selectedProductId
                document.getElementById('productName').value = '';
                document.getElementById('selectedProductId').value = '';
                document.querySelector('[name="productSKU"]').value = '';
                document.querySelector('[name="productBrand"]').value = '';
                document.querySelector('[name="itemMeasurement"]').value = '';
                document.querySelector('[name="availableStocks"]').value = '';
                document.querySelector('[name="quantity"]').value = '1';
                document.querySelector('[name="quantity"]').removeAttribute('max');
                document.querySelector('[name="salesPrice"]').value = '0.00';
                document.querySelector('[name="salesPrice"]').removeAttribute('data-base-price');
                document.getElementById('productBatches').value = '';
                
                // Reset cart
                cart = [];
                cartTotal = 0;
                document.getElementById('cartTotal').textContent = '₱0.00';
                updateCartDisplay();
                updateSaleItemsForm();
                
                // Unlock fields
                itemsAdded = false;
                
                // Reset product data
                currentProducts = JSON.parse(JSON.stringify(originalProducts));
                
                // Reset Alpine data by reloading the modal
                try {
                    const alpineElement = document.querySelector('[x-data]');
                    if (alpineElement && alpineElement.__x && alpineElement.__x.$data) {
                        const alpineData = alpineElement.__x.$data;
                        alpineData.products = JSON.parse(JSON.stringify(originalProducts));
                        alpineData.search = '';
                    }
                } catch (e) {
                    console.log('Could not reset Alpine data:', e);
                }
            }

            function validateCartBeforeSubmit() {
                if (cart.length === 0) {
                    Toast.error('Please add at least one item to proceed');
                    return false;
                }
                
                // Group quantities by product ID
                const quantityByProduct = {};
                cart.forEach(item => {
                    quantityByProduct[item.product_id] = (quantityByProduct[item.product_id] || 0) + item.quantity;
                });
                
                // Validate each product's total quantity
                for (const [productId, totalQuantity] of Object.entries(quantityByProduct)) {
                    const originalProduct = originalProducts.find(p => p.id == productId);
                    
                    if (!originalProduct) {
                        Toast.error('One of your products is no longer available');
                        return false;
                    }
                    
                    // Calculate total available stock from batches
                    const totalAvailableStock = originalProduct.batches.reduce((sum, batch) => sum + batch.quantity, 0);
                    
                    // CRITICAL: Check TOTAL quantity against stock
                    if (totalQuantity > totalAvailableStock) {
                        const productName = cart.find(item => item.product_id == productId).name;
                        Toast.error(`Not enough stock for ${productName}. Available: ${totalAvailableStock}, Requested: ${totalQuantity}`);
                        return false;
                    }
                }
                
                return true;
            }
            
            // Add event listeners when DOM is loaded
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelector('[name="quantity"]').addEventListener('input', calculateAmount);
                document.querySelector('[name="salesCash"]').addEventListener('input', calculateChange);
                
                // Add form validation on submit
                document.getElementById('addSales').addEventListener('submit', function(e) {
                    console.log('Sales form submitting...');

                    // First validate the form inputs
                    if (!validateFormBeforeSubmit()) {
                        e.preventDefault();
                        return;
                    }
                    
                    // THEN validate the cart data (this is what goes to database)
                    if (!validateCartBeforeSubmit()) {
                        e.preventDefault();
                        return;
                    }
                    
                    // If both validations pass, the form will submit normally
                });
            });

            // ============= EDIT MODAL JS FUNCTION ============= //

            // Function to update item total when quantity or price changes
            function updateItemTotal(input) {
                const row = input.closest('tr');
                const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
                const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
                const total = quantity * unitPrice;
                
                // Update the total display (find the correct readonly input)
                const totalInput = row.querySelector('td:nth-child(4) input[readonly]');
                if (totalInput) {
                    totalInput.value = '₱' + total.toFixed(2);
                }
                
                // Recalculate grand total for ALL items
                updateGrandTotal();
            }

            // Function to update grand total for ALL items in the table
            function updateGrandTotal() {
                let grandTotal = 0;
                
                // Get the currently active edit modal
                const activeModal = document.querySelector('dialog[open]');
                if (!activeModal) return;
                
                // Select all visible rows in the active modal's table body
                const rows = activeModal.querySelectorAll('tbody tr:not([style*="display: none"])');
                
                rows.forEach(row => {
                    const totalInput = row.querySelector('td:nth-child(4) input[readonly]');
                    if (totalInput && totalInput.value) {
                        const totalValue = parseFloat(totalInput.value.replace('₱', '').replace(/,/g, '')) || 0;
                        grandTotal += totalValue;
                    }
                });
                
                // Update grand total display in the active modal
                const grandTotalElem = activeModal.querySelector('input[name="total_amount"]');
                if (grandTotalElem) {
                    grandTotalElem.value = '₱' + grandTotal.toFixed(2);
                }
            }

            // Function to remove sale item with proper backend handling
            function removeSaleItem(button) {
                if (confirm('Are you sure you want to remove this item?')) {
                    const row = button.closest('tr');
                    const itemId = row.querySelector('input[name*="[inventory_id]"]').name.match(/\d+/)[0];
                    
                    // Add a hidden field to mark this item for deletion
                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = `items[${itemId}][_delete]`;
                    deleteInput.value = '1';
                    row.appendChild(deleteInput);
                    
                    // Hide the row instead of removing it completely
                    row.style.display = 'none';
                    
                    // Recalculate grand total for ALL items
                    updateGrandTotal();
                }
            }
        </script>

    </main>
</x-layout>
