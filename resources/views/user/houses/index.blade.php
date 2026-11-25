@extends('layouts.app')

@section('title', __('Pekarangan Tradisional Penglipuran'))
@section('header_title', __('Pekarangan'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 pb-12 pt-6 md:px-8 lg:px-12">
        <nav class="mb-5 text-xs text-gray-500"><a href="{{ route('home') }}"
                class="hover:text-primary">{{ __('Beranda') }}</a> / {{ __('Pekarangan') }}</nav>
        <div class="bg-primary mb-8 rounded-3xl p-6 text-white shadow-[0_16px_32px_-20px_rgba(41,53,24,0.45)] md:p-10">
            <p
                class="text-accent mb-3 inline-flex rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest">
                {{ __('Pekarangan Tradisional Penglipuran') }}</p>
            <h1 class="font-display max-w-2xl text-3xl font-bold leading-tight md:text-5xl">
                {{ __('Jelajahi pekarangan warga') }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-white/85 md:text-base">
                {{ __('Kenali keluarga, ruang budaya, dan UMKM di setiap pekarangan yang terbuka untuk wisatawan.') }}</p>
            <p class="text-accent mt-5 text-xs font-semibold uppercase tracking-widest">{{ $houseCount.' '.__('pekarangan terverifikasi') }}</p>
        </div>

        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="font-display text-charcoal text-2xl font-bold">{{ __('Direktori Pekarangan') }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ __('Cari nomor pekarangan yang sedang Anda lewati.') }}</p>
            </div>
            <form action="{{ route('houses.index') }}" method="GET" class="flex w-full max-w-md gap-2">
                <label for="house-number-search" class="sr-only">{{ __('Nomor rumah') }}</label>
                <input id="house-number-search" name="q" inputmode="numeric" value="{{ $search }}"
                    placeholder="{{ __('Contoh: 18') }}"
                    class="focus:border-primary min-h-11 min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-4 text-sm focus:outline-none">
                <button
                    class="tap-target bg-primary rounded-xl px-5 text-sm font-semibold text-white">{{ __('Cari') }}</button>
            </form>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($houses as $house)
                <article
                    class="border-border overflow-hidden rounded-2xl border bg-white shadow-sm transition-shadow hover:shadow-md">
                    @if ($house->cover_image_path)
                        <img src="{{ $house->cover_image_url }}" alt="{{ __('Pekarangan No.') }} {{ $house->number }}"
                            loading="lazy" class="aspect-[16/9] w-full object-cover">
                    @else
                        <div
                            class="border-border bg-surface text-primary/40 flex aspect-video items-center justify-center border-b">
                            <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m3 10 9-7 9 7v10H3V10Zm6 10v-7h6v7" />
                            </svg></div>
                    @endif
                    <div class="p-5">
                        <div class="flex items-center justify-between gap-3"><span
                                class="border-border bg-surface text-primary rounded-full border px-3 py-1 text-xs font-bold">{{ __('Pekarangan').' #'.$house->number }}</span><span
                                class="{{ $house->is_open_to_visitors ? 'text-primary' : 'text-secondary' }} text-xs font-medium">{{ $house->is_open_to_visitors ? __('Terbuka untuk Wisatawan') : __('Belum terbuka untuk kunjungan') }}</span>
                        </div>
                        <h3 class="font-display text-charcoal mt-4 text-xl font-bold">{{ $house->head_name }}</h3>
                        <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ translateValue($house->description) }}</p>
                        <p class="mt-3 text-xs text-gray-500">{{ $house->umkm_profile_count }} UMKM ·
                            {{ $house->male_count + $house->female_count }} {{ __('penghuni') }}</p>
                        <a href="{{ route('houses.show', ['number' => $house->number]) }}"
                            class="tap-target bg-primary mt-4 inline-flex items-center gap-1.5 rounded-full px-5 text-sm font-bold text-white transition active:scale-95">
                            <span>{{ __('Kunjungi Profil Pekarangan') }}</span>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </article>
            @empty
                <div class="border-border bg-surface col-span-full rounded-2xl border border-dashed p-10 text-center">
                    <h3 class="font-display text-xl font-bold">{{ __('Belum ada pekarangan yang cocok') }}</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Coba nomor lain atau kembali saat data rumah terverifikasi tersedia.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
