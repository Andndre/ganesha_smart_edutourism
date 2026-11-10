@extends('layouts.app')
@section('title', __('Objek Budaya - Penglipuran'))
@section('header_title', __('Objek Budaya'))

@section('content')
@php
    $categoryLabels = ['parahyangan' => __('Parahyangan'), 'pawongan' => __('Pawongan'), 'palemahan' => __('Palemahan')];
    $categories = array_values(array_intersect(array_keys($categoryLabels), array_column($objects, 'category')));
    $complexes = array_filter($objects, fn ($object) => ($object['children_count'] ?? 0) > 0);
@endphp
<div class="mx-auto max-w-7xl px-4 pb-14 pt-6 md:px-8 lg:px-12" x-data="{ mode: '{{ count($complexes) ? 'complexes' : 'philosophy' }}', cat: 'all' }">
    <nav class="mb-5 text-xs text-gray-500"><a href="{{ route('home') }}" class="hover:text-primary">{{ __('Beranda') }}</a> / {{ __('Warisan Budaya') }}</nav>
    <div class="rounded-3xl bg-[#173e23] p-6 text-white shadow-lg md:p-10">
        <p class="inline-flex rounded-full bg-white/15 px-3 py-1 text-[11px] font-bold uppercase tracking-widest">{{ __('Warisan Budaya Penglipuran') }}</p>
        <h1 class="mt-4 max-w-2xl font-display text-3xl font-bold md:text-5xl">{{ __('Jelajah Warisan Budaya') }}</h1>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-white/85 md:text-base">{{ __('Temukan kisah di balik setiap sudut desa') }}</p>
        <p class="mt-5 text-xs font-bold uppercase tracking-widest text-white/75">{{ count($objects) }} {{ __('objek budaya') }}</p>
    </div>

    @if(count($complexes))
        <div class="mt-7 flex gap-2 border-b border-gray-200" role="tablist" aria-label="{{ __('Tampilan budaya') }}">
            <button type="button" role="tab" @click="mode = 'complexes'" :aria-selected="mode === 'complexes'" :class="mode === 'complexes' ? 'border-primary text-primary' : 'border-transparent text-gray-600'" class="min-h-11 border-b-2 px-4 text-sm font-bold">{{ __('Kompleks Induk') }} ({{ count($complexes) }})</button>
            <button type="button" role="tab" @click="mode = 'philosophy'" :aria-selected="mode === 'philosophy'" :class="mode === 'philosophy' ? 'border-primary text-primary' : 'border-transparent text-gray-600'" class="min-h-11 border-b-2 px-4 text-sm font-bold">{{ __('Tri Hita Karana') }}</button>
        </div>
    @endif

    <div class="mb-6 mt-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><h2 class="font-display text-2xl font-bold text-charcoal">{{ __('Jelajahi ruang budaya') }}</h2><p class="mt-1 text-sm text-gray-600">{{ __('Pilih filosofi untuk melihat tempat dan kisahnya.') }}</p></div>
        @if(count($categories) > 1)
            <div x-show="mode === 'philosophy'" class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0" role="tablist" aria-label="{{ __('Kategori budaya') }}">
                <button type="button" role="tab" @click="cat = 'all'" :aria-selected="cat === 'all'" :class="cat === 'all' ? 'bg-primary text-white' : 'bg-white text-charcoal border border-gray-200'" class="tap-target shrink-0 rounded-full px-4 text-sm font-semibold">{{ __('Semua') }}</button>
                @foreach($categories as $category)
                    <button type="button" role="tab" @click="cat = '{{ $category }}'" :aria-selected="cat === '{{ $category }}'" :class="cat === '{{ $category }}' ? 'bg-primary text-white' : 'bg-white text-charcoal border border-gray-200'" class="tap-target shrink-0 rounded-full px-4 text-sm font-semibold">{{ $categoryLabels[$category] }}</button>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-5 xl:grid-cols-4">
        @forelse($objects as $object)
            <a href="{{ route('cultural-object', ['slug' => $object['slug']]) }}" x-show="mode === 'complexes' ? {{ ($object['children_count'] ?? 0) > 0 ? 'true' : 'false' }} : (cat === 'all' || cat === '{{ $object['category'] ?? '' }}')" class="group overflow-hidden rounded-2xl border border-[#ebe9e4] bg-white shadow-sm transition-shadow hover:shadow-md">
                <div class="relative aspect-[4/3] bg-[#eaf3ec]">
                    @if(!empty($object['historical_images'][0]))
                        <img src="{{ asset('storage/'.$object['historical_images'][0]) }}" alt="{{ $object['name'] }}" loading="lazy" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-primary/50"><svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M3 10h18M5 10V5l7-3 7 3v5M6 10v10m4-10v10m4-10v10m4-10v10M3 21h18"/></svg></div>
                    @endif
                    @if(!empty($object['ar_marker_id']) || !empty($object['model_3d_path']))<span class="absolute right-2 top-2 rounded-full bg-white px-2 py-1 text-[10px] font-bold text-primary shadow-sm">{{ __('AR Tersedia') }}</span>@endif
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-amber-800">{{ ($object['children_count'] ?? 0) > 0 ? __('Kompleks Induk') : ($categoryLabels[$object['category']] ?? __('Warisan Budaya')) }}</p>
                    <h3 class="mt-1 line-clamp-2 font-display text-base font-bold text-charcoal md:text-lg">{{ $object['name'] }}</h3>
                    <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-gray-600 md:text-sm">{{ strip_tags($object['short_description'] ?? '') }}</p>
                    @if(($object['children_count'] ?? 0) > 0)<p class="mt-2 text-xs font-semibold text-gray-500">{{ $object['children_count'] }} {{ __('ruang budaya') }}</p>@endif
                    <span class="mt-4 inline-block text-xs font-bold text-primary">{{ __('Baca kisahnya') }} →</span>
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-600">{{ __('Belum ada objek budaya yang terdaftar.') }}</div>
        @endforelse
    </div>
</div>
@endsection
