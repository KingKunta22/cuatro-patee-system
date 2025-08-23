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
                <form action="{{ route('sales.store') }}" method="POST" id="addSales" 
                    class="px-6 py-4 container grid grid-cols-7 gap-x-8 gap-y-6">
                    @csrf
                    
                    <!-- Product Search (Custom Dropdown with AlpineJS) -->
                    <div 
                        x-data="{
                            open: false,
                            search: '',
                            products: {{ Js::from($inventories) }},
                            filtered() {
                                return this.products.filter(p => 
                                    p.productName.toLowerCase().includes(this.search.toLowerCase()) ||
                                    p.productSKU.toLowerCase().includes(this.search.toLowerCase())
                                )
                            },
                            select(product) {
                                this.search = product.productName + ' (' + product.productSKU + ')'
                                this.open = false

                                // Fill hidden inputs
                                document.getElementById('selectedInventoryId').value = product.id
                                document.querySelector('[name=productSKU]').value = product.productSKU
                                document.querySelector('[name=productBrand]').value = product.productBrand
                                document.querySelector('[name=itemMeasurement]').value = product.productItemMeasurement
                                document.querySelector('[name=availableStocks]').value = product.productStock
                                document.querySelector('[name=salesAmountToPay]').setAttribute('data-base-price', product.productSellingPrice)
                                document.querySelector('[name=salesAmountToPay]').value = parseFloat(product.productSellingPrice).toFixed(2)
                                document.querySelector('[name=quantity]').setAttribute('max', product.productStock)
                            }
                        }"
                        class="relative w-full col-span-4"
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
                            class="px-4 py-3 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm"
                            autocomplete="off"
                            required
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
                                                <span class="bg-gray-100 px-2 py-1 rounded text-xs" x-text="product.productSKU"></span>
                                                <span class="mx-2">•</span>
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
                        <input type="hidden" id="selectedInventoryId" name="inventory_id">
                    </div>
                    
                    <!-- Customer Dropdown -->
                    <div class="container text-start flex col-span-3 w-full flex-col">
                        <label for="customerName">Customer Name</label>
                        <select name="customerName" class="px-3 py-2 border rounded-sm border-black" required>
                            <option value="" disabled selected>Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->customerName }}">{{ $customer->customerName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Other inputs (unchanged) -->
                    <x-form.form-input label="Product Brand" name="productBrand" type="text" class="col-span-2" readonly/>
                    <x-form.form-input label="Product SKU" name="productSKU" type="text" class="col-span-2" readonly/>
                    <x-form.form-input label="Stocks" name="availableStocks" type="number" class="col-span-1" readonly/>
                    <x-form.form-input label="Measurement" name="itemMeasurement" type="text" class="col-span-2" readonly/>
                    <x-form.form-input label="Quantity" name="quantity" type="number" value="1" min="1" class="col-span-1" required oninput="calculateAmount()"/>
                    <x-form.form-input label="Cash on Hand (₱)" name="salesCash" type="number" step="0.01" value="" class="col-span-2" required oninput="calculateChange()"/>
                    <x-form.form-input label="Amount to Pay (₱)" name="salesAmountToPay" type="number" step="0.01" value="0.00" class="col-span-2" readonly/>
                    <x-form.form-input label="Change (₱)" name="salesChange" type="number" step="0.01" value="0.00" class="col-span-2" readonly/>

                    
                    <!-- Hidden fields for sale items -->
                    <div id="saleItemsContainer" class="hidden"></div>
    
                    <div class="px-5 py-2 w-full font-bold border border-black text-2xl uppercase col-span-4 flex flex-row items-between place-content-between">
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
                    <div class="border w-auto rounded-md border-solid border-black my-2 col-span-7">
                        <table class="w-full">
                            <thead class="rounded-lg bg-main text-white px-4 py-2">
                                <tr class="rounded-lg text-md">
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

        <!-- JavaScript for product selection and form handling -->
        <script>
            
            // Function to calculate amount to pay
            function calculateAmount() {
                const quantity = parseFloat(document.querySelector('[name="quantity"]').value) || 0;
                const price = parseFloat(document.querySelector('[name="salesAmountToPay"]').getAttribute('data-base-price')) || 0;
                const amount = quantity * price;
                document.querySelector('[name="salesAmountToPay"]').value = amount.toFixed(2);
                calculateChange();
            }
            
            // Function to calculate change
            function calculateChange() {
                const amount = parseFloat(document.querySelector('[name="salesAmountToPay"]').value) || 0;
                const cash = parseFloat(document.querySelector('[name="salesCash"]').value) || 0;
                const change = cash - amount;
                document.querySelector('[name="salesChange"]').value = change.toFixed(2);
            }
            
            // Cart array to store added items
            let cart = [];
            let cartTotal = 0;
            
            // Function to add product to cart
            function addToCart() {
                const inventoryId = document.getElementById('selectedInventoryId').value;
                const productName = document.getElementById('productName').value;
                const quantity = parseFloat(document.querySelector('[name="quantity"]').value) || 0;
                const price = parseFloat(document.querySelector('[name="salesAmountToPay"]').getAttribute('data-base-price')) || 0;
                const availableStocks = parseFloat(document.querySelector('[name="availableStocks"]').value) || 0;
                
                if (!inventoryId || quantity <= 0) {
                    alert('Please select a product and enter a valid quantity');
                    return;
                }
                
                if (quantity > availableStocks) {
                    alert(`Cannot add more than available stock (${availableStocks})`);
                    return;
                }
                
                // Check if product already exists in cart
                const existingItemIndex = cart.findIndex(item => item.inventory_id === inventoryId);
                
                if (existingItemIndex >= 0) {
                    // Update existing item
                    const newQuantity = cart[existingItemIndex].quantity + quantity;
                    if (newQuantity > availableStocks) {
                        alert(`Total quantity cannot exceed available stock (${availableStocks})`);
                        return;
                    }
                    cart[existingItemIndex].quantity = newQuantity;
                    cart[existingItemIndex].total = newQuantity * price;
                } else {
                    // Add new item to cart
                    const itemTotal = price * quantity;
                    cart.push({
                        inventory_id: inventoryId,
                        name: productName,
                        quantity: quantity,
                        price: price,
                        total: itemTotal
                    });
                }
                
                // Update cart total
                cartTotal = cart.reduce((sum, item) => sum + item.total, 0);
                document.getElementById('cartTotal').textContent = '₱' + cartTotal.toFixed(2);
                
                // Update cart display
                updateCartDisplay();
                
                // Update hidden form fields for sale items
                updateSaleItemsForm();
                
                // Reset product selection fields
                document.getElementById('productName').value = '';
                document.getElementById('selectedInventoryId').value = '';
                document.querySelector('[name="productSKU"]').value = '';
                document.querySelector('[name="productBrand"]').value = '';
                document.querySelector('[name="itemMeasurement"]').value = '';
                document.querySelector('[name="availableStocks"]').value = '';
                document.querySelector('[name="quantity"]').value = '1';
                document.querySelector('[name="quantity"]').removeAttribute('max');
                document.querySelector('[name="salesAmountToPay"]').value = '0.00';
                document.querySelector('[name="salesAmountToPay"]').removeAttribute('data-base-price');
                calculateAmount();
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
                        <td class="px-2 py-2 text-center">₱${item.total.toFixed(2)}</td>
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
                
                cart.forEach((item, index) => {
                    const inventoryIdInput = document.createElement('input');
                    inventoryIdInput.type = 'hidden';
                    inventoryIdInput.name = `items[${index}][inventory_id]`;
                    inventoryIdInput.value = item.inventory_id;
                    
                    const quantityInput = document.createElement('input');
                    quantityInput.type = 'hidden';
                    quantityInput.name = `items[${index}][quantity]`;
                    quantityInput.value = item.quantity;
                    
                    const priceInput = document.createElement('input');
                    priceInput.type = 'hidden';
                    priceInput.name = `items[${index}][price]`;
                    priceInput.value = item.price;
                    
                    container.appendChild(inventoryIdInput);
                    container.appendChild(quantityInput);
                    container.appendChild(priceInput);
                });
            }
            
            // Function to remove item from cart
            function removeFromCart(index) {
                // Subtract from total
                cartTotal -= cart[index].total;
                document.getElementById('cartTotal').textContent = '₱' + cartTotal.toFixed(2);
                
                // Remove from cart
                cart.splice(index, 1);
                
                // Update display
                updateCartDisplay();
                updateSaleItemsForm();
            }
            
            // Function to reset the form
            function resetForm() {
                document.getElementById('addSales').reset();
                document.querySelector('[name="salesAmountToPay"]').removeAttribute('data-base-price');
                document.getElementById('selectedInventoryId').value = '';
                document.querySelector('[name="quantity"]').removeAttribute('max');
                
                // Reset cart
                cart = [];
                cartTotal = 0;
                document.getElementById('cartTotal').textContent = '₱0.00';
                updateCartDisplay();
                updateSaleItemsForm();
            }
            
            // Add event listeners when DOM is loaded
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelector('[name="quantity"]').addEventListener('input', calculateAmount);
                document.querySelector('[name="salesCash"]').addEventListener('input', calculateChange);
            });
        </script>

    </main>
</x-layout>