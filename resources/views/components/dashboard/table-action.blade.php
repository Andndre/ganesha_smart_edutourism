@props(['href' => null, 'type' => 'button', 'variant' => 'default'])

@php
    $classes = 'inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-lg px-3 text-sm font-semibold transition-colors disabled:pointer-events-none disabled:opacity-50 '.($variant === 'danger'
        ? 'text-red-700 hover:bg-red-50 focus-visible:outline-red-700 active:bg-red-100'
        : 'text-primary hover:bg-primary/10 focus-visible:outline-primary active:bg-primary/15');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</button>
@endif
