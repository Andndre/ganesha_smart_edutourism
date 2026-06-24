{{-- ponytail: partial dipecah untuk keterbacaan --}}
<!-- Image -->
<div class="w-full aspect-video bg-gray-100 relative">
    @php
        $firstProduct = $umkm->activeProducts->first();
        $imagePath = $firstProduct ? ($firstProduct->image_path ?? $firstProduct->images[0] ?? null) : null;
    @endphp
    @if ($imagePath)
        <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ $umkm->business_name }}"
            class="h-full w-full object-cover">
    @else
        <div class="absolute inset-0 flex flex-col items-center justify-center bg-primary/5 text-primary">
            <svg class="h-16 w-16 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="mt-2 text-xs font-semibold text-primary/40">{{ $umkm->business_name }}</span>
        </div>
    @endif
</div>
