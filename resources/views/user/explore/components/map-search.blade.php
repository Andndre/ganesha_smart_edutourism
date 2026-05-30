<div class="absolute top-[env(safe-area-inset-top)] mt-4 inset-x-4 z-40">
    <div
        class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white/95 px-4 py-3.5 shadow-md backdrop-blur-md">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" id="search-input" placeholder="Cari objek budaya atau UMKM..."
            class="text-charcoal flex-1 bg-transparent text-sm font-medium placeholder-gray-400 outline-none" />
        <div class="mx-1 h-5 w-[1.5px] bg-gray-200"></div>
        <button type="button" id="btn-filter-toggle" class="text-primary focus:outline-none">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
        </button>
    </div>

    <!-- Filter Panel -->
    <div id="filter-panel" class="mt-3 hidden rounded-2xl bg-white px-4 py-3 shadow-md">
        <p class="mb-3 text-xs font-semibold text-gray-500">KATEGORI</p>
        <div class="flex flex-col gap-2">
            <label class="filter-toggle flex cursor-pointer items-center gap-3" data-filter="cultural">
                <input type="checkbox" class="sr-only" checked />
                <span class="filter-checkbox checked">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <span class="filter-label flex-1 text-sm font-medium text-gray-700">Objek Budaya</span>
                <span class="filter-dot" style="background: #1E5128;"></span>
            </label>

            <label class="filter-toggle flex cursor-pointer items-center gap-3" data-filter="umkm">
                <input type="checkbox" class="sr-only" checked />
                <span class="filter-checkbox checked">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <span class="filter-label flex-1 text-sm font-medium text-gray-700">UMKM</span>
                <span class="filter-dot" style="background: #8B5CF6;"></span>
            </label>

            <label class="filter-toggle flex cursor-pointer items-center gap-3" data-filter="facilities">
                <input type="checkbox" class="sr-only" checked />
                <span class="filter-checkbox checked">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <span class="filter-label flex-1 text-sm font-medium text-gray-700">Fasilitas</span>
                <span class="filter-dot" style="background: #3B82F6;"></span>
            </label>

            <label class="filter-toggle flex cursor-pointer items-center gap-3" data-filter="toilets">
                <input type="checkbox" class="sr-only" checked />
                <span class="filter-checkbox checked">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <span class="filter-label flex-1 text-sm font-medium text-gray-700">Toilet</span>
                <span class="filter-dot" style="background: #06B6D4;"></span>
            </label>

            <label class="filter-toggle flex cursor-pointer items-center gap-3" data-filter="accessibility">
                <input type="checkbox" class="sr-only" checked />
                <span class="filter-checkbox checked">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <span class="filter-label flex-1 text-sm font-medium text-gray-700">Aksesibilitas</span>
                <span class="filter-dot" style="background: #F59E0B;"></span>
            </label>

        </div>
    </div>
</div>

<style>
    .filter-checkbox {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 2px solid #d1d5db;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .filter-checkbox.checked {
        background: #1E5128;
        border-color: #1E5128;
    }

    .filter-checkbox svg {
        width: 12px;
        height: 12px;
        stroke: white;
        stroke-width: 3;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .filter-checkbox.checked svg {
        opacity: 1;
    }

    .filter-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterToggleBtn = document.getElementById('btn-filter-toggle');
        const filterPanel = document.getElementById('filter-panel');

        // Toggle filter panel
        filterToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            filterPanel.classList.toggle('hidden');
        });

        // Filter toggle click handlers
        document.querySelectorAll('.filter-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault(); // Mencegah klik ganda otomatis dari elemen label
                
                const checkbox = this.querySelector('.filter-checkbox');
                const isChecked = checkbox.classList.toggle('checked');
                this.querySelector('input[type="checkbox"]').checked = isChecked;

                // Dispatch custom event for map to handle
                const filterName = this.dataset.filter;
                window.dispatchEvent(new CustomEvent('filter-change', {
                    detail: {
                        filter: filterName,
                        active: isChecked
                    }
                }));
            });
        });
    });
</script>
