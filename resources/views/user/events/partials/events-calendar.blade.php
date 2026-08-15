{{-- VIEW MODE 1: INTERACTIVE CALENDAR --}}
<div x-show="viewMode === 'calendar'" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
    class="rounded-3xl border border-gray-100 bg-white p-4 shadow-sm md:p-6" style="display: none;">
    <div id="calendar-public"></div>

    <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-gray-50 pt-3 text-[11px] text-gray-500">
        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-red-600"></span>{{ __('Purnama') }}</span>
        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-[#191A19]"></span>{{ __('Tilem') }}</span>
        <span class="flex items-center gap-1.5"><span class="font-black text-red-600">15</span>{{ __('Hari Libur / Hari Raya') }}</span>
        <span class="text-gray-400">{{ __('Klik tanggal untuk lihat detail') }}</span>
    </div>

    <button type="button" @click="openMonthRahinan()"
        class="mt-3 flex w-full items-center justify-center gap-2 rounded-2xl border border-gray-200 py-2.5 text-xs font-bold text-gray-700 transition-all active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        {{ __('Lihat Semua Hari Raya Bulan Ini') }}
    </button>
</div>
