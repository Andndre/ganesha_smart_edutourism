@extends('layouts.dashboard')

@section('title', 'Aset AR & Marker')

@push('styles')
<style>
    .model-viewer-wrapper {
        position: relative;
        width: 100%;
        height: 180px;
        background: radial-gradient(circle, #f9fafb 0%, #f3f4f6 100%);
        border: 1px border-dashed #d1d5db;
        border-radius: 12px;
        overflow: hidden;
    }
    model-viewer {
        width: 100%;
        height: 100%;
        --poster-color: transparent;
    }
    .audio-player-mini {
        background-color: #f3f4f6;
        border-radius: 8px;
        padding: 6px 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endpush

@section('content')
<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Manajer Aset AR &amp; Marker</h1>
        <p class="mt-0.5 text-sm text-gray-500 font-medium">Kelola model 3D interaktif dan marker QR untuk teknologi Augmented Reality desa.</p>
    </div>
    <div class="flex gap-2">
        <button onclick="openModelModal()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Model 3D
        </button>
        <button onclick="openMarkerModal()" class="inline-flex items-center gap-2 rounded-xl border-2 border-primary text-primary px-4 py-2 text-sm font-semibold transition-all hover:bg-primary/5 active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Marker QR
        </button>
    </div>
</div>

{{-- Main Workspace Content Grid --}}
<div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
    
    {{-- Left Side: 3D Models Panel --}}
    <div class="lg:col-span-6 flex flex-col space-y-4">
        <div class="border border-gray-100 rounded-2xl bg-white p-4 shadow-xs">
            <h2 class="font-display text-lg font-bold text-charcoal mb-1">Daftar Model 3D</h2>
            <p class="text-xs text-gray-500 mb-4 font-medium">Model GLB interaktif, USDZ untuk iOS Quick Look, dan audio narasi penjelasan.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($models as $m)
                    <div class="rounded-xl border border-gray-100 bg-white p-3 flex flex-col shadow-2xs hover:shadow-xs transition-shadow">
                        <div class="model-viewer-wrapper mb-3">
                            <model-viewer src="{{ asset('storage/' . $m->model_3d_path) }}" 
                                          camera-controls 
                                          auto-rotate 
                                          shadow-intensity="1">
                            </model-viewer>
                        </div>
                        <h3 class="font-bold text-sm text-charcoal truncate">{{ $m->name }}</h3>
                        <p class="text-xs text-gray-500 mt-1 flex-1 line-clamp-2 h-8">{{ $m->description ?: 'Tidak ada deskripsi.' }}</p>
                        
                        <div class="mt-2.5 pt-2 border-t border-gray-50 flex items-center justify-between">
                            <span class="text-[10px] bg-primary/10 text-primary font-bold px-2 py-0.5 rounded-full">
                                {{ $m->arMarkers->count() }} Marker Placed
                            </span>
                            <div class="flex gap-1">
                                <button onclick="openModelEditModal({{ json_encode($m) }})" class="p-1 text-gray-400 hover:text-primary transition-colors" title="Edit Model">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('admin.ar-manager.models.destroy', $m->id) }}" class="delete-form inline" data-confirm="Apakah Anda yakin ingin menghapus model ini beserta marker yang menggunakannya?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-gray-400 hover:text-warning transition-colors" title="Hapus Model">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 py-8 text-center text-gray-400 text-sm">Belum ada model 3D ditambahkan.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Right Side: QR Markers Table Panel --}}
    <div class="lg:col-span-6 flex flex-col space-y-4">
        <div class="border border-gray-100 rounded-2xl bg-white p-4 shadow-xs">
            <h2 class="font-display text-lg font-bold text-charcoal mb-1">Daftar Marker QR AR</h2>
            <p class="text-xs text-gray-500 mb-4 font-medium">Marker QR fisik yang ditempelkan di lokasi wisata untuk memicu model AR interaktif.</p>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">ID Marker</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Model 3D</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Penempatan Peta</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($markers as $mk)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-3 py-4 font-semibold text-charcoal">{{ $mk->ar_marker_id }}</td>
                                <td class="px-3 py-4">
                                    @if ($mk->arModel)
                                        <span class="rounded bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary-800">{{ $mk->arModel->name }}</span>
                                    @else
                                        <span class="text-xs italic text-gray-400">Tanpa Model</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4 text-xs font-medium text-gray-600">
                                    @if ($mk->mapLocation)
                                        <div class="flex flex-col">
                                            <span class="font-semibold">{{ $mk->mapLocation->name }}</span>
                                            <span class="text-[9px] uppercase text-gray-400 tracking-wider">
                                                Category: {{ $mk->mapLocation->category }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="italic text-gray-400">Belum dipasang</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4">
                                    <div class="flex items-center gap-2">
                                        <button onclick="triggerMarkerDownload('{{ $mk->ar_marker_id }}', '{{ $mk->mapLocation ? ($mk->mapLocation->locationable->slug ?? '') : '' }}')" class="rounded-lg p-1.5 text-primary hover:bg-primary/10 transition-colors" title="Unduh QR Pattern">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </button>
                                        <button onclick="openMarkerEditModal({{ json_encode($mk) }})" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-charcoal transition-colors" title="Edit Marker">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.ar-manager.markers.destroy', $mk->id) }}" class="delete-form inline" data-confirm="Apakah Anda yakin ingin menghapus marker QR ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-1.5 text-gray-400 hover:bg-warning/10 hover:text-warning transition-colors" title="Hapus Marker">
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
                                <td colspan="4" class="px-3 py-6 text-center text-gray-400 text-sm">Belum ada marker QR ditambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- 3D MODEL DIALOG / MODAL FORM --}}
