{{-- Dynamic Tab & Filter Toolbar --}}
<div
    class="flex flex-col gap-3 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
    <!-- View Switcher -->
    <div class="inline-flex rounded-xl bg-gray-50 p-1">
        <button type="button" @click="viewMode = 'calendar'"
            :class="viewMode === 'calendar' ? 'bg-white text-primary shadow-sm font-bold' :
                'text-gray-500 hover:text-charcoal font-medium'"
            class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs transition-all duration-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Kalender
        </button>
        <button type="button" @click="viewMode = 'list'"
            :class="viewMode === 'list' ? 'bg-white text-primary shadow-sm font-bold' :
                'text-gray-500 hover:text-charcoal font-medium'"
            class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs transition-all duration-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            Daftar Acara
        </button>
    </div>

    <!-- Custom Dynamic Dropdown Filter -->
    <div class="relative z-30 w-full shrink-0 sm:w-auto" x-data="{ isOpen: false }">
        <button type="button" @click="isOpen = !isOpen" @click.away="isOpen = false"
            class="hover:border-primary flex w-full items-center justify-between gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-xs font-bold text-gray-700 transition-all duration-200 hover:bg-gray-50/50 active:scale-[0.98] sm:w-56">
            <div class="flex items-center gap-2">
                <!-- Tiny visual category indicator color dot -->
                <span
                    :class="{
                        'bg-primary': selectedCategory === 'Semua',
                        'bg-amber-500': selectedCategory === 'Upacara Adat',
                        'bg-emerald-500': selectedCategory === 'Festival',
                        'bg-blue-500': selectedCategory === 'Workshop',
                        'bg-rose-500': selectedCategory === 'Kuliner'
                    }"
                    class="h-2 w-2 shrink-0 rounded-full"></span>
                <span x-text="selectedCategory === 'Semua' ? 'Semua Kategori' : selectedCategory"></span>
            </div>
            <svg class="h-3.5 w-3.5 text-gray-400 transition-transform duration-200" :class="isOpen ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Floating Menu -->
        <div x-show="isOpen" x-transition:enter="transition ease-out duration-150 transform"
            x-transition:enter-start="opacity-0 scale-95 translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-1"
            class="absolute left-0 z-50 mt-2 w-full rounded-2xl border border-gray-100 bg-white p-1.5 shadow-xl sm:w-56"
            style="display: none;">

            <template x-for="cat in ['Semua', 'Upacara Adat', 'Festival', 'Workshop', 'Kuliner']"
                :key="cat">
                <button type="button" @click="filterCategory(cat); isOpen = false"
                    :class="selectedCategory === cat ? 'bg-primary/8 text-primary font-bold' :
                        'text-gray-600 hover:bg-gray-50 hover:text-charcoal font-medium'"
                    class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-xs transition-colors duration-150">
                    <!-- Category visual color dot inside list -->
                    <span
                        :class="{
                            'bg-primary': cat === 'Semua',
                            'bg-amber-500': cat === 'Upacara Adat',
                            'bg-emerald-500': cat === 'Festival',
                            'bg-blue-500': cat === 'Workshop',
                            'bg-rose-500': cat === 'Kuliner'
                        }"
                        class="h-2 w-2 shrink-0 rounded-full"></span>
                    <span x-text="cat === 'Semua' ? 'Semua Kategori' : cat"></span>
                </button>
            </template>
        </div>
    </div>
</div>
