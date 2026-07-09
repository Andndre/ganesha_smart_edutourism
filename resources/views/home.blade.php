@extends('layouts.app')
@section('title', __('Beranda - Penglipuran'))
@section('header_title', 'Smart Edutourism')

@section('content')
    <div x-data class="relative w-full">
        <!-- Curved Green Header Background (Full width to top) -->
        <div
            class="h-62 absolute left-0 right-0 top-0 -z-10 rounded-b-[2.5rem] bg-[#1E5128] shadow-sm sm:h-64 lg:h-72 lg:rounded-b-[3rem]">
        </div>

        <section class="relative px-4 pb-4 pt-[calc(env(safe-area-inset-top)+7.5rem)] md:px-8 lg:pt-28">

            <div class="flex items-start justify-between">
                <div class="pr-4">
                    @auth
                        <h2 class="font-display text-2xl font-bold tracking-tight text-white lg:text-3xl">
                            {{ __('Rahajeng Rauh, :name!', ['name' => str(Auth::user()->name)->before(' ')]) }}</h2>
                    @else
                        <h2 class="font-display text-2xl font-bold tracking-tight text-white lg:text-3xl">
                            {{ __('Rahajeng Rauh!') }}</h2>
                    @endauth
                    <p class="mt-1.5 text-sm font-medium leading-snug text-green-100">
                        {{ __('Siap menjelajahi budaya & tradisi Penglipuran hari ini?') }}
                    </p>
                </div>

                @auth
                    <a href="{{ route('profile') }}" class="tap-target -mt-1 shrink-0 transition-transform active:scale-95"
                        aria-label="{{ __('Buka Profil') }}">
                        <div class="h-12 w-12 overflow-hidden rounded-full border-2 border-slate-200 bg-white p-0.5 shadow-sm">
                            <img src="{{ Auth::user()->avatarUrl() }}"
                                alt="{{ __('Profil :name', ['name' => Auth::user()->name]) }}"
                                class="h-full w-full rounded-full object-cover">
                        </div>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="tap-target -mt-1 shrink-0 transition-transform active:scale-95"
                        aria-label="{{ __('Masuk') }}">
                        <div
                            class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-full border-2 border-slate-200 bg-white p-2 text-gray-600 shadow-sm">
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

            <div class="mt-8 grid w-full grid-cols-2 gap-3">
                <!-- Weather Widget -->
                <div @click="$dispatch('open-weather-modal')"
                    class="flex cursor-pointer items-center gap-3 rounded-3xl border border-slate-100 bg-white p-4 shadow-sm transition-transform hover:shadow-md active:scale-95 sm:p-5"
                    title="{{ __('Lihat detail cuaca') }}">
                    @if (isset($weather) && $weather)
                        <div class="flex shrink-0 items-center justify-center rounded-2xl bg-sky-50 p-2.5 text-sky-500">
                            {!! $weather->getIconHtml() !!}
                        </div>
                        <div class="flex min-w-0 flex-col">
                            <span
                                class="text-[10px] font-bold uppercase leading-none tracking-wider text-gray-400">{{ __('Cuaca') }}</span>
                            <span
                                class="mt-1 text-xl font-black leading-none text-gray-800">{{ round($weather->temperature) }}°</span>
                            <span
                                class="mt-1 truncate text-[11px] font-semibold leading-none text-gray-500">{{ __($weather->condition) }}</span>
                        </div>
                    @else
                        <div class="flex shrink-0 items-center justify-center rounded-2xl bg-gray-50 p-2.5 text-gray-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                        <div class="flex min-w-0 flex-col">
                            <span
                                class="text-[10px] font-bold uppercase leading-none tracking-wider text-gray-400">{{ __('Cuaca') }}</span>
                            <span class="mt-1 text-xl font-black leading-none text-gray-400">--°</span>
                            <span
                                class="mt-1 truncate text-[11px] font-semibold leading-none text-gray-500">{{ __('Belum Ada') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Density Widget -->
                <div @click="$dispatch('open-density-modal')"
                    class="flex cursor-pointer items-center gap-3 rounded-3xl border border-slate-100 bg-white p-4 shadow-sm transition-transform hover:shadow-md active:scale-95 sm:p-5"
                    title="{{ __('Lihat detail kepadatan') }}">
                    <div
                        class="{{ $densityClass }} {{ $densityBg }} flex shrink-0 items-center justify-center rounded-2xl p-2.5">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="flex min-w-0 flex-col">
                        <span
                            class="text-[10px] font-bold uppercase leading-none tracking-wider text-gray-400">{{ __('Kepadatan') }}</span>
                        <span class="{{ $densityClass }} mt-1 text-xl font-black leading-none">{{ $densityMain }}</span>
                        <span
                            class="mt-1 truncate text-[11px] font-semibold leading-none text-gray-500">{{ $densitySub }}</span>
                    </div>
                </div>
            </div>
        </section>
        <section class="mb-6 mt-4 px-4 md:px-8 lg:mt-6">
            <div class="rounded-4xl w-full border border-slate-100 bg-white p-5 shadow-sm">
                <div class="grid grid-cols-4 gap-x-2 gap-y-6 sm:grid-cols-6 lg:flex lg:flex-wrap lg:justify-start lg:gap-8">
                    <a href="{{ route('explore') }}"
                        class="tap-target group flex flex-col items-center gap-1.5 transition-transform active:scale-95 lg:w-16">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-50 text-teal-600 transition-transform duration-300 group-hover:scale-105 lg:h-16 lg:w-16">
                            <svg class="h-7 w-7 lg:h-8 lg:w-8" viewBox="0 0 24 24" fill="none">
                                <path d="M4 5.5 9 4l6 2 5-1.5v13L15 19l-6-2-5 1.5V5.5Z" fill="currentColor"
                                    opacity="0.55" />
                                <path d="M12 8a2.6 2.6 0 0 0-2.6 2.6c0 1.9 2.6 5 2.6 5s2.6-3.1 2.6-5A2.6 2.6 0 0 0 12 8Z"
                                    fill="currentColor" />
                            </svg>
                        </div>
                        <span
                            class="text-center text-[11px] font-bold leading-tight text-gray-800 lg:text-xs">{{ __('Peta') }}</span>
                    </a>

                    <a href="{{ route('edutourism.index') }}"
                        class="tap-target group flex flex-col items-center gap-1.5 transition-transform active:scale-95 lg:w-20">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 transition-transform duration-300 group-hover:scale-105 lg:h-16 lg:w-16">
                            <svg class="h-7 w-7 lg:h-8 lg:w-8" viewBox="0 0 24 24" fill="none">
                                <path d="M12 4.2 21 8.6 12 13 3 8.6 12 4.2Z" fill="currentColor" />
                                <path d="M6.5 10.1v3.1a5.5 5.5 0 0 0 11 0v-3.1L12 13.4l-5.5-3.3Z" fill="currentColor"
                                    opacity="0.55" />
                                <path d="M19 9.3v4" stroke="#D4AF37" stroke-width="1.6" stroke-linecap="round" />
                                <circle cx="19" cy="14.1" r="1.15" fill="#D4AF37" />
                            </svg>
                        </div>
                        <span
                            class="text-center text-[11px] font-bold leading-tight text-gray-800 lg:text-xs">{{ __('Edutourism') }}</span>
                    </a>

                    <a href="{{ route('umkm') }}"
                        class="tap-target group flex flex-col items-center gap-1.5 transition-transform active:scale-95 lg:w-20">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 transition-transform duration-300 group-hover:scale-105 lg:h-16 lg:w-16">
                            <svg class="h-7 w-7 lg:h-8 lg:w-8" viewBox="0 0 24 24" fill="none">
                                <path d="M5 9h14l1 12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1L5 9Z" fill="currentColor"
                                    opacity="0.55" />
                                <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" />
                                <circle cx="15.5" cy="13.2" r="1.05" fill="currentColor" />
                            </svg>
                        </div>
                        <span
                            class="text-center text-[11px] font-bold leading-tight text-gray-800 lg:text-xs">{{ __('UMKM') }}</span>
                    </a>

                    <a href="{{ route('cultural-objects') }}"
                        class="tap-target group flex flex-col items-center gap-1.5 transition-transform active:scale-95 lg:w-20">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 transition-transform duration-300 group-hover:scale-105 lg:h-16 lg:w-16">
                            <svg class="h-7 w-7 lg:h-8 lg:w-8" viewBox="0 0 24 24" fill="none">
                                <rect x="3.5" y="17" width="17" height="3.5" rx="1" fill="currentColor" />
                                <rect x="5.5" y="13" width="13" height="3.5" rx="1" fill="currentColor"
                                    opacity="0.65" />
                                <rect x="7.5" y="9" width="9" height="3.5" rx="1" fill="currentColor" />
                                <rect x="9.5" y="5.5" width="5" height="3" rx="1" fill="currentColor"
                                    opacity="0.65" />
                                <path d="M12 2.2v2.3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                        </div>
                        <span
                            class="text-center text-[11px] font-bold leading-tight text-gray-800 lg:text-xs">{{ __('Budaya') }}</span>
                    </a>

                    <a href="{{ route('tour-packages') }}"
                        class="tap-target group flex flex-col items-center gap-1.5 transition-transform active:scale-95 lg:w-20">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition-transform duration-300 group-hover:scale-105 lg:h-16 lg:w-16">
                            <svg class="h-7 w-7 lg:h-8 lg:w-8" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 1 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 1 0 0-4V7Z"
                                    fill="currentColor" opacity="0.55" />
                                <path d="M15 5v2m0 3v2m0 3v2" stroke="currentColor" stroke-width="1.7"
                                    stroke-linecap="round" />
                            </svg>
                        </div>
                        <span
                            class="text-center text-[11px] font-bold leading-tight text-gray-800 lg:text-xs">{{ __('Tiket') }}</span>
                    </a>

                    <a href="{{ route('events') }}"
                        class="tap-target group flex flex-col items-center gap-1.5 transition-transform active:scale-95 lg:w-20">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition-transform duration-300 group-hover:scale-105 lg:h-16 lg:w-16">
                            <svg class="h-7 w-7 lg:h-8 lg:w-8" viewBox="0 0 24 24" fill="none">
                                <path d="M5 6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H5Z"
                                    fill="currentColor" opacity="0.55" />
                                <path d="M3 10h18v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2Z" fill="currentColor" />
                                <path d="M8 3v3M16 3v3" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" />
                                <circle cx="12" cy="15" r="1.3" fill="currentColor" />
                            </svg>
                        </div>
                        <span
                            class="text-center text-[11px] font-bold leading-tight text-gray-800 lg:text-xs">{{ __('Event') }}</span>
                    </a>

                    @if (Auth::check() && Auth::user()->isUmkmOwner())
                        <a href="{{ route('owner.dashboard') }}"
                            class="tap-target group flex flex-col items-center gap-1.5 transition-transform active:scale-95 lg:w-20">
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-50 text-purple-600 transition-transform duration-300 group-hover:scale-105 lg:h-16 lg:w-16">
                                <svg class="h-7 w-7 lg:h-8 lg:w-8" viewBox="0 0 24 24" fill="none">
                                    <rect x="4" y="4" width="7" height="7" rx="2"
                                        fill="currentColor" />
                                    <rect x="13" y="4" width="7" height="7" rx="2" fill="currentColor"
                                        opacity="0.55" />
                                    <rect x="4" y="13" width="7" height="7" rx="2" fill="currentColor"
                                        opacity="0.55" />
                                    <rect x="13" y="13" width="7" height="7" rx="2" fill="currentColor"
                                        opacity="0.55" />
                                </svg>
                            </div>
                            <span
                                class="text-center text-[11px] font-bold leading-tight text-gray-800 lg:text-xs">{{ __('Panel') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @push('modals')
        <!-- Premium Detail Weather Modal (Mobile Bottom-Sheet / Desktop Modal) -->
        <x-modal name="weather-modal">
            <div class="space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span
                        class="rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-emerald-600">{{ __('Desa Penglipuran') }}</span>
                    <button type="button" @click="isOpen = false"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition-all hover:text-gray-600 active:scale-95 md:hidden"
                        title="{{ __('Tutup') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <h3 class="font-display text-charcoal text-xl font-black leading-snug tracking-tight">
                    {{ __('Detail Cuaca Desa') }}</h3>

                @if (isset($weather) && $weather)
                    <!-- Main Status Grid -->
                    <div class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-gray-100 bg-white shadow-sm">
                            {!! $weather->getIconHtml() !!}
                        </div>
                        <div>
                            <p class="text-2xl font-black leading-none text-gray-800">{{ round($weather->temperature) }}°C</p>
                            <p class="mt-1.5 text-xs font-black leading-none text-gray-700">{{ __($weather->condition) }}</p>
                        </div>
                    </div>

                    <!-- Weather Parameters Grid -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Humidity -->
                        <div class="flex items-center gap-2.5 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 9.172V5L8 4z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold uppercase leading-none tracking-wider text-gray-400">
                                    {{ __('Kelembapan') }}
                                </p>
                                <p class="mt-1 text-xs font-black text-gray-700">{{ $weather->humidity }}%</p>
                            </div>
                        </div>

                        <!-- Wind -->
                        <div class="flex items-center gap-2.5 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold uppercase leading-none tracking-wider text-gray-400">
                                    {{ __('Angin') }}</p>
                                <p class="mt-1 text-xs font-black text-gray-700">{{ $weather->wind_speed }} km/h</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendation Box based on Weather Code -->
                    <div class="border-t border-gray-50 pt-3">
                        <h4 class="mb-1.5 text-[10px] font-black uppercase tracking-wider text-gray-400">
                            {{ __('Rekomendasi Aktivitas') }}
                        </h4>
                        <p class="text-xs leading-relaxed text-gray-500">
                            @if (\in_array($weather->weather_code, [0, 1, 2]))
                                {{ __('Cuaca sangat bersahabat untuk berjalan-jalan menjelajahi desa. Jangan lupa menggunakan tabir surya dan membawa air minum!') }}
                            @elseif(\in_array($weather->weather_code, [3, 45, 48]))
                                {{ __('Cuaca sejuk dan berawan, sangat ideal untuk berkeliling santai tanpa sengatan matahari.') }}
                            @elseif(\in_array($weather->weather_code, [51, 53, 55, 61, 63]))
                                {{ __('Gerimis atau hujan ringan. Disarankan membawa payung atau jas hujan saat menjelajahi pekarangan desa.') }}
                            @else
                                {{ __('Hujan deras atau badai terdeteksi. Sebaiknya bersantai di cafe lokal, mengunjungi rumah adat (bale), atau menikmati kuliner lokal di area tertutup.') }}
                            @endif
                        </p>
                    </div>

                    <!-- Footer Info / Updated Time -->
                    <div class="border-t border-gray-50 pt-3 text-center">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-gray-400">
                            {{ __('Pembaruan Terakhir:') }}
                            {{ $weather->updated_at->timezone('Asia/Makassar')->format('d M Y H:i') }} WITA
                        </p>
                    </div>
                @else
                    <!-- No Weather Info State -->
                    <div class="space-y-4 py-8 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-gray-700">{{ __('Data Cuaca Belum Tersedia') }}</h4>
                            <p class="mx-auto max-w-60 text-xs leading-relaxed text-gray-500">
                                {{ __('Silakan jalankan update cuaca dari sistem untuk memuat laporan cuaca terkini.') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </x-modal>

        <!-- Premium Detail Density Modal (Mobile Bottom-Sheet / Desktop Modal) -->
        <x-modal name="density-modal">
            <div class="space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span
                        class="rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-emerald-600">{{ __('Desa Penglipuran') }}</span>
                    <button type="button" @click="isOpen = false"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition-all hover:text-gray-600 active:scale-95 md:hidden"
                        title="{{ __('Tutup') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <h3 class="font-display text-charcoal text-xl font-black leading-snug tracking-tight">
                    {{ __('Kepadatan Pengunjung') }}</h3>

                <!-- Total Wisatawan Saat Ini -->
                <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-3.5">
                    <p class="text-[10px] font-bold uppercase leading-none tracking-wider text-gray-400">
                        {{ __('Total Wisatawan Saat Ini') }}</p>
                    <div class="mt-1.5 flex items-baseline gap-1.5">
                        <span class="text-3xl font-black leading-none text-gray-800">{{ $totalCurrent }}</span>
                        <span class="text-sm font-semibold text-gray-400">/ {{ $totalMax }}
                            {{ __('kapasitas total') }}</span>
                    </div>
                    @php($modalDensityPercent = $totalMax > 0 ? round(($totalCurrent / $totalMax) * 100) : 0)
                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-gray-100">
                        <div class="{{ $densityStatus['barColor'] }} h-full transition-all"
                            style="width: {{ min(100, $modalDensityPercent) }}%">
                        </div>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">
                        {{ $modalDensityPercent }}% —
                        <span class="{{ $densityStatus['color'] }} font-semibold">{{ $densityStatus['label'] }}</span>
                    </p>
                </div>

                <!-- Legend -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-xl bg-primary/10 p-3 text-center">
                        <p class="text-xs font-bold text-primary">{{ __('Aman') }}</p>
                        <p class="text-[11px] text-primary opacity-70">&lt; {{ $warningThreshold }}%</p>
                    </div>
                    <div class="rounded-xl bg-secondary/15 p-3 text-center">
                        <p class="text-xs font-bold text-secondary">{{ __('Sedang') }}</p>
                        <p class="text-[11px] text-secondary opacity-70">{{ $warningThreshold }}-{{ $criticalThreshold }}%</p>
                    </div>
                    <div class="rounded-xl bg-warning/10 p-3 text-center">
                        <p class="text-xs font-bold text-warning">{{ __('Penuh') }}</p>
                        <p class="text-[11px] text-warning opacity-70">&gt; {{ $criticalThreshold }}%</p>
                    </div>
                </div>

                <p class="border-t border-gray-50 pt-3 text-center text-[9px] font-bold uppercase tracking-wider text-gray-400">
                    {{ __('Data diperbarui setiap kali halaman dimuat ulang') }}
                </p>
            </div>
        </x-modal>
    @endpush
@endsection
