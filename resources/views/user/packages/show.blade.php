@extends('layouts.app')
@section('title', 'Detail Paket - Penglipuran')
@section('header_title', 'Reservasi Paket')

@section('content')
    <div class="relative pb-32">
        <!-- Image Header -->
        <div class="relative aspect-video w-full bg-gray-200">
            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <!-- Back Button Overlay (if not using header, but we are using header) -->
        </div>

        <!-- Package Details -->
        <div class="relative z-10 -mt-6 rounded-t-3xl border-b border-gray-100 bg-white px-5 py-6 shadow-sm">
            <div class="mb-2">
                <span
                    class="text-primary mb-3 inline-block rounded-lg border border-green-100 bg-green-50 px-2.5 py-1 text-xs font-bold">Paket
                    Wisata</span>
                <h1 class="text-charcoal text-xl font-bold">{{ $package->name }}</h1>
            </div>

            <p class="mb-4 text-sm leading-relaxed text-gray-500">
                {{ $package->description }}
            </p>

            <div class="mb-2 grid grid-cols-2 gap-3">
                <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-100 bg-gray-50 p-3">
                    <svg class="text-accent mb-1 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs font-medium text-gray-500">Durasi</span>
                    <span class="text-charcoal text-sm font-bold">{{ $package->duration_hours }} Jam</span>
                </div>
                <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-100 bg-gray-50 p-3">
                    <svg class="text-accent mb-1 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs font-medium text-gray-500">Fasilitas</span>
                    <span class="text-charcoal text-sm font-bold truncate w-full text-center">{{ implode(', ', $package->inclusions ?? []) }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Sticky Bottom CTA -->
    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-100 bg-white p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
        <div class="mb-3 flex items-center justify-between px-1">
            <span class="text-sm font-medium text-gray-500">Mulai dari</span>
            <span class="text-primary text-lg font-bold">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
        </div>
        
        <a href="{{ route('tour-package.book', $package->id) }}"
            class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center gap-2 rounded-2xl font-bold text-white shadow-lg transition-all active:scale-[0.98]">
            Pesan Tiket Sekarang
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </a>
        @guest
            <p class="text-xs text-center text-gray-400 mt-2">Anda akan diminta untuk login terlebih dahulu.</p>
        @endguest
    </div>
@endsection