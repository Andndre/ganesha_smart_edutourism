@extends('layouts.dashboard')

@section('title', 'Informasi Toko UMKM')

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Atur Informasi Toko</h1>
        <p class="mt-1 text-sm text-gray-500">Kelola identitas, deskripsi, dan kategori toko UMKM Anda yang akan ditampilkan
            di peta dan halaman jelajah.</p>
    </div>

    <div class="max-w-2xl rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('owner.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Business Name --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama Toko / Bisnis <span
                            class="text-warning">*</span></label>
                    <input type="text" name="business_name" required
                        value="{{ old('business_name', $profile->business_name ?? '') }}"
                        placeholder="Contoh: Warung Kopi Penglipuran, Rajutan Indah"
                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">
                    <p class="mt-1.5 text-xs text-gray-400">Nama toko Anda yang unik dan mudah diingat oleh wisatawan.</p>
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Kategori Bisnis <span
                            class="text-warning">*</span></label>
                    <select name="category" required
                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">
                        <option value="" disabled {{ !old('category', $profile->category ?? '') ? 'selected' : '' }}>Pilih
                            kategori...</option>
                        <option value="culinary" {{ old('category', $profile->category ?? '') === 'culinary' ? 'selected' : '' }}>Kuliner (Culinary)</option>
                        <option value="craft" {{ old('category', $profile->category ?? '') === 'craft' ? 'selected' : '' }}>
                            Kerajinan tangan (Craft)</option>
                        <option value="souvenir" {{ old('category', $profile->category ?? '') === 'souvenir' ? 'selected' : '' }}>Oleh-oleh / Cenderamata (Souvenir)</option>
                        <option value="service" {{ old('category', $profile->category ?? '') === 'service' ? 'selected' : '' }}>Jasa / Layanan Wisata (Service)</option>
                    </select>
                    <p class="mt-1.5 text-xs text-gray-400">Pilih kategori utama bisnis Anda agar wisatawan dapat memfilter
                        di peta jelajah.</p>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Deskripsi Toko</label>
                    <textarea name="description" rows="5"
                        placeholder="Tuliskan cerita singkat toko Anda, produk unggulan, atau jam operasional..."
                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">{{ old('description', $profile->description ?? '') }}</textarea>
                    <p class="mt-1.5 text-xs text-gray-400">Gambarkan keunikan toko Anda untuk menarik minat kunjungan
                        wisatawan.</p>
                </div>

                {{-- Auto-filled fields info --}}
                @if ($profile)
                    <div class="rounded-xl bg-gray-50/80 p-4 border border-gray-100 text-xs text-gray-500 space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Nama Pemilik (Sesuai Akun):</span>
                            <span class="font-mono">{{ $profile->owner_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">AR Marker ID:</span>
                            <span class="font-mono">{{ $profile->ar_marker_id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Rating Toko:</span>
                            <span class="font-semibold text-charcoal">{{ number_format($profile->rating ?? 5.0, 1) }} /
                                5.0</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-6">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection