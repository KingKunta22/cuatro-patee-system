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

            <x-modal.createModal x-ref="addProductRef">
                <x-slot:dialogTitle >Add Product</x-slot:dialogTitle>

                <!-- ============ RADIO OPTION TO CHOOSE BETWEEN ADD MANUALLY (DEFAULT) OR REFERENCED ==========-->
                <div x-data="{ addMethod: 'manual' }">
                    <div class="container mb-4r">
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
                    <form action="" method="POST">
                        @csrf
                        <!-- MANUAL SECTION -->
                        <section class="px-6 py-2" x-show=" addMethod === 'manual' ">
                            <!-- Manual fields go here -->
                            
                        </section>

                        <!-- FROM PURCHASE ORDER SECTION -->
                        <section class="px-6 py-2" x-show=" addMethod === 'po' ">
                            <!-- From PO fields go here -->
                            <div class="container">
                                Purchase Order Number
                                <select name="" id="">
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="container">
                                Purchase Order Item
                                <select name="" id="">
                                    <option value=""></option>
                                </select>
                            </div>
                        </section>

                        <!-- FORM BUTTONS -->
                        <div>
                            <x-form.closeBtn/>
                            <x-form.saveBtn/>
                        </div>
                    </form>
                </div>
            </x-modal.createModal>


        </main>
</x-layout>
