{{-- ponytail: partial dipecah untuk keterbacaan --}}
    <!-- Category Detail Modal -->
    <x-modal name="category-detail" maxWidth="sm">
        <!-- Close Button (Mobile only, desktop has close button in x-modal) -->
        <button type="button" onclick="closeCategoryModal()"
            class="absolute right-4 top-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition-all hover:text-gray-600 active:scale-95 md:hidden"
            title="Tutup">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Premium Tab Switcher (Only visible if 3D model is active) -->
        <div id="modal-tabs-container" class="mb-4 hidden justify-center gap-2 border-b border-gray-100 pb-2.5">
            <button type="button" id="tab-btn-image" onclick="switchModalTab('image')"
                class="bg-primary rounded-xl px-4 py-2 text-xs font-bold text-white shadow-sm transition-all">
                Gambar
            </button>
            <button type="button" id="tab-btn-3d" onclick="switchModalTab('3d')"
                class="rounded-xl bg-gray-50 px-4 py-2 text-xs font-bold text-gray-500 transition-all hover:bg-gray-100">
                Tampilan 3D
            </button>
        </div>

        <!-- Category Image Container -->
        <div class="text-primary flex aspect-video w-full items-center justify-center overflow-hidden rounded-2xl border border-gray-100 bg-gray-50"
            id="modal-category-image-container">
            <img id="modal-category-image" src="" alt="" class="hidden h-full w-full object-cover">
            <svg id="modal-category-fallback" class="h-14 w-14 opacity-50" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </div>

        <!-- Category 3D Viewer Container -->
        <div class="relative hidden aspect-video w-full overflow-hidden rounded-2xl border border-gray-100 bg-gray-100"
            id="modal-category-3d-container">
            <model-viewer id="modal-category-3d" class="h-full w-full" camera-controls auto-rotate shadow-intensity="1"
                touch-action="pan-y" draco-decoder-location="https://www.gstatic.com/draco/versioned/decoders/1.5.6/">
            </model-viewer>
        </div>

        <!-- Content -->
        <div class="mt-4">
            <h3 id="modal-category-name" class="font-display text-charcoal text-xl font-bold">Nama Kategori</h3>
            <p id="modal-category-description" class="mt-2 min-h-12.5 text-sm leading-relaxed text-gray-500">Deskripsi
                kategori...</p>
        </div>

        <!-- Action Button -->
        <div class="mt-6">
            <button type="button" id="modal-toggle-select-btn" onclick="toggleSelectFromModal()"
                class="bg-primary shadow-primary/20 flex w-full items-center justify-center gap-2 rounded-xl py-3.5 font-semibold text-white shadow-lg transition-transform active:scale-[0.98]">
                Pilih Kategori Ini
            </button>
        </div>
    </x-modal>
