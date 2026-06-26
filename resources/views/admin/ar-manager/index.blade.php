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

        .tus-progress-container.tus-error .tus-progress-bar {
            @apply bg-red-500;
        }
        .tus-progress-container.tus-complete .tus-progress-bar {
            @apply bg-green-500;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div id="tour-header">
            <h1 class="font-display text-charcoal text-2xl font-bold">Manajer Aset AR &amp; Marker</h1>
            <p class="mt-0.5 text-sm font-medium text-gray-500">Kelola model 3D interaktif dan marker QR untuk teknologi
                Augmented Reality desa.</p>
        </div>
        <div class="flex items-center gap-2">
            <button id="tour-trigger-btn" onclick="startTutorial()"
                class="hover:bg-gray-100 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all active:scale-[0.98]"
                title="Panduan Interaktif">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
            <button id="tour-add-btn" onclick="openModelModal()"
                class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Model 3D
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($models as $m)
            <div @if($loop->first) id="tour-first-card" @endif
                class="shadow-2xs hover:shadow-xs flex flex-col rounded-xl border border-gray-100 bg-white p-4 transition-shadow">
                <div @if($loop->first) id="tour-viewer-wrapper" @endif class="model-viewer-wrapper mb-3">
                    @if ($m->thumbnail_path)
                        <img src="{{ asset('storage/' . $m->thumbnail_path) }}"
                             alt="{{ $m->name }}"
                             class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center p-4 text-center">
                            <span class="text-xs text-gray-400">Tidak ada pratinjau</span>
                        </div>
                    @endif
                </div>
                <h3 class="text-charcoal truncate text-sm font-bold">{{ $m->name }}</h3>
                <div class="relative mt-2 max-h-16 flex-1 overflow-hidden text-xs text-gray-500">
                    {!! $m->description ? $m->description : 'Tidak ada deskripsi.' !!}
                    <div
                        class="bg-linear-to-t pointer-events-none absolute bottom-0 left-0 right-0 h-12 from-white to-transparent">
                    </div>
                </div>

                @if ($m->ar_marker_id)
                    <div @if($loop->first) id="tour-marker-download" @endif class="mt-3 flex items-center gap-1.5">
                        <span
                            class="bg-primary/10 text-primary max-w-40 truncate rounded-full px-2 py-0.5 font-mono text-[10px] font-bold">{{ $m->ar_marker_id }}</span>
                        <button type="button"
                            onclick="triggerMarkerDownload('{{ $m->ar_marker_id }}')"
                            class="text-primary hover:bg-primary/10 ml-auto shrink-0 rounded-lg p-1.5 transition-colors"
                            title="Unduh QR Marker">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </button>
                    </div>
                @else
                    <div @if($loop->first) id="tour-marker-download" @endif class="mt-3">
                        <span class="text-[10px] italic text-gray-400">Belum ada marker QR</span>
                    </div>
                @endif

                @if ($m->mapLocation)
                    <span class="mt-1 truncate text-[10px] font-medium text-gray-500">📍 {{ $m->mapLocation->name }}</span>
                @endif

                <div @if($loop->first) id="tour-actions" @endif class="mt-3 flex items-center justify-end gap-1 border-t border-gray-50 pt-2">
                    <button onclick="openModelEditModal({{ json_encode([
                        'id' => $m->id,
                        'name' => $m->getTranslations('name'),
                        'description' => $m->getTranslations('description'),
                        'ar_marker_id' => $m->ar_marker_id,
                        'model_3d_path' => $m->model_3d_path,
                        'model_3d_usdz_path' => $m->model_3d_usdz_path,
                        'audio_narration_path' => $m->audio_narration_path,
                        'map_location' => $m->mapLocation ? [
                            'name' => $m->mapLocation->name,
                            'locationable' => $m->mapLocation->locationable ? [
                                'slug' => $m->mapLocation->locationable->slug
                            ] : null
                        ] : null
                    ]) }})"
                        class="hover:text-primary p-1 text-gray-400 transition-colors" title="Edit Model">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <form method="POST" action="{{ route('admin.ar-manager.models.destroy', $m->id) }}"
                        class="delete-form inline" data-confirm="{{ __('Apakah Anda yakin ingin menghapus model ini?') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="hover:text-warning p-1 text-gray-400 transition-colors"
                            title="Hapus Model">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div id="tour-empty-state" class="col-span-3 py-12 text-center text-sm text-gray-400">Belum ada model 3D ditambahkan.</div>
        @endforelse
    </div>

    @include('admin.ar-manager.partials.modal-form')

