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
    </style>
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-charcoal text-2xl font-bold">Manajer Aset AR &amp; Marker</h1>
            <p class="mt-0.5 text-sm font-medium text-gray-500">Kelola model 3D interaktif dan marker QR untuk teknologi
                Augmented Reality desa.</p>
        </div>
        <button onclick="openModelModal()"
            class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Model 3D
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($models as $m)
            <div
                class="shadow-2xs hover:shadow-xs flex flex-col rounded-xl border border-gray-100 bg-white p-4 transition-shadow">
                <div class="model-viewer-wrapper mb-3">
                    <model-viewer src="{{ asset('storage/' . $m->model_3d_path) }}" camera-controls auto-rotate
                        shadow-intensity="1">
                    </model-viewer>
                </div>
                <h3 class="text-charcoal truncate text-sm font-bold">{{ $m->name }}</h3>
                <div class="relative mt-2 max-h-16 flex-1 overflow-hidden text-xs text-gray-500">
                    {!! $m->description ? $m->description : 'Tidak ada deskripsi.' !!}
                    <div
                        class="bg-linear-to-t pointer-events-none absolute bottom-0 left-0 right-0 h-12 from-white to-transparent">
                    </div>
                </div>

                @if ($m->ar_marker_id)
                    <div class="mt-3 flex items-center gap-1.5">
                        <span
                            class="bg-primary/10 text-primary max-w-40 truncate rounded-full px-2 py-0.5 font-mono text-[10px] font-bold">{{ $m->ar_marker_id }}</span>
                        <button type="button"
                            onclick="triggerMarkerDownload('{{ $m->ar_marker_id }}', '{{ $m->mapLocation?->locationable?->slug ?? '' }}')"
                            class="text-primary hover:bg-primary/10 ml-auto shrink-0 rounded-lg p-1.5 transition-colors"
                            title="Unduh QR Marker">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </button>
                    </div>
                @else
                    <span class="mt-3 text-[10px] italic text-gray-400">Belum ada marker QR</span>
                @endif

                @if ($m->mapLocation)
                    <span class="mt-1 truncate text-[10px] font-medium text-gray-500">📍 {{ $m->mapLocation->name }}</span>
                @endif

                <div class="mt-3 flex items-center justify-end gap-1 border-t border-gray-50 pt-2">
                    <button onclick="openModelEditModal({{ json_encode($m) }})"
                        class="hover:text-primary p-1 text-gray-400 transition-colors" title="Edit Model">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <form method="POST" action="{{ route('admin.ar-manager.models.destroy', $m->id) }}"
                        class="delete-form inline" data-confirm="Apakah Anda yakin ingin menghapus model ini?">
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
            <div class="col-span-3 py-12 text-center text-sm text-gray-400">Belum ada model 3D ditambahkan.</div>
        @endforelse
    </div>

    {{-- 3D MODEL DIALOG / MODAL FORM --}}
    <x-modal name="model-modal" maxWidth="xl" desktopLayout="drawer">
        <div class="mb-4">
            <h3 id="model-modal-title" class="font-display text-charcoal text-lg font-bold">Tambah Model 3D</h3>
        </div>
        <form id="model-form" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div id="model-method-container"></div>
            <input type="hidden" name="ar_marker_patt_content" id="model-field-patt-content">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama Model <span
                            class="text-warning">*</span></label>
                    <input type="text" name="name" id="model-field-name" required
                        placeholder="Contoh: Pura Penataran Model"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">ID Marker QR AR</label>
                    <span class="mb-1.5 block text-[10px] text-gray-400">Opsional. Harus unik. Digunakan sebagai kode
                        referensi marker fisik.</span>
                    <input type="text" name="ar_marker_id" id="model-field-marker-id"
                        placeholder="Contoh: MARKER_PURA_01" oninput="generateARMarkerInModal()"
                        class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">

                    {{-- Preview + Download --}}
                    <div id="modal-marker-preview-wrapper" class="mt-2 hidden">
                        <div
                            class="flex items-center gap-3 rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-3">
                            <div id="modal-marker-canvas-placeholder"
                                class="shadow-xs shrink-0 rounded-lg border border-gray-100 bg-white p-1"></div>
                            <div class="flex min-w-0 flex-col gap-1.5">
                                <span class="text-[10px] text-gray-500">Pola .patt akan disimpan otomatis ke server.</span>
                                <button type="button" onclick="downloadARMarkerFromModal()"
                                    class="border-primary text-primary hover:bg-primary/5 flex items-center justify-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Unduh QR (.png)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Deskripsi Model</label>
                    <div id="model-desc-toolbar"
                        class="mt-1 flex flex-wrap gap-1 rounded-t-xl border border-b-0 border-gray-200 bg-gray-50 p-1.5 text-gray-600">
                        <button type="button" data-action="bold"
                            class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                            title="Bold">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 12h8a4 4 0 100-8H6v8zm0 0h10a4 4 0 110 8H6v-8z"></path>
                            </svg>
                        </button>
                        <button type="button" data-action="italic"
                            class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                            title="Italic">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10 4h4M12 4v16M10 20h4M15 4L9 20"></path>
                            </svg>
                        </button>
                        <span class="mx-1 my-auto h-5 w-px bg-gray-200"></span>
                        <button type="button" data-action="bulletList"
                            class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                            title="Bullet List">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 6h16M4 12h16M4 18h16M8 6h.01M8 12h.01M8 18h.01"></path>
                            </svg>
                        </button>
                        <button type="button" data-action="orderedList"
                            class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                            title="Ordered List">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5h11M9 12h11M9 19h11M4 4h2v4H4V4zm0 6h2a1 1 0 011 1v1a1 1 0 01-1 1H4v-1h2v-1H4v-1zm0 6h2v3H4v-3z">
                                </path>
                            </svg>
                        </button>
                        <span class="mx-1 my-auto h-5 w-px bg-gray-200"></span>
                        <button type="button" data-action="undo"
                            class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                            title="Undo">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                            </svg>
                        </button>
                        <button type="button" data-action="redo"
                            class="flex items-center justify-center rounded-lg border border-transparent p-1.5 transition-colors hover:bg-gray-200/70 hover:text-gray-900"
                            title="Redo">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 10H11a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="model-desc-editor"
                        class="focus-within:border-primary focus-within:ring-primary/20 max-h-50 min-h-25 w-full overflow-y-auto rounded-b-xl border border-gray-200 bg-white p-4 text-sm focus-within:ring-1">
                    </div>
                    <textarea name="description" id="model-field-desc" class="hidden"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">File Model 3D (.glb) <span
                                class="text-warning" id="glb-required-asterisk">*</span></label>
                        <span class="mb-1 block text-[10px] text-gray-400">Maksimal 20MB.</span>
                        <input type="file" name="model_3d_file" id="model-field-glb-file" accept=".glb"
                            onchange="previewModelGLB(this)"
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">
                        <p id="edit-current-glb-container" class="mt-1.5 hidden text-[10px] text-gray-500">
                            File aktif: <span id="edit-current-glb-path"
                                class="rounded border border-gray-100 bg-gray-50 px-1 py-0.5 font-mono"></span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">File iOS Model (.usdz)</label>
                        <span class="mb-1 block text-[10px] text-gray-400">Maksimal 50MB.</span>
                        <input type="file" name="model_3d_usdz_file" id="model-field-usdz-file" accept=".usdz"
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">
                        <p id="edit-current-usdz-container" class="mt-1.5 hidden text-[10px] text-gray-500">
                            File aktif: <span id="edit-current-usdz-path"
                                class="rounded border border-gray-100 bg-gray-50 px-1 py-0.5 font-mono"></span>
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Audio Narasi (.mp3 / .ogg / .wav)</label>
                    <span class="mb-1 block text-[10px] text-gray-400">Maksimal 10MB. Diputar otomatis di web viewer saat
                        model dimuat.</span>
                    <input type="file" name="audio_narration_file" id="model-field-audio-file" accept="audio/*"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">
                    <p id="edit-current-audio-container" class="mt-1.5 hidden text-[10px] text-gray-500">
                        File aktif: <span id="edit-current-audio-path"
                            class="text-primary rounded border border-gray-100 bg-gray-50 px-1 py-0.5 font-mono font-semibold"></span>
                    </p>
                    <div class="mt-2 flex gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3">
                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-[10px] leading-relaxed text-amber-700">
                            <strong>Catatan AR Platform:</strong> Google AR Scene Viewer (Android) dan AR Quick Look (iOS)
                            <strong>tidak mendukung audio eksternal</strong> dari web. Agar audio terdengar saat model
                            dibuka langsung di AR, audio harus <strong>di-embed ke dalam file GLB/USDZ</strong>-nya
                            menggunakan tools seperti Blender atau Reality Composer. Audio yang diunggah di sini hanya
                            diputar di <em>web viewer</em> (tampilan di layar sebelum masuk AR).
                        </p>
                    </div>
                </div>

                <div class="mt-2.5 rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 p-3">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Pratinjau Aset
                        Model 3D</span>
                    <div class="model-viewer-wrapper flex items-center justify-center">
                        <div id="modal-viewer-placeholder" class="p-4 text-center">
                            <span class="text-xs text-gray-400">Pilih atau unggah file GLB untuk melihat model 3D</span>
                        </div>
                        <model-viewer id="modal-viewer-3d" class="hidden" camera-controls auto-rotate
                            shadow-intensity="1"></model-viewer>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModelModal()"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
                <button type="submit"
                    class="bg-primary shadow-primary/20 hover:bg-primary-600 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg">Simpan
                    Aset</button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    {{-- Tiptap Rich-Text Editor --}}
    <script type="module">
        import {
            Editor
        } from 'https://esm.sh/@tiptap/core';
        import StarterKit from 'https://esm.sh/@tiptap/starter-kit';

        document.addEventListener('DOMContentLoaded', () => {
            const textarea = document.getElementById('model-field-desc');
            const editorEl = document.getElementById('model-desc-editor');
            if (!editorEl || !textarea) return;

            const editor = new Editor({
                element: editorEl,
                extensions: [StarterKit],
                content: '',
                editorProps: {
                    attributes: {
                        class: 'focus:outline-none prose max-w-none text-sm text-gray-700 leading-relaxed min-h-20',
                    }
                },
                onUpdate({
                    editor
                }) {
                    textarea.value = editor.getHTML();
                }
            });

            window.modelDescEditor = editor;

            const toolbar = document.getElementById('model-desc-toolbar');
            if (toolbar) {
                function updateStates() {
                    const states = {
                        bold: editor.isActive('bold'),
                        italic: editor.isActive('italic'),
                        bulletList: editor.isActive('bulletList'),
                        orderedList: editor.isActive('orderedList')
                    };
                    toolbar.querySelectorAll('button').forEach(btn => {
                        const action = btn.getAttribute('data-action');
                        btn.classList.toggle('tiptap-btn-active', !!states[action]);
                    });
                }

                toolbar.querySelectorAll('button').forEach(btn => {
                    btn.addEventListener('click', e => {
                        e.preventDefault();
                        e.stopPropagation();
                        const action = btn.getAttribute('data-action');
                        if (action === 'bold') editor.chain().focus().toggleBold().run();
                        else if (action === 'italic') editor.chain().focus().toggleItalic().run();
                        else if (action === 'bulletList') editor.chain().focus().toggleBulletList()
                            .run();
                        else if (action === 'orderedList') editor.chain().focus()
                            .toggleOrderedList().run();
                        else if (action === 'undo') editor.chain().focus().undo().run();
                        else if (action === 'redo') editor.chain().focus().redo().run();
                        updateStates();
                    });
                });

                editor.on('selectionUpdate', updateStates);
                editor.on('transaction', updateStates);
            }
        });
    </script>

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

            document.getElementById('model-field-name').value = "";
            document.getElementById('model-field-marker-id').value = "";
            document.getElementById('model-field-patt-content').value = "";
            if (window.modelDescEditor) {
                window.modelDescEditor.commands.setContent('');
            }
            document.getElementById('model-field-desc').value = "";
            document.getElementById('model-field-glb-file').value = "";
            document.getElementById('model-field-glb-file').required = true;
            document.getElementById('glb-required-asterisk').style.display = 'inline';
            document.getElementById('model-field-usdz-file').value = "";
            document.getElementById('model-field-audio-file').value = "";
            document.getElementById('edit-current-glb-container').classList.add('hidden');
            document.getElementById('edit-current-usdz-container').classList.add('hidden');
            document.getElementById('edit-current-audio-container').classList.add('hidden');
            document.getElementById('modal-marker-preview-wrapper').classList.add('hidden');
            currentModalMarkerCanvas = null;
            resetModal3DViewer();

            window.dispatchEvent(new CustomEvent('open-model-modal'));
        }

        function openModelEditModal(model) {
            modelModalTitle.innerText = "Edit Model 3D";
            modelForm.action = `/admin/ar-manager/models/${model.id}`;
            modelMethodContainer.innerHTML = `@method('PUT')`;

            document.getElementById('model-field-name').value = model.name;
            document.getElementById('model-field-marker-id').value = model.ar_marker_id || "";
            document.getElementById('model-field-patt-content').value = "";
            const descHtml = model.description || '';
            document.getElementById('model-field-desc').value = descHtml;
            if (window.modelDescEditor) {
                window.modelDescEditor.commands.setContent(descHtml);
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

        function previewModelGLB(input) {
            const file = input.files[0];
            if (file) setupModal3DViewer(URL.createObjectURL(file));
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
                const qrValue = `${window.location.origin}/explore?marker=${encodeURIComponent(markerId)}`;

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
            const link = document.createElement('a');
            link.href = currentModalMarkerCanvas.toDataURL('image/png');
            link.download = `${markerId}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // ----------------------------------------------------
        // DOWNLOAD QR FOR EXISTING MARKER (from card)
        // ----------------------------------------------------
        function triggerMarkerDownload(markerId, slug) {
            try {
                const qrValue = slug ?
                    `${window.location.origin}/cultural/${slug}` :
                    `${window.location.origin}/explore?marker=${encodeURIComponent(markerId)}`;

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

    <style>
        .tiptap-btn-active {
            background-color: rgba(30, 81, 40, 0.1) !important;
            color: #1E5128 !important;
            border-color: rgba(30, 81, 40, 0.2) !important;
        }
    </style>
@endpush
