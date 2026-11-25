@extends('layouts.dashboard')

@section('title', 'Objek Budaya')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold text-charcoal">Objek Budaya</h1>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Kelola foto, cerita, audio, dan model 3D dalam satu tempat. Atur titik melalui <a href="{{ route('admin.map-manager') }}" class="text-primary underline">Peta Lokasi & Titik</a>.</p>
            <a href="{{ route('admin.settings') }}" class="mt-2 inline-flex min-h-11 items-center text-sm font-semibold text-primary underline underline-offset-4">Atur objek unggulan dan induk budaya seluruh rumah</a>
        </div>
        <a href="{{ route('admin.cultural-objects.create') }}" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-primary px-4 text-sm font-semibold text-white transition-colors hover:bg-primary-hover focus-visible:outline-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            Tambah Objek Budaya
        </a>
    </div>

    <search class="mb-6 block rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">
        <x-dashboard.table-filters :action="route('admin.cultural-objects')" id="objects-filters" search-name="search" label="Cari objek budaya" placeholder="Ketik sebagian nama dalam bahasa Indonesia atau Inggris" :filtered="request()->filled('search') || request()->filled('ar_status')">
            <div class="min-w-0 sm:w-52">
                <label for="objects-ar-status" class="mb-1.5 block text-xs font-semibold text-gray-700">Status 3D / AR</label>
                <select id="objects-ar-status" name="ar_status" class="dashboard-table-input w-full">
                    <option value="">Semua objek</option>
                    <option value="with_model" @selected(request('ar_status') === 'with_model')>Model tersedia</option>
                    <option value="without_model" @selected(request('ar_status') === 'without_model')>Belum ada model</option>
                    <option value="without_marker" @selected(request('ar_status') === 'without_marker')>Model tanpa marker</option>
                </select>
            </div>
        </x-dashboard.table-filters>
    </search>

    <div class="mb-3 flex items-center justify-between gap-3">
        <h2 class="text-sm font-semibold text-charcoal">Koleksi objek budaya</h2>
        <p class="text-xs text-gray-500">{{ $objects->total() }} objek{{ request()->filled('search') || request()->filled('ar_status') ? ' ditemukan' : '' }}</p>
    </div>
    <ul class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" aria-label="Objek budaya">
        @forelse ($objects as $object)
            @php
                $images = array_values(array_filter($object->historical_images ?? []));
                $name = translateValue($object->name);
                $hasIndonesianAudio = filled($object->audio_narration_paths['id'] ?? null);
                $hasEnglishAudio = filled($object->audio_narration_paths['en'] ?? null);
                $hasModel = filled($object->arModel?->model_3d_path);
                $editUrl = route('admin.cultural-objects.edit', $object->id);
            @endphp
            <li class="min-w-0">
                <article class="flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white">
                    <a href="{{ $editUrl }}" aria-label="Ubah {{ $name }}" class="group relative block aspect-[16/10] overflow-hidden bg-primary/5 focus-visible:outline-primary" x-data="{ failed: false }">
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-primary/60" @if(count($images)) x-cloak x-show="failed" @endif>
                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.25" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                            <span class="text-xs font-medium">{{ count($images) ? 'Foto tidak dapat dimuat' : 'Tambahkan foto objek' }}</span>
                        </div>
                        @if(count($images))
                            <img src="{{ asset('storage/'.$images[0]) }}" alt="{{ $name }}" width="640" height="400" loading="{{ $loop->index < 3 ? 'eager' : 'lazy' }}" decoding="async" class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 motion-safe:group-hover:scale-105" x-show="!failed" x-on:error="failed = true">
                            <span class="absolute bottom-3 right-3 rounded-lg bg-charcoal/75 px-2 py-1 text-xs font-semibold text-white">{{ count($images) }} foto</span>
                        @endif
                    </a>
                    <div class="flex flex-1 flex-col gap-4 p-5">
                        <div>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <x-dashboard.table-badge tone="success" class="capitalize">{{ $object->category }}</x-dashboard.table-badge>
                                @if($object->parent)
                                    <span class="min-w-0 truncate text-xs text-gray-500">Bagian dari {{ translateValue($object->parent->name) }}</span>
                                @endif
                            </div>
                            <h3 class="font-display text-xl font-bold leading-snug text-charcoal"><a href="{{ $editUrl }}" class="rounded hover:text-primary focus-visible:outline-primary">{{ $name }}</a></h3>
                            @if(filled(translateValue($object->short_description)))
                                <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-gray-500">{{ strip_tags(translateValue($object->short_description)) }}</p>
                            @endif
                        </div>
                        <dl class="mt-auto grid grid-cols-2 gap-3 border-y border-gray-100 py-3 text-xs">
                            <div>
                                <dt class="font-semibold text-gray-700">3D / AR</dt>
                                <dd class="mt-1 text-gray-500">{{ $hasModel ? 'Model tersedia' : 'Belum ada model' }}</dd>
                                @if($hasModel)
                                    <dd class="mt-1 text-gray-500">USDZ: {{ filled($object->arModel->model_3d_usdz_path) ? 'Tersedia' : 'Belum ada' }}</dd>
                                @endif
                                @if(filled($object->arModel?->ar_marker_id))
                                    <dd class="mt-1 break-all text-gray-500">Marker: {{ $object->arModel->ar_marker_id }}</dd>
                                @elseif($hasModel)
                                    <dd class="mt-1 text-gray-500">Marker belum diatur</dd>
                                @endif
                            </div>
                            <div>
                                <dt class="font-semibold text-gray-700">Audio narasi</dt>
                                <dd class="mt-1 text-gray-500">ID: {{ $hasIndonesianAudio ? 'Tersedia' : 'Belum tersedia' }}</dd>
                                <dd class="mt-1 text-gray-500">EN: {{ $hasEnglishAudio ? 'Tersedia' : 'Belum tersedia' }}</dd>
                            </div>
                        </dl>
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                            <a href="{{ route('admin.map-manager', ['place' => 'cultural_object:'.$object->id]) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline underline-offset-4">{{ $object->map_locations_count ? $object->map_locations_count.' titik peta' : 'Taruh di peta' }}</a>
                            <a href="{{ $editUrl }}#model-3d" class="inline-flex min-h-11 items-center font-semibold text-primary underline underline-offset-4">{{ $hasModel ? 'Kelola 3D/AR' : 'Tambah model 3D' }}</a>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ $editUrl }}" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-primary/10 px-4 text-sm font-semibold text-primary transition-colors hover:bg-primary/20 focus-visible:outline-primary">Ubah objek</a>
                            <form action="{{ route('admin.cultural-objects.destroy', $object->id) }}" method="POST" class="delete-form" data-confirm="Hapus objek budaya ini beserta titik petanya? Jika masih memiliki subobjek atau model 3D, kelola terlebih dahulu.">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="redirect_to" value="cultural-objects">
                                <button type="submit" aria-label="Hapus {{ $name }}" class="min-h-11 rounded-xl px-3 text-sm font-semibold text-red-700 transition-colors hover:bg-red-50 focus-visible:outline-red-700">Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
            </li>
        @empty
            <li class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center">
                <h3 class="font-display text-xl font-bold text-charcoal">{{ request()->filled('search') || request()->filled('ar_status') ? 'Objek tidak ditemukan' : 'Belum ada objek budaya' }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ request()->filled('search') || request()->filled('ar_status') ? 'Coba potongan nama yang lebih singkat atau ubah filter 3D / AR.' : 'Tambahkan objek pertama beserta foto dan ceritanya.' }}</p>
                <a href="{{ request()->filled('search') || request()->filled('ar_status') ? route('admin.cultural-objects') : route('admin.cultural-objects.create') }}" class="mt-4 inline-flex min-h-11 items-center rounded-xl bg-primary px-4 text-sm font-semibold text-white">{{ request()->filled('search') || request()->filled('ar_status') ? 'Tampilkan semua objek' : 'Tambah Objek Budaya' }}</a>
            </li>
        @endforelse
    </ul>
    @if($objects->hasPages())
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white px-5 py-4">
            {{ $objects->onEachSide(1)->links('components.dashboard.table-pagination', ['paginationLabel' => 'Navigasi halaman objek budaya', 'itemLabel' => 'objek budaya']) }}
        </div>
    @endif
@endsection
