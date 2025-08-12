<x-layout>
    <x-sidebar/>
    <div class="container w-auto ml-64 px-10 py-8 flex flex-col items-center content-start">
        <div x-data="{ close() { $refs.dialogRef.close() } }" class="container">
            <!-- SEARCH BAR AND CREATE BUTTON -->
            <div class="container flex items-center place-content-between">
                <x-searchBar placeholder="Search customer..." />
                <x-form.createBtn @click="$refs.dialogRef.showModal()">Add New Customer</x-form.createBtn>
            </div>
            <!--Modal Form -->
            <dialog x-ref="dialogRef" class="w-1/2 my-auto shadow-2xl rounded-md">
                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Add Customer</h1>
                <div class="container px-3 py-4">
                    <form action="{{ route('customers.store') }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                        @csrf
                        <x-form.form-input label="Customer Name" name="customerName" type="text" value="" />
                        <x-form.form-input label="Address" name="customerAddress" type="text" value="" />
                        <x-form.form-input label="Contact Number" name="customerContactNumber" type="number" value="" maxlength="11" type="tel" pattern="[0-9]{11}" />
                        <x-form.form-input label="Email Address" name="customerEmailAddress" type="email" value="" />
                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                            <x-form.closeBtn @click="close()">Cancel</x-form.closeBtn>
                            <x-form.saveBtn>Save</x-form.saveBtn>
                        </div>
                    </form>
                </div>
            </dialog>
            <!-- TABLE FOR CUSTOMER DETAILS -->
            <div class="border w-full rounded-md border-solid border-black p-3 my-8">
                <table class="w-full">
                <thead class="rounded-lg bg-main text-white px-4 py-2">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-2">Customer Name</th>
                        <th class=" bg-main px-4 py-2">Address</th>
                        <th class=" bg-main px-4 py-2">Contact Number</th>
                        <th class=" bg-main px-4 py-2">Email Address</th>
                        <th class=" bg-main px-4 py-2">Status</th>
                        <th class=" bg-main px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- FOR EACH LOOP TO LOOP THROUGH CUSTOMERS FROM INDEX -->
                    @foreach($customers as $customer)
                        <tr class=" text-center border-b-2" x-data="{ 
                            closeEdit() { $refs['editDialog{{ $customer->id }}'].close() }, 
                            closeDelete() { $refs['deleteDialog{{ $customer->id }}'].close() } }">
                            <td class="truncate py-3 max-w-32 px-2" title="{{ $customer->customerName }}">{{ $customer->customerName }}</td>
                            <td class="truncate py-3 max-w-32 px-2" title="{{ $customer->customerAddress }}">{{ $customer->customerAddress }}</td>
                            <td class="truncate py-3 max-w-32 px-2" title="{{ $customer->customerContactNumber }}">{{ $customer->customerContactNumber }}</td>
                            <td class="truncate py-3 max-w-32 px-2" title="{{ $customer->customerEmailAddress }}">{{ $customer->customerEmailAddress }}</td>
                            <td class="truncate py-3 max-w-32 px-2">
                                <span class="{{ $customer->customerStatus === 'Active' ? 'text-green-500 bg-green-100 px-2 py-1 rounded-full text-sm font-semibold' : 'text-red-500 bg-red-100 px-2 py-1 rounded-full text-sm font-semibold' }}">
                                    {{ $customer->customerStatus }}
                                </span>
                            </td>
                            <!-- UPDATE FORM -->
                            <td class="truncate py-3 max-w-32 px-2 flex place-content-center">
                                <x-form.editBtn @click="$refs['editDialog{{ $customer->id }}'].showModal()" />
                                <dialog x-ref="editDialog{{ $customer->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                    <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Update Customer</h1>
                                    <div class="container px-3 py-4">
                                        <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                            @csrf
                                            @method('PUT')
                                            <x-form.form-input label="Customer Name" name="customerName" type="text" value="{{ old('customerName',  $customer->customerName) }}"/>
                                            <x-form.form-input label="Address" name="customerAddress" type="text" value="{{ old('customerAddress', $customer->customerAddress) }}"/>
                                            <x-form.form-input label="Contact Number" name="customerContactNumber" type="number" value="{{ old('customerContactNumber', $customer->customerContactNumber) }}" maxlength="11" type="tel" pattern="[0-9]{11}"/>
                                            <x-form.form-input label="Email Address" name="customerEmailAddress" type="email" value="{{ old('customerEmailAddress', $customer->customerEmailAddress) }}"/>
                                            <div class="container text-start flex col-span-2 flex-col">
                                                <label for="customerStatus">Choose status:</label>
                                                <select name="customerStatus" id="customerStatus" class="px-3 py-2 border rounded-sm border-black">
                                                    <option value="Active" {{ $customer->customerStatus === 'Active' ? 'Selected' : '' }}>Active</option>
                                                    <option value="Inactive" {{ $customer->customerStatus === 'Inactive' ? 'Selected' : '' }}>Inactive</option>
                                                </select>
                                            </div>
                                            <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                                <x-form.closeBtn @click="closeEdit()">Cancel</x-form.closeBtn>
                                                <x-form.saveBtn>Update</x-form.saveBtn>
                                            </div>
                                        </form>
                                    </div>
                                </dialog>
                                <!-- DELETE FORM -->
                                <x-form.deleteBtn @click="$refs['deleteDialog{{ $customer->id }}'].showModal()" />
                                <dialog x-ref="deleteDialog{{ $customer->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                    <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Delete Customer?</h1>
                                    <div class="container px-3 py-4">
                                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                            @csrf
                                            @method('DELETE')
                                            <div>
                                                <h1>Are you sure you want to delete this customer?</h1>
                                            </div>
                                            <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                                <x-form.closeBtn type="button" @click="closeDelete()">Cancel</x-form.closeBtn>
                                                <x-form.saveBtn>Delete</x-form.saveBtn>
                                            </div>
                                        </form>
                                    </div>
                                </dialog>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 px-4 py-2 bg-gray-50 ">
                {{ $customers->links() }}
            </div>
        </div>
</x-layout>
