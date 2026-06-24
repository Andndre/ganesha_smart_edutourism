<!-- Image -->
<div class="w-full aspect-video bg-gray-100 relative lg:rounded-3xl lg:border lg:border-gray-100 lg:shadow-sm lg:overflow-hidden">
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
            </svg>
            <span class="mt-2 text-xs font-semibold text-primary/40">{{ $umkm->business_name }}</span>
        </div>
    @endif
</div>
