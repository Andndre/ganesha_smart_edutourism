@extends('layouts.app')
@section('title', __('Tiket & Paket Wisata - Penglipuran'))
@section('header_title', __('Tiket & Paket Wisata'))

@section('content')
    <div class="mx-auto w-full max-w-6xl px-4 py-6 md:px-6 md:py-8 lg:px-8"
        x-data="{ tab: '{{ request('tab') === 'package' || count($tickets) === 0 ? 'package' : 'ticket' }}' }">
        <div class="mb-6 md:mb-8">
            <h2 class="text-charcoal text-xl font-bold md:text-2xl lg:text-3xl">{{ __('Eksplorasi Bersama') }}</h2>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 md:text-base">
                {{ __('Pilih tiket masuk untuk berkeliling mandiri, atau paket wisata untuk pengalaman lengkap.') }}</p>
        </div>

        <!-- Ticket vs Package switcher -->
        <div class="mb-6 grid grid-cols-2 gap-2 rounded-2xl border border-gray-100 bg-white p-1.5 shadow-sm md:max-w-md">
            <button type="button" @click="tab = 'ticket'"
                :class="tab === 'ticket' ? 'bg-primary text-white shadow' : 'text-gray-500 hover:bg-gray-50'"
                class="tap-target flex h-11 items-center justify-center gap-2 rounded-xl text-sm font-bold transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                {{ __('Tiket Masuk') }}
            </button>
            <button type="button" @click="tab = 'package'"
                :class="tab === 'package' ? 'bg-primary text-white shadow' : 'text-gray-500 hover:bg-gray-50'"
                class="tap-target flex h-11 items-center justify-center gap-2 rounded-xl text-sm font-bold transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                {{ __('Paket Wisata') }}
            </button>
        </div>

        <!-- Entrance tickets -->
        <div x-show="tab === 'ticket'" x-cloak class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-5 xl:grid-cols-3">
            @forelse($tickets as $item)
                @include('user.packages.partials.product-card', ['item' => $item, 'badge' => __('Tiket Masuk')])
            @empty
                <div
                    class="col-span-full w-full rounded-2xl border border-gray-100 bg-white p-4 py-12 text-center text-sm text-gray-500">
                    {{ __('Belum ada tiket masuk yang tersedia.') }}
                </div>
            @endforelse
        </div>

        <!-- Tour packages -->
        <div x-show="tab === 'package'" x-cloak class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-5 xl:grid-cols-3">
            @forelse($tourPackages as $item)
                @include('user.packages.partials.product-card', ['item' => $item, 'badge' => __('Paket Wisata')])
            @empty
                <div
                    class="col-span-full w-full rounded-2xl border border-gray-100 bg-white p-4 py-12 text-center text-sm text-gray-500">
                    {{ __('Belum ada paket wisata yang tersedia.') }}
                </div>
            @endforelse
        </div>
    </div>
@endsection
