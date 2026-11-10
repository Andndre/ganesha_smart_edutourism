@extends('layouts.app')

@section('title', __('Beranda - Penglipuran'))
@section('header_title', 'Smart Edutourism')

@section('content')
<div class="mx-auto max-w-7xl px-4 pb-16 pt-5 md:px-8 lg:px-12 lg:pt-6" x-data>
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(320px,1fr)]">
        <div class="order-2 flex min-h-[245px] flex-col justify-between rounded-3xl bg-[#173e23] p-6 text-white shadow-lg lg:order-1 lg:min-h-[330px] lg:p-9">
            <div>
                <p class="inline-flex rounded-full bg-white/15 px-3 py-1 text-[10px] font-bold uppercase tracking-[.16em]">✦ {{ __('Smart Edutourism · Desa Wisata Penglipuran') }}</p>
                <h1 class="mt-5 max-w-2xl font-display text-3xl font-bold leading-tight md:text-4xl lg:text-[2.5rem]">{{ __('Jelajahi Desa Penglipuran Berbasis Kearifan Lokal') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-white/85">{{ __('Temukan rumah adat, budaya, dan karya warga melalui rute GPS interaktif serta kisah yang hidup di setiap pekarangan.') }}</p>
            </div>
            <div class="mt-6 flex flex-wrap items-center gap-3">
                <a href="{{ route('edutourism.index') }}" class="tap-target rounded-full bg-white px-5 text-sm font-bold text-[#173e23]">{{ __('Mulai Tur Mandiri') }} →</a>
                <a href="{{ route('houses.index') }}" class="tap-target rounded-full border border-white/35 bg-white/10 px-5 text-sm font-semibold text-white">{{ __('Direktori Rumah Adat') }}</a>
            </div>
            <div class="mt-6 hidden flex-wrap gap-x-5 gap-y-1 border-t border-white/20 pt-3 text-xs text-white/75 lg:flex"><span>{{ $houseCount }} {{ __('pekarangan terverifikasi') }}</span><span>{{ $culturalCount }} {{ __('objek budaya') }}</span><span>{{ $routeCount }} {{ __('rute aktif') }}</span></div>
        </div>

        <div class="order-1 grid grid-cols-2 gap-3 lg:order-2 lg:grid-cols-1 lg:gap-4">
            <div class="col-span-2 hidden rounded-2xl border border-[#ebe9e4] bg-white p-5 shadow-sm lg:block">
                <div class="flex items-center justify-between"><h2 class="text-sm font-bold text-charcoal">{{ __('Kondisi Desa Hari Ini') }}</h2><span class="rounded-full bg-[#eaf3ec] px-2 py-1 text-xs font-semibold text-primary">{{ __('Informasi terkini') }}</span></div>
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <button type="button" @click="$dispatch('open-weather-modal')" class="tap-target flex min-w-0 items-center gap-3 rounded-xl bg-surface p-3 text-left"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-xl text-amber-700">☀</span><span class="min-w-0"><strong class="block text-sm text-charcoal">{{ $weather ? round($weather->temperature).'°C' : '—' }}</strong><span class="block truncate text-xs text-gray-600">{{ $weather?->getConditionText() ?? __('Cuaca belum tersedia') }}</span></span></button>
                    <button type="button" @click="$dispatch('open-density-modal')" class="tap-target flex min-w-0 items-center gap-3 rounded-xl bg-surface p-3 text-left"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#eaf3ec] text-primary"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19h5v-2a4 4 0 0 0-4-4m-9 6H3v-2a4 4 0 0 1 4-4m9-2a4 4 0 1 0-8 0m8 8H8v-2a4 4 0 0 1 8 0Z"/></svg></span><span class="min-w-0"><strong class="block text-sm text-primary">{{ __($densityStatus['label']) }}</strong><span class="block truncate text-xs text-gray-600">{{ round($densityPercent ?? ($totalMax > 0 ? $totalCurrent / $totalMax * 100 : 0)) }}% {{ __('kapasitas') }}</span></span></button>
                </div>
            </div>
            <button type="button" @click="$dispatch('open-weather-modal')" class="flex min-h-11 min-w-0 items-center gap-2 rounded-2xl border border-[#ebe9e4] bg-white p-3 text-left shadow-sm lg:hidden"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-xl text-amber-700">☀</span><span class="min-w-0"><strong class="block text-sm">{{ $weather ? round($weather->temperature).'°C' : '—' }}</strong><span class="block truncate text-xs text-gray-500">{{ $weather?->getConditionText() ?? __('Cuaca belum tersedia') }}</span></span></button>
            <button type="button" @click="$dispatch('open-density-modal')" class="flex min-h-11 min-w-0 items-center gap-2 rounded-2xl border border-[#ebe9e4] bg-white p-3 text-left shadow-sm lg:hidden"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#eaf3ec] text-primary"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19h5v-2a4 4 0 0 0-4-4m-9 6H3v-2a4 4 0 0 1 4-4m9-2a4 4 0 1 0-8 0m8 8H8v-2a4 4 0 0 1 8 0Z"/></svg></span><span class="min-w-0"><strong class="block text-sm text-primary">{{ __($densityStatus['label']) }}</strong><span class="block truncate text-xs text-gray-500">{{ round($totalMax > 0 ? $totalCurrent / $totalMax * 100 : 0) }}% {{ __('kapasitas') }}</span></span></button>
            <a href="{{ route('packages-tickets') }}" class="col-span-2 hidden items-center gap-4 rounded-2xl border border-[#ebe9e4] bg-white p-4 shadow-sm lg:flex"><span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-[#f8f1db] text-secondary"><svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v3a2 2 0 0 0 0 4v3H3v-3a2 2 0 0 0 0-4V7Zm9 0v10"/></svg></span><span><span class="text-[10px] font-bold uppercase tracking-widest text-amber-800">{{ __('Paket dan tiket resmi') }}</span><strong class="mt-1 block font-display text-lg">{{ __('Temukan pengalaman desa') }}</strong><span class="mt-1 block text-xs font-semibold text-primary">{{ __('Lihat Semua Pilihan') }} →</span></span></a>
        </div>
    </div>

    <section class="mt-9">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-2"><h2 class="font-display text-xl font-bold text-charcoal md:text-2xl">{{ __('Akses Cepat Modul Smart Edutourism') }}</h2><p class="hidden text-xs text-gray-500 lg:block">{{ __('Pilih modul untuk menjelajahi desa') }}</p></div>
        <div class="grid grid-cols-3 gap-3 md:grid-cols-6">
            @foreach([
                ['route' => 'explore', 'label' => __('Peta'), 'icon' => 'M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z'],
                ['route' => 'cultural-objects', 'label' => __('Budaya'), 'icon' => 'M3 10h18M5 10V5l7-3 7 3v5M6 10v10m4-10v10m4-10v10m4-10v10M3 21h18'],
                ['route' => 'houses.index', 'label' => __('Rumah Adat'), 'icon' => 'm3 10 9-7 9 7v10H3V10Zm6 10v-7h6v7'],
                ['route' => 'packages-tickets', 'label' => __('Paket & Tiket'), 'icon' => 'M3 7h18v3a2 2 0 0 0 0 4v3H3v-3a2 2 0 0 0 0-4V7Zm9 0v10'],
                ['route' => 'umkm', 'label' => __('UMKM'), 'icon' => 'M4 9h16l-1 11H5L4 9Zm2-5h12l2 5H4l2-5Zm4 5v4a2 2 0 0 0 4 0V9'],
                ['route' => 'events', 'label' => __('Event'), 'icon' => 'M4 5h16v16H4V5Zm4-3v6m8-6v6M4 10h16'],
            ] as $module)
                <a href="{{ route($module['route']) }}" class="tap-target group flex min-h-24 flex-col items-center justify-center gap-2 rounded-2xl border border-[#ebe9e4] bg-white p-3 text-center shadow-sm transition-shadow hover:shadow-md"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#eaf3ec] text-primary"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $module['icon'] }}"/></svg></span><span class="text-xs font-bold text-charcoal md:text-sm">{{ $module['label'] }}</span></a>
            @endforeach
        </div>
    </section>

    <section class="mt-9 grid gap-6 lg:grid-cols-2">
        <div><div class="mb-3 flex items-center justify-between gap-2"><h2 class="font-display text-xl font-bold text-charcoal md:text-2xl">{{ __('Warisan Budaya Unggulan') }}</h2><a href="{{ route('cultural-objects') }}" class="text-xs font-bold text-primary">{{ __('Lihat semua') }} ({{ $culturalCount }}) →</a></div>
            @if($featuredCultural)
                <a href="{{ route('cultural-object', ['slug' => $featuredCultural->slug]) }}" class="block rounded-2xl border border-[#ebe9e4] bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                    @if(($featuredCultural->historical_images ?? [])[0] ?? null)<img src="{{ asset('storage/'.$featuredCultural->historical_images[0]) }}" alt="{{ translateValue($featuredCultural->name) }}" loading="lazy" class="mb-4 aspect-[16/7] w-full rounded-xl object-cover">@endif
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800">{{ __('Kompleks budaya') }} · {{ $featuredCultural->children->count() }} {{ __('ruang') }}</span>
                    <h3 class="mt-1 font-display text-xl font-bold text-charcoal">{{ translateValue($featuredCultural->name) }}</h3><p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ translateValue($featuredCultural->short_description) }}</p>
                </a>
            @else
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-600">{{ __('Cerita budaya akan tampil setelah diterbitkan.') }}</div>
            @endif
        </div>
        <div><div class="mb-3 flex items-center justify-between gap-2"><h2 class="font-display text-xl font-bold text-charcoal md:text-2xl">{{ __('Karya & Kuliner Desa') }}</h2><a href="{{ route('umkm', ['tab' => 'direktori']) }}" class="text-xs font-bold text-primary">{{ __('Lihat Semua') }} →</a></div>
            <div class="grid grid-cols-2 gap-3">
                @forelse(array_slice($featuredUmkm, 0, 4) as $umkm)
                    <a href="{{ route('umkm.store', ['id' => $umkm['id']]) }}" class="overflow-hidden rounded-2xl border border-[#ebe9e4] bg-white p-3 shadow-sm transition-shadow hover:shadow-md">
                        @if(!empty($umkm['image_path']))<img src="{{ asset('storage/'.$umkm['image_path']) }}" alt="{{ translateValue($umkm['business_name']) }}" loading="lazy" class="aspect-[4/3] w-full rounded-xl object-cover">@else<div class="flex aspect-[4/3] items-center justify-center rounded-xl bg-[#eaf3ec] text-primary"><svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 9h16l-1 11H5L4 9Zm2-5h12l2 5H4l2-5Z"/></svg></div>@endif
                        <h3 class="mt-3 line-clamp-2 text-sm font-bold text-charcoal">{{ translateValue($umkm['business_name']) }}</h3><p class="mt-1 text-xs text-gray-600">{{ $umkm['owner_name'] ?? __('Warga Penglipuran') }}</p>
                    </a>
                @empty
                    <div class="col-span-2 rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-600">{{ __('UMKM warga akan tampil setelah tersedia.') }}</div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@include('home-modals')
@endsection
