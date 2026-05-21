@extends('layouts.admin')

@section('title', 'UMKM')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">UMKM Desa</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola produk dan toko UMKM lokal Desa Penglipuran.</p>
    </div>
    <button onclick="openCreateModal()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
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
            ['label' => 'Total UMKM',    'value' => $totalProfiles,   'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5'],
            ['label' => 'Total Produk',  'value' => $totalProducts,  'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
            ['label' => 'Terjual Bulan Ini', 'value' => $totalSoldThisMonth, 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ];
    @endphp
    @foreach ($umkmStats as $s)
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                    <svg class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-charcoal">{{ $s['value'] }}</p>
                    <p class="text-xs text-gray-500">{{ $s['label'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Search + Filter --}}
<form method="GET" action="{{ route('admin.umkm') }}" class="mb-4 flex flex-col gap-3 sm:flex-row">
    <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk atau toko UMKM..." class="w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
    </div>
    <select name="category" onchange="this.form.submit()" class="rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-primary focus:outline-none">
        <option value="Semua Kategori">Semua Kategori</option>
        @foreach(['Kerajinan', 'Kuliner', 'Tekstil', 'Minuman'] as $cat)
            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
    </select>
</form>

{{-- Product Table --}}
<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Produk</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Toko</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Kategori</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Harga</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Stok</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($products as $p)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4 font-medium text-charcoal">
                            <div>
                                <p>{{ $p->name }}</p>
                                @if(!$p->is_active)
                                    <span class="inline-block mt-0.5 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-400">Nonaktif</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-500">{{ $p->umkmProfile->business_name ?? 'Lokal' }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-lg bg-secondary/10 px-2.5 py-1 text-xs font-semibold text-secondary-700">{{ $p->umkmProfile->category ?? 'Lainnya' }}</span>
                        </td>
                        <td class="px-5 py-4 font-semibold text-charcoal">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                        <td class="px-5 py-4">
                            @if ($p->stock <= 5)
                                <span class="rounded-full bg-warning/10 px-2.5 py-0.5 text-xs font-bold text-warning">{{ $p->stock }} — Stok Rendah</span>
                            @else
                                <span class="text-gray-600">{{ $p->stock }} {{ $p->unit ?? 'pcs' }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <button onclick="openEditModal({{ json_encode($p) }})" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-primary/10 hover:text-primary" title="Edit">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('admin.umkm.destroy', $p->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')" class="inline">
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
                        <td colspan="6" class="px-5 py-8 text-center text-gray-400">Belum ada data produk UMKM.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="border-t border-gray-100 px-5 py-3.5">
            {{ $products->links() }}
        </div>
    @endif
</div>

{{-- Product Modal Form --}}
<div id="product-modal" class="fixed inset-0 z-50 items-center justify-center hidden bg-charcoal/50 backdrop-blur-sm p-4">
    <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl transition-all">
        <div class="mb-4 flex items-center justify-between">
            <h3 id="modal-title" class="font-display text-lg font-bold text-charcoal">Tambah Produk UMKM</h3>
            <button onclick="closeModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="modal-form" method="POST" action="">
            @csrf
            <div id="method-container"></div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama Produk <span class="text-warning">*</span></label>
                    <input type="text" name="name" id="field-name" required class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Toko / UMKM Profil <span class="text-warning">*</span></label>
                    <select name="umkm_profile_id" id="field-profile" required class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                        @foreach ($profiles as $profile)
                            <option value="{{ $profile->id }}">{{ $profile->business_name }} ({{ $profile->owner_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Harga (Rp) <span class="text-warning">*</span></label>
                        <input type="number" name="price" id="field-price" required class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Stok</label>
                        <input type="number" name="stock" id="field-stock" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Satuan (Unit)</label>
                        <input type="text" name="unit" id="field-unit" placeholder="pcs, porsi, bungkus" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">File AR Model 3D (.glb path)</label>
                        <input type="text" name="ar_model_path" id="field-ar-model" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                    <textarea name="description" id="field-desc" rows="3" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="field-active" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label class="text-sm font-semibold text-gray-700">Produk Aktif / Ditampilkan</label>
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
    const modal = document.getElementById('product-modal');
    const form = document.getElementById('modal-form');
    const modalTitle = document.getElementById('modal-title');
    const methodContainer = document.getElementById('method-container');

    function openCreateModal() {
        modalTitle.innerText = "Tambah Produk UMKM";
        form.action = "{{ route('admin.umkm.store') }}";
        methodContainer.innerHTML = "";
        
        document.getElementById('field-name').value = "";
        document.getElementById('field-price').value = "";
        document.getElementById('field-stock').value = "";
        document.getElementById('field-unit').value = "pcs";
        document.getElementById('field-ar-model').value = "";
        document.getElementById('field-desc').value = "";
        document.getElementById('field-active').checked = true;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openEditModal(prod) {
        modalTitle.innerText = "Edit Produk UMKM";
        form.action = `/admin/umkm/products/${prod.id}`;
        methodContainer.innerHTML = `@method('PUT')`;

        document.getElementById('field-name').value = prod.name;
        document.getElementById('field-profile').value = prod.umkm_profile_id;
        document.getElementById('field-price').value = Math.round(prod.price);
        document.getElementById('field-stock').value = prod.stock;
        document.getElementById('field-unit').value = prod.unit || "pcs";
        document.getElementById('field-ar-model').value = prod.ar_model_path || "";
        document.getElementById('field-desc').value = prod.description || "";
        document.getElementById('field-active').checked = prod.is_active;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
