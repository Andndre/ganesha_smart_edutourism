@extends('layouts.admin')

@section('title', isset($event) ? 'Edit Event' : 'Tambah Event')

@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.events') }}" class="rounded-xl p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-charcoal">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">{{ isset($event) ? 'Edit Event' : 'Tambah Event Baru' }}</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ isset($event) ? 'Ubah detail event budaya atau festival desa.' : 'Isi detail event budaya atau festival desa.' }}</p>
    </div>
</div>

<form action="{{ isset($event) ? route('admin.events.update', $event->id) : route('admin.events.store') }}" method="POST" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    @csrf
    @if(isset($event))
        @method('PUT')
    @endif

    {{-- Main Form --}}
    <div class="space-y-5 lg:col-span-2">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 font-semibold text-charcoal">Informasi Utama</h2>
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Event <span class="text-warning">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $event->name ?? '') }}" placeholder="Contoh: Festival Bambu Penglipuran 2026"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Event</label>
                    <textarea name="description" rows="4" placeholder="Jelaskan latar belakang dan kegiatan dalam event ini..."
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 resize-none">{{ old('description', $event->description ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tanggal Mulai <span class="text-warning">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date', isset($event) ? $event->start_datetime->format('Y-m-d') : '') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tanggal Selesai <span class="text-warning">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date', isset($event) ? $event->end_datetime->format('Y-m-d') : '') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Waktu Mulai</label>
                        <input type="time" name="start_time" value="{{ old('start_time', isset($event) ? $event->start_datetime->format('H:i') : '') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Waktu Selesai</label>
                        <input type="time" name="end_time" value="{{ old('end_time', isset($event) ? $event->end_datetime->format('H:i') : '') }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Lokasi <span class="text-warning">*</span></label>
                    <input type="text" name="location_name" value="{{ old('location_name', $event->location_name ?? '') }}" placeholder="Contoh: Bale Banjar atau Pura Penataran Agung"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Latitude (opsional)</label>
                        <input type="number" step="any" name="latitude" value="{{ old('latitude', $event->latitude ?? '') }}" placeholder="Contoh: -8.4312" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Longitude (opsional)</label>
                        <input type="number" step="any" name="longitude" value="{{ old('longitude', $event->longitude ?? '') }}" placeholder="Contoh: 115.3421" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar Settings --}}
    <div class="space-y-5">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold text-charcoal">Kategori & Publikasi</h2>
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kategori</label>
                    <select name="category" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                        @foreach(['Upacara Adat', 'Festival', 'Workshop', 'Pameran', 'Pertunjukan Seni'] as $cat)
                            <option value="{{ $cat }}" {{ old('category', isset($event) ? $event->getCategoryLabel() : '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold text-charcoal">Biaya & Kapasitas</h2>
            <div class="space-y-4">
                <div class="flex items-center gap-2 py-1">
                    <input type="checkbox" id="is_free" name="is_free" value="1" {{ old('is_free', isset($event) ? $event->is_free : true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="is_free" class="text-sm font-semibold text-gray-700">Event Gratis</label>
                </div>
                <div id="price-container">
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Harga Tiket (Rp)</label>
                    <input type="number" name="price" value="{{ old('price', $event->price ?? '') }}" placeholder="Contoh: 50000" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kapasitas Maksimal (opsional)</label>
                    <input type="number" name="max_participants" value="{{ old('max_participants', $event->max_participants ?? '') }}" placeholder="Maks. pengunjung" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <button type="submit"
                class="w-full rounded-xl bg-primary py-3 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                {{ isset($event) ? 'Perbarui Event' : 'Simpan Event' }}
            </button>
            <a href="{{ route('admin.events') }}"
                class="block w-full rounded-xl border border-gray-200 py-3 text-center text-sm font-semibold text-gray-500 transition-all hover:bg-gray-50">
                Batal
            </a>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    const isFreeCheckbox = document.getElementById('is_free');
    const priceContainer = document.getElementById('price-container');
    const priceInput = priceContainer.querySelector('input[name="price"]');

    function togglePriceVisibility() {
        if (isFreeCheckbox.checked) {
            priceContainer.style.display = 'none';
            priceInput.required = false;
        } else {
            priceContainer.style.display = 'block';
            priceInput.required = true;
        }
    }

    isFreeCheckbox.addEventListener('change', togglePriceVisibility);
    togglePriceVisibility(); // Run on load
</script>
@endpush
