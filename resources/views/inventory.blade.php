<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 pt-6 pb-3 flex flex-col items-center content-start">

            {{-- Temporary debug - display validation errors --}}
            @if($errors->any())
                <div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-red-100 border border-red-400 text-red-700 rounded shadow-lg">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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

        <!-- CONTAINER OUTSIDE THE TABLE -->
        <section class="container flex flex-col items-center place-content-start">

            <!-- SEARCH BAR AND FILTERS - SEPARATE FORM TO AVOID CONFLICTS -->
            <div class="container flex items-center place-content-start gap-4 mb-4">
                <!-- SEPARATE SEARCH/FILTER FORM - WON'T AFFECT OTHER FORMS -->
                <form action="{{ route('inventory.index') }}" method="GET" class="flex items-center gap-4 mr-auto">
                    <!-- Simple Search Input -->
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Search inventory..." 
                            class="pl-10 pr-4 py-2 border border-black rounded-md w-64"
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="relative">
                        <select name="category" class="px-3 py-2 border rounded-md border-black w-48 appearance-none max-h-[200px] overflow-y-auto" onchange="this.form.submit()">
                            <option value="all" {{ request('category') == 'all' || !request('category') ? 'selected' : '' }}>All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->productCategory }}" {{ request('category') == $category->productCategory ? 'selected' : '' }}>
                                    {{ $category->productCategory }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div class="relative">
                        <select name="brand" class="px-3 py-2 border rounded-md border-black w-40 appearance-none max-h-[200px] overflow-y-auto" onchange="this.form.submit()">
                            <option value="all" {{ request('brand') == 'all' || !request('brand') ? 'selected' : '' }}>All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->productBrand }}" {{ request('brand') == $brand->productBrand ? 'selected' : '' }}>
                                    {{ $brand->productBrand }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Search Button -->
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-all duration-100 ease-in-out">
                        Search
                    </button>

                    <!-- Clear Button (only show when filters are active) -->
                    @if(request('search') || (request('category') && request('category') != 'all') || (request('brand') && request('brand') != 'all'))
                        <a href="{{ route('inventory.index') }}" class="text-white px-4 py-2 hover:bg-gray-300 rounded-md bg-gray-400 transition-all duration-100 ease-in-out">
                            Clear
                        </a>
                    @endif
                </form>

                <!-- SEPARATE FROM THE FILTER FORM -->
                <x-form.createBtn @click="$refs.addProductRef.showModal()">Add New Product</x-form.createBtn>
            </div>
        </section>

        <!-- CONTAINER FOR TABLE DETAILS -->
        <section class="border w-full rounded-md border-solid border-black my-3">
            <table class="w-full table-fixed">
                <thead class="rounded-lg bg-main text-white px-4 py-3">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-3">Product Name</th>
                        <th class=" bg-main px-4 py-3">Category</th>
                        <th class=" bg-main px-4 py-3">SKU</th>
                        <th class=" bg-main px-4 py-3">Batch</th>
                        <th class=" bg-main px-4 py-3">Brand</th>
                        <th class=" bg-main px-4 py-3">Price</th>
                        <th class=" bg-main px-4 py-3">Stock</th>
                        <th class=" bg-main px-4 py-3">Status</th>
                        <th class=" bg-main px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr class="border-b">
                        <td class="truncate px-2 py-2 text-center" title="{{ $product->productName }}">
                            {{ $product->productName }}
                        </td>
                        <!-- For category -->
                        <td class="truncate px-2 py-2 text-center" title="{{ $product->category->productCategory ?? $product->productCategory ?? 'N/A' }}">
                            {{ $product->category->productCategory ?? $product->productCategory ?? 'N/A' }}
                        </td>
                        <td class="truncate px-2 py-2 text-center" title="{{ $product->productSKU }}">
                            {{ $product->productSKU }}
                        </td>
                        <td class="truncate px-2 py-2 text-center">
                            {{ $product->batches->count() }}
                        </td>
                        <!-- For brand -->
                        <td class="truncate px-2 py-2 text-center" title="{{ $product->brand->productBrand ?? $product->productBrand ?? 'N/A' }}">
                            {{ $product->brand->productBrand ?? $product->productBrand ?? 'N/A' }}
                        </td>
                        <td class="truncate px-2 py-2 text-center">
                            ₱{{ number_format($product->productSellingPrice, 2) }}
                        </td>
                        <td class="truncate px-2 py-2 text-center">
                            {{ $product->batches->sum('quantity') }} <!-- TOTAL STOCK -->
                        </td>
                        <td class="truncate px-2 py-2 text-center text-sm font-semibold">
                            @php
                                $totalStock = $product->batches->sum('quantity');
                            @endphp
                            @if ($totalStock == 0)
                                <span class="text-red-600 bg-red-100 px-2 py-1 rounded-xl">Out of Stock</span>
                            @elseif ($totalStock <= 10)
                                <span class="text-yellow-600 bg-yellow-100 px-2 py-1 rounded-xl">Low Stock</span>
                            @else
                                <span class="text-green-600 bg-green-100 px-2 py-1 rounded-xl">Active Stock</span>
                            @endif
                        </td>
                        <td class="truncate px-2 py-2 text-center">
                            <button @click="$refs['viewInventoryDetails{{ $product->id }}'].showModal()" 
                                    class="flex rounded-md bg-gray-400 px-3 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition:all duration-100 ease-in font-semibold">
                                View Details
                            </button>
                        </td>
                    </tr>
                    
                </tbody>

                <!-- VIEW INVENTORY DETAILS MODAL PER PRODUCT-->
                <x-modal.createModal x-ref="viewInventoryDetails{{ $product->id }}">
                    <x-slot:dialogTitle>Product Details: {{ $product->productName }}</x-slot:dialogTitle>
                    
                    <div class="grid grid-cols-2 gap-6 p-6">
                        <!-- LEFT: IMAGE -->
                        <div class="flex flex-col items-center justify-center">
                            <!-- Product name bigger -->
                            <h2 class="text-3xl tracking-wide font-bold text-start mr-auto uppercase pb-4 text-gray-800">
                                {{ $product->productName }}
                            </h2>
                            @if($product->productImage)
                                <img src="{{ asset('storage/' . $product->productImage) }}" 
                                    alt="{{ $product->productName }}" 
                                    class="w-full max-h-80 object-contain rounded-xl shadow-lg border">
                            @endif
                        </div>

                        <!-- RIGHT: DETAILS -->
                        <div class="flex flex-col space-y-4">

                            <!-- Info grid -->
                            <div class="grid grid-cols-4 gap-3">
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">SKU</p>
                                    <p class="text-sm">{{ $product->productSKU }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Total Stock</p>
                                    <p class="text-sm">{{ $product->batches->sum('quantity') }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Brand</p>
                                    <p class="text-sm">{{ $product->brand->productBrand ?? 'N/A' }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Measurement</p>
                                    <p class="text-sm">{{ $product->productItemMeasurement }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Category</p>
                                    <p class="text-sm">{{ $product->category->productCategory ?? 'N/A' }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Selling Price</p>
                                    <p class="text-sm">₱{{ number_format($product->productSellingPrice, 2) }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Cost Price</p>
                                    <p class="text-sm">₱{{ number_format($product->productCostPrice, 2) }}</p>
                                </div>
                                <!-- BATCH DETAILS SECTION -->
                                <div class="bg-gray-50 col-span-4 p-3 rounded-md">
                                    <p class="font-semibold text-md">Batch Details</p>
                                    @foreach($product->batches as $batch)
                                        <div class="text-sm border-b py-2">
                                            <strong>{{ $batch->batch_number }}:</strong><br>
                                            Qty: {{ $batch->quantity }} | 
                                            Exp: {{ $batch->expiration_date ? $batch->expiration_date->format('M d, Y') : 'Non-perishable' }} |
                                            Cost: ₱{{ number_format($batch->cost_price, 2) }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="flex justify-between items-center gap-x-4 px-6 pb-4 mt-4 border-t pt-4">
                        <!-- EDIT BUTTON: Opens edit dialog -->
                        <button 
                            @click="$refs['editProductDetails{{ $product->id }}'].showModal()" 
                            class="flex w-24 place-content-center rounded-md bg-button-create/70 px-3 py-2 text-blue-50 font-semibold items-center content-center hover:bg-button-create/60 transition-all duration-100 ease-in">
                            Edit
                        </button>

                        <!-- DELETE BUTTON: Opens delete dialog -->
                        <x-form.closeBtn @click="$refs['confirmDeleteModal{{ $product->id }}'].showModal()">Delete</x-form.closeBtn>

                        <!-- CLOSE BUTTON: Closes view details dialog -->
                        <button 
                            @click="$refs['viewInventoryDetails{{ $product->id }}'].close()" 
                            class="flex rounded-md ml-auto font-semibold bg-gray-400 px-6 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition-all duration-100 ease-in">
                            Close
                        </button>
                    </div>
                </x-modal.createModal>

                @endforeach
            </table>

            <!-- PAGINATION -->
            <div class="mt-4 px-4 py-2 bg-gray-50">
                {{ $products->appends(request()->except('page'))->links() }}
            </div>
        </section>


        <!-- ============================================ -->
        <!----------------- MODALS SECTION ----------------->
        <!-- ============================================ -->

        <!-- MODAL FOR ADDING PRODUCT -->
        <x-modal.createModal x-ref="addProductRef" class="h-5/6 w-1/2 rounded">
            <x-slot:dialogTitle>Add Product</x-slot:dialogTitle>

            <div x-data="{
                addMethod: 'manual',
                batches: [],
                newBatch: { quantity: '', expiration_date: '' },
                manual_productStock: 0,
                isPerishable: false,
                
                // Computed properties for field states
                get isManualMode() {
                    return this.addMethod === 'manual';
                },
                get isPOMode() {
                    return this.addMethod === 'po';
                },
                get shouldShowExpiryFields() {
                    return !this.isPerishable;
                },
                get shouldRequireBatches() {
                    return !this.isPerishable;
                },
                get areStocksFullyAssigned() {
                    if (this.isPerishable) return true; // Non-perishable doesn't need batch assignment
                    
                    const totalStock = this.isManualMode ? this.manual_productStock : 
                                    (this.isPOMode && typeof this.productStock !== 'undefined' ? this.productStock : 0);
                    return this.getTotalStock() === parseInt(totalStock || 0);
                },
                get shouldDisableQuantityField() {
                    return this.areStocksFullyAssigned && this.batches.length > 0;
                },
                get shouldDisableExpiryField() {
                    return this.areStocksFullyAssigned && this.batches.length > 0;
                },

                addBatch() {
                    if (this.newBatch.quantity && (this.isPerishable || this.newBatch.expiration_date)) {
                        this.batches.push({
                            quantity: parseInt(this.newBatch.quantity),
                            expiration_date: this.isPerishable ? null : this.newBatch.expiration_date,
                            batch_id: 'BATCH-' + Math.random().toString(36).substr(2, 9).toUpperCase()
                        });
                        this.newBatch = { quantity: '', expiration_date: '' };
                    }
                },
                
                removeBatch(index) {
                    this.batches.splice(index, 1);
                    // Re-enable fields if stocks are no longer fully assigned
                    this.updateFieldStates();
                },
                
                getTotalStock() {
                    return this.batches.reduce((total, batch) => total + parseInt(batch.quantity || 0), 0);
                },
                
                updateFieldStates() {
                    // This method will be called when batches change
                    // Fields will automatically update due to computed properties
                },
                
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                },
                
                validateForm() {
                    if (this.addMethod === 'manual') {
                        if (this.shouldRequireBatches && this.batches.length === 0) {
                            Toast.error('Please add batches to assign expiry dates');
                            return false;
                        }
                        
                        if (this.batches.length > 0) {
                            const totalStock = parseInt(this.manual_productStock || 0);
                            const assignedStock = this.getTotalStock();
                            
                            if (assignedStock !== totalStock) {
                                Toast.error('Batch quantities must equal total stocks');
                                return false;
                            }
                        }
                        
                        if (!this.manual_productStock || this.manual_productStock <= 0) {
                            Toast.error('Please enter total stocks');
                            return false;
                        }
                    }
                    return true;
                }
            }" class="px-3 pt-1">
                <div class="container mb-1 px-4 font-semibold flex w-full">
                    <label class="cursor-pointer flex-nowrap text-nowrap whitespace-nowrap">
                        <input type="radio" name="addMethod" value="manual" x-model="addMethod">
                        Add Manually
                    </label>
                    <label class="ml-4 cursor-pointer flex-nowrap text-nowrap whitespace-nowrap">
                        <input type="radio" name="addMethod" value="po" x-model="addMethod">
                        Add from Purchase Order
                    </label>

                    <div class="container place-items-end ml-auto" title="Products without expiry dates (Non-perishable products)">
                        <label class="flex items-center text-sm">
                            <input type="checkbox" name="shared_is_perishable" value="1" 
                                x-model="isPerishable" class="mr-1">
                            No Expiry
                        </label>
                    </div>
                </div>

                <!-- FORM WRAPS EVERYTHING -->
                <form id="addProductForm" x-ref="addProductForm" action="{{ route('inventory.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="add_method" x-model="addMethod">

                    <input type="hidden" name="manual_is_perishable" :value="isPerishable ? '1' : '0'">
                    <input type="hidden" name="is_perishable" :value="isPerishable ? '1' : '0'">

                    <!-- MANUAL SECTION -->
                    <section x-show="addMethod === 'manual'" class="space-y-6">
                        <!-- PRODUCT DETAILS -->
                        <div class="bg-gray-50 px-4 pt-4 pb-0 rounded-md">
                            <div class="grid grid-cols-6 gap-x-4 gap-y-3">

                                {{-- LABEL FOR PRODUCT NAME --}}
                                <x-form.form-input label="Product Name" class="col-span-2" name="manual_productName" type="text" value="" x-bind:required="addMethod === 'manual'"/>

                                {{-- LABEL FOR PRODUCT BRAND --}}
                                <div class='flex flex-col text-start col-span-2'>
                                    <label for="manual_productBrand">Product Brand</label>
                                    <select name="manual_productBrand" id="manual_productBrand" class="px-3 py-2.5 border rounded-sm border-black" x-bind:required="addMethod === 'manual'">
                                        <option value="" disabled selected>Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->productBrand }}">{{ $brand->productBrand }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- LABEL FOR PRODUCT CATEGORY --}}
                                <div class="container flex flex-col text-start col-span-2">
                                    <label for="manual_productCategory">Product Category</label>
                                    <select name="manual_productCategory" id="manual_productCategory" class="px-3 py-2.5 border rounded-sm border-black" x-bind:required="addMethod === 'manual'">
                                        <option value="" disabled selected>Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->productCategory }}">{{ $category->productCategory }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- LABEL FOR ITEM MEASUREMENT --}}
                                <div class="container flex flex-col text-start col-span-1">
                                    <label for="manual_productItemMeasurement">UOM</label>
                                    <select name="manual_productItemMeasurement" class="px-3 py-2.5 border rounded-sm border-black" x-bind:required="addMethod === 'manual'">
                                        <option value="" disabled selected>Select</option>
                                        <option value="kilogram">kg</option>
                                        <option value="gram">g</option>
                                        <option value="liter">L</option>
                                        <option value="milliliter">mL</option>
                                        <option value="pcs">pcs</option>
                                        <option value="set">set</option>
                                        <option value="pair">pair</option>
                                        <option value="pack">pack</option>
                                    </select>
                                </div>


                                <x-form.form-input label="Selling Price"  class="col-span-1" name="manual_productSellingPrice" value="" 
                                    type="number" step="0.01" min="0" x-bind:required="addMethod === 'manual'"/>

                                <x-form.form-input label="Cost Price"  class="col-span-1" name="manual_productCostPrice" value="" 
                                    type="number" step="0.01" min="0" x-bind:required="addMethod === 'manual'"/>

                                <x-form.form-input label="Total Stocks" type="number" value="" 
                                    name="manual_productStock" 
                                    x-model="manual_productStock"
                                    x-bind:required="addMethod === 'manual'"/>

                                <div class='container flex flex-col text-start col-span-2'>
                                    <label for="manual_productImage">Upload image</label>
                                    <input 
                                        id="manual_productImage" 
                                        name="manual_productImage" 
                                        type="file" 
                                        class="px-3 py-1.5 text-sm border rounded-sm border-black" 
                                        autocomplete="off"
                                        x-bind:required="addMethod === 'manual'"
                                    >
                                </div>

                                <!-- INFORMATION TEXT -->
                                <div class="container text-xs col-span-6 flex items-center content-start justify-start">
                                    <p class='text-xs text-gray-400 w-auto'>
                                        <strong>Batch Management:</strong> '
                                        Add batches with quantities and expiry dates. Total batches must equal total stock. <br> You may check the No Expiry box (upper right corner) for non-perishable items.
                                    </p>
                                </div>
                                <div class="container flex flex-col text-start col-span-1">
                                    <label>Quantity</label>
                                    <input type="number" x-model="newBatch.quantity" min="1" 
                                        :max="manual_productStock ? manual_productStock - getTotalStock() : 0"
                                        :disabled="isPerishable || !manual_productStock || manual_productStock <= 0 || shouldDisableQuantityField"
                                        class="px-3 py-2 border rounded-sm border-black [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0"
                                        :class="{'bg-gray-100': isPerishable || !manual_productStock || manual_productStock <= 0 || shouldDisableQuantityField}">
                                </div>
                                <div class="container flex flex-col text-start col-span-2" x-show="!isPerishable">
                                    <label>Expiration Date</label>
                                    <input type="date" x-model="newBatch.expiration_date" 
                                        :required="!isPerishable && !shouldDisableExpiryField"
                                        min="{{ date('Y-m-d') }}"
                                        :disabled="!manual_productStock || manual_productStock <= 0 || shouldDisableExpiryField"
                                        class="px-3 py-2 text-sm border rounded-sm border-black"
                                        :class="{'bg-gray-100': !manual_productStock || manual_productStock <= 0 || shouldDisableExpiryField}">
                                </div>

                                <!-- Show message when non-perishable -->
                                <div class="container flex flex-col text-start col-span-2" x-show="isPerishable">
                                    <label>Expiration Date</label>
                                    <div class="px-3 py-2 text-sm border rounded-sm border-black bg-gray-100 text-gray-500">
                                        Not required
                                    </div>
                                </div>

                                <div class="container flex flex-col mb-1 col-span-2 place-items-end content-end items-center justify-end">
                                    <button type="button" @click="addBatch()" 
                                        :disabled="!manual_productStock || !newBatch.quantity || (!isPerishable && !newBatch.expiration_date) || (parseInt(newBatch.quantity) + getTotalStock()) > parseInt(manual_productStock)"
                                        :class="{
                                            'bg-teal-500 hover:bg-teal-600': manual_productStock && newBatch.quantity && (isPerishable || newBatch.expiration_date) && (parseInt(newBatch.quantity) + getTotalStock()) <= parseInt(manual_productStock), 
                                            'bg-gray-300 cursor-not-allowed': !manual_productStock || !newBatch.quantity || (!isPerishable && !newBatch.expiration_date) || (parseInt(newBatch.quantity) + getTotalStock()) > parseInt(manual_productStock)
                                        }"
                                        class="px-4 py-2 text-md rounded text-white w-full transition-all duration-100 ease-in-out">
                                        + Add Batch
                                    </button>
                                </div>

                                <!-- STOCK ASSIGNMENT SUMMARY - ONLY SHOW FOR PERISHABLE ITEMS -->
                                <template x-if="!isPerishable && manual_productStock > 0">
                                    <div class="text-sm col-span-6">
                                        <span class="font-semibold" :class="{
                                            'text-green-600': getTotalStock() == manual_productStock,
                                            'text-yellow-600': getTotalStock() < manual_productStock,
                                            'text-red-600': getTotalStock() > manual_productStock
                                        }">
                                            Stocks assigned: <span x-text="getTotalStock()"></span>/<span x-text="manual_productStock || 0"></span>
                                        </span>
                                        <span x-show="getTotalStock() < manual_productStock" class="text-red-500 ml-2">
                                            (Remaining: <span x-text="(manual_productStock || 0) - getTotalStock()"></span> stocks need assignment)
                                        </span>
                                        <span x-show="getTotalStock() > manual_productStock" class="text-red-500 ml-2">
                                            (Warning: Overassigned by <span x-text="getTotalStock() - (manual_productStock || 0)"></span> stocks!)
                                        </span>
                                    </div>
                                </template>

                            </div>

                            <!-- PREVIEW TABLE FOR ADDED BATCH -->
                            <template x-if="batches.length > 0">
                                <div class="border w-auto rounded-md border-solid border-black mt-3 h-28 overflow-y-auto">
                                    <table class="w-full h-auto overflow-y-auto">
                                        <thead class="rounded-lg bg-main text-white px-4 py-2">
                                            <tr class="rounded-lg">
                                                <th class="bg-main px-2 py-2 text-sm">Batch</th>
                                                <th class="bg-main px-2 py-2 text-sm">No. of Items</th>
                                                <th class="bg-main px-2 py-2 text-sm">Expiry Date</th>
                                                <th class="bg-main px-2 py-2 text-sm">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(batch, index) in batches" :key="index">
                                                <tr class="border-b text-xs">
                                                    <td class="px-1 py-1 text-center" x-text="index + 1"></td>
                                                    <td class="px-1 py-1 text-center" x-text="batch.quantity"></td>
                                                    <td class="px-1 py-1 text-center" 
                                                        x-text="batch.expiration_date ? new Date(batch.expiration_date).toLocaleDateString() : 'Non-perishable'">
                                                    </td>
                                                    <td class="px-1 py-1 text-center mx-auto flex place-items-center items-center justify-center content-center">                            
                                                        <button type="button" @click="removeBatch(index)" class="text-red-500 hover:text-red-700">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 hover:fill-button-delete/70 cursor-pointer">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <template x-if="batches.length === 0">
                                <!-- EMPTY STATE -->
                                <div class="border w-auto rounded-md border-solid border-black mt-3 h-28">
                                    <div class="text-center py-8 text-gray-500">
                                        <p x-show="!isPerishable">No batch added yet. Adding batch is required to assign expiry dates for the items</p>
                                        <p x-show="isPerishable">Disabled for non-perishable items</p>
                                    </div>
                                </div>
                            </template>

                            <!-- HIDDEN INPUTS FOR BATCH DATA - PROPERLY WORKING -->
                            <template x-for="(batch, index) in batches" :key="index">
                                <div>
                                    <input type="hidden" :name="`manual_batches[${index}][quantity]`" :value="batch.quantity">
                                    <input type="hidden" :name="`manual_batches[${index}][expiration_date]`" :value="batch.expiration_date || ''">
                                </div>
                            </template>
                        </div>
                    </section>

