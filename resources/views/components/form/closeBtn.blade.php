<button type="button" {{ $attributes->merge(['class' => 'flex place-content-center rounded-md bg-button-delete px-3 py-2 w-24 text-white items-center content-center hover:bg-button-delete/80 transition:all duration-100 ease-in']) }}>
    {{ $slot }}
</button>
