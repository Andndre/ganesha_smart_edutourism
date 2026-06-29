@extends('layouts.dashboard')

@section('title', 'UMKM')



@section('content')

    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-charcoal text-2xl font-bold">UMKM Desa</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola produk dan toko UMKM lokal Desa Penglipuran.</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Produk
        </button>
    </div>

    {{-- Summary Stats --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        @php
            $umkmStats = [
                [
                    'label' => 'Total UMKM',
                    'value' => $totalProfiles,
                    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5',
                ],
                [
                    'label' => 'Total Produk',
                    'value' => $totalProducts,
                    'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                ],
                [
                    'label' => 'Terjual Bulan Ini',
                    'value' => $totalSoldThisMonth,
                    'icon' =>
                        'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                ],
            ];
        @endphp
        @foreach ($umkmStats as $s)
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl">
                        <svg class="text-primary h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-charcoal text-xl font-bold">{{ $s['value'] }}</p>
                        <p class="text-xs text-gray-500">{{ $s['label'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Search + Filter --}}
    <form method="GET" action="{{ route('admin.umkm') }}" class="mb-4 flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari produk atau toko UMKM...') }}"
                class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm focus:outline-none focus:ring-1">
        </div>
        <select name="category" onchange="this.form.submit()"
            class="focus:border-primary rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none">
            <option value="Semua Kategori">{{ __('Semua Kategori') }}</option>
            @foreach (['Kerajinan', 'Kuliner', 'Tekstil', 'Minuman'] as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ __($cat) }}
                </option>
            @endforeach
        </select>
    </form>

    {{-- Product Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Produk</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Toko
                        </th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Kategori</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Harga
                        </th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Stok
                        </th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($products as $p)
                        <tr class="hover:bg-gray-50/50">
                            <td class="text-charcoal px-5 py-4 font-medium">
                                <div>
                                    <p>{{ $p->display_name }}</p>
                                    @if (!$p->is_active)
                                        <span
                                            class="mt-0.5 inline-block rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-400">Nonaktif</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-500">{{ $p->umkmProfile->business_name ?? 'Lokal' }}</td>
                            <td class="px-5 py-4">
                                <span
                                    class="bg-primary/10 text-primary-800 rounded-lg px-2.5 py-1 text-xs font-semibold">{{ translateValue($p->category?->name) ?? 'Lainnya' }}</span>
                            </td>
                            <td class="text-charcoal px-5 py-4 font-semibold">
                                @if ($p->display_price !== null)
                                    Rp {{ number_format($p->display_price, 0, ',', '.') }}
                                @else
                                    <span class="text-xs italic text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($p->stock !== null && $p->stock <= 5)
                                    <span
                                        class="bg-warning/10 text-warning rounded-full px-2.5 py-0.5 text-xs font-bold">{{ $p->stock }}
                                        — Stok Rendah</span>
                                @elseif ($p->stock === null)
                                    <span class="text-gray-400 text-xs italic">{{ __('Tidak terbatas') }}</span>
                                @else
                                    <span class="text-gray-600">{{ $p->stock }} {{ $p->display_unit }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="openEditModal({{ json_encode([
                                        'id' => $p->id,
                                        'name' => $p->getTranslations('name'),
                                        'description' => $p->getTranslations('description'),
                                        'umkm_product_category_id' => $p->umkm_product_category_id,
                                        'umkm_profile_id' => $p->umkm_profile_id,
                                        'price' => $p->price,
                                        'stock' => $p->stock,
                                        'unit' => $p->unit,
                                        'images' => $p->images,
                                        'is_active' => $p->is_active,
                                    ]) }})"
                                        class="hover:bg-primary/10 hover:text-primary rounded-lg p-1.5 text-gray-400 transition-colors"
                                        title="Edit">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.umkm.destroy', $p->id) }}"
                                        class="delete-form inline"
                                        data-confirm="{{ __('Apakah Anda yakin ingin menghapus produk ini?') }}">
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
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400">Belum ada data produk UMKM.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="border-t border-gray-100 px-5 py-3.5">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    {{-- Product Modal Form --}}
    <x-modal name="product-modal" maxWidth="xl" desktopLayout="drawer">
        <div class="mb-4">
            <h3 id="modal-title" class="font-display text-charcoal text-lg font-bold">Tambah Produk UMKM</h3>
        </div>
        <form id="modal-form" method="POST" action="" enctype="multipart/form-data" x-data="{ locale: 'en' }">
            @csrf
            <div id="method-container"></div>
            <input type="hidden" name="product_id" id="field-product-id" value="">
            <div class="space-y-4">
                {{-- Locale tabs --}}
                <div class="sticky top-0 z-10 bg-white py-2.5 border-b border-gray-100 mb-4 flex gap-2">
                    <button @click="locale = 'en'" :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" type="button">English</button>
                    <button @click="locale = 'id'" :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" type="button">Indonesia</button>
                </div>

                {{-- Name --}}
                <div x-show="locale === 'en'">
                    <label class="block text-sm font-semibold text-gray-700">Product Name (EN) <span
                            class="text-warning">*</span></label>
                    <input type="text" name="name[en]" id="field-name-en" required
                        placeholder="e.g. Traditional Handwoven Fabric"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @error('name.en')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div x-show="locale === 'id'">
                    <label class="block text-sm font-semibold text-gray-700">Nama Produk (ID) <span
                            class="text-warning">*</span></label>
                    <input type="text" name="name[id]" id="field-name-id" required
                        placeholder="Contoh: Kain Tenun Tradisional"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @error('name.id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Kategori Produk <span
                            class="text-warning">*</span></label>
                    <select name="umkm_product_category_id" id="field-category" required
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                        <option value="" disabled selected>Pilih Kategori...</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('umkm_product_category_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Toko / UMKM Profil <span
                            class="text-warning">*</span></label>
                    <select name="umkm_profile_id" id="field-profile" required
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                        @foreach ($profiles as $profile)
                            <option value="{{ $profile->id }}">{{ $profile->business_name }}
                                ({{ $profile->owner_name }})</option>
                        @endforeach
                    </select>
                    @error('umkm_profile_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Harga (Rp) <span
                                class="text-warning">*</span></label>
                        <input type="number" name="price" id="field-price" required
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                        @error('price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Stok</label>
                        <input type="number" name="stock" id="field-stock" min="0"
                            placeholder="{{ __('Kosongkan jika tidak terbatas') }}"
                            class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                        @error('stock')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Satuan (Unit)</label>
                    <input type="text" name="unit" id="field-unit" placeholder="pcs, porsi, bungkus"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @error('unit')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Foto Produk (PNG, JPG, dll. - Bisa pilih
                        banyak)</label>
                    <input type="file" name="images[]" id="field-images" accept="image/*" multiple
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @error('images.*')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    <div id="current-images-container" class="mt-2 hidden">
                        <p class="mb-1 text-xs font-semibold text-gray-700">Foto saat ini:</p>
                        <div id="current-images-list" class="flex flex-wrap gap-2"></div>
                    </div>
                </div>

                {{-- Description --}}
                <div x-show="locale === 'en'">
                    <label class="block text-sm font-semibold text-gray-700">Description (EN)</label>
                    <textarea name="description[en]" id="field-desc-en" rows="3"
                        placeholder="Product description in English..."
                        class="focus:border-primary mt-1 w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"></textarea>
                    @error('description.en')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div x-show="locale === 'id'">
                    <label class="block text-sm font-semibold text-gray-700">Deskripsi (ID)</label>
                    <textarea name="description[id]" id="field-desc-id" rows="3"
                        placeholder="Deskripsi produk dalam Bahasa Indonesia..."
                        class="focus:border-primary mt-1 w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"></textarea>
                    @error('description.id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="field-active" value="1" checked
                        class="text-primary focus:ring-primary rounded border-gray-300">
                    <label class="text-sm font-semibold text-gray-700">Produk Aktif / Ditampilkan</label>
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
        const storageUrl = "{{ asset('storage') }}";



        function openCreateModal() {
            modalTitle.innerText = "Tambah Produk UMKM";
            form.action = "{{ route('admin.umkm.store') }}";
            methodContainer.innerHTML = "";

            document.getElementById('field-product-id').value = "";
            document.getElementById('field-name-en').value = "";
            document.getElementById('field-name-id').value = "";
            document.getElementById('field-category').value = "";
            document.getElementById('field-price').value = "";
            document.getElementById('field-stock').value = "";
            document.getElementById('field-unit').value = "pcs";
            document.getElementById('field-images').value = "";
            document.getElementById('field-desc-en').value = "";
            document.getElementById('field-desc-id').value = "";
            document.getElementById('field-active').checked = true;

            document.getElementById('current-images-container').classList.add('hidden');

            window.dispatchEvent(new CustomEvent('open-product-modal'));
        }

        function openEditModal(prod) {
            modalTitle.innerText = "Edit Produk UMKM";
            form.action = `/admin/umkm/products/${prod.id}`;
            methodContainer.innerHTML = `@method('PUT')`;

            document.getElementById('field-product-id').value = prod.id;
            
            // Handle translatable object or fallback string safely
            document.getElementById('field-name-en').value = (typeof prod.name === 'object') ? (prod.name?.en || "") : prod.name;
            document.getElementById('field-name-id').value = (typeof prod.name === 'object') ? (prod.name?.id || "") : prod.name;
            
            document.getElementById('field-category').value = prod.umkm_product_category_id || "";
            document.getElementById('field-profile').value = prod.umkm_profile_id;
            document.getElementById('field-price').value = Math.round(prod.price);
            document.getElementById('field-stock').value = prod.stock !== null ? prod.stock : '';
            document.getElementById('field-unit').value = prod.unit || "pcs";
            
            document.getElementById('field-desc-en').value = (typeof prod.description === 'object') ? (prod.description?.en || "") : (prod.description || "");
            document.getElementById('field-desc-id').value = (typeof prod.description === 'object') ? (prod.description?.id || "") : (prod.description || "");
            
            document.getElementById('field-active').checked = prod.is_active;

            document.getElementById('field-images').value = "";

            // Images
            const imagesContainer = document.getElementById('current-images-container');
            const imagesList = document.getElementById('current-images-list');
            imagesList.textContent = ''; // clear old ones using safe textContent assignment

            if (prod.images && Array.isArray(prod.images) && prod.images.length > 0) {
                prod.images.forEach(img => {
                    const imgContainer = document.createElement('div');
                    imgContainer.className =
                        'relative group w-16 h-16 rounded-lg overflow-hidden border border-gray-200';

                    const imgEl = document.createElement('img');
                    imgEl.src = `/storage/${img}`;
                    imgEl.className = 'w-full h-full object-cover';

                    imgContainer.appendChild(imgEl);
                    imagesList.appendChild(imgContainer);
                });
                imagesContainer.classList.remove('hidden');
            } else {
                imagesContainer.classList.add('hidden');
            }

            window.dispatchEvent(new CustomEvent('open-product-modal'));
        }

        function closeModal() {
            window.dispatchEvent(new CustomEvent('close-product-modal'));
        }

        document.getElementById('field-images')?.addEventListener('change', function() {
            const maxSize = 2 * 1024 * 1024;
            const oversized = Array.from(this.files || []).find(f => f.size > maxSize);
            if (oversized) {
                Swal.fire({
                    title: 'Ukuran File Terlalu Besar',
                    text: 'Maksimal 2MB per foto.',
                    icon: 'warning',
                    confirmButtonColor: '#1E5128',
                    confirmButtonText: 'Mengerti',
                    background: '#ffffff'
                });
                this.value = '';
            }
        });

        @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            window.dispatchEvent(new CustomEvent('open-product-modal'));
            @if(old('_method') == 'PUT')
                form.action = "/admin/umkm/products/{{ old('product_id') }}";
                methodContainer.innerHTML = `@method('PUT')`;
                modalTitle.innerText = "Edit Produk UMKM";
                document.getElementById('field-product-id').value = "{{ old('product_id') }}";
            @else
                form.action = "{{ route('admin.umkm.store') }}";
                methodContainer.innerHTML = "";
                modalTitle.innerText = "Tambah Produk UMKM";
                document.getElementById('field-product-id').value = "";
            @endif

            document.getElementById('field-name-en').value = @json(old('name.en', ''));
            document.getElementById('field-name-id').value = @json(old('name.id', ''));
            document.getElementById('field-category').value = @json(old('umkm_product_category_id', ''));
            document.getElementById('field-profile').value = @json(old('umkm_profile_id', ''));
            document.getElementById('field-price').value = @json(old('price', ''));
            document.getElementById('field-stock').value = @json(old('stock', ''));
            document.getElementById('field-unit').value = @json(old('unit', 'pcs'));
            document.getElementById('field-desc-en').value = @json(old('description.en', ''));
            document.getElementById('field-desc-id').value = @json(old('description.id', ''));
            document.getElementById('field-active').checked = {{ old('is_active') ? 'true' : 'false' }};
        });
        @endif


    </script>
@endpush
