{{-- Header component with optional back arrow and logo support --}}
{{-- Main tab routes show logo: home, explore, ar-scan, umkm, profile --}}
{{-- Other routes show back arrow for navigation --}}

@props([
    'showBack' => true,
    'backUrl' => url()->previous(),
    'headerTitle' => null,
])

@php
    // Main tab routes that should show logo instead of back arrow
    $mainTabRoutes = ['home', 'explore', 'ar-scan', 'umkm', 'profile'];
    $currentRouteName = Route::currentRouteName();
    $isMainTab = in_array($currentRouteName, $mainTabRoutes);
@endphp

<header class="pt-sat bg-primary z-40 shrink-0 px-4 text-white">
    <nav class="flex h-14 items-center justify-between">
        <div class="flex items-center gap-3">
            @if ($showBack && !$isMainTab)
                <a href="{{ $backUrl }}"
                    class="tap-target -ml-2 flex h-10 w-10 items-center justify-center rounded-full transition-all active:scale-95"
                    aria-label="Kembali">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            @if ($isMainTab)
                {{-- Main tabs: show logo --}}
                <img src="{{ asset('icons/logo-wht-notext.png') }}" alt="Penglipuran Logo"
                    class="h-8 w-auto object-contain">
                @if ($headerTitle)
                    <span class="text-label font-semibold">{{ $headerTitle }}</span>
                @endif
            @else
                {{-- Detail pages: show back arrow + title from yield --}}
                <h1 class="text-label font-semibold">@yield('header_title', $headerTitle)</h1>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <span id="offline-indicator" class="bg-warning hidden rounded-full px-2 py-1 text-xs font-medium">
                Offline Mode
            </span>
        </div>
    </nav>
</header>
