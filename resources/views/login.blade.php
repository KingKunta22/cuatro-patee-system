<x-layout class="flex bg-main size-screen text-sm">
    <div class="w-3/5 h-screen grid place-items-center">
        <img src="{{ asset('assets/imgs/petshop 1.png')}}" class="w-96 h-auto">
    </div>
    <form action="/login" method="POST" class="w-2/5 py-16 px-14 bg-white">
        @csrf
        <div class="grid place-items-center">
            <img src="{{ asset('assets/imgs/logowhite.png') }}" class="w-32 h-auto">
        </div>
        <h1 class="pt-12">Log in to your account</h1>
        
        <div class="container">
            <div class="container flex flex-col py-4">
                <label for="username">Username</label>
                <input name="username" type="text" class="{{ $errors->has('login') ? 'border-red-700' : 'border-black' }} border border-solid rounded-xl  px-3 py-2 invalid:border-pink-500 invalid:text-pink-600 focus:border-sky-500 focus:outline-sky-500" value="{{ old('username') }}" autocomplete="off">
            </div>
            <div class="container flex flex-col py-4  relative">
                <label for="password">Password</label>
                <input name="password" type="password" id="password" class="{{ $errors->has('login') ? 'border-red-700' : ' border-black'}} border border-solid rounded-xl  px-3 py-2 invalid:border-pink-500 invalid:text-pink-600 focus:border-sky-500 focus:outline-sky-500" autocomplete="off"
                >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" id="showValue" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 absolute cursor-pointer right-4 top-1/2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" 
                id="hideValue" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 absolute cursor-pointer right-4 top-1/2 hidden">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </div>
            <br>
            <button type="submit" class="uppercase font-bold text-xl rounded-2xl bg-main text-white px-8 py-3 w-full hover:bg-main-light transition-all duration-200 ease-in my-6">
                Log in
            </button>
            <div class="text-center">
                <button type="button" onclick="document.getElementById('forgotDialog').showModal()" class="text-sm text-blue-600 hover:underline">Forgot password?</button>
            </div>
        </div>
        <div class='h-auto transition-all duration-100 ease-in text-center w-72 mx-auto my-0 rounded-md text-white bg-red-500 text-[0.75rem]' x-show="$wire.errors.has('login')" style="display: {{ $errors->has('login') && !session('forgot_success') && !$errors->has('forgot') && !$errors->has('code_validation') && !session('code_success') && !$errors->has('reset') && !session('reset_success') ? 'block' : 'none' }};">Incorrect credentials, please try again.</div>
        @if($errors->has('forgot'))
            <div class='h-auto transition-all duration-100 ease-in text-center w-72 mx-auto my-2 rounded-md text-white bg-red-500 text-[0.75rem]'>{{ $errors->first('forgot') }}</div>
        @endif
        @if(session('forgot_success'))
            <div class='h-auto transition-all duration-100 ease-in text-center w-72 mx-auto my-2 rounded-md text-white bg-green-500 text-[0.75rem]'>{{ session('forgot_success') }}</div>
        @endif
        @if($errors->has('code_validation'))
            <div class='h-auto transition-all duration-100 ease-in text-center w-72 mx-auto my-2 rounded-md text-white bg-red-500 text-[0.75rem]'>{{ $errors->first('code_validation') }}</div>
        @endif
        @if(session('code_success'))
            <div class='h-auto transition-all duration-100 ease-in text-center w-72 mx-auto my-2 rounded-md text-white bg-green-500 text-[0.75rem]'>{{ session('code_success') }}</div>
        @endif
        @if($errors->has('reset'))
            <div class='h-auto transition-all duration-100 ease-in text-center w-72 mx-auto my-2 rounded-md text-white bg-red-500 text-[0.75rem]'>{{ $errors->first('reset') }}</div>
        @endif
        @if(session('reset_success'))
            <div class='h-auto transition-all duration-100 ease-in text-center w-72 mx-auto my-2 rounded-md text-white bg-green-500 text-[0.75rem]'>{{ session('reset_success') }}</div>
        @endif
    </form>

    <!-- Forgot Password Modal -->
    <dialog id="forgotDialog" class="w-96 my-auto shadow-2xl rounded-md">
        <h1 class="italic text-xl px-6 py-4 text-start font-bold bg-main text-white">Forgot Password</h1>
        <div class="container px-4 py-4">
            <!-- Step 1: Send Code -->
            <div id="step1" class="grid grid-cols-1 gap-3">
                <form method="POST" action="{{ route('forgot.send') }}" class="grid grid-cols-1 gap-3">
                    @csrf
                    <label class="text-sm">Username</label>
                    <input type="text" name="username" class="border border-black rounded px-3 py-2" required autocomplete="off" value="{{ old('username') }}">
                    <label class="text-sm">Email</label>
                    <input type="email" name="email" class="border border-black rounded px-3 py-2" required autocomplete="off" value="{{ old('email') }}">
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" onclick="document.getElementById('forgotDialog').close()" class="px-3 py-2 bg-gray-300 rounded">Cancel</button>
                        <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Send Code</button>
                    </div>
                </form>
            </div>

            <!-- Step 2: Validate Code -->
            <div id="step2" class="grid grid-cols-1 gap-3 hidden">
                <form method="POST" action="{{ route('forgot.validate') }}" class="grid grid-cols-1 gap-3">
                    @csrf
                    <label class="text-sm">Username</label>
                    <input type="text" name="username" class="border border-black rounded px-3 py-2" required autocomplete="off" value="{{ old('username') }}">
                    <label class="text-sm">Email</label>
                    <input type="email" name="email" class="border border-black rounded px-3 py-2" required autocomplete="off" value="{{ old('email') }}">
                    <label class="text-sm">Verification Code</label>
                    <input type="text" name="code" class="border border-black rounded px-3 py-2" required autocomplete="off" placeholder="Enter 6-digit code" maxlength="6">
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" onclick="document.getElementById('forgotDialog').close()" class="px-3 py-2 bg-gray-300 rounded">Cancel</button>
                        <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded">Validate Code</button>
                    </div>
                </form>
            </div>

            <!-- Step 3: Reset Password -->
            <div id="step3" class="grid grid-cols-1 gap-3 hidden">
                <form method="POST" action="{{ route('forgot.reset') }}" class="grid grid-cols-1 gap-3" id="resetPasswordForm">
                    @csrf
                    <label class="text-sm">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="border border-black rounded px-3 py-2 @error('new_password') border-red-500 @enderror" required autocomplete="off">
                    <span class="text-red-500 text-sm hidden" id="error-new-password"></span>
                    @error('new_password')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                    
                    <label class="text-sm">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="border border-black rounded px-3 py-2 @error('new_password') border-red-500 @enderror" required autocomplete="off">
                    <span class="text-red-500 text-sm hidden" id="error-confirm-password"></span>
                    
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" onclick="document.getElementById('forgotDialog').close()" class="px-3 py-2 bg-gray-300 rounded">Cancel</button>
                        <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle modal step transitions based on session data
            @if(session('forgot_success'))
                // Show step 2 (code validation) if code was sent successfully
                document.getElementById('step1').classList.add('hidden');
                document.getElementById('step2').classList.remove('hidden');
                document.getElementById('step3').classList.add('hidden');
                // Open modal if not already open
                document.getElementById('forgotDialog').showModal();
            @endif
            
            @if(session('code_success'))
                // Show step 3 (password reset) if code was validated successfully
                document.getElementById('step1').classList.add('hidden');
                document.getElementById('step2').classList.add('hidden');
                document.getElementById('step3').classList.remove('hidden');
                // Ensure modal is open
                if (!document.getElementById('forgotDialog').open) {
                    document.getElementById('forgotDialog').showModal();
                }
            @endif

            // Reset modal when closed
            document.getElementById('forgotDialog').addEventListener('close', function() {
                document.getElementById('step1').classList.remove('hidden');
                document.getElementById('step2').classList.add('hidden');
                document.getElementById('step3').classList.add('hidden');
            });
            
            // Keep modal open if reset password validation fails (server-side fallback)
            @if($errors->has('forgot') && session('code_validated'))
                document.getElementById('step1').classList.add('hidden');
                document.getElementById('step2').classList.add('hidden');
                document.getElementById('step3').classList.remove('hidden');
                document.getElementById('forgotDialog').showModal();
            @endif
        });

        
    </script>
</x-layout>