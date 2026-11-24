@extends('layouts.dashboard')

@section('title', 'Fasilitas Umum')

@section('content')

    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-charcoal text-2xl font-bold">Fasilitas Umum</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola toilet, parkir, dan fasilitas lain. Titik tambahan diatur lewat <a href="{{ route('admin.map-manager') }}" class="text-primary underline">Peta Lokasi & Titik</a>.</p>
        </div>
        <a href="{{ route('admin.facilities.create') }}"
            class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Fasilitas
        </a>
    </div>

    <x-dashboard.table title="Fasilitas umum" :columns="['Nama', 'Tipe', 'Status', 'Titik peta', ['label' => 'Aksi', 'align' => 'right']]" :paginator="$facilities">
        <x-slot:toolbar>
            <x-dashboard.table-filters :action="route('admin.facilities')" id="facilities-filters" search-name="search" label="Cari fasilitas" placeholder="Nama fasilitas" :filtered="request()->filled('search')">
            </x-dashboard.table-filters>
        </x-slot:toolbar>

        @forelse ($facilities as $facility)
            <tr>
                <th scope="row" class="text-charcoal font-medium">{{ translateValue($facility->name) }}</th>
                <td>
                    <x-dashboard.table-badge tone="success" class="capitalize">{{ $facility->type }}</x-dashboard.table-badge>
                </td>
                <td>
                    @if ($facility->is_active)
                        <x-dashboard.table-badge tone="success">Aktif</x-dashboard.table-badge>
                    @else
                        <x-dashboard.table-badge>Nonaktif</x-dashboard.table-badge>
                    @endif
                </td>
                <td class="text-gray-500">{{ $facility->map_locations_count }} titik</td>
                <td>
                    <div class="table-actions">
                        <x-dashboard.table-action href="{{ route('admin.facilities.edit', $facility->id) }}">Ubah</x-dashboard.table-action>
                        <form action="{{ route('admin.facilities.destroy', $facility->id) }}" method="POST" class="delete-form"
                            data-confirm="Hapus fasilitas ini beserta semua titiknya?">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="redirect_to" value="facilities">
                            <x-dashboard.table-action type="submit" variant="danger">Hapus</x-dashboard.table-action>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-dashboard.table-empty :colspan="5" :filtered="request()->filled('search')" message="Belum ada fasilitas." />
        @endforelse
    </x-dashboard.table>

@endsection
