@extends('layouts.dashboard')

@section('title', 'Daftar Produk Toko')

@php
    use Illuminate\Support\Facades\Storage;
    $ownerProfileId = $profile?->id;
    $categoryPayload = $categories->map(fn($c) => [
        'id' => $c->id,
        'name' => translateValue($c->name),
        'name_translations' => $c->getTranslations('name'),
        'description_translations' => $c->getTranslations('description'),
        'price' => $c->price,
        'unit' => $c->unit,
        'image_path' => $c->image_path,
        'model_3d_path' => $c->model_3d_path,
        'model_3d_usdz_path' => $c->model_3d_usdz_path,
        'editable_by_me' => $profile ? $c->editableByOwner($profile) : false,
    ]);
@endphp

@section('content')
    <div class="mb-8 flex max-w-6xl flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div id="tour-header">
            <h1 class="font-display text-charcoal text-3xl font-extrabold tracking-tight">{{ 'Daftar Produk Toko' }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ 'Pilih kategori, atur stok, dan kelola katalog produk Anda.' }}</p>
        </div>
        @if (!$noProfile)
            <div class="flex items-center gap-2">
                <button id="tour-trigger-btn" onclick="startTutorial()"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:bg-gray-100 active:scale-[0.98]"
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
                    {{ 'Tambah Produk' }}
                </button>
            </div>
        @endif
    </div>

    @if ($noProfile)
        <x-owner.no-profile-warning message="Anda belum memiliki profil toko UMKM yang aktif. Silakan buat profil toko terlebih dahulu sebelum menambahkan katalog produk." />
    @else
        {{-- Search + Filter --}}
        <form id="tour-search-filter" method="GET" action="{{ route('owner.products') }}" class="mb-6 flex max-w-6xl flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ 'Cari kategori...' }}"
                    class="focus:border-primary focus:ring-primary/20 w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm focus:outline-none focus:ring-1">
            </div>
            <select name="category" onchange="this.form.submit()"
                class="focus:border-primary rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none">
                <option value="Semua Kategori">{{ 'Semua Kategori' }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->name }}" {{ request('category') === $cat->name ? 'selected' : '' }}>
                        {{ translateValue($cat->name) }}
                    </option>
                @endforeach
            </select>
        </form>

        {{-- Products Grid --}}
        @if ($products->isEmpty())
            <div id="tour-empty-state" class="max-w-6xl rounded-2xl border border-gray-100 bg-white p-8 text-center text-gray-400">
                {{ 'Belum ada produk terdaftar.' }}
            </div>
        @else
            <div id="tour-products-grid" class="grid max-w-6xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $p)
                    <div @if ($loop->first) id="tour-first-card" @endif
                        class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                        <div class="relative h-44 shrink-0 overflow-hidden bg-gray-50">
                            @if ($p->display_image)
                                <img src="{{ Storage::url($p->display_image) }}" alt="{{ $p->display_name }}"
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else
                                <div class="bg-linear-to-br from-primary/5 to-primary/10 text-primary flex h-full w-full flex-col items-center justify-center">
                                    <svg class="h-10 w-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span class="mt-2 text-[10px] font-bold uppercase tracking-widest opacity-60">{{ 'Tanpa Foto' }}</span>
                                </div>
                            @endif

                            <div class="absolute left-3 right-3 top-3 z-10 flex items-center justify-between">
                                <span class="text-primary rounded-lg bg-white/95 px-2.5 py-1 text-[10px] font-bold shadow-sm backdrop-blur-sm">
                                    {{ translateValue($p->category?->name) ?? 'Lainnya' }}
                                </span>
                                @if ($p->is_active)
                                    <span class="bg-secondary rounded-lg px-2.5 py-1 text-[10px] font-bold text-white shadow-sm">{{ 'Aktif' }}</span>
                                @else
                                    <span class="rounded-lg bg-gray-500 px-2.5 py-1 text-[10px] font-bold text-white shadow-sm">{{ 'Nonaktif' }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-1 flex-col justify-between p-5">
                            <div>
                                <h4 class="font-display text-charcoal group-hover:text-primary text-lg font-bold leading-snug tracking-tight transition-colors">
                                    {{ $p->display_name }}</h4>
                                <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-gray-500">
                                    {{ $p->display_description ?? 'Belum ada deskripsi.' }}</p>
                            </div>

                            <div class="mt-4 flex items-center justify-between border-t border-gray-50 pt-4">
                                <div>
                                    <span class="block text-[10px] font-semibold uppercase text-gray-400">{{ 'Harga' }}</span>
                                    <span class="text-charcoal text-base font-bold">
                                        @if ($p->display_price !== null)
                                            Rp {{ number_format($p->display_price, 0, ',', '.') }}
                                        @else
                                            <span class="text-xs italic text-gray-400">{{ 'Belum diatur' }}</span>
                                        @endif
                                    </span>
                                </div>

                                <div class="text-right">
                                    <span class="block text-[10px] font-semibold uppercase text-gray-400">{{ 'Stok' }}</span>
                                    @if ($p->stock !== null && $p->stock <= 5)
                                        <span class="text-warning text-xs font-bold">{{ $p->stock }} ({{ 'Menipis' }})</span>
                                    @elseif ($p->stock === null)
                                        <span class="text-xs font-semibold italic text-gray-400">{{ 'Tersedia' }}</span>
                                    @else
                                        <span class="text-xs font-bold text-gray-600">{{ $p->stock }} {{ $p->display_unit }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div @if ($loop->first) id="tour-card-actions" @endif class="flex shrink-0 gap-2 px-5 pb-5">
                            <button onclick="openEditModal({{ json_encode([
                                'id'                       => $p->id,
                                'umkm_product_category_id' => $p->umkm_product_category_id,
                                'price'                    => $p->getAttribute('price'),
                                'unit'                     => $p->unit,
                                'stock'                    => $p->stock,
                                'is_active'                => $p->is_active,
                            ]) }})"
                                class="border-primary/20 bg-primary/5 text-primary hover:bg-primary inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border py-2.5 text-xs font-bold transition-all hover:text-white">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                {{ 'Ubah' }}
                            </button>

                            <form method="POST" action="{{ route('owner.products.destroy', $p->id) }}"
                                class="delete-form inline shrink-0"
                                data-confirm="{{ 'Apakah Anda yakin ingin menghapus produk ini?' }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="border-warning/20 bg-warning/5 text-warning hover:bg-warning rounded-xl border p-2.5 transition-all hover:text-white" title="{{ 'Hapus' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($products->hasPages())
                <div class="mt-8 max-w-6xl">{{ $products->links() }}</div>
            @endif
        @endif

        {{-- Kategori yang saya gunakan --}}
        @php($myCategoryIds = $products->pluck('umkm_product_category_id')->unique()->filter())
        @if ($myCategoryIds->isNotEmpty())
            <div id="tour-my-categories" class="mt-12 max-w-6xl">
                <h2 class="font-display text-charcoal text-xl font-bold tracking-tight">{{ 'Kategori yang Saya Pakai' }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ 'Ubah langsung jika seluruh produk pada kategori ini milik Anda. Jika tidak, ajukan permintaan edit ke admin.' }}</p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories->whereIn('id', $myCategoryIds) as $cat)
                        @php($editable = $profile ? $cat->editableByOwner($profile) : false)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-charcoal">{{ translateValue($cat->name) }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider {{ $editable ? 'text-primary' : 'text-warning' }}">
                                    {{ $editable ? 'Bisa diubah langsung' : 'Perlu izin admin' }}
                                </p>
                            </div>
                            @if ($editable)
                                <button onclick="openCategoryEdit({{ $cat->id }})"
                                    class="border-primary/20 bg-primary/5 text-primary hover:bg-primary inline-flex items-center justify-center gap-1.5 rounded-xl border px-3 py-2 text-xs font-bold transition-all hover:text-white">
                                    {{ 'Ubah' }}
                                </button>
                            @else
                                <button onclick="openCategoryRequestEdit({{ $cat->id }}, '{{ addslashes(translateValue($cat->name)) }}')"
                                    class="border-warning/20 bg-warning/5 text-warning hover:bg-warning inline-flex items-center justify-center gap-1.5 rounded-xl border px-3 py-2 text-xs font-bold transition-all hover:text-white">
                                    {{ 'Minta Edit ke Admin' }}
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- =========================
         Modal: Produk (kategori + stok + aktif)
         ========================= --}}
    <x-modal name="product-modal" maxWidth="lg" desktopLayout="drawer">
        <div class="mb-4">
            <h3 id="modal-title" class="font-display text-charcoal text-lg font-bold">{{ 'Tambah Produk UMKM' }}</h3>
        </div>
        <form id="modal-form" method="POST" action="" x-data="categoryPicker(@js($categoryPayload->all()))">
            @csrf
            <div id="method-container"></div>
            <input type="hidden" name="product_id" id="field-product-id" value="">
            <input type="hidden" name="umkm_product_category_id" :value="selectedId">

            <div class="space-y-4">
                {{-- Searchable Category Dropdown --}}
                <div class="relative">
                    <label class="block text-sm font-semibold text-gray-700">{{ 'Kategori' }} <span class="text-warning">*</span></label>
                    <div class="relative mt-1">
                        <input type="text" x-model="query" @focus="open = true" @click="open = true"
                            placeholder="{{ 'Cari kategori...' }}"
                            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                        <svg class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div x-show="open" x-cloak @click.outside="open = false"
                        class="absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg">
                        <template x-for="c in filtered()" :key="c.id">
                            <button type="button" @click="select(c)"
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm hover:bg-gray-50">
                                <img x-show="c.image_path" :src="`/storage/${c.image_path}`" class="h-8 w-8 rounded-lg object-cover">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-charcoal" x-text="c.name"></p>
                                    <p x-show="c.price !== null" class="text-[10px] text-gray-500">Rp <span x-text="Number(c.price).toLocaleString('id-ID')"></span></p>
                                </div>
                            </button>
                        </template>
                        <template x-if="filtered().length === 0">
                            <p class="px-4 py-2.5 text-xs italic text-gray-400">{{ 'Tidak ada kategori cocok.' }}</p>
                        </template>
                        <button type="button" @click="openCreate()"
                            class="flex w-full items-center gap-2 border-t border-gray-100 bg-primary/5 px-4 py-3 text-left text-sm font-bold text-primary hover:bg-primary/10">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            {{ 'Tambah Kategori Baru' }}
                        </button>
                    </div>
                    @error('umkm_product_category_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Selected category preview --}}
                <div x-show="selected" x-cloak class="rounded-xl border border-primary/20 bg-primary/5 p-3">
                    <div class="flex items-center gap-3">
                        <img x-show="selected?.image_path" :src="`/storage/${selected?.image_path}`" class="h-12 w-12 rounded-lg object-cover">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-gray-500">{{ 'Kategori dipilih' }}</p>
                            <p class="font-bold text-charcoal" x-text="selected?.name"></p>
                            <p class="text-[11px] text-gray-500">
                                Rp <span x-text="selected?.price !== null ? Number(selected?.price).toLocaleString('id-ID') : '—'"></span>
                                · <span x-text="selected?.unit || 'pcs'"></span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Harga & Satuan --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ 'Harga (Rp)' }}</label>
                    <input type="number" name="price" id="field-price" min="0" step="500"
                        placeholder="{{ 'Kosongkan jika menggunakan harga kategori' }}"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">{{ 'Satuan' }}</label>
                    <input type="text" name="unit" id="field-unit"
                        placeholder="pcs / kg / porsi"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ 'Persediaan (Stok)' }}</label>
                        <input type="number" name="stock" id="field-stock" min="0" placeholder="{{ 'Kosongkan jika selalu ada' }}"
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="is_active" id="field-active" value="1" checked
                            class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300">
                        <label for="field-active" class="text-sm font-semibold text-gray-700">{{ 'Produk Aktif / Tampil' }}</label>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
                <button type="button" onclick="closeProductModal()"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">{{ 'Batal' }}</button>
                <button type="submit"
                    class="bg-primary shadow-primary/20 hover:bg-primary-600 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg">{{ 'Simpan Produk' }}</button>
            </div>
        </form>
    </x-modal>

    {{-- =========================
         Modal: Tambah / Ubah Kategori (inline)
         ========================= --}}
    <x-modal name="category-form-modal" maxWidth="2xl" desktopLayout="drawer">
        <div class="mb-4">
            <h3 id="category-modal-title" class="font-display text-charcoal text-lg font-bold">{{ 'Tambah Kategori Baru' }}</h3>
        </div>
        <form id="category-form" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_category_id" id="category-form-id" value="">

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Nama (EN) <span class="text-warning">*</span></label>
                        <input type="text" name="name[en]" id="cat-name-en" required
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Nama (ID) <span class="text-warning">*</span></label>
                        <input type="text" name="name[id]" id="cat-name-id" required
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Deskripsi (EN)</label>
                        <textarea name="description[en]" id="cat-desc-en" rows="3"
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Deskripsi (ID)</label>
                        <textarea name="description[id]" id="cat-desc-id" rows="3"
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"></textarea>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">{{ 'Harga' }} (Rp)</label>
                            <input type="number" name="price" id="cat-price" min="0" step="0.01"
                                class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">{{ 'Satuan' }}</label>
                            <input type="text" name="unit" id="cat-unit" placeholder="pcs, kg"
                                class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">{{ 'Gambar Kategori' }}</label>
                        <input type="file" name="image" id="cat-image" accept="image/*"
                            class="file:bg-primary/10 file:text-primary mt-1 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
                    </div>

                    <details class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                        <summary class="cursor-pointer text-sm font-semibold text-gray-700">{{ 'Tingkat lanjut (Model 3D, opsional)' }}</summary>
                        <div class="mt-3 space-y-3">
                            <p class="text-[11px] italic text-gray-500">{{ 'Biarkan kosong jika ingin admin yang menambahkan model 3D.' }}</p>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Model 3D (GLB/GLTF, ≤20MB)</label>
                                <input type="file" name="model_3d_file" accept=".glb,.gltf"
                                    class="file:bg-primary/10 file:text-primary mt-1 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700">Model USDZ (iOS, ≤50MB)</label>
                                <input type="file" name="model_3d_usdz_file" accept=".usdz"
                                    class="file:bg-primary/10 file:text-primary mt-1 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
                            </div>
                        </div>
                    </details>
                </div>
            </div>

            <p id="category-form-error" class="mt-3 hidden text-xs text-red-500"></p>

            <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
                <button type="button" onclick="closeCategoryModal()"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">{{ 'Batal' }}</button>
                <button type="submit"
                    class="bg-primary shadow-primary/20 hover:bg-primary-600 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg">{{ 'Simpan Kategori' }}</button>
            </div>
        </form>
    </x-modal>

    {{-- =========================
         Modal: Minta Edit ke Admin
         ========================= --}}
    <x-modal name="category-request-edit-modal" maxWidth="md" desktopLayout="centered">
        <h3 class="font-display text-charcoal text-lg font-bold">{{ 'Minta Edit ke Admin' }}</h3>
        <p class="mt-1 text-xs text-gray-500" id="request-edit-target"></p>
        <form id="request-edit-form" class="mt-4 space-y-3">
            @csrf
            <textarea name="note" id="request-edit-note" rows="4" required maxlength="1000"
                placeholder="{{ 'Misal: tolong ganti harga jadi Rp 25.000, atau perbarui foto kategori...' }}"
                class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"></textarea>
            <p id="request-edit-error" class="hidden text-xs text-red-500"></p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeRequestEditModal()"
                    class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-500 hover:bg-gray-50">{{ 'Batal' }}</button>
                <button type="submit"
                    class="bg-primary hover:bg-primary-600 rounded-xl px-4 py-2 text-sm font-semibold text-white">{{ 'Kirim Permintaan' }}</button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    <script>
        const productForm = document.getElementById('modal-form');
        const productModalTitle = document.getElementById('modal-title');
        const methodContainer = document.getElementById('method-container');
        const fieldPrice = document.getElementById('field-price');
        const fieldUnit  = document.getElementById('field-unit');
        const fieldStock = document.getElementById('field-stock');
        const fieldActive = document.getElementById('field-active');
        const fieldProductId = document.getElementById('field-product-id');

        // -------- Alpine component: searchable category picker --------
        function categoryPicker(initialList) {
            return {
                items: initialList,
                query: '',
                open: false,
                selectedId: '',
                selected: null,
                filtered() {
                    const q = (this.query || '').toLowerCase().trim();
                    if (!q) return this.items;
                    return this.items.filter(c => (c.name || '').toLowerCase().includes(q));
                },
                select(c) {
                    this.selected = c;
                    this.selectedId = c.id;
                    this.query = c.name;
                    this.open = false;
                },
                selectById(id) {
                    const c = this.items.find(x => x.id === id);
                    if (c) this.select(c);
                },
                openCreate() {
                    this.open = false;
                    openCategoryCreate();
                },
                addAndSelect(c) {
                    this.items.unshift(c);
                    this.select(c);
                },
            };
        }

        function openCreateModal() {
            productModalTitle.innerText = "{{ 'Tambah Produk UMKM' }}";
            productForm.action = "{{ route('owner.products.store') }}";
            methodContainer.innerHTML = "";
            productForm.reset();
            fieldProductId.value = "";

            const root = Alpine.$data(productForm);
            root.selected = null; root.selectedId = ''; root.query = '';

            window.dispatchEvent(new CustomEvent('open-product-modal'));
        }

        function openEditModal(product) {
            productModalTitle.innerText = "{{ 'Edit Produk UMKM' }}";
            productForm.action = `/owner/products/${product.id}`;
            methodContainer.innerHTML = `@method('PUT')`;
            fieldProductId.value = product.id;
            fieldPrice.value  = product.price !== null ? product.price : '';
            fieldUnit.value   = product.unit ?? '';
            fieldStock.value  = product.stock !== null ? product.stock : '';
            fieldActive.checked = product.is_active;

            const root = Alpine.$data(productForm);
            root.selectById(product.umkm_product_category_id);

            window.dispatchEvent(new CustomEvent('open-product-modal'));
        }

        function closeProductModal() {
            window.dispatchEvent(new CustomEvent('close-product-modal'));
        }

        // -------- Category create/edit modal --------
        const categoryForm = document.getElementById('category-form');
        const categoryModalTitle = document.getElementById('category-modal-title');
        const categoryFormError = document.getElementById('category-form-error');
        const categoryFormId = document.getElementById('category-form-id');

        function openCategoryCreate() {
            categoryModalTitle.innerText = "{{ 'Tambah Kategori Baru' }}";
            categoryForm.reset();
            categoryForm.action = "{{ route('owner.categories.store') }}";
            categoryFormId.value = "";
            categoryFormError.classList.add('hidden');
            window.dispatchEvent(new CustomEvent('open-category-form-modal'));
        }

        function openCategoryEdit(id) {
            const cat = Alpine.$data(productForm).items.find(c => c.id === id);
            if (!cat) return;
            categoryModalTitle.innerText = "{{ 'Ubah Kategori' }}";
            categoryForm.reset();
            categoryForm.action = `/owner/categories/${id}`;
            categoryFormId.value = id;
            document.getElementById('cat-name-en').value = cat.name_translations?.en || cat.name || '';
            document.getElementById('cat-name-id').value = cat.name_translations?.id || cat.name || '';
            document.getElementById('cat-desc-en').value = cat.description_translations?.en || '';
            document.getElementById('cat-desc-id').value = cat.description_translations?.id || '';
            document.getElementById('cat-price').value = cat.price !== null ? cat.price : '';
            document.getElementById('cat-unit').value = cat.unit || '';
            categoryFormError.classList.add('hidden');
            window.dispatchEvent(new CustomEvent('open-category-form-modal'));
        }

        function closeCategoryModal() {
            window.dispatchEvent(new CustomEvent('close-category-form-modal'));
        }

        categoryForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            categoryFormError.classList.add('hidden');
            const formData = new FormData(categoryForm);
            try {
                const res = await fetch(categoryForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', Accept: 'application/json' },
                });
                if (!res.ok) {
                    const errJson = await res.json().catch(() => null);
                    const firstErr = errJson?.errors ? Object.values(errJson.errors).flat()[0] : (errJson?.message || res.statusText);
                    categoryFormError.innerText = firstErr || '{{ 'Gagal menyimpan kategori.' }}';
                    categoryFormError.classList.remove('hidden');
                    return;
                }
                const data = await res.json();
                const editing = !!categoryFormId.value;
                closeCategoryModal();
                if (editing) {
                    window.location.reload();
                } else {
                    Alpine.$data(productForm).addAndSelect({
                        id: data.id,
                        name: data.name,
                        name_translations: data.name_translations,
                        description_translations: {},
                        price: null,
                        unit: null,
                        image_path: null,
                        editable_by_me: true,
                    });
                }
            } catch (err) {
                categoryFormError.innerText = '{{ 'Terjadi kesalahan jaringan.' }}';
                categoryFormError.classList.remove('hidden');
            }
        });

        // -------- Request edit modal --------
        const requestEditForm = document.getElementById('request-edit-form');
        const requestEditTarget = document.getElementById('request-edit-target');
        const requestEditError = document.getElementById('request-edit-error');
        let currentRequestEditCategoryId = null;

        function openCategoryRequestEdit(id, name) {
            currentRequestEditCategoryId = id;
            requestEditTarget.innerText = "{{ 'Kategori:' }} " + name;
            requestEditForm.reset();
            requestEditError.classList.add('hidden');
            window.dispatchEvent(new CustomEvent('open-category-request-edit-modal'));
        }

        function closeRequestEditModal() {
            window.dispatchEvent(new CustomEvent('close-category-request-edit-modal'));
        }

        requestEditForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            requestEditError.classList.add('hidden');
            const formData = new FormData(requestEditForm);
            try {
                const res = await fetch(`/owner/categories/${currentRequestEditCategoryId}/request-edit`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', Accept: 'application/json' },
                });
                if (!res.ok) {
                    const errJson = await res.json().catch(() => null);
                    const firstErr = errJson?.errors ? Object.values(errJson.errors).flat()[0] : (errJson?.message || res.statusText);
                    requestEditError.innerText = firstErr || '{{ 'Gagal mengirim permintaan.' }}';
                    requestEditError.classList.remove('hidden');
                    return;
                }
                closeRequestEditModal();
                Swal.fire({
                    title: '{{ 'Permintaan terkirim' }}',
                    text: '{{ 'Admin akan menerima notifikasi dan menindaklanjuti permintaan Anda.' }}',
                    icon: 'success',
                    confirmButtonColor: '#1E5128',
                });
            } catch (err) {
                requestEditError.innerText = '{{ 'Terjadi kesalahan jaringan.' }}';
                requestEditError.classList.remove('hidden');
            }
        });

        // Re-open product modal on validation error
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function () {
                window.dispatchEvent(new CustomEvent('open-product-modal'));
            });
        @endif
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
                    description: 'Panduan ini akan menunjukkan cara mengelola katalog produk toko UMKM Anda, mulai dari menambah produk hingga mengatur kategori.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 2: Tombol Tambah Produk
            if (document.getElementById('tour-add-btn')) {
                steps.push({
                    element: '#tour-add-btn',
                    popover: {
                        title: '➕ Tambah Produk Baru',
                        description: 'Klik tombol ini untuk menambahkan produk baru. Anda bisa memilih kategori yang sudah ada atau membuat kategori baru langsung dari sini.',
                        side: 'bottom',
                        align: 'end'
                    }
                });
            }

            // Langkah 3: Cari & Filter Kategori
            if (document.getElementById('tour-search-filter')) {
                steps.push({
                    element: '#tour-search-filter',
                    popover: {
                        title: '🔍 Cari & Saring Produk',
                        description: 'Gunakan kolom pencarian atau dropdown ini untuk menyaring produk berdasarkan nama kategori dengan cepat.',
                        side: 'bottom',
                        align: 'start'
                    }
                });
            }

            if (hasCard) {
                // Langkah 4: Kartu Produk
                steps.push({
                    element: '#tour-first-card',
                    popover: {
                        title: '🛍️ Kartu Produk',
                        description: 'Setiap produk ditampilkan dalam kartu seperti ini, lengkap dengan foto, kategori, status aktif, harga, dan sisa stok.',
                        side: 'top',
                        align: 'start'
                    }
                });

                // Langkah 5: Aksi Ubah & Hapus
                steps.push({
                    element: '#tour-card-actions',
                    popover: {
                        title: '⚙️ Ubah atau Hapus Produk',
                        description: 'Gunakan tombol "Ubah" untuk mengganti kategori, harga, satuan, atau stok produk. Tombol hapus akan menghapus produk secara permanen.',
                        side: 'top',
                        align: 'start'
                    }
                });
            } else {
                // Langkah Alternatif jika belum ada produk
                steps.push({
                    element: '#tour-empty-state',
                    popover: {
                        title: '📭 Belum Ada Produk',
                        description: 'Setelah Anda menambahkan produk pertama, kartu produk akan muncul di area ini.',
                        side: 'top',
                        align: 'start'
                    }
                });
            }

            // Langkah: Kategori yang Saya Pakai
            if (document.getElementById('tour-my-categories')) {
                steps.push({
                    element: '#tour-my-categories',
                    popover: {
                        title: '🏷️ Kategori yang Saya Pakai',
                        description: 'Daftar kategori yang digunakan oleh produk Anda. Jika kategori tersebut hanya dipakai oleh produk Anda sendiri, Anda bisa mengubahnya langsung. Jika dipakai pemilik toko lain juga, ajukan permintaan edit ke admin.',
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
            const tourCompleted = localStorage.getItem('owner_products_tour_completed');
            if (!tourCompleted) {
                setTimeout(() => {
                    startTutorial();
                    localStorage.setItem('owner_products_tour_completed', 'true');
                }, 1000);
            }
        });
    </script>
@endpush
