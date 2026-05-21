@extends('layouts.admin')

@section('title', 'Objek Budaya')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #picker-map { height: 220px; border-radius: 12px; z-index: 0; }
        .leaflet-control-attribution { display: none !important; }
        #picker-map .leaflet-container { border-radius: 12px; }
    </style>
@endpush

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
    <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl transition-all">
        <div class="mb-4 flex items-center justify-between">
            <h3 id="modal-title" class="font-display text-lg font-bold text-charcoal">Tambah Objek Budaya</h3>
            <button onclick="closeModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="modal-form" method="POST" action="" enctype="multipart/form-data">
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
                        <div class="relative mt-1 flex rounded-xl shadow-sm">
                            <input type="text" name="ar_marker_id" id="field-ar-marker" class="w-full rounded-l-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                            <button type="button" onclick="generateMarkerId()" class="inline-flex items-center rounded-r-xl border border-l-0 border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-gray-500 hover:bg-gray-100 hover:text-charcoal transition-colors">
                                Generate
                            </button>
                        </div>
                    </div>
                </div>
                {{-- Interactive Map Picker --}}
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label class="block text-sm font-semibold text-gray-700">Lokasi (Klik peta untuk memilih titik)</label>
                        <button type="button" onclick="resetMapToDefault()" class="text-xs text-primary hover:underline">Reset ke pusat desa</button>
                    </div>
                    <div id="picker-map" class="w-full overflow-hidden border border-gray-200 shadow-sm"></div>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400 uppercase">Lat</span>
                            <input type="number" step="any" name="latitude" id="field-latitude" placeholder="-8.5406" class="w-full rounded-xl border border-gray-200 py-2 pl-10 pr-3 text-sm focus:border-primary focus:outline-none" oninput="syncMapFromFields()">
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400 uppercase">Lng</span>
                            <input type="number" step="any" name="longitude" id="field-longitude" placeholder="115.4170" class="w-full rounded-xl border border-gray-200 py-2 pl-10 pr-3 text-sm focus:border-primary focus:outline-none" oninput="syncMapFromFields()">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">File Model 3D (.glb)</label>
                    <input type="file" name="model_3d_file" id="field-model-file" accept=".glb,.gltf" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    <p id="current-model-container" class="mt-1 text-xs text-gray-500 hidden">
                        File saat ini: <span id="current-model-path" class="font-mono bg-gray-50 px-1 py-0.5 rounded border border-gray-100"></span>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Audio Narasi (.mp3)</label>
                    <input type="file" name="audio_narration_file" id="field-audio-file" accept=".mp3,.wav" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    <p id="current-audio-container" class="mt-1 text-xs text-gray-500 hidden">
                        File saat ini: <span id="current-audio-path" class="font-mono bg-gray-50 px-1 py-0.5 rounded border border-gray-100"></span>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Foto Sejarah (PNG, JPG, dll. - Bisa pilih banyak)</label>
                    <input type="file" name="historical_images[]" id="field-images" accept="image/*" multiple class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    <div id="current-images-container" class="mt-2 hidden">
                        <p class="text-xs text-gray-700 font-semibold mb-1">Foto saat ini:</p>
                        <div id="current-images-list" class="flex flex-wrap gap-2"></div>
                    </div>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // ==========================================
    // MAP PICKER
    // ==========================================
    const PENGLIPURAN_LAT = -8.421750367447837;
    const PENGLIPURAN_LNG = 115.35900208148409;
    const PENGLIPURAN_ZOOM = 17;

    let pickerMap = null;
    let pickerMarker = null;

    function initPickerMap(lat, lng) {
        if (pickerMap) {
            // Map already exists — just update view & marker
            const center = (lat && lng) ? [lat, lng] : [PENGLIPURAN_LAT, PENGLIPURAN_LNG];
            pickerMap.setView(center, PENGLIPURAN_ZOOM);
            if (lat && lng) {
                placePickerMarker(lat, lng);
            } else {
                if (pickerMarker) { pickerMarker.remove(); pickerMarker = null; }
            }
            setTimeout(() => pickerMap.invalidateSize(), 50);
            return;
        }

        pickerMap = L.map('picker-map', { zoomControl: true, attributionControl: false })
            .setView([PENGLIPURAN_LAT, PENGLIPURAN_LNG], PENGLIPURAN_ZOOM);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 20
        }).addTo(pickerMap);

        pickerMap.on('click', function(e) {
            placePickerMarker(e.latlng.lat, e.latlng.lng);
            document.getElementById('field-latitude').value = e.latlng.lat.toFixed(7);
            document.getElementById('field-longitude').value = e.latlng.lng.toFixed(7);
        });

        if (lat && lng) {
            placePickerMarker(lat, lng);
        }

        setTimeout(() => pickerMap.invalidateSize(), 50);
    }

    function placePickerMarker(lat, lng) {
        const icon = L.divIcon({
            className: '',
            html: `<div style="width:20px;height:20px;background:#1E5128;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.4);transform:translate(-50%,-50%)"></div>`,
            iconSize: [0, 0],
            iconAnchor: [0, 0]
        });
        if (pickerMarker) pickerMarker.remove();
        pickerMarker = L.marker([lat, lng], { icon, draggable: true }).addTo(pickerMap);
        pickerMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById('field-latitude').value = pos.lat.toFixed(7);
            document.getElementById('field-longitude').value = pos.lng.toFixed(7);
        });
    }

    function syncMapFromFields() {
        const lat = parseFloat(document.getElementById('field-latitude').value);
        const lng = parseFloat(document.getElementById('field-longitude').value);
        if (!isNaN(lat) && !isNaN(lng) && pickerMap) {
            placePickerMarker(lat, lng);
            pickerMap.setView([lat, lng], pickerMap.getZoom());
        }
    }

    function resetMapToDefault() {
        document.getElementById('field-latitude').value = '';
        document.getElementById('field-longitude').value = '';
        if (pickerMap) {
            pickerMap.setView([PENGLIPURAN_LAT, PENGLIPURAN_LNG], PENGLIPURAN_ZOOM);
        }
        if (pickerMarker) { pickerMarker.remove(); pickerMarker = null; }
    }

    // ==========================================
    // MODAL
    // ==========================================
    const modal = document.getElementById('object-modal');
    const form = document.getElementById('modal-form');
    const modalTitle = document.getElementById('modal-title');
    const methodContainer = document.getElementById('method-container');
    let isMarkerManual = false;

    function openCreateModal() {
        modalTitle.innerText = "Tambah Objek Budaya";
        form.action = "{{ route('admin.cultural-objects.store') }}";
        methodContainer.innerHTML = "";
        
        document.getElementById('field-name').value = "";
        document.getElementById('field-category').value = "temple";
        document.getElementById('field-ar-marker').value = "";
        document.getElementById('field-latitude').value = "";
        document.getElementById('field-longitude').value = "";
        document.getElementById('field-model-file').value = "";
        document.getElementById('field-audio-file').value = "";
        document.getElementById('field-images').value = "";
        document.getElementById('field-desc').value = "";

        document.getElementById('current-model-container').classList.add('hidden');
        document.getElementById('current-audio-container').classList.add('hidden');
        document.getElementById('current-images-container').classList.add('hidden');
        
        isMarkerManual = false;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        initPickerMap(null, null);
    }

    function openEditModal(obj) {
        modalTitle.innerText = "Edit Objek Budaya";
        form.action = `/admin/cultural-objects/${obj.id}`;
        methodContainer.innerHTML = `@method('PUT')`;

        document.getElementById('field-name').value = obj.name;
        document.getElementById('field-category').value = obj.category;
        document.getElementById('field-ar-marker').value = obj.ar_marker_id || "";
        isMarkerManual = true;
        document.getElementById('field-latitude').value = obj.latitude || "";
        document.getElementById('field-longitude').value = obj.longitude || "";
        document.getElementById('field-desc').value = obj.description || "";
        
        // Reset file inputs
        document.getElementById('field-model-file').value = "";
        document.getElementById('field-audio-file').value = "";
        document.getElementById('field-images').value = "";

        // Model 3D Path
        const modelContainer = document.getElementById('current-model-container');
        const modelPath = document.getElementById('current-model-path');
        if (obj.model_3d_path) {
            modelPath.textContent = obj.model_3d_path;
            modelContainer.classList.remove('hidden');
        } else {
            modelContainer.classList.add('hidden');
        }

        // Audio Path
        const audioContainer = document.getElementById('current-audio-container');
        const audioPath = document.getElementById('current-audio-path');
        if (obj.audio_narration_path) {
            audioPath.textContent = obj.audio_narration_path;
            audioContainer.classList.remove('hidden');
        } else {
            audioContainer.classList.add('hidden');
        }

        // Historical Images
        const imagesContainer = document.getElementById('current-images-container');
        const imagesList = document.getElementById('current-images-list');
        imagesList.textContent = ''; // clear old ones using safe textContent assignment
        
        if (obj.historical_images && Array.isArray(obj.historical_images) && obj.historical_images.length > 0) {
            obj.historical_images.forEach(img => {
                const imgContainer = document.createElement('div');
                imgContainer.className = 'relative group w-16 h-16 rounded-lg overflow-hidden border border-gray-200';
                
                const imgEl = document.createElement('img');
                imgEl.src = `/storage/${img}`;
                imgEl.className = 'w-full h-full object-cover';
                
                imgContainer.appendChild(imgEl);
                imagesList.appendChild(imgContainer);
            });
            imagesContainer.classList.remove('hidden');
        } else {
            imagesContainer.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        initPickerMap(obj.latitude ? parseFloat(obj.latitude) : null, obj.longitude ? parseFloat(obj.longitude) : null);
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function generateMarkerId() {
        const nameVal = document.getElementById('field-name').value.trim();
        if (nameVal) {
            const slug = nameVal.toUpperCase().replace(/[^A-Z0-9]/g, '_').replace(/_+/g, '_').replace(/^_+|_+$/g, '');
            document.getElementById('field-ar-marker').value = `MARKER_${slug}`;
        } else {
            const rand = Math.random().toString(36).substring(2, 8).toUpperCase();
            document.getElementById('field-ar-marker').value = `MARKER_${rand}`;
        }
    }

    document.getElementById('field-name').addEventListener('input', function() {
        if (!isMarkerManual) {
            const nameVal = this.value.trim();
            if (nameVal) {
                const slug = nameVal.toUpperCase().replace(/[^A-Z0-9]/g, '_').replace(/_+/g, '_').replace(/^_+|_+$/g, '');
                document.getElementById('field-ar-marker').value = `MARKER_${slug}`;
            } else {
                document.getElementById('field-ar-marker').value = "";
            }
        }
    });

    document.getElementById('field-ar-marker').addEventListener('input', function() {
        isMarkerManual = true;
    });

    // Auto-parse Google Maps coordinate paste (e.g. "-8.4217, 115.3590")
    document.getElementById('field-latitude').addEventListener('paste', function(e) {
        const text = (e.clipboardData || window.clipboardData).getData('text').trim();
        const match = text.match(/^(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)$/);
        if (match) {
            e.preventDefault();
            const lat = parseFloat(match[1]);
            const lng = parseFloat(match[2]);
            this.value = lat;
            document.getElementById('field-longitude').value = lng;
            syncMapFromFields();
        }
    });
</script>
@endpush
