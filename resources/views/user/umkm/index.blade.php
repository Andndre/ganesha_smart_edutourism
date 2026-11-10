@extends('layouts.app')
@section('title', __('Katalog UMKM - Penglipuran'))
@section('header_title', __('Katalog UMKM'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 pb-40 pt-6 md:px-8 lg:px-12"
         x-data="{ tab: '{{ $activeTab ?? 'smart-route' }}' }"
         x-on:switch-umkm-tab.window="tab = $event.detail">
        {{-- Alerts OUTSIDE the form --}}
        @include('user.umkm.partials.index._alerts')

        {{-- Tab Navigation --}}
        <div class="mb-6 flex gap-4 border-b border-gray-100">
            <button @click="tab = 'smart-route'"
                :class="{ 'text-primary border-b-2 border-primary': tab === 'smart-route', 'text-gray-500': tab !== 'smart-route' }"
                class="pb-2 text-sm font-bold transition-all">
                {{ __('Smart Route') }}
            </button>
            <button @click="tab = 'direktori'"
                :class="{ 'text-primary border-b-2 border-primary': tab === 'direktori', 'text-gray-500': tab !== 'direktori' }"
                class="pb-2 text-sm font-bold transition-all">
                {{ __('Direktori UMKM') }} ({{ $umkmList->total() }})
            </button>
        </div>

        {{-- Smart Route Tab --}}
        <div x-show="tab === 'smart-route'">
            <div class="mb-6">
                <h2 class="font-display text-charcoal text-2xl font-bold md:text-3xl">{{ __('Jelajah UMKM') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Pilih satu atau lebih kategori yang Anda inginkan. Sistem kami akan membantu mencarikan lokasi UMKM yang memiliki produk tersebut.') }}</p>
            </div>

            <button type="button" @click="$dispatch('open-fair-rotation')" class="mb-6 flex min-h-14 w-full items-center justify-between rounded-2xl border border-[#d9e6da] bg-[#eaf3ec] px-4 text-left text-sm font-bold text-[#173e23] hover:bg-[#e0eee2]"><span>{{ __('Bagaimana Smart Route membagi rekomendasi secara adil?') }}</span><svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></button>

            <form action="{{ route('umkm.recommend') }}" method="POST">
                @csrf
                <input type="hidden" name="lat" id="recommend-lat">
                <input type="hidden" name="lng" id="recommend-lng">
                @include('user.umkm.partials.index._category_search')
                @include('user.umkm.partials.index._category_grid')

                @include('user.umkm.partials.index._sticky_bar')
            </form>
        </div>

        {{-- Direktori UMKM Tab --}}
        <div id="umkm-directory-panel" x-show="tab === 'direktori'" x-cloak>
            <div class="mb-6">
                <h2 class="text-charcoal text-xl font-bold">{{ __('Direktori UMKM') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Jelajahi semua UMKM di Desa Penglipuran') }}</p>
            </div>
            @include('user.umkm.partials.index._directory_content')
        </div>
    </div>

    {{-- Modals and scripts outside the x-data div --}}
    @include('user.umkm.partials.index._multi_stop_modal')
    @include('user.umkm.partials.index._fair_rotation_modal')
    @include('user.umkm.partials.index._category_detail_modal')
    @include('user.umkm.partials.index._scripts')
    @include('user.umkm.partials.index._directory_pagination_script')
@endsection
