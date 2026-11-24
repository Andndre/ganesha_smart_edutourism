@extends('layouts.dashboard')

@section('title', 'Objek Budaya')

@section('content')

    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-charcoal text-2xl font-bold">Objek Budaya</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola konten objek budaya. Titik lokasi di peta diatur lewat <a href="{{ route('admin.map-manager') }}" class="text-primary underline">Peta Lokasi & Titik</a>.</p>
        </div>
        <a href="{{ route('admin.cultural-objects.create') }}"
            class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Objek Budaya
        </a>
    </div>

    <x-dashboard.table title="Objek budaya" :columns="['Nama', 'Tri Hita Karana', 'Titik peta', 'AR', 'Gambar', 'Audio', ['label' => 'Aksi', 'align' => 'right']]" :paginator="$objects">
        <x-slot:toolbar>
            <x-dashboard.table-filters :action="route('admin.cultural-objects')" id="objects-filters" search-name="search" label="Cari objek budaya" placeholder="Nama objek budaya" :filtered="request()->filled('search')">
            </x-dashboard.table-filters>
        </x-slot:toolbar>

        @forelse ($objects as $object)
            @php
                $hasImages = collect($object->historical_images ?? [])->contains(fn ($path) => filled($path));
                $hasIndonesianAudio = filled($object->audio_narration_paths['id'] ?? null);
                $hasEnglishAudio = filled($object->audio_narration_paths['en'] ?? null);
                $audioCount = (int) $hasIndonesianAudio + (int) $hasEnglishAudio;
            @endphp
            <tr>
                <th scope="row" class="text-charcoal font-medium">
                    @if ($object->parent)
                        <span class="text-xs font-normal text-gray-600">{{ translateValue($object->parent->name) }}
                            &rsaquo;</span>
                    @endif
                    {{ translateValue($object->name) }}
                </th>
                <td>
                    <x-dashboard.table-badge tone="success" class="capitalize">{{ $object->category }}</x-dashboard.table-badge>
                </td>
                <td class="text-gray-500">
                    @if ($object->map_locations_count === 0)
                        <x-dashboard.table-action :href="route('admin.map-manager', ['place' => 'cultural_object:'.$object->id])">Taruh di peta</x-dashboard.table-action>
                    @else
                        {{ $object->map_locations_count }} titik
                    @endif
                </td>
                <td>
                    @if ($object->arModel)
                        <x-dashboard.table-badge tone="success">Tersedia</x-dashboard.table-badge>
                    @else
                        <x-dashboard.table-badge>Belum tersedia</x-dashboard.table-badge>
                    @endif
                </td>
                <td>
                    <x-dashboard.table-badge :tone="$hasImages ? 'success' : 'neutral'">{{ $hasImages ? 'Tersedia' : 'Belum tersedia' }}</x-dashboard.table-badge>
                </td>
                <td>
                    <div class="flex flex-col items-start gap-1.5">
                        <x-dashboard.table-badge :tone="$audioCount === 2 ? 'success' : ($audioCount === 1 ? 'warning' : 'neutral')">{{ $audioCount }}/2</x-dashboard.table-badge>
                        <div class="flex flex-col gap-0.5 whitespace-nowrap text-xs text-gray-500">
                            <span>ID: {{ $hasIndonesianAudio ? 'Tersedia' : 'Belum tersedia' }}</span>
                            <span>EN: {{ $hasEnglishAudio ? 'Tersedia' : 'Belum tersedia' }}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="table-actions">
                        <x-dashboard.table-action href="{{ route('admin.cultural-objects.edit', $object->id) }}">Ubah</x-dashboard.table-action>
                        <form action="{{ route('admin.cultural-objects.destroy', $object->id) }}" method="POST" class="delete-form"
                            data-confirm="Hapus objek budaya ini beserta semua titik dan model AR-nya?">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="redirect_to" value="cultural-objects">
                            <x-dashboard.table-action type="submit" variant="danger">Hapus</x-dashboard.table-action>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-dashboard.table-empty :colspan="7" :filtered="request()->filled('search')" message="Belum ada objek budaya." />
        @endforelse
    </x-dashboard.table>

@endsection
