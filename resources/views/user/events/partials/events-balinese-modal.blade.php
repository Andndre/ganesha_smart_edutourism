{{-- BALINESE CALENDAR DATE DETAIL (MOBILE BOTTOM-SHEET / DESKTOP MODAL) --}}
<x-modal name="balinese-detail" maxWidth="sm">
    <div class="space-y-4">
        <h3 class="font-display text-charcoal text-xl font-black leading-snug tracking-tight">
            {{ __('Kalender Bali') }}
        </h3>

        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5">
                <p class="text-[9px] font-bold uppercase leading-none tracking-wider text-gray-400">{{ __('Wuku') }}</p>
                <p class="mt-1 text-xs font-black text-gray-700" x-text="selectedBalineseDate.wuku"></p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5">
                <p class="text-[9px] font-bold uppercase leading-none tracking-wider text-gray-400">{{ __('Sasih') }}</p>
                <p class="mt-1 text-xs font-black text-gray-700" x-text="selectedBalineseDate.sasih"></p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5">
                <p class="text-[9px] font-bold uppercase leading-none tracking-wider text-gray-400">{{ __('Wewaran') }}</p>
                <p class="mt-1 text-xs font-black text-gray-700"
                    x-text="selectedBalineseDate.pancaWara + ' ' + selectedBalineseDate.saptaWara"></p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5">
                <p class="text-[9px] font-bold uppercase leading-none tracking-wider text-gray-400">{{ __('Saka') }}</p>
                <p class="mt-1 text-xs font-black text-gray-700" x-text="selectedBalineseDate.saka"></p>
            </div>
        </div>

        <template x-if="selectedBalineseDate.isPurnama || selectedBalineseDate.isTilem || selectedBalineseDate.rahinan?.length || selectedBalineseDate.nationalHoliday">
            <div class="border-t border-gray-50 pt-3">
                <h4 class="mb-1.5 text-[10px] font-black uppercase tracking-wider text-gray-400">{{ __('Rerainan') }}</h4>
                <div class="flex flex-wrap gap-1.5">
                    <template x-if="selectedBalineseDate.isPurnama">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2.5 py-0.5 text-[10px] font-bold text-gray-700"><span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>Purnama</span>
                    </template>
                    <template x-if="selectedBalineseDate.isTilem">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2.5 py-0.5 text-[10px] font-bold text-gray-700"><span class="h-1.5 w-1.5 rounded-full bg-[#191A19]"></span>Tilem</span>
                    </template>
                    <template x-for="name in (selectedBalineseDate.rahinan || [])" :key="name">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2.5 py-0.5 text-[10px] font-bold text-gray-700"><span class="h-1.5 w-1.5 rounded-full bg-red-600"></span><span x-text="name"></span></span>
                    </template>
                    <template x-if="selectedBalineseDate.nationalHoliday">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2.5 py-0.5 text-[10px] font-bold text-gray-700"><span class="h-1.5 w-1.5 rounded-full bg-red-600"></span><span x-text="selectedBalineseDate.nationalHoliday"></span></span>
                    </template>
                </div>
            </div>
        </template>
    </div>
</x-modal>
