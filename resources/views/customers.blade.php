<x-layout>
    <x-sidebar/>
    <div class="container w-auto ml-64 px-10 pt-6 pb-2 flex flex-col items-center content-start">
        <div x-data="{ close() { $refs.dialogRef.close() } }" class="container">
            <!-- SEARCH BAR AND CREATE BUTTON -->
            <div class="container flex items-center place-content-between">
                                    <!-- SEPARATE SEARCH/FILTER FORM - WON'T AFFECT OTHER FORMS -->
                    <form action="{{ route('customers.index') }}" method="GET" id="statusFilterForm" class="mr-auto flex">

                        <!-- Simple Search Input -->
                        <div class="relative">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}"
                                placeholder="Search customers..." 
                                class="pl-10 pr-4 py-2 border border-black rounded-md w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Search Button -->
                        <button type="submit" class="px-4 py-2 ml-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            Search
                        </button>

                        <!-- Clear Button (only show when filters are active) -->
                        @if(request('search') )
                            <a href="{{ route('customers.index') }}" class="text-white mx-2 px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                                Clear
                            </a>
                        @endif
                    </form>
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
            <div class="border w-full rounded-md border-solid border-black my-5">
                <table class="w-full">
                <thead class="rounded-lg bg-main text-white px-4 py-3">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-3">Customer Name</th>
                        <th class=" bg-main px-4 py-3">Address</th>
                        <th class=" bg-main px-4 py-3">Contact Number</th>
                        <th class=" bg-main px-4 py-3">Email Address</th>
                        <th class=" bg-main px-4 py-3">Status</th>
                        <th class=" bg-main px-4 py-3">Action</th>
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
