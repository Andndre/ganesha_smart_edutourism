@extends('layouts.app')
@section('title', __('Paket Wisata & Tiket Gerbang - Penglipuran'))
@section('header_title', __('Paket & Tiket'))

@section('content')
<div class="px-4 py-6 md:px-8 md:py-8 lg:px-12" x-data="{
    tab: '{{ $activeTab === 'tickets' ? 'tickets' : 'packages' }}',
    selectedPkgIndex: 0,
    packages: {{ Js::from($packages) }}
}">
    {{-- Header Row --}}
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <nav class="mb-1 text-xs font-medium text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-primary transition-colors">{{ __('Beranda') }}</a>
                <span class="mx-1.5">/</span>
                <span>{{ __('Informasi Wisata') }}</span>
                <span class="mx-1.5">/</span>
                <span class="text-charcoal font-semibold">{{ __('Paket Eduwisata & Tiket Gerbang') }}</span>
            </nav>
            <h1 class="font-playfair text-charcoal text-2xl font-bold md:text-3xl lg:text-4xl">
                {{ __('Katalog Paket Eduwisata & Tiket Resmi') }}
            </h1>
            <p class="mt-1 text-xs text-gray-600 md:text-sm">
                {{ __('Pilih tab untuk meninjau paket eduwisata bersama warga atau tarif tiket masuk gerbang desa.') }}
            </p>
        </div>

        {{-- Segmented Tab Switcher --}}
        <div class="inline-flex self-start rounded-full bg-slate-200/80 p-1 shadow-inner md:self-auto">
            <button type="button"
                @click="tab = 'packages'"
                :class="tab === 'packages' ? 'bg-white text-primary shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                class="tap-target flex items-center gap-1.5 rounded-full px-4 py-2 text-xs transition-all md:px-5 md:text-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>{{ __('Paket Eduwisata') }} (3)</span>
            </button>
            <button type="button"
                @click="tab = 'tickets'"
                :class="tab === 'tickets' ? 'bg-white text-primary shadow-xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
                class="tap-target flex items-center gap-1.5 rounded-full px-4 py-2 text-xs transition-all md:px-5 md:text-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                <span>{{ __('Tiket Masuk Gerbang') }}</span>
            </button>
        </div>
    </div>

    {{-- TAB 1: PAKET EDUWISATA IMERSIF --}}
    <div x-show="tab === 'packages'" x-cloak class="space-y-6">
        {{-- Official Inclusive Notice Banner --}}
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 text-emerald-900 shadow-xs">
            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-xs">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="text-xs leading-relaxed md:text-sm">
                <strong class="font-bold text-emerald-950">{{ __('Transparansi Tiket:') }}</strong>
                {{ __('Seluruh paket eduwisata di bawah ini SUDAH TERMASUK tiket masuk resmi gerbang desa & hutan bambu. Anda tidak perlu membayar tiket masuk gerbang terpisah.') }}
                <span class="block mt-1 text-[11px] text-emerald-800 md:text-xs">
                    {{ __('Reservasi difasilitasi langsung oleh pengelola desa via WhatsApp tanpa pembayaran daring.') }}
                </span>
            </div>
        </div>

        {{-- 3 Package Cards Grid --}}
        <div>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-playfair text-charcoal text-lg font-bold md:text-xl">
                    {{ __('Pilihan Pengalaman Eduwisata Otentik') }}
                </h2>
                <span class="text-xs text-gray-500">
                    {{ __('Klik paket untuk melihat rincian jadwal rundown di bawah') }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ($packages as $idx => $pkg)
                    <div @click="selectedPkgIndex = {{ $idx }}"
                        :class="selectedPkgIndex === {{ $idx }} ? 'ring-2 ring-primary border-transparent shadow-md' : 'border-gray-200 hover:border-gray-300 shadow-xs'"
                        class="group flex cursor-pointer flex-col justify-between overflow-hidden rounded-2xl border bg-white p-4 transition-all active:scale-[0.99]">
                        <div>
                            {{-- Image Thumbnail with Duration Badge --}}
                            <div class="relative mb-3.5 aspect-video w-full overflow-hidden rounded-xl bg-gray-100">
                                <img src="{{ asset('storage/' . $pkg['image']) }}"
                                    onerror="this.classList.add('hidden'); this.nextElementSibling?.classList.remove('hidden')"
                                    alt="{{ $pkg['title'] }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                                <div class="hidden absolute inset-0 flex items-center justify-center bg-gray-100 text-gray-400">
                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="absolute top-2.5 left-2.5 rounded-md px-2 py-0.5 text-[10px] font-bold shadow-xs {{ $pkg['badge_color'] }}">
                                    {{ $pkg['badge'] }}
                                </span>
                            </div>

                            {{-- Title & Price --}}
                            <h3 class="font-playfair text-charcoal text-base font-bold leading-snug md:text-lg">
                                {{ $pkg['title'] }}
                            </h3>
                            <div class="mt-2 flex items-baseline gap-1.5">
                                <span class="text-primary text-lg font-bold md:text-xl">{{ $pkg['price'] }}</span>
                                <span class="text-xs text-gray-500">{{ $pkg['unit'] }}</span>
                            </div>
                            <p class="mt-2 text-xs leading-relaxed text-gray-600 line-clamp-2">
                                {{ $pkg['tagline'] }}
                            </p>

                            {{-- Key Highlights List --}}
                            <ul class="mt-3.5 space-y-1.5 border-t border-gray-100 pt-3 text-xs text-gray-600">
                                @foreach ($pkg['highlights'] as $hl)
                                    <li class="flex items-start gap-1.5">
                                        <svg class="h-3.5 w-3.5 shrink-0 text-emerald-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="leading-tight">{{ $hl }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Selector Button (Explicit Tab Indicator) --}}
                        <div class="mt-4 pt-2">
                            <button type="button"
                                :class="selectedPkgIndex === {{ $idx }} ? 'bg-primary text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="tap-target flex w-full items-center justify-center gap-1.5 rounded-xl py-2.5 text-xs font-bold transition-all">
                                <span x-text="selectedPkgIndex === {{ $idx }} ? '{{ __('Jadwal & Rundown Aktif ↓') }}' : '{{ __('Lihat Jadwal & Rundown →') }}'"></span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Active Package Rundown & Inclusions Drawer / Box --}}
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm md:p-8">
            <template x-if="packages[selectedPkgIndex]">
                <div>
                    {{-- Header of Details Box --}}
                    <div class="mb-6 flex flex-col gap-3 border-b border-gray-100 pb-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <span class="text-primary text-[11px] font-bold tracking-wider uppercase">
                                {{ __('RUNDOWN LENGKAP & FASILITAS TERPILIH') }}
                            </span>
                            <h2 class="font-playfair text-charcoal text-xl font-bold md:text-2xl" x-text="packages[selectedPkgIndex].title + ' (' + packages[selectedPkgIndex].duration + ')'"></h2>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-1.5 text-xs font-semibold text-emerald-800 border border-emerald-200/60">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="packages[selectedPkgIndex].duration + ' • Sesi Budaya Imersif'"></span>
                        </div>
                    </div>

                    {{-- Split Content: Timeline (Left) + Booking & Inclusions (Right) --}}
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                        {{-- LEFT: Visual Stepper Timeline --}}
                        <div class="lg:col-span-7">
                            <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-gray-500">
                                {{ __('Alur & Jadwal Aktivitas:') }}
                            </h3>
                            <div class="space-y-4">
                                <template x-for="(step, sIdx) in packages[selectedPkgIndex].rundown" :key="sIdx">
                                    <div class="flex items-start gap-3.5">
                                        {{-- Step Indicator Badge --}}
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800 border border-emerald-300">
                                            <span x-text="sIdx + 1"></span>
                                        </div>
                                        {{-- Step Body --}}
                                        <div class="flex-1 rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5">
                                            <div class="flex items-center justify-between gap-2">
                                                <h4 class="text-xs font-bold text-gray-900 md:text-sm" x-text="step.title"></h4>
                                                <span class="rounded bg-white px-2 py-0.5 text-[10px] font-semibold text-emerald-800 shadow-2xs border border-gray-200" x-text="step.time"></span>
                                            </div>
                                            <p class="mt-1 text-xs leading-relaxed text-gray-600" x-text="step.desc"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- RIGHT: Inclusions & WhatsApp Action Card --}}
                        <div class="lg:col-span-5">
                            <div class="rounded-2xl border border-gray-200 bg-slate-50/90 p-5 shadow-xs">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">
                                    {{ __('Fasilitas & Layanan Sudah Termasuk:') }}
                                </h3>

                                <ul class="mt-3.5 space-y-2.5">
                                    <template x-for="(inc, iIdx) in packages[selectedPkgIndex].inclusions" :key="iIdx">
                                        <li class="flex items-start gap-2.5 text-xs text-gray-700">
                                            <div class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white">
                                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <span class="leading-snug" x-text="inc"></span>
                                        </li>
                                    </template>
                                </ul>

                                <div class="my-4 border-t border-gray-200"></div>

                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">{{ __('Biaya Paket') }}</span>
                                    <span class="text-primary text-base font-bold md:text-lg" x-text="packages[selectedPkgIndex].price + ' / orang'"></span>
                                </div>

                                {{-- WhatsApp Action Button --}}
                                <a :href="'https://wa.me/{{ $whatsappNumber }}?text=' + encodeURIComponent('Halo Pengelola Desa Wisata Penglipuran, saya tertarik reservasi ' + packages[selectedPkgIndex].title + ' untuk eduwisata.')"
                                    target="_blank" rel="noopener noreferrer"
                                    class="tap-target mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-700 py-3 text-xs font-bold text-white shadow-md shadow-emerald-700/20 transition-all hover:bg-emerald-800 active:scale-98">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.04 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67Z" />
                                    </svg>
                                    <span>{{ __('Tanya Ketersediaan via WhatsApp') }}</span>
                                </a>

                                <p class="mt-2.5 text-center text-[10.5px] leading-tight text-gray-500">
                                    {{ __('Reservasi dilayani oleh Paguyuban Pemandu Adat Desa Penglipuran.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- TAB 2: TIKET MASUK GERBANG RESMI LOKET --}}
    <div x-show="tab === 'tickets'" x-cloak class="space-y-6">
        {{-- Official Loket Info Notice --}}
        <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 p-4 text-amber-900 shadow-xs">
            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white shadow-xs">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
            </div>
            <div class="text-xs leading-relaxed md:text-sm">
                <strong class="font-bold text-amber-950">{{ __('Informasi Loket Gerbang:') }}</strong>
                {{ __('Tiket masuk desa dibeli langsung di Loket Gerbang Desa (Tunai & QRIS) atau secara online melalui mitra resmi kami (Traveloka & Tiket.com). Parkir kendaraan resmi dan akses hutan bambu 45 hektar sudah termasuk di dalam tiket masuk.') }}
            </div>
        </div>

        {{-- 3 Tickets Grid --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            {{-- Ticket 1: Domestik --}}
            <div class="flex flex-col justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-xs">
                <div>
                    <h3 class="font-playfair text-charcoal text-lg font-bold">
                        {{ __('Tiket Masuk Wisatawan Domestik') }}
                    </h3>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-primary text-2xl font-bold">Rp 25.000</span>
                        <span class="text-xs text-gray-500">{{ __('per orang (WNI)') }}</span>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-gray-600">
                        {{ __('Akses keliling lorong desa adat, hutan bambu konservasi 45 hektar, peta digital interaktif, dan parkir kendaraan resmi.') }}
                    </p>
                </div>
                <div class="mt-5 border-t border-gray-100 pt-3.5">
                    <div class="flex items-center justify-center gap-2 rounded-xl bg-slate-100 py-2.5 text-xs font-semibold text-gray-700">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        <span>{{ __('Tersedia di Loket Fisik & OTA Resmi') }}</span>
                    </div>
                </div>
            </div>

            {{-- Ticket 2: Mancanegara --}}
            <div class="flex flex-col justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-xs">
                <div>
                    <h3 class="font-playfair text-charcoal text-lg font-bold">
                        {{ __('Tiket Masuk Wisatawan Mancanegara') }}
                    </h3>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-primary text-2xl font-bold">Rp 50.000</span>
                        <span class="text-xs text-gray-500">{{ __('per orang (WNA)') }}</span>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-gray-600">
                        {{ __('Full village access, sacred bamboo forest sanctuary, interactive digital map, and English digital guide stories.') }}
                    </p>
                </div>
                <div class="mt-5 border-t border-gray-100 pt-3.5">
                    <div class="flex items-center justify-center gap-2 rounded-xl bg-slate-100 py-2.5 text-xs font-semibold text-gray-700">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        <span>{{ __('Tersedia di Loket Fisik & OTA Resmi') }}</span>
                    </div>
                </div>
            </div>

            {{-- Ticket 3: Pemandu Lokal Adat --}}
            <div class="flex flex-col justify-between rounded-2xl border-2 border-emerald-600 bg-white p-5 shadow-sm">
                <div>
                    <span class="inline-block rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">
                        {{ __('Rekomendasi Eduwisata') }}
                    </span>
                    <h3 class="font-playfair text-charcoal mt-1 text-lg font-bold">
                        {{ __('Tiket Masuk + Pemandu Lokal Adat') }}
                    </h3>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-primary text-2xl font-bold">Rp 100.000</span>
                        <span class="text-xs text-gray-500">{{ __('rombongan (maks. 5 orang)') }}</span>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-gray-600">
                        {{ __('Didampingi langsung oleh pemandu warga lokal Penglipuran untuk interpretasi filosofi budaya, arsitektur, dan adat istiadat.') }}
                    </p>
                </div>
                <div class="mt-5 border-t border-gray-100 pt-3.5">
                    <a href="https://wa.me/{{ $whatsappNumber }}?text={{ rawurlencode('Halo Pengelola Desa Wisata Penglipuran, saya ingin reservasi Pemandu Lokal Adat untuk kunjungan rombongan.') }}"
                        target="_blank" rel="noopener noreferrer"
                        class="tap-target flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-700 py-2.5 text-xs font-bold text-white shadow-xs transition-all hover:bg-emerald-800 active:scale-98">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.04 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67Z" />
                        </svg>
                        <span>{{ __('Tanya Pemandu via WhatsApp') }}</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Gate Operations & Facilities Card --}}
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-xs md:p-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <h3 class="font-playfair text-charcoal text-base font-bold md:text-lg">
                        {{ __('Jam Operasional & Akses Pos Gerbang') }}
                    </h3>
                    <ul class="mt-3 space-y-2 text-xs leading-relaxed text-gray-600 md:text-sm">
                        <li class="flex items-start gap-2">
                            <span class="font-bold text-gray-800">• {{ __('Jam Buka:') }}</span>
                            <span>{{ __('Setiap hari pukul 08.00 – 18.30 WITA.') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="font-bold text-gray-800">• {{ __('Lokasi Pos:') }}</span>
                            <span>{{ __('Gerbang Utama Selatan & Gerbang Parkir Hutan Bambu Utara.') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="font-bold text-gray-800">• {{ __('Fasilitas Termasuk:') }}</span>
                            <span>{{ __('Area parkir kendaraan, toilet umum bersih, dan akses penuh ke jalur hutan bambu konservasi 45 hektar.') }}</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-playfair text-charcoal text-base font-bold md:text-lg">
                        {{ __('Panduan Pembelian Tiket') }}
                    </h3>
                    <ul class="mt-3 space-y-2 text-xs leading-relaxed text-gray-600 md:text-sm">
                        <li class="flex items-start gap-2">
                            <span class="font-bold text-gray-800">• {{ __('Loket Fisik:') }}</span>
                            <span>{{ __('Pembelian langsung dapat dilakukan menggunakan uang tunai maupun QRIS di kasir gerbang.') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="font-bold text-gray-800">• {{ __('E-Ticket OTA:') }}</span>
                            <span>{{ __('Tersedia di Traveloka dan Tiket.com. Tunjukkan kode QR tiket pada petugas untuk dipindai saat tiba di gerbang.') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="font-bold text-gray-800">• {{ __('Pemandu Adat:') }}</span>
                            <span>{{ __('Dapat langsung dikoordinasikan di pos informasi desa atau melalui reservasi via WhatsApp.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

