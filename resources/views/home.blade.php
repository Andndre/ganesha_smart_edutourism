@extends('layouts.app')
@section('title', 'Beranda - Penglipuran')
@section('header_title', 'Desa Wisata Penglipuran')

@section('content')
<div x-data>
    <section class="bg-primary pb-13 relative rounded-b-3xl px-5 pt-[calc(env(safe-area-inset-top)+8rem)] text-white">

        <div class="flex items-start justify-between">
            <div class="pr-4">
                @auth
                    <h2 class="font-display text-2xl font-bold">
                        {{ __('Rahajeng Rauh, :name!', ['name' => str(Auth::user()->name)->before(' ')]) }}</h2>
                @else
                    <h2 class="font-display text-2xl font-bold">{{ __('Rahajeng Rauh!') }}</h2>
                @endauth
                <p class="text-primary-100/90 mt-1.5 text-sm font-medium leading-snug">
                    {{ __('Siap menjelajahi budaya & tradisi Penglipuran hari ini?') }}
                </p>
            </div>

            @auth
                <a href="{{ route('profile') }}" class="tap-target -mt-1 shrink-0 transition-transform active:scale-95"
                    aria-label="Buka Profil">
                    <div class="h-12 w-12 overflow-hidden rounded-full border-2 border-white/30 bg-white/10 p-0.5 shadow-sm">
                        <img src="https://ui-avatars.com/api/?name={{ \urlencode(Auth::user()->name) }}&background=D4AF37&color=fff&bold=true"
                            alt="Profil {{ Auth::user()->name }}" class="h-full w-full rounded-full object-cover">
                    </div>
                </a>
            @else
                <a href="{{ route('login') }}" class="tap-target -mt-1 shrink-0 transition-transform active:scale-95"
                    aria-label="Masuk">
                    <div
                        class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-full border-2 border-white/30 bg-white/10 p-2 text-white shadow-sm">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </a>
            @endauth
        </div>

        @php
            $densityParts = explode(' ', $densityText, 2);
            $densityMain = $densityParts[0] ?? '';
            $densitySub = $densityParts[1] ?? '';
        @endphp

        <div
            class="text-charcoal mx-auto flex w-full max-w-[calc(100vw-2.5rem)] translate-y-8 items-center justify-between gap-2 rounded-2xl bg-white p-4 shadow-lg shadow-gray-100/50 sm:p-5">
            <div class="flex min-w-0 flex-1 items-center gap-2.5 sm:gap-3">
                <!-- Trigger (Clickable Weather Card) -->
                <div @click="$dispatch('open-weather-modal')" class="flex min-w-0 flex-1 items-center gap-2.5 sm:gap-3 cursor-pointer hover:bg-gray-50/50 p-1.5 -m-1.5 rounded-xl transition-all active:scale-95" title="Lihat detail cuaca">
                    @if (isset($weather) && $weather)
                        <div class="flex shrink-0 items-center justify-center rounded-full bg-gray-50 p-2 sm:p-2.5">
                            {!! $weather->getIconHtml() !!}
                        </div>
                        <div class="flex min-w-0 flex-col">
                            <span class="text-[10px] font-bold uppercase leading-none tracking-wider text-gray-400">Cuaca Hari Ini</span>
                            <span class="mt-1 text-xl font-black leading-none text-gray-800 sm:text-2xl">{{ round($weather->temperature) }}°C</span>
                            <span class="mt-1 truncate text-[11px] font-semibold leading-none text-gray-500">{{ $weather->condition }}</span>
                        </div>
                    @else
                        <div class="flex shrink-0 items-center justify-center rounded-full bg-gray-100 p-2 text-gray-400 sm:p-2.5">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                        <div class="flex min-w-0 flex-col">
                            <span class="text-[10px] font-bold uppercase leading-none tracking-wider text-gray-400">Cuaca Hari Ini</span>
                            <span class="mt-1 text-xl font-black leading-none text-gray-400 sm:text-2xl">--°C</span>
                            <span class="mt-1 truncate text-[11px] font-semibold leading-none text-gray-500">Belum Diperbarui</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mx-1.5 h-12 w-[1.5px] shrink-0 bg-gray-100 sm:mx-2"></div>

            <div class="flex min-w-0 flex-1 items-center gap-2.5 sm:gap-3">
                <div
                    class="{{ $densityClass }} {{ $densityBg }} flex shrink-0 items-center justify-center rounded-full p-2 sm:p-2.5">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="flex min-w-0 flex-col">
                    <span class="text-[10px] font-bold uppercase leading-none tracking-wider text-gray-400">Kepadatan
                        Desa</span>
                    <span
                        class="{{ $densityClass }} mt-1 text-xl font-black leading-none sm:text-2xl">{{ $densityMain }}</span>
                    <span
                        class="mt-1 truncate text-[11px] font-semibold leading-none text-gray-500">{{ $densitySub }}</span>
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
            <a href="{{ route('explore') }}?filter=facilities"
                class="tap-target flex flex-col items-center gap-2 transition-transform active:scale-95">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-2xl border border-gray-100 bg-white text-gray-500 shadow-sm">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-center text-[11px] font-medium leading-tight">Info<br>Fasilitas</span>
            </a>
            @if (Auth::check() && Auth::user()->isUmkmOwner())
                <a href="{{ route('owner.dashboard') }}"
                    class="tap-target flex flex-col items-center gap-2 transition-transform active:scale-95">
                    <div
                        class="text-primary border-primary/20 bg-primary/5 flex h-14 w-14 items-center justify-center rounded-2xl border shadow-sm">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                        </svg>
                    </div>
                    <span class="text-primary text-center text-[11px] font-bold leading-tight">Panel<br>UMKM</span>
                </a>
            @endif

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
            @forelse($recommendedRoutes as $route)
                <div
                    class="min-w-65 flex flex-col justify-between rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="text-charcoal font-bold">{{ $route->name }}</h4>
                            <p class="mt-1 text-xs text-gray-500">
                                Estimasi {{ $route->estimated_duration_minutes ?? 60 }} Menit •
                                {{ $route->route_points_count ?? 0 }} Objek
                            </p>
                        </div>
                        <div class="bg-primary/10 text-primary rounded-lg p-2">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <a href="{{ route('explore') }}?route={{ $route->id }}"
                        class="bg-primary mt-4 block w-full rounded-xl py-2 text-center text-sm font-medium text-white transition-transform active:scale-95">
                        Mulai Rute
                    </a>
                </div>
            @empty
                <div class="w-full rounded-2xl border border-gray-100 bg-white p-4 py-6 text-center text-sm text-gray-500">
                    Tidak ada rute rekomendasi saat ini.
                </div>
            @endforelse
        </div>
    </section>

