@props([
    'variant' => 'default', // default, success, warning, error, info
    'size' => 'sm',
])

@php
    $classes = match ($variant) {
        'success' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        'error' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    };

    $sizeClasses = match ($size) {
        'sm' => 'text-xs px-2 py-0.5',
        'md' => 'text-sm px-2.5 py-1',
        default => 'text-xs px-2 py-0.5',
    };
@endphp

<span class="{{ $classes }} {{ $sizeClasses }} inline-flex items-center rounded-full font-medium">
    {{ $slot }}
</span>
