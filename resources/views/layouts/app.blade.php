<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1E5128">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Dynamic Viewport Height for Mobile -->
    <style>
        :root {
            --sat: env(safe-area-inset-top);
            --sab: env(safe-area-inset-bottom);
            --sal: env(safe-area-inset-left);
            --sar: env(safe-area-inset-right);
        }

        html,
        body {
            height: 100dvh;
            /* Dynamic viewport height */
            overflow: hidden;
        }

        /* Prevent pull-to-refresh on mobile */
        body {
            overscroll-behavior: none;
        }
    </style>

    <!-- Dark Mode Support -->
    <script>
        if (
            localStorage.getItem("theme") === "dark" ||
            (!("theme" in localStorage) &&
                window.matchMedia("(prefers-color-scheme: dark)").matches)
        ) {
            document.documentElement.classList.add("dark");
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-surface dark:bg-dark-bg text-charcoal dark:text-dark-sand antialiased">
    <!-- Top Bar -->
    <header class="pt-sat bg-surface/80 dark:bg-dark-bg/80 sticky top-0 z-40 px-4 backdrop-blur-md">
        <nav class="flex h-14 items-center justify-between">
            <button @click="history.back()" class="tap-target -ml-2 p-2">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h1 class="text-label font-semibold">@yield('title', 'Penglipuran')</h1>
            <div class="flex items-center gap-2">
                <!-- Offline indicator -->
                <span id="offline-indicator" class="text-warning hidden text-xs">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M3.172a5 5 0 017.072 0l.548 3.564a1 1 0 00.812 1.272l2.551.433a5 5 0 014.714 4.714l.433 2.551a1 1 0 001.272.812l3.564.548A5 5 0 0110 14.828v1.172a3 3 0 01-6 0v-3.344a1 1 0 01.658-.98l2.343-.391a4 4 0 002.83-2.814l-.195-.977a4 4 0 00-1.245-1.245l-.977-.195a4 4 0 00-2.814-.83l-.391.2a3 3 0 01-.98.658V3a3 3 0 010-6z"
                            clip-rule="evenodd" />
                    </svg>
                </span>
            </div>
        </nav>
    </header>

    <!-- Main Content Area -->
    <main id="main-content" class="h-[calc(100dvh-7rem)] overflow-y-auto pb-20">
        @yield('content')
    </main>

    <!-- Bottom Navigation -->
    @include('components.navigation.bottom-nav')

    <!-- Offline Banner -->
    <div id="offline-banner"
        class="bg-warning fixed bottom-20 left-0 right-0 hidden py-2 text-center text-xs text-white">
        Anda sedang offline. Data peta tetap dapat diakses.
    </div>
</body>

</html>
