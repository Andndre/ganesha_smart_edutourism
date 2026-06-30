@props(['size' => 'md'])
@php
    $box = $size === 'sm' ? 'h-10 w-10' : 'h-12 w-12';
    $icon = $size === 'sm' ? 'h-5 w-5' : 'h-6 w-6';
@endphp
<button id="btn-map-style" type="button"
    {{ $attributes->merge(['class' => "tap-target flex $box items-center justify-center rounded-full border border-gray-100 bg-white text-gray-600 shadow-md transition-all hover:bg-gray-50 active:scale-95"]) }}
    title="{{ __('Jenis Peta') }}">
    <svg class="{{ $icon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zm10 0h6v6h-6zM4 14h6v6H4zm10 0h6v6h-6z" />
    </svg>
</button>
