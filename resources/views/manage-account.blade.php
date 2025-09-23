<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 pt-6 pb-3 flex flex-col items-center content-start">
        <div class="container">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

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
                        <th class="bg-main px-4 py-3">Username</th>
                        <th class="bg-main px-4 py-3">Email</th>
                        <th class="bg-main px-4 py-3">Role</th>
                        <th class="bg-main px-4 py-3">Date Joined</th>
                        <th class="bg-main px-4 py-3">Status</th>
                        <th class="bg-main px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="text-center border-b-2">
                        <td class="truncate py-3 max-w-32 px-2 flex flex-row items-center mx-auto content-center place-content-center">
                            <img src="{{ Auth::user()->getAvatarUrl() }}" alt="Avatar" class="w-6 h-6 mr-1 rounded-full">
                            <span>{{ $user->name }}</span>
                        </td>
                        <td class="truncate py-3 max-w-32 px-2">{{ $user->email }}</td>
                        <td class="truncate py-3 max-w-32 px-2 capitalize">{{ $user->role }}</td>
                        <td class="truncate py-3 max-w-32 px-2">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="truncate py-3 max-w-32 px-2">
                            <span class="{{ $user->status === 'active' ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100' }} px-2 py-1 rounded-full text-sm font-semibold capitalize">
                                {{ $user->status }}
                            </span>
                        </td>
                        <td class="truncate py-3 max-w-32 px-2 flex place-content-center gap-2">
                            <!-- UPDATE BUTTON -->
                            <x-form.editBtn @click="document.getElementById('editDialog{{ $user->id }}').showModal()" />
                            <dialog id="editDialog{{ $user->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Update Account</h1>
                                <div class="container px-3 py-4">
                                    <form action="{{ route('users.update', $user) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                        @csrf
                                        @method('PUT')
                                        <x-form.form-input label="Username" name="name" type="text" value="{{ $user->name }}"/>
                                        <x-form.form-input label="Email Address" name="email" type="email" value="{{ $user->email }}"/>
                                        <x-form.form-input label="New Password (leave blank to keep current)" name="password" type="password"/>
                                        <x-form.form-input label="Confirm Password" name="password_confirmation" type="password"/>
                                        <div class="container text-start flex flex-col">
                                            <label for="role">Role:</label>
                                            <select name="role" class="px-3 py-2 border rounded-sm border-black">
                                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>Staff</option>
                                            </select>
                                        </div>
                                        <div class="container text-start flex flex-col">
                                            <label for="status">Status:</label>
                                            <select name="status" class="px-3 py-2 border rounded-sm border-black">
                                                <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                            <x-form.closeBtn type="button" @click="document.getElementById('editDialog{{ $user->id }}').close()">Cancel</x-form.closeBtn>
                                            <x-form.saveBtn>Update</x-form.saveBtn>
                                        </div>
                                    </form>
                                </div>
                            </dialog>

                            <!-- DELETE BUTTON -->
                            <x-form.deleteBtn @click="document.getElementById('deleteDialog{{ $user->id }}').showModal()" />
                            <dialog id="deleteDialog{{ $user->id }}" class="w-1/2 my-auto shadow-2xl rounded-md">
                                <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Delete Account?</h1>
                                <div class="container px-3 py-4">
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                                        @csrf
                                        @method('DELETE')
                                        <div class="col-span-2">
                                            <p class="text-lg">Are you sure you want to delete {{ $user->name }}?</p>
                                            <p class="text-xs text-gray-600 mt-2">This action cannot be undone.</p>
                                        </div>
                                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                            <x-form.closeBtn type="button" @click="document.getElementById('deleteDialog{{ $user->id }}').close()">Cancel</x-form.closeBtn>
                                            <x-form.saveBtn class="bg-red-600 hover:bg-red-700">Delete</x-form.saveBtn>
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

        <!-- ADD USER MODAL -->
        <dialog x-ref="dialogRef" class="w-1/2 my-auto shadow-2xl rounded-md">
            <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Add Account</h1>
            <div class="container px-3 py-4">
                <form action="{{ route('users.store') }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6">
                    @csrf
                    <x-form.form-input label="Username" name="name" type="text" required />
                    <x-form.form-input label="Email" name="email" type="email" required />
                    <x-form.form-input label="Password" name="password" type="password" required />
                    <x-form.form-input label="Confirm Password" name="password_confirmation" type="password" required />
                    <div class="container text-start flex flex-col">
                        <label for="role">Role:</label>
                        <select name="role" class="px-3 py-2 border rounded-sm border-black" required>
                            <option value="" disabled selected>Choose Role</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                        <x-form.closeBtn type="button" @click="$refs.dialogRef.close()">Cancel</x-form.closeBtn>
                        <x-form.saveBtn>Save</x-form.saveBtn>
                    </div>
                </form>
            </div>
        </dialog>
    </main>
</x-layout>