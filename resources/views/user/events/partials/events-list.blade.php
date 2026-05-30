{{-- VIEW MODE 2: TIMELINE LIST --}}
<div x-show="viewMode === 'list'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
    class="space-y-6">
    
    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
        <div>
            <h2 class="text-charcoal text-base font-bold">Acara Mendatang</h2>
            <p class="text-[11px] text-gray-500 mt-0.5">Daftar agenda kebudayaan terdekat yang terdaftar</p>
        </div>
        <span class="text-[10px] font-bold text-gray-400 uppercase bg-gray-50 px-2 py-1 rounded-lg" x-text="filteredTimelineEvents.length + ' Acara'"></span>
    </div>

    <!-- Timeline Cards Container -->
    <div class="relative space-y-6 border-l-2 border-gray-100 pl-4 ml-2">
        <template x-for="(e, index) in filteredTimelineEvents" :key="e.id">
            <div class="relative animate-fade-in-up" :style="'animation-delay: ' + (index * 60) + 'ms'">
                <!-- Timeline Pin -->
                <div class="left-[-22px] absolute top-1.5 z-10 h-3 w-3 rounded-full border-2 border-white bg-primary shadow-sm"></div>

                <!-- Card Element -->
                <div class="group overflow-hidden rounded-2xl border border-gray-100 bg-white p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                    <div class="mb-3 flex items-center justify-between flex-wrap gap-2">
                        <span :class="{
                            'bg-amber-50 text-amber-600 border-amber-100': e.category.toLowerCase().includes('adat') || e.category.toLowerCase().includes('upacara'),
                            'bg-emerald-50 text-emerald-600 border-emerald-100': e.category.toLowerCase().includes('festival') || e.category.toLowerCase().includes('seni'),
                            'bg-blue-50 text-blue-600 border-blue-100': e.category.toLowerCase().includes('workshop'),
                            'bg-rose-50 text-rose-600 border-rose-100': e.category.toLowerCase().includes('kuliner')
                        }" class="rounded-lg border px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider" x-text="e.category"></span>
                        
                        <span :class="e.is_free ? 'bg-green-50 text-green-600' : 'bg-gray-50 text-gray-600'" 
                            class="rounded-md px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider" 
                            x-text="e.is_free ? 'Gratis' : 'Rp ' + Number(e.price).toLocaleString('id-ID')"></span>
                    </div>

                    <h3 class="text-charcoal group-hover:text-primary mb-1 text-base font-extrabold transition-colors duration-200" x-text="e.name"></h3>
                    <p class="mb-4 text-xs leading-relaxed text-gray-500 line-clamp-2" x-text="e.description || 'Tidak ada deskripsi tambahan untuk event budaya ini.'"></p>
                    
                    <!-- Parameters Grid -->
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-gray-50 pt-3 text-[11px] text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span x-text="formatDateLong(e.start_date)"></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="e.start_time + ' WITA'"></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <span class="truncate max-w-[120px] md:max-w-none" x-text="e.location_name"></span>
                        </div>
                    </div>

                    <!-- Click Details Trigger -->
                    <div class="mt-4 flex gap-2">
                        <button type="button" @click="openDetail(e)"
                            class="flex-1 text-center bg-gray-50 hover:bg-primary hover:text-white rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 transition-all duration-300 active:scale-[0.98]">
                            Lihat Detail & Waktu
                        </button>
                        <template x-if="e.latitude && e.longitude">
                            <a :href="'https://www.google.com/maps/search/?api=1&query=' + e.latitude + ',' + e.longitude" 
                                target="_blank"
                                class="aspect-square bg-gray-50 text-gray-400 hover:bg-amber-50 hover:text-amber-600 rounded-xl p-2 flex items-center justify-center transition-all duration-300">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="filteredTimelineEvents.length === 0" 
        class="rounded-3xl border border-dashed border-gray-200 bg-white p-8 text-center" style="display: none;">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-400">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <h3 class="text-charcoal mt-3 text-sm font-bold">Tidak Ada Event Ditemukan</h3>
        <p class="mt-1 text-xs text-gray-400">Tidak ada event untuk kategori yang dipilih di bulan ini.</p>
    </div>
</div>
