<div id="map-search-overlay" class="absolute inset-x-4 top-[env(safe-area-inset-top)] z-40 mt-4">
    <div
        class="flex h-14 items-center gap-3 rounded-full border border-white bg-white/90 px-5 shadow-[0_8px_30px_rgba(0,0,0,0.12)] backdrop-blur-md">
        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" id="search-input" placeholder="{{ __('Cari objek budaya atau UMKM...') }}"
            class="text-charcoal flex-1 bg-transparent text-sm font-medium placeholder-gray-500 outline-none" />
        <button type="button" id="btn-search-clear" aria-label="{{ __('Hapus pencarian') }}"
            class="hidden shrink-0 rounded-full p-1 text-gray-500 transition-transform duration-150 active:scale-90">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="mx-1 h-5 w-[1.5px] bg-gray-200"></div>
        <button type="button" id="btn-filter-toggle"
            class="text-primary transition-transform duration-150 focus:outline-none active:scale-90">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
        </button>
    </div>

    <!-- Search Results Dropdown -->
    <div id="search-results"
        class="mt-3 hidden overflow-hidden rounded-2xl border border-gray-100/50 bg-white/95 shadow-lg backdrop-blur-md">
        <ul id="search-results-list" class="max-h-64 divide-y divide-gray-50 overflow-y-auto"></ul>
    </div>

    <!-- Bento Grid Filter Panel -->
    <div id="filter-panel"
        class="mt-3 hidden rounded-2xl border border-gray-100/50 bg-white/95 px-4 py-4 shadow-lg backdrop-blur-md transition-all duration-300">
        <div class="mb-3 flex items-center justify-between">
            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">{{ __('Kategori Tempat') }}</p>
            <button type="button" id="btn-reset-filters"
                class="text-primary hover:text-primary/80 text-[11px] font-extrabold transition-colors active:scale-95">{{ __('Reset') }}</button>
        </div>

        <div class="grid grid-cols-2 gap-2.5">
            <!-- Objek Budaya -->
            <button type="button"
                class="filter-card active active:scale-98 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all duration-200"
                data-filter="cultural">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-50 text-[#1E5128]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3.5 21.5V10L6.5 4H10v17.5zM20.5 21.5V10L17.5 4H14v17.5z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-bold leading-tight text-gray-800">{{ __('Objek Budaya') }}</p>
                    <span class="mt-0.5 inline-block h-1 w-6 rounded-full" style="background: #1E5128;"></span>
                </div>
            </button>

            <!-- UMKM -->
            <button type="button"
                class="filter-card active active:scale-98 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all duration-200"
                data-filter="umkm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-[#8B5CF6]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.5 10V6.8a3.5 3.5 0 017 0V10h-2V6.8a1.5 1.5 0 00-3 0V10z" />
                        <path d="M5 9.5h14l.8 10.6a1.1 1.1 0 01-1.1 1.2H5.3a1.1 1.1 0 01-1.1-1.2z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-bold leading-tight text-gray-800">{{ __('UMKM') }}</p>
                    <span class="mt-0.5 inline-block h-1 w-6 rounded-full" style="background: #8B5CF6;"></span>
                </div>
            </button>

            <!-- Fasilitas -->
            <button type="button"
                class="filter-card active active:scale-98 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all duration-200"
                data-filter="facilities">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-[#3B82F6]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M12 3a9 9 0 100 18 9 9 0 000-18zm0 2.8a1.7 1.7 0 110 3.4 1.7 1.7 0 010-3.4zm-1.6 4.8h3.2v7.2h-3.2z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-bold leading-tight text-gray-800">{{ __('Fasilitas') }}</p>
                    <span class="mt-0.5 inline-block h-1 w-6 rounded-full" style="background: #3B82F6;"></span>
                </div>
            </button>

            <!-- Toilet -->
            <button type="button"
                class="filter-card active active:scale-98 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all duration-200"
                data-filter="toilets">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-[#0E7490]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="6.05" cy="3.6" r="2.1" />
                        <rect x="3.5" y="6.9" width="5.1" height="7.6" rx="1.8" />
                        <rect x="4.2" y="13" width="1.7" height="8" rx="0.8" />
                        <rect x="6.2" y="13" width="1.7" height="8" rx="0.8" />
                        <circle cx="16.55" cy="3.6" r="2.1" />
                        <path d="M16.55 6.9 20.5 15.6H12.6z" />
                        <rect x="14.7" y="14.6" width="1.7" height="6.4" rx="0.8" />
                        <rect x="16.7" y="14.6" width="1.7" height="6.4" rx="0.8" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-bold leading-tight text-gray-800">{{ __('Toilet') }}</p>
                    <span class="mt-0.5 inline-block h-1 w-6 rounded-full" style="background: #0E7490;"></span>
                </div>
            </button>

            <!-- Aksesibilitas -->
            <button type="button"
                class="filter-card active active:scale-98 col-span-2 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 px-3 py-2.5 text-left transition-all duration-200"
                data-filter="accessibility">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-[#B45309]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="9" cy="3.8" r="2.1" />
                        <path d="M7.7 7.3h2.5v4.6h4.5v2.4H9.1a1.4 1.4 0 01-1.4-1.4z" />
                        <path fill-rule="evenodd"
                            d="M11.5 10.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zm0 2.2a3.3 3.3 0 110 6.6 3.3 3.3 0 010-6.6z" />
                    </svg>
                </span>
                <div>
                    <p class="text-xs font-bold leading-tight text-gray-800">{{ __('Aksesibilitas') }}</p>
                    <span class="mt-0.5 inline-block h-1 w-12 rounded-full" style="background: #B45309;"></span>
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
        border-color: rgba(14, 116, 144, 0.25);
        background-color: rgba(14, 116, 144, 0.04);
    }

    .filter-card.active[data-filter="accessibility"] {
        border-color: rgba(180, 83, 9, 0.25);
        background-color: rgba(180, 83, 9, 0.04);
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
        if (!document.body.dataset.mapSearchListenersRegistered) {
            document.body.addEventListener('click', function(e) {
                // 1. Toggle filter panel
                const filterToggleBtn = e.target.closest('#btn-filter-toggle');
                if (filterToggleBtn) {
                    e.stopPropagation();
                    const filterPanel = document.getElementById('filter-panel');
                    if (filterPanel) {
                        filterPanel.classList.toggle('hidden');
                    }
                    // Filter panel and search results share the same spot
                    const searchResults = document.getElementById('search-results');
                    if (searchResults) {
                        searchResults.classList.add('hidden');
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
                }
            });
            // Sembunyikan bottom nav saat keyboard naik — tanpa ini hasil pencarian
            // terhimpit antara keyboard dan nav, hanya muat satu baris.
            // Delegasi di document supaya tetap hidup lintas wire:navigate.
            const isCompact = () => window.matchMedia('(max-width: 767px)').matches;
            const bottomNav = () => document.querySelector('nav[role="navigation"]');
            document.addEventListener('focusin', (e) => {
                if (e.target.id === 'search-input' && isCompact()) {
                    const nav = bottomNav();
                    if (nav) nav.style.display = 'none';
                }
            });
            document.addEventListener('focusout', (e) => {
                if (e.target.id === 'search-input') {
                    const nav = bottomNav();
                    if (nav) nav.style.display = '';
                }
            });

            document.body.dataset.mapSearchListenersRegistered = 'true';
        }
    })();
</script>
