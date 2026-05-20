@props([
    'shape' => 'rect', // rect, circle, text
    'width' => 'full',
    'height' => 'h-4',
])

@php
    $shapeClass = match ($shape) {
        'circle' => 'rounded-full',
        'text' => 'rounded h-4 w-3/4',
        default => 'rounded-lg',
    };
@endphp

<div class="{{ $shapeClass }} {{ $height }} animate-pulse bg-gray-200 dark:bg-gray-700"
    style="width: {{ $width }}"></div>
