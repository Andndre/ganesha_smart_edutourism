<div class="absolute inset-x-4 top-[env(safe-area-inset-top)] z-40 mt-4">
    <div
        class="flex h-14 items-center gap-3 rounded-full border border-white bg-white/90 px-5 shadow-[0_8px_30px_rgba(0,0,0,0.12)] backdrop-blur-md">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" id="search-input" placeholder="Cari objek budaya atau UMKM..."
            class="text-charcoal flex-1 bg-transparent text-sm font-medium placeholder-gray-400 outline-none" />
        <div class="mx-1 h-5 w-[1.5px] bg-gray-200"></div>
        <button type="button" id="btn-filter-toggle"
            class="text-primary transition-transform duration-150 focus:outline-none active:scale-90">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
        </button>
    </div>

    <!-- Bento Grid Filter Panel -->
    <div id="filter-panel"
        class="mt-3 hidden rounded-2xl border border-gray-100/50 bg-white/95 px-4 py-4 shadow-lg backdrop-blur-md transition-all duration-300">
        <div class="mb-3 flex items-center justify-between">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Kategori Tempat</p>
            <button type="button" id="btn-reset-filters"
                class="text-primary hover:text-primary/80 text-[10px] font-extrabold transition-colors active:scale-95">REKONDISI</button>
        </div>

        <div class="grid grid-cols-2 gap-2.5">
            <!-- Objek Budaya -->
            <button type="button"
                class="filter-card active active:scale-98 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all duration-200"
                data-filter="cultural">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-50 text-[#1E5128]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-bold leading-tight text-gray-800">Objek Budaya</p>
                    <span class="mt-0.5 inline-block h-1 w-6 rounded-full" style="background: #1E5128;"></span>
                </div>
            </button>

            <!-- UMKM -->
            <button type="button"
                class="filter-card active active:scale-98 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all duration-200"
                data-filter="umkm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-[#8B5CF6]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-bold leading-tight text-gray-800">UMKM</p>
                    <span class="mt-0.5 inline-block h-1 w-6 rounded-full" style="background: #8B5CF6;"></span>
                </div>
            </button>

            <!-- Fasilitas -->
            <button type="button"
                class="filter-card active active:scale-98 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all duration-200"
                data-filter="facilities">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-[#3B82F6]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-bold leading-tight text-gray-800">Fasilitas</p>
                    <span class="mt-0.5 inline-block h-1 w-6 rounded-full" style="background: #3B82F6;"></span>
                </div>
            </button>

            <!-- Toilet -->
            <button type="button"
                class="filter-card active active:scale-98 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all duration-200"
                data-filter="toilets">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-[#06B6D4]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4a1 1 0 100 2 1 1 0 000-2zm-2 8h4v8h-4v-8zm8-2h-3v8h2v-8h1zM5 10h3v8H6v-8H5z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-bold leading-tight text-gray-800">Toilet</p>
                    <span class="mt-0.5 inline-block h-1 w-6 rounded-full" style="background: #06B6D4;"></span>
                </div>
            </button>

            <!-- Aksesibilitas -->
            <button type="button"
                class="filter-card active active:scale-98 col-span-2 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all duration-200"
                data-filter="accessibility">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-[#F59E0B]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 10.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM7.5 13.5h7.5m-7.5-3.5h5a2 2 0 012 2v6m-7-6.5V6a2 2 0 012-2h1.5" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-bold leading-tight text-gray-800">Aksesibilitas</p>
                    <span class="mt-0.5 inline-block h-1 w-12 rounded-full" style="background: #F59E0B;"></span>
                </div>
            </button>
        </div>
    </div>
</div>

<style>
    .filter-card {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1.5px solid #f3f4f6;
    }

    /* Active States */
    .filter-card.active[data-filter="cultural"] {
        border-color: rgba(30, 81, 40, 0.25);
        background-color: rgba(30, 81, 40, 0.04);
    }

    .filter-card.active[data-filter="umkm"] {
        border-color: rgba(139, 92, 246, 0.25);
        background-color: rgba(139, 92, 246, 0.04);
    }

    .filter-card.active[data-filter="facilities"] {
        border-color: rgba(59, 130, 246, 0.25);
        background-color: rgba(59, 130, 246, 0.04);
    }

    .filter-card.active[data-filter="toilets"] {
        border-color: rgba(6, 182, 212, 0.25);
        background-color: rgba(6, 182, 212, 0.04);
    }

    .filter-card.active[data-filter="accessibility"] {
        border-color: rgba(245, 158, 11, 0.25);
        background-color: rgba(245, 158, 11, 0.04);
    }

    /* Inactive State */
    .filter-card:not(.active) {
        opacity: 0.5;
        border-color: #f3f4f6;
        background-color: #f9fafb;
    }

    .filter-card:not(.active) span {
        background-color: #f3f4f6 !important;
        color: #9ca3af !important;
    }
</style>

<script>
    (function() {
        if (!window.mapSearchListenersRegistered) {
            document.body.addEventListener('click', function(e) {
                // 1. Toggle filter panel
                const filterToggleBtn = e.target.closest('#btn-filter-toggle');
                if (filterToggleBtn) {
                    e.stopPropagation();
                    const filterPanel = document.getElementById('filter-panel');
                    if (filterPanel) {
                        filterPanel.classList.toggle('hidden');
                    }
                    return;
                }

                // 2. Filter card click (Bento Cards)
                const filterCard = e.target.closest('.filter-card');
                if (filterCard) {
                    const isChecked = filterCard.classList.toggle('active');
                    const filterName = filterCard.dataset.filter;
                    window.dispatchEvent(new CustomEvent('filter-change', {
                        detail: {
                            filter: filterName,
                            active: isChecked
                        }
                    }));
                    return;
                }

                // 3. Reset/Rekondisi filters to active
                const resetBtn = e.target.closest('#btn-reset-filters');
                if (resetBtn) {
                    document.querySelectorAll('.filter-card').forEach(card => {
                        if (!card.classList.contains('active')) {
                            card.classList.add('active');
                            const filterName = card.dataset.filter;
                            window.dispatchEvent(new CustomEvent('filter-change', {
                                detail: {
                                    filter: filterName,
                                    active: true
                                }
                            }));
                        }
                    });
                    return;
                }
            });
            window.mapSearchListenersRegistered = true;
        }
    })();
</script>
