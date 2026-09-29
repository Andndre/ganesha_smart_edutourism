<!-- Sticky Bottom CTA (mobile & tablet only) -->
<div class="fixed bottom-(--route-banner-h,0px) inset-x-0 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] bg-white border-t border-gray-100 z-30 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] lg:hidden">
    <div class="mx-auto w-full max-w-2xl">
        @if ($umkm->mapLocation)
            <a href="{{ route('explore', [
                'id' => $umkm->mapLocation->id,
                'lat' => $umkm->mapLocation->latitude,
                'lng' => $umkm->mapLocation->longitude,
                'name' => $umkm->business_name,
                'action' => 'route',
            ]) }}"
                class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-primary font-bold text-white transition-all active:scale-[0.98]"
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
                class="flex h-12 w-full cursor-not-allowed items-center justify-center rounded-xl border border-gray-200 bg-gray-100 font-bold text-gray-600">
                {{ __('Lokasi toko belum tersedia') }}
            </button>
        @endif
    </div>
</div>
