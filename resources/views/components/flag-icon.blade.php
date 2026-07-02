@props(['locale'])

@php $clipId = 'flag-clip-'.\Illuminate\Support\Str::random(8); @endphp

<span {{ $attributes->merge(['class' => 'inline-block shrink-0']) }}>
    <svg viewBox="0 0 20 15" preserveAspectRatio="none" class="block h-full w-full">
        <defs>
            <clipPath id="{{ $clipId }}">
                <rect width="20" height="15" rx="2" ry="2" />
            </clipPath>
        </defs>
        <g clip-path="url(#{{ $clipId }})">
            @if ($locale === 'id')
                <rect width="20" height="7.5" fill="#CE1126" />
                <rect y="7.5" width="20" height="7.5" fill="#FFFFFF" />
            @else
                <rect width="20" height="15" fill="#00247D" />
                <path d="M0 0L20 15M20 0L0 15" stroke="#FFFFFF" stroke-width="3" />
                <path d="M0 0L20 15M20 0L0 15" stroke="#CF142B" stroke-width="1.2" />
                <path d="M10 0V15M0 7.5H20" stroke="#FFFFFF" stroke-width="5" />
                <path d="M10 0V15M0 7.5H20" stroke="#CF142B" stroke-width="3" />
            @endif
        </g>
    </svg>
</span>
