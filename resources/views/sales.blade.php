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

        <!-- CONTAINER OUTSIDE THE TABLE -->
        <section class="container flex flex-col items-center place-content-start">
            <div class="container flex items-start justify-start place-content-start w-auto gap-x-4 text-white mr-auto mb-4">
                <div class="container flex flex-col px-6 py-3 w-64 text-start rounded-md bg-[#5C717B]">
                    <span class="font-semibold text-xl">₱(InsertRevenue)</span>
                    <span class="text-xs">Total Revenue</span>
                </div>
                <div class="container flex flex-col px-6 py-3 w-64 text-start rounded-md bg-[#2C3747]">
                    <span class="font-semibold text-xl">₱(InsertProfit)</span>
                    <span class="text-xs">Total Profit</span>
                </div>
               <div class="container flex flex-col px-6 py-3 w-64 text-start rounded-md bg-[#5C717B]">
                    <span class="font-semibold text-xl">₱(InsertCost)</span>
                    <span class="text-xs">Total Cost</span>
                </div>
            </div>

            <!-- SEARCH BAR AND FILTERS - SEPARATE FORM TO AVOID CONFLICTS -->
            <div class="container flex items-center place-content-start gap-4 mb-4">
                <!-- SEPARATE SEARCH/FILTER FORM - WON'T AFFECT OTHER FORMS -->
                <form action="{{ route('sales.index') }}" method="GET" class="flex items-center gap-4 mr-auto">
                    <!-- Simple Search Input -->
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

                    <!-- Search Button -->
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        Search
                    </button>

                    <!-- Clear Button (only show when filters are active) -->
                    @if(request('search'))
                        <a href="{{ route('sales.index') }}" class="text-white px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                            Clear
                        </a>
                    @endif
                </form>

                <!-- Your existing create button - SEPARATE FROM THE FILTER FORM -->
                <x-form.createBtn @click="$refs.addSalesRef.showModal()">Add New Sale</x-form.createBtn>
            </div>
        </section>

        <!-- CONTAINER FOR TABLE DETAILS -->
        <section class="border w-full rounded-md border-solid border-black my-3">
            <table class="w-full">
                <thead class="rounded-lg bg-main text-white px-4 py-3">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-3">Invoice Number</th>
                        <th class=" bg-main px-4 py-3">Date</th>
                        <th class=" bg-main px-4 py-3">Customer Name</th>
                        <th class=" bg-main px-4 py-3">Amount</th>
                        <th class=" bg-main px-4 py-3">Quantity</th>
                        <th class=" bg-main px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="truncate px-2 py-2 text-center" title="">INVOICE NUM</td>
                        <td class="truncate px-2 py-2 text-center" title="">DATE</td>
                        <td class="truncate px-2 py-2 text-center" title="">CUSTOMER NAME</td>
                        <td class="truncate px-2 py-2 text-center" title="">AMOUNT</td>
                        <td class="truncate px-2 py-2 text-center" title="">QUANTITY</td>
                        <td class="truncate px-2 py-2 text-center" title="">ACTION</td>
                    </tr>
                </tbody>
            </table>

            <!-- PAGINATION VIEW -->
            {{-- <div class="mt-4 px-4 py-2 bg-gray-50"> --}}
            {{--    {{ $inventoryItems->appends(request()->except('page'))->links() }} --}}
            {{-- </div> --}}

        </section>

        <!-- ============================================ -->
        <!----------------- MODALS SECTION ----------------->
        <!-- ============================================ -->

        <!-- ADD SALES MODAL -->
        <x-modal.createModal x-ref="addSalesRef">
            <x-slot:dialogTitle>Add Sale</x-slot:dialogTitle>
            <div class="container">
                <!-- ADD ORDER FORM -->
                <form action="" method="POST" class="px-6 py-4 container grid grid-cols-7 gap-x-8 gap-y-6">
                    @csrf
                    
                    <x-form.form-input label="Invoice Number" name="invoiceNumber" type="text" class="col-span-3" />
                    
                    <x-form.form-input label="Date" name="salesDate" type="date" class="col-span-2" value="" required/>

                    <x-form.form-input label="Product SKU" name="productSKU" type="text" class="col-span-2" value="" readonly/>

                    <div class="container text-start flex col-span-3 w-full flex-col">
                        <label for="productName">Product Name</label>
                        <select name="productName" class="px-3 py-2 border rounded-sm border-black" required>
                            <option value="" disabled selected>Select Product</option>
                            <option value="" >Online</option>
                        </select>
                    </div>

                    <div class="container text-start flex col-span-3 w-full flex-col">
                        <label for="customerName">Customer Name</label>
                        <select name="customerName" class="px-3 py-2 border rounded-sm border-black" required>
                            <option value="" disabled selected>Select Customer</option>
                            <option value="" >Online</option>
                        </select>
                    </div>

                    <x-form.form-input label="Quantity" name="quantity" type="number" value="" class="col-span-1" required/>

                    <x-form.form-input label="Cash on Hand (₱)" name="salesCash" type="number" step="0.01" value="" class="col-span-2" required />

                    <x-form.form-input label="Change (₱)" name="salesChange" type="number" step="0.01" value="" class="col-span-2" required />

                    <x-form.form-input label="Amount to Pay (₱)" name="salesAmountToPay" type="number" step="0.01" value="" class="col-span-2" readonly/>

                    <!-- ADD BUTTON FOR ADDING ITEMS TO SESSION -->
                    <div class="flex items-end content-center place-content-center w-full col-span-1">
                        <button type="button" class= 'bg-teal-500/70 px-3 py-2 rounded text-white hover:bg-teal-500 w-full'>
                            Add
                        </button>
                    </div>

                    <!-- PREVIEW TABLE FOR ADDED SALES/PRODUCTS -->
                    @if(session('purchase_order_items'))
                        <div class="border w-auto rounded-md border-solid border-black p-3 my-4 col-span-7">
                            <table class="w-full">
                                <thead class="rounded-lg bg-main text-white px-4 py-2">
                                    <tr class="rounded-lg">
                                        <th class="bg-main px-2 py-2 text-sm">Item/s</th>
                                        <th class="bg-main px-2 py-2 text-sm">Quantity</th>
                                        <th class="bg-main px-2 py-2 text-sm">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b">
                                        <td class="px-2 py-2 text-center">{{ $item['productName'] }}</td>
                                        <td class="px-2 py-2 text-center">{{ $item['quantity'] }}</td>
                                        <td class="px-2 py-2 text-center">₱{{ number_format($item['unitPrice'], 2) }}</td>d>
                                        <td class="px-2 py-2 text-center">
                                        <form action="" method="POST" class="flex place-content-center">
                                            @csrf
                                            @method('DELETE')
                                            <button>
                                                <x-form.deleteBtn />
                                            </button>
                                        </form>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- EMPTY STATE -->
                        <div class="border w-auto rounded-md border-solid border-black p-3 my-4 col-span-7">
                            <div class="text-center py-8 text-gray-500">
                                <p>No items added yet. Add items above to preview your order.</p>
                            </div>
                        </div>
                    @endif


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
                
                        <x-form.saveBtn type="button" @click="$refs.confirmSubmit.showModal()">Save</x-form.saveBtn>


                        <!-- CONFIRM CANCEL/SAVE MODALS -->
                        <x-modal.createModal x-ref="confirmSalesCancel">

                            <x-slot:dialogTitle>Confirm Cancel?</x-slot:dialogTitle>

                            <h1 class="text-xl px-4 py-3">Are you sure you want to cancel this sale?</h1>
                            <div class="container flex w-full flex-row items-center content-end place-content-end px-4 py-3">
                                <button type="button" @click="$refs.confirmSalesCancel.close()" class="mr-3 flex place-content-center rounded-md bg-button-delete px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in">
                                    Cancel
                                </button>
                                <x-form.saveBtn type="button" @click="$refs.confirmSalesCancel.close(); $refs.addSalesRef.resetForm()">Confirm</x-form.saveBtn>
                            </div>

                        </x-modal.createModal>

                        <x-modal.createModal x-ref="confirmSubmit">

                            <x-slot:dialogTitle>Confirm Save?</x-slot:dialogTitle>

                            <h1 class="text-xl px-4 py-3">Are you sure you want to save this sale?</h1>
                            <div class="container flex w-full flex-row items-center content-end place-content-end px-4 py-3">
                                <button type="button" @click="$refs.confirmSubmit.close()" class="mr-3 flex place-content-center rounded-md bg-button-delete px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in">
                                    Cancel
                                </button>
                                <x-form.saveBtn type="submit" form="saveForm">Confirm</x-form.saveBtn>
                            </div>

                        </x-modal.createModal>

                    </div>

                </form>
            </div>
        </x-modal.createModal>

    </main>
</x-layout>
