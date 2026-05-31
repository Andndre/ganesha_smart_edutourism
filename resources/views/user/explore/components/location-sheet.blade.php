<x-modal name="location-sheet" maxWidth="md" :hasBackdrop="false">
    <div class="px-1 py-1">
        <!-- Header -->
        <div class="mb-4 flex items-start justify-between">
            <div>
                <h3 id="sheet-title" class="font-display text-charcoal text-xl font-bold tracking-tight">Nama Lokasi</h3>
                <span id="sheet-category-badge"
                    class="inline-flex items-center gap-1.5 mt-2 rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                    <span id="sheet-category-dot" class="h-2 w-2 rounded-full"></span>
                    <span id="sheet-category-text">Kategori</span>
                </span>
            </div>
            <!-- Custom close button for mobile (desktop close button is handled by x-modal template) -->
            <button type="button" onclick="closeSheet()"
                class="hover:text-charcoal -mr-2 rounded-full bg-gray-50 p-2 text-gray-400 active:scale-95 transition-all md:hidden">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Content Area -->
        <div class="max-h-[30dvh] overflow-y-auto space-y-4 pr-1">
            <!-- Deskripsi Section -->
            <div id="section-desc">
                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Deskripsi</h4>
                <p id="sheet-desc" class="text-sm leading-relaxed text-gray-600">Detail deskripsi lokasi.</p>
            </div>

            <!-- Aksesibilitas Section -->
            <div id="section-accessibility"
                class="rounded-2xl bg-amber-50/80 border border-amber-100/60 p-4 flex gap-3">
                <div class="text-amber-600 shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 10.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM7.5 13.5h7.5m-7.5-3.5h5a2 2 0 012 2v6m-7-6.5V6a2 2 0 012-2h1.5" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-bold text-amber-800 uppercase tracking-wider mb-1">Aksesibilitas</h4>
                    <p id="sheet-accessibility" class="text-xs leading-relaxed text-amber-700 font-medium"></p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex gap-3 pb-2">
            <a href="#" id="sheet-route-btn" target="_blank"
                class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white py-3.5 text-sm font-bold text-gray-700 shadow-xs hover:bg-gray-50 active:scale-95 transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Arahkan
            </a>
            <a href="#" id="sheet-detail-btn"
                class="bg-primary hover:bg-primary/95 flex flex-1 items-center justify-center gap-2 rounded-xl py-3.5 text-sm font-bold text-white shadow-lg shadow-primary/20 active:scale-95 transition-all">
                Detail
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
</x-modal>