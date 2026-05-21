@extends('layouts.admin')

@section('title', 'Pemesanan')

@section('content')

<div class="mb-6">
    <h1 class="font-display text-2xl font-bold text-charcoal">Kelola Pemesanan</h1>
    <p class="mt-0.5 text-sm text-gray-500">Semua transaksi tiket masuk dan paket wisata.</p>
</div>

{{-- Filter Tabs --}}
<div class="mb-4 flex gap-2 overflow-x-auto pb-1">
    @php $tabs = ['Semua', 'Aktif', 'Selesai', 'Dibatalkan']; @endphp
    @foreach ($tabs as $i => $tab)
        <button class="shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition-all
            {{ $i === 0 ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50' }}">
            {{ $tab }}
        </button>
    @endforeach
</div>

<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">ID Booking</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Wisatawan</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Paket</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Tgl. Kunjungan</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Total</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php
                    $bookings = [
                        ['id' => '#BK-0041', 'name' => 'Sari Dewi',     'pkg' => 'Paket Keluarga 1 Hari', 'date' => '21 Mei 2026', 'total' => 'Rp 340.000', 'status' => 'Aktif'],
                        ['id' => '#BK-0040', 'name' => 'Budi Santoso',  'pkg' => 'Paket Edukasi Budaya',  'date' => '21 Mei 2026', 'total' => 'Rp 120.000', 'status' => 'Selesai'],
                        ['id' => '#BK-0039', 'name' => 'Maria Tan',     'pkg' => 'Paket Sunrise Trek',    'date' => '21 Mei 2026', 'total' => 'Rp 150.000', 'status' => 'Aktif'],
                        ['id' => '#BK-0038', 'name' => 'Reza Pratama',  'pkg' => 'Paket Keluarga 1 Hari', 'date' => '20 Mei 2026', 'total' => 'Rp 255.000', 'status' => 'Dibatalkan'],
                        ['id' => '#BK-0037', 'name' => 'Lisa Cahyani',  'pkg' => 'Paket Edukasi Budaya',  'date' => '19 Mei 2026', 'total' => 'Rp 240.000', 'status' => 'Selesai'],
                        ['id' => '#BK-0036', 'name' => 'Agus Wijaya',   'pkg' => 'Paket Keluarga 1 Hari', 'date' => '18 Mei 2026', 'total' => 'Rp 425.000', 'status' => 'Selesai'],
                    ];
                    $badge = [
                        'Aktif'      => 'bg-primary/10 text-primary',
                        'Selesai'    => 'bg-gray-100 text-gray-400',
                        'Dibatalkan' => 'bg-warning/10 text-warning',
                    ];
                @endphp
                @foreach ($bookings as $b)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4 font-mono text-xs text-gray-400">{{ $b['id'] }}</td>
                        <td class="px-5 py-4 font-medium text-charcoal">{{ $b['name'] }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $b['pkg'] }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $b['date'] }}</td>
                        <td class="px-5 py-4 font-semibold text-charcoal">{{ $b['total'] }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge[$b['status']] }}">{{ $b['status'] }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <button class="rounded-lg px-3 py-1.5 text-xs font-semibold text-primary border border-primary/20 hover:bg-primary/5 transition-colors">
                                Detail
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3.5 text-xs text-gray-400">
        <span>Menampilkan 6 dari 41 data</span>
        <div class="flex items-center gap-1">
            <button class="rounded-lg px-2.5 py-1 hover:bg-gray-100">← Prev</button>
            <span class="rounded-lg bg-primary px-2.5 py-1 text-white">1</span>
            <button class="rounded-lg px-2.5 py-1 hover:bg-gray-100">2</button>
            <button class="rounded-lg px-2.5 py-1 hover:bg-gray-100">3</button>
            <button class="rounded-lg px-2.5 py-1 hover:bg-gray-100">Next →</button>
        </div>
    </div>
</div>

@endsection
