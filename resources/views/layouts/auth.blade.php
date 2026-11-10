<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#FAF9F6">
    <meta name="mobile-web-app-capable" content="yes">

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Penglipuran">

    <title>@yield('title', __('Penglipuran Smart Tour'))</title>

    {{-- Shortcut icon --}}
    <link rel="shortcut icon" href="{{ asset('icons/logo-penglipuran-shortcut.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

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
        }
    </style>

    <script>
        window.Laravel = {
            reverbKey: "{{ config('broadcasting.connections.reverb.key') }}",
            reverbHost: "{{ config('broadcasting.connections.reverb.options.host') }}",
            reverbPort: "{{ config('broadcasting.connections.reverb.options.port', 8081) }}"
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="v7-auth bg-surface text-charcoal flex h-dvh flex-col antialiased">

    <main id="main-content" class="no-scrollbar pb-sab relative flex-1 overflow-y-auto">
        @yield('content')
    </main>

    @stack('scripts')

    @include('components.session-state-sync')

    @include('components.cookie-consent')

</body>

</html>
