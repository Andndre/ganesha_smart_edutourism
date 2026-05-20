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
        }

        html,
        body {
            height: 100dvh;
            overflow: hidden;
        }

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
    <!-- Minimal Header -->
    <header class="pt-sat bg-surface/80 dark:bg-dark-bg/80 sticky top-0 z-40 px-4 backdrop-blur-md">
        <div class="flex h-14 items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <svg class="text-primary h-8 w-8" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                </svg>
                <span class="font-display text-title text-primary font-bold">Penglipuran</span>
            </a>
        </div>
    </header>

    <!-- Main Content - Centered -->
    <main id="main-content" class="h-[calc(100dvh-3.5rem)] overflow-y-auto">
        <div class="flex min-h-full items-center justify-center px-4 py-8">
            <div class="w-full max-w-md">
                @yield('content')
            </div>
        </div>
    </main>
</body>

</html>
