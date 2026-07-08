@extends('layouts.dashboard')

@section('title', isset($package) ? 'Edit Paket Wisata' : 'Tambah Paket Wisata')

@section('content')

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.packages') }}"
            class="hover:text-charcoal rounded-xl p-2 text-gray-400 transition-colors hover:bg-gray-100">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="font-display text-charcoal text-2xl font-bold">
                {{ isset($package) ? 'Edit Paket Wisata' : 'Tambah Paket Wisata Baru' }}</h1>
            <p class="mt-0.5 text-sm text-gray-500">
                {{ isset($package) ? 'Ubah detail paket wisata dan penawaran harga.' : 'Buat paket baru untuk ditawarkan kepada wisatawan.' }}
            </p>
        </div>
    </div>

    <form action="{{ isset($package) ? route('admin.packages.update', $package->id) : route('admin.packages.store') }}"
        method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        @csrf
        @if (isset($package))
            @method('PUT')
        @endif

        <div class="space-y-5 lg:col-span-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-charcoal mb-5 font-semibold">Detail Paket</h2>
                <div class="space-y-4">
                    <div x-data="{ locale: 'id' }">
                        <div class="sticky top-0 z-10 mb-4 flex gap-2 border-b border-gray-100 bg-white py-3">
                            <button @click="locale = 'id'"
                                :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                                type="button"
                                class="rounded-xl px-4 py-2 text-sm font-semibold transition-all">Indonesia</button>
                            <button @click="locale = 'en'"
                                :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                                type="button"
                                class="rounded-xl px-4 py-2 text-sm font-semibold transition-all">English</button>
                        </div>

                        <div x-show="locale === 'en'">
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Name <span
                                    class="text-warning">*</span></label>
                            <input type="text" name="name[en]"
                                value="{{ old('name.en', isset($package) ? $package->getTranslation('name', 'en', false) : '') }}"
                                placeholder="e.g. Family Day Package"
                                class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-1"
                                required>
                            @error('name.en')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div x-show="locale === 'id'">
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Paket <span
                                    class="text-warning">*</span></label>
                            <input type="text" name="name[id]"
                                value="{{ old('name.id', isset($package) ? $package->getTranslation('name', 'id', false) : '') }}"
                                placeholder="Contoh: Paket Keluarga 1 Hari"
                                class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-1"
                                required>
                            @error('name.id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="locale === 'en'">
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Description</label>
                            <textarea name="description[en]" rows="3" placeholder="Describe what this package includes..."
                                class="focus:border-primary focus:ring-primary/30 w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-1">{{ old('description.en', isset($package) ? $package->getTranslation('description', 'en', false) : '') }}</textarea>
                            @error('description.en')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div x-show="locale === 'id'">
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi</label>
                            <textarea name="description[id]" rows="3" placeholder="Jelaskan apa saja yang termasuk dalam paket ini..."
                                class="focus:border-primary focus:ring-primary/30 w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-1">{{ old('description.id', isset($package) ? $package->getTranslation('description', 'id', false) : '') }}</textarea>
                            @error('description.id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Product Type <span
                                class="text-warning">*</span></label>
                        <select name="type"
                            class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-1"
                            required>
                            <option value="package" @selected(old('type', $package->type ?? 'package') === 'package')>Tour Package</option>
                            <option value="ticket" @selected(old('type', $package->type ?? 'package') === 'ticket')>Entrance Ticket</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Entrance tickets appear under the "Tiket Masuk" tab and skip package extras.</p>
                        @error('type')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Harga per Orang <span
                                    class="text-warning">*</span></label>
                            <div class="relative">
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">Rp</span>
                                <input type="number" name="price" value="{{ old('price', $package->price ?? '') }}"
                                    placeholder="85000"
                                    class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 py-3 pl-10 pr-4 text-sm focus:outline-none focus:ring-1"
                                    required>
                                @error('price')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Durasi (Jam)</label>
                            <input type="number" step="any" name="duration_hours"
                                value="{{ old('duration_hours', $package->duration_hours ?? '') }}" placeholder="Contoh: 8"
                                class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-1">
                            @error('duration_hours')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-charcoal mb-5 font-semibold">Yang Termasuk & Tidak Termasuk</h2>
                <div x-data="{ locale: 'id' }">
                    <div class="sticky top-0 z-10 mb-4 flex gap-2 border-b border-gray-100 bg-white py-3">
                        <button @click="locale = 'id'"
                            :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                            type="button"
                            class="rounded-xl px-4 py-2 text-sm font-semibold transition-all">Indonesia</button>
                        <button @click="locale = 'en'"
                            :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                            type="button"
                            class="rounded-xl px-4 py-2 text-sm font-semibold transition-all">English</button>
                    </div>

                    <div x-show="locale === 'en'">
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Inclusions (EN)</label>
                        <textarea name="inclusions[en]" rows="4"
                            placeholder="One item per line&#10;e.g.&#10;Village entry ticket&#10;Tour guide"
                            class="focus:border-primary focus:ring-primary/30 w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-1">{{ old('inclusions.en', isset($package) ? implode("\n", $package->getInclusionsForLocale('en')) : '') }}</textarea>
                        @error('inclusions.en')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">One item per line</p>
                    </div>
                    <div x-show="locale === 'id'">
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Inclusions (ID)</label>
                        <textarea name="inclusions[id]" rows="4"
                            placeholder="Satu item per baris&#10;Contoh:&#10;Tiket masuk desa&#10;Pemandu wisata"
                            class="focus:border-primary focus:ring-primary/30 w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-1">{{ old('inclusions.id', isset($package) ? implode("\n", $package->getInclusionsForLocale('id')) : '') }}</textarea>
                        @error('inclusions.id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Satu item per baris</p>
                    </div>

                    <hr class="my-4 border-gray-100">

                    <div x-show="locale === 'en'">
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Exclusions (EN)</label>
                        <textarea name="exclusions[en]" rows="3"
                            placeholder="What is NOT included&#10;e.g.&#10;Personal expenses&#10;Hotel pickup"
                            class="focus:border-primary focus:ring-primary/30 w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-1">{{ old('exclusions.en', isset($package) ? implode("\n", $package->getExclusionsForLocale('en')) : '') }}</textarea>
                        @error('exclusions.en')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">One item per line</p>
                    </div>
                    <div x-show="locale === 'id'">
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Exclusions (ID)</label>
                        <textarea name="exclusions[id]" rows="3"
                            placeholder="Yang TIDAK termasuk&#10;Contoh:&#10;Biaya pribadi&#10;Penjemputan hotel"
                            class="focus:border-primary focus:ring-primary/30 w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-1">{{ old('exclusions.id', isset($package) ? implode("\n", $package->getExclusionsForLocale('id')) : '') }}</textarea>
                        @error('exclusions.id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Satu item per baris</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h2 class="text-charcoal mb-4 font-semibold">Foto Paket Wisata</h2>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Upload Foto</label>
                        <input type="file" name="images[]" id="field-images" accept="image/*" multiple
                            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                        @error('images.*')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    @if (isset($package) && \is_array($package->images) && count($package->images) > 0)
                        <div>
                            <p class="mb-1.5 text-xs font-semibold text-gray-700">Foto saat ini:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($package->images as $img)
                                    <div
                                        class="group relative h-16 w-16 overflow-hidden rounded-lg border border-gray-200">
                                        <img src="{{ asset('storage/' . $img) }}" class="h-full w-full object-cover">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h2 class="text-charcoal mb-4 font-semibold">Pengaturan</h2>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Maks. Peserta / Sesi
                            (opsional)</label>
                        <input type="number" name="max_capacity"
                            value="{{ old('max_capacity', $package->max_capacity ?? '') }}" placeholder="Contoh: 20"
                            class="focus:border-primary focus:ring-primary/30 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-1">
                        @error('max_capacity')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}
                            class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300">
                        <label for="is_active" class="text-sm font-semibold text-gray-700">Aktifkan Paket Wisata</label>
                    </div>
                </div>
            </div>
            <button type="submit"
                class="bg-primary shadow-primary/20 hover:bg-primary-600 w-full rounded-xl py-3 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
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
        document.querySelector('input[name="images[]"]')?.addEventListener('change', function() {
            const maxSize = 5 * 1024 * 1024;
            const oversized = Array.from(this.files || []).find(f => f.size > maxSize);
            if (oversized) {
                Swal.fire({
                    title: 'Ukuran File Terlalu Besar',
                    text: 'Maksimal 5MB per gambar.',
                    icon: 'warning',
                    confirmButtonColor: '#1E5128',
                    confirmButtonText: 'Mengerti',
                    background: '#ffffff'
                });
                this.value = '';
            }
        });
    </script>
@endpush
