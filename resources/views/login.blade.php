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
        </div>
        <div class='{{ $errors->has('login') ? 'h-auto' : 'h-0'}} transition-all duration-100 ease-in text-center w-72 mx-auto my-0 rounded-md text-white bg-red-500 text-[0.75rem]'>Incorrect credentials, please try again.</div>
    </form>
</x-layout>