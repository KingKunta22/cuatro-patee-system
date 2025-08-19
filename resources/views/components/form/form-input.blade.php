@props(['label', 'name', 'type' => 'text', 'value' => '', 'required' => true, 'id' => null])

<div {{ $attributes->only('class')->merge(['class' => 'container flex flex-col text-start']) }}>
    <label for="{{ $id ?? $name }}">{{ $label }}</label>
    <input 
        id="{{ $id ?? $name }}" 
        name="{{ $name }}" 
        type="{{ $type }}" 
        class="px-3 py-2 border rounded-sm border-black [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0" 
        autocomplete="off" 
        @if($required) required @endif
        value="{{ $value }}"
        {{ $attributes->except('class') }}
    >
</div>