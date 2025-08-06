<button {{ $attributes }} class="flex rounded-md bg-button-create px-3 py-2 w-auto text-white items-center content-center hover:bg-button-create/90 transition:all duration-100 ease-in">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
    </svg>
    {{ $slot }}
</button>
