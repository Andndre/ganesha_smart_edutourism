@extends('layouts.dashboard')

@section('title', 'Informasi Toko UMKM')

@section('content')
    <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div id="tour-header">
            <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Atur Informasi Toko</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola identitas, deskripsi, dan kategori toko UMKM Anda yang akan ditampilkan
                di peta dan halaman jelajah.</p>
        </div>
        <button id="tour-trigger-btn" onclick="startTutorial()" type="button"
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:bg-gray-100 active:scale-[0.98]"
            title="Panduan Interaktif">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </button>
    </div>

    <div class="max-w-2xl rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('owner.profile.update') }}" x-data="{ locale: 'en' }">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Locale tabs --}}
                <x-locale-toggle />

                {{-- Business Name --}}
                <div id="tour-business-name" x-show="locale === 'en'">
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
                <div id="tour-description" x-show="locale === 'en'">
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
                    <div id="tour-owner-info" class="rounded-xl bg-gray-50/80 p-4 border border-gray-100 text-xs text-gray-500 space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600">Nama Pemilik (Sesuai Akun):</span>
                            <span class="font-mono">{{ $profile->owner_name }}</span>
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
                <button id="tour-save-btn" type="submit"
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

@push('scripts')
    <script>
        function startTutorial() {
            const driver = window.driver.js.driver;
            const hasOwnerInfo = document.getElementById('tour-owner-info') !== null;
            const steps = [];

            // Langkah 1: Pengantar
            steps.push({
                element: '#tour-header',
                popover: {
                    title: '👋 Selamat Datang!',
                    description: 'Panduan ini akan menunjukkan cara mengatur informasi toko UMKM Anda agar tampil menarik di peta dan halaman jelajah wisatawan.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 2: Nama Toko
            steps.push({
                element: '#tour-business-name',
                popover: {
                    title: '🏪 Nama Toko',
                    description: 'Isi nama toko/bisnis Anda dalam Bahasa Inggris dan Indonesia. Gunakan tombol tab di atas untuk beralih antar bahasa.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 3: Deskripsi Toko
            steps.push({
                element: '#tour-description',
                popover: {
                    title: '📝 Deskripsi Toko',
                    description: 'Tuliskan cerita singkat toko Anda, produk unggulan, atau jam operasional untuk menarik minat wisatawan.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            if (hasOwnerInfo) {
                // Langkah 4: Info Pemilik & Rating
                steps.push({
                    element: '#tour-owner-info',
                    popover: {
                        title: 'ℹ️ Info Pemilik & Rating',
                        description: 'Bagian ini menampilkan nama pemilik sesuai akun dan rating toko Anda saat ini. Data ini bersifat otomatis dan tidak dapat diubah langsung di sini.',
                        side: 'top',
                        align: 'start'
                    }
                });
            }

            // Langkah 5: Simpan Perubahan
            steps.push({
                element: '#tour-save-btn',
                popover: {
                    title: '💾 Simpan Perubahan',
                    description: 'Setelah selesai mengisi informasi pada kedua bahasa, klik tombol ini untuk menyimpan perubahan profil toko Anda.',
                    side: 'top',
                    align: 'end'
                }
            });

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
            const tourCompleted = localStorage.getItem('owner_profile_tour_completed');
            if (!tourCompleted) {
                setTimeout(() => {
                    startTutorial();
                    localStorage.setItem('owner_profile_tour_completed', 'true');
                }, 1000);
            }
        });
    </script>
@endpush