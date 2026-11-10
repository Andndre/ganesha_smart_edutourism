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