<x-modal name="model-modal" maxWidth="xl">
    <div class="mb-4">
        <h3 id="model-modal-title" class="font-display text-lg font-bold text-charcoal">Tambah Model 3D</h3>
    </div>
    <form id="model-form" method="POST" action="" enctype="multipart/form-data">
        @csrf
        <div id="model-method-container"></div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Nama Model <span class="text-warning">*</span></label>
                <input type="text" name="name" id="model-field-name" required placeholder="Contoh: Pura Penataran Model" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Deskripsi Model</label>
                <textarea name="description" id="model-field-desc" rows="3" placeholder="Deskripsi detail mengenai model 3D..." class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">File Model 3D (.glb) <span class="text-warning" id="glb-required-asterisk">*</span></label>
                    <span class="block text-[10px] text-gray-400 mb-1">Maksimal 20MB.</span>
                    <input type="file" name="model_3d_file" id="model-field-glb-file" accept=".glb" onchange="previewModelGLB(this)" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
                    
                    <p id="edit-current-glb-container" class="mt-1.5 text-[10px] text-gray-500 hidden">
                        File aktif: <span id="edit-current-glb-path" class="font-mono bg-gray-50 px-1 py-0.5 rounded border border-gray-100"></span>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">File iOS Model (.usdz)</label>
                    <span class="block text-[10px] text-gray-400 mb-1">Maksimal 50MB.</span>
                    <input type="file" name="model_3d_usdz_file" id="model-field-usdz-file" accept=".usdz" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
                    
                    <p id="edit-current-usdz-container" class="mt-1.5 text-[10px] text-gray-500 hidden">
                        File aktif: <span id="edit-current-usdz-path" class="font-mono bg-gray-50 px-1 py-0.5 rounded border border-gray-100"></span>
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Audio Narasi (.mp3)</label>
                <span class="block text-[10px] text-gray-400 mb-1">Maksimal 10MB.</span>
                <input type="file" name="audio_narration_file" id="model-field-audio-file" accept="audio/*" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
                
                <p id="edit-current-audio-container" class="mt-1.5 text-[10px] text-gray-500 hidden">
                    File aktif: <span id="edit-current-audio-path" class="font-mono bg-gray-50 px-1 py-0.5 rounded border border-gray-100 font-semibold text-primary"></span>
                </p>
            </div>

            {{-- 3D Interactive Model Viewer Panel inside modal --}}
            <div class="mt-2.5 border border-dashed border-gray-200 bg-gray-50/50 p-3 rounded-2xl">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Pratinjau Aset Model 3D</span>
                <div class="model-viewer-wrapper flex items-center justify-center">
                    <div id="modal-viewer-placeholder" class="text-center p-4">
                        <span class="text-xs text-gray-400">Pilih atau unggah file GLB untuk melihat model 3D</span>
                    </div>
                    <model-viewer id="modal-viewer-3d" class="hidden" camera-controls auto-rotate shadow-intensity="1"></model-viewer>
                </div>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="closeModelModal()" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
            <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary-600">Simpan Aset</button>
        </div>
    </form>
