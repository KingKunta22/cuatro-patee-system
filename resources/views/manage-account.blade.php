<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 pt-6 pb-3 flex flex-col items-center content-start">
        <div class="container">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 rounded shadow-lg">
                    {{ session('error') }}
                </div>
            @endif
        
            <!-- AUTO HIDE MESSAGES SCRIPT -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Auto-hide ANY fixed toast messages (success or error)
                    document.querySelectorAll('.fixed.top-20').forEach(toast => {
                        setTimeout(() => {
                            toast.style.transition = 'all 0.5s ease-out';
                            toast.style.opacity = '0';
                            toast.style.transform = 'translate(-50%, -20px)';
                            setTimeout(() => toast.remove(), 500);
                        }, 3000);
                    });
                });
            </script>

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
                                    <form action="{{ route('users.update', $user) }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6" data-user-id="{{ $user->id }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="modal_id" value="editDialog{{ $user->id }}">
                                        
                                        <div class="container text-start flex flex-col">
                                            <label for="edit_username_{{ $user->id }}" class="mb-1">Username</label>
                                            <input id="edit_username_{{ $user->id }}" readonly name="name" type="text" class="px-3 py-2 border rounded-sm border-black @error('name') border-red-500 @enderror" value="{{ old('name', $user->name) }}" data-original-value="{{ $user->name }}">
                                            <span class="text-red-500 text-sm mt-1 hidden error-username"></span>
                                            @error('name')
                                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="container text-start flex flex-col">
                                            <label for="edit_email_{{ $user->id }}" class="mb-1">Email Address</label>
                                            <input id="edit_email_{{ $user->id }}" name="email" type="email" class="px-3 py-2 border rounded-sm border-black @error('email') border-red-500 @enderror" value="{{ old('email', $user->email) }}" data-original-value="{{ $user->email }}">
                                            <span class="text-red-500 text-sm mt-1 hidden error-email"></span>
                                            @error('email')
                                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        
                                        <!-- Password Field -->
                                        <div class="container text-start flex flex-col">
                                            <label for="password_{{ $user->id }}" class="mb-1">New Password (leave blank to keep current)</label>
                                            <input id="password_{{ $user->id }}" type="password" name="password" class="px-3 py-2 border rounded-sm border-black @error('password') border-red-500 @enderror" value="{{ old('password') }}">
                                            <span class="text-red-500 text-sm mt-1 hidden error-password"></span>
                                            @error('password')
                                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        
                                        <!-- Password Confirmation Field -->
                                        <div class="container text-start flex flex-col">
                                            <label for="password_confirmation_{{ $user->id }}" class="mb-1">Confirm Password</label>
                                            <input id="password_confirmation_{{ $user->id }}" type="password" name="password_confirmation" class="px-3 py-2 border rounded-sm border-black @error('password') border-red-500 @enderror">
                                            <span class="text-red-500 text-sm mt-1 hidden error-password-confirm"></span>
                                        </div>
                                        
                                        <div class="container text-start flex flex-col">
                                            <label for="role_{{ $user->id }}">Role:</label>
                                            <select name="role" class="px-3 py-2 border rounded-sm border-black">
                                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                                            </select>
                                            @error('role')
                                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        
                                        <div class="container text-start flex flex-col">
                                            <label for="status_{{ $user->id }}">Status:</label>
                                            <select name="status" class="px-3 py-2 border rounded-sm border-black">
                                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            @error('status')
                                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        
                                        <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                                            <x-form.closeBtn type="button" @click="document.getElementById('editDialog{{ $user->id }}').close()"
                                                class="mr-2 px-4 py-2 rounded text-white hover:bg-gray-300 bg-gray-400">
                                                Cancel
                                            </x-form.closeBtn>
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
                                            <x-form.closeBtn type="button" @click="document.getElementById('deleteDialog{{ $user->id }}').close()"
                                                class="mr-2 px-4 py-2 rounded text-white hover:bg-gray-300 bg-gray-400">
                                                Cancel</x-form.closeBtn>
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
            <h1 class="italic text-2xl px-6 py-4 text-start font-bold bg-main text-white">Add User</h1>
            <div class="container px-3 py-4">
                <form action="{{ route('users.store') }}" method="POST" class="px-6 py-4 container grid grid-cols-2 gap-x-8 gap-y-6" id="addUserForm">
                    @csrf
                    <input type="hidden" name="modal_id" value="addUserModal">

                    <div class="container text-start flex flex-col">
                        <label for="add_username" class="mb-1">Username</label>
                        <input id="add_username" autocomplete='off' name="name" type="text" class="px-3 py-2 border rounded-sm border-black @error('name') border-red-500 @enderror" required value="{{ old('name') }}">
                        <span class="text-red-500 text-sm mt-1 hidden error-username"></span>
                        @error('name')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="container text-start flex flex-col">
                        <label for="add_email" class="mb-1">Email</label>
                        <input id="add_email" autocomplete='off' name="email" type="email" class="px-3 py-2 border rounded-sm border-black @error('email') border-red-500 @enderror" required value="{{ old('email') }}">
                        <span class="text-red-500 text-sm mt-1 hidden error-email"></span>
                        @error('email')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <!-- Password Field -->
                    <div class="container text-start flex flex-col">
                        <label for="add_password" class="mb-1">Password</label>
                        <input id="add_password" type="password" name="password" class="px-3 py-2 border rounded-sm border-black @error('password') border-red-500 @enderror" required value="{{ old('password') }}">
                        <span class="text-red-500 text-sm mt-1 hidden error-password"></span>
                        @error('password')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <!-- Password Confirmation Field -->
                    <div class="container text-start flex flex-col">
                        <label for="add_password_confirmation" class="mb-1">Confirm Password</label>
                        <input id="add_password_confirmation" type="password" name="password_confirmation" class="px-3 py-2 border rounded-sm border-black @error('password') border-red-500 @enderror" required>
                        <span class="text-red-500 text-sm mt-1 hidden error-password-confirm"></span>
                    </div>
                    
                    <div class="container text-start flex flex-col col-span-2">
                        <label for="add_role">Role:</label>
                        <select name="role" class="px-3 py-2 border rounded-sm border-black" required>
                            <option value="" disabled selected>Choose Role</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                        @error('role')
                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="container col-span-2 gap-x-4 place-content-end w-full flex items-end content-center">
                        <x-form.closeBtn type="button" @click="$refs.dialogRef.close()" class="mr-2 px-4 py-2 rounded text-white hover:bg-gray-300 bg-gray-400">Cancel</x-form.closeBtn>
                        <x-form.saveBtn>Save</x-form.saveBtn>
                    </div>
                </form>
            </div>
        </dialog>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Store all existing users data for validation
            const existingUsers = @json($users->map(function($user) {
                return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email];
            }));

            // Helper function to clear all error messages in a form
            function clearErrors(form) {
                form.querySelectorAll('.error-username, .error-email, .error-password, .error-password-confirm').forEach(el => {
                    el.classList.add('hidden');
                    el.textContent = '';
                });
                form.querySelectorAll('input').forEach(input => {
                    input.classList.remove('border-red-500');
                });
            }

            // Helper function to show error
            function showError(form, errorClass, message) {
                const errorEl = form.querySelector(`.${errorClass}`);
                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.classList.remove('hidden');
                    const input = errorEl.previousElementSibling;
                    if (input && input.tagName === 'INPUT') {
                        input.classList.add('border-red-500');
                    }
                }
            }

            // Validate username uniqueness
            function validateUsername(form, username, currentUserId = null) {
                const trimmedUsername = username.trim();
                if (!trimmedUsername) {
                    showError(form, 'error-username', 'Username is required.');
                    return false;
                }
                
                const exists = existingUsers.some(user => 
                    user.name.toLowerCase() === trimmedUsername.toLowerCase() && user.id !== currentUserId
                );
                
                if (exists) {
                    showError(form, 'error-username', 'The username has already been taken.');
                    return false;
                }
                return true;
            }

            // Validate email uniqueness
            function validateEmail(form, email, currentUserId = null) {
                const trimmedEmail = email.trim();
                if (!trimmedEmail) {
                    showError(form, 'error-email', 'Email is required.');
                    return false;
                }
                
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(trimmedEmail)) {
                    showError(form, 'error-email', 'Please enter a valid email address.');
                    return false;
                }
                
                const exists = existingUsers.some(user => 
                    user.email.toLowerCase() === trimmedEmail.toLowerCase() && user.id !== currentUserId
                );
                
                if (exists) {
                    showError(form, 'error-email', 'The email has already been taken.');
                    return false;
                }
                return true;
            }

            // Validate password match
            function validatePasswordMatch(form, password, passwordConfirm) {
                if (password !== passwordConfirm) {
                    showError(form, 'error-password-confirm', 'The password confirmation does not match.');
                    return false;
                }
                return true;
            }

            // ADD USER FORM VALIDATION
            const addForm = document.getElementById('addUserForm');
            if (addForm) {
                addForm.addEventListener('submit', function(e) {
                    clearErrors(this);
                    
                    const username = this.querySelector('input[name="name"]').value;
                    const email = this.querySelector('input[name="email"]').value;
                    const password = this.querySelector('input[name="password"]').value;
                    const passwordConfirmation = this.querySelector('input[name="password_confirmation"]').value;
                    
                    let isValid = true;
                    
                    // Validate username
                    if (!validateUsername(this, username)) {
                        isValid = false;
                    }
                    
                    // Validate email
                    if (!validateEmail(this, email)) {
                        isValid = false;
                    }
                    
                    // Validate password fields
                    if (!password || !passwordConfirmation) {
                        showError(this, 'error-password', 'Password is required.');
                        isValid = false;
                    } else if (password.length < 8) {
                        showError(this, 'error-password', 'Password must be at least 8 characters.');
                        isValid = false;
                    } else if (!validatePasswordMatch(this, password, passwordConfirmation)) {
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                        return false;
                    }
                });
            }

            // EDIT USER FORMS VALIDATION
            @foreach($users as $user)
                const editForm{{ $user->id }} = document.querySelector('#editDialog{{ $user->id }} form');
                if (editForm{{ $user->id }}) {
                    editForm{{ $user->id }}.addEventListener('submit', function(e) {
                        clearErrors(this);
                        
                        const username = this.querySelector('input[name="name"]').value;
                        const email = this.querySelector('input[name="email"]').value;
                        const password = this.querySelector('input[name="password"]').value;
                        const passwordConfirmation = this.querySelector('input[name="password_confirmation"]').value;
                        const currentUserId = {{ $user->id }};
                        
                        let isValid = true;
                        
                        // Validate username
                        if (!validateUsername(this, username, currentUserId)) {
                            isValid = false;
                        }
                        
                        // Validate email
                        if (!validateEmail(this, email, currentUserId)) {
                            isValid = false;
                        }
                        
                        // Validate passwords only if they're filled
                        if (password || passwordConfirmation) {
                            if (password.length < 8) {
                                showError(this, 'error-password', 'Password must be at least 8 characters.');
                                isValid = false;
                            } else if (!validatePasswordMatch(this, password, passwordConfirmation)) {
                                isValid = false;
                            }
                        }
                        
                        if (!isValid) {
                            e.preventDefault();
                            return false;
                        }
                    });
                }
            @endforeach

            // Reopen modal if there are server-side validation errors (fallback)
            @if($errors->any())
                @if(old('modal_id'))
                    setTimeout(() => {
                        @if(old('modal_id') === 'addUserModal')
                            const dialog = document.querySelector('[x-ref="dialogRef"]');
                            if (dialog) dialog.showModal();
                        @else
                            const dialog = document.getElementById('{{ old('modal_id') }}');
                            if (dialog) dialog.showModal();
                        @endif
                    }, 100);
                @endif
            @endif
        });
        </script>
    </main>
</x-layout>