<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1E5128">

    <title>@yield('title', 'Admin') — Penglipuran</title>

    {{-- Google Fonts: Plus Jakarta Sans (UI) + Playfair Display (editorial) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sat: env(safe-area-inset-top);
            --sab: env(safe-area-inset-bottom);
        }

        /* Sidebar fixed on desktop, content scrolls independently */
        @media (min-width: 1024px) {
            #admin-sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                width: 260px;
                overflow-y: auto;
            }

            #admin-main {
                margin-left: 260px;
            }
        }

        /* Mobile sidebar overlay */
        #admin-sidebar.sidebar-hidden {
            display: none;
        }

        @media (min-width: 1024px) {
            #admin-sidebar.sidebar-hidden {
                display: flex;
            }
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

{{-- Charcoal body bg, text inherits design tokens --}}
<body class="bg-surface text-charcoal antialiased">

    {{-- ============================================================
         SIDEBAR — Desktop fixed, mobile overlay
         Uses charcoal (#191A19) aligned with DESIGN.md charcoal token
         ============================================================ --}}
    <aside id="admin-sidebar"
        class="sidebar-hidden z-40 flex h-screen w-[260px] flex-col border-r border-gray-100 bg-charcoal text-white shadow-xl lg:flex">

        {{-- Brand Header --}}
        <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
            <img src="{{ asset('icons/logo-wht-notext.png') }}" alt="Penglipuran Logo" class="h-8 w-auto object-contain">
            <div>
                <p class="font-display text-sm font-bold leading-tight tracking-wide text-white">Penglipuran</p>
                <p class="text-[10px] font-medium uppercase tracking-widest text-white/40">Admin Panel</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">

            @php
                $navItems = [
                    ['route' => 'admin.dashboard',        'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
                    ['route' => 'admin.capacity',         'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H3v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'label' => 'Kapasitas Wisatawan'],
                    ['route' => 'admin.cultural-objects', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'label' => 'Objek Budaya'],
                    ['route' => 'admin.umkm',             'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'label' => 'UMKM'],
                    ['route' => 'admin.events',           'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'label' => 'Event & Kalender'],
                    ['route' => 'admin.tour-routes',      'icon' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7', 'label' => 'Rute Wisata'],
                    ['route' => 'admin.packages',         'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'label' => 'Paket Wisata'],
                    ['route' => 'admin.bookings',         'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'label' => 'Pemesanan'],
                    ['route' => 'admin.feedback',         'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'label' => 'Ulasan & Feedback'],
                    ['route' => 'admin.reports',          'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label' => 'Laporan & Analitik'],
                ];
            @endphp

            @foreach ($navItems as $item)
                @php
                    $isActive = Route::is($item['route']) || Route::is($item['route'] . '.*');
                @endphp
                <a href="{{ route($item['route']) }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all
                        {{ $isActive
                            ? 'bg-primary text-white shadow-lg shadow-primary/30'
                            : 'text-white/60 hover:bg-white/8 hover:text-white' }}">
                    <svg class="h-[18px] w-[18px] shrink-0 {{ $isActive ? 'text-white' : 'text-white/40 group-hover:text-white/70' }}"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                    </svg>
                    <span class="truncate">{{ $item['label'] }}</span>
                    @if ($isActive)
                        <span class="ml-auto h-1.5 w-1.5 shrink-0 rounded-full bg-secondary"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Footer: Admin user info + Logout --}}
        <div class="border-t border-white/10 p-4">
            <div class="mb-3 flex items-center gap-3 px-1">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-xs font-semibold text-white">{{ auth()->user()?->name ?? 'Admin' }}</p>
                    <p class="truncate text-[10px] text-white/40">{{ auth()->user()?->email ?? '' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-white/50 transition-all hover:bg-white/8 hover:text-white">
                    <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- ============================================================
         MOBILE HEADER — only visible below lg breakpoint
         ============================================================ --}}
    <header class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-gray-100 bg-surface/90 px-4 backdrop-blur-md lg:hidden">
        <button id="sidebar-toggle" class="tap-target -ml-2 rounded-lg p-2 text-charcoal transition-colors hover:bg-gray-100" aria-label="Buka Menu">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <h1 class="text-sm font-bold text-charcoal">@yield('title', 'Admin')</h1>
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
            {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
        </div>
    </header>

    {{-- Mobile sidebar backdrop --}}
    <div id="sidebar-backdrop"
        class="fixed inset-0 z-30 hidden bg-black/50 backdrop-blur-sm lg:hidden"
        onclick="closeSidebar()">
    </div>

    {{-- ============================================================
         MAIN CONTENT
         ============================================================ --}}
    <main id="admin-main" class="min-h-screen overflow-y-auto bg-surface p-6 lg:p-8">
        @yield('content')
    </main>

    <script>
        const sidebar = document.getElementById('admin-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const toggle = document.getElementById('sidebar-toggle');

        function openSidebar() {
            sidebar.classList.remove('sidebar-hidden');
            backdrop.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            if (window.innerWidth < 1024) {
                sidebar.classList.add('sidebar-hidden');
                backdrop.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        toggle?.addEventListener('click', function () {
            if (sidebar.classList.contains('sidebar-hidden')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        });

        // Auto-close sidebar on resize to desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                backdrop.classList.add('hidden');
                document.body.style.overflow = '';
                sidebar.classList.remove('sidebar-hidden');
            } else {
                sidebar.classList.add('sidebar-hidden');
            }
        });
    </script>

    @stack('scripts')

</body>

</html>
