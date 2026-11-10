<div id="map-search-overlay" class="absolute inset-x-4 top-[env(safe-area-inset-top)] z-40 mt-4 md:right-auto md:left-6 md:w-[min(26rem,calc(100%-3rem))]">
    <div
        class="flex h-14 items-center gap-2 rounded-2xl border border-gray-200 bg-white px-2 shadow-[0_8px_28px_rgb(0_0_0_/_0.16)]">
        <svg class="ml-2 h-5 w-5 shrink-0 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        {{-- aria-label, bukan cuma placeholder: placeholder hilang begitu diketik. --}}
        <input type="text" id="search-input" placeholder="{{ __('Cari objek budaya atau UMKM...') }}"
            aria-label="{{ __('Cari objek budaya atau UMKM') }}"
            autocomplete="off" class="h-11 min-w-0 flex-1 bg-transparent text-base font-medium leading-6 text-charcoal placeholder:text-gray-500 focus:outline-none" />
        <button type="button" id="btn-search-clear" aria-label="{{ __('Hapus pencarian') }}"
            class="hidden min-h-11 min-w-11 shrink-0 items-center justify-center rounded-xl text-gray-600 hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="h-6 w-px shrink-0 bg-gray-200" aria-hidden="true"></div>
        <button type="button" id="btn-filter-toggle" aria-label="{{ __('Kategori Tempat') }}" aria-controls="filter-panel" aria-expanded="false"
            class="relative flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-xl text-primary hover:bg-primary/10 focus-visible:outline-2 focus-visible:outline-primary">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 7h16M7 12h10m-7 5h4" />
            </svg>
            <span id="filter-active-indicator" class="absolute right-2 top-2 hidden h-2 w-2 rounded-full border border-white bg-primary" aria-hidden="true"></span>
        </button>
    </div>

    <!-- Search Results Dropdown -->
    <div id="search-results"
        class="mt-2 hidden overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-[0_8px_28px_rgb(0_0_0_/_0.16)]">
        <ul id="search-results-list" class="max-h-[min(20rem,50dvh)] divide-y divide-gray-100 overflow-y-auto"></ul>
    </div>

    <!-- Compact filter panel keeps the map visible on desktop. -->
    <div id="filter-panel"
        class="mt-2 hidden max-h-[min(24rem,65dvh)] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-3 shadow-[0_8px_28px_rgb(0_0_0_/_0.16)]">
        <div class="flex min-h-11 items-center justify-between gap-2 px-2">
            <p class="font-display text-base font-bold text-charcoal">{{ __('Kategori Tempat') }}</p>
            <button type="button" id="btn-reset-filters"
                class="min-h-11 rounded-lg px-2 text-xs font-bold text-primary hover:bg-primary/10 focus-visible:outline-2 focus-visible:outline-primary">{{ __('Reset') }}</button>
        </div>

        <div class="mt-1 grid gap-1">
            <!-- Objek Budaya -->
            <button type="button"
                class="filter-card active flex min-h-12 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold text-charcoal transition-colors hover:bg-surface focus-visible:outline-2 focus-visible:outline-primary"
                data-filter="cultural" aria-pressed="true">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3.5 21.5V10L6.5 4H10v17.5zM20.5 21.5V10L17.5 4H14v17.5z" />
                    </svg>
                </span>
                <span class="flex-1">{{ __('Objek Budaya') }}</span>
                <span class="filter-check" aria-hidden="true">✓</span>
            </button>

            <!-- UMKM -->
            <button type="button"
                class="filter-card active flex min-h-12 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold text-charcoal transition-colors hover:bg-surface focus-visible:outline-2 focus-visible:outline-primary"
                data-filter="umkm" aria-pressed="true">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8.5 10V6.8a3.5 3.5 0 017 0V10h-2V6.8a1.5 1.5 0 00-3 0V10z" />
                        <path d="M5 9.5h14l.8 10.6a1.1 1.1 0 01-1.1 1.2H5.3a1.1 1.1 0 01-1.1-1.2z" />
                    </svg>
                </span>
                <span class="flex-1">{{ __('UMKM') }}</span>
                <span class="filter-check" aria-hidden="true">✓</span>
            </button>

            <!-- Fasilitas -->
            <button type="button"
                class="filter-card active flex min-h-12 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold text-charcoal transition-colors hover:bg-surface focus-visible:outline-2 focus-visible:outline-primary"
                data-filter="facilities" aria-pressed="true">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M12 3a9 9 0 100 18 9 9 0 000-18zm0 2.8a1.7 1.7 0 110 3.4 1.7 1.7 0 010-3.4zm-1.6 4.8h3.2v7.2h-3.2z" />
                    </svg>
                </span>
                <span class="flex-1">{{ __('Fasilitas') }}</span>
                <span class="filter-check" aria-hidden="true">✓</span>
            </button>

            <!-- Toilet -->
            <button type="button"
                class="filter-card active flex min-h-12 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold text-charcoal transition-colors hover:bg-surface focus-visible:outline-2 focus-visible:outline-primary"
                data-filter="toilets" aria-pressed="true">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
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
                <span class="flex-1">{{ __('Toilet') }}</span>
                <span class="filter-check" aria-hidden="true">✓</span>
            </button>

            <!-- Aksesibilitas -->
            <button type="button"
                class="filter-card active flex min-h-12 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold text-charcoal transition-colors hover:bg-surface focus-visible:outline-2 focus-visible:outline-primary"
                data-filter="accessibility" aria-pressed="true">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="9" cy="3.8" r="2.1" />
                        <path d="M7.7 7.3h2.5v4.6h4.5v2.4H9.1a1.4 1.4 0 01-1.4-1.4z" />
                        <path fill-rule="evenodd"
                            d="M11.5 10.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zm0 2.2a3.3 3.3 0 110 6.6 3.3 3.3 0 010-6.6z" />
                    </svg>
                </span>
                <span class="flex-1">{{ __('Aksesibilitas') }}</span>
                <span class="filter-check" aria-hidden="true">✓</span>
            </button>
        </div>
    </div>