</x-modal>

{{-- QR MARKER DIALOG / MODAL FORM --}}
<x-modal name="marker-modal" maxWidth="lg">
    <div class="mb-4">
        <h3 id="marker-modal-title" class="font-display text-lg font-bold text-charcoal">Tambah Marker QR</h3>
    </div>
    <form id="marker-form" method="POST" action="">
        @csrf
        <div id="marker-method-container"></div>
        <input type="hidden" name="ar_marker_patt_content" id="marker-field-patt-content">
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Kode ID Marker <span class="text-warning">*</span></label>
                <span class="block text-[10px] text-gray-400 mb-1.5">Harus unik. Digunakan sebagai nama file pola AR dan referensi sistem.</span>
                <input type="text" name="ar_marker_id" id="marker-field-marker-id" required placeholder="Contoh: MARKER_PURA_01" oninput="generateARMarkerFromForm()" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Model 3D Terkait</label>
                <select name="ar_model_id" id="marker-field-model-id" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    <option value="">Tanpa Model 3D (Hanya QR)</option>
                    @foreach ($models as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Penempatan di Titik Peta</label>
                <select name="map_location_id" id="marker-field-location-id" onchange="generateARMarkerFromForm()" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    <option value="">Belum dipasang di peta</option>
                    @foreach ($locations as $loc)
                        <option value="{{ $loc->id }}" data-slug="{{ $loc->locationable->slug ?? '' }}">{{ $loc->name }} ({{ ucfirst($loc->category) }})</option>
                    @endforeach
                </select>
            </div>
            
            {{-- QR Marker Pattern Preview Container inside modal --}}
            <div id="marker-preview-wrapper" class="mt-2.5 border border-dashed border-gray-200 bg-gray-50/50 p-4 rounded-2xl flex flex-col items-center hidden">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 align-self-start">Pola QR Marker AR Hasil Regenerasi</span>
                <div id="marker-canvas-placeholder" class="bg-white border border-gray-100 rounded-lg p-2 shadow-xs">
                    {{-- Generated Canvas goes here --}}
                </div>
                <span class="mt-2 block text-[9px] text-gray-400 text-center">Pola pelacakan (.patt) akan dibuat dan disimpan secara otomatis saat Anda menyimpan marker.</span>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="closeMarkerModal()" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
            <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary-600">Simpan Marker</button>
        </div>
    </form>
</x-modal>

@endsection

@push('scripts')
{{-- Google Model Viewer --}}
<script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>
<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        const ModelViewerElement = customElements.get('model-viewer');
        if (ModelViewerElement) {
            ModelViewerElement.meshoptDecoderLocation = 'https://unpkg.com/meshoptimizer@0.17.0/meshopt_decoder.js';
        }
    });
</script>

