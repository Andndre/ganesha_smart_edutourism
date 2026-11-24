@props(['tone' => 'neutral'])

<span {{ $attributes->class([
    'inline-flex items-center whitespace-nowrap rounded-lg px-2.5 py-1 text-xs font-semibold',
    'bg-primary/10 text-primary' => $tone === 'success',
    'bg-amber-50 text-amber-800' => $tone === 'warning',
    'bg-gray-100 text-gray-700' => $tone === 'neutral',
]) }}>{{ $slot }}</span>
