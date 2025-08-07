<x-layout>
    <x-sidebar/>
    <div class="container w-auto ml-64 px-10 py-8 flex flex-col items-center content-start">
        <div x-data="{ close() { $refs.dialogRef.close() } }" class="container">
            <!-- SEARCH BAR AND CREATE BUTTON -->
            <div class="container flex items-center place-content-between">
                <x-searchBar placeholder="Search suppliers..." />
                <x-createBtn @click="$refs.dialogRef.showModal()">Create New</x-createBtn>
            </div>
            <!--Modal Form -->
            <dialog x-ref="dialogRef" class="w-1/2 my-auto shadow-2xl rounded-md">
                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Add Supplier</h1>
                <div class="container px-3 py-4">
                    <!-- This will not route us into /suppliers, instead, 
                    this will call the store method inside the controller-->
                    <form action="{{ route('suppliers.store') }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                        @csrf
                        <x-form-input label="Supplier Name" name="supplierName" type="text" value="" />
                        <x-form-input label="Address" name="supplierAddress" type="text" value="" />
                        <x-form-input label="Contact Number" name="supplierContactNumber" type="number" value="" maxlength="11" type="tel" pattern="[0-9]{11}"/>
                        <x-form-input label="Email Address" name="supplierEmailAddress" type="email" value="" />
                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
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
                    <th class=" bg-main px-4 py-2">Supplier Name</th>
                    <th class=" bg-main px-4 py-2">Address</th>
                    <th class=" bg-main px-4 py-2">Contact Number</th>
                    <th class=" bg-main px-4 py-2">Email Address</th>
                    <th class=" bg-main px-4 py-2">Status</th>
                    <th class=" bg-main px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- FOR EACH LOOP TO LOOP THROUGH SUPPLIERS FROM INDEX -->
                @foreach($suppliers as $supplier)
                    <tr class=" text-center border-b-2" x-data="{ 
                        closeEdit() { $refs['editDialog{{ $supplier->id }}'].close() }, 
                        closeDelete() { $refs['deleteDialog{{ $supplier->id }}'].close() } }">
                        <td class="truncate py-3 max-w-32 px-2" title="{{ $supplier->supplierName }}">{{ $supplier->supplierName }}</td>
                        <td class="truncate py-3 max-w-32 px-2" title="{{ $supplier->supplierAddress }}">{{ $supplier->supplierAddress }}</td>
                        <td class="truncate py-3 max-w-32 px-2" title="{{ $supplier->supplierContactNumber }}">{{ $supplier->supplierContactNumber }}</td>
                        <td class="truncate py-3 max-w-32 px-2" title="{{ $supplier->supplierEmailAddress }}">{{ $supplier->supplierEmailAddress }}</td>
                        <td class="truncate py-3 max-w-32 px-2">
                            <span class="{{ $supplier->supplierStatus === 'Active' ? 'text-green-500 bg-green-100 px-2 py-1 rounded-full text-sm font-semibold' : 'text-red-500 bg-red-100 px-2 py-1 rounded-full text-sm font-semibold' }}">
                                {{ $supplier->supplierStatus ?? 'Unset' }}
                            </span>
                        </td>
                        <!-- UPDATE FORM -->
                        <td class="truncate py-3 max-w-32 px-2 flex place-content-center">
                            <x-editBtn @click="$refs['editDialog{{ $supplier->id }}'].showModal()" />
                            <dialog x-ref="editDialog{{ $supplier->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Update Supplier</h1>
                                <div class="container px-3 py-4">
                                    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                        @csrf
                                        @method('PUT')
                                        <x-form-input label="Supplier Name" name="supplierName" type="text" value="{{ old('supplierName',  $supplier->supplierName) }}"/>
                                        <x-form-input label="Address" name="supplierAddress" type="text" value="{{ old('supplierAddress', $supplier->supplierAddress) }}"/>
                                        <x-form-input label="Contact Number" name="supplierContactNumber" type="number" value="{{ old('supplierContactNumber', $supplier->supplierContactNumber) }}" maxlength="11" type="tel" pattern="[0-9]{11}"/>
                                        <x-form-input label="Email Address" name="supplierEmailAddress" type="email" value="{{ old('supplierEmailAddress', $supplier->supplierEmailAddress) }}"/>
                                        <div class="container text-start flex col-span-2 flex-col">
                                            <label for="supplierStatus">Choose status:</label>
                                            <select name="supplierStatus" id="supplierStatus" class="px-3 py-2 border rounded-sm border-black">
                                                <option value="Active" {{ $supplier->supplierStatus === 'Active' ? 'Selected' : '' }}>Active</option>
                                                <option value="Inactive" {{ $supplier->supplierStatus === 'Inactive' ? 'Selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                            <x-closeBtn @click="closeEdit()">Cancel</x-closeBtn>
                                            <x-saveBtn>Update</x-saveBtn>
                                        </div>
                                    </form>
                                </div>
                            </dialog>
                            <!-- DELETE FORM -->
                            <x-deleteBtn @click="$refs['deleteDialog{{ $supplier->id }}'].showModal()" />
                            <dialog x-ref="deleteDialog{{ $supplier->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Delete Supplier?</h1>
                                <div class="container px-3 py-4">
                                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                        @csrf
                                        @method('DELETE')
                                        <div>
                                            <h1>Are you sure you want to delete this supplier?</h1>
                                        </div>
                                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                            <x-closeBtn type="button" @click="closeDelete()">Cancel</x-closeBtn>
                                            <x-saveBtn>Delete</x-saveBtn>
                                        </div>
                                    </form>
                                </div>
                            </dialog>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</x-layout>
