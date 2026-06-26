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
            <div class="sticky top-0 z-10 bg-white py-2.5 border-b border-gray-100 mb-4 flex gap-2">
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
                    <input type="text" name="name[en]" id="model-field-name-en"
                        placeholder="e.g. Traditional Temple Model"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @error('name.en')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Description (EN)</label>
                    <x-tiptap-editor name="description[en]" id="model-field-desc-en" placeholder="e.g. Traditional Temple Model" has-image="true" />
                    @error('description.en')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Indonesian Tab Section --}}
            <div x-show="locale === 'id'" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama Model (ID) <span
                            class="text-warning">*</span></label>
                    <input type="text" name="name[id]" id="model-field-name-id"
                        placeholder="Contoh: Model Pura Tradisional"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @error('name.id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Deskripsi Model (ID)</label>
                    <x-tiptap-editor name="description[id]" id="model-field-desc-id" placeholder="Contoh: Model Pura Tradisional" has-image="true" />
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
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">
                    @error('model_3d_file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    <input type="hidden" name="tmp_model_3d_path" id="model-field-tmp-glb" value="">
                    <div id="model-glb-progress" class="mt-2 hidden tus-progress-container">
                        <div class="flex items-center gap-2">
                            <span class="tus-status-icon"></span>
                            <span class="tus-progress-text flex-1 text-[10px] text-gray-500"></span>
                        </div>
                        <div class="mt-1 h-1 w-full overflow-hidden rounded-full bg-gray-200">
                            <div class="tus-progress-bar h-full rounded-full bg-penglipuran-green transition-all duration-300" style="width:0%"></div>
                        </div>
                    </div>
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
                    @error('model_3d_usdz_file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    <input type="hidden" name="tmp_model_3d_usdz_path" id="model-field-tmp-usdz" value="">
                    <div id="model-usdz-progress" class="mt-2 hidden tus-progress-container">
                        <div class="flex items-center gap-2">
                            <span class="tus-status-icon"></span>
                            <span class="tus-progress-text flex-1 text-[10px] text-gray-500"></span>
                        </div>
                        <div class="mt-1 h-1 w-full overflow-hidden rounded-full bg-gray-200">
                            <div class="tus-progress-bar h-full rounded-full bg-penglipuran-green transition-all duration-300" style="width:0%"></div>
                        </div>
                    </div>
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
                @error('audio_narration_file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                <input type="hidden" name="tmp_audio_narration_path" id="model-field-tmp-audio" value="">
                <div id="model-audio-progress" class="mt-2 hidden tus-progress-container">
                    <div class="flex items-center gap-2">
                        <span class="tus-status-icon"></span>
                        <span class="tus-progress-text flex-1 text-[10px] text-gray-500"></span>
                    </div>
                    <div class="mt-1 h-1 w-full overflow-hidden rounded-full bg-gray-200">
                        <div class="tus-progress-bar h-full rounded-full bg-penglipuran-green transition-all duration-300" style="width:0%"></div>
                    </div>
                </div>
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
