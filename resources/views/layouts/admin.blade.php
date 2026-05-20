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
            overflow: hidden;
        }

        body {
            overscroll-behavior: none;
        }

        /* Desktop sidebar */
        @media (min-width: 1024px) {
            #sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                width: 256px;
            }

            #main-content {
                margin-left: 256px;
            }
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
    <!-- Desktop Sidebar - Hidden on Mobile -->
    <aside id="sidebar" class="bg-dark-surface hidden h-screen w-64 flex-col text-white lg:flex">
        <div class="border-b border-gray-700 p-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <svg class="text-secondary h-8 w-8" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                </svg>
                <span class="font-display text-title font-bold">Penglipuran</span>
            </a>
            <span class="text-caption text-gray-400">Admin Panel</span>
        </div>

        <nav class="flex-1 space-y-2 p-4">
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-700/50">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
            <a href="{{ route('admin.cultural-objects') }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-700/50">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Cultural Objects
            </a>
            <a href="{{ route('admin.umkm') }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-700/50">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                UMKM
            </a>
            <a href="{{ route('admin.events') }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-700/50">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Events
            </a>
            <a href="{{ route('admin.tour-routes') }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-700/50">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a2 2 0 012-2h3.382a2 2 0 012 2v1.764l2.553.91a1 1 0 00.894 0L15 7v12a1 1 0 01-1 1h-2.618a2 2 0 01-2-2v-1.618a1 1 0 00-.553-.894l-1.447-.723a1 1 0 01-.447-.553V8.618a2 2 0 00-1-1.382l-.553-.276A2 2 0 016 5.618V3a2 2 0 00-2-2H3a2 2 0 00-2 2v2.618a2 2 0 001.382 1.764l.553.276A2 2 0 004 10.618v1.764a1 1 0 00.447.894l1.447.723a2 2 0 002.276 0L9.894 12a1 1 0 011 1v2.618a2 2 0 01-2 2H7.382a2 2 0 01-2-2v-1.618a1 1 0 00-1-1.382z" />
                </svg>
                Tour Routes
            </a>
            <a href="{{ route('admin.capacity') }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-700/50">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H3v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Capacity Monitor
            </a>
        </nav>

        <div class="border-t border-gray-700 p-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left hover:bg-gray-700/50">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m10 4v-1a3 3 0 00-3-3h-1a1 1 0 01-1-1V7a1 1 0 011-1h1a3 3 0 003 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile Top Navigation -->
    <header class="pt-sat bg-surface/80 dark:bg-dark-bg/80 sticky top-0 z-40 px-4 backdrop-blur-md lg:hidden">
        <nav class="flex h-14 items-center justify-between">
            <button @click="document.getElementById('sidebar').classList.toggle('hidden')" class="tap-target -ml-2 p-2">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <h1 class="text-label font-semibold">@yield('title', 'Admin')</h1>
            <a href="{{ route('profile') }}" class="tap-target -mr-2 p-2">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h1a7 7 0 007-7 7 7 0 007 7h1a7 7 0 007-7 7 7 0 00-7-7z" />
                </svg>
            </a>
        </nav>
    </header>

    <!-- Main Content Area -->
    <main id="main-content" class="overflow-y-auto p-4 lg:h-screen lg:p-8">
        @yield('content')
    </main>
</body>

</html>
