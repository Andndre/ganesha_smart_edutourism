<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="#FAF9F6">
    <meta name="mobile-web-app-capable" content="yes">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Penglipuran">

    <title>@yield('title', 'Penglipuran Smart Tour')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Shortcut icon --}}
    <link rel="shortcut icon" href="{{ asset('icons/logo-color-notext-shortcut.ico') }}">

    <style>
        :root {
            --sat: env(safe-area-inset-top);
            --sab: env(safe-area-inset-bottom);
        }

        html,
        body {
            height: 100dvh;
            overflow: hidden;
            overscroll-behavior: none;
            /* Mencegah pull-to-refresh bawaan browser */
        }

        /* Top Loading Bar */
        #global-loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background-color: #1E5128;
            z-index: 9999;
            width: 0%;
            opacity: 1;
            transition: width 0.4s cubic-bezier(0.1, 0.8, 0.3, 1), opacity 0.2s ease-in-out;
            pointer-events: none;
        }

        #global-loading-bar.finished {
            width: 100% !important;
            opacity: 0;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('styles')
</head>

<body class="bg-surface text-charcoal flex h-dvh flex-col antialiased">

    <!-- Global Offline Coaching Modal -->
    <div id="offline-coaching-modal" class="fixed inset-0 flex items-center justify-center bg-charcoal/40 backdrop-blur-sm transition-all duration-300 pointer-events-none opacity-0 scale-95" style="z-index: 999999;">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-[90%] shadow-2xl border border-gray-100 flex flex-col items-center text-center">
            <!-- Icon -->
            <div class="w-12 h-12 rounded-full bg-amber-50 text-[#E28F1B] flex items-center justify-center mb-4">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                </svg>
            </div>
            
            <!-- Title -->
            <h3 class="text-charcoal text-base font-bold mb-2">Anda Sedang Luring (Offline)</h3>
            
            <!-- Description -->
            <p class="text-gray-500 text-xs leading-relaxed mb-6">
                Koneksi internet Anda terputus. Jangan khawatir, Anda tetap dapat menjelajahi desa melalui data luring (cache).
                <br><br>
                Perhatikan indikator <span class="bg-[#E28F1B] text-white rounded-full px-2.5 py-0.5 font-bold">Offline</span> di pojok kanan atas sebagai penanda status koneksi Anda.
            </p>
            
            <!-- Action Button -->
            <button id="close-offline-coaching-btn" class="bg-primary hover:bg-[#152E1D] text-white font-semibold text-sm py-2.5 px-6 rounded-xl w-full transition-all duration-200 active:scale-95 shadow-md">
                Baik, Saya Mengerti
            </button>
        </div>
    </div>

    @unless (Route::is('explore') || Route::is('edutourism.active'))
        @include('components.navigation.header', [
            'showBack' => true,
            'headerTitle' => null,
        ])
    @endunless

    @php
        $mainTabRoutes = ['home', 'explore', 'ar-scan', 'umkm', 'profile'];
        $currentRouteName = Route::currentRouteName();
        $isMainTab = in_array($currentRouteName, $mainTabRoutes);
        $hasActiveSession = isset($activeEdutourismSession) && !Route::is('edutourism.active');
    @endphp

    <main id="main-content" class="no-scrollbar relative flex-1 overflow-y-auto {{ $isMainTab ? ($hasActiveSession ? 'pb-48' : 'pb-24') : ($hasActiveSession ? 'pb-28' : 'pb-6') }}">
        @yield('content')
    </main>

    @if($hasActiveSession)
        <div class="fixed left-0 right-0 z-40 {{ $isMainTab ? 'bottom-[calc(env(safe-area-inset-bottom)+4rem)]' : 'bottom-0' }} bg-white/80 backdrop-blur-md border-t border-gray-200 px-4 py-3" id="floating-route-banner">
            <a href="{{ route('edutourism.active') }}" class="pointer-events-auto flex items-center justify-between overflow-hidden rounded-2xl bg-[#1E5128] shadow-lg shadow-[#1E5128]/30 transition-transform active:scale-95">
                <div class="flex items-center gap-3 p-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20 text-white backdrop-blur-sm">
                        <svg class="h-6 w-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-green-100">Smart Edutourism Aktif</p>
                        <h4 class="font-bold text-white leading-tight truncate max-w-[150px] sm:max-w-[200px]">{{ $activeEdutourismSession->tourRoute->name }}</h4>
                    </div>
                </div>
                <div class="bg-black/10 px-4 py-5 flex items-center justify-center border-l border-white/10">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
        </div>
    @endif

    @if($isMainTab)
        @include('components.navigation.bottom-nav')
    @endif

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker terdaftar!', reg.scope))
                    .catch(err => console.error('Pendaftaran Service Worker gagal:', err));
            });
        }

        // Instant visual feedback for Bottom Nav and loading bar
        document.addEventListener('DOMContentLoaded', () => {
            const bottomNavLinks = document.querySelectorAll('nav a');
            
            bottomNavLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    // Only process normal clicks on same-origin pages (excluding AR scan which goes to camera)
                    const isSameOrigin = link.host === window.location.host;
                    const isARScan = link.pathname.includes('/ar-scan');
                    
                    if (e.button === 0 && isSameOrigin && !isARScan && !e.metaKey && !e.ctrlKey) {
                        // 1. Instantly swap active style on bottom nav tabs (0ms feedback)
                        bottomNavLinks.forEach(l => {
                            l.classList.remove('text-primary');
                            l.classList.add('text-gray-400', 'hover:text-gray-600');
                            const svg = l.querySelector('svg');
                            if (svg) {
                                svg.setAttribute('stroke-width', '2');
                            }
                        });
                        
                        link.classList.remove('text-gray-400', 'hover:text-gray-600');
                        link.classList.add('text-primary');
                        const svg = link.querySelector('svg');
                        if (svg) {
                            svg.setAttribute('stroke-width', '2.5');
                        }

                        // 2. Trigger loading bar animation
                        let loadingBar = document.getElementById('global-loading-bar');
                        if (!loadingBar) {
                            loadingBar = document.createElement('div');
                            loadingBar.id = 'global-loading-bar';
                            document.body.appendChild(loadingBar);
                        }
                        
                        // Reset loading bar state
                        loadingBar.classList.remove('finished');
                        loadingBar.style.width = '0%';
                        
                        // Force reflow
                        void loadingBar.offsetWidth;
                        
                        // Progressively animate
                        loadingBar.style.width = '20%';
                        setTimeout(() => { loadingBar.style.width = '60%'; }, 150);
                        setTimeout(() => { loadingBar.style.width = '85%'; }, 400);

                        // 3. Smooth main content fade
                        const mainContent = document.getElementById('main-content');
                        if (mainContent) {
                            mainContent.style.opacity = '0.5';
                            mainContent.style.transition = 'opacity 0.2s ease';
                        }
                    }
                });
            });
        });

        // Online/Offline status and Coaching Modal detection
        function updateOnlineStatus() {
            const localIndicator = document.getElementById('offline-indicator');
            const coachingModal = document.getElementById('offline-coaching-modal');
            const hasSeenPopup = localStorage.getItem('has_seen_offline_popup') === 'true';
            
            if (navigator.onLine) {
                // User is Online: hide indicator
                if (localIndicator) {
                    localIndicator.classList.add('hidden');
                }
                // Close modal if open (in case they reconnect)
                if (coachingModal) {
                    coachingModal.classList.remove('opacity-100', 'pointer-events-auto', 'scale-100');
                    coachingModal.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
                }
            } else {
                // User is Offline: show indicator
                if (localIndicator) {
                    localIndicator.classList.remove('hidden');
                }
                // Show coaching modal if they haven't seen it yet
                if (coachingModal && !hasSeenPopup) {
                    coachingModal.classList.remove('opacity-0', 'pointer-events-none', 'scale-95');
                    coachingModal.classList.add('opacity-100', 'pointer-events-auto', 'scale-100');
                }
            }
        }

        // Handle closing the coaching modal and setting persistent preference
        document.addEventListener('DOMContentLoaded', () => {
            const closeBtn = document.getElementById('close-offline-coaching-btn');
            const coachingModal = document.getElementById('offline-coaching-modal');
            
            if (closeBtn && coachingModal) {
                closeBtn.addEventListener('click', () => {
                    // Hide modal with animation
                    coachingModal.classList.remove('opacity-100', 'pointer-events-auto', 'scale-100');
                    coachingModal.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
                    // Store flag in localStorage permanently
                    localStorage.setItem('has_seen_offline_popup', 'true');
                });
            }
            
            // Run check on DOMContentLoaded
            updateOnlineStatus();
        });

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
    </script>

    @stack('modals')
    @stack('scripts')

</body>

</html>
