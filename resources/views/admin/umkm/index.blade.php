@extends('layouts.admin')

@section('title', 'UMKM')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">UMKM Desa</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola produk dan toko UMKM lokal Desa Penglipuran.</p>
    </div>
    <button class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Produk
    </button>
</div>

{{-- Summary Stats --}}
<div class="mb-6 grid grid-cols-3 gap-4">
    @php
        $umkmStats = [
            ['label' => 'Total UMKM',    'value' => '24',   'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5'],
            ['label' => 'Total Produk',  'value' => '137',  'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
            ['label' => 'Terjual Bulan Ini', 'value' => '89', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ];
    @endphp
    @foreach ($umkmStats as $s)
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                    <svg class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-charcoal">{{ $s['value'] }}</p>
                    <p class="text-xs text-gray-500">{{ $s['label'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Search --}}
<div class="mb-4 flex flex-col gap-3 sm:flex-row">
    <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" placeholder="Cari produk atau toko UMKM..." class="w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
    </div>
    <select class="rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-primary focus:outline-none">
        <option>Semua Kategori</option>
        <option>Kerajinan</option>
        <option>Kuliner</option>
        <option>Tekstil</option>
        <option>Minuman</option>
    </select>
</div>

{{-- Product Table --}}
<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Produk</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Toko</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Kategori</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Harga</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Stok</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php
                    $products = [
                        ['name' => 'Loloh Cemcem Tradisional',   'shop' => 'Bu Kadek',         'cat' => 'Minuman',   'price' => 'Rp 15.000',  'stock' => 48,  'low' => false],
                        ['name' => 'Kain Endek Penglipuran',     'shop' => 'Tenun Bali Sari',  'cat' => 'Tekstil',   'price' => 'Rp 250.000', 'stock' => 12,  'low' => true],
                        ['name' => 'Miniatur Gapura Bali',       'shop' => 'Kerajinan Wayan',  'cat' => 'Kerajinan', 'price' => 'Rp 85.000',  'stock' => 30,  'low' => false],
                        ['name' => 'Jaja Laklak',                'shop' => 'Dapur Bu Made',    'cat' => 'Kuliner',   'price' => 'Rp 20.000',  'stock' => 5,   'low' => true],
                        ['name' => 'Patung Barong Kayu',         'shop' => 'Kerajinan Wayan',  'cat' => 'Kerajinan', 'price' => 'Rp 450.000', 'stock' => 8,   'low' => false],
                        ['name' => 'Madu Trigona Asli',          'shop' => 'Alam Penglipuran', 'cat' => 'Kuliner',   'price' => 'Rp 120.000', 'stock' => 22,  'low' => false],
                    ];
                @endphp
                @foreach ($products as $p)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4 font-medium text-charcoal">{{ $p['name'] }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $p['shop'] }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-lg bg-secondary/10 px-2.5 py-1 text-xs font-semibold text-secondary-700">{{ $p['cat'] }}</span>
                        </td>
                        <td class="px-5 py-4 font-semibold text-charcoal">{{ $p['price'] }}</td>
                        <td class="px-5 py-4">
                            @if ($p['low'])
                                <span class="rounded-full bg-warning/10 px-2.5 py-0.5 text-xs font-bold text-warning">{{ $p['stock'] }} — Stok Rendah</span>
                            @else
                                <span class="text-gray-600">{{ $p['stock'] }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <button class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-primary/10 hover:text-primary">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-warning/10 hover:text-warning">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3.5 text-xs text-gray-400">
        <span>Menampilkan 6 dari 137 data</span>
        <div class="flex items-center gap-1">
            <button class="rounded-lg px-2.5 py-1 hover:bg-gray-100">← Prev</button>
            <span class="rounded-lg bg-primary px-2.5 py-1 text-white">1</span>
            <button class="rounded-lg px-2.5 py-1 hover:bg-gray-100">2</button>
            <button class="rounded-lg px-2.5 py-1 hover:bg-gray-100">Next →</button>
        </div>
    </div>
</div>

@endsection
