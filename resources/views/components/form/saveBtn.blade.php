@props(['type' => 'submit', 'form' => null])

<button 
    type="{{ $type }}"   
    @if($form) form="{{ $form }}" @endif
    class="flex place-content-center rounded-md bg-button-save px-3 py-2 w-24 text-white items-center content-center hover:bg-button-save/80 transition:all duration-100 ease-in">
    {{ $slot ?? 'Save' }}
</button>
