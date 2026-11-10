@extends('layouts.app')

@section('title', __('Rumah Adat Penglipuran'))
@section('header_title', __('Rumah Adat'))

@section('content')
<div class="mx-auto max-w-7xl px-4 pb-12 pt-6 md:px-8 lg:px-12">
    <nav class="mb-5 text-xs text-gray-500"><a href="{{ route('home') }}" class="hover:text-primary">{{ __('Beranda') }}</a> / {{ __('Rumah Adat') }}</nav>
    <div class="mb-8 rounded-3xl bg-[#173e23] p-6 text-white shadow-lg md:p-10">
        <p class="mb-3 inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-widest">{{ __('Pekarangan Tradisional Penglipuran') }}</p>
        <h1 class="max-w-2xl font-display text-3xl font-bold leading-tight md:text-5xl">{{ __('Jelajahi rumah adat warga') }}</h1>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-white/85 md:text-base">{{ __('Kenali keluarga, ruang budaya, dan UMKM di setiap pekarangan yang terbuka untuk wisatawan.') }}</p>
        <p class="mt-5 text-xs font-semibold uppercase tracking-widest text-white/80">{{ $houseCount }} {{ __('pekarangan terverifikasi') }}</p>
    </div>

    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div><h2 class="font-display text-2xl font-bold text-charcoal">{{ __('Direktori Rumah Adat') }}</h2><p class="mt-1 text-sm text-gray-600">{{ __('Cari nomor pekarangan yang sedang Anda lewati.') }}</p></div>
        <form action="{{ route('houses.index') }}" method="GET" class="flex w-full max-w-md gap-2">
            <label for="house-number-search" class="sr-only">{{ __('Nomor rumah') }}</label>
            <input id="house-number-search" name="q" inputmode="numeric" value="{{ $search }}" placeholder="{{ __('Contoh: 18') }}" class="min-h-11 min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-4 text-sm focus:border-primary focus:outline-none">
            <button class="tap-target rounded-xl bg-primary px-5 text-sm font-semibold text-white">{{ __('Cari') }}</button>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($houses as $house)
            <article class="overflow-hidden rounded-2xl border border-[#ebe9e4] bg-white shadow-sm transition-shadow hover:shadow-md">
                @if($house->cover_image_path)
                    <img src="{{ asset('storage/'.$house->cover_image_path) }}" alt="{{ __('Pekarangan No.') }} {{ $house->number }}" loading="lazy" class="aspect-[16/9] w-full object-cover">
                @else
                    <div class="flex aspect-[16/9] items-center justify-center bg-[#eef3ed] text-primary"><svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10H3V10Zm6 10v-7h6v7"/></svg></div>
                @endif
                <div class="p-5">
                    <div class="flex items-center justify-between gap-3"><span class="rounded-full bg-[#eaf3ec] px-3 py-1 text-xs font-bold text-primary">{{ __('Pekarangan') }} #{{ $house->number }}</span><span class="text-xs font-medium {{ $house->is_open_to_visitors ? 'text-primary' : 'text-amber-700' }}">{{ $house->is_open_to_visitors ? __('Terbuka untuk Wisatawan') : __('Belum terbuka untuk kunjungan') }}</span></div>
                    <h3 class="mt-4 font-display text-xl font-bold text-charcoal">{{ $house->head_name }}</h3>
                    <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ translateValue($house->description) }}</p>
                    <p class="mt-3 text-xs text-gray-500">{{ $house->umkm_profiles_count }} UMKM · {{ $house->male_count + $house->female_count }} {{ __('penghuni') }}</p>
                    <a href="{{ route('houses.show', ['number' => $house->number]) }}" class="tap-target mt-4 inline-flex rounded-full bg-primary px-5 text-sm font-bold text-white">{{ __('Kunjungi Profil Pekarangan') }} →</a>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center"><h3 class="font-display text-xl font-bold">{{ __('Belum ada pekarangan yang cocok') }}</h3><p class="mt-2 text-sm text-gray-600">{{ __('Coba nomor lain atau kembali saat data rumah terverifikasi tersedia.') }}</p></div>
        @endforelse
    </div>
    <div class="mt-6">{{ $houses->links() }}</div>
</div>
@endsection