</div>

@push('modals')
    <!-- Premium Detail Weather Modal (Mobile Bottom-Sheet / Desktop Modal) -->
    <div x-data="{ isOpen: false }"
        x-show="isOpen"
        @open-weather-modal.window="isOpen = true"
        class="bg-charcoal/60 fixed inset-0 z-100 flex items-end justify-center px-0 md:items-center md:px-4 backdrop-blur-sm"
        style="display: none; will-change: transform; transform: translate3d(0,0,0);"
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" 
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0"
        x-cloak>
        
        <div class="relative w-full rounded-t-[2.5rem] bg-white p-6 shadow-2xl md:rounded-3xl max-w-md pb-10 md:pb-6" 
            style="padding-bottom: calc(1.5rem + env(safe-area-inset-bottom));"
            @click.away="isOpen = false"
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-300 transform" 
            x-transition:enter-start="translate-y-full md:translate-y-4 md:scale-95"
            x-transition:enter-end="translate-y-0 md:translate-y-0 md:scale-100" 
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0 md:translate-y-0 md:scale-100" 
            x-transition:leave-end="translate-y-full md:translate-y-4 md:scale-95">
            
            <!-- Pull Bar on Mobile -->
            <div class="mx-auto -mt-2 mb-5 h-1.5 w-12 rounded-full bg-gray-200 md:hidden"></div>
            
            <!-- Close Button on Desktop -->
            <button type="button" @click="isOpen = false" 
                class="absolute right-4 top-4 hidden items-center justify-center h-8 w-8 rounded-full bg-gray-50 text-gray-400 hover:text-gray-600 transition-colors md:flex"
                title="Tutup">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Modal Body -->
            <div class="space-y-5" x-show="isOpen">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <span class="rounded-lg border px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider bg-emerald-50 text-emerald-600 border-emerald-100">Desa Penglipuran</span>
                    <button type="button" @click="isOpen = false" class="text-xs font-bold text-gray-400 hover:text-gray-600 md:hidden">Tutup</button>
                </div>

                <h3 class="font-display text-charcoal text-xl font-black tracking-tight leading-snug">Detail Cuaca Desa</h3>

                @if(isset($weather) && $weather)
                    <!-- Main Status Grid -->
                    <div class="flex items-center gap-4 bg-gray-50/70 p-3.5 rounded-2xl border border-gray-100">
                        <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shrink-0 shadow-sm border border-gray-100">
                            {!! $weather->getIconHtml() !!}
                        </div>
                        <div>
                            <p class="text-2xl font-black text-gray-800 leading-none">{{ round($weather->temperature) }}°C</p>
                            <p class="text-xs font-black text-gray-700 mt-1.5 leading-none">{{ $weather->condition }}</p>
                        </div>
                    </div>

                    <!-- Weather Parameters Grid -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Humidity -->
                        <div class="flex items-center gap-2.5 bg-gray-50/70 p-3.5 rounded-xl border border-gray-100">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 9.172V5L8 4z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider leading-none">Kelembapan</p>
                                <p class="text-xs font-black text-gray-700 mt-1">{{ $weather->humidity }}%</p>
                            </div>
                        </div>

                        <!-- Wind -->
                        <div class="flex items-center gap-2.5 bg-gray-50/70 p-3.5 rounded-xl border border-gray-100">
                            <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider leading-none">Angin</p>
                                <p class="text-xs font-black text-gray-700 mt-1">{{ $weather->wind_speed }} km/h</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendation Box based on Weather Code -->
                    <div class="border-t border-gray-50 pt-3">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Rekomendasi Aktivitas</h4>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            @if(in_array($weather->weather_code, [0, 1, 2]))
                                Cuaca sangat bersahabat untuk berjalan-jalan menjelajahi desa. Jangan lupa menggunakan tabir surya dan membawa air minum!
                            @elseif(in_array($weather->weather_code, [3, 45, 48]))
                                Cuaca sejuk dan berawan, sangat ideal untuk berkeliling santai tanpa sengatan matahari.
                            @elseif(in_array($weather->weather_code, [51, 53, 55, 61, 63]))
                                Gerimis atau hujan ringan. Disarankan membawa payung atau jas hujan saat menjelajahi pekarangan desa.
                            @else
                                Hujan deras atau badai terdeteksi. Sebaiknya bersantai di cafe lokal, mengunjungi rumah adat (bale), atau menikmati kuliner lokal di area tertutup.
                            @endif
                        </p>
                    </div>

                    <!-- Footer Info / Updated Time -->
                    <div class="border-t border-gray-50 pt-3 text-center">
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">
                            Pembaruan Terakhir: {{ $weather->updated_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                        </p>
                    </div>
                @else
                    <!-- No Weather Info State -->
                    <div class="text-center py-8 space-y-4">
                        <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-gray-700">Data Cuaca Belum Tersedia</h4>
                            <p class="text-xs text-gray-500 max-w-[240px] mx-auto leading-relaxed">Silakan jalankan update cuaca dari sistem untuk memuat laporan cuaca terkini.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endpush
@endsection
