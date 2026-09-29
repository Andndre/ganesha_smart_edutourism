<!-- Info Card -->
<div class="relative z-10 -mt-4 rounded-t-3xl border-b border-gray-100 bg-white px-5 py-6 shadow-sm lg:mt-0 lg:rounded-3xl lg:border lg:border-gray-100 lg:shadow-sm lg:px-7 lg:py-7">
    <div class="mb-4 flex items-center gap-4">
        <div class="text-primary flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gray-100 shadow-inner">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
            </svg>
        </div>
        <div>
            <h1 class="text-charcoal text-xl font-bold lg:text-2xl">{{ $umkm->business_name }}</h1>
            <p class="mt-1 flex items-center gap-1 text-sm text-gray-500">
                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ __('Milik:') }} {{ $umkm->owner_name }}
            </p>
        </div>
    </div>

    @if ($umkm->description)
        <div class="prose prose-sm mt-2 max-w-none text-gray-600 prose-p:leading-relaxed lg:prose-base">
            {!! $umkm->description !!}
        </div>
    @endif

    <!-- Inline CTA (desktop only) -->
    <div class="mt-6 hidden border-t border-gray-100 pt-6 lg:block">
        @if ($umkm->mapLocation)
            <a href="{{ route('explore', [
                'id' => $umkm->mapLocation->id,
                'lat' => $umkm->mapLocation->latitude,
                'lng' => $umkm->mapLocation->longitude,
                'name' => $umkm->business_name,
                'action' => 'route',
            ]) }}"
                class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center gap-2 rounded-2xl font-bold text-white shadow-lg transition-all hover:bg-[#152E1D] active:scale-[0.98]"
                onclick="if(navigator.vibrate) navigator.vibrate(50)">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ __('Arahkan ke Toko') }}
            </a>
        @else
            <button disabled
                class="flex h-14 w-full cursor-not-allowed items-center justify-center rounded-2xl border border-gray-200 bg-gray-100 font-bold text-gray-600">
                {{ __('Lokasi toko belum tersedia') }}
            </button>
        @endif
    </div>
</div>
