{{-- ponytail: partial dipecah untuk keterbacaan --}}
<!-- Sticky Bottom CTA -->
<div
    class="bottom-(--route-banner-h,0px) fixed inset-x-0 z-30 border-t border-gray-100 bg-white/90 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-8px_20px_rgba(0,0,0,0.06)] backdrop-blur-md">
    @if ($umkm->mapLocation)
        <a href="{{ route('explore', [
            'id' => $umkm->mapLocation->id,
            'lat' => $umkm->mapLocation->latitude,
            'lng' => $umkm->mapLocation->longitude,
            'name' => $umkm->business_name,
            'action' => 'route',
        ]) }}"
            class="bg-primary flex h-12 w-full items-center justify-center gap-2 rounded-xl font-bold text-white transition-all active:scale-[0.98]">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            {{ __('Lihat Peta untuk Bayar di Tempat') }}
        </a>
    @else
        <button
            class="bg-primary flex h-12 w-full items-center justify-center gap-2 rounded-xl font-bold text-white transition-all active:scale-[0.98]"
            onclick="scrollToMap()">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            {{ __('Lihat Peta untuk Bayar di Tempat') }}
        </button>
    @endif
    <p class="mt-2 text-center text-xs text-gray-500">
        {{ __('UMKM ini melayani pembayaran langsung di lokasi (Bayar di Tempat).') }}</p>
</div>
