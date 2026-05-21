@extends('layouts.admin')

@section('title', 'Event & Kalender')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Event & Kalender Budaya</h1>
        <p class="mt-0.5 text-sm text-gray-500">Jadwalkan dan kelola upacara adat, festival, dan event desa.</p>
    </div>
    <a href="{{ route('admin.events.create') }}"
        class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Event
    </a>
</div>

{{-- Upcoming events (timeline) --}}
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    @php
        $upcoming = [
            ['label' => 'Mendatang',    'count' => 5, 'color' => 'text-primary bg-primary/10'],
            ['label' => 'Bulan Ini',    'count' => 8, 'color' => 'text-secondary bg-secondary/10'],
            ['label' => 'Sudah Lewat',  'count' => 23, 'color' => 'text-gray-400 bg-gray-100'],
        ];
    @endphp
    @foreach ($upcoming as $u)
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500">{{ $u['label'] }}</p>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $u['color'] }}">{{ $u['count'] }}</span>
            </div>
        </div>
    @endforeach
</div>

<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nama Event</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Tanggal</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Lokasi</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php
                    $events = [
                        ['name' => 'Hari Raya Galungan',         'date' => '4 Jun 2026',   'loc' => 'Seluruh Desa',    'status' => 'Mendatang'],
                        ['name' => 'Festival Bambu Penglipuran', 'date' => '15 Jun 2026',  'loc' => 'Kebun Bambu',     'status' => 'Mendatang'],
                        ['name' => 'Upacara Melasti',            'date' => '1 Jun 2026',   'loc' => 'Pura Dalem',      'status' => 'Mendatang'],
                        ['name' => 'Pameran Kerajinan UMKM',     'date' => '20 Mei 2026',  'loc' => 'Balai Desa',      'status' => 'Selesai'],
                        ['name' => 'Workshop Tari Pendet',       'date' => '10 Mei 2026',  'loc' => 'Bale Banjar',     'status' => 'Selesai'],
                    ];
                    $statusClass = [
                        'Mendatang' => 'bg-primary/10 text-primary',
                        'Selesai'   => 'bg-gray-100 text-gray-400',
                    ];
                @endphp
                @foreach ($events as $e)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4 font-medium text-charcoal">{{ $e['name'] }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $e['date'] }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $e['loc'] }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass[$e['status']] }}">{{ $e['status'] }}</span>
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
</div>

@endsection
