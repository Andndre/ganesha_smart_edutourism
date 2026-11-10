@extends('layouts.app')

@section('title', __('Beranda - Penglipuran'))
@section('header_title', 'Smart Edutourism')

@section('content')
<div class="mx-auto w-full max-w-[1376px] px-4 pb-20 pt-4 md:px-8 lg:px-12 lg:pb-12 lg:pt-6" x-data>
    <div class="mb-4 grid grid-cols-2 gap-3 md:hidden">
        <button type="button" @click="$dispatch('open-weather-modal')" class="tap-target flex min-w-0 items-center gap-2 rounded-2xl border border-[#ebe9e4] bg-white p-3 text-left shadow-sm transition hover:border-amber-200">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700">{!! $weather?->getIconHtml() ?? '<i class="fa-solid fa-sun" aria-hidden="true"></i>' !!}</span>
            <span class="min-w-0"><strong class="block text-sm text-charcoal">{{ $weather ? round($weather->temperature).'°C' : '—' }}</strong><span class="block truncate text-xs text-gray-500">{{ $weather?->getConditionText() ?? __('Cuaca belum tersedia') }}</span></span>
        </button>
        <button type="button" @click="$dispatch('open-density-modal')" @class([
            'tap-target flex min-w-0 items-center gap-2 rounded-2xl border border-[#ebe9e4] bg-white p-3 text-left shadow-sm transition',
            'hover:border-emerald-200' => $densityStatus['key'] === 'safe',
            'hover:border-amber-200' => $densityStatus['key'] === 'medium',
            'hover:border-rose-200' => $densityStatus['key'] === 'full',
        ])>
            <span @class([
                'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl',
                'bg-emerald-50 text-emerald-700' => $densityStatus['key'] === 'safe',
                'bg-amber-50 text-amber-700' => $densityStatus['key'] === 'medium',
                'bg-rose-50 text-rose-700' => $densityStatus['key'] === 'full',
            ])><x-density-status-icon :status="$densityStatus['key']" class="h-5 w-5" /></span>
            <span class="min-w-0"><strong @class([
                'block text-sm',
                'text-emerald-800' => $densityStatus['key'] === 'safe',
                'text-amber-800' => $densityStatus['key'] === 'medium',
                'text-rose-800' => $densityStatus['key'] === 'full',
            ])>{{ __($densityStatus['label']) }} ({{ round($densityPercent) }}%)</strong><span class="block truncate text-xs text-gray-500">{{ $totalCurrent }} {{ __('Pengunjung Aktif') }}</span></span>
        </button>
    </div>

    <section class="grid grid-cols-1 items-stretch gap-5 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,1fr)]" aria-label="{{ __('Pengantar Desa Penglipuran') }}">
        <div class="flex min-h-40 flex-col justify-between rounded-3xl bg-[#173e23] p-5 text-white shadow-[0_16px_32px_-20px_rgba(23,62,35,0.65)] md:p-6 lg:min-h-[330px] lg:p-8">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-[.12em] text-white">
                    <span class="hidden text-[#f2c94c] lg:inline" aria-hidden="true">✦</span>
                    <span class="lg:hidden">{{ __('Smart Edutourism · Tur Mandiri') }}</span>
                    <span class="hidden lg:inline">{{ __('Smart Edutourism · Desa Wisata Penglipuran') }}</span>
                </p>
                <h1 class="mt-3 max-w-2xl font-display text-[22px] font-bold leading-[1.18] tracking-tight text-white lg:mt-4 lg:text-[2rem]">
                    <span class="lg:hidden">{{ __(':count Rute Wisata', ['count' => $routeCount]) }}</span>
                    <span class="hidden lg:inline">{{ __('Eksplorasi Desa Terbersih di Dunia Berbasis Kearifan Lokal') }}</span>
                </h1>
                <p class="mt-1 line-clamp-1 text-xs leading-relaxed text-white/80 lg:hidden">{{ __(':count rute GPS interaktif & pecahkan kuis adat di setiap pos.', ['count' => $routeCount]) }}</p>
                <p class="mt-3 hidden max-w-2xl text-sm leading-relaxed text-white/80 lg:block">
                    {{ __('Jelajahi :houses rumah adat berporos kosmologi Sanga Mandala, :routes rute wisata berpanduan GPS interaktif, dan dengarkan narasi filosofi otentik para tetua adat.', ['houses' => $houseCount, 'routes' => $routeCount]) }}
                </p>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-3 lg:mt-6">
                <a href="{{ route('edutourism.index') }}" class="tap-target inline-flex items-center justify-center gap-2 rounded-full bg-white px-5 text-sm font-bold text-[#173e23] transition hover:bg-[#f4f2ec] focus-visible:outline-white">
                    {{ __('Mulai Tur Mandiri') }}
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
                <a href="{{ route('houses.index') }}" class="hidden min-h-11 min-w-11 items-center justify-center gap-2 rounded-full border border-white/30 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/15 lg:inline-flex">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6"/></svg>
                    {{ __('Direktori :count Rumah Adat', ['count' => $houseCount]) }}
                </a>
            </div>

            <div class="mt-6 hidden flex-wrap gap-x-5 gap-y-2 border-t border-white/20 pt-3 text-[11px] text-white/75 lg:flex">
                <span>{{ $houseCount }} {{ __('pekarangan terverifikasi') }}</span>
                <span>{{ $culturalCount }} {{ __('objek budaya') }} · {{ __('Tri Hita Karana') }}</span>
                <span>{{ $routeCount }} {{ __('rute aktif') }} · {{ __('Jelajah dengan berjalan kaki') }}</span>
            </div>
        </div>

        <section
            x-data="{
                activeIndex: 0,
                slideCount: {{ count($featuredPackages) }},
                timerId: null,
                userPaused: false,
                pointerPaused: false,
                focusPaused: false,
                visibilityHandler: null,
                desktopQuery: null,
                breakpointHandler: null,
                init() {
                    this.desktopQuery = window.matchMedia('(min-width: 1024px)');
                    this.breakpointHandler = () => this.desktopQuery.matches ? this.startAutoplay() : this.stopAutoplay();
                    this.desktopQuery.addEventListener('change', this.breakpointHandler);
                    this.visibilityHandler = () => document.hidden ? this.stopAutoplay() : this.startAutoplay();
                    document.addEventListener('visibilitychange', this.visibilityHandler);
                    this.startAutoplay();
                },
                destroy() {
                    this.stopAutoplay();
                    document.removeEventListener('visibilitychange', this.visibilityHandler);
                    this.desktopQuery.removeEventListener('change', this.breakpointHandler);
                },
                next() {
                    this.activeIndex = (this.activeIndex + 1) % this.slideCount;
                },
                previous() {
                    this.activeIndex = (this.activeIndex - 1 + this.slideCount) % this.slideCount;
                },
                select(index) {
                    this.activeIndex = index;
                },
                startAutoplay() {
                    if (this.timerId || this.slideCount < 2 || !this.desktopQuery.matches || this.userPaused || this.pointerPaused || this.focusPaused || document.hidden) return;
                    this.timerId = window.setInterval(() => this.next(), 10000);
                },
                stopAutoplay() {
                    if (!this.timerId) return;
                    window.clearInterval(this.timerId);
                    this.timerId = null;
                },
                toggleAutoplay() {
                    this.userPaused = !this.userPaused;
                    this.userPaused ? this.stopAutoplay() : this.startAutoplay();
                }
            }"
            @mouseenter="pointerPaused = true; stopAutoplay()"
            @mouseleave="pointerPaused = false; startAutoplay()"
            @focusin="focusPaused = true; stopAutoplay()"
            @focusout="$nextTick(() => { if (!$el.contains(document.activeElement)) { focusPaused = false; startAutoplay() } })"
            class="relative hidden h-full min-w-0 lg:block"
            role="region"
            aria-roledescription="carousel"
            aria-label="{{ __('Paket Eduwisata Unggulan') }}"
            aria-live="off">
            <div class="grid h-full min-h-[330px] grid-cols-1 grid-rows-1 overflow-hidden rounded-3xl border border-[#ebe9e4] bg-white shadow-sm">
                @forelse ($featuredPackages as $index => $package)
                    <article
                        x-show="activeIndex === {{ $index }}"
                        x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        x-cloak
                        class="col-start-1 row-start-1 grid h-full grid-cols-[minmax(0,0.78fr)_minmax(0,1fr)] overflow-hidden rounded-3xl bg-white"
                        role="group"
                        aria-roledescription="slide"
                        aria-label="{{ __('Paket') }} {{ $index + 1 }} {{ __('dari') }} {{ count($featuredPackages) }}"
                        :aria-hidden="activeIndex !== {{ $index }}">
                        <div class="relative min-h-[160px] overflow-hidden bg-[#eaf3ec] lg:min-h-[330px]">
                            <img src="{{ asset($package['image']) }}" alt="{{ $package['title'] }}" class="absolute inset-0 h-full w-full object-cover" @if($index > 0) loading="lazy" @else fetchpriority="high" @endif>
                            <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-800 shadow-sm">{{ $package['badge'] }}</span>
                            <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 rounded-full bg-black/55 px-2.5 py-1 text-[10px] font-semibold text-white backdrop-blur-sm">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
                                {{ $package['duration'] }} · {{ __('Termasuk tiket masuk') }}
                            </span>
                        </div>

                        <div class="flex min-h-[280px] min-w-0 flex-col p-4 md:p-5 lg:min-h-[330px] lg:p-5">
                            <p class="inline-flex self-start rounded-full bg-[#f6f0dc] px-2.5 py-1 text-[9px] font-bold uppercase tracking-wide text-amber-800">{{ __('Paket Eduwisata Unggulan') }}</p>
                            <h2 class="mt-2 font-display text-xl font-bold leading-snug text-charcoal md:text-2xl lg:text-lg xl:text-xl">{{ $package['title'] }}</h2>
                            <p class="mt-1 flex flex-wrap items-baseline gap-x-1.5 gap-y-0.5">
                                <strong class="text-base font-bold text-primary md:text-lg">{{ $package['price'] }}</strong>
                                <span class="text-[11px] text-gray-500">/ {{ $package['unit'] }}</span>
                            </p>
                            <p class="mt-3 line-clamp-2 text-[11px] leading-relaxed text-gray-600">{{ $package['tagline'] }}</p>
                            <ul class="mt-2 hidden space-y-1 text-[10px] leading-snug text-primary md:block">
                                @foreach (array_slice($package['highlights'], 0, 3) as $highlight)
                                    <li class="flex items-start gap-1.5">
                                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                                        <span class="line-clamp-1">{{ $highlight }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-auto pt-4">
                                <a href="{{ route('packages-tickets', ['tab' => 'packages']) }}" class="tap-target flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-3 text-xs font-bold text-white transition hover:bg-[#173e23]">
                                    {{ __('Lihat Detail & Reservasi') }}
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                                </a>
                                <div class="mt-1 flex flex-col items-center">
                                    <a href="{{ route('packages-tickets', ['tab' => 'packages']) }}" class="inline-flex min-h-6 items-center whitespace-nowrap text-[10px] font-bold text-primary hover:underline">{{ __('Lihat seluruh paket dan tiket (:count)', ['count' => count($featuredPackages)]) }} →</a>
                                    <div class="flex shrink-0 items-center" aria-label="{{ __('Pilih paket') }}">
                                        @foreach ($featuredPackages as $dotIndex => $dotPackage)
                                            <button type="button" @click="select({{ $dotIndex }})" class="tap-target flex h-7 items-center justify-center" :aria-current="activeIndex === {{ $dotIndex }} ? 'true' : 'false'" aria-label="{{ __('Tampilkan paket') }} {{ $dotIndex + 1 }}">
                                                <span class="h-1.5 rounded-full transition-all duration-200" :class="activeIndex === {{ $dotIndex }} ? 'w-4 bg-primary' : 'w-1.5 bg-gray-300'"></span>
                                            </button>
                                        @endforeach
                                        <button type="button" @click="toggleAutoplay()" class="tap-target flex h-7 w-7 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-primary" :aria-label="userPaused ? '{{ __('Lanjutkan putar otomatis') }}' : '{{ __('Jeda putar otomatis') }}'" :aria-pressed="userPaused.toString()">
                                            <svg x-show="userPaused" x-cloak class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                                            <svg x-show="!userPaused" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 5h4v14H7zm6 0h4v14h-4z"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-start-1 row-start-1 flex min-h-[330px] flex-col items-center justify-center rounded-3xl border border-dashed border-gray-300 bg-white p-8 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[#f6f0dc] text-amber-800"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v3a2 2 0 0 0 0 4v3H3v-3a2 2 0 0 0 0-4V7Zm9 0v10"/></svg></span>
                        <h2 class="mt-3 font-display text-lg font-bold text-charcoal">{{ __('Paket eduwisata belum tersedia') }}</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Jelajahi pilihan tiket dan informasi wisata desa.') }}</p>
                        <a href="{{ route('packages-tickets') }}" class="tap-target mt-4 inline-flex items-center rounded-full bg-primary px-4 text-sm font-bold text-white">{{ __('Lihat Paket & Tiket') }}</a>
                    </div>
                @endforelse
            </div>

            @if (count($featuredPackages) > 1)
                <button type="button" @click="previous()" class="tap-target absolute left-2 top-28 z-20 flex h-10 w-10 items-center justify-center rounded-full border border-gray-100 bg-white text-charcoal shadow-md transition hover:scale-105 hover:text-primary focus-visible:ring-2 focus-visible:ring-primary lg:-left-4 lg:top-1/2 lg:-translate-y-1/2" aria-label="{{ __('Paket sebelumnya') }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                </button>
                <button type="button" @click="next()" class="tap-target absolute right-2 top-28 z-20 flex h-10 w-10 items-center justify-center rounded-full border border-gray-100 bg-white text-charcoal shadow-md transition hover:scale-105 hover:text-primary focus-visible:ring-2 focus-visible:ring-primary lg:-right-4 lg:top-1/2 lg:-translate-y-1/2" aria-label="{{ __('Paket berikutnya') }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                </button>
            @endif
        </section>
    </section>

    <section class="mt-4 lg:mt-9">
        <div class="mb-4 hidden flex-wrap items-end justify-between gap-2 lg:flex">
            <h2 class="font-display text-xl font-bold text-charcoal md:text-2xl">{{ __('Akses Cepat Modul Smart Edutourism') }}</h2>
            <p class="hidden text-xs text-gray-500 lg:block">{{ __('Pilih modul navigasi atau direktori desa yang ingin dijelajahi') }}</p>
        </div>
        <div class="grid grid-cols-3 gap-3 lg:grid-cols-6">
            @foreach ([
                ['route' => 'explore', 'label' => __('Peta Interaktif'), 'subtitle' => __('Navigasi GPS & Pos'), 'icon' => 'M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z'],
                ['route' => 'cultural-objects', 'label' => __('Warisan Budaya'), 'subtitle' => __('Kosmologi Tri Hita'), 'icon' => 'M3 10h18M5 10V5l7-3 7 3v5M6 10v10m4-10v10m4-10v10m4-10v10M3 21h18'],
                ['route' => 'houses.index', 'label' => __('Rumah Adat'), 'subtitle' => __('Sanga Mandala'), 'icon' => 'm3 10 9-7 9 7v10H3V10Zm6 10v-7h6v7'],
                ['route' => 'packages-tickets', 'label' => __('Paket & Tiket'), 'subtitle' => __('Katalog & Loket'), 'icon' => 'M3 7h18v3a2 2 0 0 0 0 4v3H3v-3a2 2 0 0 0 0-4V7Zm9 0v10'],
                ['route' => 'umkm', 'label' => __('Direktori UMKM'), 'subtitle' => __('Kuliner, Kriya & Kafe'), 'icon' => 'M4 9h16l-1 11H5L4 9Zm2-5h12l2 5H4l2-5Zm4 5v4a2 2 0 0 0 4 0V9'],
                ['route' => 'events', 'label' => __('Kalender Acara'), 'subtitle' => __('Jadwal Upacara'), 'icon' => 'M4 5h16v16H4V5Zm4-3v6m8-6v6M4 10h16'],
            ] as $module)
                <a href="{{ route($module['route']) }}" class="group flex min-h-[88px] min-w-0 flex-col items-center justify-center gap-2 rounded-2xl border border-[#ebe9e4] bg-white p-3 text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md lg:min-h-[72px] lg:flex-row lg:justify-start lg:gap-3 lg:text-left">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50"><x-module-icon :name="$module['route']" /></span>
                    <span class="min-w-0"><span class="block text-xs font-bold text-charcoal lg:hidden">{{ match ($module['route']) { 'explore' => __('Peta'), 'cultural-objects' => __('Budaya'), 'umkm' => __('UMKM'), 'events' => __('Event'), default => $module['label'] } }}</span><span class="hidden truncate text-[13px] font-bold text-charcoal lg:block">{{ $module['label'] }}</span><span class="mt-0.5 hidden truncate text-[10px] text-gray-500 lg:block">{{ $module['subtitle'] }}</span></span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mt-5 lg:mt-9">
        <div class="mb-4 hidden flex-wrap items-end justify-between gap-2 lg:flex">
            <h2 class="font-display text-xl font-bold text-charcoal md:text-2xl">{{ __('Sorotan Warisan & Cita Rasa Otentik Desa') }}</h2>
            <p class="hidden text-xs text-gray-500 lg:block">{{ __('Dokumentasi keaslian budaya dan karya kriya warga adat') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 lg:gap-6">
            <div class="min-w-0">
                <div class="mb-3 flex items-center justify-between gap-2 lg:hidden">
                    <h2 class="text-sm font-bold text-charcoal">{{ __('Warisan Budaya Unggulan') }}</h2>
                    <a href="{{ route('cultural-objects') }}" class="text-xs font-semibold text-primary">{{ __('Lihat semua') }} ({{ $culturalCount }}) →</a>
                </div>
                @if ($featuredCultural)
                    <a href="{{ route('cultural-object', ['slug' => $featuredCultural->slug]) }}" class="flex items-center gap-3 rounded-2xl border border-[#ebe9e4] bg-white p-3 shadow-sm lg:hidden">
                        @if (($featuredCultural->historical_images ?? [])[0] ?? null)
                            <img src="{{ asset('storage/'.$featuredCultural->historical_images[0]) }}" alt="{{ translateValue($featuredCultural->name) }}" loading="lazy" class="h-[72px] w-[72px] shrink-0 rounded-xl object-cover">
                        @endif
                        <span class="min-w-0">
                            <span class="inline-block rounded border border-amber-200 bg-amber-50 px-1.5 py-0.5 text-[9px] font-bold uppercase text-amber-800">{{ __('Kompleks budaya') }} · {{ $featuredCultural->children->count() }} {{ __('ruang') }}</span>
                            <span class="mt-1 block truncate font-display text-base font-bold text-charcoal">{{ translateValue($featuredCultural->name) }}</span>
                            <span class="mt-0.5 block truncate text-xs text-gray-600">{{ translateValue($featuredCultural->short_description) }}</span>
                        </span>
                    </a>
                @else
                    <p class="rounded-2xl border border-dashed border-gray-300 bg-white p-4 text-sm text-gray-600 lg:hidden">{{ __('Cerita budaya akan tampil setelah diterbitkan.') }}</p>
                @endif
            <section class="hidden h-full rounded-3xl border border-[#ebe9e4] bg-white p-5 shadow-sm lg:block" aria-labelledby="featured-culture-heading">
                @if ($featuredCultural)
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-800">{{ __('Kompleks Induk Utama') }}</p>
                            <h3 id="featured-culture-heading" class="mt-1 truncate font-display text-lg font-bold text-charcoal md:text-xl">{{ translateValue($featuredCultural->name) }}</h3>
                        </div>
                        <a href="{{ route('cultural-object', ['slug' => $featuredCultural->slug]) }}" class="tap-target inline-flex shrink-0 items-center gap-1 text-xs font-bold text-primary hover:underline">{{ __('Eksplorasi Ruang') }} <span aria-hidden="true">→</span></a>
                    </div>
                    @if (($featuredCultural->historical_images ?? [])[0] ?? null)
                        <a href="{{ route('cultural-object', ['slug' => $featuredCultural->slug]) }}" class="block overflow-hidden rounded-2xl bg-[#eaf3ec]">
                            <img src="{{ asset('storage/'.$featuredCultural->historical_images[0]) }}" alt="{{ translateValue($featuredCultural->name) }}" loading="lazy" class="aspect-[16/6] w-full object-cover transition duration-500 hover:scale-[1.02]">
                        </a>
                    @else
                        <div class="flex aspect-[16/6] items-center justify-center rounded-2xl bg-[#eaf3ec] px-6 text-center text-sm text-primary">{{ __('Cerita budaya akan tampil setelah diterbitkan.') }}</div>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($featuredCultural->children->take(4) as $culturalRoom)
                            <span class="bg-surface rounded-full border border-[#ebe9e4] px-3 py-1 text-[11px] text-gray-600">{{ translateValue($culturalRoom->name) }}</span>
                        @empty
                            <span class="bg-surface rounded-full border border-[#ebe9e4] px-3 py-1 text-[11px] text-gray-600">{{ __('Tri Hita Karana') }}</span>
                        @endforelse
                    </div>
                @else
                    <div class="flex min-h-72 flex-col justify-center rounded-2xl border border-dashed border-gray-300 p-6 text-center">
                        <h3 id="featured-culture-heading" class="font-display text-lg font-bold text-charcoal">{{ __('Warisan Budaya Unggulan') }}</h3>
                        <p class="mt-2 text-sm text-gray-600">{{ __('Cerita budaya akan tampil setelah diterbitkan.') }}</p>
                    </div>
                @endif
            </section>
            </div>

            <section class="min-w-0 lg:rounded-3xl lg:border lg:border-[#ebe9e4] lg:bg-white lg:p-5 lg:shadow-sm" aria-labelledby="featured-umkm-heading">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="hidden text-[10px] font-bold uppercase tracking-wider text-amber-800 lg:block">{{ __('Karya & Kuliner Khas Desa') }}</p>
                        <h3 id="featured-umkm-heading" class="text-sm font-bold text-charcoal lg:mt-1 lg:font-display lg:text-xl"><span class="lg:hidden">{{ __('Karya & Kuliner Desa') }}</span><span class="hidden lg:inline">{{ __('Produk UMKM Unggulan Warga Adat') }}</span></h3>
                    </div>
                    <a href="{{ route('umkm', ['tab' => 'direktori']) }}" class="tap-target inline-flex shrink-0 items-center gap-1 text-xs font-bold text-primary hover:underline">{{ __('Lihat Semua') }} <span class="hidden lg:inline">({{ $umkmCount }})</span> <span aria-hidden="true">→</span></a>
                </div>

                <div class="grid grid-cols-2 gap-2 lg:gap-3">
                    @forelse (array_slice($featuredUmkm, 0, 2) as $umkm)
                        @php
                            $productName = ($umkm['product_name'] ?? null) ?: translateValue($umkm['business_name'] ?? null);
                            $productImage = ($umkm['product_image_path'] ?? null) ?: ($umkm['image_path'] ?? null);
                            $productPrice = $umkm['product_price'] ?? null;
                            $houseNumber = $umkm['house_number'] ?? null;
                        @endphp
                        <a href="{{ route('umkm.store', ['id' => $umkm['id']]) }}" class="bg-surface group overflow-hidden rounded-2xl border border-[#ebe9e4] p-2.5 transition hover:border-[#d7dfd7] hover:shadow-sm">
                            @if ($productImage)
                                <img src="{{ asset('storage/'.$productImage) }}" alt="{{ $productName }}" loading="lazy" class="aspect-[4/3] w-full rounded-xl object-cover transition duration-500 group-hover:scale-[1.02] lg:aspect-[16/8]">
                            @else
                                <div class="flex aspect-[16/8] items-center justify-center rounded-xl bg-[#eaf3ec] text-primary"><svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 9h16l-1 11H5L4 9Zm2-5h12l2 5H4l2-5Z"/></svg></div>
                            @endif
                            <h4 class="mt-2 line-clamp-1 text-xs font-bold text-charcoal md:text-[13px]"><span class="lg:hidden">{{ translateValue($umkm['business_name']) }}</span><span class="hidden lg:inline">{{ $productName }}</span></h4>
                            <p class="mt-0.5 truncate text-[11px] text-gray-500 lg:hidden">{{ $umkm['owner_name'] ?? __('Warga Penglipuran') }}</p>
                            <div class="mt-1 hidden items-center justify-between gap-2 text-[10px] lg:flex">
                                @if ($productPrice !== null)
                                    <span class="font-bold text-primary">Rp {{ number_format((float) $productPrice, 0, ',', '.') }}</span>
                                @else
                                    <span class="font-semibold text-primary">{{ translateValue($umkm['business_name']) }}</span>
                                @endif
                                <span class="truncate text-right text-gray-500">{{ $houseNumber ? __('Pekarangan No.').' '.$houseNumber : __('Warga Penglipuran') }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-600">{{ __('UMKM warga akan tampil setelah tersedia.') }}</div>
                    @endforelse
                </div>
            </section>
        </div>
    </section>
</div>
@endsection
