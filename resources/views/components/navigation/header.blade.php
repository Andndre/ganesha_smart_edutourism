{{-- Header component with optional back arrow and logo support --}}
{{-- Main tab routes show logo: home, explore, ar-scan, umkm, profile --}}
{{-- Other routes show back arrow for navigation --}}

@props([
    'showBack' => true,
    'fallbackUrl' => route('home'),
    'headerTitle' => null,
])

@php
    // Main tab routes that should show logo instead of back arrow
    $mainTabRoutes = ['home', 'explore', 'ar-scan', 'umkm', 'profile'];
    $currentRouteName = Route::currentRouteName();
    $isMainTab = in_array($currentRouteName, $mainTabRoutes);
    $isVisited = Route::is('visited');
@endphp

<header
    class="{{ $isMainTab ? 'absolute top-0 left-0 right-0 z-50 pt-[env(safe-area-inset-top)] px-4 md:px-8 mt-4 pointer-events-none' : 'pt-sat bg-primary z-40 shrink-0 px-4 md:px-8 text-white' }}">
    <nav
        class="{{ $isMainTab ? 'bg-white/90 backdrop-blur-md rounded-full shadow-[0_8px_30px_rgba(0,0,0,0.12)] px-5 text-charcoal border border-white pointer-events-auto' : '' }} flex h-14 items-center justify-between">
        @if (Route::is('umkm'))
            <!-- Search bar header for UMKM page -->
            <div class="flex items-center gap-3 w-full relative"
                 x-data="{
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
                 }"
                 x-on:click.outside="open = false"
                 x-on:keydown.escape.window="open = false">
                <svg class="h-5 w-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <div class="relative flex-1 flex items-center">
                    <input type="text" id="search-input" x-ref="searchInput" placeholder="{{ __('Cari produk atau toko UMKM...') }}"
                        x-model.debounce.300ms="query"
                        x-on:input="fetchResults"
                        x-on:focus="if (query.length >= 2) open = true"
                        class="text-charcoal w-full bg-transparent text-sm font-medium placeholder-gray-400 outline-none pr-6" />
                    <button type="button" 
                        x-show="query.length > 0"
                        x-cloak
                        @click="query = ''; results = { umkms: [], products: [], categories: [] }; open = false; $nextTick(() => { $refs.searchInput.dispatchEvent(new Event('input')); })"
                        class="absolute right-0 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <span id="offline-indicator" class="bg-warning hidden rounded-full px-2 py-1 text-xs font-medium shrink-0">
                    Offline
                </span>

                {{-- Dropdown results --}}
                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="translate-y-1 opacity-0"
                    x-transition:enter-end="translate-y-0 opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-y-0 opacity-100"
                    x-transition:leave-end="translate-y-1 opacity-0"
                    class="absolute left-0 right-0 top-full z-50 mt-4 max-h-96 overflow-y-auto rounded-2xl border border-gray-200 bg-white/95 shadow-xl backdrop-blur-md">

                    {{-- Loading inside dropdown --}}
                    <div x-show="loading" x-cloak class="flex items-center justify-center gap-2 px-4 py-8 text-sm text-gray-500">
                        <svg class="h-4 w-4 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        <span>{{ __('Mencari...') }}</span>
                    </div>

                    {{-- Results content (hidden while loading) --}}
                    <div x-show="!loading" x-cloak>
                        {{-- UMKM section --}}
                        <template x-if="results.umkms.length > 0">
                            <div>
                                <div class="sticky top-0 bg-gray-50/90 px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 backdrop-blur-sm">
                                    {{ __('UMKM') }}
                                </div>
                                <template x-for="umkm in results.umkms" :key="'umkm-' + umkm.id">
                                    <a :href="'/umkm/store/' + umkm.id"
                                        class="flex min-h-11 items-center gap-3 px-4 py-3 text-sm transition-colors hover:bg-primary/4 active:bg-primary/8">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/8 text-primary">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate font-medium text-gray-900" x-text="umkm.business_name"></p>
                                        </div>
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </template>
                            </div>
                        </template>

                        {{-- Products section --}}
                        <template x-if="results.products.length > 0">
                            <div>
                                <div class="sticky top-0 bg-gray-50/90 px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 backdrop-blur-sm">
                                    {{ __('Produk') }}
                                </div>
                                <template x-for="product in results.products" :key="'prod-' + product.id">
                                    <a :href="'/umkm/store/' + product.umkm_profile_id"
                                        class="flex min-h-11 items-center gap-3 px-4 py-3 text-sm transition-colors hover:bg-primary/4 active:bg-primary/8">
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
                                            <p x-show="product.umkm_business_name" class="truncate text-xs text-gray-500" x-text="product.umkm_business_name"></p>
                                        </div>
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </template>
                            </div>
                        </template>

                        {{-- Categories section --}}
                        <template x-if="results.categories.length > 0">
                            <div>
                                <div class="sticky top-0 bg-gray-50/90 px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 backdrop-blur-sm">
                                    {{ __('Kategori') }}
                                </div>
                                <template x-for="cat in results.categories" :key="'cat-' + cat.id">
                                    <button type="button"
                                        @click="
                                            window.dispatchEvent(new CustomEvent('umkm-search-category', { detail: { id: cat.id, name: cat.name } }));
                                            open = false;
                                        "
                                        class="flex w-full min-h-11 items-center gap-3 px-4 py-3 text-sm text-left transition-colors hover:bg-primary/4 active:bg-primary/8">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate font-medium text-gray-900" x-text="cat.name"></p>
                                        </div>
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- No results state --}}
                        <div
                            x-show="query.length >= 2 && totalResults() === 0"
                            x-cloak
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
        @else
            <div class="flex items-center gap-3">
                @if ($showBack && !$isMainTab)
                    <button
                        onclick="if (document.referrer && document.referrer.includes(window.location.host)) { history.back(); } else { window.location.href = '{{ $fallbackUrl }}'; }"
                        class="tap-target -ml-2 flex h-10 w-10 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent p-0 text-white transition-all active:scale-95"
                        aria-label="{{ __('Kembali') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                @endif

                @if ($isMainTab)
                    {{-- Main tabs: show logo (dark variant for pill) --}}
                    <img src="{{ asset('icons/logo-color-notext.png') }}" alt="Penglipuran Logo"
                        class="h-8 w-auto object-contain opacity-90">
                    <span class="text-label text-charcoal max-w-37.5 truncate font-bold tracking-tight">
                        @hasSection('header_title')
                            @yield('header_title')
                        @elseif ($headerTitle)
                            {{ $headerTitle }}
                        @endif
                    </span>
                @else
                    {{-- Detail pages: show back arrow + title from yield --}}
                    <h1 class="text-label font-semibold">@yield('header_title', $headerTitle)</h1>
                @endif
            </div>

            <div class="flex items-center gap-1">
                <span id="offline-indicator" class="bg-warning hidden rounded-full px-2 py-1 text-xs font-medium">
                    Offline
                </span>

                @if ($isMainTab)
                    <div class="relative" x-data="notificationBell()" @notification-received.window="onNewNotification($event.detail)">
                        <button @click="toggle()" class="tap-target hover:text-primary relative p-2.5 text-gray-500 transition-colors active:scale-95"
                            aria-label="{{ __('Notifikasi') }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            {{-- Notification Badge --}}
                            <span x-show="unreadCount > 0" x-transition
                                  class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white">
                                <span x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                            </span>
                        </button>

                        @include('components.notification-panel')
                    </div>
                @endif

                @if ($isVisited)
                    <a href="{{ route('favorites') }}"
                        class="hover:text-white/80 relative flex items-center gap-1.5 p-2.5 text-white transition-colors active:scale-95"
                        aria-label="{{ __('Favorit Saya') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        <span class="hidden md:inline text-sm font-medium">{{ __('Favorit') }}</span>
                    </a>
                @endif

                {{-- Language Switcher --}}
                <div class="relative" x-data="{ langOpen: false, currentLocale: '{{ app()->getLocale() }}' }">
                    <button @click="langOpen = !langOpen"
                            class="tap-target hover:text-primary relative p-2.5 {{ $isMainTab ? 'text-gray-500' : 'text-white/80 hover:text-white' }} transition-colors active:scale-95"
                            aria-label="{{ __('Ganti Bahasa') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 14v1c0 1.1.9 2 2 2v1h2v2.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V6h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </button>

                    <div x-show="langOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         @click.outside="langOpen = false"
                         class="absolute right-0 top-full mt-2 w-max min-w-40 overflow-hidden rounded-2xl border border-gray-100/80 bg-white/95 shadow-2xl backdrop-blur-md z-50"
                         style="display: none;">
                        <div class="py-1">
                            <a href="{{ route('lang.switch', 'id') }}"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium transition-colors hover:bg-gray-50 whitespace-nowrap"
                               :class="currentLocale === 'id' ? 'text-[#1E5128]' : 'text-gray-700'">
                                <span>Bahasa Indonesia</span>
                                <span x-show="currentLocale === 'id'" class="ml-auto">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                            </a>
                            <a href="{{ route('lang.switch', 'en') }}"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium transition-colors hover:bg-gray-50 whitespace-nowrap"
                               :class="currentLocale === 'en' ? 'text-[#1E5128]' : 'text-gray-700'">
                                <span>English</span>
                                <span x-show="currentLocale === 'en'" class="ml-auto">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </nav>
</header>
