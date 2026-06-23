{{-- 3D MODEL DIALOG / MODAL FORM --}}
<x-modal name="model-modal" maxWidth="xl" desktopLayout="drawer">
    <div class="mb-4">
        <h3 id="model-modal-title" class="font-display text-charcoal text-lg font-bold">Tambah Model 3D</h3>
    </div>
    <form id="model-form" method="POST" action="" enctype="multipart/form-data" x-data="{ locale: 'en' }">
        @csrf
        <div id="model-method-container"></div>
        <input type="hidden" name="ar_marker_patt_content" id="model-field-patt-content">
        <input type="hidden" name="model_id" id="model-field-id" value="">

        <div class="space-y-4">
            {{-- Locale tabs --}}
            <div class="flex gap-2 mb-2">
                <button @click="locale = 'en'" :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" type="button">English</button>
                <button @click="locale = 'id'" :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" type="button">Indonesia</button>
            </div>

            {{-- English Tab Section --}}
            <div x-show="locale === 'en'" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Model Name (EN) <span
                            class="text-warning">*</span></label>
                    <input type="text" name="name[en]" id="model-field-name-en" required
                        placeholder="e.g. Traditional Temple Model"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @error('name.en')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Description (EN)</label>
                    <div id="model-desc-toolbar-en"
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
                    <div id="model-desc-editor-en"
                        class="focus-within:border-primary focus-within:ring-primary/20 max-h-50 min-h-25 w-full overflow-y-auto rounded-b-xl border border-gray-200 bg-white p-4 text-sm focus-within:ring-1">
                    </div>
                    <textarea name="description[en]" id="model-field-desc-en" class="hidden"></textarea>
                    @error('description.en')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Indonesian Tab Section --}}
            <div x-show="locale === 'id'" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama Model (ID) <span
                            class="text-warning">*</span></label>
                    <input type="text" name="name[id]" id="model-field-name-id" required
                        placeholder="Contoh: Model Pura Tradisional"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @error('name.id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Deskripsi Model (ID)</label>
                    <div id="model-desc-toolbar-id"
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
                    <div id="model-desc-editor-id"
                        class="focus-within:border-primary focus-within:ring-primary/20 max-h-50 min-h-25 w-full overflow-y-auto rounded-b-xl border border-gray-200 bg-white p-4 text-sm focus-within:ring-1">
                    </div>
                    <textarea name="description[id]" id="model-field-desc-id" class="hidden"></textarea>
                    @error('description.id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
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
                @error('ar_marker_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">File Model 3D (.glb) <span
                            class="text-warning" id="glb-required-asterisk">*</span></label>
                    <span class="mb-1 block text-[10px] text-gray-400">Maksimal 20MB.</span>
                    <input type="file" name="model_3d_file" id="model-field-glb-file" accept=".glb"
                        onchange="previewModelGLB(this)"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">
                    @error('model_3d_file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    <p id="edit-current-glb-container" class="mt-1.5 hidden text-[10px] text-gray-500">
                        File aktif: <span id="edit-current-glb-path"
                            class="rounded border border-gray-100 bg-gray-50 px-1 py-0.5 font-mono"></span>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">File iOS Model (.usdz)</label>
                    <span class="mb-1 block text-[10px] text-gray-400">Maksimal 50MB.</span>
                    <input type="file" name="model_3d_usdz_file" id="model-field-usdz-file" accept=".usdz"
                        onchange="previewModelUSDZ(this)"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">
                    @error('model_3d_usdz_file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
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
                    onchange="previewModelAudio(this)"
                    class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">
                @error('audio_narration_file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
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
