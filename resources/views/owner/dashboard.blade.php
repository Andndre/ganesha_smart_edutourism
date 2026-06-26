@extends('layouts.dashboard')

@section('title', 'Dashboard Pemilik UMKM')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between max-w-6xl">
        <div>
            <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Ringkasan Toko Anda</h1>
            <p class="mt-1 text-sm text-gray-500">Selamat datang kembali! Berikut ringkasan performa dan informasi toko
                Anda.</p>
        </div>
        <a href="{{ route('home') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 px-4 py-2.5 text-sm font-semibold text-primary transition-all hover:bg-primary hover:text-white active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Lihat Aplikasi Wisata
        </a>
    </div>

    @if (!$profile)
        <x-owner.no-profile-warning message="Akun Anda belum memiliki profil toko UMKM yang aktif. Silakan isi informasi profil toko Anda terlebih dahulu agar dapat mengelola produk dan menandai lokasi di peta." />
    @else
        {{-- Stats Cards Grid --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 max-w-6xl mb-8">
            {{-- Card 1: Total Products --}}
            <div
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
            <div
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
        <div class="max-w-4xl rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="font-display text-lg font-bold text-charcoal mb-4">Profil Toko Aktif</h3>
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <span class="text-xs text-gray-400 block font-semibold uppercase">Nama Bisnis / Toko</span>
                        <span class="text-base text-charcoal font-semibold">{{ $profile->business_name }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block font-semibold uppercase">Kategori Toko</span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-bold text-primary">
                            {{ ucfirst($profile->category) }}
                        </span>
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
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $profile->description ?? 'Belum ada deskripsi.' }}
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