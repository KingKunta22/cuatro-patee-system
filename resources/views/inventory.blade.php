<x-layout>
    <x-sidebar/>
        <main x-data="{ close() 
        { $refs.addProductOptionRef.close() } 
        { $refs.addManualProductRef.close() } 
        { $refs.addReferencedProductRef.close() } 
         }" class="container w-auto ml-64 px-10 py-8 flex flex-col items-center content-start">

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
                    <x-form.createBtn @click="$refs.addProductOptionRef.showModal()">Add New Product</x-form.createBtn>
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

             <!-- ============ MODAL OPTION TO CHOOSE ADD MANUALLY OR REFERENCED ==========-->
            <x-modal.createModal x-ref="addProductOptionRef">
                <x-slot:dialogTitle >How do you wanna add this product?</x-slot:dialogTitle>
                <div class="container flex flex-row space-between items-center w-full text-center place-content-center">
                    <button type="submit" @click="$refs.addManualProductRef.showModal()" class="px-6 py-3 uppercase mx-2 font-semibold bg-main text-white rounded hover:bg-main/60 transition-all duration-100 ease-in-out">Add Manually</button>
                    <button type="submit" @click="$refs.addReferencedProductRef.showModal()" class="px-6 py-3 uppercase mx-2 font-semibold bg-main text-white rounded hover:bg-main/60 transition-all duration-100 ease-in-out">Reference from PO Number</button>
                </div>
            </x-modal.createModal>


            <!-- ============ MODAL FOR ADDING PRODUCT MANUALLY ==========-->
            <x-modal.createModal x-ref="addManualProductRef">
                <x-slot:dialogTitle>Add Product</x-slot:dialogTitle>

                <form action="{{ route('inventory.store') }}" method="POST" class="grid grid-cols-6 w-full gap-x-8 gap-y-6 px-6 py-4">
                    @csrf
                    <x-form.form-input label="Product Name" name="productName" type="text" value="" class=" col-span-3 "/>
                    <x-form.form-input label="SKU" name="productSKU" type="text" value="" class="col-span-3 "/>
                    <div class='container flex flex-col text-start col-span-3'>
                        <label for="productBrand">Product Brand</label>
                        <select name="productBrand" id="productBrand" class="px-3 py-2 border rounded-sm border-black" required>
                            <option value="Pedigree">Pedigree</option>
                            <option value="Whiskas">Whiskas</option>
                            <option value="Royal Canin">Royal Canin</option>
                            <option value="Cesar">Cesar</option>
                            <option value="Acana">Acana</option>
                        </select>
                    </div>
                    <div class="container flex flex-col text-start col-span-2">
                        <label for="productCategory">Product Category</label>
                        <select name="productCategory" id="productCategory" class="px-3 py-2 border rounded-sm border-black" required>
                            <option value="dogFoodDry">Dog Food (Dry)</option>
                            <option value="dogFoodWet">Dog Food (Wet)</option>
                            <option value="catFoodDry">Cat Food (Dry)</option>
                            <option value="catFoodWet">Cat Food (Wet)</option>
                            <option value="dogToy">Dog Toy</option>
                        </select>
                    </div>
                    <x-form.form-input label="Stock" name="productStock" type="number" value="" class="col-span-1/2"/>
                    
                    <x-form.form-input label="Selling Price (₱)" name="productSellingPrice" type="number" value="" step="0.01" class="col-span-2"/>
                    <x-form.form-input label="Cost Price (₱)" name="productCostPrice" type="number" value="" step="0.01" class="col-span-2"/>
                    <x-form.form-input label="Profit Margin" readonly name="productProfitMargin" type="text" value="(Insert PM Automated)%" class="col-span-2 "/>
                    <x-form.form-input label="Expiration Date" min="{{ date('Y-m-d') }}" name="productExpDate" type="date" value="" class="col-span-2 "/>

                    <x-form.form-input label="Upload an image from your computer" name="productImage" type="file" value="" class="col-span-4"/>

                    <!-- FORM BUTTONS -->
                    <div class="container col-span-6 gap-x-2 place-content-end w-full flex items-end content-center">
                        <button @click='$refs.addManualProductRef.close()' type="button" class="flex place-content-center rounded-md bg-button-delete px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in">
                            Cancel
                        </button>
                        <x-form.saveBtn>Save</x-form.saveBtn>
                    </div>
                </form>

            </x-modal.createModal>


            <!-- ============ MODAL FOR ADDING PRODUCT WITH REFERENCE ==========-->
            <x-modal.createModal x-ref="addReferencedProductRef">
                <x-slot:dialogTitle>Add Product</x-slot:dialogTitle>

                <form action="{{ route('inventory.store') }}" method="POST" class="grid grid-cols-6 w-full gap-x-8 gap-y-6 px-6 py-4">
                    @csrf
                    <x-form.form-input label="Product Name" name="productName" type="text" value="" class=" col-span-3 "/>
                    <x-form.form-input label="SKU" name="productSKU" type="text" value="" class="col-span-3 "/>
                    <div class='container flex flex-col text-start col-span-3'>
                        <label for="productBrand">Product Brand</label>
                        <select name="productBrand" id="productBrand" class="px-3 py-2 border rounded-sm border-black" required>
                            <option value="Pedigree">Pedigree</option>
                            <option value="Whiskas">Whiskas</option>
                            <option value="Royal Canin">Royal Canin</option>
                            <option value="Cesar">Cesar</option>
                            <option value="Acana">Acana</option>
                        </select>
                    </div>
                    <div class="container flex flex-col text-start col-span-2">
                        <label for="productCategory">Product Category</label>
                        <select name="productCategory" id="productCategory" class="px-3 py-2 border rounded-sm border-black" required>
                            <option value="dogFoodDry">Dog Food (Dry)</option>
                            <option value="dogFoodWet">Dog Food (Wet)</option>
                            <option value="catFoodDry">Cat Food (Dry)</option>
                            <option value="catFoodWet">Cat Food (Wet)</option>
                            <option value="dogToy">Dog Toy</option>
                        </select>
                    </div>
                    <x-form.form-input label="Stock" name="productStock" type="number" value="" class="col-span-1/2"/>
                    
                    <x-form.form-input label="Selling Price (₱)" name="productSellingPrice" type="number" value="" step="0.01" class="col-span-2"/>
                    <x-form.form-input label="Cost Price (₱)" name="productCostPrice" type="number" value="" step="0.01" class="col-span-2"/>
                    <x-form.form-input label="Profit Margin" readonly name="productProfitMargin" type="text" value="(Insert PM Automated)%" class="col-span-2 "/>
                    <x-form.form-input label="Expiration Date" min="{{ date('Y-m-d') }}" name="productExpDate" type="date" value="" class="col-span-2 "/>

                    <x-form.form-input label="Upload an image from your computer" name="productImage" type="file" value="" class="col-span-4"/>

                    <!-- FORM BUTTONS -->
                    <div class="container col-span-6 gap-x-2 place-content-end w-full flex items-end content-center">
                        <button @click='$refs.addReferencedProductRef.close()' type="button" class="flex place-content-center rounded-md bg-button-delete px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in">
                            Cancel
                        </button>
                        <x-form.saveBtn>Save</x-form.saveBtn>
                    </div>
                </form>

            </x-modal.createModal>




        </main>
</x-layout>
