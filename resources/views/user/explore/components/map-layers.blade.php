{{-- Toggle lapisan peta. Dulu dua FAB melayang di atas peta; dipindah ke sini supaya
     tumpukan tombol tidak menutupi peta terus-menerus. Id-nya sengaja dipertahankan —
     map-script mengikat listener lewat getElementById. --}}
<div class="mt-5 border-t border-gray-100 pt-4">
    <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-gray-500">{{ __('Lapisan Peta') }}</p>

    <div class="flex flex-col gap-2">
        <button type="button" id="btn-layer-map" aria-pressed="true"
            class="map-layer-toggle fab-btn-active flex w-full items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all active:scale-[0.98]">
            <span
                class="map-layer-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="text-charcoal block text-xs font-bold">{{ __('Wisatawan Live') }}</span>
                <span class="block text-[11px] text-gray-500">{{ __('Posisi pengunjung lain saat ini') }}</span>
            </span>
            <span class="map-layer-state text-[11px] font-extrabold text-gray-400">{{ __('Hidup') }}</span>
        </button>

        <button type="button" id="btn-real-heatmap" aria-pressed="true"
            class="map-layer-toggle fab-btn-active flex w-full items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all active:scale-[0.98]">
            <span
                class="map-layer-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                </svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="text-charcoal block text-xs font-bold">{{ __('Kepadatan Panas (Heatmap)') }}</span>
                <span class="block text-[11px] text-gray-500">{{ __('Area yang sedang ramai') }}</span>
            </span>
            <span class="map-layer-state text-[11px] font-extrabold text-gray-400">{{ __('Hidup') }}</span>
        </button>
    </div>
</div>
