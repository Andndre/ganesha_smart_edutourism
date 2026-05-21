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
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="bg-surface text-charcoal flex h-dvh flex-col antialiased">

    @include('components.navigation.header', [
        'showBack' => true,
        'backUrl' => url()->previous(),
        'headerTitle' => null,
    ])

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
    </script>

    @stack('scripts')

</body>

</html>
