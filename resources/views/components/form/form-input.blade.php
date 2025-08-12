@props(['label', 'name', 'type' => 'text', 'value'])

<div {{ $attributes->merge(['class' => 'container flex flex-col text-start'])}}>
    <label for="{{ $name }}">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" class="px-3 py-2 border rounded-sm border-black [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0" autocomplete="off" required value="{{ $value }}" {{ $attributes }}>
</div>
