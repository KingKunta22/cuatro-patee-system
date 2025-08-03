<x-layout>
    <x-sidebar/>
    <div class="container w-auto ml-64 px-10 py-8 flex flex-col items-center content-start">
        <div x-data="{ close() { $refs.dialogRef.close() } }" class="container">
            <!-- SEARCH BAR AND CREATE BUTTON -->
            <div class="container flex items-center place-content-between">
                <div class="container flex items-center place-content-between">
                    <x-searchBar placeholder="Search purchase orders..." />
                    <x-createBtn @click="$refs.dialogRef.showModal()">Add New Order</x-createBtn>
                </div>
            </div>
            <!--Modal Form -->
            <dialog x-ref="dialogRef" class="w-1/2 my-auto shadow-2xl rounded-md">
                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Add Order</h1>
                <div class="container px-3 py-4">
                    <form action="" method="POST" class="px-6 py-4 container grid grid-cols-4 gap-x-8 gap-y-6">
                        @csrf
                        <div class="container text-start flex col-span-2 flex-col">
                            <label for="purchaseOrderSupplier">Supplier</label>
                            <select name="purchaseOrderSupplier" class="px-3 py-2 border rounded-sm border-black">
                                <option value="Insert Supplier Name">Insert Supplier Name</option>
                            </select>
                        </div>
                        <x-form-input label="Product Name" name="purchaseOrderProductName" type="text" class="col-span-2" value=""/>
                        <div class="container text-start flex col-span-2 w-full flex-col">
                            <label for="purchaseOrderPaymentTerms">Payment Terms</label>
                            <select name="purchaseOrderPaymentTerms" class="px-3 py-2 border rounded-sm border-black">
                                <option value="Insert Payment Terms">Insert Payment Terms</option>
                            </select>
                        </div>
                        <x-form-input label="Unit Price" name="purchaseOrderUnitPrice" type="number" value=""/>
                        <x-form-input label="Quantity" name="purchaseOrderQuantity" type="number" value=""/>
                        <x-form-input label="Expected Delivery Date" name="purchaseOrderQuantity" type="date" value=""/>
                        <x-form-input label="Total" name="purchaseOrderTotal" type="number" value=""/>
                        <x-closeBtn>Add</x-closeBtn>
                        <!-- INSERT ADDED ORDERS HERE -->
                        <div class="border w-auto rounded-md border-solid col-span-4 border-black p-3 my-4">
                            <table class="w-full">
                                <thead class="rounded-lg bg-main text-white px-4 py-2">
                                    <tr class="rounded-lg">
                                        <th class="bg-main px-2 text-sm">Items</th>
                                        <th class="bg-main px-2 text-sm">Quantity</th>
                                        <th class="bg-main px-2 text-sm">Price</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <!-- FORM BUTTONS -->
                        <div class="container col-span-4 gap-x-4 place-content-end w-full flex items-end content-center">
                            <x-closeBtn @click="close()">Cancel</x-closeBtn>
                            <x-saveBtn>Save</x-saveBtn>
                        </div>
                    </form>
                </div>
            </dialog>
        </div>
        <!-- TABLE FOR SUPPLIER DETAILS -->
        <div class="border w-full rounded-md border-solid border-black p-3 my-8">
            <table class="w-full">
                <thead class="rounded-lg bg-main text-white px-4 py-2">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-2">Order Number</th>
                        <th class=" bg-main px-4 py-2">Supplier Name</th>
                        <th class=" bg-main px-4 py-2">Date</th>
                        <th class=" bg-main px-4 py-2">Items</th>
                        <th class=" bg-main px-4 py-2">Total</th>
                        <th class=" bg-main px-4 py-2">Expected Delivery Date</th>
                        <th class=" bg-main px-4 py-2">Status</th>
                        <th class=" bg-main px-4 py-2">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</x-layout>
