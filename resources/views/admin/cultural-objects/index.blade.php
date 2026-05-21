@extends('layouts.admin')

@section('title', 'Objek Budaya')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Objek Budaya</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola data cagar budaya dan situs warisan Desa Penglipuran.</p>
    </div>
    <button onclick="openCreateModal()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Objek
    </button>
</div>

{{-- Search + Filter --}}
<form method="GET" action="{{ route('admin.cultural-objects') }}" class="mb-4 flex flex-col gap-3 sm:flex-row">
    <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari objek budaya..." class="w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
    </div>
    <select name="category" onchange="this.form.submit()" class="rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-primary focus:outline-none">
        <option value="Semua Kategori">Semua Kategori</option>
        <option value="temple" {{ request('category') === 'temple' ? 'selected' : '' }}>Pura</option>
        <option value="house" {{ request('category') === 'house' ? 'selected' : '' }}>Bale Adat</option>
        <option value="craft" {{ request('category') === 'craft' ? 'selected' : '' }}>Monumen/Kerajinan</option>
        <option value="tradition" {{ request('category') === 'tradition' ? 'selected' : '' }}>Alam/Tradisi</option>
    </select>
</form>

{{-- Table --}}
<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nama Objek</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Kategori</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Lokasi</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status AR/3D</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($objects as $obj)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4 font-medium text-charcoal">{{ $obj->name }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-lg bg-primary/8 px-2.5 py-1 text-xs font-semibold text-primary">
                                @if($obj->category === 'temple') Pura
                                @elseif($obj->category === 'house') Bale Adat
                                @elseif($obj->category === 'craft') Monumen/Kerajinan
                                @elseif($obj->category === 'tradition') Alam/Tradisi
                                @else {{ $obj->category }}
                                @endif
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-500">
                            @if($obj->latitude && $obj->longitude)
                                {{ round($obj->latitude, 4) }}, {{ round($obj->longitude, 4) }}
                            @else
                                Belum diset
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col gap-0.5 text-xs text-gray-500">
                                <span>Marker: {{ $obj->ar_marker_id ?: '-' }}</span>
                                <span>Model: {{ $obj->model_3d_path ? 'Tersedia' : '-' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <button onclick="openEditModal({{ json_encode($obj) }})" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-primary/10 hover:text-primary" title="Edit">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('admin.cultural-objects.destroy', $obj->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus objek ini?')" class="inline">
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
                        <td colspan="5" class="px-5 py-8 text-center text-gray-400">Belum ada data objek budaya.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($objects->hasPages())
        <div class="border-t border-gray-100 px-5 py-3.5">
            {{ $objects->links() }}
        </div>
    @endif
</div>

{{-- Dynamic Modal Form --}}
<div id="object-modal" class="fixed inset-0 z-50 items-center justify-center hidden bg-charcoal/50 backdrop-blur-sm p-4">
    <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl transition-all">
        <div class="mb-4 flex items-center justify-between">
            <h3 id="modal-title" class="font-display text-lg font-bold text-charcoal">Tambah Objek Budaya</h3>
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
                    <label class="block text-sm font-semibold text-gray-700">Nama Objek <span class="text-warning">*</span></label>
                    <input type="text" name="name" id="field-name" required class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Kategori <span class="text-warning">*</span></label>
                        <select name="category" id="field-category" required class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                            <option value="temple">Pura</option>
                            <option value="house">Bale Adat</option>
                            <option value="craft">Monumen/Kerajinan</option>
                            <option value="tradition">Alam/Tradisi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Marker AR ID</label>
                        <input type="text" name="ar_marker_id" id="field-ar-marker" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Latitude</label>
                        <input type="number" step="any" name="latitude" id="field-latitude" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Longitude</label>
                        <input type="number" step="any" name="longitude" id="field-longitude" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">File Model 3D (.glb path)</label>
                    <input type="text" name="model_3d_path" id="field-model" placeholder="models/candi_bentar.glb" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Audio Narasi (.mp3 path)</label>
                    <input type="text" name="audio_narration_path" id="field-audio" placeholder="audio/candi_bentar.mp3" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                    <textarea name="description" id="field-desc" rows="3" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
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
    const modal = document.getElementById('object-modal');
    const form = document.getElementById('modal-form');
    const modalTitle = document.getElementById('modal-title');
    const methodContainer = document.getElementById('method-container');

    function openCreateModal() {
        modalTitle.innerText = "Tambah Objek Budaya";
        form.action = "{{ route('admin.cultural-objects.store') }}";
        methodContainer.innerHTML = "";
        
        document.getElementById('field-name').value = "";
        document.getElementById('field-category').value = "Pura";
        document.getElementById('field-ar-marker').value = "";
        document.getElementById('field-latitude').value = "";
        document.getElementById('field-longitude').value = "";
        document.getElementById('field-model').value = "";
        document.getElementById('field-audio').value = "";
        document.getElementById('field-desc').value = "";
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openEditModal(obj) {
        modalTitle.innerText = "Edit Objek Budaya";
        form.action = `/admin/cultural-objects/${obj.id}`;
        methodContainer.innerHTML = `@method('PUT')`;

        document.getElementById('field-name').value = obj.name;
        document.getElementById('field-category').value = obj.category;
        document.getElementById('field-ar-marker').value = obj.ar_marker_id || "";
        document.getElementById('field-latitude').value = obj.latitude || "";
        document.getElementById('field-longitude').value = obj.longitude || "";
        document.getElementById('field-model').value = obj.model_3d_path || "";
        document.getElementById('field-audio').value = obj.audio_narration_path || "";
        document.getElementById('field-desc').value = obj.description || "";

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
