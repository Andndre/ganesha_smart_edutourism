@extends('layouts.dashboard')

@section('title', 'Daftar Produk Toko')

@push('styles')
    <style>
        .model-viewer-wrapper {
            position: relative;
            width: 100%;
            height: 250px;
            background: radial-gradient(circle, #f9fafb 0%, #f3f4f6 100%);
            border: 1px border-dashed #d1d5db;
            border-radius: 16px;
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
    <div class="mb-8 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between max-w-6xl">
        <div>
            <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Daftar Produk Toko</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola katalog produk, harga, persediaan stok, dan model 3D interaktif
                produk Anda.</p>
        </div>
        @if (!$noProfile)
            <button onclick="openCreateModal()"
                class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Produk
            </button>
        @endif
    </div>

    @if ($noProfile)
        <div class="rounded-2xl border border-warning/20 bg-warning/5 p-6 shadow-sm max-w-3xl">
            <div class="flex items-start gap-4">
                <div class="rounded-xl bg-warning/10 p-3 text-warning">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-display text-lg font-bold text-warning-800">Profil Toko Belum Dibuat</h3>
                    <p class="mt-1 text-sm text-warning-700">Anda belum memiliki profil toko UMKM yang aktif. Silakan buat
                        profil toko terlebih dahulu sebelum menambahkan katalog produk.</p>
                    <div class="mt-4">
                        <a href="{{ route('owner.profile') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-warning px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-warning/20 transition-all hover:bg-warning-600 active:scale-[0.98]">
                            Buat Profil Toko
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Search + Filter --}}
        <form method="GET" action="{{ route('owner.products') }}" class="mb-6 flex flex-col gap-3 sm:flex-row max-w-6xl">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..."
                    class="w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">
            </div>
            <select name="category" onchange="this.form.submit()"
                class="rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-primary focus:outline-none">
                <option value="Semua Kategori">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->name }}" {{ request('category') === $cat->name ? 'selected' : '' }}>{{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </form>

        {{-- Products Grid --}}
        @if ($products->isEmpty())
            <div class="rounded-2xl border border-gray-100 bg-white p-8 text-center text-gray-400 max-w-6xl">
                Belum ada produk terdaftar.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl">
                @foreach ($products as $p)
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md flex flex-col justify-between">
                        {{-- Product Image / 3D Model Header --}}
                        <div class="relative overflow-hidden bg-gray-50 h-44 shrink-0">
                            @if($p->images && count($p->images) > 0)
                                <img src="/storage/{{ $p->images[0] }}" alt="{{ $p->name }}"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else
                                <div
                                    class="w-full h-full flex flex-col items-center justify-center bg-linear-to-br from-primary/5 to-primary/10 text-primary">
                                    <svg class="h-10 w-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span class="text-[10px] uppercase font-bold tracking-widest opacity-60 mt-2">Tanpa Foto</span>
                                </div>
                            @endif

                            {{-- Top Badge: Category & Status --}}
                            <div class="absolute top-3 left-3 right-3 flex justify-between items-center z-10">
                                <span
                                    class="rounded-lg bg-white/95 backdrop-blur-sm px-2.5 py-1 text-[10px] font-bold text-primary shadow-sm">
                                    {{ $p->category->name ?? 'Lainnya' }}
                                </span>
                                @if ($p->is_active)
                                    <span
                                        class="rounded-lg bg-secondary px-2.5 py-1 text-[10px] font-bold text-white shadow-sm">Aktif</span>
                                @else
                                    <span
                                        class="rounded-lg bg-gray-500 px-2.5 py-1 text-[10px] font-bold text-white shadow-sm">Nonaktif</span>
                                @endif
                            </div>

                            {{-- 3D Model Badge --}}
                            @if ($p->ar_model_path)
                                <div class="absolute bottom-3 right-3 z-10">
                                    <span
                                        class="inline-flex items-center gap-1 text-[9px] font-bold text-secondary bg-white/95 backdrop-blur-sm rounded-lg px-2 py-1 shadow-sm">
                                        <svg class="h-3 w-3 text-secondary animate-pulse" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        3D Model
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Product Body --}}
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h4
                                    class="font-display text-lg font-bold text-charcoal tracking-tight leading-snug group-hover:text-primary transition-colors">
                                    {{ $p->name }}</h4>
                                <p class="mt-2 text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                    {{ $p->description ?? 'Belum ada deskripsi.' }}</p>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-gray-400 block font-semibold uppercase">Harga</span>
                                    <span class="text-base font-bold text-charcoal">Rp
                                        {{ number_format($p->price, 0, ',', '.') }}</span>
                                </div>

                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 block font-semibold uppercase">Stok</span>
                                    @if ($p->stock !== null && $p->stock <= 5)
                                        <span class="text-xs font-bold text-warning">{{ $p->stock }} (Menipis)</span>
                                    @elseif ($p->stock === null)
                                        <span class="text-xs text-gray-400 italic font-semibold">Tersedia</span>
                                    @else
                                        <span class="text-xs text-gray-600 font-bold">{{ $p->stock }} {{ $p->unit ?? 'pcs' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Product Actions --}}
                        <div class="px-5 pb-5 shrink-0 flex gap-2">
                            <button onclick="openEditModal({{ json_encode($p) }})"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-primary/20 bg-primary/5 py-2.5 text-xs font-bold text-primary transition-all hover:bg-primary hover:text-white">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Ubah
                            </button>

                            <form method="POST" action="{{ route('owner.products.destroy', $p->id) }}"
                                class="delete-form inline shrink-0" data-confirm="Apakah Anda yakin ingin menghapus produk ini?">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="rounded-xl border border-warning/20 bg-warning/5 p-2.5 text-warning transition-all hover:bg-warning hover:text-white"
                                    title="Hapus">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($products->hasPages())
                <div class="mt-8 max-w-6xl">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    @endif

    {{-- Product Modal Form --}}
    <x-modal name="product-modal" maxWidth="2xl" desktopLayout="drawer">
        <div class="mb-4">
            <h3 id="modal-title" class="font-display text-lg font-bold text-charcoal">Tambah Produk UMKM</h3>
        </div>
        <form id="modal-form" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div id="method-container"></div>

            <div class="grid gap-6 md:grid-cols-2">
                {{-- Left Column (Text Fields) --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Nama Produk <span
                                class="text-warning">*</span></label>
                        <input type="text" name="name" id="field-name" required
                            class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Kategori <span
                                class="text-warning">*</span></label>
                        <select name="umkm_product_category_id" id="field-category" required
                            class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                            <option value="" disabled selected>Pilih Kategori...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Harga (Rp) <span
                                    class="text-warning">*</span></label>
                            <input type="number" name="price" id="field-price" required min="0" placeholder="5000"
                                class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Persediaan (Stok)</label>
                            <input type="number" name="stock" id="field-stock" min="0"
                                placeholder="Kosongkan jika selalu ada"
                                class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Satuan</label>
                            <input type="text" name="unit" id="field-unit" placeholder="pcs, bungkus, porsi"
                                class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="is_active" id="field-active" value="1" checked
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                            <label for="field-active" class="text-sm font-semibold text-gray-700">Produk Aktif /
                                Tampil</label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Deskripsi Produk</label>
                        <textarea name="description" id="field-description" rows="3"
                            class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none"></textarea>
                    </div>
                </div>

                {{-- Right Column (Media & 3D Model Upload / Previews) --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Unggah Foto Produk</label>
                        <input type="file" name="images[]" multiple accept="image/*"
                            class="mt-1 w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Model 3D (Opsional - format
                            .glb)</label>
                        <input type="file" id="field-glb-file" name="ar_model_file" accept=".glb"
                            onchange="preview3DModel(this)"
                            class="mt-1 w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        <input type="hidden" name="ar_model_path" id="field-glb-path">
                    </div>

                    {{-- 3D Interactive Model Viewer Panel --}}
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Pratinjau 3D
                            Interaktif</span>
                        <div class="model-viewer-wrapper flex items-center justify-center">
                            <div id="viewer-placeholder" class="text-center p-4">
                                <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span class="mt-2 block text-xs text-gray-400">Pilih file 3D (.glb) untuk melihat
                                    pratinjau interaktif di sini</span>
                            </div>
                            <model-viewer id="viewer-3d" class="hidden" camera-controls auto-rotate
                                shadow-intensity="1"></model-viewer>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
                <button type="button" onclick="closeModal()"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
                <button type="submit"
                    class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary-600">Simpan
                    Produk</button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    <script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>
    <script>
        const form = document.getElementById('modal-form');
        const modalTitle = document.getElementById('modal-title');
        const methodContainer = document.getElementById('method-container');
        const storageUrl = "/storage";

        // Fields
        const fieldName = document.getElementById('field-name');
        const fieldCategory = document.getElementById('field-category');
        const fieldPrice = document.getElementById('field-price');
        const fieldStock = document.getElementById('field-stock');
        const fieldUnit = document.getElementById('field-unit');
        const fieldActive = document.getElementById('field-active');
        const fieldDescription = document.getElementById('field-description');
        const fieldGlbFile = document.getElementById('field-glb-file');
        const fieldGlbPath = document.getElementById('field-glb-path');

        // 3D Viewer Elements
        const viewer3d = document.getElementById('viewer-3d');
        const viewerPlaceholder = document.getElementById('viewer-placeholder');

        function openCreateModal() {
            modalTitle.innerText = "Tambah Produk UMKM";
            form.action = "{{ route('owner.products.store') }}";
            methodContainer.innerHTML = "";

            form.reset();
            reset3DViewer();

            window.dispatchEvent(new CustomEvent('open-product-modal'));
        }

        function openEditModal(product) {
            modalTitle.innerText = "Edit Produk UMKM";
            form.action = `/owner/products/${product.id}`;
            methodContainer.innerHTML = `@method('PUT')`;

            fieldName.value = product.name;
            fieldCategory.value = product.umkm_product_category_id || "";
            fieldPrice.value = Math.round(product.price);
            fieldStock.value = product.stock !== null ? product.stock : "";
            fieldUnit.value = product.unit || "pcs";
            fieldActive.checked = product.is_active;
            fieldDescription.value = product.description || "";
            fieldGlbFile.value = "";
            fieldGlbPath.value = product.ar_model_path || "";

            // Setup 3D viewer if model exists
            if (product.ar_model_path) {
                setup3DViewer(`${storageUrl}/${product.ar_model_path}`);
            } else {
                reset3DViewer();
            }

            window.dispatchEvent(new CustomEvent('open-product-modal'));
        }

        function closeModal() {
            window.dispatchEvent(new CustomEvent('close-product-modal'));
        }

        function preview3DModel(input) {
            const file = input.files[0];
            if (file) {
                const blobUrl = URL.createObjectURL(file);
                setup3DViewer(blobUrl);
            }
        }

        function setup3DViewer(src) {
            viewerPlaceholder.classList.add('hidden');
            viewer3d.classList.remove('hidden');
            viewer3d.src = src;
        }

        function reset3DViewer() {
            viewer3d.classList.add('hidden');
            viewerPlaceholder.classList.remove('hidden');
            viewer3d.src = "";
        }
    </script>
@endpush