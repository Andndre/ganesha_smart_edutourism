@extends('layouts.admin')

@section('title', 'Objek Budaya')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Objek Budaya</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola data cagar budaya dan situs warisan Desa Penglipuran.</p>
    </div>
    <button class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Objek
    </button>
</div>

{{-- Search + Filter --}}
<div class="mb-4 flex flex-col gap-3 sm:flex-row">
    <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" placeholder="Cari objek budaya..." class="w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
    </div>
    <select class="rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-primary focus:outline-none">
        <option>Semua Kategori</option>
        <option>Pura</option>
        <option>Bale Adat</option>
        <option>Monumen</option>
        <option>Alam</option>
    </select>
</div>

{{-- Table --}}
<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nama Objek</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Kategori</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Lokasi</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php
                    $objects = [
                        ['name' => 'Pura Penataran Agung',    'cat' => 'Pura',      'loc' => 'Pusat Desa',       'active' => true],
                        ['name' => 'Bale Banjar Penglipuran', 'cat' => 'Bale Adat', 'loc' => 'Zona Utama',       'active' => true],
                        ['name' => 'Gerbang Candi Bentar',    'cat' => 'Monumen',   'loc' => 'Pintu Masuk',      'active' => true],
                        ['name' => 'Kebun Bambu Suci',        'cat' => 'Alam',      'loc' => 'Zona Selatan',     'active' => true],
                        ['name' => 'Pura Dalem',              'cat' => 'Pura',      'loc' => 'Zona Utara',       'active' => false],
                        ['name' => 'Rumah Adat Tradisional',  'cat' => 'Bale Adat', 'loc' => 'Sepanjang Jalan',  'active' => true],
                    ];
                @endphp
                @foreach ($objects as $obj)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4 font-medium text-charcoal">{{ $obj['name'] }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-lg bg-primary/8 px-2.5 py-1 text-xs font-semibold text-primary">{{ $obj['cat'] }}</span>
                        </td>
                        <td class="px-5 py-4 text-gray-500">{{ $obj['loc'] }}</td>
                        <td class="px-5 py-4">
                            @if ($obj['active'])
                                <span class="flex w-fit items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                                    <span class="h-1.5 w-1.5 rounded-full bg-primary"></span> Aktif
                                </span>
                            @else
                                <span class="flex w-fit items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <button class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-primary/10 hover:text-primary" title="Edit">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-warning/10 hover:text-warning" title="Hapus">
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
        <span>Menampilkan 6 dari 6 data</span>
        <span>Halaman 1 dari 1</span>
    </div>
</div>

@endsection
