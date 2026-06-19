@extends('layouts.dashboard')

@section('title', 'Kategori Produk UMKM')

@push('styles')
    <style>
        .category-image-wrapper {
            position: relative;
            width: 100%;
            height: 180px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #f3f4f6;
            background: radial-gradient(circle, #f9fafb 0%, #f3f4f6 100%);
        }

        .model-viewer-wrapper {
            position: relative;
            width: 100%;
            height: 180px;
            background: radial-gradient(circle, #f9fafb 0%, #f3f4f6 100%);
            border: 1px dashed #d1d5db;
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

    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div id="tour-header">
            <h1 class="font-display text-charcoal text-2xl font-bold">Kategori Produk UMKM</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola kategori produk yang dapat digunakan oleh pemilik UMKM.</p>
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
            <button id="tour-add-btn" onclick="openCreateModal()"
                class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kategori
            </button>
        </div>
    </div>

    {{-- Categories Cards Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($categories as $cat)
            <div @if($loop->first) id="tour-first-card" @endif
                class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md flex flex-col justify-between">
                <div>
                    <div class="category-image-wrapper mb-3">
                        @if ($cat->image_path)
                            <img src="{{ asset('storage/' . $cat->image_path) }}" alt="{{ $cat->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center">
                                <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375 0 11-.75 0 .375 0 01.75 0z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <h3 class="font-semibold text-charcoal text-base leading-tight">{{ $cat->name }}</h3>
                    <p class="font-mono text-[10px] text-gray-400 mt-1 mb-2">{{ $cat->slug }}</p>
                    <p class="text-sm text-gray-500 line-clamp-3 min-h-15 mb-4">{{ $cat->description ? $cat->description : 'Tidak ada deskripsi.' }}</p>
                </div>
                
                <div>
                    <div class="flex items-center justify-between border-t border-gray-50 pt-3">
                        <div>
                            <p class="text-xs text-gray-400">Total Produk</p>
                            <p class="text-sm font-bold text-primary">{{ $cat->products_count }} produk</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">Model 3D AR</p>
                            @if ($cat->model_3d_path)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-primary mt-0.5">
                                    <svg class="h-3.5 w-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Tersedia
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic mt-0.5 block">Tidak ada</span>
                            @endif
                        </div>
                    </div>
                    
                    <div @if($loop->first) id="tour-actions" @endif class="mt-4 flex gap-2">
                        <button onclick="openEditModal({{ json_encode($cat) }})"
                            class="flex-1 text-center rounded-xl border border-gray-200 py-2 text-xs font-semibold text-gray-600 transition-colors hover:bg-gray-50">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('admin.umkm.categories.destroy', $cat->id) }}"
                            class="delete-form flex-1"
                            data-confirm="Apakah Anda yakin ingin menghapus kategori ini? Semua produk di dalamnya akan kehilangan kategori.">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full rounded-xl border border-warning/30 py-2 text-xs font-semibold text-warning transition-colors hover:bg-warning/5">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div id="tour-empty-state" class="col-span-full rounded-2xl border border-dashed border-gray-200 p-8 text-center text-gray-400">
                Belum ada data kategori produk. Klik "Tambah Kategori" untuk membuat baru.
            </div>
        @endforelse
    </div>

    {{-- Category Modal Form --}}
    <x-modal name="category-modal" maxWidth="md" desktopLayout="drawer">
        <div class="mb-4">
            <h3 id="modal-title" class="font-display text-charcoal text-lg font-bold">Tambah Kategori Produk</h3>
        </div>
        <form id="modal-form" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div id="method-container"></div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama Kategori <span
                            class="text-warning">*</span></label>
                    <input type="text" name="name" id="field-name" required
                        placeholder="Contoh: Pakaian Adat, Makanan Ringan"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="font-display block text-sm font-semibold text-gray-700">Deskripsi</label>
                    <textarea name="description" id="field-description" placeholder="Deskripsi singkat tentang kategori..." rows="3"
                        class="focus:border-primary mt-1 w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="font-display block text-sm font-semibold text-gray-700">Gambar Kategori</label>
                    <input type="file" name="image" id="field-image" accept="image/*"
                        class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 mt-1 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
                    <span class="mt-1 block text-[10px] text-gray-400">Format gambar (jpg, jpeg, png), maks 2MB.</span>
                    <div id="image-preview-container" class="mt-2.5 hidden">
                        <span class="text-primary block text-[10px] font-bold uppercase tracking-wider">Gambar Saat
                            Ini:</span>
                        <div class="relative mt-1 h-20 w-32 overflow-hidden rounded-lg border border-gray-200">
                            <img id="image-preview" src="" alt="Pratinjau" class="h-full w-full object-cover">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="font-display block text-sm font-semibold text-gray-700">Model 3D (.glb)</label>
                    <input type="file" name="model_3d_file" id="field-model-3d" accept=".glb" onchange="previewModelGLB(this)"
                        class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 mt-1 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
                    <span class="mt-1 block text-[10px] text-gray-400">Format model GLB (kompresi Draco didukung), maks
                        20MB.</span>
                    <span id="current-model-3d" class="text-primary mt-1 block text-[10px] font-semibold"></span>
                </div>
                <div>
                    <label class="font-display block text-sm font-semibold text-gray-700">Model 3D iOS (.usdz)</label>
                    <input type="file" name="model_3d_usdz_file" id="field-model-3d-usdz" accept=".usdz"
                        class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 mt-1 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
                    <span class="mt-1 block text-[10px] text-gray-400">Format model USDZ untuk iOS Apple Quick Look, maks
                        50MB.</span>
                    <span id="current-model-3d-usdz" class="text-primary mt-1 block text-[10px] font-semibold"></span>
                </div>

                {{-- 3D Model Preview --}}
                <div class="mt-2.5 rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 p-3">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Pratinjau Model 3D</span>
                    <div class="model-viewer-wrapper flex items-center justify-center">
                        <div id="modal-viewer-placeholder" class="p-4 text-center">
                            <span class="text-xs text-gray-400">Pilih atau unggah file GLB untuk melihat model 3D</span>
                        </div>
                        <model-viewer id="modal-viewer-3d" class="hidden" camera-controls auto-rotate shadow-intensity="1"></model-viewer>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal()"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
                <button type="submit"
                    class="bg-primary shadow-primary/20 hover:bg-primary-600 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg">Simpan</button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    <script>
        const form = document.getElementById('modal-form');
        const modalTitle = document.getElementById('modal-title');
        const methodContainer = document.getElementById('method-container');
        const fieldName = document.getElementById('field-name');
        const fieldDescription = document.getElementById('field-description');
        const fieldImage = document.getElementById('field-image');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const imagePreview = document.getElementById('image-preview');
        const fieldModel3d = document.getElementById('field-model-3d');
        const fieldModel3dUsdz = document.getElementById('field-model-3d-usdz');
        const currentModel3d = document.getElementById('current-model-3d');
        const currentModel3dUsdz = document.getElementById('current-model-3d-usdz');
        const modalViewer3d = document.getElementById('modal-viewer-3d');
        const modalViewerPlaceholder = document.getElementById('modal-viewer-placeholder');

        function openCreateModal() {
            modalTitle.innerText = "Tambah Kategori Produk";
            form.action = "{{ route('admin.umkm.categories.store') }}";
            methodContainer.innerHTML = "";
            fieldName.value = "";
            fieldDescription.value = "";
            fieldImage.value = "";
            fieldModel3d.value = "";
            fieldModel3dUsdz.value = "";
            imagePreviewContainer.classList.add('hidden');
            imagePreview.src = "";
            currentModel3d.innerText = "";
            currentModel3dUsdz.innerText = "";
            resetModal3DViewer();

            window.dispatchEvent(new CustomEvent('open-category-modal'));
        }

        function openEditModal(cat) {
            modalTitle.innerText = "Edit Kategori Produk";
            form.action = `/admin/umkm/categories/${cat.id}`;
            methodContainer.innerHTML = `@method('PUT')`;
            fieldName.value = cat.name;
            fieldDescription.value = cat.description || "";
            fieldImage.value = "";
            fieldModel3d.value = "";
            fieldModel3dUsdz.value = "";
            currentModel3d.innerText = cat.model_3d_path ? "File aktif: " + cat.model_3d_path.split('/').pop() : "";
            currentModel3dUsdz.innerText = cat.model_3d_usdz_path ? "File aktif: " + cat.model_3d_usdz_path.split('/')
            .pop() : "";

            if (cat.image_path) {
                imagePreview.src = `/storage/${cat.image_path}`;
                imagePreviewContainer.classList.remove('hidden');
            } else {
                imagePreviewContainer.classList.add('hidden');
                imagePreview.src = "";
            }

            if (cat.model_3d_path) {
                setupModal3DViewer(`/storage/${cat.model_3d_path}`);
            } else {
                resetModal3DViewer();
            }

            window.dispatchEvent(new CustomEvent('open-category-modal'));
        }

        function closeModal() {
            window.dispatchEvent(new CustomEvent('close-category-modal'));
        }

        // 3D Model Modal Viewer Helpers
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

        // Driver.js Categories Interactive Tour
        function startTutorial() {
            const driver = window.driver.js.driver;
            const hasCard = document.getElementById('tour-first-card') !== null;
            const steps = [];

            // Langkah 1: Pengantar
            steps.push({
                element: '#tour-header',
                popover: {
                    title: '👋 Selamat Datang!',
                    description: 'Panduan ini akan menunjukkan cara mengelola kategori produk UMKM lokal di desa Penglipuran.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 2: Tombol Tambah Kategori
            steps.push({
                element: '#tour-add-btn',
                popover: {
                    title: '➕ Tambah Kategori Baru',
                    description: 'Gunakan tombol ini untuk menambahkan kategori produk baru, mengunggah ikon gambar representatif, serta file model 3D AR.',
                    side: 'bottom',
                    align: 'end'
                }
            });

            if (hasCard) {
                // Langkah 3: Kartu Kategori Pertama
                steps.push({
                    element: '#tour-first-card',
                    popover: {
                        title: '📦 Kartu Kategori',
                        description: 'Menampilkan thumbnail, slug unik, deskripsi, total produk yang terdaftar, serta status ketersediaan model 3D AR.',
                        side: 'top',
                        align: 'start'
                    }
                });

                // Langkah 4: Tombol Aksi
                steps.push({
                    element: '#tour-actions',
                    popover: {
                        title: '⚙️ Aksi Cepat',
                        description: 'Gunakan tombol Edit untuk mengubah data kategori atau Hapus untuk menghapusnya dari database.',
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
                        description: 'Setelah kategori pertama berhasil ditambahkan, kartu kategori visual akan tampil di area galeri ini.',
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
            const tourCompleted = localStorage.getItem('umkm_categories_tour_completed');
            if (!tourCompleted) {
                setTimeout(() => {
                    startTutorial();
                    localStorage.setItem('umkm_categories_tour_completed', 'true');
                }, 1000);
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
@endpush
