@extends('layouts.dashboard')

@section('title', 'Kategori Produk UMKM')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Kategori Produk UMKM</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola kategori produk yang dapat digunakan oleh pemilik UMKM.</p>
    </div>
    <button onclick="openCreateModal()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Kategori
    </button>
</div>

{{-- Categories Table --}}
<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm max-w-4xl">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nama Kategori</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Slug</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Total Produk</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($categories as $cat)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 shrink-0 overflow-hidden rounded-xl border border-gray-100 bg-gray-50 flex items-center justify-center text-primary">
                                    @if($cat->image_path)
                                        <img src="{{ asset('storage/' . $cat->image_path) }}" alt="{{ $cat->name }}" class="h-full w-full object-cover">
                                    @else
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-semibold text-charcoal">{{ $cat->name }}</div>
                                    @if($cat->description)
                                        <div class="text-xs text-gray-400 mt-0.5 max-w-xs truncate" title="{{ $cat->description }}">{{ $cat->description }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-500 font-mono text-xs">{{ $cat->slug }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary-800">{{ $cat->products_count }} produk</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <button onclick="openEditModal({{ json_encode($cat) }})" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-primary/10 hover:text-primary" title="Edit">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('admin.umkm.categories.destroy', $cat->id) }}" class="delete-form inline" data-confirm="Apakah Anda yakin ingin menghapus kategori ini? Semua produk di dalamnya akan kehilangan kategori.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-warning/10 hover:text-warning" title="Hapus">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-gray-400">Belum ada data kategori produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Category Modal Form --}}
<div id="category-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-charcoal/50 backdrop-blur-sm p-4 justify-center">
    <div class="my-auto self-start w-full max-w-md rounded-2xl bg-white p-6 shadow-xl transition-all">
        <div class="mb-4 flex items-center justify-between">
            <h3 id="modal-title" class="font-display text-lg font-bold text-charcoal">Tambah Kategori Produk</h3>
            <button onclick="closeModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="modal-form" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div id="method-container"></div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama Kategori <span class="text-warning">*</span></label>
                    <input type="text" name="name" id="field-name" required placeholder="Contoh: Pakaian Adat, Makanan Ringan" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 font-display">Deskripsi</label>
                    <textarea name="description" id="field-description" placeholder="Deskripsi singkat tentang kategori..." rows="3" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 font-display">Gambar Kategori</label>
                    <input type="file" name="image" id="field-image" accept="image/*" class="mt-1 w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                    <span class="text-[10px] text-gray-400 mt-1 block">Format gambar (jpg, jpeg, png), maks 2MB.</span>
                    <div id="image-preview-container" class="mt-2.5 hidden">
                        <span class="text-[10px] font-bold text-primary uppercase tracking-wider block">Gambar Saat Ini:</span>
                        <div class="relative mt-1 h-20 w-32 overflow-hidden rounded-lg border border-gray-200">
                            <img id="image-preview" src="" alt="Pratinjau" class="h-full w-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
                <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary-600">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const modal = document.getElementById('category-modal');
    const form = document.getElementById('modal-form');
    const modalTitle = document.getElementById('modal-title');
    const methodContainer = document.getElementById('method-container');
    const fieldName = document.getElementById('field-name');
    const fieldDescription = document.getElementById('field-description');
    const fieldImage = document.getElementById('field-image');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');

    function openCreateModal() {
        modalTitle.innerText = "Tambah Kategori Produk";
        form.action = "{{ route('admin.umkm.categories.store') }}";
        methodContainer.innerHTML = "";
        fieldName.value = "";
        fieldDescription.value = "";
        fieldImage.value = "";
        imagePreviewContainer.classList.add('hidden');
        imagePreview.src = "";
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openEditModal(cat) {
        modalTitle.innerText = "Edit Kategori Produk";
        form.action = `/admin/umkm/categories/${cat.id}`;
        methodContainer.innerHTML = `@method('PUT')`;
        fieldName.value = cat.name;
        fieldDescription.value = cat.description || "";
        fieldImage.value = "";
        
        if (cat.image_path) {
            imagePreview.src = `/storage/${cat.image_path}`;
            imagePreviewContainer.classList.remove('hidden');
        } else {
            imagePreviewContainer.classList.add('hidden');
            imagePreview.src = "";
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
