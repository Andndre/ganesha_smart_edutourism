@extends('layouts.admin')

@section('title', 'Tambah Event')

@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.events') }}" class="rounded-xl p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-charcoal">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Tambah Event Baru</h1>
        <p class="mt-0.5 text-sm text-gray-500">Isi detail event budaya atau festival desa.</p>
    </div>
</div>

<form class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Main Form --}}
    <div class="space-y-5 lg:col-span-2">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-5 font-semibold text-charcoal">Informasi Utama</h2>
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Event <span class="text-warning">*</span></label>
                    <input type="text" placeholder="Contoh: Festival Bambu Penglipuran 2026"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Event</label>
                    <textarea rows="4" placeholder="Jelaskan latar belakang dan kegiatan dalam event ini..."
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 resize-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tanggal Mulai <span class="text-warning">*</span></label>
                        <input type="date" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tanggal Selesai</label>
                        <input type="date" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Waktu Mulai</label>
                        <input type="time" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Lokasi <span class="text-warning">*</span></label>
                        <select class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                            <option value="">Pilih Lokasi...</option>
                            <option>Pura Penataran Agung</option>
                            <option>Bale Banjar</option>
                            <option>Kebun Bambu</option>
                            <option>Balai Desa</option>
                            <option>Seluruh Desa</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar Settings --}}
    <div class="space-y-5">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-semibold text-charcoal">Pengaturan</h2>
            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kategori</label>
                    <select class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                        <option>Upacara Adat</option>
                        <option>Festival</option>
                        <option>Workshop</option>
                        <option>Pameran</option>
                        <option>Pertunjukan Seni</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Status Publikasi</label>
                    <select class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none">
                        <option>Draft</option>
                        <option>Publikasi</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kapasitas (opsional)</label>
                    <input type="number" placeholder="Maks. pengunjung" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <button type="submit"
                class="w-full rounded-xl bg-primary py-3 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                Simpan Event
            </button>
            <a href="{{ route('admin.events') }}"
                class="block w-full rounded-xl border border-gray-200 py-3 text-center text-sm font-semibold text-gray-500 transition-all hover:bg-gray-50">
                Batal
            </a>
        </div>
    </div>
</form>

@endsection
