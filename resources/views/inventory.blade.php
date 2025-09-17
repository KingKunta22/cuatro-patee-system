<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 pt-6 pb-3 flex flex-col items-center content-start">

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
                        <th class=" bg-main px-4 py-3">Expiry Date</th>
                        <th class=" bg-main px-4 py-3">Action</th>
                    </tr>
                </thead>

                @foreach($inventoryItems as $item)

                <tbody>
                    <tr class="border-b">
                        <td class="truncate px-2 py-2 text-center" title="{{ $item->productName }}">{{ $item->productName }}</td>
                        <td class="truncate px-2 py-2 text-center" title="{{ $item->productCategory }}">{{ $item->productCategory }}</td>
                        <td class="truncate px-2 py-2 text-center" title="{{ $item->productSKU }}">{{ $item->productSKU }}</td>
                        <td class="truncate px-2 py-2 text-center" title="{{ $item->productBatch }}">{{ $item->productBatch }}</td>
                        <td class="truncate px-2 py-2 text-center" title="{{ $item->productBrand}}">{{ $item->productBrand}}</td>
                        <td class="truncate px-2 py-2 text-center" title="{{ $item->productSellingPrice }}">₱{{ $item->productSellingPrice }}</td>
                        <td class="truncate px-2 py-2 text-center" title="{{ $item->productStock }}">{{ $item->productStock }}</td>
                        <td class="truncate px-2 py-2 text-center text-sm font-semibold">
                            @if ($item->productStock == 0)
                                <span class="text-red-600 bg-red-100 px-2 py-1 rounded-xl" title="This item is out of stock (0 units available)">
                                    Out of Stock
                                </span>
                            @elseif ($item->productStock <= 5)
                                <span class="text-red-600 bg-red-100 px-2 py-1 rounded-xl" title="This item has very low stock (only {{ $item->productStock }} units left)">
                                    Very Low Stock
                                </span>
                            @elseif ($item->productStock <= 10)
                                <span class="text-yellow-600 bg-yellow-100 px-2 py-1 rounded-xl" title="This item has low stock (only {{ $item->productStock }} units left)">
                                    Low Stock
                                </span>
                            @else
                                <span class="text-green-600 bg-green-100 px-2 py-1 rounded-xl" title="This item has sufficient stock ({{ $item->productStock }} units available)">
                                    Active Stock
                                </span>
                            @endif
                        </td>
                        <td class="truncate px-2 py-2 text-center" title="{{ $item->productExpirationDate }}">{{ $item->productExpirationDate }}</td>
                        <td class="truncate px-2 py-2 text-center" title="">
                            <button @click="$refs['viewInventoryDetails{{ $item->id }}'].showModal()" class="flex rounded-md bg-gray-400 px-3 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition:all duration-100 ease-in font-semibold">View Details</button>
                        </td>
                    </tr>
                </tbody>


                <!-- VIEW INVENTORY DETAILS MODAL PER PRODUCT-->
                <x-modal.createModal x-ref="viewInventoryDetails{{ $item->id }}">
                    <x-slot:dialogTitle>Product Details</x-slot:dialogTitle>
                    
                    <div class="grid grid-cols-2 gap-6 p-6">

                        <!-- LEFT: IMAGE -->
                        <div class="flex flex-col items-center justify-center">
                            <!-- Product name bigger -->
                            <h2 class="text-3xl tracking-wide font-bold text-start mr-auto uppercase pb-4 text-gray-800">{{ $item->productName }}</h2>

                            <img src="{{ asset('storage/' . $item->productImage) }}" 
                                alt="{{ $item->productName }}" 
                                class="w-full max-h-80 object-contain rounded-xl shadow-lg border">
                        </div>

                        <!-- RIGHT: DETAILS -->
                        <div class="flex flex-col space-y-4">

                            <!-- Info grid -->
                            <div class="grid grid-cols-4 gap-3">
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">SKU</p>
                                    <p class="text-sm">{{ $item->productSKU }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Batch</p>
                                    <p class="text-sm">{{ $item->productBatch }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Brand</p>
                                    <p class="text-sm">{{ $item->productBrand }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Stock</p>
                                    <p class="text-sm">{{ $item->productStock }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Measurement</p>
                                    <p class="text-sm">{{ $item->productItemMeasurement }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Category</p>
                                    <p class="text-sm">{{ $item->productCategory }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Selling Price</p>
                                    <p class="text-sm">₱{{ number_format($item->productSellingPrice, 2) }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-2 p-3 rounded-md">
                                    <p class="font-semibold text-md">Cost Price</p>
                                    <p class="text-sm">₱{{ number_format($item->productCostPrice, 2) }}</p>
                                </div>
                                <div class="bg-gray-50 col-span-3 p-3 rounded-md">
                                    <p class="font-semibold text-md">Expiration Date</p>
                                    <p class="text-sm">
                                        {{ \Carbon\Carbon::parse($item->productExpirationDate)->format('M d, Y') }}
                                        ({{ \Carbon\Carbon::parse($item->productExpirationDate)->diffForHumans() }})
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="flex justify-between items-center gap-x-4 px-6 pb-4 mt-4 border-t pt-4">
                        <!-- EDIT BUTTON: Opens edit dialog -->
                        <button 
                            @click="$refs['editProductDetails{{ $item->id }}'].showModal()" 
                            class="flex w-24 place-content-center rounded-md bg-button-create/70 px-3 py-2 text-blue-50 font-semibold items-center content-center hover:bg-button-create/60 transition-all duration-100 ease-in">
                            Edit
                        </button>

                        <!-- DELETE BUTTON: Opens delete dialog -->
                        <x-form.closeBtn @click="$refs['confirmDeleteModal{{ $item->id }}'].showModal()">Delete</x-form.closeBtn>

                        <!-- CLOSE BUTTON: Closes view details dialog -->
                        <button 
                            @click="$refs['viewInventoryDetails{{ $item->id }}'].close()" 
                            class="flex rounded-md ml-auto font-semibold bg-gray-400 px-6 py-2 w-auto text-white items-center content-center hover:bg-gray-400/70 transition-all duration-100 ease-in">
                            Close
                        </button>
                    </div>
                </x-modal.createModal>

                @endforeach
            </table>

            <!-- PAGINATION VIEW -->
            <div class="mt-4 px-4 py-2 bg-gray-50">
                {{ $inventoryItems->appends(request()->except('page'))->links() }}
            </div>

        </section>


        <!-- ============================================ -->
        <!----------------- MODALS SECTION ----------------->
        <!-- ============================================ -->

        <!-- MODAL FOR ADDING PRODUCT -->
        <x-modal.createModal x-ref="addProductRef">
            <x-slot:dialogTitle>Add Product</x-slot:dialogTitle>

            <div x-data="{
                addMethod: 'manual',
                batches: [],
                newBatch: { quantity: '', expiration_date: '' },
                
                addBatch() {
                    if (this.newBatch.quantity && this.newBatch.expiration_date) {
                        this.batches.push({
                            quantity: this.newBatch.quantity,
                            expiration_date: this.newBatch.expiration_date,
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
                },
                
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }
            }" class="px-3 py-4">
                <div class="container mb-4 font-semibold flex">
                    <label class="cursor-pointer">
                        <input type="radio" name="addMethod" value="manual" x-model="addMethod">
                        Add Manually
                    </label>
                    <label class="ml-4 cursor-pointer">
                        <input type="radio" name="addMethod" value="po" x-model="addMethod">
                        Add from Purchase Order
                    </label>
                </div>

                <!-- FORM WRAPS EVERYTHING -->
                <form id="addProductForm" x-ref="addProductForm" action="{{ route('inventory.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="add_method" x-model="addMethod">

                    <!-- MANUAL SECTION -->
                    <section x-show="addMethod === 'manual'" class="space-y-6">
                        <!-- PRODUCT DETAILS -->
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h3 class="font-semibold text-lg mb-4">Product Details</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <x-form.form-input label="Product Name" name="manual_productName" type="text" value="" x-bind:required="addMethod === 'manual'"/>
                                
                                <div class='flex flex-col text-start'>
                                    <label for="manual_productBrand">Product Brand</label>
                                    <select name="manual_productBrand" id="manual_productBrand" class="px-3 py-2 border rounded-sm border-black" x-bind:required="addMethod === 'manual'">
                                        <option value="" disabled selected>Select Brand</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->productBrand }}">{{ $brand->productBrand }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex flex-col text-start">
                                    <label for="manual_productCategory">Product Category</label>
                                    <select name="manual_productCategory" id="manual_productCategory" class="px-3 py-2 border rounded-sm border-black" x-bind:required="addMethod === 'manual'">
                                        <option value="" disabled selected>Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->productCategory }}">{{ $category->productCategory }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex flex-col text-start">
                                    <label for="manual_productItemMeasurement">Measurement per item</label>
                                    <select name="manual_productItemMeasurement" class="px-3 py-2 border rounded-sm border-black" x-bind:required="addMethod === 'manual'">
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

                                <x-form.form-input label="Selling Price (₱)" name="manual_productSellingPrice" value="" 
                                    type="number" step="0.01" min="0" x-bind:required="addMethod === 'manual'"/>

                                <x-form.form-input label="Cost Price (₱)" name="manual_productCostPrice" value="" 
                                    type="number" step="0.01" min="0" x-bind:required="addMethod === 'manual'"/>

                                <x-form.form-input label="Upload an image" name="manual_productImage" type="file" value="" 
                                    class="col-span-2" x-bind:required="addMethod === 'manual'"/>
                            </div>
                        </div>

                        <!-- BATCH DETAILS -->
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h3 class="font-semibold text-lg mb-2">Batch Details</h3>
                            <p class="text-xs text-gray-600 mb-4 italic">
                                If the same product has items with different expiration dates, add them here. <br>
                                Each row will be tracked as a separate batch.
                            </p>

                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium">Quantity</label>
                                    <input type="number" x-model="newBatch.quantity" min="1" 
                                        class="w-full px-3 py-2 border rounded-sm border-black" placeholder="Qty">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium">Expiration Date</label>
                                    <input type="date" x-model="newBatch.expiration_date" 
                                        min="{{ date('Y-m-d') }}"
                                        class="w-full px-3 py-2 border rounded-sm border-black">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" @click="addBatch()" 
                                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                                        + Add Batch
                                    </button>
                                </div>
                            </div>

                            <!-- BATCH LIST -->
                            <template x-for="(batch, index) in batches" :key="batch.batch_id">
                                <div class="grid grid-cols-3 gap-4 mb-2 items-center">
                                    <div>
                                        <input type="number" :name="'batches[' + index + '][quantity]'" 
                                            x-model="batch.quantity" min="1" readonly
                                            class="w-full px-3 py-2 border rounded-sm border-gray-300 bg-gray-100">
                                    </div>
                                    <div>
                                        <input type="date" :name="'batches[' + index + '][expiration_date]'" 
                                            x-model="batch.expiration_date" readonly
                                            class="w-full px-3 py-2 border rounded-sm border-gray-300 bg-gray-100">
                                    </div>
                                    <div>
                                        <button type="button" @click="removeBatch(index)" 
                                            class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- STOCK PREVIEW -->
                        <div class="bg-blue-50 p-4 rounded-md" x-show="batches.length > 0">
                            <h3 class="font-semibold text-lg mb-3">Stock Preview</h3>
                            <div class="space-y-2">
                                <template x-for="(batch, index) in batches" :key="batch.batch_id">
                                    <div class="flex justify-between items-center text-sm">
                                        <span x-text="'Batch ' + String.fromCharCode(65 + index) + ' – ' + batch.quantity + ' pcs (Exp: ' + formatDate(batch.expiration_date) + ')'"></span>
                                    </div>
                                </template>
                                <div class="border-t pt-2 mt-2 font-semibold">
                                    Total Active Stock: <span x-text="getTotalStock()"></span> pcs
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- PURCHASE ORDER SECTION -->
                    <section x-show="addMethod === 'po'" class="grid grid-cols-6 col-span-6 gap-4" 
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
                                
                                async getItems(poId) {
                                    this.poId = poId;
                                    this.items = [];
                                    this.selectedItemId = null;
                                    this.costPrice = 0;
                                    this.selectedQuality = 'goodCondition';
                                    this.badItemCount = 0;
                                    
                                    if (!poId) return;
                                    
                                    const response = await fetch(`/get-items/${poId}`);
                                    this.items = await response.json();
                                    
                                    if (this.items.length === 0) {
                                        console.log('No items available for this PO');
                                        return;
                                    }
                                    
                                    if (this.items.length === 1) {
                                        this.selectedItemId = this.items[0].id;
                                        this.setCostPrice(this.items[0].id);
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
                                        this.itemMeasurement = item.itemMeasurement;
                                    }
                                },
                                
                                updateStockBasedOnQuality() {
                                    if (this.selectedQuality === 'goodCondition') {
                                        this.productStock = this.originalStock;
                                        this.badItemCount = 0;
                                    } else if (this.badItemCount > 0) {
                                        this.productStock = Math.max(0, this.originalStock - this.badItemCount);
                                    }
                                }
                            }">


                        <!-- PO Number Dropdown -->
                        <div class="container text-start flex col-span-3 w-full flex-col font-semibold">
                            <label>Purchase Order Number</label>
                            <select name="purchaseOrderNumber" 
                                    class="px-3 py-2 border rounded-sm border-black" 
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
                        <div class="container text-start flex col-span-3 w-full flex-col font-semibold">
                            <label>Purchase Order Item</label>
                            <select name="selectedItemId"
                                    class="px-3 py-2 border rounded-sm border-black"
                                    x-model="selectedItemId" 
                                    :disabled="!items.length"
                                    @change="setCostPrice($event.target.value)">
                                <option value="" disabled selected>Select PO Item</option>
                                <template x-for="item in items" :key="item.id">
                                    <option :value="item.id" x-text="item.productName"></option>
                                </template>
                            </select>
                        </div>

                        <div class="container grid grid-cols-6 col-span-6 gap-4">
                            <x-form.form-input label="Product Name" name="productName" type="text" value="" 
                                                class="col-span-3"
                                                x-model="productName"
                                                x-bind:required="addMethod === 'po'"/>
                                                
                            <div class='container flex flex-col text-start col-span-3'>
                                <label for="productBrand">Product Brand</label>
                                <select name="productBrand" id="productBrand" class="px-3 py-2 border rounded-sm border-black overflow-y-auto max-h-[200px]" x-bind:required="addMethod === 'po'">
                                    <option value="" disabled selected>Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->productBrand }}">{{ $brand->productBrand }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="container flex flex-col text-start col-span-3">
                                <label for="productCategory">Product Category</label>
                                <select name="productCategory" id="productCategory" class="px-3 py-2 border rounded-sm border-black overflow-y-auto max-h-[200px]" x-bind:required="addMethod === 'po'">
                                    <option value="" disabled selected>Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->productCategory }}">{{ $category->productCategory }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <x-form.form-input label="Stock" type="number" value="" 
                                                class="col-span-1" 
                                                name="productStock" 
                                                x-model="productStock"
                                                x-bind:required="addMethod === 'po'"
                                                readonly/>
                            
                            <div class="container text-start flex col-span-2 w-full flex-col">
                                <label for="itemMeasurement">Measurement per item</label>
                                <select class="px-3 py-2 border rounded-sm border-black" 
                                name="productItemMeasurement" 
                                x-model="itemMeasurement"
                                x-bind:required="addMethod === 'po'">
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

                            <x-form.form-input label="Selling Price (₱)" name="productSellingPrice" type="number" value="" class="col-span-2" 
                                                x-bind:required="addMethod === 'po'"
                                                x-model="sellingPrice"/>

                            <x-form.form-input label="Cost Price (₱)" name="productCostPrice" type="number" value="" 
                                                class="col-span-2" readonly
                                                x-model="costPrice"/>


                            <div class="container text-start flex col-span-2 w-full flex-col relative">
                                <label for="productQuality">Quality Status</label>
                                <select class="px-3 py-2 border rounded-sm border-black" 
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

                                <!-- WILL ONLY SHOW IF QUALITY != goodCondition -->
                                <div class="container absolute flex flex-row items-center content-center top-16 left-0 text-md w-full py-2"
                                    x-show="selectedQuality !== 'goodCondition'">
                                    <label for="badItemQuantity" class="pr-2">Item count: </label>
                                    <input name="badItemQuantity" id="badItemQuantity" type="number" step="1" min="0" 
                                        :max="originalStock" class="text-sm w-16 px-2 py-1 border border-black"
                                        x-model="badItemCount"
                                        @input="updateStockBasedOnQuality()">
                                </div>
                            </div>

                            <x-form.form-input label="Expiration Date" name="productExpirationDate" type="date"
                                value="" 
                                min="{{ date('Y-m-d') }}"
                                class="col-span-2" x-bind:required="addMethod === 'po'"
                            />
                            <x-form.form-input label="Upload an image" name="productImage" type="file" value="" class="col-span-2" x-bind:required="addMethod === 'po'"/>
                        </div>
                    </section>

                    <!-- PREVIEW TABLE FOR ADDED PRODUCTS -->
                    <div class="border w-auto rounded-md border-solid border-black col-span-7">
                        <table class="w-full">
                            <thead class="rounded-lg bg-main text-white px-2 py-1">
                                <tr class="rounded-lg text-sm">
                                    <th class="bg-main px-2 py-2">Item/s</th>
                                    <th class="bg-main px-2 py-2">Quantity</th>
                                    <th class="bg-main px-2 py-2">Exp Date</th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                                <!-- Cart items will be added here dynamically -->
                                <tr id="emptyCartMessage">
                                    <td colspan="4" class="text-center py-4 text-gray-500">
                                        No items added yet. Add items above to preview the items.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- FORM BUTTONS -->
                    <div class="flex justify-end gap-4 mt-6">
                        <x-form.closeBtn type="button" @click="$refs.cancelAddProduct.showModal()">Cancel</x-form.closeBtn>
                        <x-form.saveBtn 
                            type="button" 
                            @click="
                                if (document.getElementById('addProductForm').reportValidity()) {
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
        @foreach($inventoryItems as $item)
            <x-modal.createModal x-ref="editProductDetails{{ $item->id }}">
                <x-slot:dialogTitle>Update {{ $item->productName }}</x-slot:dialogTitle>

                <div class="container px-3 py-4">
                    <form id="updateInventoryForm{{ $item->id }}" action="{{ route('inventory.update', $item->id) }}" method="POST" enctype="multipart/form-data"
                        class="px-6 py-4 container grid grid-cols-6 gap-x-8 gap-y-6"
                        x-data>
                        @csrf
                        @method('PUT')

                        <!-- Left Side (Image + Upload) -->
                        <div class="col-span-2 flex flex-col items-center gap-3">
                            @if($item->productImage)
                                <img src="{{ asset('storage/' . $item->productImage) }}" 
                                    alt="{{ $item->productName }}" 
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
                                value="{{ $item->productName }}" class="col-span-4" required />

                            <!-- Product Brand (full width) - FIXED: Dynamic from database -->
                            <div class="col-span-4 flex flex-col text-start">
                                <label for="productBrand" class="font-medium">Product Brand</label>
                                <select name="productBrand" id="productBrand" 
                                    class="px-3 py-2 border rounded border-gray-300 focus:ring focus:ring-blue-200" required>
                                    <option value="" disabled>Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->productBrand }}" {{ $item->productBrand == $brand->productBrand ? 'selected' : '' }}>
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
                                        <option value="{{ $category->productCategory }}" {{ $item->productCategory == $category->productCategory ? 'selected' : '' }}>
                                            {{ $category->productCategory }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Stock (half) -->
                            <x-form.form-input label="Stock" name="productStock" value="{{ $item->productStock }}" 
                                class="col-span-2" type="number" step="1" min="0" required />
                        </div>

                        <!-- Rest of fields below (full-width layout) -->
                        <x-form.form-input label="Selling Price (₱)" name="productSellingPrice" 
                            value="{{ $item->productSellingPrice }}" class="col-span-2" 
                            type="number" step="0.01" min="0" required />

                        <x-form.form-input label="Cost Price (₱)" name="productCostPrice" 
                            value="{{ $item->productCostPrice }}" class="col-span-2" 
                            type="number" step="0.01" min="0" required />

                        <div class="flex flex-col text-start col-span-3">
                            <label for="itemMeasurement" class="font-medium">Measurement per item</label>
                            <select name="productItemMeasurement" 
                                class="px-3 py-2 border rounded border-gray-300 focus:ring focus:ring-blue-200" required>
                                <option value="" disabled>Select Measurement</option>
                                <option value="kilogram" {{ $item->productItemMeasurement == 'kilogram' ? 'selected' : '' }}>kilogram (kg)</option>
                                <option value="gram" {{ $item->productItemMeasurement == 'gram' ? 'selected' : '' }}>gram (g)</option>
                                <option value="liter" {{ $item->productItemMeasurement == 'liter' ? 'selected' : '' }}>liter (L)</option>
                                <option value="milliliter" {{ $item->productItemMeasurement == 'milliliter' ? 'selected' : '' }}>milliliter (mL)</option>
                                <option value="pcs" {{ $item->productItemMeasurement == 'pcs' ? 'selected' : '' }}>pieces (pcs)</option>
                                <option value="set" {{ $item->productItemMeasurement == 'set' ? 'selected' : '' }}>set</option>
                                <option value="pair" {{ $item->productItemMeasurement == 'pair' ? 'selected' : '' }}>pair</option>
                                <option value="pack" {{ $item->productItemMeasurement == 'pack' ? 'selected' : '' }}>pack</option>
                            </select>
                        </div>

                        <x-form.form-input label="Expiration Date" name="productExpirationDate" type="date"
                            value="{{ $item->productExpirationDate }}" 
                            min="{{ date('Y-m-d') }}"
                            class="col-span-3" required
                        />

                        <!-- Footer Buttons -->
                        <div class="container col-span-6 gap-x-4 place-content-end w-full flex items-end content-center px-6 mt-4">
                            <button type="button" 
                                    @click="$refs['editProductDetails{{ $item->id }}'].close()" 
                                    class="mr-2 px-4 py-2 rounded bg-gray-400 hover:bg-gray-300 text-white duration-200 transition-all ease-in-out">
                                Cancel
                            </button>
                            <x-form.saveBtn @click="$refs['confirmEditProduct{{ $item->id }}'].showModal()" type="button">Update</x-form.saveBtn>
                        </div>
                    </form>
                </div>
            </x-modal.createModal>


            <!-- UPDATE CONFIRMATION MODAL -->
            <x-modal.createModal x-ref="confirmEditProduct{{ $item->id }}">
                <x-slot:dialogTitle>Confirm Changes?</x-slot:dialogTitle>
                <div class="container px-2 py-2">
                    <h1 class="py-6 px-5 text-xl">Are you sure you want to save these changes?</h1>
                    <div class="col-span-6 place-items-end flex justify-end gap-4">
                        <x-form.closeBtn @click="$refs.confirmEditProduct{{ $item->id }}.close()">Cancel</x-form.closeBtn>
                        <x-form.saveBtn type="submit" form="updateInventoryForm{{ $item->id }}">Save</x-form.saveBtn>
                    </div>
                </div>
            </x-modal.createModal>

            
        @endforeach



        <!-- DELETE CONFIRMATION MODAL -->
        @foreach ($inventoryItems as $item)
            <x-modal.createModal x-ref="confirmDeleteModal{{ $item->id }}" class="z-50">
                <x-slot:dialogTitle>Are you sure?</x-slot:dialogTitle>
                
                <div class="container px-2 py-2">
                    <h1 class="py-6 px-5 text-xl">
                        <p class="text-lg">Are you sure you want to delete <span class="font-bold">{{ $item->productName }}</span>?</p>
                        <p class="text-xs text-gray-600 mt-2">This action cannot be undone. All items associated with this product will also be deleted.</p>
                    </h1>
                    
                    <div class="flex justify-end gap-4">
                        <!-- CANCEL -->
                        <x-form.closeBtn 
                            @click="$refs['confirmDeleteModal{{ $item->id }}'].close()" 
                            type="button">
                            Cancel
                        </x-form.closeBtn>

                        <!-- DELETE FORM -->
                        <form action="{{ route('inventory.destroy', $item->id) }}" method="POST">
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
