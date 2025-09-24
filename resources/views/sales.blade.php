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
            document.addEventListener('DOMContentLoaded', function() {
                const successMessage = document.getElementById('success-message');
                if (successMessage) {
                    setTimeout(() => {
                        successMessage.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                        successMessage.style.opacity = '0';
                        successMessage.style.transform = 'translate(-50%, -20px)';
                        setTimeout(() => { successMessage.remove(); }, 500);
                    }, 3000);
                }

                // After redirect, trigger download or print if flagged
                const downloadId = @json(session('download_sale_id'));
                if (downloadId) {
                    // Trigger file download without blocking UI
                    const link = document.createElement('a');
                    link.href = `/sales/${downloadId}/download-receipt`;
                    link.download = '';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
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
                            placeholder="Search invoice number.." 
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
                <div x-data="{
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
                            // FIX 3: Only show product name, not SKU
                            this.search = product.productName;
                            this.open = false;

                            const currentProduct = window.currentProducts.find(p => p.id == product.id) || product;
                            
                            document.getElementById('selectedProductId').value = currentProduct.id
                            document.querySelector('[name=productSKU]').value = currentProduct.productSKU
                            
                            // FIX 1: Show actual brand name
                            document.querySelector('[name=productBrand]').value = currentProduct.brand ? currentProduct.brand.productBrand : 'N/A';
                            
                            document.querySelector('[name=itemMeasurement]').value = currentProduct.productItemMeasurement
                            document.querySelector('[name=salesPrice]').setAttribute('data-base-price', currentProduct.productSellingPrice)
                            document.querySelector('[name=salesPrice]').value = parseFloat(currentProduct.productSellingPrice).toFixed(2)
                            
                            // Use the new function to update stock and batch information
                            updateProductSelectionUI(currentProduct.id);
                            
                            document.querySelector('[name=quantity]').value = '1'
                            calculateAmount();
                        }
                    }"
                    class="relative w-full col-span-3">
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
                                            <!-- FIX 1: Show brand name properly -->
                                            <span class="text-gray-600" x-text="product.brand ? product.brand.productBrand : 'N/A'"></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-blue-600">₱
                                            <span x-text="parseFloat(product.productSellingPrice).toFixed(2)">
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Stock:
                                            <span x-html="getCurrentStock(product.id)"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

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

                    <!-- Product Batch Input Field -->
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-700 mb-1">Product Batch</label>
                        <input 
                            type="text" 
                            name="productBatch" 
                            id="productBatch"
                            class="px-3 py-2 border rounded-md w-full" 
                            readonly
                            placeholder="Will auto-populate with latest expiration date"
                            title="Batch information will appear here"
                        >
                    </div>

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
            
            <div class="container">
                <div class="grid grid-cols-1 gap-4 p-4">
                    <!-- Sale Information -->
                    <div class="col-span-1">
                        <h2 class="text-xl font-bold mb-4">Sale Information</h2>
                        <div class="grid grid-cols-4 gap-3">
                            <div class="bg-gray-50 p-3 rounded-md">
                                <p class="font-semibold text-md">Invoice Number</p>
                                <p class="text-sm truncate" title="{{ $sale->invoice_number }}">{{ $sale->invoice_number }}</p>
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
                                <p class="font-semibold text-md">Processed by</p>
                                <p class="text-sm">System</p>
                            </div>
                        </div>
                    </div>

                    <!-- Batch Information -->
                    <div class="col-span-1">
                        <h3 class="text-lg font-semibold mb-3">Batch Information</h3>
                        <div class="grid grid-cols-1 @if($sale->items->groupBy('product_batch_id')->count() == 1) grid-cols-1 @elseif($sale->items->groupBy('product_batch_id')->count() == 2) grid-cols-2 @else grid-cols-3 @endif gap-4">
                            @php
                                $batchGroups = $sale->items->groupBy('product_batch_id');
                            @endphp
                            @foreach($batchGroups as $batchId => $items)
                                @php
                                    $batch = $items->first()->productBatch;
                                    $totalQuantity = $items->sum('quantity');
                                @endphp
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <p class="font-semibold text-sm text-gray-800">{{ $batch->batch_number ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-600 mt-1">Exp: {{ $batch ? \Carbon\Carbon::parse($batch->expiration_date)->format('M d, Y') : 'N/A' }}</p>
                                    <p class="text-xs text-gray-700 mt-1">Quantity Sold: {{ $totalQuantity }}</p>
                                    <p class="text-xs text-gray-700">Product: {{ $items->first()->product_name }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Items in this sale -->
                    <div class="col-span-1">
                        <div class="flex gap-2">
                            <!-- Table (3/4 width) -->
                            <div class="w-4/6">
                                <h2 class="text-xl font-bold mb-4">Items Sold</h2>
                                <div class="border rounded-md border-solid border-black">
                                    <table class="w-full">
                                        <thead class="rounded-lg bg-main text-white">
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
                                                <td class="px-2 py-2 text-xs text-center">
                                                    {{ $saleItem->product->productName ?? $saleItem->product_name }}
                                                </td>
                                                <td class="px-2 py-2 text-xs text-center">
                                                    {{ $saleItem->quantity }} 
                                                    {{ $saleItem->product->productItemMeasurement ?? '' }}
                                                </td>
                                                <td class="px-2 py-2 text-xs text-center">₱{{ number_format($saleItem->unit_price, 2) }}</td>
                                                <td class="px-2 py-2 text-xs text-center">₱{{ number_format($saleItem->total_price, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Totals (1/4 width) -->
                            <div class="w-auto">
                                <div class="px-4">
                                    <h3 class="font-bold text-lg mb-4">Payment Summary</h3>
                                    <div class="space-y-3 pt-2">
                                        <div class="flex justify-between">
                                            <span class="text-xs font-semibold">Cash Received:</span>
                                            <span class="text-xs">₱{{ number_format($sale->cash_received, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-xs font-semibold">Change:</span>
                                            <span class="text-xs">₱{{ number_format($sale->change, 2) }}</span>
                                        </div>
                                        <div class="border-t pt-2 mt-2">
                                            <div class="flex justify-between">
                                                <span class="text-xs font-bold">Total Amount:</span>
                                                <span class="text-xs font-bold">₱{{ number_format($sale->total_amount, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="flex justify-between items-center w-full px-6 pt-4 pb-2 border-t mt-4">
                    <!-- Buttons on left -->
                    <div class="flex items-center space-x-4">
                        <button onclick="downloadSalePDF({{ $sale->id }})" class="flex items-center space-x-1 cursor-pointer bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-400 transition-colors">
                            <span>Download</span>
                        </button>
                        <a href="{{ route('sales.download-receipt', ['sale' => $sale->id]) }}?inline=1" target="_blank"
                           class="flex items-center space-x-1 cursor-pointer bg-green-500 text-white rounded hover:bg-green-400 ml-auto font-semibold px-6 py-2 w-auto transition-all duration-100 ease-in">
                            <span>Print</span>
                        </a>
                    </div>

                    <!-- Action buttons on right -->
                    <div class="flex gap-4">
                        <!-- EDIT BUTTON -->
                        <button 
                            @click="$refs['editDialog{{ $sale->id }}'].showModal()" 
                            class="flex w-24 place-content-center rounded-md bg-button-create/70 px-3 py-2 text-blue-50 font-semibold items-center content-center hover:bg-button-create/70 transition-all duration-100 ease-in">
                            Edit
                        </button>

                        <!-- DELETE BUTTON -->
                        <x-form.closeBtn @click="$refs['deleteDialog{{ $sale->id }}'].showModal()">Delete</x-form.closeBtn>

                        <!-- CLOSE BUTTON -->
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

    // Store original product data for stock management
    window.originalProducts = {{ Js::from($products) }};
    window.currentProducts = JSON.parse(JSON.stringify(window.originalProducts));
    
    // Cart array to store added items
    window.cart = [];
    window.cartTotal = 0;

    // Function to calculate amount to pay (quantity * unit price)
    function calculateAmount() {
        const quantity = parseFloat(document.querySelector('[name="quantity"]').value) || 0;
        const basePriceElement = document.querySelector('[name="salesPrice"]');
        const basePriceValue = basePriceElement.getAttribute('data-base-price');
        const basePrice = basePriceValue ? parseFloat(basePriceValue) : 0;
        const amount = quantity * basePrice;
        
        document.querySelector('[name="salesPrice"]').value = amount.toFixed(2);
        calculateChange();
    }

    // Function to calculate change (cash - total cart amount)
    function calculateChange() {
        const cashInput = document.querySelector('[name="salesCash"]');
        const changeInput = document.querySelector('[name="salesChange"]');

        if (cashInput.value && cashInput.value.trim() !== '') {
            const cash = parseFloat(cashInput.value) || 0;
            const change = cash - window.cartTotal;
            changeInput.value = Math.max(0, change).toFixed(2);
        } else {
            changeInput.value = '0.00';
        }
    }
    
    // Function to update available stocks in UI
    function updateAvailableStocksUI(productId, quantityAdded) {
        const productIndex = window.currentProducts.findIndex(p => p.id == productId);
        
        if (productIndex !== -1) {
            let remainingQuantity = quantityAdded;
            
            // Deduct from batches (FIFO/FEFO order)
            const sortedBatches = window.currentProducts[productIndex].batches
                .filter(batch => batch.quantity > 0)
                .sort((a, b) => new Date(a.expiration_date) - new Date(b.expiration_date));
            
            for (const batch of sortedBatches) {
                if (remainingQuantity <= 0) break;
                
                const quantityToDeduct = Math.min(batch.quantity, remainingQuantity);
                batch.quantity -= quantityToDeduct;
                remainingQuantity -= quantityToDeduct;
            }
            
            // Update UI for the selected product
            updateProductSelectionUI(productId);
        }
    }

    // New function to update product selection UI
    function updateProductSelectionUI(productId = null) {
        if (!productId) {
            productId = document.getElementById('selectedProductId').value;
        }
        
        if (!productId) return;
        
        const product = window.currentProducts.find(p => p.id == productId);
        if (!product) return;
        
        const availableBatches = product.batches.filter(batch => batch.quantity > 0);
        const totalStock = availableBatches.reduce((sum, batch) => sum + batch.quantity, 0);
        
        document.querySelector('[name="availableStocks"]').value = totalStock;
        document.querySelector('[name="quantity"]').setAttribute('max', totalStock);
        
        if (availableBatches.length > 0) {
            const sortedBatches = availableBatches.sort((a, b) => 
                new Date(a.expiration_date) - new Date(b.expiration_date)
            );
            const latestBatch = sortedBatches[0];
            document.getElementById('productBatch').value = 
                `${latestBatch.batch_number} (Exp: ${new Date(latestBatch.expiration_date).toLocaleDateString()})`;
            document.getElementById('productBatch').title = 
                `Available batches: ${availableBatches.map(b => `${b.batch_number}: ${b.quantity} units (Exp: ${new Date(b.expiration_date).toLocaleDateString()})`).join(', ')}`;
        } else {
            document.getElementById('productBatch').value = 'No batches available';
            document.getElementById('productBatch').title = '';
        }
        
        // Update the product batches hidden field
        document.getElementById('productBatches').value = JSON.stringify(availableBatches);
    }
            
    // Function in adding product to cart
    function addToCart() {
        const productId = document.getElementById('selectedProductId').value;
        const quantity = parseFloat(document.querySelector('[name="quantity"]').value) || 0;
        const basePrice = parseFloat(document.querySelector('[name="salesPrice"]').getAttribute('data-base-price') || 0);
        const productName = document.getElementById('productName').value;

        if (!productId || quantity <= 0) {
            Toast.error('Please select a product with available stock');
            return;
        }

        // Get the CURRENT product data with updated batches
        const productInCurrent = window.currentProducts.find(p => p.id == productId);
        if (!productInCurrent) {
            Toast.error('Product not found');
            return;
        }

        // Get available batches from CURRENT data
        const availableBatches = productInCurrent.batches.filter(batch => batch.quantity > 0);
        const totalAvailableStock = availableBatches.reduce((sum, batch) => sum + batch.quantity, 0);

        // FIX 1: Check if requested quantity is available RIGHT NOW
        if (quantity > totalAvailableStock) {
            Toast.error(`Not enough stock available. Available: ${totalAvailableStock}, Requested: ${quantity}`);
            return;
        }

        // Check if item already exists in cart
        const existingCartQuantity = window.cart.filter(item => item.product_id === productId)
                                        .reduce((sum, item) => sum + item.quantity, 0);

        // FIX 2: Get ORIGINAL available stock (before any cart additions)
        const originalProduct = window.originalProducts.find(p => p.id == productId);
        const originalAvailableStock = originalProduct ? originalProduct.batches.reduce((sum, batch) => sum + batch.quantity, 0) : 0;

        const totalRequestedQuantity = existingCartQuantity + quantity;

        // FIX 3: Compare against ORIGINAL stock, not current stock
        if (totalRequestedQuantity > originalAvailableStock) {
            Toast.error(`Total quantity in cart (${totalRequestedQuantity}) exceeds available stock (${originalAvailableStock})`);
            return;
        }

        // Use FIFO batch selection
        const selectedBatches = selectBatchesForSale(availableBatches, quantity);

        if (!selectedBatches) {
            Toast.error('Error allocating stock from batches');
            return;
        }

        // Find if item already in cart
        const existingItemIndex = window.cart.findIndex(item => item.product_id === productId);
        
        if (existingItemIndex >= 0) {
            window.cart[existingItemIndex].quantity += quantity;
            window.cart[existingItemIndex].total = window.cart[existingItemIndex].quantity * basePrice;
            window.cart[existingItemIndex].batches = [...window.cart[existingItemIndex].batches, ...selectedBatches];
        } else {
            const itemTotal = basePrice * quantity;
            window.cart.push({
                product_id: productId,
                product_batch_id: selectedBatches[0].id,
                name: productName.split(' (')[0],
                product_name: productName.split(' (')[0],
                quantity: quantity,
                price: basePrice,
                total: itemTotal,
                batches: selectedBatches
            });
        }

        // Update UI
        updateAvailableStocksUI(productId, quantity);
        window.cartTotal = window.cart.reduce((sum, item) => sum + item.total, 0);
        document.getElementById('cartTotal').textContent = '₱' + formatNumberWithCommas(window.cartTotal.toFixed(2));
        updateCartDisplay();
        updateSaleItemsForm();

        // ADD THIS LINE to refresh dropdown stock display
        refreshProductDropdown();
        
        itemsAdded = true;
        resetProductSelection();
        calculateChange();
        
        Toast.success('Product added to cart!');
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
        document.getElementById('productBatch').value = '';
    }

    // Function to select batches using FIFO/FEFO
    function selectBatchesForSale(batches, requiredQuantity) {
        if (!batches || batches.length === 0) return null;
        
        // Filter batches with stock and sort by expiration date (FIFO/FEFO)
        const sortedBatches = batches
            .filter(batch => batch.quantity > 0)
            .sort((a, b) => new Date(a.expiration_date) - new Date(b.expiration_date));
        
        if (sortedBatches.length === 0) return null;
        
        let remainingQuantity = requiredQuantity;
        const selectedBatches = [];
        
        for (const batch of sortedBatches) {
            if (remainingQuantity <= 0) break;
            
            const quantityFromBatch = Math.min(batch.quantity, remainingQuantity);
            selectedBatches.push({
                id: batch.id,
                batch_number: batch.batch_number,
                expiration_date: batch.expiration_date,
                quantity: batch.quantity,
                quantityToSell: quantityFromBatch
            });
            
            remainingQuantity -= quantityFromBatch;
        }
        
        // If we couldn't fulfill the entire quantity, return null
        if (remainingQuantity > 0) {
            console.warn(`Could not fulfill entire quantity. Requested: ${requiredQuantity}, Fulfilled: ${requiredQuantity - remainingQuantity}`);
            return null;
        }
        
        return selectedBatches;
    }

    
    // Function to update cart display
    function updateCartDisplay() {
        const cartItemsContainer = document.getElementById('cartItems');
        const emptyCartMessage = document.getElementById('emptyCartMessage');
        
        if (window.cart.length > 0 && emptyCartMessage) {
            emptyCartMessage.remove();
        }
        
        cartItemsContainer.innerHTML = '';
        
        window.cart.forEach((item, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b';
            row.innerHTML = `
                <td class="px-2 py-2 text-center">${item.name}</td>
                <td class="px-2 py-2 text-center">${item.quantity}</td>
                <td class="px-2 py-2 text-center">₱${formatNumberWithCommas(item.total.toFixed(2))}</td>
                <td class="px-2 py-2 text-center">
                    <button type="button" onclick="removeFromCart(${index})" class="text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </td>
            `;
            cartItemsContainer.appendChild(row);
        });
        
        if (window.cart.length === 0) {
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
        
        window.cart.forEach((item) => {
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
        const removedItem = window.cart[index];
        
        window.cartTotal -= removedItem.total;
        document.getElementById('cartTotal').textContent = '₱' + formatNumberWithCommas(window.cartTotal.toFixed(2));
        window.cart.splice(index, 1);
        
        // Restore the quantities to the batches
        const productIndex = window.currentProducts.findIndex(p => p.id == removedItem.product_id);
        if (productIndex !== -1) {
            removedItem.batches.forEach(removedBatch => {
                const currentBatch = window.currentProducts[productIndex].batches.find(b => b.id == removedBatch.id);
                if (currentBatch) {
                    currentBatch.quantity += removedBatch.quantityToSell;
                }
            });
            
            updateProductSelectionUI(removedItem.product_id);
        }
        
        // ADD THIS LINE to refresh dropdown
        refreshProductDropdown();
        
        updateCartDisplay();
        updateSaleItemsForm();
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
        
        if (cash < window.cartTotal) {
            Toast.error('Cash amount cannot be less than the total amount to pay');
            return false;
        }
        
        if (window.cart.length === 0) {
            Toast.error('Please add at least one item to the cart');
            return false;
        }
        
        return true;
    }
    
    // Function to reset the form properly
    function resetForm() {
        document.querySelector('[name="salesCash"]').value = '';
        document.querySelector('[name="salesChange"]').value = '0.00';
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
        
        window.cart = [];
        window.cartTotal = 0;
        document.getElementById('cartTotal').textContent = '₱0.00';
        updateCartDisplay();
        updateSaleItemsForm();
        
        itemsAdded = false;
        window.currentProducts = JSON.parse(JSON.stringify(window.originalProducts));
    }

    // SIMPLIFIED VALIDATION: Only check if cart has items
    function validateCartBeforeSubmit() {
        if (window.cart.length === 0) {
            Toast.error('Please add at least one item to proceed');
            return false;
        }
        return true;
    }

    // Function to download sale PDF
    function downloadSalePDF(saleId) {
        window.open(`/sales/${saleId}/download-receipt`, '_blank');
    }


    // Function to get current stock for display in AlpineJS dropdown - UPDATED
    function getCurrentStock(productId) {
        const currentProduct = window.currentProducts.find(p => p.id == productId);
        if (!currentProduct) return '<span class="text-red-600">0</span>';
        
        const totalStock = currentProduct.batches.reduce((sum, batch) => sum + batch.quantity, 0);
        const stockClass = totalStock > 0 ? 'text-green-600' : 'text-red-600';
        
        return `<span class="${stockClass}">${totalStock}</span>`;
    }

    // Function to refresh AlpineJS dropdown data
    function refreshProductDropdown() {
        // Force Alpine to re-evaluate the filtered products
        const alpineComponent = document.querySelector('[x-data]').__x;
        if (alpineComponent) {
            alpineComponent.$data.products = JSON.parse(JSON.stringify(window.currentProducts));
        }
    }

    // Make it globally available
    window.getCurrentStock = getCurrentStock;
    
    // Add event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('[name="quantity"]').addEventListener('input', calculateAmount);
        document.querySelector('[name="salesCash"]').addEventListener('input', calculateChange);
        
        document.getElementById('addSales').addEventListener('submit', function(e) {
            if (!validateFormBeforeSubmit()) {
                e.preventDefault();
                return;
            }
            
            if (!validateCartBeforeSubmit()) {
                e.preventDefault();
                return;
            }
        });
    });

    // Simple Toast function for notifications
    window.Toast = {
        success: function(message) {
            this.show(message, 'green');
        },
        error: function(message) {
            this.show(message, 'red');
        },
        info: function(message) {
            this.show(message, 'blue');
        },
        show: function(message, color) {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg text-white bg-${color}-500 z-50`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    };

    // Hndle print on form submission
    document.getElementById('addSales').addEventListener('submit', function(e) {
        if (!validateFormBeforeSubmit()) {
            e.preventDefault();
            return;
        }
        
        if (!validateCartBeforeSubmit()) {
            e.preventDefault();
            return;
        }
        
        // Handle print after successful submission
        const shouldPrint = document.querySelector('input[name="salesPrint"]').checked;
        if (shouldPrint) {
            // You might want to store this flag and handle printing after redirect
            localStorage.setItem('printAfterSave', 'true');
        }
    });

    // Function to print after successful sale creation (call this on your success page)
    function printNewSale(invoiceNumber) {
        if (localStorage.getItem('printAfterSave') === 'true') {
            // Find the sale and print it
            setTimeout(() => {
                // This would need to be implemented based on how you handle new sales
                console.log('Printing new sale:', invoiceNumber);
                localStorage.removeItem('printAfterSave');
            }, 1000);
        }
    }


    // Enhanced print function with better data extraction
    function printSaleDetails(saleId) {
        try {
            // Get the modal
            const modal = document.querySelector(`[x-ref="viewSaleDetails${saleId}"]`);
            if (!modal) {
                Toast.error('Sale details not found');
                return;
            }

            // Extract data from the modal
            const invoiceNumber = modal.querySelector('[x-slot\\:dialogTitle]')?.textContent.replace('Sale Details: ', '') || 'N/A';
            const saleInfoItems = modal.querySelectorAll('.bg-gray-50.p-3.rounded-md');
            const saleDate = saleInfoItems[1]?.querySelector('.text-sm')?.textContent || 'N/A';
            const itemsCount = saleInfoItems[2]?.querySelector('.text-sm')?.textContent || 'N/A';
            const processedBy = saleInfoItems[3]?.querySelector('.text-sm')?.textContent || 'System';

            // Extract batch information
            const batchItems = modal.querySelectorAll('.bg-gray-50.p-4.rounded-md');
            const batchHTML = Array.from(batchItems).map(batch => `
                <div class="batch-item">
                    <strong>${batch.querySelector('.font-semibold')?.textContent || 'N/A'}</strong><br>
                    ${batch.querySelectorAll('.text-xs')[0]?.textContent || ''}<br>
                    ${batch.querySelectorAll('.text-xs')[1]?.textContent || ''}<br>
                    ${batch.querySelectorAll('.text-xs')[2]?.textContent || ''}
                </div>
            `).join('');

            // Extract table data
            const tableRows = modal.querySelectorAll('table tbody tr');
            const tableHTML = Array.from(tableRows).map(row => `
                <tr>
                    <td>${row.cells[0]?.textContent || ''}</td>
                    <td>${row.cells[1]?.textContent || ''}</td>
                    <td>${row.cells[2]?.textContent || ''}</td>
                    <td>${row.cells[3]?.textContent || ''}</td>
                </tr>
            `).join('');

            // Extract payment summary
            const paymentSummary = modal.querySelector('.bg-gray-50.p-4.rounded-md');
            const cashReceived = paymentSummary?.querySelector('.flex.justify-between:nth-child(1) span:last-child')?.textContent || '₱0.00';
            const change = paymentSummary?.querySelector('.flex.justify-between:nth-child(2) span:last-child')?.textContent || '₱0.00';
            const totalAmount = paymentSummary?.querySelector('.flex.justify-between:nth-child(3) span:last-child')?.textContent || '₱0.00';

            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Sale Receipt - ${invoiceNumber}</title>
                    <style>
                        body { 
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                            margin: 20px; 
                            color: #333;
                            line-height: 1.4;
                        }
                        .header { 
                            text-align: center; 
                            margin-bottom: 30px; 
                            padding-bottom: 15px;
                            border-bottom: 3px double #333;
                        }
                        .header h1 { 
                            margin: 0; 
                            font-size: 28px; 
                            color: #2d3748;
                        }
                        .header h2 { 
                            margin: 5px 0 0 0; 
                            font-size: 18px; 
                            color: #4a5568;
                        }
                        .section { 
                            margin-bottom: 25px; 
                        }
                        .section h3 { 
                            background: #2d3748; 
                            color: white; 
                            padding: 8px 12px; 
                            margin: 0 0 15px 0;
                            border-radius: 4px;
                            font-size: 16px;
                        }
                        .info-grid {
                            display: grid;
                            grid-template-columns: repeat(4, 1fr);
                            gap: 15px;
                            margin-bottom: 20px;
                        }
                        .info-item {
                            background: #f8f9fa;
                            padding: 12px;
                            border-radius: 6px;
                            border-left: 4px solid #2d3748;
                        }
                        .info-item strong {
                            display: block;
                            margin-bottom: 5px;
                            color: #2d3748;
                        }
                        .batch-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                            gap: 15px;
                        }
                        .batch-item {
                            background: #f8f9fa;
                            padding: 12px;
                            border-radius: 6px;
                            border: 1px solid #e2e8f0;
                        }
                        .table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 15px 0;
                            font-size: 14px;
                        }
                        .table th {
                            background: #2d3748;
                            color: white;
                            padding: 10px;
                            text-align: left;
                            font-weight: 600;
                        }
                        .table td {
                            padding: 8px 10px;
                            border-bottom: 1px solid #e2e8f0;
                        }
                        .table tr:hover {
                            background: #f7fafc;
                        }
                        .payment-summary {
                            background: #f8f9fa;
                            padding: 20px;
                            border-radius: 8px;
                            border: 2px solid #e2e8f0;
                        }
                        .summary-item {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 10px;
                            padding: 5px 0;
                        }
                        .total-amount {
                            border-top: 2px solid #2d3748;
                            padding-top: 15px;
                            margin-top: 15px;
                            font-size: 16px;
                            font-weight: bold;
                        }
                        .footer {
                            text-align: center;
                            margin-top: 40px;
                            padding-top: 20px;
                            border-top: 1px solid #cbd5e0;
                            color: #718096;
                            font-size: 12px;
                        }
                        @media print {
                            body { margin: 15mm; }
                            .header { border-bottom: 3px double #000; }
                            .no-print { display: none; }
                        }
                        @page {
                            size: A4;
                            margin: 15mm;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>SALES RECEIPT</h1>
                        <h2>Invoice: ${invoiceNumber}</h2>
                    </div>
                    
                    <div class="section">
                        <h3>Sale Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <strong>Invoice Number</strong>
                                ${invoiceNumber}
                            </div>
                            <div class="info-item">
                                <strong>Sale Date</strong>
                                ${saleDate}
                            </div>
                            <div class="info-item">
                                <strong>Items Count</strong>
                                ${itemsCount}
                            </div>
                            <div class="info-item">
                                <strong>Processed By</strong>
                                ${processedBy}
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3>Batch Information</h3>
                        <div class="batch-grid">
                            ${batchHTML}
                        </div>
                    </div>

                    <div class="section">
                        <h3>Items Sold</h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableHTML}
                            </tbody>
                        </table>
                    </div>

                    <div class="section">
                        <h3>Payment Summary</h3>
                        <div class="payment-summary">
                            <div class="summary-item">
                                <span>Cash Received:</span>
                                <span><strong>${cashReceived}</strong></span>
                            </div>
                            <div class="summary-item">
                                <span>Change:</span>
                                <span><strong>${change}</strong></span>
                            </div>
                            <div class="summary-item total-amount">
                                <span>Total Amount:</span>
                                <span><strong>${totalAmount}</strong></span>
                            </div>
                        </div>
                    </div>

                    <div class="footer">
                        <p>Generated on: ${new Date().toLocaleDateString('en-PH', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}</p>
                        <p>Thank you for your business!</p>
                    </div>

                    <script>
                        window.onload = function() {
                            window.print();
                            setTimeout(() => {
                                window.close();
                            }, 1000);
                        }
                    <\/script>
                </body>
                </html>
            `;

            const printWindow = window.open('', '_blank', 'width=1000,height=700');
            printWindow.document.write(printContent);
            printWindow.document.close();

        } catch (error) {
            console.error('Print error:', error);
            Toast.error('Error generating print preview');
        }
    }
</script>

    </main>
</x-layout>
