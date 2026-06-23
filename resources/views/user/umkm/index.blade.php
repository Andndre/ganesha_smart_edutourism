@extends('layouts.app')
@section('title', __('Katalog UMKM - Penglipuran'))
@section('header_title', __('Katalog UMKM'))

@section('content')
    <div class="px-4 pb-40 pt-[calc(env(safe-area-inset-top)+6rem)]" x-data="{ tab: 'smart-route' }">
        {{-- Alerts OUTSIDE the form --}}
        @include('user.umkm.partials.index._alerts')

        {{-- Omni-Search Bar (above tabs, shared) --}}
        @include('user.umkm.partials.index._hero_search')

        {{-- Tab Navigation --}}
        <div class="mb-6 flex gap-4 border-b border-gray-100">
            <button @click="tab = 'smart-route'"
                :class="{ 'text-primary border-b-2 border-primary': tab === 'smart-route', 'text-gray-400': tab !== 'smart-route' }"
                class="pb-2 text-sm font-bold transition-all">
                {{ __('Smart Route') }}
            </button>
            <button @click="tab = 'direktori'"
                :class="{ 'text-primary border-b-2 border-primary': tab === 'direktori', 'text-gray-400': tab !== 'direktori' }"
                class="pb-2 text-sm font-bold transition-all">
                {{ __('Direktori UMKM') }}
            </button>
        </div>

        {{-- Smart Route Tab --}}
        <div x-show="tab === 'smart-route'">
            <div class="mb-6">
                <h2 class="text-charcoal text-xl font-bold">{{ __('Jelajah UMKM') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Pilih satu atau lebih kategori yang Anda inginkan. Sistem kami akan membantu mencarikan lokasi UMKM yang memiliki produk tersebut.') }}</p>
            </div>

            <form action="{{ route('umkm.recommend') }}" method="POST">
                @csrf
                @include('user.umkm.partials.index._category_grid')

                @php
                    $hasActiveSession = false;
                    if (auth()->check() || session()->has('guest_token')) {
                        $hasActiveSession = \App\Models\RouteSession::where('status', 'active')
                            ->where(function ($q) {
                                $q->where('user_id', auth()->id())->orWhere('guest_token', session('guest_token'));
                            })
                            ->exists();
                    }
                @endphp
                @include('user.umkm.partials.index._sticky_bar')
            </form>
        </div>

        {{-- Direktori UMKM Tab --}}
        <div x-show="tab === 'direktori'" x-cloak>
            <div class="mb-6">
                <h2 class="text-charcoal text-xl font-bold">{{ __('Direktori UMKM') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Jelajahi semua UMKM di Desa Penglipuran') }}</p>
            </div>
            @include('user.umkm.partials.index._umkm_grid')
        </div>
    </div>

    {{-- Modals and scripts outside the x-data div --}}
    @include('user.umkm.partials.index._multi_stop_modal')
    @include('user.umkm.partials.index._category_detail_modal')
    @include('user.umkm.partials.index._scripts')
@endsection
