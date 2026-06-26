@extends('layouts.dashboard')

@section('title', 'Informasi Toko UMKM')

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Atur Informasi Toko</h1>
        <p class="mt-1 text-sm text-gray-500">Kelola identitas, deskripsi, dan kategori toko UMKM Anda yang akan ditampilkan
            di peta dan halaman jelajah.</p>
    </div>

    <div class="max-w-2xl rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('owner.profile.update') }}" x-data="{ locale: 'en' }">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Locale tabs --}}
                <x-locale-toggle />

                {{-- Business Name --}}
                <div x-show="locale === 'en'">
                    <label class="block text-sm font-semibold text-gray-700">Business Name (EN) <span
                            class="text-warning">*</span></label>
                    <input type="text" name="business_name[en]" required
                        value="{{ old('business_name.en', $profile ? $profile->getTranslation('business_name', 'en', false) : '') }}"
                        placeholder="Example: Penglipuran Coffee Shop, Beautiful Knits"
                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">
                    <p class="mt-1.5 text-xs text-gray-400">Your unique business name in English.</p>
                    @error('business_name.en')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div x-show="locale === 'id'">
                    <label class="block text-sm font-semibold text-gray-700">Nama Toko / Bisnis (ID) <span
                            class="text-warning">*</span></label>
                    <input type="text" name="business_name[id]" required
                        value="{{ old('business_name.id', $profile ? $profile->getTranslation('business_name', 'id', false) : '') }}"
                        placeholder="Contoh: Warung Kopi Penglipuran, Rajutan Indah"
                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">
                    <p class="mt-1.5 text-xs text-gray-400">Nama toko Anda yang unik dan mudah diingat oleh wisatawan.</p>
                    @error('business_name.id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div x-show="locale === 'en'">
                    <label class="block text-sm font-semibold text-gray-700">Store Description (EN)</label>
                    <textarea name="description[en]" rows="5"
                        placeholder="Write a short story of your shop, featured products, or operational hours..."
                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">{{ old('description.en', $profile ? $profile->getTranslation('description', 'en', false) : '') }}</textarea>
                    <p class="mt-1.5 text-xs text-gray-400">Describe the uniqueness of your shop in English to attract tourists.</p>
                    @error('description.en')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div x-show="locale === 'id'">
                    <label class="block text-sm font-semibold text-gray-700">Deskripsi Toko (ID)</label>
                    <textarea name="description[id]" rows="5"
                        placeholder="Tuliskan cerita singkat toko Anda, produk unggulan, atau jam operasional..."
                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">{{ old('description.id', $profile ? $profile->getTranslation('description', 'id', false) : '') }}</textarea>
                    <p class="mt-1.5 text-xs text-gray-400">Gambarkan keunikan toko Anda untuk menarik minat kunjungan wisatawan.</p>
                    @error('description.id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

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