<!-- PURCHASE ORDER SECTION -->
<section x-show="addMethod === 'po'" class="space-y-6" 
        x-data="{
    items: [],
    poId: null,
    sellingPrice: 0,
    costPrice: 0,
    productName: '',
    productStock: 0,
    originalStock: 0,
    itemMeasurement: '',
    selectedItemId: null,
    selectedQuality: 'goodCondition',
    badItemCount: 0,
    batches: [],
    newBatch: { quantity: '', expiration_date: '' },
    // use the top-level No Expiry checkbox via $root.isPerishable

    async getItems(poId) {
        this.poId = poId;
        this.items = [];
        this.selectedItemId = null;
        this.costPrice = 0;
        this.selectedQuality = 'goodCondition';
        this.badItemCount = 0;
        this.batches = [];
        this.newBatch = { quantity: '', expiration_date: '' };
        this.productStock = 0;
        this.originalStock = 0;
        this.itemMeasurement = ''; 
        
        if (!poId) return;
        
        try {
            const response = await fetch(`/get-items/${poId}`);
            this.items = await response.json();
            
            if (this.items.length === 0) return;
            
            if (this.items.length === 1) {
                this.selectedItemId = this.items[0].id;
                this.setCostPrice(this.items[0].id);
            }
        } catch (error) {
            console.error('Error fetching items:', error);
        }
    },
    
    setCostPrice(itemId) {
        this.selectedItemId = itemId;
        const item = this.items.find(i => i.id == itemId);
        if (item) {
            this.costPrice = item.unitPrice || 0;
            this.productName = item.productName;
            this.productStock = item.quantity;
            this.originalStock = item.quantity;
            this.itemMeasurement = item.itemMeasurement || ''; 
        }
    },
    
    updateStockBasedOnQuality() {
        if (this.selectedQuality === 'goodCondition') {
            this.productStock = this.originalStock;
            this.badItemCount = 0;
        } else if (this.badItemCount > 0) {
            this.productStock = Math.max(0, this.originalStock - this.badItemCount);
        }
    },
    
    addBatch() {
        if (this.newBatch.quantity && (this.$root.isPerishable || this.newBatch.expiration_date)) {
            this.batches.push({
                quantity: parseInt(this.newBatch.quantity),
                expiration_date: this.$root.isPerishable ? null : this.newBatch.expiration_date,
                batch_id: 'BATCH-' + Math.random().toString(36).substr(2, 9).toUpperCase()
            });
            this.newBatch = { quantity: '', expiration_date: '' };
        }
    },
    
    removeBatch(index) {
        this.batches.splice(index, 1);
    },
    
    getTotalStock() {
        return this.batches.reduce((total, batch) => total + parseInt(batch.quantity || 0), 0);
    }
}">

    <!-- PRODUCT DETAILS -->
    <div class="bg-gray-50 px-4 pt-4 pb-0 rounded-md">
        <div class="grid grid-cols-6 gap-x-4 gap-y-3">

            <!-- PO Number Dropdown -->
            <div class="flex flex-col text-start col-span-2">
                <label class="font-semibold">Purchase Order Number</label>
                <select name="purchaseOrderNumber" 
                        class="px-3 py-2.5 border rounded-sm border-black" 
                        x-model="poId" 
                        :required="addMethod === 'po'" 
                        @change="getItems($event.target.value)">
                    <option value="" disabled selected>Select PO Number</option>
                    @foreach($unaddedPOs as $po)
                        <option value="{{ $po->id }}">{{ $po->orderNumber }}</option>
                    @endforeach
                </select>
            </div>

            <!-- PO Items Dropdown -->
            <div class="flex flex-col text-start col-span-2">
                <label class="font-semibold">Purchase Order Item</label>
                <select name="selectedItemId"
                        class="px-3 py-2.5 border rounded-sm border-black"
                        x-model="selectedItemId" 
                        :disabled="!items.length"
                        @change="setCostPrice($event.target.value)"
                        required>
                    <option value="" disabled selected>Select PO Item</option>
                    <template x-for="item in items" :key="item.id">
                        <option :value="item.id" x-text="item.productName"></option>
                    </template>
                </select>
            </div>

            {{-- LABEL FOR PRODUCT NAME --}}
            <x-form.form-input label="Product Name" name="productName" type="text" value="" 
                                x-model="productName"
                                class="col-span-2"
                                x-bind:required="addMethod === 'po'"/>

            {{-- LABEL FOR PRODUCT BRAND --}}
            <div class='flex flex-col text-start col-span-2'>
                <label for="productBrand">Product Brand</label>
                <select name="productBrand" id="productBrand" class="px-3 py-2.5 border rounded-sm border-black" x-bind:required="addMethod === 'po'">
                    <option value="" disabled selected>Select Brand</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->productBrand }}">{{ $brand->productBrand }}</option>
                    @endforeach
                </select>
            </div>

            {{-- LABEL FOR PRODUCT CATEGORY --}}
            <div class="container flex flex-col text-start col-span-2">
                <label for="productCategory">Product Category</label>
                <select name="productCategory" id="productCategory" class="px-3 py-2.5 border rounded-sm border-black" x-bind:required="addMethod === 'po'">
                    <option value="" disabled selected>Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->productCategory }}">{{ $category->productCategory }}</option>
                    @endforeach
                </select>
            </div>

            {{-- LABEL FOR ITEM MEASUREMENT --}}
            <div class="container flex flex-col text-start col-span-1">
                <label for="itemMeasurement">UOM</label>
                <select name="productItemMeasurement" 
                        class="px-3 py-2.5 border rounded-sm border-black" 
                        x-model="itemMeasurement"
                        x-bind:required="addMethod === 'po'">
                    <option value="" disabled selected>Select</option>
                    <option value="kilogram">kg</option>
                    <option value="gram">g</option>
                    <option value="liter">L</option>
                    <option value="milliliter">mL</option>
                    <option value="pcs">pcs</option>
                    <option value="set">set</option>
                    <option value="pair">pair</option>
                    <option value="pack">pack</option>
                </select>
            </div>

            <!-- HIDDEN PRODUCT STOCK FIELD -->
            <input type="hidden" name="productStock" x-model="productStock" x-bind:required="addMethod === 'po'">

            <x-form.form-input label="Total Stocks" type="number" value="" 
                name="display_productStock" 
                x-model="productStock"
                readonly
                class="col-span-1"/>

            <x-form.form-input label="Selling Price" class="col-span-1" name="productSellingPrice" value="" 
                type="number" step="0.01" min="0" 
                x-bind:required="addMethod === 'po'"
                x-model="sellingPrice"/>

            <x-form.form-input label="Cost Price" class="col-span-1" name="productCostPrice" value="" 
                type="number" step="0.01" min="0" 
                readonly
                x-model="costPrice"/>

            <div class='container flex flex-col text-start col-span-2'>
                <label for="productImage">Upload image</label>
                <input 
                    id="productImage" 
                    name="productImage" 
                    type="file" 
                    class="px-3 py-1.5 text-sm border rounded-sm border-black" 
                    autocomplete="off"
                    x-bind:required="addMethod === 'po'"
                >
            </div>

            <!-- Quality Status -->
            <div class="flex flex-col text-start relative col-span-1">
                <label for="productQuality">Quality</label>
                <select class="px-3 py-2.5 border rounded-sm border-black" 
                name="productQuality" 
                x-model="selectedQuality"
                @change="updateStockBasedOnQuality()"
                x-bind:required="addMethod === 'po'">
                    <option value="goodCondition" selected>Good Condition</option>
                    <option value="defective">Defective</option>
                    <option value="incorrectItem">Incorrect Item</option>
                    <option value="nearExpiry">Near Expiry</option>
                    <option value="rejected">Rejected</option>
                    <option value="quantityMismatch">Quantity Mismatch</option>
                </select>
            </div>

            <!-- ALWAYS SHOW BUT DISABLED WHEN GOOD CONDITION -->
            <div class="flex flex-col items-center content-center text-md w-full col-span-1">
                <label for="badItemQuantity" class="pr-2">Item count: </label>
                <input name="badItemQuantity" id="badItemQuantity" type="number" step="1" min="0" 
                    :max="originalStock" 
                    :disabled="selectedQuality === 'goodCondition'"
                    class="w-full px-3 py-2 text-md border border-black"
                    :class="{'bg-gray-100': selectedQuality === 'goodCondition'}"
                    x-model="badItemCount"
                    @input="updateStockBasedOnQuality()">
            </div>

            <!-- INFORMATION TEXT -->
            <div class="container text-xs col-span-6">
                <p class='text-xs text-gray-400 w-auto'>
                    <strong>Batch Management:</strong> '
                    Add batches with quantities and expiry dates. Total batches must equal total stock. <br> You may check the No Expiry box (upper right corner) for non-perishable items.
                </p>
            </div>

            <!-- Quantity Input - PO SECTION ONLY -->
            <div class="container flex flex-col text-start col-span-1">
                <label>Quantity</label>
                <input type="number" x-model="newBatch.quantity" min="1" 
                    :max="productStock ? productStock - getTotalStock() : 0"
                    :disabled="getTotalStock() === productStock || $root.isPerishable || !productStock || productStock <= 0"
                    class="px-3 py-2 border rounded-sm border-black [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0"
                    :class="{'bg-gray-100': getTotalStock() === productStock || $root.isPerishable || !productStock || productStock <= 0}">
            </div>

            <!-- Expiry Date Input - PO SECTION ONLY -->
            <div class="container flex flex-col text-start col-span-2" x-show="!$root.isPerishable">
                <label>Expiration Date</label>
                <input type="date" x-model="newBatch.expiration_date"
                    :required="!$root.isPerishable && !(getTotalStock() === productStock)"
                    min="{{ date('Y-m-d') }}"
                    :disabled="$root.isPerishable || getTotalStock() === productStock || !productStock || productStock <= 0"
                    class="px-3 py-2 text-sm border rounded-sm border-black"
                    :class="{'bg-gray-100': $root.isPerishable || getTotalStock() === productStock || !productStock || productStock <= 0}">
            </div>

            <!-- Show message when non-perishable -->
            <div class="container flex flex-col text-start col-span-2" x-show="$root.isPerishable">
                <label>Expiration Date</label>
                <div class="px-3 py-2 text-sm border rounded-sm border-black bg-gray-100 text-gray-500">
                    Not required
                </div>
            </div>
            <div class="container flex flex-col mb-1 col-span-2 place-items-end content-end items-center justify-end">
                <button type="button" @click="addBatch()" 
                    :disabled="getTotalStock() === productStock || !productStock || !newBatch.quantity || (!isPerishable && !newBatch.expiration_date) || (parseInt(newBatch.quantity) + getTotalStock()) > parseInt(productStock)"
                    :class="{
                        'bg-teal-500 hover:bg-teal-600': !(getTotalStock() === productStock) && productStock && newBatch.quantity && (isPerishable || newBatch.expiration_date) && (parseInt(newBatch.quantity) + getTotalStock()) <= parseInt(productStock), 
                        'bg-gray-300 cursor-not-allowed': getTotalStock() === productStock || !productStock || !newBatch.quantity || (!isPerishable && !newBatch.expiration_date) || (parseInt(newBatch.quantity) + getTotalStock()) > parseInt(productStock)
                    }"
                    class="px-4 py-2 text-md rounded text-white w-full transition-all duration-100 ease-in-out">
                    + Add Batch
                </button>
            </div>
        </div>

        <!-- STOCK ASSIGNMENT SUMMARY -->
        <div class="mt-3 text-sm col-span-6" x-show="(!isPerishable && productStock > 0) || batches.length > 0">
            <span class="font-semibold" :class="{
                'text-green-600': getTotalStock() == productStock, 
                'text-yellow-600': getTotalStock() < productStock,
                'text-red-600': getTotalStock() > productStock
            }">
                Stocks assigned: <span x-text="getTotalStock()"></span>/<span x-text="productStock || 0"></span>
            </span>
            <span x-show="getTotalStock() < productStock" class="text-red-500 ml-2">
                (Remaining: <span x-text="(productStock || 0) - getTotalStock()"></span> stocks need assignment)
            </span>
            <span x-show="getTotalStock() > productStock" class="text-red-500 ml-2">
                (Warning: Overassigned by <span x-text="getTotalStock() - (productStock || 0)"></span> stocks!)
            </span>
        </div>

        <!-- PREVIEW TABLE FOR ADDED BATCH -->
        <template x-if="batches.length > 0">
            <div class="border w-auto rounded-md border-solid border-black mt-3 h-28 overflow-y-auto">
                <table class="w-full h-auto overflow-y-auto">
                    <thead class="rounded-lg bg-main text-white px-4 py-2">
                        <tr class="rounded-lg">
                            <th class="bg-main px-2 py-2 text-sm">Batch</th>
                            <th class="bg-main px-2 py-2 text-sm">No. of Items</th>
                            <th class="bg-main px-2 py-2 text-sm">Expiry Date</th>
                            <th class="bg-main px-2 py-2 text-sm">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(batch, index) in batches" :key="index">
                            <tr class="border-b text-xs">
                                <td class="px-1 py-1 text-center" x-text="index + 1"></td>
                                <td class="px-1 py-1 text-center" x-text="batch.quantity"></td>
                                <td class="px-1 py-1 text-center" x-text="new Date(batch.expiration_date).toLocaleDateString()"></td>
                                <td class="px-1 py-1 text-center mx-auto flex place-items-center items-center justify-center content-center">                            
                                    <button type="button" @click="removeBatch(index)" class="text-red-500 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 hover:fill-button-delete/70 cursor-pointer">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        <template x-if="batches.length === 0">
            <!-- EMPTY STATE -->
            <div class="border w-auto rounded-md border-solid border-black mt-3 h-28">
                <div class="text-center py-8 text-gray-500">
                    <p x-show="!isPerishable">No batch added yet. Adding batch is required to assign expiry dates for the items</p>
                    <p x-show="isPerishable">Disabled for non-perishable items</p>
                </div>
            </div>
        </template>

        <!-- HIDDEN INPUTS FOR BATCH DATA - FIXED FOR PO SECTION -->
        <template x-for="(batch, index) in batches" :key="index">
            <div>
                <input type="hidden" :name="`batches[${index}][quantity]`" :value="batch.quantity">
                <input type="hidden" :name="`batches[${index}][expiration_date]`" :value="batch.expiration_date || ''">
            </div>
        </template>
    </div>
