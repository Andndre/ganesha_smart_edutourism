@props([
    'interactive' => false,
    'padding' => 'md',
])

@php
    $paddingClasses = match ($padding) {
        'none' => '',
        'sm' => 'p-3',
        'md' => 'p-4',
        'lg' => 'p-6',
        default => 'p-4',
    };
@endphp

<div
    class="dark:bg-dark-surface {{ $interactive ? 'active:scale-[0.98] active:shadow-none cursor-pointer' : '' }} {{ $paddingClasses }} rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-800">
    {{ $slot }}
</div>
