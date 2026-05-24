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

<body class="bg-surface text-charcoal flex h-dvh flex-col antialiased">

    @unless (Route::is('explore'))
        @include('components.navigation.header', [
            'showBack' => true,
            'headerTitle' => null,
        ])
    @endunless

    <main id="main-content" class="no-scrollbar relative flex-1 overflow-y-auto pb-24">
        @yield('content')
    </main>

    @include('components.navigation.bottom-nav')

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
    </script>

    @stack('scripts')

</body>

</html>
