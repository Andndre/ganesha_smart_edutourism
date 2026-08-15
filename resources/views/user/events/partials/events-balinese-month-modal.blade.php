{{-- ALL RERAINAN / HARI PENTING IN THE CURRENTLY VIEWED MONTH --}}
<x-modal name="balinese-month-list" maxWidth="md">
    <div class="space-y-4">
        <h3 class="font-display text-charcoal text-xl font-black leading-snug tracking-tight">
            {{ __('Hari Raya') }} <span x-text="monthRahinanLabel"></span>
        </h3>

        <template x-if="monthRahinanList.length === 0">
            <p class="text-xs text-gray-500">{{ __('Tidak ada rerainan tercatat bulan ini.') }}</p>
        </template>

        <div class="divide-y divide-gray-50">
            <template x-for="item in monthRahinanList" :key="item.date.toISOString()">
                <div class="flex items-start gap-3 py-3">
                    <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-xl bg-gray-50 text-gray-700">
                        <span class="text-sm font-black leading-none" x-text="item.date.getDate()"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-bold text-gray-500"
                            x-text="item.date.toLocaleDateString('id-ID', { weekday: 'long' }) + ' · ' + item.wuku"></p>
                        <div class="mt-1 flex flex-wrap gap-1.5">
                            <template x-if="item.isPurnama">
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2 py-0.5 text-[10px] font-bold text-gray-700"><span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>Purnama</span>
                            </template>
                            <template x-if="item.isTilem">
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2 py-0.5 text-[10px] font-bold text-gray-700"><span class="h-1.5 w-1.5 rounded-full bg-[#191A19]"></span>Tilem</span>
                            </template>
                            <template x-for="name in item.rahinan" :key="name">
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2 py-0.5 text-[10px] font-bold text-gray-700"><span class="h-1.5 w-1.5 rounded-full bg-red-600"></span><span x-text="name"></span></span>
                            </template>
                            <template x-if="item.nationalHoliday">
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2 py-0.5 text-[10px] font-bold text-gray-700"><span class="h-1.5 w-1.5 rounded-full bg-red-600"></span><span x-text="item.nationalHoliday"></span></span>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</x-modal>
