@extends('layouts.app')
@section('title', 'Beranda - Penglipuran')
@section('header_title', 'Desa Wisata Penglipuran')

@section('content')
    <section class="bg-primary relative rounded-b-3xl px-5 pb-12 pt-6 text-white">

        <div class="flex items-start justify-between">
            <div class="pr-4">
                <h2 class="font-display text-2xl font-bold">Rahajeng Rauh, Andre!</h2>
                <p class="text-primary-100/90 mt-1.5 text-sm font-medium leading-snug">
                    Siap menjelajahi budaya & tradisi Penglipuran hari ini?
                </p>
            </div>

            <a href="{{ route('profile') }}" class="tap-target -mt-1 shrink-0 transition-transform active:scale-95"
                aria-label="Buka Profil">
                <div class="h-12 w-12 overflow-hidden rounded-full border-2 border-white/30 bg-white/10 p-0.5 shadow-sm">
                    <img src="https://ui-avatars.com/api/?name=Andre&background=D4AF37&color=fff&bold=true"
                        alt="Profil Andre" class="h-full w-full rounded-full object-cover">
                </div>
            </a>
        </div>

        <div
            class="text-charcoal mx-auto mt-8 flex w-full max-w-[calc(100vw-2.5rem)] translate-y-8 items-center justify-between rounded-2xl bg-white p-4 shadow-md">
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-blue-50 p-2.5 text-blue-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Cuaca Hari Ini</p>
                    <p class="mt-0.5 text-sm font-bold">27°C Cerah</p>
                </div>
            </div>

            <div class="h-10 w-[1.5px] bg-gray-100"></div>

            <div class="flex items-center gap-3">
                <div class="text-primary rounded-full bg-green-50 p-2.5">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Kepadatan Desa</p>
                    <p class="text-primary mt-0.5 text-sm font-bold">Aman (Lancar)</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-6 mt-12 px-4">
        <div class="grid grid-cols-4 gap-x-2 gap-y-6">
            <a href="{{ route('explore') }}"
                class="tap-target flex flex-col items-center gap-2 transition-transform active:scale-95">
                <div
                    class="text-primary flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <span class="text-center text-[11px] font-medium leading-tight">Peta<br>Wisata</span>
            </a>
            <a href="{{ route('umkm') }}"
                class="tap-target flex flex-col items-center gap-2 transition-transform active:scale-95">
                <div
                    class="text-primary flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <span class="text-center text-[11px] font-medium leading-tight">Pasar<br>UMKM</span>
            </a>
            <a href="{{ route('learning') }}"
                class="tap-target flex flex-col items-center gap-2 transition-transform active:scale-95">
                <div
                    class="text-secondary flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span class="text-center text-[11px] font-medium leading-tight">Buku<br>Saku</span>
            </a>
            <a href="{{ route('cultural-objects') }}"
                class="tap-target flex flex-col items-center gap-2 transition-transform active:scale-95">
                <div
                    class="text-secondary flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <span class="text-center text-[11px] font-medium leading-tight">Objek<br>Budaya</span>
            </a>
            <a href="{{ route('tour-packages') }}"
                class="tap-target flex flex-col items-center gap-2 transition-transform active:scale-95">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-100 bg-white text-blue-600 shadow-sm">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <span class="text-center text-[11px] font-medium leading-tight">Tiket &<br>Paket</span>
            </a>
            <a href="{{ route('events') }}"
                class="tap-target flex flex-col items-center gap-2 transition-transform active:scale-95">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-100 bg-white text-blue-600 shadow-sm">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-center text-[11px] font-medium leading-tight">Jadwal<br>Event</span>
            </a>
            <a href="{{ route('explore') }}?filter=facilities" class="tap-target flex flex-col items-center gap-2 transition-transform active:scale-95">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-100 bg-white text-gray-500 shadow-sm">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-center text-[11px] font-medium leading-tight">Info<br>Fasilitas</span>
            </a>
            <!-- Emergency SOS (Routes to Map) -->
            <a href="{{ route('explore') }}?filter=sos"
                class="tap-target flex flex-col items-center gap-2 transition-transform active:scale-95">
                <div
                    class="text-warning flex h-14 w-14 items-center justify-center rounded-2xl border border-red-100 bg-red-50 shadow-sm">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <span class="text-warning text-center text-[11px] font-medium leading-tight">Rute<br>Darurat</span>
            </a>
        </div>
    </section>

    <section class="mb-4 px-4 py-4">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-charcoal text-lg font-bold">Jalur Rekomendasi</h3>
            <a href="{{ route('explore') }}" class="text-primary text-sm font-medium">Lihat Peta</a>
        </div>

        <div class="no-scrollbar flex gap-4 overflow-x-auto pb-2">
            <div class="min-w-65 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-charcoal font-bold">Jalur Budaya Singkat</h4>
                        <p class="mt-1 text-xs text-gray-500">Estimasi 1 Jam • 4 Objek</p>
                    </div>
                    <div class="bg-primary/10 text-primary rounded-lg p-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <button
                    class="bg-primary mt-4 w-full rounded-xl py-2 text-sm font-medium text-white transition-transform active:scale-95">Mulai
                    Rute</button>
            </div>
            <div class="min-w-65 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-charcoal font-bold">Eksplorasi Hutan Bambu</h4>
                        <p class="mt-1 text-xs text-gray-500">Estimasi 1.5 Jam • Alam</p>
                    </div>
                    <div class="bg-primary/10 text-primary rounded-lg p-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                    </div>
                </div>
                <button
                    class="bg-primary mt-4 w-full rounded-xl py-2 text-sm font-medium text-white transition-transform active:scale-95">Mulai
                    Rute</button>
            </div>
        </div>
    </section>
@endsection