{{-- QR Code Generator --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

<script>
    const storageUrl = "{{ asset('storage') }}";

    // ----------------------------------------------------
    // MODEL MODAL & VIEWER SCRIPTS
    // ----------------------------------------------------
    const modelForm = document.getElementById('model-form');
    const modelModalTitle = document.getElementById('model-modal-title');
    const modelMethodContainer = document.getElementById('model-method-container');
    const modalViewer3d = document.getElementById('modal-viewer-3d');
    const modalViewerPlaceholder = document.getElementById('modal-viewer-placeholder');

    function openModelModal() {
        modelModalTitle.innerText = "Tambah Model 3D";
        modelForm.action = "{{ route('admin.ar-manager.models.store') }}";
        modelMethodContainer.innerHTML = "";
        
        document.getElementById('model-field-name').value = "";
        document.getElementById('model-field-desc').value = "";
        document.getElementById('model-field-glb-file').value = "";
        document.getElementById('model-field-glb-file').required = true;
        document.getElementById('glb-required-asterisk').style.display = 'inline';
        document.getElementById('model-field-usdz-file').value = "";
        document.getElementById('model-field-audio-file').value = "";

        document.getElementById('edit-current-glb-container').classList.add('hidden');
        document.getElementById('edit-current-usdz-container').classList.add('hidden');
        document.getElementById('edit-current-audio-container').classList.add('hidden');
        resetModal3DViewer();

        window.dispatchEvent(new CustomEvent('open-model-modal'));
    }

    function openModelEditModal(model) {
        modelModalTitle.innerText = "Edit Model 3D";
        modelForm.action = `/admin/ar-manager/models/${model.id}`;
        modelMethodContainer.innerHTML = `@method('PUT')`;

        document.getElementById('model-field-name').value = model.name;
        document.getElementById('model-field-desc').value = model.description || "";
        document.getElementById('model-field-glb-file').value = "";
        document.getElementById('model-field-glb-file').required = false;
        document.getElementById('glb-required-asterisk').style.display = 'none';
        document.getElementById('model-field-usdz-file').value = "";
        document.getElementById('model-field-audio-file').value = "";

        // Set GLB active file display
        const glbContainer = document.getElementById('edit-current-glb-container');
        const glbPath = document.getElementById('edit-current-glb-path');
        if (model.model_3d_path) {
            glbPath.textContent = model.model_3d_path.split('/').pop();
            glbContainer.classList.remove('hidden');
            setupModal3DViewer(`${storageUrl}/${model.model_3d_path}`);
        } else {
            glbContainer.classList.add('hidden');
            resetModal3DViewer();
        }

        // Set USDZ active file display
        const usdzContainer = document.getElementById('edit-current-usdz-container');
        const usdzPath = document.getElementById('edit-current-usdz-path');
        if (model.model_3d_usdz_path) {
            usdzPath.textContent = model.model_3d_usdz_path.split('/').pop();
            usdzContainer.classList.remove('hidden');
        } else {
            usdzContainer.classList.add('hidden');
        }

        // Set Audio active file display
        const audioContainer = document.getElementById('edit-current-audio-container');
        const audioPath = document.getElementById('edit-current-audio-path');
        if (model.audio_narration_path) {
            audioPath.textContent = model.audio_narration_path.split('/').pop();
            audioContainer.classList.remove('hidden');
        } else {
            audioContainer.classList.add('hidden');
        }

        window.dispatchEvent(new CustomEvent('open-model-modal'));
    }

    function closeModelModal() {
        window.dispatchEvent(new CustomEvent('close-model-modal'));
    }

    function previewModelGLB(input) {
        const file = input.files[0];
        if (file) {
            const blobUrl = URL.createObjectURL(file);
            setupModal3DViewer(blobUrl);
        }
    }

    function setupModal3DViewer(src) {
        if (modalViewerPlaceholder) modalViewerPlaceholder.classList.add('hidden');
        if (modalViewer3d) {
            modalViewer3d.classList.remove('hidden');
            modalViewer3d.src = src;
        }
    }

    function resetModal3DViewer() {
        if (modalViewer3d) {
            modalViewer3d.classList.add('hidden');
            modalViewer3d.src = "";
        }
        if (modalViewerPlaceholder) modalViewerPlaceholder.classList.remove('hidden');
    }

    // ----------------------------------------------------
    // MARKER MODAL & GENERATOR SCRIPTS
    // ----------------------------------------------------
    const markerForm = document.getElementById('marker-form');
    const markerModalTitle = document.getElementById('marker-modal-title');
    const markerMethodContainer = document.getElementById('marker-method-container');

    function openMarkerModal() {
        markerModalTitle.innerText = "Tambah Marker QR";
        markerForm.action = "{{ route('admin.ar-manager.markers.store') }}";
        markerMethodContainer.innerHTML = "";

        document.getElementById('marker-field-marker-id').value = "";
        document.getElementById('marker-field-model-id').value = "";
        document.getElementById('marker-field-location-id').value = "";
        document.getElementById('marker-field-patt-content').value = "";
        
        document.getElementById('marker-preview-wrapper').classList.add('hidden');

        window.dispatchEvent(new CustomEvent('open-marker-modal'));
    }

    function openMarkerEditModal(marker) {
        markerModalTitle.innerText = "Edit Marker QR";
        markerForm.action = `/admin/ar-manager/markers/${marker.id}`;
        markerMethodContainer.innerHTML = `@method('PUT')`;

        document.getElementById('marker-field-marker-id').value = marker.ar_marker_id;
        document.getElementById('marker-field-model-id').value = marker.ar_model_id || "";
        document.getElementById('marker-field-location-id').value = marker.map_location_id || "";
        document.getElementById('marker-field-patt-content').value = "";

        setTimeout(generateARMarkerFromForm, 100);

        window.dispatchEvent(new CustomEvent('open-marker-modal'));
    }

    function closeMarkerModal() {
        window.dispatchEvent(new CustomEvent('close-marker-modal'));
    }

    // ----------------------------------------------------
    // AR PATTERN & QR MARKER DYNAMIC BUILDER
    // ----------------------------------------------------
    let currentMarkerCanvas = null;

    function generateARMarkerFromForm() {
        const markerInput = document.getElementById('marker-field-marker-id');
        const pattInput = document.getElementById('marker-field-patt-content');
        const locationSelect = document.getElementById('marker-field-location-id');
        const previewWrapper = document.getElementById('marker-preview-wrapper');
        const canvasPlaceholder = document.getElementById('marker-canvas-placeholder');

        if (!markerInput) return;

        const markerId = markerInput.value.trim();
        if (!markerId) {
            if (previewWrapper) previewWrapper.classList.add('hidden');
            if (pattInput) pattInput.value = '';
            return;
        }

        if (previewWrapper) previewWrapper.classList.remove('hidden');

        try {
            // Find location slug
            let slug = '';
            if (locationSelect && locationSelect.selectedIndex > 0) {
                const selectedOpt = locationSelect.options[locationSelect.selectedIndex];
                slug = selectedOpt.getAttribute('data-slug') || '';
            }

            // Build dynamic tourist destination URL or fallback to scan query
            const qrValue = slug 
                ? `${window.location.origin}/cultural/${slug}` 
                : `${window.location.origin}/explore?marker=${encodeURIComponent(markerId)}`;

            // Render QR using QRious
            const qr = new QRious({
                value: qrValue,
                size: 250,
                level: 'H'
            });

            // Create high-resolution AR.js canvas
            const markerCanvas = document.createElement('canvas');
            markerCanvas.width = 400;
            markerCanvas.height = 400;
            const ctx = markerCanvas.getContext('2d');

            // Solid Black border
            ctx.fillStyle = '#000000';
            ctx.fillRect(0, 0, 400, 400);

            // White background inside border
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(80, 80, 240, 240);

            // Centered QR Code
            ctx.drawImage(qr.canvas, 80, 80, 240, 240);

            currentMarkerCanvas = markerCanvas;

            // Display in modal placeholder
            canvasPlaceholder.innerHTML = '';
            const displayImg = document.createElement('img');
            displayImg.src = markerCanvas.toDataURL('image/png');
            displayImg.className = 'w-40 h-40 object-contain';
            canvasPlaceholder.appendChild(displayImg);

            // Set initial fallback pattern (plain QR)
            const fallbackPattText = generatePattText(markerCanvas, 80, 240);
            if (pattInput) {
                pattInput.value = fallbackPattText;
            }

            // Load and render brand logo in center
            const logo = new Image();
            logo.onload = function() {
                // White background overlay for logo
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(170, 170, 60, 60);

                // Render logo
                ctx.drawImage(logo, 174, 174, 52, 52);

                currentMarkerCanvas = markerCanvas;

                // Update display image
                displayImg.src = markerCanvas.toDataURL('image/png');

                // Regenerate pattern including the logo
                const pattText = generatePattText(markerCanvas, 80, 240);
                if (pattInput) {
                    pattInput.value = pattText;
                }
            };
            logo.src = '/icons/logo-color-notext.png';

        } catch (e) {
            console.error('AR Marker generation failed:', e);
        }
    }

    // ----------------------------------------------------
    // EXPORT DOWNLOAD SYSTEM
    // ----------------------------------------------------
    function triggerMarkerDownload(markerId, slug) {
        try {
            // Build dynamic tourist destination URL or fallback to scan query
            const qrValue = slug 
                ? `${window.location.origin}/cultural/${slug}` 
                : `${window.location.origin}/explore?marker=${encodeURIComponent(markerId)}`;

            const qr = new QRious({
                value: qrValue,
                size: 300,
                level: 'H'
            });

            const markerCanvas = document.createElement('canvas');
            markerCanvas.width = 500;
            markerCanvas.height = 500;
            const ctx = markerCanvas.getContext('2d');

            ctx.fillStyle = '#000000';
            ctx.fillRect(0, 0, 500, 500);

            ctx.fillStyle = '#ffffff';
            ctx.fillRect(100, 100, 300, 300);

            ctx.drawImage(qr.canvas, 100, 100, 300, 300);

            const logo = new Image();
            logo.onload = function() {
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(212, 212, 76, 76);
                ctx.drawImage(logo, 217, 217, 66, 66);

                const pngUrl = markerCanvas.toDataURL('image/png');
                const pngLink = document.createElement('a');
                pngLink.href = pngUrl;
                pngLink.download = `${markerId}.png`;
                document.body.appendChild(pngLink);
                pngLink.click();
                document.body.removeChild(pngLink);
            };
            logo.src = '/icons/logo-color-notext.png';

        } catch (e) {
            console.error('Marker download failed:', e);
        }
    }

    // Helper to generate matrix mapping patterns for AR.js
    function generatePattText(canvas, borderWidth, patternSize) {
        const ctx = canvas.getContext('2d');
        const gridSize = 16;
        const cellW = patternSize / gridSize;
        const cellH = patternSize / gridSize;
        
        const grid = [];
        for (let r = 0; r < gridSize; r++) {
            grid[r] = [];
            for (let c = 0; c < gridSize; c++) {
                const startX = borderWidth + c * cellW;
                const startY = borderWidth + r * cellH;
                
                const imgData = ctx.getImageData(startX, startY, cellW, cellH);
                const data = imgData.data;
                let sumR = 0, sumG = 0, sumB = 0;
                const count = data.length / 4;
                
                for (let i = 0; i < data.length; i += 4) {
                    sumR += data[i];
                    sumG += data[i + 1];
                    sumB += data[i + 2];
                }
                
                const normR = (sumR / count / 255).toFixed(3);
                const normG = (sumG / count / 255).toFixed(3);
                const normB = (sumB / count / 255).toFixed(3);
                
                grid[r][c] = `${normR} ${normG} ${normB}`;
            }
        }
        
        const rotations = [];
        
        function rotate90(arr) {
            const n = arr.length;
            const rotated = Array.from({ length: n }, () => []);
            for (let r = 0; r < n; r++) {
                for (let c = 0; c < n; c++) {
                    rotated[c][n - 1 - r] = arr[r][c];
                }
            }
            return rotated;
        }
        
        let currentGrid = grid;
        for (let i = 0; i < 4; i++) {
            const blockLines = [];
            for (let r = 0; r < gridSize; r++) {
                blockLines.push(currentGrid[r].join(' '));
            }
            rotations.push(blockLines.join('\n'));
            currentGrid = rotate90(currentGrid);
        }
        
        return rotations.join('\n\n') + '\n';
    }
</script>
@endpush
