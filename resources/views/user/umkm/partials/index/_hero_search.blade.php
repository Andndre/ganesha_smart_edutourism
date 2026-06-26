{{-- Hero Omni-Search: Alpine.js live search with AJAX dropdown --}}
<div x-data="{
    query: '',
    results: { umkms: [], products: [], categories: [] },
    open: false,
    loading: false,

    fetchResults() {
        if (this.query.length < 2) {
            this.open = false;
            this.loading = false;
            return;
        }

        this.loading = true;

        fetch('/umkm/api-search?q=' + encodeURIComponent(this.query))
            .then(r => r.json())
            .then(d => {
                this.results = d;
                this.open = true;
                this.loading = false;
            })
            .catch(() => {
                this.loading = false;
            });
    },

    totalResults() {
        return this.results.umkms.length + this.results.products.length + this.results.categories.length;
    }
}" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false"
    class="relative mb-6">

    {{-- Search input wrapper --}}
    <div class="relative">
        {{-- Search icon --}}
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        {{-- Loading spinner --}}
        <div x-show="loading" x-cloak class="absolute inset-y-0 right-0 flex items-center pr-4">
            <svg class="text-primary h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
        </div>

        <input type="text" x-model.debounce.300ms="query" x-on:input="fetchResults"
            placeholder="{{ __('Cari produk atau toko UMKM...') }}"
            class="focus:border-primary focus:ring-primary/20 w-full rounded-xl border border-gray-200 bg-white px-5 py-4 pl-12 text-base shadow-sm transition-all placeholder:text-gray-400 focus:outline-none focus:ring-2"
            role="combobox" aria-expanded="false" aria-haspopup="listbox"
            aria-label="{{ __('Cari produk atau toko UMKM...') }}">
    </div>

    {{-- Dropdown results --}}
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-1 opacity-0"
        class="absolute left-0 right-0 top-full z-50 mt-2 max-h-96 overflow-y-auto rounded-xl border border-gray-200 bg-white/80 shadow-lg backdrop-blur-md">

        {{-- Loading inside dropdown --}}
        <div x-show="loading" x-cloak class="flex items-center justify-center gap-2 px-4 py-8 text-sm text-gray-500">
            <svg class="text-primary h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <span>{{ __('Mencari...') }}</span>
        </div>

        {{-- Results content (hidden while loading) --}}
        <div x-show="!loading" x-cloak>
            {{-- UMKM section --}}
            <template x-if="results.umkms.length > 0">
                <div>
                    <div
                        class="sticky top-0 bg-gray-50/90 px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 backdrop-blur-sm">
                        {{ __('UMKM') }}
                    </div>
                    <template x-for="umkm in results.umkms" :key="'umkm-' + umkm.id">
                        <a :href="'/umkm/store/' + umkm.id"
                            class="hover:bg-primary/4 active:bg-primary/8 flex min-h-11 items-center gap-3 px-4 py-3 text-sm transition-colors">
                            <div
                                class="bg-primary/8 text-primary flex h-8 w-8 shrink-0 items-center justify-center rounded-full">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-gray-900" x-text="umkm.business_name"></p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </template>
                </div>
            </template>

            {{-- Products section --}}
            <template x-if="results.products.length > 0">
                <div>
                    <div
                        class="sticky top-0 bg-gray-50/90 px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 backdrop-blur-sm">
                        {{ __('Produk') }}
                    </div>
                    <template x-for="product in results.products" :key="'prod-' + product.id">
                        <a :href="'/umkm/store/' + product.umkm_profile_id"
                            class="hover:bg-primary/[0.04] active:bg-primary/[0.08] flex min-h-11 items-center gap-3 px-4 py-3 text-sm transition-colors">
                            <div x-show="product.image_path"
                                class="h-8 w-8 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                <img :src="'/storage/' + product.image_path" :alt="product.name"
                                    class="h-full w-full object-cover">
                            </div>
                            <div x-show="!product.image_path"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-gray-900" x-text="product.name"></p>
                                <p x-show="product.umkm_business_name" class="truncate text-xs text-gray-500"
                                    x-text="product.umkm_business_name"></p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </template>
                </div>
            </template>

            {{-- Categories section --}}
            <template x-if="results.categories.length > 0">
                <div>
                    <div
                        class="sticky top-0 bg-gray-50/90 px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 backdrop-blur-sm">
                        {{ __('Kategori') }}
                    </div>
                    <template x-for="cat in results.categories" :key="'cat-' + cat.id">
                        <button type="button"
                            @click="
                                // Suggest filter by this category — set tab to 'smart-route' (client-side)
                                window.dispatchEvent(new CustomEvent('umkm-search-category', { detail: { id: cat.id, name: cat.name } }));
                                open = false;
                            "
                            class="hover:bg-primary/[0.04] active:bg-primary/[0.08] flex min-h-11 w-full items-center gap-3 px-4 py-3 text-left text-sm transition-colors">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-gray-900" x-text="cat.name"></p>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </template>
                </div>
            </template>

            {{-- No results state --}}
            <div x-show="query.length >= 2 && totalResults() === 0" x-cloak
                class="flex flex-col items-center px-4 py-10 text-center">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700">{{ __('Hasil tidak ditemukan') }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ __('Coba gunakan kata kunci lain.') }}</p>
            </div>
        </div>
    </div>
</div>
