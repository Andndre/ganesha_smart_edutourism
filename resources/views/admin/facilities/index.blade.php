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

    <form method="GET" action="{{ route('admin.facilities') }}" class="mb-4">
        <div class="relative max-w-md">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari fasilitas..."
                class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm focus:outline-none focus:ring-1">
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nama</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Tipe</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Titik Peta</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($facilities as $facility)
                        <tr class="hover:bg-gray-50/50">
                            <td class="text-charcoal px-5 py-4 font-medium">{{ translateValue($facility->name) }}</td>
                            <td class="px-5 py-4">
                                <span class="bg-primary/10 text-primary-800 rounded-lg px-2.5 py-1 text-xs font-semibold capitalize">{{ $facility->type }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if ($facility->is_active)
                                    <span class="rounded-lg bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">Aktif</span>
                                @else
                                    <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-500">{{ $facility->mapLocations()->count() }} titik</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.facilities.edit', $facility->id) }}" class="text-primary font-semibold hover:underline">Edit</a>
                                    <form action="{{ route('admin.facilities.destroy', $facility->id) }}" method="POST" class="delete-form"
                                        data-confirm="Hapus fasilitas ini beserta semua titiknya?">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="facilities">
                                        <button type="submit" class="font-semibold text-red-500 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-400">Belum ada fasilitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $facilities->links() }}
    </div>

@endsection
