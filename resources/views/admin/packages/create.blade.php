@extends('layouts.admin')

@section('title', isset($package) ? 'Edit Paket Wisata' : 'Tambah Paket Wisata')

@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.packages') }}" class="rounded-xl p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-charcoal">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">{{ isset($package) ? 'Edit Paket Wisata' : 'Tambah Paket Wisata Baru' }}</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ isset($package) ? 'Ubah detail paket wisata dan penawaran harga.' : 'Buat paket baru untuk ditawarkan kepada wisatawan.' }}</p>
    </div>
</div>

<form action="{{ isset($package) ? route('admin.packages.update', $package->id) : route('admin.packages.store') }}" method="POST" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    @csrf
    @if(isset($package))
        @method('PUT')
    @endif

    <div class="space-y-5 lg:col-span-2">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 font-semibold text-charcoal">Detail Paket</h2>
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Paket <span class="text-warning">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $package->name ?? '') }}" placeholder="Contoh: Paket Keluarga 1 Hari"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi</label>
                    <textarea name="description" rows="3" placeholder="Jelaskan apa saja yang termasuk dalam paket ini..."
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 resize-none">{{ old('description', $package->description ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Harga per Orang <span class="text-warning">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">Rp</span>
                            <input type="number" name="price" value="{{ old('price', $package->price ?? '') }}" placeholder="85000"
                                class="w-full rounded-xl border border-gray-200 py-3 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30" required>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Durasi (Jam)</label>
                        <input type="number" step="any" name="duration_hours" value="{{ old('duration_hours', $package->duration_hours ?? '') }}" placeholder="Contoh: 8"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 font-semibold text-charcoal">Yang Termasuk dalam Paket (Inclusions)</h2>
            <div id="includes-list" class="space-y-2">
                @if(isset($package) && is_array($package->inclusions) && count($package->inclusions) > 0)
                    @foreach($package->inclusions as $inc)
                        <div class="flex items-center gap-2">
                            <input type="text" name="inclusions[]" value="{{ $inc }}" placeholder="Contoh: Tiket masuk desa" class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                            <button type="button" onclick="this.parentElement.remove()" class="h-10 w-10 rounded-xl bg-gray-100 text-gray-400 hover:text-warning">
                                <svg class="mx-auto h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                @else
                    <div class="flex items-center gap-2">
                        <input type="text" name="inclusions[]" placeholder="Contoh: Tiket masuk desa" class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                        <button type="button" onclick="this.parentElement.remove()" class="h-10 w-10 rounded-xl bg-gray-100 text-gray-400 hover:text-warning">
                            <svg class="mx-auto h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
            <button type="button" onclick="addInclude()"
                class="mt-3 flex items-center gap-2 text-sm font-semibold text-primary hover:underline">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Item
            </button>
        </div>
    </div>

    <div class="space-y-5">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold text-charcoal">Pengaturan</h2>
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Maks. Peserta / Sesi (opsional)</label>
                    <input type="number" name="max_capacity" value="{{ old('max_capacity', $package->max_capacity ?? '') }}" placeholder="Contoh: 20"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                </div>
                <div class="flex items-center gap-2 py-1">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="is_active" class="text-sm font-semibold text-gray-700">Aktifkan Paket Wisata</label>
                </div>
            </div>
        </div>
        <button type="submit"
            class="w-full rounded-xl bg-primary py-3 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
            Simpan Paket
        </button>
        <a href="{{ route('admin.packages') }}"
            class="block w-full rounded-xl border border-gray-200 py-3 text-center text-sm font-semibold text-gray-500 transition-all hover:bg-gray-50">
            Batal
        </a>
    </div>
</form>

@endsection

@push('scripts')
<script>
    function addInclude() {
        const list = document.getElementById('includes-list');
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';
        row.innerHTML = `
            <input type="text" name="inclusions[]" placeholder="Tambahkan item..." class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            <button type="button" onclick="this.parentElement.remove()" class="h-10 w-10 rounded-xl bg-gray-100 text-gray-400 hover:text-warning">
                <svg class="mx-auto h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>`;
        list.appendChild(row);
        row.querySelector('input').focus();
    }
</script>
@endpush
