{{-- UNIFIED DAY SUMMARY MODAL: BALINESE CALENDAR & DAY AGENDA --}}
<x-modal name="balinese-detail" maxWidth="md">
    <div class="space-y-5">
        <!-- Header -->
        <div class="flex items-start justify-between gap-3 border-b border-gray-100 pb-3">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-primary">
                    {{ __('Kalender Bali & Agenda') }}
                </span>
                <h3 class="font-display text-charcoal mt-0.5 text-lg font-black leading-snug tracking-tight"
                    x-text="selectedDay.dateLong || '{{ __('Detail Hari') }}'"></h3>
            </div>
            <button type="button" @click="isOpen = false"
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-50 text-gray-500 transition-colors hover:text-gray-700 active:scale-95 md:hidden"
                title="{{ __('Tutup') }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- 1. Balinese Calendar Grid -->
        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
            <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3">
                <p class="text-[10px] font-bold uppercase leading-none tracking-wider text-gray-500">{{ __('Wuku') }}</p>
                <p class="mt-1 text-xs font-black text-gray-800"
                    x-text="selectedDay.balinese?.wuku || selectedBalineseDate.wuku || '-'"></p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3">
                <p class="text-[10px] font-bold uppercase leading-none tracking-wider text-gray-500">{{ __('Sasih') }}</p>
                <p class="mt-1 text-xs font-black text-gray-800"
                    x-text="selectedDay.balinese?.sasih || selectedBalineseDate.sasih || '-'"></p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3">
                <p class="text-[10px] font-bold uppercase leading-none tracking-wider text-gray-500">{{ __('Wewaran') }}</p>
                <p class="mt-1 text-xs font-black text-gray-800 truncate"
                    x-text="((selectedDay.balinese?.pancaWara || selectedBalineseDate.pancaWara || '') + ' ' + (selectedDay.balinese?.saptaWara || selectedBalineseDate.saptaWara || '')).trim() || '-'"></p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3">
                <p class="text-[10px] font-bold uppercase leading-none tracking-wider text-gray-500">{{ __('Saka') }}</p>
                <p class="mt-1 text-xs font-black text-gray-800"
                    x-text="selectedDay.balinese?.saka || selectedBalineseDate.saka || '-'"></p>
            </div>
        </div>

        <!-- Rerainan / Hari Raya Badges -->
        <template x-if="(selectedDay.balinese?.isPurnama ?? selectedBalineseDate.isPurnama) || (selectedDay.balinese?.isTilem ?? selectedBalineseDate.isTilem) || ((selectedDay.balinese?.rahinan || selectedBalineseDate.rahinan || []).length > 0) || (selectedDay.balinese?.nationalHoliday ?? selectedBalineseDate.nationalHoliday)">
            <div class="rounded-2xl border border-gray-100 bg-gray-50/40 p-3">
                <h4 class="mb-1.5 text-[10px] font-black uppercase tracking-wider text-gray-500">{{ __('Rerainan & Hari Penting') }}</h4>
                <div class="flex flex-wrap gap-1.5">
                    <template x-if="selectedDay.balinese?.isPurnama ?? selectedBalineseDate.isPurnama">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-red-100 bg-red-50/80 px-2.5 py-0.5 text-[11px] font-extrabold text-red-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>{{ __('Purnama') }}
                        </span>
                    </template>
                    <template x-if="selectedDay.balinese?.isTilem ?? selectedBalineseDate.isTilem">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-100 px-2.5 py-0.5 text-[11px] font-extrabold text-charcoal">
                            <span class="bg-charcoal h-1.5 w-1.5 rounded-full"></span>{{ __('Tilem') }}
                        </span>
                    </template>
                    <template x-for="name in (selectedDay.balinese?.rahinan || selectedBalineseDate.rahinan || [])" :key="name">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-100 bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span><span x-text="name"></span>
                        </span>
                    </template>
                    <template x-if="selectedDay.balinese?.nationalHoliday ?? selectedBalineseDate.nationalHoliday">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-red-100 bg-red-50 px-2.5 py-0.5 text-[11px] font-bold text-red-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span><span x-text="selectedDay.balinese?.nationalHoliday || selectedBalineseDate.nationalHoliday"></span>
                        </span>
                    </template>
                </div>
            </div>
        </template>

        <!-- 2. Events on this Day Section -->
        <div class="border-t border-gray-100 pt-4">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-black uppercase tracking-wider text-charcoal">
                    {{ __('Agenda Acara di Penglipuran') }}
                </h4>
                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-600"
                    x-text="(selectedDay.events?.length || 0) + ' {{ __('Acara') }}'"></span>
            </div>

            <!-- Event Cards List -->
            <template x-if="selectedDay.events && selectedDay.events.length > 0">
                <div class="mt-3 space-y-2.5">
                    <template x-for="ev in selectedDay.events" :key="ev.id">
                        <div @click="openEventFromDay(ev)"
                            class="group flex cursor-pointer items-start justify-between gap-3 rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5 transition-all duration-200 hover:border-primary/40 hover:bg-white hover:shadow-md active:scale-[0.99]">
                            <div class="min-w-0 flex-1">
                                <div class="mb-1.5 flex flex-wrap items-center gap-1.5">
                                    <span
                                        :class="{
                                            'bg-amber-50 text-amber-600 border-amber-100': ev.category && (ev.category.toLowerCase().includes('adat') || ev.category.toLowerCase().includes('upacara')),
                                            'bg-emerald-50 text-emerald-600 border-emerald-100': ev.category && (ev.category.toLowerCase().includes('festival') || ev.category.toLowerCase().includes('seni')),
                                            'bg-blue-50 text-blue-600 border-blue-100': ev.category && ev.category.toLowerCase().includes('workshop'),
                                            'bg-rose-50 text-rose-600 border-rose-100': ev.category && ev.category.toLowerCase().includes('kuliner')
                                        }"
                                        class="rounded-md border px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider"
                                        x-text="ev.category"></span>

                                    <span :class="ev.is_free ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-600'"
                                        class="rounded-md px-1.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider"
                                        x-text="ev.is_free ? '{{ __('Gratis') }}' : '{{ __('Rp') }} ' + Number(ev.price).toLocaleString('id-ID')"></span>
                                </div>

                                <h5 class="text-charcoal group-hover:text-primary line-clamp-1 text-sm font-bold transition-colors"
                                    x-text="ev.name"></h5>

                                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span x-text="ev.start_time + ' - ' + ev.end_time + ' WITA'"></span>
                                    </span>
                                    <template x-if="ev.location_name">
                                        <span class="flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            <span class="max-w-[160px] truncate" x-text="ev.location_name"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-400 transition-all duration-200 group-hover:bg-primary group-hover:text-white group-hover:shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Empty State when no events -->
            <template x-if="!selectedDay.events || selectedDay.events.length === 0">
                <div class="mt-3 flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 px-4 py-6 text-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="mt-2.5 text-xs font-bold text-gray-700">
                        {{ __('Tidak ada agenda acara pada tanggal ini') }}
                    </p>
                    <p class="mt-0.5 text-[11px] leading-relaxed text-gray-500">
                        {{ __('Anda tetap dapat menikmati suasana desa dan daya tarik budaya harian.') }}
                    </p>
                </div>
            </template>
        </div>
    </div>
</x-modal>
