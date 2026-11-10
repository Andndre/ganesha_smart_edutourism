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
    $densityButtonTone = match ($sharedDensityStatus['key'] ?? 'safe') {
        'medium' => 'bg-amber-50 text-amber-800 hover:bg-amber-100 focus-visible:ring-amber-700',
        'full' => 'bg-rose-50 text-rose-800 hover:bg-rose-100 focus-visible:ring-rose-700',
        default => 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 focus-visible:ring-emerald-700',
    };
@endphp

<header class="pt-sat relative z-50 shrink-0 border-b border-[#ebe9e4] bg-white px-4 text-charcoal md:px-8 lg:px-0" x-data>
    <div class="mx-auto flex h-16 max-w-7xl items-stretch justify-between gap-3 lg:h-[72px] lg:gap-4 lg:px-2">
        <div class="flex min-w-0 flex-1 items-center gap-3 lg:flex-initial lg:shrink-0 lg:h-full">
            @if ($showBack && !$isMainTab)
                <button
                    onclick="if (window.history.length > 1 && document.referrer && document.referrer.includes(window.location.host) && !document.referrer.includes('/login') && !document.referrer.includes('/auth')) { history.back(); } else { window.location.href = '{{ $fallbackUrl }}'; }"
                    class="-ml-2 flex min-h-11 min-w-11 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent p-0 text-charcoal transition-all active:scale-95 lg:hidden"
                    aria-label="{{ __('Kembali') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            @endif

            @if ($isMainTab)
                @if (Route::is('home'))
                    <span class="leading-tight lg:hidden"><span class="block text-base font-bold tracking-tight">Smart Edutourism</span><span class="mt-1 block text-xs font-normal text-gray-600">{{ __('Desa Penglipuran, Bali') }}</span></span>
                @else
                <img src="{{ asset('icons/logo-penglipuran.png') }}" alt="Penglipuran Logo" width="32" height="32" fetchpriority="high" class="h-8 w-auto object-contain opacity-90 lg:hidden">
                <span class="text-label max-w-37.5 truncate font-bold tracking-tight text-charcoal lg:hidden">
                    @hasSection('header_title')
                        @yield('header_title')
                    @elseif ($headerTitle)
                        {{ $headerTitle }}
                    @endif
                </span>
                @endif
            @else
                <h1 class="text-label truncate font-semibold text-charcoal lg:hidden">@yield('header_title', $headerTitle)</h1>
            @endif

            <a href="{{ route('home') }}" class="hidden h-full shrink-0 items-center gap-3 lg:flex" aria-label="{{ __('Beranda') }} · Smart Edutourism">
                <img src="{{ asset('icons/logo-penglipuran.png') }}" alt="Penglipuran Logo" width="40" height="40" class="h-10 w-10 object-contain">
                <span class="leading-tight">
                    <span class="block text-sm font-bold tracking-tight text-charcoal">Smart Edutourism</span>
                    <span class="mt-0.5 block text-[11px] text-gray-500">{{ __('Desa Penglipuran, Bali') }}</span>
                </span>
            </a>
        </div>

        <nav class="hidden h-full min-w-0 items-stretch justify-center gap-0.5 lg:flex" aria-label="{{ __('Navigasi utama') }}">
            <a href="{{ route('home') }}" @if (Route::is('home')) aria-current="page" @endif class="flex h-full shrink-0 items-center border-b-2 px-1 text-xs whitespace-nowrap transition-colors xl:px-3 xl:text-[13px] {{ Route::is('home') ? 'border-primary font-bold text-primary' : 'border-transparent font-medium text-gray-600 hover:text-primary' }}">{{ __('Beranda') }}</a>
            <a href="{{ route('cultural-objects') }}" @if (Route::is('cultural-*')) aria-current="page" @endif class="flex h-full shrink-0 items-center border-b-2 px-1 text-xs whitespace-nowrap transition-colors xl:px-3 xl:text-[13px] {{ Route::is('cultural-*') ? 'border-primary font-bold text-primary' : 'border-transparent font-medium text-gray-600 hover:text-primary' }}">{{ __('Warisan Budaya') }}</a>
            <a href="{{ route('explore') }}" @if (Route::is('explore')) aria-current="page" @endif class="flex h-full shrink-0 items-center border-b-2 px-1 text-xs whitespace-nowrap transition-colors xl:px-3 xl:text-[13px] {{ Route::is('explore') ? 'border-primary font-bold text-primary' : 'border-transparent font-medium text-gray-600 hover:text-primary' }}">{{ __('Peta Interaktif') }}</a>
            <a href="{{ route('umkm') }}" @if (Route::is('umkm*')) aria-current="page" @endif class="flex h-full shrink-0 items-center border-b-2 px-1 text-xs whitespace-nowrap transition-colors xl:px-3 xl:text-[13px] {{ Route::is('umkm*') ? 'border-primary font-bold text-primary' : 'border-transparent font-medium text-gray-600 hover:text-primary' }}">{{ __('Direktori UMKM') }}</a>
            <a href="{{ route('events') }}" @if (Route::is('events*')) aria-current="page" @endif class="flex h-full shrink-0 items-center border-b-2 px-1 text-xs whitespace-nowrap transition-colors xl:px-3 xl:text-[13px] {{ Route::is('events*') ? 'border-primary font-bold text-primary' : 'border-transparent font-medium text-gray-600 hover:text-primary' }}">{{ __('Kalender Acara') }}</a>
        </nav>

        <div class="flex h-full shrink-0 items-center gap-1 md:gap-2">
                <span id="offline-indicator" class="bg-warning hidden rounded-full px-2 py-1 text-xs font-medium">
                    Offline
                </span>

                <button type="button" @click="$dispatch('open-weather-modal')" @class([
                    'hidden h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-[11px] font-semibold text-amber-800 transition hover:bg-amber-100 active:scale-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-700 md:flex lg:w-auto lg:gap-1.5 lg:rounded-full lg:px-2 xl:h-11 xl:gap-1.5 xl:px-3',
                ]) aria-label="{{ __('Cuaca Desa Penglipuran') }}: {{ $sharedWeather?->getConditionText() ?? __('Cuaca belum tersedia') }}">
                        {!! $sharedWeather?->getIconHtml() ?? '<i class="fa-solid fa-sun" aria-hidden="true"></i>' !!}
                        <span class="hidden lg:inline xl:hidden">{{ $sharedWeather ? round($sharedWeather->temperature).'°C' : '-' }}</span>
                        <span class="hidden xl:inline">{{ $sharedWeather ? round($sharedWeather->temperature).'°C' : '-' }} {{ $sharedWeather?->getConditionText() ?? __('Cuaca belum tersedia') }}</span>
                    </button>
                <button type="button"
                    x-data="villageDensity({
                        totalCurrent: {{ (int) ($sharedTotalCurrent ?? 0) }},
                        totalMax: {{ (int) ($sharedTotalMax ?? 0) }},
                        percent: {{ (int) round($sharedDensityPercent ?? 0) }},
                        statusKey: '{{ $sharedDensityStatus['key'] ?? 'safe' }}',
                        labels: {
                            safe: '{{ __('Aman') }}',
                            medium: '{{ __('Sedang') }}',
                            full: '{{ __('Penuh') }}'
                        },
                        ariaPrefix: '{{ __('Kepadatan Pengunjung') }}'
                    })"
                    @density-updated.window="onDensityUpdated($event.detail)"
                    @click="$dispatch('open-density-modal')"
                    @class([
                        'hidden h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[11px] font-semibold transition active:scale-95 focus-visible:outline-none focus-visible:ring-2 md:flex lg:w-auto lg:gap-1.5 lg:rounded-full lg:px-2 xl:h-11 xl:gap-1.5 xl:px-3',
                        $densityButtonTone,
                    ])
                    :class="headerButtonTone"
                    aria-label="{{ __('Kepadatan Pengunjung') }}: {{ __($sharedDensityStatus['label']) }}, {{ round($sharedDensityPercent) }}%"
                    :aria-label="ariaLabel">
                    <x-density-status-icon :status="$sharedDensityStatus['key']" class="h-5 w-5" />
                    <span class="hidden lg:inline xl:hidden" x-text="percent + '%'">{{ round($sharedDensityPercent) }}%</span>
                    <span class="hidden xl:inline" x-text="label + ' (' + percent + '%)'">{{ __($sharedDensityStatus['label']) }} ({{ round($sharedDensityPercent) }}%)</span>
                </button>

                <div class="static sm:relative" x-data="notificationBell()" @notification-received.window="onNewNotification($event.detail)">
                    <button @click="toggle()" class="tap-target hover:text-primary relative p-2.5 text-gray-500 transition-colors active:scale-95"
                        aria-label="{{ __('Notifikasi') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        {{-- Notification Badge --}}
                        <span x-show="unreadCount > 0" x-transition
                              class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[11px] font-bold leading-none text-white">
                            <span x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                        </span>
                    </button>

                    @include('components.notification-panel')
                </div>

                @if ($isVisited)
                    <a href="{{ route('favorites') }}"
                        class="hover:text-primary relative flex items-center gap-1.5 p-2.5 text-gray-500 transition-colors active:scale-95"
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
                            class="tap-target hover:text-primary relative flex items-center justify-center gap-1.5 p-2.5 text-gray-500 transition-colors active:scale-95"
                            aria-label="{{ __('Ganti Bahasa') }}">
                        <x-flag-icon :locale="app()->getLocale()" class="h-6 w-8 ring-1 ring-black/10" />
                        {{-- ponytail: bendera itu warna doang; label teks bikin locale aktif kebaca tanpa lihat warna --}}
                        <span class="text-xs font-bold uppercase">{{ app()->getLocale() }}</span>
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
                               :class="currentLocale === 'id' ? 'text-primary' : 'text-gray-700'">
                                <x-flag-icon locale="id" class="h-4 w-5 ring-1 ring-black/10" />
                                <span>Bahasa Indonesia</span>
                                <span x-show="currentLocale === 'id'" class="ml-auto">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                            </a>
                            <a href="{{ route('lang.switch', 'en') }}"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium transition-colors hover:bg-gray-50 whitespace-nowrap"
                               :class="currentLocale === 'en' ? 'text-primary' : 'text-gray-700'">
                                <x-flag-icon locale="en" class="h-4 w-5 ring-1 ring-black/10" />
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

                <a href="{{ auth()->check() ? route('profile') : route('login') }}" class="border-secondary bg-accent/35 text-primary hover:bg-accent/55 hidden h-9 w-9 shrink-0 items-center justify-center rounded-full border text-sm font-bold transition lg:flex" aria-label="{{ auth()->check() ? __('Profil') : __('Masuk') }}">
                    @auth
                        {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    @else
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 0 0-16 0m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                    @endauth
                </a>
            </div>
    </div>
</header>
