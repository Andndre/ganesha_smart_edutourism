@props(['status' => 'safe'])

@php
    $statusKey = in_array($status, ['safe', 'medium', 'full'], true) ? $status : 'safe';
    $iconColor = match ($statusKey) {
        'medium' => 'text-amber-700',
        'full' => 'text-rose-700',
        default => 'text-emerald-700',
    };
@endphp

<svg {{ $attributes->class(['shrink-0', $iconColor]) }}
    :class="typeof iconColor !== 'undefined' ? iconColor : '{{ $iconColor }}'"
    viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
    <g x-show="(typeof statusKey !== 'undefined' ? statusKey : '{{ $statusKey }}') === 'safe'" @if ($statusKey !== 'safe') style="display: none;" @endif>
        <circle cx="12" cy="7" r="2.5" />
        <path d="M5.5 20a6.5 6.5 0 0 1 13 0v.5h-13z" />
    </g>
    <g x-show="(typeof statusKey !== 'undefined' ? statusKey : '{{ $statusKey }}') === 'medium'" @if ($statusKey !== 'medium') style="display: none;" @endif>
        <circle cx="12" cy="7.25" r="2.35" />
        <circle cx="5.15" cy="9" r="1.65" />
        <circle cx="18.85" cy="9" r="1.65" />
        <path d="M7.15 20.5v-.55a4.85 4.85 0 0 1 9.7 0v.55z" />
        <path d="M1.5 20.5v-.65a3.65 3.65 0 0 1 5.55-3.1 6.1 6.1 0 0 0-1.15 3.2v.55z" />
        <path d="M18.1 20.5v-.55a6.1 6.1 0 0 0-1.15-3.2 3.65 3.65 0 0 1 5.55 3.1v.65z" />
    </g>
    <g x-show="(typeof statusKey !== 'undefined' ? statusKey : '{{ $statusKey }}') === 'full'" @if ($statusKey !== 'full') style="display: none;" @endif>
        <circle cx="4" cy="9.25" r="1.5" />
        <circle cx="8" cy="7.75" r="1.7" />
        <circle cx="12" cy="7" r="1.9" />
        <circle cx="16" cy="7.75" r="1.7" />
        <circle cx="20" cy="9.25" r="1.5" />
        <path d="M1 20a3.5 3.5 0 0 1 4-3.45 4.1 4.1 0 0 1 7-1.1 4.1 4.1 0 0 1 7 1.1A3.5 3.5 0 0 1 23 20v1H1z" />
    </g>
</svg>