@endsection

@push('scripts')
    <x-tiptap-editor-script />

    {{-- Google Model Viewer --}}
    <script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>
    <script type="module">
        document.addEventListener('DOMContentLoaded', () => {
            const ModelViewerElement = customElements.get('model-viewer');
            if (ModelViewerElement) {
                ModelViewerElement.meshoptDecoderLocation =
                    'https://unpkg.com/meshoptimizer@0.17.0/meshopt_decoder.js';
            }
        });
    </script>

    {{-- QR Code Generator --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

    <script>
        const storageUrl = "{{ asset('storage') }}";
        let currentModalMarkerCanvas = null;
        let pendingThumbnailData = null;

        // ----------------------------------------------------
        // THUMBNAIL CAPTURE
        // ----------------------------------------------------
        function captureModelThumbnail() {
            const viewer = document.getElementById('modal-viewer-3d');
            if (!viewer || viewer.classList.contains('hidden') || !viewer.src) return;
            try {
                const canvas = viewer.shadowRoot?.querySelector('canvas');
                if (canvas) {
                    pendingThumbnailData = canvas.toDataURL('image/png');
                }
            } catch(e) {
                console.warn('Thumbnail capture failed:', e);
            }
        }

        function hookModelViewerLoad(viewerEl) {
            if (!viewerEl) return;
            viewerEl.addEventListener('load', () => {
                setTimeout(captureModelThumbnail, 500);
            }, { once: true });
        }

        // Intercept form submit — inject thumbnail data
        document.addEventListener('DOMContentLoaded', () => {
            modelForm?.addEventListener('submit', function() {
                if (pendingThumbnailData) {
                    let input = document.getElementById('thumbnail-data-input');
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'thumbnail_data';
                        input.id = 'thumbnail-data-input';
                        this.appendChild(input);
                    }
                    input.value = pendingThumbnailData;
                }
            });
        });

        // ----------------------------------------------------
        // MODEL MODAL
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
            document.getElementById('model-field-id').value = "";

            document.getElementById('model-field-name-en').value = "";
            document.getElementById('model-field-name-id').value = "";
            document.getElementById('model-field-marker-id').value = "";
            document.getElementById('model-field-patt-content').value = "";
            if (typeof window.clearAllTiptapEditors === 'function') {
                window.clearAllTiptapEditors(modelForm);
            }
            document.getElementById('model-field-glb-file').value = "";
            document.getElementById('glb-required-asterisk').style.display = 'inline';
            document.getElementById('model-field-usdz-file').value = "";
            document.getElementById('model-field-audio-file').value = "";
            document.getElementById('edit-current-glb-container').classList.add('hidden');
            document.getElementById('edit-current-usdz-container').classList.add('hidden');
            document.getElementById('edit-current-audio-container').classList.add('hidden');
            document.getElementById('model-field-tmp-glb').value = '';
            document.getElementById('model-field-tmp-usdz').value = '';
            document.getElementById('model-field-tmp-audio').value = '';
            document.getElementById('modal-marker-preview-wrapper').classList.add('hidden');
            currentModalMarkerCanvas = null;
            resetModal3DViewer();

            window.dispatchEvent(new CustomEvent('open-model-modal'));
        }

        function openModelEditModal(model) {
            modelModalTitle.innerText = "Edit Model 3D";
            modelForm.action = `/admin/ar-manager/models/${model.id}`;
            modelMethodContainer.innerHTML = `@method('PUT')`;
            document.getElementById('model-field-id').value = model.id;

            const nameEn = (typeof model.name === 'object') ? (model.name?.en || "") : model.name;
            const nameId = (typeof model.name === 'object') ? (model.name?.id || "") : model.name;
            document.getElementById('model-field-name-en').value = nameEn;
            document.getElementById('model-field-name-id').value = nameId;

            document.getElementById('model-field-marker-id').value = model.ar_marker_id || "";
            document.getElementById('model-field-patt-content').value = "";
            document.getElementById('model-field-tmp-glb').value = '';
            document.getElementById('model-field-tmp-usdz').value = '';
            document.getElementById('model-field-tmp-audio').value = '';

            const descEn = (typeof model.description === 'object') ? (model.description?.en || "") : (model.description || "");
            const descId = (typeof model.description === 'object') ? (model.description?.id || "") : (model.description || "");
            if (typeof window.setTiptapContent === 'function') {
                window.setTiptapContent('#model-field-desc-en-textarea', descEn);
                window.setTiptapContent('#model-field-desc-id-textarea', descId);
            }

            document.getElementById('model-field-glb-file').value = "";
            document.getElementById('model-field-glb-file').required = false;
            document.getElementById('glb-required-asterisk').style.display = 'none';
            document.getElementById('model-field-usdz-file').value = "";
            document.getElementById('model-field-audio-file').value = "";

            const glbContainer = document.getElementById('edit-current-glb-container');
            if (model.model_3d_path) {
                document.getElementById('edit-current-glb-path').textContent = model.model_3d_path.split('/').pop();
                glbContainer.classList.remove('hidden');
                setupModal3DViewer(`${storageUrl}/${model.model_3d_path}`);
            } else {
                glbContainer.classList.add('hidden');
                resetModal3DViewer();
            }

            const usdzContainer = document.getElementById('edit-current-usdz-container');
            if (model.model_3d_usdz_path) {
                document.getElementById('edit-current-usdz-path').textContent = model.model_3d_usdz_path.split('/').pop();
                usdzContainer.classList.remove('hidden');
            } else {
                usdzContainer.classList.add('hidden');
            }

            const audioContainer = document.getElementById('edit-current-audio-container');
            if (model.audio_narration_path) {
                document.getElementById('edit-current-audio-path').textContent = model.audio_narration_path.split('/')
                    .pop();
                audioContainer.classList.remove('hidden');
            } else {
                audioContainer.classList.add('hidden');
            }

            // Regenerate marker preview if marker ID exists
            if (model.ar_marker_id) {
                setTimeout(generateARMarkerInModal, 50);
            } else {
                document.getElementById('modal-marker-preview-wrapper').classList.add('hidden');
                currentModalMarkerCanvas = null;
            }

            window.dispatchEvent(new CustomEvent('open-model-modal'));
        }

        function closeModelModal() {
            window.dispatchEvent(new CustomEvent('close-model-modal'));
        }

        // ----------------------------------------------------
        // Chunked upload initialization
        // ----------------------------------------------------
        document.addEventListener('DOMContentLoaded', () => {
            function initChunkedUpload(inputId, hiddenId, progressId, maxSize, exts) {
                const input = document.getElementById(inputId);
                if (!input) return;
                new ChunkedUploader({
                    input: input,
                    hiddenInput: document.getElementById(hiddenId),
                    progressContainer: document.getElementById(progressId),
                    maxSize: maxSize,
                    allowedExtensions: exts,
                    endpoint: '/admin/api/tus/upload',
                    onStart: () => {
                        const fileInput = document.getElementById(inputId);
                        if (inputId === 'model-field-glb-file' && fileInput?.files[0]) {
                            setupModal3DViewer(URL.createObjectURL(fileInput.files[0]));
                        }
                    },
                });
            }

            // GLB
            initChunkedUpload('model-field-glb-file', 'model-field-tmp-glb', 'model-glb-progress', 20 * 1024 * 1024, ['.glb']);
            // USDZ
            initChunkedUpload('model-field-usdz-file', 'model-field-tmp-usdz', 'model-usdz-progress', 50 * 1024 * 1024, ['.usdz']);
            // Audio
            initChunkedUpload('model-field-audio-file', 'model-field-tmp-audio', 'model-audio-progress', 10 * 1024 * 1024, ['.mp3', '.ogg', '.wav']);
        });

        function setupModal3DViewer(src) {
            if (modalViewerPlaceholder) modalViewerPlaceholder.classList.add('hidden');
            if (modalViewer3d) {
                modalViewer3d.classList.remove('hidden');
                modalViewer3d.src = src;
                hookModelViewerLoad(modalViewer3d);
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
        // AR MARKER GENERATOR (inside model modal)
        // ----------------------------------------------------
        function generateARMarkerInModal() {
            const markerInput = document.getElementById('model-field-marker-id');
            const pattInput = document.getElementById('model-field-patt-content');
            const previewWrapper = document.getElementById('modal-marker-preview-wrapper');
            const canvasPlaceholder = document.getElementById('modal-marker-canvas-placeholder');

            if (!markerInput) return;
            const markerId = markerInput.value.trim();

            if (!markerId) {
                previewWrapper.classList.add('hidden');
                pattInput.value = '';
                currentModalMarkerCanvas = null;
                return;
            }

            previewWrapper.classList.remove('hidden');

            try {
                const qrValue = `${window.location.origin}/ar/scan/${encodeURIComponent(markerId)}`;

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

                currentModalMarkerCanvas = markerCanvas;

                canvasPlaceholder.innerHTML = '';
                const img = document.createElement('img');
                img.src = markerCanvas.toDataURL('image/png');
                img.className = 'w-24 h-24 object-contain';
                canvasPlaceholder.appendChild(img);

                pattInput.value = generatePattText(markerCanvas, 100, 300);

                const logo = new Image();
                logo.onload = function() {
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(212, 212, 76, 76);
                    ctx.drawImage(logo, 217, 217, 66, 66);
                    currentModalMarkerCanvas = markerCanvas;
                    img.src = markerCanvas.toDataURL('image/png');
                    pattInput.value = generatePattText(markerCanvas, 100, 300);
                };
                logo.src = '/icons/logo-color-notext.png';
            } catch (e) {
                console.error('AR Marker generation failed:', e);
            }
        }

        function downloadARMarkerFromModal() {
            const markerInput = document.getElementById('model-field-marker-id');
            if (!markerInput || !currentModalMarkerCanvas) return;
            const markerId = markerInput.value.trim();
            const form = document.getElementById('model-form');
            const link = document.createElement('a');
            link.href = currentModalMarkerCanvas.toDataURL('image/png');
            link.download = `${markerId}.png`;
            form.appendChild(link);
            link.click();
            form.removeChild(link);
        }

        // ----------------------------------------------------
        // DOWNLOAD QR FOR EXISTING MARKER (from card)
        // ----------------------------------------------------
        function triggerMarkerDownload(markerId) {
            try {
                const qrValue = `${window.location.origin}/ar/scan/${encodeURIComponent(markerId)}`;

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

                    const link = document.createElement('a');
                    link.href = markerCanvas.toDataURL('image/png');
                    link.download = `${markerId}.png`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                };
                logo.src = '/icons/logo-color-notext.png';
            } catch (e) {
                console.error('Marker download failed:', e);
            }
        }

        // ----------------------------------------------------
        // AR.js PATT GENERATOR
        // ----------------------------------------------------
        function generatePattText(canvas, borderWidth, patternSize) {
            const ctx = canvas.getContext('2d');
            const gridSize = 16;
            const cellW = patternSize / gridSize;
            const cellH = patternSize / gridSize;

            const grid = [];
            for (let r = 0; r < gridSize; r++) {
                grid[r] = [];
                for (let c = 0; c < gridSize; c++) {
                    const imgData = ctx.getImageData(borderWidth + c * cellW, borderWidth + r * cellH, cellW, cellH);
                    const data = imgData.data;
                    let sumR = 0,
                        sumG = 0,
                        sumB = 0;
                    const count = data.length / 4;
                    for (let i = 0; i < data.length; i += 4) {
                        sumR += data[i];
                        sumG += data[i + 1];
                        sumB += data[i + 2];
                    }
                    grid[r][c] =
                        `${(sumR/count/255).toFixed(3)} ${(sumG/count/255).toFixed(3)} ${(sumB/count/255).toFixed(3)}`;
                }
            }

            function rotate90(arr) {
                const n = arr.length;
                const rotated = Array.from({
                    length: n
                }, () => []);
                for (let r = 0; r < n; r++)
                    for (let c = 0; c < n; c++)
                        rotated[c][n - 1 - r] = arr[r][c];
                return rotated;
            }

            const rotations = [];
            let cur = grid;
            for (let i = 0; i < 4; i++) {
                rotations.push(cur.map(row => row.join(' ')).join('\n'));
                cur = rotate90(cur);
            }
            return rotations.join('\n\n') + '\n';
        }
    </script>

    <script>
        function startTutorial() {
            const driver = window.driver.js.driver;
            const hasCard = document.getElementById('tour-first-card') !== null;
            const steps = [];

            // Langkah 1: Pengantar
            steps.push({
                element: '#tour-header',
                popover: {
                    title: '👋 Selamat Datang!',
                    description: 'Panduan ini akan menunjukkan cara mengelola model 3D dan marker Augmented Reality (AR) di desa wisata.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 2: Tombol Tambah Model
            steps.push({
                element: '#tour-add-btn',
                popover: {
                    title: '➕ Tambah Model 3D Baru',
                    description: 'Gunakan tombol ini untuk mengunggah model 3D baru (.glb/.usdz), mengatur ID marker AR, dan melampirkan audio narasi.',
                    side: 'bottom',
                    align: 'end'
                }
            });

            if (hasCard) {
                // Langkah 3: Kartu Model 3D
                steps.push({
                    element: '#tour-first-card',
                    popover: {
                        title: '📦 Kartu Model 3D',
                        description: 'Setiap aset 3D yang diunggah akan tampil di dalam kartu ini beserta detail lokasi dan pratinjaunya.',
                        side: 'top',
                        align: 'start'
                    }
                });

                // Langkah 4: Viewer 3D
                steps.push({
                    element: '#tour-viewer-wrapper',
                    popover: {
                        title: '🔄 Viewer 3D Interaktif',
                        description: 'Anda dan pengunjung dapat memutar, memperbesar, dan berinteraksi dengan model 3D langsung di browser.',
                        side: 'top',
                        align: 'start'
                    }
                });

                // Langkah 5: Marker & Download
                steps.push({
                    element: '#tour-marker-download',
                    popover: {
                        title: '📥 Unduh QR Marker AR',
                        description: 'Di sini tertera ID Marker. Klik ikon unduh untuk mendapatkan gambar kode QR fisik untuk dipasang di lokasi wisata asli.',
                        side: 'top',
                        align: 'end'
                    }
                });

                // Langkah 6: Edit & Hapus
                steps.push({
                    element: '#tour-actions',
                    popover: {
                        title: '⚙️ Aksi Cepat',
                        description: 'Gunakan tombol ini untuk mengubah informasi model atau menghapusnya jika sudah tidak digunakan.',
                        side: 'top',
                        align: 'end'
                    }
                });
            } else {
                // Langkah Alternatif jika kosong
                steps.push({
                    element: '#tour-empty-state',
                    popover: {
                        title: '📭 Belum Ada Data',
                        description: 'Setelah Anda menambahkan model 3D pertama, kartu visual aset akan muncul di area galeri ini.',
                        side: 'top',
                        align: 'start'
                    }
                });
            }

            const driverObj = driver({
                showProgress: true,
                allowClose: true,
                steps: steps,
                popoverClass: 'driverjs-theme'
            });

            driverObj.drive();
        }

        // Auto-run for first-time visitors
        document.addEventListener('DOMContentLoaded', () => {
            const tourCompleted = localStorage.getItem('ar_manager_tour_completed');
            if (!tourCompleted) {
                // Delay slightly to allow tipping, model-viewer and page transitions to settle
                setTimeout(() => {
                    startTutorial();
                    localStorage.setItem('ar_manager_tour_completed', 'true');
                }, 1000);
            }
        });
    </script>



    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.dispatchEvent(new CustomEvent('open-model-modal'));
            @if(old('_method') == 'PUT')
                modelForm.action = "/admin/ar-manager/models/{{ old('model_id') }}";
                modelMethodContainer.innerHTML = `@method('PUT')`;
                modelModalTitle.innerText = "Edit Model 3D";
                document.getElementById('model-field-id').value = "{{ old('model_id') }}";
            @else
                modelForm.action = "{{ route('admin.ar-manager.models.store') }}";
                modelMethodContainer.innerHTML = "";
                modelModalTitle.innerText = "Tambah Model 3D";
                document.getElementById('model-field-id').value = "";
            @endif
            document.getElementById('model-field-name-en').value = @json(old('name.en', ''));
            document.getElementById('model-field-name-id').value = @json(old('name.id', ''));
            document.getElementById('model-field-marker-id').value = @json(old('ar_marker_id', ''));

            const oldDescEn = @json(old('description.en', ''));
            const oldDescId = @json(old('description.id', ''));
            if (typeof window.setTiptapContent === 'function') {
                window.setTiptapContent('#model-field-desc-en-textarea', oldDescEn);
                window.setTiptapContent('#model-field-desc-id-textarea', oldDescId);
            }
        });
    </script>
    @endif
@endpush