</section>

                    <!-- FORM BUTTONS -->
                    <div class="flex justify-end gap-4 mt-4 mx-4">
                        <x-form.closeBtn type="button" @click="$refs.cancelAddProduct.showModal()">Cancel</x-form.closeBtn>
                        <x-form.saveBtn 
                            type="button" 
                            @click="
                                if (validateForm() && document.getElementById('addProductForm').reportValidity()) {
                                    ($refs.confirmAddProduct || document.getElementById('confirmAddProduct')).showModal();
                                }">
                            Add
                        </x-form.saveBtn>
                    </div>
                </form>
            </div>
        </x-modal.createModal>

        <!-- CONFIRMATION MODALS -->
        <x-modal.createModal x-ref="cancelAddProduct" class="z-50">
            <x-slot:dialogTitle>Confirm Cancel?</x-slot:dialogTitle>
            <div class="container px-2 py-2">
                <h1 class="py-6 px-5 text-xl">Are you sure you want to go back? Changes will not be saved</h1>
                <div class="col-span-6 place-items-end flex justify-end gap-4">
                    <x-form.closeBtn @click="$refs.cancelAddProduct.close()">Cancel</x-form.closeBtn>
                    <x-form.saveBtn @click="
                        // Get the form directly by ID kay lahi ang ilahang modal components
                        document.getElementById('addProductForm').reset();
                        // Close modals in correct order
                        $refs.addProductRef.close();
                        $refs.cancelAddProduct.close();
                    " type="button">Confirm</x-form.saveBtn>
                </div>
            </div>
        </x-modal.createModal>

        <x-modal.createModal x-ref="confirmAddProduct" class="z-50">
            <x-slot:dialogTitle>Confirm Save?</x-slot:dialogTitle>
            <div class="container px-2 py-2">
                <h1 class="py-6 px-5 text-xl">Are you sure you want to add this item to inventory?</h1>
                <div class="col-span-6 place-items-end flex justify-end gap-4">
                    <x-form.closeBtn @click="$refs.confirmAddProduct.close()">Cancel</x-form.closeBtn>
                    <x-form.saveBtn type="submit" form="addProductForm">Save</x-form.saveBtn>
                </div>
            </div>
        </x-modal.createModal>

        <!-- ======================================================= -->
        <!-- VIEW INVENTORY MODAL IS INLINE PARA WAY HASOL ALPINE JS -->
        <!-- ======================================================= -->

        <!-- UPDATE MODAL -->
        @foreach($products as $product)
            <x-modal.createModal x-ref="editProductDetails{{ $product->id }}">
                <x-slot:dialogTitle>Update {{ $product->productName }}</x-slot:dialogTitle>

                <div class="container px-3 py-4">
                    <form id="updateInventoryForm{{ $product->id }}" action="{{ route('inventory.update', $product->id) }}" method="POST" enctype="multipart/form-data"
                        class="px-6 py-4 container grid grid-cols-6 gap-x-8 gap-y-6"
                        x-data>
                        @csrf
                        @method('PUT')

                        <!-- Left Side (Image + Upload) -->
                        <div class="col-span-2 flex flex-col items-center gap-3">
                            @if($product->productImage)
                                <img src="{{ asset('storage/' . $product->productImage) }}" 
                                    alt="{{ $product->productName }}" 
                                    class="size-44 object-contain border rounded shadow-sm">
                            @endif
                            <div class="w-full">
                                <x-form.form-input label="Update image (optional)" name="productImage" type="file" :required="false" />
                            </div>
                        </div>

                        <!-- Right Side (Main Info) -->
                        <div class="col-span-4 grid grid-cols-4 gap-6">
                            
                            <!-- Product Name (full width) -->
                            <x-form.form-input label="Product Name" name="productName" type="text" 
                                value="{{ $product->productName }}" class="col-span-4" required />

                            <!-- Product Brand (full width) - FIXED: Dynamic from database -->
                            <div class="col-span-4 flex flex-col text-start">
                                <label for="productBrand" class="font-medium">Product Brand</label>
                                <select name="productBrand" id="productBrand" 
                                    class="px-3 py-2 border rounded border-gray-300 focus:ring focus:ring-blue-200" required>
                                    <option value="" disabled>Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->productBrand }}" {{ $product->productBrand == $brand->productBrand ? 'selected' : '' }}>
                                            {{ $brand->productBrand }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Category  -->
                            <div class="col-span-2 flex flex-col text-start">
                                <label for="productCategory" class="font-medium">Product Category</label>
                                <select name="productCategory" id="productCategory" 
                                    class="px-3 py-2 border rounded border-gray-300 focus:ring focus:ring-blue-200" required>
                                    <option value="" disabled>Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->productCategory }}" {{ $product->productCategory == $category->productCategory ? 'selected' : '' }}>
                                            {{ $category->productCategory }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Stock (half) -->
                            <x-form.form-input label="Stock" name="productStock" value="{{ $product->productStock }}" 
                                class="col-span-2" type="number" step="1" min="0" required />
                        </div>

                        <!-- Rest of fields below (full-width layout) -->
                        <x-form.form-input label="Selling Price (₱)" name="productSellingPrice" 
                            value="{{ $product->productSellingPrice }}" class="col-span-2" 
                            type="number" step="0.01" min="0" required />

                        <x-form.form-input label="Cost Price (₱)" name="productCostPrice" 
                            value="{{ $product->productCostPrice }}" class="col-span-2" 
                            type="number" step="0.01" min="0" required />

                        <div class="flex flex-col text-start col-span-3">
                            <label for="itemMeasurement" class="font-medium">Measurement per item</label>
                            <select name="productItemMeasurement" 
                                    class="px-3 py-2 border rounded border-gray-300 focus:ring focus:ring-blue-200" required>
                                <option value="" disabled>Select Measurement</option>
                                <option value="kilogram" {{ $product->productItemMeasurement == 'kilogram' ? 'selected' : '' }}>kilogram (kg)</option>
                                <option value="gram" {{ $product->productItemMeasurement == 'gram' ? 'selected' : '' }}>gram (g)</option>
                                <option value="liter" {{ $product->productItemMeasurement == 'liter' ? 'selected' : '' }}>liter (L)</option>
                                <option value="milliliter" {{ $product->productItemMeasurement == 'milliliter' ? 'selected' : '' }}>milliliter (mL)</option>
                                <option value="pcs" {{ $product->productItemMeasurement == 'pcs' ? 'selected' : '' }}>pieces (pcs)</option>
                                <option value="set" {{ $product->productItemMeasurement == 'set' ? 'selected' : '' }}>set</option>
                                <option value="pair" {{ $product->productItemMeasurement == 'pair' ? 'selected' : '' }}>pair</option>
                                <option value="pack" {{ $product->productItemMeasurement == 'pack' ? 'selected' : '' }}>pack</option>
                            </select>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="container col-span-6 gap-x-4 place-content-end w-full flex items-end content-center px-6 mt-4">
                            <button type="button" 
                                    @click="$refs['editProductDetails{{ $product->id }}'].close()" 
                                    class="mr-2 px-4 py-2 rounded bg-gray-400 hover:bg-gray-300 text-white duration-200 transition-all ease-in-out">
                                Cancel
                            </button>
                            <x-form.saveBtn @click="$refs['confirmEditProduct{{ $product->id }}'].showModal()" type="button">Update</x-form.saveBtn>
                        </div>
                    </form>
                </div>
            </x-modal.createModal>


            <!-- UPDATE CONFIRMATION MODAL -->
            <x-modal.createModal x-ref="confirmEditProduct{{ $product->id }}">
                <x-slot:dialogTitle>Confirm Changes?</x-slot:dialogTitle>
                <div class="container px-2 py-2">
                    <h1 class="py-6 px-5 text-xl">Are you sure you want to save these changes?</h1>
                    <div class="col-span-6 place-items-end flex justify-end gap-4">
                        <x-form.closeBtn @click="$refs.confirmEditProduct{{ $product->id }}.close()">Cancel</x-form.closeBtn>
                        <x-form.saveBtn type="submit" form="updateInventoryForm{{ $product->id }}">Save</x-form.saveBtn>
                    </div>
                </div>
            </x-modal.createModal>

            
        @endforeach



        <!-- DELETE CONFIRMATION MODAL -->
        @foreach ($products as $product)
            <x-modal.createModal x-ref="confirmDeleteModal{{ $product->id }}" class="z-50">
                <x-slot:dialogTitle>Are you sure?</x-slot:dialogTitle>
                
                <div class="container px-2 py-2">
                    <h1 class="py-6 px-5 text-xl">
                        <p class="text-lg">Are you sure you want to delete <span class="font-bold">{{ $product->productName }}</span>?</p>
                        <p class="text-xs text-gray-600 mt-2">This action cannot be undone. All items associated with this product will also be deleted.</p>
                    </h1>
                    
                    <div class="flex justify-end gap-4">
                        <!-- CANCEL -->
                        <x-form.closeBtn 
                            @click="$refs['confirmDeleteModal{{ $product->id }}'].close()" 
                            type="button">
                            Cancel
                        </x-form.closeBtn>

                        <!-- DELETE FORM -->
                        <form action="{{ route('inventory.destroy', $product->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <x-form.saveBtn type="submit">Delete</x-form.saveBtn>
                        </form>
                    </div>
                </div>
            </x-modal.createModal>
        @endforeach

    </main>
</x-layout>