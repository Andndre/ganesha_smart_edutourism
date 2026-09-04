{{-- Sits below the search bar; z-30 so the search results / filter panel (z-40) cover it. --}}
<div id="multi-route-banner" class="absolute inset-x-4 top-[calc(env(safe-area-inset-top)+5.5rem)] z-30 hidden">
    <div role="status"
        class="flex items-center gap-3 rounded-2xl border border-white bg-white/90 px-4 py-2.5 shadow-[0_8px_30px_rgba(0,0,0,0.12)] backdrop-blur-md">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-orange-50 text-[#C2410C]">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-charcoal truncate text-xs font-bold">{{ __('Rute Belanja Aktif') }}</p>
            <p id="multi-route-progress" class="truncate text-[11px] font-medium text-gray-600"></p>
        </div>
        <button type="button" id="btn-stop-multi-route"
            class="tap-target -mr-1 flex min-h-11 shrink-0 items-center rounded-full px-3 text-xs font-extrabold text-[#C2410C] transition-transform active:scale-95">
            {{ __('Berhenti') }}
        </button>
    </div>
</div>
