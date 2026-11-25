@extends('layouts.dashboard')

@section('title', $house ? 'Ubah Pekarangan' : 'Tambah Pekarangan')

@section('content')
@php
    $isOwner = $isOwner ?? false;
    $backUrl = $isOwner ? route('owner.dashboard') : route('admin.houses.index');
    $formAction = $isOwner ? route('owner.house.update') : ($house ? route('admin.houses.update', $house) : route('admin.houses.store'));
    $description = $house?->getTranslations('description') ?? [];
@endphp

<div class="mx-auto max-w-6xl">
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ $backUrl }}" class="inline-flex min-h-11 items-center gap-1.5 text-sm font-semibold text-primary hover:underline">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Kembali ke daftar pekarangan
            </a>
            <p class="mt-2 text-xs font-bold uppercase tracking-[.18em] text-primary">Direktori Pekarangan</p>
            <h1 class="mt-1 font-display text-3xl font-bold text-charcoal">{{ $house ? 'Ubah Pekarangan No. '.$house->number : 'Tambah Pekarangan' }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-gray-600">Lengkapi data keluarga, pilih titik rumah di peta, lalu tambahkan foto. UMKM Rumah mengikuti data rumah secara otomatis. Pastikan lokasi sudah benar sebelum ditampilkan untuk pengunjung.</p>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold {{ $house?->is_published ? 'bg-primary/10 text-primary' : 'bg-amber-50 text-amber-800' }}">
            <span class="h-2 w-2 rounded-full {{ $house?->is_published ? 'bg-primary' : 'bg-amber-500' }}"></span>
            {{ $house?->is_published ? 'Terbit' : 'Draf' }}
        </span>
    </div>

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
        @csrf
        @if($house && !$house->is_published && $house->umkmProfile?->isHouse() && !$house->umkmProfile->is_active)
            <input type="hidden" name="activate_house_umkm" value="1">
        @endif
        @if($house) @method('PUT') @endif

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                @unless($isOwner)
                <x-dashboard.panel title="Akun Pengelola Rumah">
                    <label for="house-owner" class="mb-2 block text-sm font-semibold text-charcoal">Pengelola Rumah + UMKM</label>
                    <select id="house-owner" name="owner_user_id" class="min-h-12 w-full rounded-xl border border-gray-500 bg-white px-3 text-base focus-visible:outline-2 focus-visible:outline-primary" aria-describedby="house-owner-help">
                        <option value="">Belum ditetapkan</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" @selected((string) old('owner_user_id', $house?->owner_user_id) === (string) $owner->id)>{{ $owner->name }} — {{ $owner->email }}</option>
                        @endforeach
                    </select>
                    <p id="house-owner-help" class="mt-2 text-sm text-gray-600">Akun mendapat akses rumah ini. Profil UMKM Rumah dibuat otomatis atau menggunakan profil akun yang dipilih. Nama mengikuti nomor rumah.</p>
                    @error('owner_user_id')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                </x-dashboard.panel>
                @endunless
                <x-dashboard.panel title="Data Keluarga">
                    <p class="-mt-2 mb-5 text-xs leading-relaxed text-gray-500">Identitas rumah dan jumlah penghuni yang ditampilkan dalam direktori.</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-dashboard.form-field name="number" label="Nomor rumah" type="number" :value="old('number', $house?->number)" min="1" max="9999" required />
                        <x-dashboard.form-field name="head_name" label="Nama kepala keluarga" :value="old('head_name', $house?->head_name)" maxlength="255" required />
                        <x-dashboard.form-field name="male_count" label="Jumlah laki-laki" type="number" :value="old('male_count', $house?->male_count ?? 0)" min="0" max="999" required />
                        <x-dashboard.form-field name="female_count" label="Jumlah perempuan" type="number" :value="old('female_count', $house?->female_count ?? 0)" min="0" max="999" required />
                    </div>
                </x-dashboard.panel>

                <x-dashboard.panel title="Cerita Pekarangan">
                    <x-dashboard.localized-textarea name="description" id="house-description" label="Deskripsi rumah" :value="$description"
                        placeholder-id="Contoh: Pekarangan ini menjadi tempat tinggal keluarga dan ruang kegiatan budaya."
                        placeholder-en="Contoh teks Inggris: This courtyard is a family home and a place for cultural activities." />
                </x-dashboard.panel>

                <x-dashboard.panel title="Lokasi Rumah">
                    <x-dashboard.location-picker id="house-location" :latitude="old('latitude', $house?->mapLocation?->latitude)" :longitude="old('longitude', $house?->mapLocation?->longitude)" />
                    <input type="hidden" name="is_accessible" value="0">
                    <div class="mt-4"><x-dashboard.check-option name="is_accessible" value="1" :checked="old('is_accessible', $house?->mapLocation?->is_accessible ?? true)" label="Dapat diakses pengguna kursi roda" /></div>
                    <div class="mt-4"><x-dashboard.localized-textarea name="accessibility_notes" id="house-accessibility" label="Catatan aksesibilitas" :value="$house?->mapLocation?->getTranslations('accessibility_notes') ?? []" /></div>
                </x-dashboard.panel>

                <x-dashboard.panel title="UMKM Rumah">
                    <p class="text-sm leading-relaxed text-gray-600">Satu rumah terhubung ke satu profil UMKM. Produk dan usaha keluarga dikelola melalui profil ini.</p>
                    @if($house?->umkmProfile)
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-primary/5 p-4">
                            <div class="min-w-0">
                                <p class="font-semibold text-charcoal">{{ translateValue($house->umkmProfile->business_name, 'id') }}</p>
                                <p class="mt-1 text-sm text-gray-600">{{ $house->umkmProfile->is_active ? 'Aktif' : 'Tidak aktif' }}</p>
                            </div>
                            <span class="rounded-full border border-primary/20 px-3 py-1 text-xs font-semibold text-primary">{{ $house->umkmProfile->isHouse() ? 'Terhubung otomatis' : 'Terhubung' }}</span>
                        </div>
                    @else
                        <p class="mt-4 rounded-xl bg-surface p-4 text-sm text-gray-600">Profil UMKM Rumah dibuat otomatis saat rumah disimpan.</p>
                    @endif
                    @error('umkm_profile_ids')<p class="mt-3 text-sm text-red-700">{{ $message }}</p>@enderror
                </x-dashboard.panel>

                <x-dashboard.panel title="Ruang Budaya Otomatis">
                    <p class="text-sm leading-relaxed text-gray-600">Susunan ruang budaya sama untuk seluruh rumah dan dikelola oleh admin desa. Cerita, audio, dan model 3D/AR mengikuti objek budaya yang sama.</p>
                    @if($houseCulture)
                        <p class="mt-4 font-semibold text-charcoal">{{ translateValue($houseCulture->name, 'id') }}</p>
                        <ul class="mt-3 flex flex-wrap gap-2">
                            @forelse($houseCulture->children as $room)
                                <li class="rounded-lg bg-primary/10 px-3 py-2 text-sm text-primary">{{ translateValue($room->name, 'id') }}</li>
                            @empty
                                <li class="text-sm text-gray-600">Objek anak belum ditambahkan ke induk ini.</li>
                            @endforelse
                        </ul>
                    @else
                        <p class="mt-4 text-sm text-gray-600">Induk budaya untuk seluruh rumah belum ditentukan oleh admin desa.</p>
                    @endif
                    @unless($isOwner)
                        <a href="{{ route('admin.settings') }}" class="mt-4 inline-flex min-h-12 items-center text-sm font-semibold text-primary underline underline-offset-4">Atur induk budaya seluruh rumah</a>
                    @endunless
                </x-dashboard.panel>
            </div>

            <div class="space-y-6">
                <x-dashboard.panel title="Foto Rumah">
                    <div class="space-y-6">
                        <x-dashboard.image-upload id="house-cover" name="cover_image" label="Foto sampul" remove-name="remove_cover_image" :existing="$house?->cover_image_path ? [$house->cover_image_path] : []" />
                        <x-dashboard.image-upload id="house-gallery" name="gallery_images[]" label="Foto galeri" :multiple="true" :append="true" :max-files="8" :existing="$house?->gallery ?? []" existing-hint="Foto baru akan ditambahkan ke galeri." />
                        @foreach($house?->gallery ?? [] as $galleryPath)
                            <x-dashboard.check-option name="remove_gallery_images[]" :value="$galleryPath" :label="'Hapus foto galeri '.($loop->iteration)" />
                        @endforeach
                    </div>
                </x-dashboard.panel>

                <x-dashboard.panel title="Publikasi">
                    <div class="mb-4"><x-dashboard.form-field name="ar_marker_id" label="ID penanda AR" :value="old('ar_marker_id', $house?->ar_marker_id)" maxlength="100" /></div>
                    <p class="-mt-2 mb-4 text-xs leading-relaxed text-gray-500">Atur akses pengunjung dan keterlihatan rumah di direktori.</p>
                    <div class="space-y-2">
                        <x-dashboard.check-option name="is_open_to_visitors" value="1" :checked="old('is_open_to_visitors', $house?->is_open_to_visitors)" label="Terbuka untuk pengunjung" />
                        <x-dashboard.check-option name="is_published" value="1" :checked="old('is_published', $house?->is_published)" label="Tampilkan untuk pengunjung" />
                    </div>
                    @if($house?->ar_marker_id)
                        <div class="mt-5 border-t border-gray-100 pt-5 text-center">
                            <p class="mb-3 text-sm font-semibold text-charcoal">Penanda pekarangan</p>
                            <img src="{{ qrSvgDataUri(route('ar-scan-redirect', ['arMarkerId' => $house->ar_marker_id])) }}" alt="Penanda QR pekarangan nomor {{ $house->number }}" class="mx-auto h-40 w-40">
                            <p class="mt-2 break-all font-mono text-xs text-gray-500">{{ $house->ar_marker_id }}</p>
                            <p class="mt-2 text-xs text-gray-500">Cetak dan pasang di pintu masuk pekarangan.</p>
                        </div>
                    @endif
                </x-dashboard.panel>
            </div>
        </div>

        <div class="sticky bottom-0 z-20 mt-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white/95 p-3 shadow-lg backdrop-blur sm:px-5">
            <p class="text-xs text-gray-500">Pastikan data rumah sudah sesuai sebelum disimpan.</p>
            <div class="flex w-full gap-2 sm:w-auto">
                <a href="{{ $backUrl }}" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl border border-gray-200 px-4 text-sm font-semibold text-gray-600 hover:bg-surface sm:flex-none">Batal</a>
                @if($isOwner || auth()->user()?->isAdmin())
                    <button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-primary px-5 text-sm font-bold text-white transition hover:bg-primary-hover sm:flex-none">{{ $house ? 'Simpan Perubahan' : 'Simpan Rumah' }}</button>
                @endif
            </div>
        </div>
    </form>

    @if(!$isOwner && $house && auth()->user()?->isAdmin())
        <form method="POST" action="{{ route('admin.houses.destroy', $house) }}" onsubmit="return confirm('Hapus rumah adat ini beserta penandanya?')" class="mt-8 border-t border-gray-200 pt-5">
            @csrf @method('DELETE')
            <button type="submit" class="min-h-11 text-sm font-semibold text-red-700 underline underline-offset-4">Hapus rumah adat</button>
        </form>
    @endif
</div>
@endsection
