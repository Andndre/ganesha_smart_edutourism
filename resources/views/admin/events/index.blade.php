@extends('layouts.dashboard')

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
            ['label' => 'Mendatang',    'count' => $upcomingCount, 'color' => 'text-primary bg-primary/10'],
            ['label' => 'Bulan Ini',    'count' => $thisMonthCount, 'color' => 'text-secondary bg-secondary/10'],
            ['label' => 'Sudah Lewat',  'count' => $pastCount, 'color' => 'text-gray-400 bg-gray-100'],
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

{{-- Search + Filter --}}
<form method="GET" action="{{ route('admin.events') }}" class="mb-4 flex flex-col gap-3 sm:flex-row">
    <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari event..." class="w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
    </div>
    <select name="category" onchange="this.form.submit()" class="rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-primary focus:outline-none">
        <option value="Semua Kategori">Semua Kategori</option>
        @foreach(['Upacara Adat', 'Festival', 'Workshop', 'Pameran', 'Pertunjukan Seni'] as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
    </select>
</form>

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
                @forelse ($events as $e)
                    @php
                        $now = now();
                        if ($e->start_datetime > $now) {
                            $statusLabel = 'Mendatang';
                            $statusClass = 'bg-primary/10 text-primary';
                        } elseif ($e->end_datetime < $now) {
                            $statusLabel = 'Selesai';
                            $statusClass = 'bg-gray-100 text-gray-400';
                        } else {
                            $statusLabel = 'Berlangsung';
                            $statusClass = 'bg-secondary/10 text-secondary-800';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4 font-medium text-charcoal">
                            <div>
                                <p>{{ $e->name }}</p>
                                <span class="inline-block mt-0.5 rounded bg-primary/8 px-1.5 py-0.5 text-[10px] font-semibold text-primary">{{ $e->getCategoryLabel() }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-500">
                            {{ $e->start_datetime->format('d M Y') }}
                            @if ($e->start_datetime->format('d M Y') !== $e->end_datetime->format('d M Y'))
                                - {{ $e->end_datetime->format('d M Y') }}
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-500">{{ $e->location_name }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.events.edit', $e->id) }}" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-primary/10 hover:text-primary" title="Edit">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.events.destroy', $e->id) }}" class="delete-form inline" data-confirm="Apakah Anda yakin ingin menghapus event ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-warning/10 hover:text-warning" title="Hapus">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-gray-400">Belum ada data event.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($events->hasPages())
        <div class="border-t border-gray-100 px-5 py-3.5">
            {{ $events->links() }}
        </div>
    @endif
</div>

@endsection