</div>

<style>
    .filter-card.active { background-color: color-mix(in srgb, var(--color-primary) 8%, white); }
    .filter-card:not(.active) > span:first-child { opacity: .55; }
    .filter-check { display: flex; width: 1.25rem; height: 1.25rem; flex: none; align-items: center; justify-content: center; border: 1.5px solid var(--color-primary); border-radius: .375rem; background: var(--color-primary); color: white; font-size: .875rem; line-height: 1; }
    .filter-card:not(.active) .filter-check { background: white; color: transparent; }
</style>

<script>
    (function() {
        if (!document.body.dataset.mapSearchListenersRegistered) {
            const updateFilterIndicator = () => {
                const indicator = document.getElementById('filter-active-indicator');
                if (indicator) indicator.classList.toggle('hidden', !document.querySelector('.filter-card:not(.active)'));
            };
            document.body.addEventListener('click', function(e) {
                const filterToggleBtn = e.target.closest('#btn-filter-toggle');
                if (filterToggleBtn) {
                    const filterPanel = document.getElementById('filter-panel');
                    if (filterPanel) {
                        filterPanel.classList.toggle('hidden');
                        filterToggleBtn.setAttribute('aria-expanded', String(!filterPanel.classList.contains('hidden')));
                    }
                    document.getElementById('search-results')?.classList.add('hidden');
                    return;
                }

                const filterCard = e.target.closest('.filter-card');
                if (filterCard) {
                    const isChecked = filterCard.classList.toggle('active');
                    filterCard.setAttribute('aria-pressed', String(isChecked));
                    updateFilterIndicator();
                    const filterName = filterCard.dataset.filter;
                    window.dispatchEvent(new CustomEvent('filter-change', {
                        detail: {
                            filter: filterName,
                            active: isChecked
                        }
                    }));
                    return;
                }

                const resetBtn = e.target.closest('#btn-reset-filters');
                if (resetBtn) {
                    document.querySelectorAll('.filter-card').forEach(card => {
                        if (!card.classList.contains('active')) {
                            card.classList.add('active');
                            card.setAttribute('aria-pressed', 'true');
                            const filterName = card.dataset.filter;
                            window.dispatchEvent(new CustomEvent('filter-change', {
                                detail: {
                                    filter: filterName,
                                    active: true
                                }
                            }));
                        }
                    });
                    updateFilterIndicator();
                    return;
                }

                const filterPanel = document.getElementById('filter-panel');
                if (filterPanel && !filterPanel.classList.contains('hidden') && !e.target.closest('#map-search-overlay')) {
                    filterPanel.classList.add('hidden');
                    document.getElementById('btn-filter-toggle')?.setAttribute('aria-expanded', 'false');
                }
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') {
                    document.getElementById('filter-panel')?.classList.add('hidden');
                    document.getElementById('btn-filter-toggle')?.setAttribute('aria-expanded', 'false');
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
