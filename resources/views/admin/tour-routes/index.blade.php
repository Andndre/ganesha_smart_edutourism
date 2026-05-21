@extends('layouts.admin')

@section('title', 'Rute Wisata')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Rute Wisata</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola jalur dan titik kunjungan yang direkomendasikan kepada wisatawan.</p>
    </div>
    <button class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Rute
    </button>
</div>

{{-- Route Cards --}}
<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    @php
        $routes = [
            [
                'name'     => 'Rute Edukasi Budaya',
                'type'     => 'Edukasi',
                'duration' => '2-3 jam',
                'distance' => '1.8 km',
                'stops'    => ['Gerbang Candi Bentar', 'Bale Banjar', 'Pura Penataran Agung', 'Rumah Adat (Demo)'],
                'active'   => true,
                'color'    => 'primary',
            ],
            [
                'name'     => 'Rute Alam & Bambu',
                'type'     => 'Alam',
                'duration' => '1.5-2 jam',
                'distance' => '2.4 km',
                'stops'    => ['Kebun Bambu Suci', 'Sumber Mata Air', 'Area Piknik', 'Viewpoint Bukit'],
                'active'   => true,
                'color'    => 'primary',
            ],
            [
                'name'     => 'Rute UMKM & Kuliner',
                'type'     => 'Belanja',
                'duration' => '1-2 jam',
                'distance' => '0.9 km',
                'stops'    => ['Pasar Tradisional', 'Toko Kerajinan Wayan', 'Warung Loloh Cemcem', 'Toko Madu Trigona'],
                'active'   => true,
                'color'    => 'secondary',
            ],
            [
                'name'     => 'Rute Aksesibilitas',
                'type'     => 'Difabel',
                'duration' => '1-1.5 jam',
                'distance' => '0.7 km',
                'stops'    => ['Area Parkir Utama', 'Zona Utama (Jalur Rata)', 'Bale Banjar', 'Area Istirahat'],
                'active'   => false,
                'color'    => 'primary',
            ],
        ];
    @endphp

    @foreach ($routes as $route)
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            {{-- Header --}}
            <div class="mb-4 flex items-start justify-between gap-2">
                <div>
                    <h3 class="font-semibold text-charcoal">{{ $route['name'] }}</h3>
                    <span class="mt-1 inline-block rounded-lg bg-primary/8 px-2.5 py-0.5 text-xs font-semibold text-primary">
                        {{ $route['type'] }}
                    </span>
                </div>
                @if ($route['active'])
                    <span class="flex shrink-0 items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold text-primary">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary"></span> Aktif
                    </span>
                @else
                    <span class="flex shrink-0 items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Nonaktif
                    </span>
                @endif
            </div>

            {{-- Meta --}}
            <div class="mb-4 flex gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $route['duration'] }}
                </span>
                <span class="flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    {{ $route['distance'] }}
                </span>
            </div>

            {{-- Waypoints --}}
            <div class="relative mb-4 pl-4">
                <div class="absolute left-1.5 top-2 bottom-2 w-px bg-gray-200"></div>
                @foreach ($route['stops'] as $i => $stop)
                    <div class="relative mb-2 flex items-center gap-2">
                        <span class="absolute -left-3 flex h-3 w-3 items-center justify-center rounded-full
                            {{ $i === 0 || $i === count($route['stops']) - 1
                                ? 'bg-primary'
                                : 'border-2 border-gray-300 bg-white' }}">
                        </span>
                        <p class="pl-2 text-sm {{ $i === 0 || $i === count($route['stops']) - 1 ? 'font-semibold text-charcoal' : 'text-gray-500' }}">
                            {{ $stop }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- Actions --}}
            <div class="flex gap-2 border-t border-gray-50 pt-4">
                <button class="flex-1 rounded-xl border border-gray-200 py-2 text-xs font-semibold text-gray-600 transition-colors hover:bg-gray-50">
                    Edit Rute
                </button>
                <button class="flex-1 rounded-xl border border-gray-200 py-2 text-xs font-semibold text-gray-600 transition-colors hover:bg-gray-50">
                    {{ $route['active'] ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
                <button class="rounded-xl border border-warning/20 px-3 py-2 text-xs font-semibold text-warning transition-colors hover:bg-warning/5">
                    Hapus
                </button>
            </div>
        </div>
    @endforeach
</div>

@endsection
