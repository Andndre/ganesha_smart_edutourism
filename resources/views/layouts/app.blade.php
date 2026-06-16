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

    @stack('styles')
</head>

<body class="flex h-dvh items-center justify-center overflow-hidden bg-gray-50 antialiased">

    <div class="bg-surface text-charcoal relative flex h-full w-full flex-col md:flex-row-reverse overflow-hidden"
        style="transform: translateZ(0);">

        <!-- Content Area Wrapper -->
        <div class="relative flex h-full w-full flex-1 flex-col overflow-hidden">

        <!-- Global Offline Coaching Modal -->
        <div id="offline-coaching-modal"
            class="bg-charcoal/40 pointer-events-none fixed inset-0 flex scale-95 items-center justify-center opacity-0 backdrop-blur-sm transition-all duration-300"
            style="z-index: 999999;">
            <div
                class="flex w-[90%] max-w-sm flex-col items-center rounded-2xl border border-gray-100 bg-white p-6 text-center shadow-2xl">
                <!-- Icon -->
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-[#E28F1B]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                    </svg>
                </div>

                <!-- Title -->
                <h3 class="text-charcoal mb-2 text-base font-bold">Anda Sedang Luring (Offline)</h3>

                <!-- Description -->
                <p class="mb-6 text-xs leading-relaxed text-gray-500">
                    Koneksi internet Anda terputus. Jangan khawatir, Anda tetap dapat menjelajahi desa melalui data
                    luring (cache).
                    <br><br>
                    Perhatikan indikator <span
                        class="rounded-full bg-[#E28F1B] px-2.5 py-0.5 font-bold text-white">Offline</span> di pojok
                    kanan atas sebagai penanda status koneksi Anda.
                </p>

                <!-- Action Button -->
                <button id="close-offline-coaching-btn"
                    class="bg-primary w-full rounded-xl px-6 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:bg-[#152E1D] active:scale-95">
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
            $mainTabRoutes = ['home', 'explore', 'umkm', 'profile'];
            $currentRouteName = Route::currentRouteName();
            $isMainTab = in_array($currentRouteName, $mainTabRoutes);
            $hasActiveSession = isset($activeEdutourismSession) && !Route::is('edutourism.active');
        @endphp

        <main id="main-content"
            class="no-scrollbar {{ $isMainTab ? ($hasActiveSession ? 'pb-48' : 'pb-24') : ($hasActiveSession ? 'pb-28' : 'pb-6') }} relative flex-1 overflow-y-auto">
            @yield('content')
        </main>

        @if ($hasActiveSession)
            <div class="{{ $isMainTab ? 'bottom-[calc(env(safe-area-inset-bottom)+4rem)] md:bottom-0' : 'bottom-0' }} fixed md:absolute left-0 right-0 z-40 border-t border-gray-200 bg-white/80 px-4 py-3 backdrop-blur-md"
                id="floating-route-banner">
                <a href="{{ route('edutourism.active') }}"
                    class="pointer-events-auto flex items-center justify-between overflow-hidden rounded-2xl bg-[#1E5128] shadow-lg shadow-[#1E5128]/30 transition-transform active:scale-95">
                    <div class="flex items-center gap-3 p-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20 text-white backdrop-blur-sm">
                            <svg class="h-6 w-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-green-100">Smart Edutourism
                                Aktif</p>
                            <h4 class="max-w-[150px] truncate font-bold leading-tight text-white sm:max-w-[200px]">
                                {{ $activeEdutourismSession->tourRoute->name }}</h4>
                        </div>
                    </div>
                    <div class="flex items-center justify-center border-l border-white/10 bg-black/10 px-4 py-5">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            </div>
        @endif
        
        </div> <!-- End of Content Area Wrapper -->

        @if ($isMainTab)
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
                                // Append to body so it stays at the top of the viewport
                                document.body.appendChild(loadingBar);
                            }

                            // Reset loading bar state
                            loadingBar.classList.remove('finished');
                            loadingBar.style.width = '0%';

                            // Force reflow
                            void loadingBar.offsetWidth;

                            // Progressively animate
                            loadingBar.style.width = '20%';
                            setTimeout(() => {
                                loadingBar.style.width = '60%';
                            }, 150);
                            setTimeout(() => {
                                loadingBar.style.width = '85%';
                            }, 400);

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

        {{-- Eruda Mobile DevTools for local environment --}}
        @env('local')
            <script src="https://cdn.jsdelivr.net/npm/eruda"></script>
            <script>
                eruda.init();
            </script>
        @endenv

        <script>
            // Global GPS Tracking for Reverb Heatmap
            (function() {
                // Only run tracking if geolocation is supported
                if (!navigator.geolocation) return;

                // Generate a persistent session ID for tracking
                let sessionId = localStorage.getItem('gps_session_id');
                if (!sessionId) {
                    sessionId = crypto.randomUUID ? crypto.randomUUID() : 'session-' + Math.random().toString(36).substr(2,
                        9);
                    localStorage.setItem('gps_session_id', sessionId);
                }

                let lastKnownPos = null;
                
                // Keep track of the latest position
                navigator.geolocation.watchPosition(
                    (pos) => {
                        lastKnownPos = {
                            latitude: pos.coords.latitude,
                            longitude: pos.coords.longitude
                        };
                    },
                    (err) => {
                        console.debug("Background GPS tracking error:", err.message);
                    }, {
                        enableHighAccuracy: true,
                        maximumAge: 1000,
                        timeout: 10000
                    }
                );
                
                // Ping the server every 10 seconds with the last known position
                setInterval(() => {
                    if (lastKnownPos) {
                        fetch('/api/tracking/ping', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            },
                            body: JSON.stringify({
                                latitude: lastKnownPos.latitude,
                                longitude: lastKnownPos.longitude,
                                session_id: sessionId
                            })
                        }).catch(() => { /* silent fail for tracking */ });
                    }
                }, 10000);
            })();
        </script>

    </div>
</body>

</html>
