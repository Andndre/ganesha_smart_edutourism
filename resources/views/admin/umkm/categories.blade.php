@extends('layouts.dashboard')

@section('title', 'Kategori Produk UMKM')

@section('content')

    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-charcoal text-2xl font-bold">Kategori Produk UMKM</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola kategori produk yang dapat digunakan oleh pemilik UMKM.</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kategori
        </button>
    </div>

    {{-- Categories Table --}}
    <div class="max-w-4xl overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nama
                            Kategori</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Slug
                        </th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Total
                            Produk</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($categories as $cat)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="text-primary flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-100 bg-gray-50">
                                        @if ($cat->image_path)
                                            <img src="{{ asset('storage/' . $cat->image_path) }}" alt="{{ $cat->name }}"
                                                class="h-full w-full object-cover">
                                        @else
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-charcoal font-semibold">{{ $cat->name }}</div>
                                        @if ($cat->description)
                                            <div class="mt-0.5 max-w-xs truncate text-xs text-gray-400"
                                                title="{{ $cat->description }}">{{ $cat->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $cat->slug }}</td>
                            <td class="px-5 py-4">
                                <span
                                    class="bg-primary/10 text-primary-800 rounded-lg px-2.5 py-1 text-xs font-semibold">{{ $cat->products_count }}
                                    produk</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="openEditModal({{ json_encode($cat) }})"
                                        class="hover:bg-primary/10 hover:text-primary rounded-lg p-1.5 text-gray-400 transition-colors"
                                        title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.umkm.categories.destroy', $cat->id) }}"
                                        class="delete-form inline"
                                        data-confirm="Apakah Anda yakin ingin menghapus kategori ini? Semua produk di dalamnya akan kehilangan kategori.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="hover:bg-warning/10 hover:text-warning rounded-lg p-1.5 text-gray-400 transition-colors"
                                            title="Hapus">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-400">Belum ada data kategori produk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
                    <input type="file" name="model_3d_file" id="field-model-3d" accept=".glb"
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

            window.dispatchEvent(new CustomEvent('open-category-modal'));
        }

        function closeModal() {
            window.dispatchEvent(new CustomEvent('close-category-modal'));
        }
    </script>
@endpush
