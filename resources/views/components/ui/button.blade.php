@props([
    'variant' => 'primary', // primary, secondary, ghost, danger
    'size' => 'md', // sm, md, lg
    'href' => null,
])

@php
    $classes = match ($variant) {
        'primary' => 'bg-primary text-white hover:bg-primary-700 active:scale-95',
        'secondary' => 'bg-white border border-gray-300 text-charcoal hover:bg-gray-50',
        'ghost' => 'bg-transparent text-gray-600 hover:bg-gray-100',
        'danger' => 'bg-error text-white hover:bg-red-700',
        default => '',
    };

    $sizeClasses = match ($size) {
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-label',
        'lg' => 'px-6 py-3 text-body',
        default => '',
    };
@endphp

@if ($href)
    <a href="{{ $href }}"
        class="tap-target focus:ring-secondary {{ $classes }} {{ $sizeClasses }} inline-flex items-center justify-center rounded-lg font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2">
        {{ $slot }}
    </a>
@else
    <button type="button"
        class="tap-target focus:ring-secondary {{ $classes }} {{ $sizeClasses }} inline-flex items-center justify-center rounded-lg font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
        {{ $slot }}
    </button>
@endif
