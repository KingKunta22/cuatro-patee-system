<x-layout>
    <x-sidebar/>
    <div class="container w-auto ml-64 px-10 py-8 flex flex-col items-center content-start">
        <div x-data="{ close() { $refs.dialogRef.close() } }" class="container">
            <!-- SEARCH BAR AND CREATE BUTTON -->
            <div class="container flex items-center place-content-between">
                    <div class="relative w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <!-- Search SVG Icon from Heroicons -->
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" placeholder="Search suppliers..." class="pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-main focus:border-transparent w-full" id="searchInput">
                </div>
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
                        <x-form-input label="Supplier Name" name="supplierName" type="text" />
                        <x-form-input label="Address" name="supplierAddress" type="text" />
                        <x-form-input label="Contact Number" name="supplierContactNumber" type="number" class=""/>
                        <x-form-input label="Email Address" name="supplierEmailAddress" type="email" />
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
                <tr clsas="rounded-lg">
                    <th class=" bg-main px-4 py-2">Supplier Name</th>
                    <th class=" bg-main px-4 py-2">Address</th>
                    <th class=" bg-main px-4 py-2">Contact Number</th>
                    <th class=" bg-main px-4 py-2">Email Address</th>
                    <th class=" bg-main px-4 py-2">Status</th>
                    <th class=" bg-main px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody class="text-center">
                @foreach($suppliers as $supplier)
                    <tr class="border-b-2">
                        <td class="truncate py-3 max-w-32 px-2" title="{{ $supplier->supplierName }}">{{ $supplier->supplierName }}</td>
                        <td class="truncate py-3 max-w-32 px-2" title="{{ $supplier->supplierAddress }}">{{ $supplier->supplierAddress }}</td>
                        <td class="truncate py-3 max-w-32 px-2" title="{{ $supplier->supplierContactNumber }}">{{ $supplier->supplierContactNumber }}</td>
                        <td class="truncate py-3 max-w-32 px-2" title="{{ $supplier->supplierEmailAddress }}">{{ $supplier->supplierEmailAddress }}</td>
                        <td class="truncate py-3 max-w-32 px-2">
                            {{ $supplier->supplierStatus ?? 'Unset' }}
                        </td>
                        <!-- UPDATE FORM -->
                        <td class="truncate py-3 max-w-32 px-2 flex place-content-center" x-data="{ close() { $refs.dialogRef.close() } }">
                            <x-editBtn @click="$refs.dialogRef.showModal()" />
                            <dialog x-ref="dialogRef" class="w-1/2 my-auto shadow-2xl rounded-md">
                                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Update Supplier</h1>
                                <div class="container px-3 py-4">
                                    <!-- This will not route us into /suppliers, instead, 
                                    this will call the store method inside the controller-->
                                    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                        @csrf
                                        @method('PUT')
                                        <x-form-input label="Supplier Name" name="supplierName" type="text" value="{{ $supplier->supplierName }}"/>
                                        <x-form-input label="Address" name="supplierAddress" type="text" value="{{ $supplier->supplierAddress }}"/>
                                        <x-form-input label="Contact Number" name="supplierContactNumber" type="number" value="{{ $supplier->supplierContactNumber }}"/>
                                        <x-form-input label="Email Address" name="supplierEmailAddress" type="email" value="{{ $supplier->supplierEmailAddress }}"/>
                                        <div class="container text-start flex col-span-2 flex-col">
                                            <label for="status">Choose status:</label>
                                            <select name="supplierStatus" id="status" class="px-3 py-2 border rounded-sm border-black">
                                                <option value="active" {{ $supplier->supplierStatus === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $supplier->supplierStatus === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                            <x-closeBtn @click="close()">Cancel</x-closeBtn>
                                            <x-saveBtn>Update</x-saveBtn>
                                        </div>
                                    </form>
                                </div>
                            </dialog>
                            <!-- DELETE FORM -->
                            <x-deleteBtn></x-deleteBtn>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</x-layout>
