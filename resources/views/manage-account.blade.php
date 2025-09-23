<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 pt-6 pb-3 flex flex-col items-center content-start">
        <div class="container">
            <!-- SEARCH BAR AND CREATE BUTTON -->
            <div class="container flex items-center place-content-end">
                <x-form.createBtn @click="$refs.dialogRef.showModal()">Add New User</x-form.createBtn>
            </div>

        </div>
        <!-- TABLE FOR ACCOUNT DETAILS -->
        <div class="border w-full rounded-md border-solid border-black my-5">
            <table class="w-full">
            <thead class="rounded-lg bg-main text-white px-4 py-3">
                <tr class="rounded-lg">
                    <th class=" bg-main px-4 py-3">Username</th>
                    <th class=" bg-main px-4 py-3">First Name</th>
                    <th class=" bg-main px-4 py-3">Role</th>
                    <th class=" bg-main px-4 py-3">Date Joined</th>
                    <th class=" bg-main px-4 py-3">Status</th>
                    <th class=" bg-main px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr class=" text-center border-b-2">
                    <td class="truncate py-3 max-w-32 px-2" title=""></td>
                    <td class="truncate py-3 max-w-32 px-2" title=""></td>
                    <td class="truncate py-3 max-w-32 px-2" title=""></td>
                    <td class="truncate py-3 max-w-32 px-2" title=""></td>
                    <td class="truncate py-3 max-w-32 px-2">
                        <span class="text-red-500 bg-red-100 px-2 py-1 rounded-full text-sm font-semibold">
                            Inactive
                        </span>
                    </td>
                    <!-- UPDATE FORM -->
                    <td class="truncate py-3 max-w-32 px-2 flex place-content-center">
                        <x-form.editBtn @click="" />
                        <dialog x-ref="" class="w-1/2 my-auto shadow-2xl rounded-md">
                            <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Update Account</h1>
                            <div class="container px-3 py-4">
                                <form action="" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                    @csrf
                                    @method('PUT')
                                    <x-form.form-input label="Supplier Name" name="s" type="text" value=""/>
                                    <x-form.form-input label="Address" name="" type="text" value=""/>
                                    <x-form.form-input label="Contact Number" name="" type="number" value="" maxlength="11" type="tel" pattern="[0-9]{11}"/>
                                    <x-form.form-input label="Email Address" name="" type="email" value=""/>
                                    <div class="container text-start flex col-span-2 flex-col">
                                        <label for="">Choose status:</label>
                                        <select name="" id="" class="px-3 py-2 border rounded-sm border-black">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                        <x-form.closeBtn @click="">Cancel</x-form.closeBtn>
                                        <x-form.saveBtn>Update</x-form.saveBtn>
                                    </div>
                                </form>
                            </div>
                        </dialog>
                        <!-- DELETE FORM -->
                        <x-form.deleteBtn @click="" />
                        <dialog x-ref="" class="w-1/2 my-auto shadow-2xl rounded-md">
                            <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Delete Account?</h1>
                            <div class="container px-3 py-4">
                                <form action="" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                    @csrf
                                    @method('DELETE')
                                    <div>
                                        <p class="text-lg">Are you sure you want to delete this supplier?</p>
                                        <p class="text-xs text-gray-600 mt-2">This action cannot be undone. All items associated with this supplier will also be deleted.</p>
                                    </div>
                                    <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                        <x-form.closeBtn type="button" @click="">Cancel</x-form.closeBtn>
                                        <x-form.saveBtn>Delete</x-form.saveBtn>
                                    </div>
                                </form>
                            </div>
                        </dialog>
                    </td>
                </tr>
            </tbody>
        </table>
        {{-- <div class="mt-4 px-4 py-2 bg-gray-50 ">
            {{ $suppliers->links() }}
        </div> --}}

        <!--Modal Form -->
        <dialog x-ref="dialogRef" class="w-1/2 my-auto shadow-2xl rounded-md">
            <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Add Account</h1>
            <div class="container px-3 py-4">
                <form action="" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                    @csrf
                    <x-form.form-input label="Username" name="" type="text" value="" />
                    <div class="container text-start flex col-span-1 flex-col">
                        <label for="">Choose role:</label>
                        <select name="" id="" class="px-3 py-2 border rounded-sm border-black">
                            <option value="" disabled>Choose Role</option>
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>
                        </select>
                    </div>
                    <x-form.form-input label="First Name" name="" type="text" value=""/>
                    <x-form.form-input label="Last Name" name="" type="text" value="" />
                    <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                        <x-form.closeBtn @click="">Cancel</x-form.closeBtn>
                        <x-form.saveBtn>Save</x-form.saveBtn>
                    </div>
                </form>
            </div>
        </dialog>
    </main>
</x-layout>
