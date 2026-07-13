@extends('layouts.dashboard')

@section('title', 'Dashboard Pemilik UMKM')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between max-w-6xl">
        <div id="tour-header">
            <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Ringkasan Toko Anda</h1>
            <p class="mt-1 text-sm text-gray-500">Selamat datang kembali! Berikut ringkasan performa dan informasi toko
                Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <button id="tour-trigger-btn" onclick="startTutorial()"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:bg-gray-100 active:scale-[0.98]"
                title="Panduan Interaktif">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
            <a href="{{ route('home') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 px-4 py-2.5 text-sm font-semibold text-primary transition-all hover:bg-primary hover:text-white active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Lihat Aplikasi Wisata
            </a>
        </div>
    </div>

    @if (!$profile)
        <div id="tour-no-profile-warning">
            <x-owner.no-profile-warning message="Akun Anda belum memiliki profil toko UMKM yang aktif. Silakan isi informasi profil toko Anda terlebih dahulu agar dapat mengelola produk dan menandai lokasi di peta." />
        </div>
    @else
        {{-- Stats Cards Grid --}}
        <div id="tour-stats-grid" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 max-w-6xl mb-8">
            {{-- Card 1: Total Products --}}
            <div id="tour-product-count-card"
                class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Produk</p>
                        <p class="mt-2 font-display text-3xl font-bold text-charcoal">{{ $productCount }}</p>
                    </div>
                    <div class="rounded-xl bg-primary/10 p-3.5 text-primary">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-primary font-medium">
                    <span>Kelola produk di menu daftar produk</span>
                </div>
            </div>

            {{-- Card 2: Active Products --}}
            <div
                class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Produk Aktif</p>
                        <p class="mt-2 font-display text-3xl font-bold text-charcoal">{{ $activeProductCount }}</p>
                    </div>
                    <div class="rounded-xl bg-secondary/10 p-3.5 text-secondary">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-gray-500">
                    <span>Tampil secara publik di aplikasi klien</span>
                </div>
            </div>

            {{-- Card 3: Rating --}}
            <div id="tour-rating-card"
                class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Rating Toko</p>
                        <p class="mt-2 font-display text-3xl font-bold text-charcoal">
                            {{ number_format($profile->rating ?? 5.0, 1) }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-amber-500/10 p-3.5 text-amber-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-gray-500">
                    <span class="flex text-amber-500">★★★★★</span>
                    <span>Berdasarkan ulasan pengunjung</span>
                </div>
            </div>
        </div>

        {{-- Store Info Showcase --}}
        <div id="tour-store-info" class="max-w-4xl rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-bold text-charcoal mb-4">Profil Toko Aktif</h3>
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <span class="text-xs text-gray-400 block font-semibold uppercase">Nama Bisnis / Toko</span>
                        <span class="text-base text-charcoal font-semibold">{{ $profile->business_name }}</span>
                    </div>
                    <div>
                        {{-- TODO: Pertimbangkan menambahkan fitur jadwal buka toko --}}
                        <span class="text-xs text-gray-400 block font-semibold uppercase">Status Toko</span>
                        @if ($profile->is_active)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-lg bg-secondary/10 px-2.5 py-1 text-xs font-bold text-secondary">Buka
                                / Aktif</span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-150 px-2.5 py-1 text-xs font-bold text-gray-500">Tutup
                                / Nonaktif</span>
                        @endif
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <span class="text-xs text-gray-400 block font-semibold uppercase">Deskripsi Toko</span>
                        <p class="text-sm text-gray-600 leading-relaxed">{!! $profile->description ?? 'Belum ada deskripsi.' !!}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block font-semibold uppercase">Lokasi Koordinat Peta</span>
                        @if ($profile->mapLocation)
                            <span class="text-sm font-mono text-gray-600 block">Lat: {{ $profile->mapLocation->latitude }}, Lng:
                                {{ $profile->mapLocation->longitude }}</span>
                            <a href="{{ route('owner.location') }}"
                                class="mt-1 inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline">
                                Kustomisasi di Peta →
                            </a>
                        @else
                            <span class="text-sm italic text-warning font-semibold block">Lokasi belum diatur di peta!</span>
                            <a href="{{ route('owner.location') }}"
                                class="mt-1 inline-flex items-center gap-1 text-xs font-bold text-warning hover:underline">
                                Atur Lokasi Sekarang →
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        function startTutorial() {
            const driver = window.driver.js.driver;
            const hasProfile = document.getElementById('tour-no-profile-warning') === null;
            const steps = [];

            // Langkah 1: Pengantar
            steps.push({
                element: '#tour-header',
                popover: {
                    title: '👋 Selamat Datang!',
                    description: 'Ini adalah Dashboard Pemilik UMKM. Di sini Anda dapat memantau performa toko serta mengelola informasi profil, lokasi, dan produk Anda.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            if (!hasProfile) {
                // Langkah Alternatif: Belum punya profil toko
                steps.push({
                    element: '#tour-no-profile-warning',
                    popover: {
                        title: '📝 Buat Profil Toko Anda',
                        description: 'Sebelum dapat mengelola produk dan lokasi, Anda perlu membuat profil toko UMKM terlebih dahulu. Klik tombol di atas untuk mulai mengisi informasi toko.',
                        side: 'top',
                        align: 'start'
                    }
                });
            } else {
                // Langkah 2: Total Produk
                steps.push({
                    element: '#tour-product-count-card',
                    popover: {
                        title: '📦 Total Produk',
                        description: 'Kartu ini menampilkan jumlah seluruh produk yang telah Anda tambahkan ke toko, baik yang aktif maupun tidak.',
                        side: 'bottom',
                        align: 'start'
                    }
                });

                // Langkah 3: Rating Toko
                steps.push({
                    element: '#tour-rating-card',
                    popover: {
                        title: '⭐ Rating Toko',
                        description: 'Rating ini dihitung berdasarkan ulasan pengunjung terhadap toko Anda di aplikasi wisata.',
                        side: 'bottom',
                        align: 'end'
                    }
                });

                // Langkah 4: Profil Toko Aktif
                steps.push({
                    element: '#tour-store-info',
                    popover: {
                        title: '🏬 Profil Toko Aktif',
                        description: 'Ringkasan informasi toko Anda yang tampil ke publik, termasuk nama, kategori, status buka/tutup, deskripsi, dan lokasi koordinat di peta.',
                        side: 'top',
                        align: 'start'
                    }
                });
            }

            const driverObj = driver({
                showProgress: true,
                allowClose: true,
                steps: steps,
                popoverClass: 'driverjs-theme'
            });

            driverObj.drive();
        }

        // Auto-run for first-time visitors
        document.addEventListener('DOMContentLoaded', () => {
            const tourCompleted = localStorage.getItem('owner_dashboard_tour_completed');
            if (!tourCompleted) {
                setTimeout(() => {
                    startTutorial();
                    localStorage.setItem('owner_dashboard_tour_completed', 'true');
                }, 1000);
            }
        });
    </script>
@endpush