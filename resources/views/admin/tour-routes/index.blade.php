@extends('layouts.admin')

@section('title', 'Rute Wisata')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Rute Wisata</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola jalur dan titik kunjungan yang direkomendasikan kepada wisatawan.</p>
    </div>
    <button onclick="openCreateModal()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Rute
    </button>
</div>

{{-- Route Cards --}}
<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    @forelse ($routes as $route)
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            {{-- Header --}}
            <div class="mb-4 flex items-start justify-between gap-2">
                <div>
                    <h3 class="font-semibold text-charcoal">{{ $route->name }}</h3>
                    <span class="mt-1 inline-block rounded-lg bg-primary/8 px-2.5 py-0.5 text-xs font-semibold text-primary">
                        {{ $route->difficulty }}
                    </span>
                    @if ($route->is_smart_route)
                        <span class="mt-1 inline-block rounded-lg bg-secondary/10 px-2.5 py-0.5 text-xs font-semibold text-secondary-800">
                            Smart Route
                        </span>
                    @endif
                </div>
                @if ($route->is_active)
                    <span class="flex shrink-0 items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-bold text-primary">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary"></span> Aktif
                    </span>
                @else
                    <span class="flex shrink-0 items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Nonaktif
                    </span>
                @endif
            </div>

            {{-- Description --}}
            @if($route->description)
                <p class="mb-4 text-xs text-gray-500 line-clamp-2">{{ $route->description }}</p>
            @endif

            {{-- Meta --}}
            <div class="mb-4 flex gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @if ($route->estimated_duration_minutes < 60)
                        {{ $route->estimated_duration_minutes }} menit
                    @else
                        {{ round($route->estimated_duration_minutes / 60, 1) }} jam
                    @endif
                </span>
                <span class="flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    @if ($route->distance_meters < 1000)
                        {{ $route->distance_meters }} m
                    @else
                        {{ round($route->distance_meters / 1000, 1) }} km
                    @endif
                </span>
            </div>

            {{-- Waypoints --}}
            <div class="relative mb-4 pl-4">
                <div class="absolute left-1.5 top-2 bottom-2 w-px bg-gray-200"></div>
                @forelse ($route->routePoints as $i => $point)
                    <div class="relative mb-2 flex items-center gap-2">
                        <span class="absolute -left-3 flex h-3 w-3 items-center justify-center rounded-full
                            {{ $i === 0 || $i === count($route->routePoints) - 1
                                ? 'bg-primary'
                                : 'border-2 border-gray-300 bg-white' }}">
                        </span>
                        <p class="pl-2 text-sm {{ $i === 0 || $i === count($route->routePoints) - 1 ? 'font-semibold text-charcoal' : 'text-gray-500' }}">
                            {{ $point->locationable ? $point->locationable->name : ($point->storytelling_content ?? 'Titik Kunjungan') }}
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic">Belum ada titik rute yang dikonfigurasi.</p>
                @endforelse
            </div>

            {{-- Actions --}}
            <div class="flex gap-2 border-t border-gray-50 pt-4">
                <button onclick="openEditModal({{ json_encode($route) }})" class="flex-1 rounded-xl border border-gray-200 py-2 text-xs font-semibold text-gray-600 transition-colors hover:bg-gray-50">
                    Edit Rute
                </button>
                <form method="POST" action="{{ route('admin.tour-routes.toggle', $route->id) }}" class="flex-1">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full rounded-xl border border-gray-200 py-2 text-xs font-semibold text-gray-600 transition-colors hover:bg-gray-50">
                        {{ $route->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.tour-routes.destroy', $route->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rute ini?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-warning/20 px-3 py-2 text-xs font-semibold text-warning transition-colors hover:bg-warning/5">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-full rounded-2xl border border-dashed border-gray-200 p-8 text-center text-gray-400">
            Belum ada rute wisata. Klik "Tambah Rute" untuk memulai.
        </div>
    @endforelse
</div>

{{-- Dynamic Modal Form --}}
<div id="route-modal" class="fixed inset-0 z-50 items-center justify-center hidden bg-charcoal/50 backdrop-blur-sm p-4">
    <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl transition-all">
        <div class="mb-4 flex items-center justify-between">
            <h3 id="modal-title" class="font-display text-lg font-bold text-charcoal">Tambah Rute Wisata</h3>
            <button onclick="closeModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="modal-form" method="POST" action="">
            @csrf
            <div id="method-container"></div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama Rute <span class="text-warning">*</span></label>
                    <input type="text" name="name" id="field-name" required class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Kategori / Tema <span class="text-warning">*</span></label>
                    <select name="difficulty" id="field-difficulty" required class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                        <option>Mudah</option>
                        <option>Sedang</option>
                        <option>Sulit</option>
                        <option>Edukasi</option>
                        <option>Alam</option>
                        <option>Belanja</option>
                        <option>Difabel</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Durasi (Menit) <span class="text-warning">*</span></label>
                        <input type="number" name="estimated_duration_minutes" id="field-duration" required class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Jarak (Meter) <span class="text-warning">*</span></label>
                        <input type="number" name="distance_meters" id="field-distance" required class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Deskripsi Rute</label>
                    <textarea name="description" id="field-desc" rows="3" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_smart_route" id="field-smart" value="1" class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label class="text-sm font-semibold text-gray-700">Smart Route (Rekomendasi AI)</label>
                </div>
                <div id="active-checkbox-container" class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="field-active" value="1" class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label class="text-sm font-semibold text-gray-700">Aktifkan Rute</label>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
                <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary-600">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const modal = document.getElementById('route-modal');
    const form = document.getElementById('modal-form');
    const modalTitle = document.getElementById('modal-title');
    const methodContainer = document.getElementById('method-container');
    const activeCheckboxContainer = document.getElementById('active-checkbox-container');

    function openCreateModal() {
        modalTitle.innerText = "Tambah Rute Wisata";
        form.action = "{{ route('admin.tour-routes.store') }}";
        methodContainer.innerHTML = "";
        activeCheckboxContainer.style.display = "none";
        
        document.getElementById('field-name').value = "";
        document.getElementById('field-difficulty').value = "Mudah";
        document.getElementById('field-duration').value = "";
        document.getElementById('field-distance').value = "";
        document.getElementById('field-desc').value = "";
        document.getElementById('field-smart').checked = false;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openEditModal(route) {
        modalTitle.innerText = "Edit Rute Wisata";
        form.action = `/admin/tour-routes/${route.id}`;
        methodContainer.innerHTML = `@method('PUT')`;
        activeCheckboxContainer.style.display = "flex";

        document.getElementById('field-name').value = route.name;
        document.getElementById('field-difficulty').value = route.difficulty;
        document.getElementById('field-duration').value = route.estimated_duration_minutes;
        document.getElementById('field-distance').value = route.distance_meters;
        document.getElementById('field-desc').value = route.description || "";
        document.getElementById('field-smart').checked = route.is_smart_route;
        document.getElementById('field-active').checked = route.is_active;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
