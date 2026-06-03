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
@endphp

<header
    class="{{ $isMainTab ? 'absolute top-0 left-0 right-0 z-50 pt-[env(safe-area-inset-top)] px-4 mt-4 pointer-events-none' : 'pt-sat bg-primary z-40 shrink-0 px-4 text-white' }}">
    <nav
        class="{{ $isMainTab ? 'bg-white/90 backdrop-blur-md rounded-full shadow-[0_8px_30px_rgba(0,0,0,0.12)] px-5 text-charcoal border border-white pointer-events-auto' : '' }} flex h-14 items-center justify-between">
        @if (Route::is('umkm'))
            <!-- Search bar header for UMKM page -->
            <div class="flex items-center gap-3 w-full">
                <svg class="h-5 w-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <div class="relative flex-1 flex items-center">
                    <input type="text" id="search-input" placeholder="Cari kategori UMKM..."
                        class="text-charcoal w-full bg-transparent text-sm font-medium placeholder-gray-400 outline-none pr-6" />
                    <button type="button" id="clear-search-btn" class="hidden absolute right-0 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <span id="offline-indicator" class="bg-warning hidden rounded-full px-2 py-1 text-xs font-medium shrink-0">
                    Offline
                </span>
            </div>
        @else
            <div class="flex items-center gap-3">
                @if ($showBack && !$isMainTab)
                    <button
                        onclick="if (document.referrer && document.referrer.includes(window.location.host)) { history.back(); } else { window.location.href = '{{ $fallbackUrl }}'; }"
                        class="tap-target -ml-2 flex h-10 w-10 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent p-0 text-white transition-all active:scale-95"
                        aria-label="Kembali">
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
                    <button class="hover:text-primary relative p-1.5 text-gray-500 transition-colors active:scale-95"
                        aria-label="Notifikasi">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <!-- Notification Dot -->
                        <span class="absolute right-2 top-1 flex h-2 w-2">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                        </span>
                    </button>
                @endif
            </div>
        @endif
    </nav>
</header>
