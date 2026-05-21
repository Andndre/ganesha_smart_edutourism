@extends('layouts.admin')

@section('title', 'Paket Wisata')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Paket Wisata</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola paket wisata dan harga yang ditawarkan kepada pengunjung.</p>
    </div>
    <a href="{{ route('admin.packages.create') }}"
        class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Paket
    </a>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @php
        $packages = [
            ['name' => 'Paket Keluarga 1 Hari',   'price' => 'Rp 85.000',  'desc' => 'Tiket masuk, pemandu wisata, dan makan siang tradisional.', 'duration' => '8 jam', 'sold' => 124, 'active' => true],
            ['name' => 'Paket Edukasi Budaya',     'price' => 'Rp 120.000', 'desc' => 'Workshop tari, membatik, dan belajar memasak makanan Bali.', 'duration' => '6 jam', 'sold' => 89,  'active' => true],
            ['name' => 'Paket Sunrise Trek',       'price' => 'Rp 75.000',  'desc' => 'Jelajah kebun bambu dan zona alam sejak dini hari.',         'duration' => '4 jam', 'sold' => 57,  'active' => true],
            ['name' => 'Paket Foto Prewedding',    'price' => 'Rp 500.000', 'desc' => 'Sesi foto eksklusif di lokasi-lokasi ikonik desa.',           'duration' => '3 jam', 'sold' => 18,  'active' => false],
        ];
    @endphp
    @foreach ($packages as $pkg)
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md">
            <div class="mb-3 flex items-start justify-between">
                <h3 class="font-semibold text-charcoal">{{ $pkg['name'] }}</h3>
                @if ($pkg['active'])
                    <span class="shrink-0 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold text-primary">Aktif</span>
                @else
                    <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-400">Nonaktif</span>
                @endif
            </div>
            <p class="text-sm text-gray-500">{{ $pkg['desc'] }}</p>
            <div class="mt-4 flex items-center justify-between border-t border-gray-50 pt-3">
                <div>
                    <p class="text-xl font-bold text-primary">{{ $pkg['price'] }}</p>
                    <p class="text-xs text-gray-400">per orang · {{ $pkg['duration'] }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-charcoal">{{ $pkg['sold'] }}</p>
                    <p class="text-xs text-gray-400">terjual</p>
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button class="flex-1 rounded-xl border border-gray-200 py-2 text-xs font-semibold text-gray-600 transition-colors hover:bg-gray-50">Edit</button>
                <button class="flex-1 rounded-xl border border-warning/30 py-2 text-xs font-semibold text-warning transition-colors hover:bg-warning/5">Hapus</button>
            </div>
        </div>
    @endforeach
</div>

@endsection
