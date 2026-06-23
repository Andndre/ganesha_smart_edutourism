@extends('layouts.app')
@section('title', __('Paket Wisata - Penglipuran'))
@section('header_title', __('Paket Wisata'))

@section('content')
    <div class="mx-auto w-full max-w-6xl px-4 py-6 md:px-6 md:py-8 lg:px-8">
        <div class="mb-6 md:mb-8">
            <h2 class="text-charcoal text-xl font-bold md:text-2xl lg:text-3xl">{{ __('Eksplorasi Bersama') }}</h2>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 md:text-base">{{ __('Pilih paket wisata yang sesuai dengan durasi dan preferensi perjalanan Anda.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-5 xl:grid-cols-3">
            @forelse($packages as $package)
                <a href="{{ route('tour-package', $package['id']) }}"
                    class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all hover:-translate-y-1 hover:border-gray-200 hover:shadow-lg active:scale-[0.98]">
                    <div class="relative aspect-video overflow-hidden bg-gray-200">
                        @if ($package['images'] && count($package['images']) > 0)
                            <img src="{{ Storage::url($package['images'][0]) }}" alt="{{ $package['name'] }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                        <div
                            class="text-primary absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1.5 text-xs font-bold shadow-sm backdrop-blur">
                            {{ __('Paket Wisata') }}
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="text-charcoal mb-1 text-lg font-bold">{{ $package['name'] }}</h3>
                        <p class="mb-3 line-clamp-2 text-sm text-gray-500">{{ $package['description'] }}</p>

                        <div class="mt-auto flex flex-wrap items-center justify-between gap-2 border-t border-gray-50 pt-4">
                            <div class="flex items-center gap-4 text-xs font-semibold text-gray-500">
                                <div class="flex items-center gap-1.5">
                                    <svg class="text-primary h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $package['duration_hours'] }} {{ __('Jam') }}
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="text-primary h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ __('Min. :count Orang', ['count' => $package['min_capacity']]) }}
                                </div>
                            </div>
                            <div class="text-primary text-lg font-bold">Rp {{ number_format($package['price'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div
                    class="col-span-full w-full rounded-2xl border border-gray-100 bg-white p-4 py-12 text-center text-sm text-gray-500">
                    {{ __('Belum ada paket wisata yang tersedia.') }}
                </div>
            @endforelse
        </div>
    </div>
@endsection
