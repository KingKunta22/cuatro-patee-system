<x-layout>
    <x-sidebar/>
    <div class="container w-auto ml-64 px-10 pt-6 pb-2 flex flex-col items-center content-start">
        <div x-data="{ close() { $refs.dialogRef.close() } }" class="container">
            <!-- SEARCH BAR AND CREATE BUTTON -->
            <div class="container flex items-center place-content-between">
                <!-- SEPARATE SEARCH/FILTER FORM - WON'T AFFECT OTHER FORMS -->
                <form action="{{ route('suppliers.index') }}" method="GET" id="statusFilterForm" class="mr-auto flex">
                    <!-- Simple Search Input -->
                    <div class="relative">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Search suppliers..." 
                            autocomplete="off"
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
                        <a href="{{ route('suppliers.index') }}" class="text-white mx-2 px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                            Clear
                        </a>
                    @endif
                </form>
                
                <!-- SHOW "ADD NEW SUPPLIER" BUTTON ONLY TO ADMINS -->
                @if(Auth::user()->role === 'admin')
                    <x-form.createBtn @click="$refs.dialogRef.showModal()">Add New Supplier</x-form.createBtn>
                @endif
            </div>
            
            <!-- ADD SUPPLIER MODAL (ONLY FOR ADMINS) -->
            @if(Auth::user()->role === 'admin')
            <dialog x-ref="dialogRef" class="w-1/2 my-auto shadow-2xl rounded-md">
                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Add Supplier</h1>
                <div class="container px-3 py-4">
                    <form action="{{ route('suppliers.store') }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                        @csrf
                        <x-form.form-input label="Supplier Name" name="supplierName" type="text" value="" />
                        <x-form.form-input label="Address" name="supplierAddress" type="text" value="" />
                        <x-form.form-input label="Contact Number" name="supplierContactNumber" type="text" value="" maxlength="11" pattern="[0-9]{11}" inputmode="numeric"/>
                        <x-form.form-input label="Email Address" name="supplierEmailAddress" type="email" value="" pattern="^[^@\s]+@[^@\s]+\.[^@\s]+$"/>
                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                            <x-form.closeBtn @click="close()">Cancel</x-form.closeBtn>
                            <x-form.saveBtn>Save</x-form.saveBtn>
                        </div>
                    </form>
                </div>
            </dialog>
            @endif
        </div>
        
        <!-- TABLE FOR SUPPLIER DETAILS -->
        <div class="border w-full rounded-md border-solid border-black my-5">
            <table class="w-full">
                <thead class="rounded-lg bg-main text-white px-4 py-3">
                    <tr class="rounded-lg">
                        <th class=" bg-main px-4 py-3">Supplier Name</th>
                        <th class=" bg-main px-4 py-3">Address</th>
                        <th class=" bg-main px-4 py-3">Contact Number</th>
                        <th class=" bg-main px-4 py-3">Email Address</th>
                        <th class=" bg-main px-4 py-3">Status</th>
                        @if(Auth::user()->role === 'admin')
                            <th class=" bg-main px-4 py-3">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
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
                            
                            <!-- ACTION BUTTONS - ONLY SHOW FOR ADMINS -->
                            @if(Auth::user()->role === 'admin')
                                <td class="truncate py-3 max-w-32 px-2 flex place-content-center">
                                    <x-form.editBtn @click="$refs['editDialog{{ $supplier->id }}'].showModal()" />
                                    <dialog x-ref="editDialog{{ $supplier->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                        <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Edit Supplier</h1>
                                        <div class="container px-3 py-4">
                                            <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                                @csrf
                                                @method('PUT')
                                                <x-form.form-input label="Supplier Name" name="supplierName" type="text" value="{{ old('supplierName',  $supplier->supplierName) }}"/>
                                                <x-form.form-input label="Address" name="supplierAddress" type="text" value="{{ old('supplierAddress', $supplier->supplierAddress) }}"/>
                                                <x-form.form-input label="Contact Number" name="supplierContactNumber" type="text" value="{{ old('supplierContactNumber', $supplier->supplierContactNumber) }}" maxlength="11" pattern="[0-9]{11}" inputmode="numeric"/>
                                                <x-form.form-input label="Email Address" name="supplierEmailAddress" type="email" value="{{ old('supplierEmailAddress', $supplier->supplierEmailAddress) }}" pattern="^[^@\s]+@[^@\s]+\.[^@\s]+$"/>
                                                <div class="container text-start flex col-span-2 flex-col">
                                                    <label for="supplierStatus">Choose status:</label>
                                                    <select name="supplierStatus" id="supplierStatus" class="px-3 py-2 border rounded-sm border-black">
                                                        <option value="Active" {{ $supplier->supplierStatus === 'Active' ? 'Selected' : '' }}>Active</option>
                                                        <option value="Inactive" {{ $supplier->supplierStatus === 'Inactive' ? 'Selected' : '' }}>Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                                    <x-form.closeBtn @click="closeEdit()">Cancel</x-form.closeBtn>
                                                    <x-form.saveBtn>Edit</x-form.saveBtn>
                                                </div>
                                            </form>
                                        </div>
                                    </dialog>
                                    
                                    <x-form.deleteBtn @click="$refs['deleteDialog{{ $supplier->id }}'].showModal()" />
                                    <dialog x-ref="deleteDialog{{ $supplier->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                        <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Delete Supplier?</h1>
                                        <div class="container px-3 py-4">
                                            <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                                @csrf
                                                @method('DELETE')
                                                <div>
                                                    <p class="text-lg">Are you sure you want to delete this supplier?</p>
                                                    <p class="text-xs text-gray-600 mt-2">This action cannot be undone. All items associated with this supplier will also be deleted.</p>
                                                </div>
                                                <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                                    <x-form.closeBtn type="button" @click="closeDelete()">Cancel</x-form.closeBtn>
                                                    <x-form.saveBtn>Delete</x-form.saveBtn>
                                                </div>
                                            </form>
                                        </div>
                                    </dialog>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4 px-4 py-2 bg-gray-50 ">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
</x-layout>