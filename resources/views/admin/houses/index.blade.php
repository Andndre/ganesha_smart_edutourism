@extends('layouts.dashboard')

@section('title', 'Pekarangan')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.18em] text-primary">Warisan Desa</p>
            <h1 class="font-display text-3xl font-bold text-charcoal">Pekarangan</h1>
            <p class="mt-2 text-sm text-gray-600">Tambahkan rumah warga dan pilih pengelolanya. UMKM Rumah dibuat otomatis, lalu data dapat dilengkapi sebelum ditampilkan untuk pengunjung.</p>
        </div>
        @if (auth()->user()?->isAdmin())
            <a href="{{ route('admin.houses.create') }}" class="tap-target rounded-full bg-primary px-5 text-sm font-bold text-white">Tambah Pekarangan</a>
        @endif
    </div>

    <x-dashboard.table title="Direktori pekarangan" :columns="['Pekarangan', 'Kepala keluarga', ['label' => 'UMKM', 'align' => 'right'], 'Status', ['label' => 'Aksi', 'align' => 'right']]" :paginator="$houses">
        <x-slot:toolbar>
            <x-dashboard.table-filters :action="route('admin.houses.index')" id="houses-filters" search-name="q" label="Cari rumah adat" placeholder="Nomor rumah atau nama kepala keluarga" :filtered="request()->filled('q')">
            </x-dashboard.table-filters>
        </x-slot:toolbar>

        @forelse ($houses as $house)
            <tr>
                <th scope="row" class="font-semibold">No. {{ $house->number }}</th>
                <td>{{ $house->head_name }}</td>
                <td class="table-number">{{ $house->umkm_profile_count }}</td>
                <td>
                    <x-dashboard.table-badge :tone="$house->is_published ? 'success' : 'warning'">{{ $house->is_published ? 'Terbit' : 'Draf' }}</x-dashboard.table-badge>
                </td>
                <td>
                    <div class="table-actions">
                        <x-dashboard.table-action :href="route('admin.houses.edit', $house)">{{ auth()->user()?->isAdmin() ? 'Ubah' : 'Lihat' }}</x-dashboard.table-action>
                    </div>
                </td>
            </tr>
        @empty
            <x-dashboard.table-empty :colspan="5" :filtered="request()->filled('q')" message="Belum ada rumah adat." />
        @endforelse
    </x-dashboard.table>

@endsection
