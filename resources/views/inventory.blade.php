<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 py-8 flex flex-col items-center content-start">

        <!-- CONTAINER OUTSIDE THE TABLE -->
        <section class="container flex items-center place-content-start">
            <!-- SEARCH BAR AND CREATE BUTTON -->
            <div class="container flex items-center place-content-start">
                <x-searchBar placeholder="Search inventory..." />
                <form action="{{ route('purchase-orders.index') }}" method="GET" id="statusFilterForm" class="mr-auto ml-4">
                    <select name="status" class="truncate w-36 px-3 py-2 border rounded-md border-black" onchange="document.getElementById('statusFilterForm').submit()">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Category</option>
                        <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Confirmed" {{ request('status') === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="Delivered" {{ request('status') === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <select name="status" class="truncate w-36 px-3 py-2 border rounded-md border-black" onchange="document.getElementById('statusFilterForm').submit()">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Brand</option>
                        <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Confirmed" {{ request('status') === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="Delivered" {{ request('status') === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </form>
                <x-form.createBtn @click="$refs.addProductRef.showModal()">Add New Product</x-form.createBtn>
            </div>
        </section>

        <!-- CONTAINER FOR TABLE DETAILS -->
        <section class="border w-full rounded-md border-solid border-black p-3 my-8">
            <table class="w-full">
                <thead class="rounded-lg bg-main text-white px-4 py-2">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-2">Product Name</th>
                        <th class=" bg-main px-4 py-2">Category</th>
                        <th class=" bg-main px-4 py-2">SKU</th>
                        <th class=" bg-main px-4 py-2">Brand</th>
                        <th class=" bg-main px-4 py-2">Price</th>
                        <th class=" bg-main px-4 py-2">Stock</th>
                        <th class=" bg-main px-4 py-2">Status</th>
                        <th class=" bg-main px-4 py-2">Expiration Date</th>
                        <th class=" bg-main px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="truncate px-2 py-2 text-center" title="">ProductName</td>
                        <td class="truncate px-2 py-2 text-center" title="">Category</td>
                        <td class="truncate px-2 py-2 text-center" title="">SKU</td>
                        <td class="truncate px-2 py-2 text-center" title="">Brand</td>
                        <td class="truncate px-2 py-2 text-center" title="">Price</td>
                        <td class="truncate px-2 py-2 text-center" title="">Stock</td>
                        <td class="truncate px-2 py-2 text-center" title="">Status</td>
                        <td class="truncate px-2 py-2 text-center" title="">ExpirationDate</td>
                        <td class="truncate px-2 py-2 text-center" title="">Action</td>
                    </tr>
                </tbody>
            </table>

            <!-- PAGINATION VIEW -->
            {{--                 <div class="mt-4 px-4 py-2 bg-gray-50 ">
                {{ $purchaseOrders->links() }}
            </div> --}}

        </section>


        <!-- ============================================ -->
        <!----------------- MODALS SECTION ----------------->
        <!-- ============================================ -->

        <!-- MODAL FOR ADDING PRODUCT -->
        <x-modal.createModal x-ref="addProductRef">
            <x-slot:dialogTitle>Add Product</x-slot:dialogTitle>

            <!-- RADIO OPTION TO CHOOSE BETWEEN ADD MANUALLY (DEFAULT) OR REFERENCED -->
            <div x-data="{ addMethod: 'manual' }" class="grid grid-cols-6 px-3 py-4">
                <div class="container mb-4 col-span-6 font-semibold">
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
                <form id="addProductForm" x-ref="addProductForm" action="{{ route('inventory.store')}}" method="POST" class="grid grid-cols-6 col-span-6 gap-x-8 gap-y-6" enctype="multipart/form-data">
                    @csrf

                    <!-- MANUAL SECTION -->
                    <section class="grid grid-cols-6 col-span-6 justify-end gap-4" 
                            x-show="addMethod === 'manual'"
                            x-data="{
                            sellingPrice: 0,
                            costPrice: 0,
                            profitMargin: 0,
                            calculateProfitMargin() {
                                if (this.costPrice > 0 && this.sellingPrice > 0) {
                                    this.profitMargin = ((this.sellingPrice - this.costPrice) / this.costPrice * 100).toFixed(2) + '%';
                                } else {
                                    this.profitMargin = 0;
                                }
                            },
                        }" >
                        <x-form.form-input label="Product Name" name="productName" type="text" value="" class="col-span-3" x-bind:required="addMethod === 'manual'"/>
                        <x-form.form-input label="SKU" name="productSKU" type="text" value="{{ $newSKU }}" class="col-span-3" readonly/>
                        <div class='container flex flex-col text-start col-span-3'>
                            <label for="productBrand">Product Brand</label>
                            <select name="productBrand" id="productBrand" class="px-3 py-2 border rounded-sm border-black" x-bind:required="addMethod === 'manual'">
                                <option value="" disabled selected>Select Brand</option>
                                <option value="Pedigree">Pedigree</option>
                                <option value="Whiskas">Whiskas</option>
                                <option value="Royal Canin">Royal Canin</option>
                                <option value="Cesar">Cesar</option>
                                <option value="Acana">Acana</option>
                            </select>
                        </div>
                        <div class="container flex flex-col text-start col-span-2">
                            <label for="productCategory">Product Category</label>
                            <select name="productCategory" id="productCategory" class="px-3 py-2 border rounded-sm border-black" x-bind:required="addMethod === 'manual'">
                                <option value="" disabled selected>Select Category</option>
                                <option value="Dog Food (Dry)">Dog Food (Dry)</option>
                                <option value="Dog Food (Wet)">Dog Food (Wet)</option>
                                <option value="Cat Food (Dry)">Cat Food (Dry)</option>
                                <option value="Cat Food (Wet)">Cat Food (Wet)</option>
                                <option value="Dog Toy">Dog Toy</option>
                            </select>
                        </div>
                        
                        <x-form.form-input label="Stock" name="productStock" type="number" value="" class="col-span-1/2" x-bind:required="addMethod === 'manual'"/>

                        <x-form.form-input label="Selling Price (₱)" name="productSellingPrice" type="number" value="" class="col-span-2" 
                                            x-bind:required="addMethod === 'manual'"
                                            x-model="sellingPrice"
                                            @input="calculateProfitMargin()"/>

                        <x-form.form-input label="Cost Price (₱)" name="productCostPrice" type="number" value="" class="col-span-2" 
                                            x-bind:required="addMethod === 'manual'"
                                            x-model="costPrice"
                                            @input="calculateProfitMargin()"/>

                        <x-form.form-input label="Profit Margin (%)" name="productProfitMargin" type="text" value="" 
                                            class="col-span-2" 
                                            readonly
                                            x-model="profitMargin"/>
                        
                        <div class="container text-start flex col-span-2 w-full flex-col">
                            <label for="itemMeasurement">Measurement per item</label>
                            <select name="itemMeasurement" class="px-3 py-2 border rounded-sm border-black" x-bind:required="addMethod === 'manual'">
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

                        <x-form.form-input label="Expiration Date" name="productExpDate" type="date"
                            value="" 
                            min="{{ date('Y-m-d') }}"
                            class="col-span-2" x-bind:required="addMethod === 'manual'"
                        />
                        <x-form.form-input label="Upload an image" name="productImage" type="file" value="" class="col-span-2" :required="false"/>
                    </section>

                    <!-- PURCHASE ORDER SECTION -->
                    <section class="grid grid-cols-6 col-span-6 gap-4" 
                            x-show="addMethod === 'po'"
                            x-data="{
                                items: [],
                                poId: null,
                                sellingPrice: 0,
                                costPrice: 0,
                                profitMargin: '0%',
                                productName: '',
                                productStock: 0,
                                itemMeasurement: '',
                                selectedItemId: null,
                                
                                async getItems(poId) {
                                    this.poId = poId;
                                    this.items = [];
                                    this.selectedItemId = null;
                                    this.costPrice = 0;
                                    
                                    if (!poId) return;
                                    
                                    const response = await fetch(`/get-items/${poId}`);
                                    this.items = await response.json();
                                    
                                    // AUTO-SELECT if only 1 item exists
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
                                        this.calculateProfitMargin();
                                        this.productName = item.productName;
                                        this.productStock = item.quantity;
                                        this.itemMeasurement = item.itemMeasurement;
                                    }
                                },
                                
                                calculateProfitMargin() {
                                    if (this.costPrice > 0 && this.sellingPrice > 0) {
                                        const margin = ((this.sellingPrice - this.costPrice) / this.costPrice * 100);
                                        this.profitMargin = margin.toFixed(2) + '%';
                                    } else {
                                        this.profitMargin = '0%';
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
                                @foreach($deliveredPOs as $po)
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
                                                
                            <x-form.form-input label="SKU" name="productSKU" type="text" value="{{ $newSKU }}" class="col-span-3" readonly/>

                            <div class='container flex flex-col text-start col-span-3'>
                                <label for="productBrand">Product Brand</label>
                                <select name="productBrand" id="productBrand" class="px-3 py-2 border rounded-sm border-black" x-bind:required="addMethod === 'po'">
                                    <option value="" disabled selected>Select Brand</option>
                                    <option value="Pedigree">Pedigree</option>
                                    <option value="Whiskas">Whiskas</option>
                                    <option value="Royal Canin">Royal Canin</option>
                                    <option value="Cesar">Cesar</option>
                                    <option value="Acana">Acana</option>
                                </select>
                            </div>
                            <div class="container flex flex-col text-start col-span-2">
                                <label for="productCategory">Product Category</label>
                                <select name="productCategory" id="productCategory" class="px-3 py-2 border rounded-sm border-black" x-bind:required="addMethod === 'po'">
                                    <option value="" disabled selected>Select Category</option>
                                    <option value="Dog Food (Dry)">Dog Food (Dry)</option>
                                    <option value="Dog Food (Wet)">Dog Food (Wet)</option>
                                    <option value="Cat Food (Dry)">Cat Food (Dry)</option>
                                    <option value="Cat Food (Wet)">Cat Food (Wet)</option>
                                    <option value="Dog Toy">Dog Toy</option>
                                </select>
                            </div>
                            
                            <x-form.form-input label="Stock" type="number" value="" 
                                                class="col-span-1/2" 
                                                name="productStock" 
                                                x-model="productStock"
                                                x-bind:required="addMethod === 'po'"/>

                            <x-form.form-input label="Selling Price (₱)" name="productSellingPrice" type="number" value="" class="col-span-2" 
                                                x-bind:required="addMethod === 'po'"
                                                x-model="sellingPrice"
                                                @input="calculateProfitMargin()"/>

                            <x-form.form-input label="Cost Price (₱)" name="productCostPrice" type="number" value="" 
                                                class="col-span-2" readonly
                                                x-model="costPrice"/>

                            <x-form.form-input label="Profit Margin (%)" name="productProfitMargin" type="text" value="" 
                                                class="col-span-2" readonly
                                                x-model="profitMargin"/>
                            
                            <div class="container text-start flex col-span-2 w-full flex-col">
                                <label for="itemMeasurement">Measurement per item</label>
                                <select class="px-3 py-2 border rounded-sm border-black" 
                                name="itemMeasurement" 
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

                            <x-form.form-input label="Expiration Date" name="productExpDate" type="date"
                                value="" 
                                min="{{ date('Y-m-d') }}"
                                class="col-span-2" x-bind:required="addMethod === 'po'"
                            />
                            <x-form.form-input label="Upload an image" name="productImage" type="file" value="" class="col-span-2" :required="false"/>
                        </div>
                    </section>

                    <!-- FORM BUTTONS -->
                    <div class="col-span-6 place-items-end flex justify-end gap-4">
                        <x-form.closeBtn type="button" @click="$refs.cancelAddProduct.showModal()">Cancel</x-form.closeBtn>
                        <x-form.saveBtn 
                            type="button" 
                            @click="
                                if (document.getElementById('addProductForm').reportValidity()) {
                                    ($refs.confirmAddProduct || document.getElementById('confirmAddProduct')).showModal();
                                }" >Add</x-form.saveBtn>
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
    </main>
</x-layout>
