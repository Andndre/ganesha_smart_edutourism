<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1E5128">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — Penglipuran</title>

    {{-- Google Fonts: Plus Jakarta Sans (UI) + Playfair Display (editorial) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet">

    {{-- Shortcut icon --}}
    <link rel="shortcut icon" href="{{ asset('icons/logo-color-notext-shortcut.ico') }}">

    <style>
        :root {
            --sat: env(safe-area-inset-top);
            --sab: env(safe-area-inset-bottom);
        }

        /* Sidebar fixed on desktop & mobile overlay, content scrolls independently */
        #admin-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 260px;
            overflow-y: auto;
        }

        @media (min-width: 1024px) {
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
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-surface text-charcoal antialiased">

    {{-- ============================================================
    SIDEBAR — Desktop fixed, mobile overlay
    Uses charcoal (#191A19) aligned with DESIGN.md charcoal token
    ============================================================ --}}
    <aside id="admin-sidebar"
        class="sidebar-hidden z-40 flex h-screen w-[260px] flex-col border-r border-gray-100 bg-charcoal text-white shadow-xl lg:flex">

        {{-- Brand Header --}}
        <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
            <img src="{{ asset('icons/logo-wht-notext.png') }}" alt="Penglipuran Logo"
                class="h-8 w-auto object-contain">
            <div>
                <p class="font-display text-sm font-bold leading-tight tracking-wide text-white">Penglipuran</p>
                <p class="text-[10px] font-medium uppercase tracking-widest text-white/40">
                    {{ auth()->user()->isAdmin() ? 'Admin Panel' : 'Owner Panel' }}
                </p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-4">

            @php
                $navItems = [];

                if (auth()->user()->isAdmin()) {
                    $navItems = [
                        [
                            'url' => route('admin.dashboard'),
                            'route' => 'admin.dashboard',
                            'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                            'label' => 'Dashboard'
                        ],
                        [
                            'url' => route('admin.capacity'),
                            'route' => 'admin.capacity',
                            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H3v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                            'label' => 'Kapasitas Wisatawan'
                        ],
                        [
                            'url' => route('admin.map-manager'),
                            'route' => 'admin.map-manager',
                            'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                            'label' => 'Peta Lokasi & Titik'
                        ],
                        [
                            'type' => 'header',
                            'label' => 'Sektor UMKM'
                        ],
                        [
                            'url' => route('admin.umkm.owners'),
                            'route' => 'admin.umkm.owners',
                            'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                            'label' => 'Pemilik & Toko UMKM'
                        ],
                        [
                            'url' => route('admin.umkm.categories'),
                            'route' => 'admin.umkm.categories',
                            'icon' => 'M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                            'label' => 'Kategori Produk'
                        ],
                        [
                            'type' => 'header',
                            'label' => 'Wisata & Event'
                        ],
                        [
                            'url' => route('admin.events'),
                            'route' => 'admin.events',
                            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                            'label' => 'Event & Kalender'
                        ],
                        [
                            'url' => route('admin.tour-routes'),
                            'route' => 'admin.tour-routes',
                            'icon' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
                            'label' => 'Rute Wisata'
                        ],
                        [
                            'url' => route('admin.packages'),
                            'route' => 'admin.packages',
                            'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                            'label' => 'Paket Wisata'
                        ],
                        [
                            'url' => route('admin.bookings'),
                            'route' => 'admin.bookings',
                            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                            'label' => 'Pemesanan'
                        ],
                        [
                            'url' => route('admin.feedback'),
                            'route' => 'admin.feedback',
                            'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                            'label' => 'Ulasan & Feedback'
                        ],
                        [
                            'url' => route('admin.reports'),
                            'route' => 'admin.reports',
                            'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            'label' => 'Laporan & Analitik'
                        ],
                    ];
                } elseif (auth()->user()->isUmkmOwner()) {
                    $navItems = [
                        [
                            'url' => route('owner.dashboard'),
                            'route' => 'owner.dashboard',
                            'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                            'label' => 'Dashboard Ringkasan'
                        ],
                        [
                            'type' => 'header',
                            'label' => 'Toko Saya'
                        ],
                        [
                            'url' => route('owner.profile'),
                            'route' => 'owner.profile',
                            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                            'label' => 'Informasi Toko'
                        ],
                        [
                            'url' => route('owner.location'),
                            'route' => 'owner.location',
                            'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                            'label' => 'Kustomisasi Lokasi'
                        ],
                        [
                            'url' => route('owner.products'),
                            'route' => 'owner.products',
                            'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                            'label' => 'Daftar Produk'
                        ],
                    ];
                }
            @endphp

            @foreach ($navItems as $item)
                @if (isset($item['type']) && $item['type'] === 'header')
                    <div class="px-4 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-white/30">{{ $item['label'] }}</div>
                @else
                    @php
                        $isActive = Route::is($item['route']) || Route::is($item['route'] . '.*');
                        $activeClass = $isActive
                            ? 'bg-primary text-white shadow-lg shadow-primary/30'
                            : 'text-white/60 hover:bg-white/8 hover:text-white';
                    @endphp
                    <a href="{{ $item['url'] }}"
                        class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ $activeClass }}">
                        <svg class="h-[18px] w-[18px] shrink-0 {{ $isActive ? 'text-white' : 'text-white/40 group-hover:text-white/70' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        <span class="truncate">{{ $item['label'] }}</span>
                        @if ($isActive)
                            <span class="ml-auto h-1.5 w-1.5 shrink-0 rounded-full bg-secondary"></span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Footer: User info + Logout --}}
        <div class="border-t border-white/10 p-4">
            <div class="mb-3 flex items-center gap-3 px-1">
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-xs font-semibold text-white">{{ auth()->user()?->name ?? 'User' }}</p>
                    <p class="truncate text-[10px] text-white/40">{{ auth()->user()?->email ?? '' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-white/50 transition-all hover:bg-white/8 hover:text-white">
                    <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- ============================================================
    MOBILE HEADER — only visible below lg breakpoint
    ============================================================ --}}
    <header
        class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-gray-100 bg-surface/90 px-4 backdrop-blur-md lg:hidden">
        <button id="sidebar-toggle"
            class="tap-target -ml-2 rounded-lg p-2 text-charcoal transition-colors hover:bg-gray-100"
            aria-label="Buka Menu">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <h1 class="text-sm font-bold text-charcoal">@yield('title', 'Dashboard')</h1>
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">
            {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
        </div>
    </header>

    {{-- Mobile sidebar backdrop --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-black/50 backdrop-blur-sm lg:hidden"
        onclick="closeSidebar()">
    </div>

    {{-- ============================================================
    MAIN CONTENT
    ============================================================ --}}
    <main id="admin-main" class="min-h-screen overflow-y-auto bg-surface p-6 lg:p-8">
        {{-- Success/Error Alerts --}}
        @if (session('success'))
            <div
                class="mb-6 flex items-center gap-3 rounded-xl bg-primary/10 border border-primary/20 p-4 text-sm text-primary">
                <svg class="h-5 w-5 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-semibold text-primary-800">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-warning/10 border border-warning/20 p-4 text-sm text-warning-800">
                <div class="flex items-center gap-3 mb-2 text-warning">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-bold text-warning-800">Terjadi Kesalahan:</span>
                </div>
                <ul class="list-disc pl-8 space-y-1 text-warning-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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

        // SweetAlert2 global delete confirmation handler
        document.addEventListener('DOMContentLoaded', () => {
            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (form.classList.contains('delete-form') || form.hasAttribute('data-confirm')) {
                    if (form.dataset.confirmed) {
                        return;
                    }
                    
                    e.preventDefault();
                    
                    const message = form.getAttribute('data-confirm') || 'Apakah Anda yakin ingin menghapus data ini?';
                    
                    Swal.fire({
                        title: 'Konfirmasi Hapus',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#1E5128', // Matches primary brand color
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        background: '#ffffff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = 'true';
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>
