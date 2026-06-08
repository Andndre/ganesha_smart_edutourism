{{-- PREMIUM DETAIL EVENT MODAL (MOBILE BOTTOM-SHEET / DESKTOP MODAL) --}}
<x-modal name="event-detail" maxWidth="lg">
    <div class="space-y-5">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <span :class="{
                'bg-amber-50 text-amber-600 border-amber-100': selectedEvent.category && (selectedEvent.category.toLowerCase().includes('adat') || selectedEvent.category.toLowerCase().includes('upacara')),
                'bg-emerald-50 text-emerald-600 border-emerald-100': selectedEvent.category && (selectedEvent.category.toLowerCase().includes('festival') || selectedEvent.category.toLowerCase().includes('seni')),
                'bg-blue-50 text-blue-600 border-blue-100': selectedEvent.category && selectedEvent.category.toLowerCase().includes('workshop'),
                'bg-rose-50 text-rose-600 border-rose-100': selectedEvent.category && selectedEvent.category.toLowerCase().includes('kuliner')
            }" class="rounded-lg border px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider" x-text="selectedEvent.category"></span>
            
            <button type="button" @click="isOpen = false"
                class="flex items-center justify-center h-8 w-8 rounded-full bg-gray-50 text-gray-400 hover:text-gray-600 active:scale-95 transition-all md:hidden"
                title="Tutup">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <h3 class="font-display text-charcoal text-xl font-black tracking-tight leading-snug" x-text="selectedEvent.name"></h3>

        <!-- Visual Date Card Widget -->
        <div class="flex items-center gap-3 bg-gray-50/70 p-3.5 rounded-2xl border border-gray-100">
            <div class="flex flex-col items-center justify-center w-12 h-12 rounded-xl bg-primary text-white font-black shrink-0 shadow-sm shadow-primary/10">
                <span class="text-[9px] uppercase tracking-wider leading-none" x-text="formatDateCard(selectedEvent.start_date).month"></span>
                <span class="text-lg leading-none mt-1" x-text="formatDateCard(selectedEvent.start_date).day"></span>
            </div>
            <div>
                <p class="text-xs font-black text-gray-700" x-text="formatDateLong(selectedEvent.start_date)"></p>
                <p class="text-[11px] text-gray-500 mt-0.5" x-text="selectedEvent.start_time + ' - ' + selectedEvent.end_time + ' WITA'"></p>
            </div>
        </div>

        <!-- Location Widget -->
        <div class="flex items-start gap-3 bg-gray-50/70 p-3.5 rounded-2xl border border-gray-100">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-[#D4AF37] flex items-center justify-center shrink-0">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-xs font-black text-gray-700">Lokasi Acara</p>
                <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed" x-text="selectedEvent.location_name"></p>
            </div>
        </div>

        <!-- Price & Capacity Widget -->
        <div class="grid grid-cols-2 gap-3">
            <div class="flex items-center gap-2.5 bg-gray-50/70 p-3.5 rounded-xl border border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider leading-none">Harga Tiket</p>
                    <p class="text-xs font-black text-gray-700 mt-1" x-text="selectedEvent.is_free ? 'Gratis' : 'Rp ' + Number(selectedEvent.price).toLocaleString('id-ID')"></p>
                </div>
            </div>
            <div class="flex items-center gap-2.5 bg-gray-50/70 p-3.5 rounded-xl border border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 005.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider leading-none">Kapasitas</p>
                    <p class="text-xs font-black text-gray-700 mt-1" x-text="selectedEvent.max_participants ? selectedEvent.max_participants + ' Orang' : '-'"></p>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="border-t border-gray-50 pt-3">
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Deskripsi Acara</h4>
            <p class="text-xs text-gray-500 leading-relaxed max-h-36 overflow-y-auto" x-text="selectedEvent.description || 'Tidak ada deskripsi tambahan.'"></p>
        </div>

        <!-- Open in Maps GPS Button -->
        <template x-if="selectedEvent.latitude && selectedEvent.longitude">
            <a :href="'https://www.google.com/maps/search/?api=1&query=' + selectedEvent.latitude + ',' + selectedEvent.longitude" 
                target="_blank"
                class="mt-6 flex items-center justify-center gap-2 rounded-2xl bg-primary hover:bg-[#152E1D] px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/10 transition-all duration-200 active:scale-[0.98] w-full">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Buka Petunjuk Arah (Maps GPS)
            </a>
        </template>
    </div>
</x-modal>
