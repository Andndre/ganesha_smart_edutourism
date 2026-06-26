@extends('layouts.dashboard')

@section('title', 'Daftar Produk Toko')



@section('content')
    <div class="mb-8 flex max-w-6xl flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-charcoal text-3xl font-extrabold tracking-tight">Daftar Produk Toko</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola katalog produk, harga, dan persediaan stok produk Anda.</p>
        </div>
        @if (!$noProfile)
            <button onclick="openCreateModal()"
                class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Produk
            </button>
        @endif
    </div>

    @if ($noProfile)
        <x-owner.no-profile-warning message="Anda belum memiliki profil toko UMKM yang aktif. Silakan buat profil toko terlebih dahulu sebelum menambahkan katalog produk." />
    @else
        {{-- Search + Filter --}}
        <form method="GET" action="{{ route('owner.products') }}" class="mb-6 flex max-w-6xl flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..."
                    class="focus:border-primary focus:ring-primary/20 w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm focus:outline-none focus:ring-1">
            </div>
            <select name="category" onchange="this.form.submit()"
                class="focus:border-primary rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none">
                <option value="Semua Kategori">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->name }}" {{ request('category') === $cat->name ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </form>

        {{-- Products Grid --}}
        @if ($products->isEmpty())
            <div class="max-w-6xl rounded-2xl border border-gray-100 bg-white p-8 text-center text-gray-400">
                Belum ada produk terdaftar.
            </div>
        @else
            <div class="grid max-w-6xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $p)
                    <div
                        class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                        {{-- Product Image --}}
                        <div class="relative h-44 shrink-0 overflow-hidden bg-gray-50">
                            @if ($p->images && count($p->images) > 0)
                                <img src="/storage/{{ $p->images[0] }}" alt="{{ $p->name }}"
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else
                                <div
                                    class="bg-linear-to-br from-primary/5 to-primary/10 text-primary flex h-full w-full flex-col items-center justify-center">
                                    <svg class="h-10 w-10 opacity-40" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span class="mt-2 text-[10px] font-bold uppercase tracking-widest opacity-60">Tanpa
                                        Foto</span>
                                </div>
                            @endif

                            {{-- Top Badge: Category & Status --}}
                            <div class="absolute left-3 right-3 top-3 z-10 flex items-center justify-between">
                                <span
                                    class="text-primary rounded-lg bg-white/95 px-2.5 py-1 text-[10px] font-bold shadow-sm backdrop-blur-sm">
                                    {{ $p->category->name ?? 'Lainnya' }}
                                </span>
                                @if ($p->is_active)
                                    <span
                                        class="bg-secondary rounded-lg px-2.5 py-1 text-[10px] font-bold text-white shadow-sm">Aktif</span>
                                @else
                                    <span
                                        class="rounded-lg bg-gray-500 px-2.5 py-1 text-[10px] font-bold text-white shadow-sm">Nonaktif</span>
                                @endif
                            </div>

                        </div>

                        {{-- Product Body --}}
                        <div class="flex flex-1 flex-col justify-between p-5">
                            <div>
                                <h4
                                    class="font-display text-charcoal group-hover:text-primary text-lg font-bold leading-snug tracking-tight transition-colors">
                                    {{ $p->name }}</h4>
                                <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-gray-500">
                                    {{ $p->description ?? 'Belum ada deskripsi.' }}</p>
                            </div>

                            <div class="mt-4 flex items-center justify-between border-t border-gray-50 pt-4">
                                <div>
                                    <span class="block text-[10px] font-semibold uppercase text-gray-400">Harga</span>
                                    <span class="text-charcoal text-base font-bold">Rp
                                        {{ number_format($p->price, 0, ',', '.') }}</span>
                                </div>

                                <div class="text-right">
                                    <span class="block text-[10px] font-semibold uppercase text-gray-400">Stok</span>
                                    @if ($p->stock !== null && $p->stock <= 5)
                                        <span class="text-warning text-xs font-bold">{{ $p->stock }} (Menipis)</span>
                                    @elseif ($p->stock === null)
                                        <span class="text-xs font-semibold italic text-gray-400">Tersedia</span>
                                    @else
                                        <span class="text-xs font-bold text-gray-600">{{ $p->stock }}
                                            {{ $p->unit ?? 'pcs' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Product Actions --}}
                        <div class="flex shrink-0 gap-2 px-5 pb-5">
                            <button onclick="openEditModal({{ json_encode([
                                'id' => $p->id,
                                'name' => $p->getTranslations('name'),
                                'description' => $p->getTranslations('description'),
                                'umkm_product_category_id' => $p->umkm_product_category_id,
                                'price' => $p->price,
                                'stock' => $p->stock,
                                'unit' => $p->unit,
                                'images' => $p->images,
                                'is_active' => $p->is_active,
                            ]) }})"
                                class="border-primary/20 bg-primary/5 text-primary hover:bg-primary inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border py-2.5 text-xs font-bold transition-all hover:text-white">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Ubah
                            </button>

                            <form method="POST" action="{{ route('owner.products.destroy', $p->id) }}"
                                class="delete-form inline shrink-0"
                                data-confirm="{{ __('Apakah Anda yakin ingin menghapus produk ini?') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="border-warning/20 bg-warning/5 text-warning hover:bg-warning rounded-xl border p-2.5 transition-all hover:text-white"
                                    title="Hapus">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($products->hasPages())
                <div class="mt-8 max-w-6xl">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    @endif

    {{-- Product Modal Form --}}
    <x-modal name="product-modal" maxWidth="2xl" desktopLayout="drawer">
        <div class="mb-4">
            <h3 id="modal-title" class="font-display text-charcoal text-lg font-bold">Tambah Produk UMKM</h3>
        </div>
        <form id="modal-form" method="POST" action="" enctype="multipart/form-data" x-data="{ locale: 'en' }">
            @csrf
            <div id="method-container"></div>
            <input type="hidden" name="product_id" id="field-product-id" value="">

            <div class="grid gap-6 md:grid-cols-2">
                {{-- Left Column (Text Fields) --}}
                <div class="space-y-4">
                    {{-- Locale tabs --}}
                    <x-locale-toggle />

                    {{-- Name --}}
                    <div x-show="locale === 'en'">
                        <label class="block text-sm font-semibold text-gray-700">Product Name (EN) <span
                                class="text-warning">*</span></label>
                        <input type="text" name="name[en]" id="field-name-en" required
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                        @error('name.en')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-show="locale === 'id'">
                        <label class="block text-sm font-semibold text-gray-700">Nama Produk (ID) <span
                                class="text-warning">*</span></label>
                        <input type="text" name="name[id]" id="field-name-id" required
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                        @error('name.id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Kategori <span
                                class="text-warning">*</span></label>
                        <select name="umkm_product_category_id" id="field-category" required
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                            <option value="" disabled selected>Pilih Kategori...</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('umkm_product_category_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Harga (Rp) <span
                                    class="text-warning">*</span></label>
                            <input type="number" name="price" id="field-price" required min="0"
                                placeholder="5000"
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                            @error('price')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Persediaan (Stok)</label>
                            <input type="number" name="stock" id="field-stock" min="0"
                                placeholder="Kosongkan jika selalu ada"
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                            @error('stock')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Satuan</label>
                            <input type="text" name="unit" id="field-unit" placeholder="pcs, bungkus, porsi"
                                class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                            @error('unit')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input type="checkbox" name="is_active" id="field-active" value="1" checked
                                class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300">
                            <label for="field-active" class="text-sm font-semibold text-gray-700">Produk Aktif /
                                Tampil</label>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div x-show="locale === 'en'">
                        <label class="block text-sm font-semibold text-gray-700">Description (EN)</label>
                        <textarea name="description[en]" id="field-description-en" rows="3"
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"></textarea>
                        @error('description.en')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-show="locale === 'id'">
                        <label class="block text-sm font-semibold text-gray-700">Deskripsi Produk (ID)</label>
                        <textarea name="description[id]" id="field-description-id" rows="3"
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"></textarea>
                        @error('description.id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Right Column (Media) --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Unggah Foto Produk</label>
                        <input type="file" name="images[]" id="field-images" multiple accept="image/*"
                            onchange="previewImages(this)"
                            class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 mt-1 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
                        @error('images.*')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-400">Pratinjau
                            Foto</span>
                        <div id="image-preview-container" class="grid grid-cols-3 gap-3">
                        </div>
                        <p id="no-image-text" class="text-xs italic text-gray-400">Belum ada foto yang dipilih.</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
                <button type="button" onclick="closeModal()"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
                <button type="submit"
                    class="bg-primary shadow-primary/20 hover:bg-primary-600 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg">Simpan
                    Produk</button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    <script>
        const form = document.getElementById('modal-form');
        const modalTitle = document.getElementById('modal-title');
        const methodContainer = document.getElementById('method-container');

        // Fields
        const fieldName = document.getElementById('field-name');
        const fieldCategory = document.getElementById('field-category');
        const fieldPrice = document.getElementById('field-price');
        const fieldStock = document.getElementById('field-stock');
        const fieldUnit = document.getElementById('field-unit');
        const fieldActive = document.getElementById('field-active');
        const fieldDescription = document.getElementById('field-description');

        const imagePreviewContainer = document.getElementById('image-preview-container');
        const noImageText = document.getElementById('no-image-text');
        const storageUrl = "/storage";

        function previewImages(input) {
            const maxSize = 5 * 1024 * 1024; // 5MB (server limit: 5120 KB)
            const oversized = Array.from(input.files || []).find(f => f.size > maxSize);
            if (oversized) {
                Swal.fire({
                    title: 'Ukuran File Terlalu Besar',
                    text: 'Ukuran file maksimal adalah 5MB per gambar.',
                    icon: 'warning',
                    confirmButtonColor: '#1E5128',
                    confirmButtonText: 'Mengerti',
                    background: '#ffffff'
                });
                input.value = '';
                imagePreviewContainer.innerHTML = '';
                noImageText.classList.remove('hidden');
                return;
            }

            imagePreviewContainer.innerHTML = '';

            if (input.files && input.files.length > 0) {
                noImageText.classList.add('hidden');

                Array.from(input.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'h-24 w-full rounded-xl border border-gray-200 object-cover shadow-sm';
                        imagePreviewContainer.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                });
            } else {
                noImageText.classList.remove('hidden');
            }
        }

        function openCreateModal() {
            modalTitle.innerText = "Tambah Produk UMKM";
            form.action = "{{ route('owner.products.store') }}";
            methodContainer.innerHTML = "";

            form.reset();
            document.getElementById('field-product-id').value = "";
            document.getElementById('field-name-en').value = "";
            document.getElementById('field-name-id').value = "";
            document.getElementById('field-description-en').value = "";
            document.getElementById('field-description-id').value = "";
            document.getElementById('field-images').value = "";
            imagePreviewContainer.innerHTML = '';
            noImageText.classList.remove('hidden');

            window.dispatchEvent(new CustomEvent('open-product-modal'));
        }

        function openEditModal(product) {
            modalTitle.innerText = "Edit Produk UMKM";
            form.action = `/owner/products/${product.id}`;
            methodContainer.innerHTML = `@method('PUT')`;

            document.getElementById('field-product-id').value = product.id;
            
            // Handle translatable object or fallback string safely
            document.getElementById('field-name-en').value = (typeof product.name === 'object') ? (product.name?.en || "") : product.name;
            document.getElementById('field-name-id').value = (typeof product.name === 'object') ? (product.name?.id || "") : product.name;
            
            fieldCategory.value = product.umkm_product_category_id || "";
            fieldPrice.value = Math.round(product.price);
            fieldStock.value = product.stock !== null ? product.stock : "";
            fieldUnit.value = product.unit || "pcs";
            fieldActive.checked = product.is_active;
            
            document.getElementById('field-description-en').value = (typeof product.description === 'object') ? (product.description?.en || "") : (product.description || "");
            document.getElementById('field-description-id').value = (typeof product.description === 'object') ? (product.description?.id || "") : (product.description || "");

            document.getElementById('field-images').value = "";
            imagePreviewContainer.innerHTML = '';

            if (product.images && product.images.length > 0) {
                noImageText.classList.add('hidden');
                product.images.forEach(imagePath => {
                    const img = document.createElement('img');
                    img.src = `${storageUrl}/${imagePath}`;
                    img.className = 'h-24 w-full rounded-xl border border-gray-200 object-cover shadow-sm';
                    imagePreviewContainer.appendChild(img);
                });
            } else {
                noImageText.classList.remove('hidden');
            }

            window.dispatchEvent(new CustomEvent('open-product-modal'));
        }

        function closeModal() {
            window.dispatchEvent(new CustomEvent('close-product-modal'));
        }

        // Auto-reopen modal & restore old() values on validation error
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function () {
                window.dispatchEvent(new CustomEvent('open-product-modal'));

                @if(old('_method') == 'PUT')
                    form.action = "/owner/products/{{ old('product_id') }}";
                    methodContainer.innerHTML = `@method('PUT')`;
                    modalTitle.innerText = "Edit Produk UMKM";
                    document.getElementById('field-product-id').value = "{{ old('product_id') }}";
                @else
                    form.action = "{{ route('owner.products.store') }}";
                    methodContainer.innerHTML = "";
                    modalTitle.innerText = "Tambah Produk UMKM";
                    document.getElementById('field-product-id').value = "";
                @endif

                document.getElementById('field-name-en').value = @json(old('name.en', ''));
                document.getElementById('field-name-id').value = @json(old('name.id', ''));
                fieldCategory.value = @json(old('umkm_product_category_id', ''));
                fieldPrice.value = @json(old('price', ''));
                fieldStock.value = @json(old('stock', ''));
                fieldUnit.value = @json(old('unit', ''));
                fieldActive.checked = @json((bool)old('is_active', false));
                document.getElementById('field-description-en').value = @json(old('description.en', ''));
                document.getElementById('field-description-id').value = @json(old('description.id', ''));
            });
        @endif
    </script>
@endpush
