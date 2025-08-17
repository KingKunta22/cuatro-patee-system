<dialog
    {{ $attributes->merge(['id' => $attributes->get('x-ref')]) }}
    x-ref="{{ $attributes->get('x-ref') }}"
    class="w-1/2 my-auto shadow-2xl rounded-md"
    @keydown.escape.window="$el.close()">
    <div class="container w-auto bg-main text-white flex items-center px-6 py-4">
        <h1 class="italic text-2xl font-bold mr-auto"> 
            {{ $dialogTitle }}
        </h1>
        <button type="button" @click="$el.closest('dialog').close()" class="text-lg font-bold hover:opacity-75">
            ✕
        </button>
    </div>
    <div class="container px-3 py-4 ">
        {{ $slot }}
    </div>
</dialog>