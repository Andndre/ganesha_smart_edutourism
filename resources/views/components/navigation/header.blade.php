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

<header class="{{ $isMainTab ? 'absolute top-0 left-0 right-0 z-50 pt-[env(safe-area-inset-top)] px-4 mt-4 pointer-events-none' : 'pt-sat bg-primary z-40 shrink-0 px-4 text-white' }}">
    <nav class="flex h-14 items-center justify-between {{ $isMainTab ? 'bg-white/90 backdrop-blur-md rounded-full shadow-[0_8px_30px_rgba(0,0,0,0.12)] px-5 text-charcoal border border-white pointer-events-auto' : '' }}">
        <div class="flex items-center gap-3">
            @if ($showBack && !$isMainTab)
                <button onclick="if (document.referrer && document.referrer.includes(window.location.host)) { history.back(); } else { window.location.href = '{{ $fallbackUrl }}'; }"
                    class="tap-target -ml-2 flex h-10 w-10 items-center justify-center rounded-full transition-all active:scale-95 cursor-pointer bg-transparent border-0 p-0 text-white"
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
                <span class="text-label font-bold tracking-tight text-charcoal truncate max-w-[150px]">
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
                <button class="relative p-1.5 text-gray-500 hover:text-primary transition-colors active:scale-95" aria-label="Notifikasi">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <!-- Notification Dot -->
                    <span class="absolute top-1 right-2 flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                </button>
            @endif
        </div>
    </nav>
</header>
