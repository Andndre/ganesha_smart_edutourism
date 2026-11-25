@extends('layouts.dashboard')

@section('title', 'Tambah Pekarangan')

@section('content')
@php
    $ownerMode = old('owner_mode', 'later');
    $oldNumber = old('number');
    $duplicateUrl = is_scalar($oldNumber) ? $existingNumbers->get((int) $oldNumber) : null;
@endphp
<div class="mx-auto max-w-3xl">
    <a href="{{ route('admin.houses.index') }}" class="inline-flex min-h-12 items-center text-sm font-semibold text-primary underline">Kembali ke daftar rumah</a>
    <h1 id="house-create-title" class="mt-3 font-display text-3xl font-bold text-charcoal">Tambah pekarangan warga</h1>
    <p class="mt-2 text-base leading-relaxed text-gray-600">Isi data rumah, tentukan lokasinya, lalu pilih pengelola. UMKM Rumah akan dibuat otomatis.</p>

    <form action="{{ route('admin.houses.store') }}" method="POST" enctype="multipart/form-data" aria-labelledby="house-create-title"
        data-house-wizard data-existing-numbers="{{ $existingNumbers->toJson() }}" data-error-fields="{{ json_encode($errors->keys()) }}" class="mt-6 space-y-6">
        @csrf
        <input type="hidden" name="creation_flow" value="guided">
        <input type="hidden" name="is_published" value="0" data-publish-value>

        <nav aria-label="Langkah penambahan rumah" class="rounded-2xl border border-gray-200 bg-white p-4">
            <ol class="flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold">
                @foreach(['Data rumah', 'Lokasi', 'Pengelola'] as $title)
                    <li data-step-indicator="{{ $loop->iteration }}" class="flex items-center gap-2"><span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary">{{ $loop->iteration }}</span>{{ $title }}</li>
                @endforeach
            </ol>
            <p data-step-status class="mt-3 text-sm text-gray-600" aria-live="polite">Lengkapi data berikut. Bagian opsional dapat diisi nanti.</p>
        </nav>

        <section data-house-step="1" aria-labelledby="house-step-1" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5 sm:p-7">
            <h2 id="house-step-1" tabindex="-1" class="font-display text-2xl font-bold text-charcoal">1. Data rumah</h2>
            <p class="text-sm text-gray-600">Cukup isi nomor rumah dan nama kepala keluarga untuk memulai.</p>
            <div>
                <x-dashboard.form-field name="number" id="house-number" label="Nomor rumah" type="number" :value="old('number')" min="1" max="9999" inputmode="numeric" required class="min-h-12 text-base" />
                @error('number')<p class="mt-2 text-sm text-red-700" data-server-error="number">{{ $message }}</p>@enderror
                <div data-duplicate-house @if(!$duplicateUrl) hidden @endif class="mt-3 rounded-xl border border-amber-300 bg-amber-50 p-4">
                    <p data-duplicate-message class="text-sm font-semibold text-charcoal">Rumah No. {{ is_scalar($oldNumber) ? $oldNumber : '' }} sudah terdaftar.</p>
                    <a data-duplicate-link href="{{ $duplicateUrl ?? '#' }}" class="mt-2 inline-flex min-h-12 items-center font-semibold text-primary underline">Buka rumah tersebut</a>
                </div>
            </div>
            <div>
                <x-dashboard.form-field name="head_name" id="house-head-name" label="Nama kepala keluarga" :value="old('head_name')" maxlength="255" autocomplete="name" required class="min-h-12 text-base" />
                @error('head_name')<p class="mt-2 text-sm text-red-700" data-server-error="head_name">{{ $message }}</p>@enderror
            </div>
            <p class="rounded-xl bg-primary/5 p-4 text-sm text-primary">Nama UMKM otomatis: <strong data-house-name>Rumah No. {{ old('number', '…') }}</strong></p>
            <p class="rounded-xl bg-surface p-4 text-sm text-gray-600">Ruang budaya mengikuti susunan pekarangan yang diatur admin desa secara otomatis, termasuk cerita, audio, dan model 3D/AR.</p>
            <details data-extra class="rounded-xl border border-gray-200 p-4" @if($errors->hasAny(['male_count', 'female_count', 'cover_image', 'description.*', 'ar_marker_id'])) open @endif>
                <summary class="min-h-12 cursor-pointer py-3 font-semibold text-charcoal">Informasi tambahan (opsional)</summary>
                <div class="mt-3 space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-dashboard.form-field name="male_count" label="Jumlah laki-laki" type="number" :value="old('male_count')" min="0" max="999" class="min-h-12 text-base" />
                        <x-dashboard.form-field name="female_count" label="Jumlah perempuan" type="number" :value="old('female_count')" min="0" max="999" class="min-h-12 text-base" />
                    </div>
                    <x-dashboard.image-upload id="new-house-cover" name="cover_image" label="Foto rumah" />
                    <div>
                        <label for="house-description-id" class="mb-2 block text-sm font-semibold text-charcoal">Cerita rumah</label>
                        <textarea id="house-description-id" name="description[id]" rows="4" maxlength="10000" class="w-full rounded-xl border border-gray-500 p-3 text-base focus-visible:outline-2 focus-visible:outline-primary">{{ old('description.id') }}</textarea>
                    </div>
                    <details data-extra class="rounded-xl border border-gray-200 p-4">
                        <summary class="min-h-12 cursor-pointer py-3 font-semibold text-charcoal">Pengaturan tambahan</summary>
                        <div class="mt-3 space-y-5">
                            <div><label for="house-description-en" class="mb-2 block text-sm font-semibold text-charcoal">Cerita rumah dalam bahasa Inggris</label><textarea id="house-description-en" name="description[en]" rows="4" maxlength="10000" class="w-full rounded-xl border border-gray-500 p-3 text-base">{{ old('description.en') }}</textarea></div>
                            <x-dashboard.form-field name="ar_marker_id" label="ID penanda AR (otomatis jika dikosongkan)" :value="old('ar_marker_id')" maxlength="100" />
                        </div>
                    </details>
                </div>
            </details>
        </section>

        <section data-house-step="2" aria-labelledby="house-step-2" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5 sm:p-7">
            <h2 id="house-step-2" tabindex="-1" class="font-display text-2xl font-bold text-charcoal">2. Lokasi rumah</h2>
            <p class="text-sm text-gray-600">Ketuk lokasi rumah di peta. Jika lokasinya belum pasti, Anda dapat melengkapinya setelah menyimpan draf.</p>
            <x-dashboard.location-picker id="new-house-location" label="Pilih lokasi rumah" :latitude="old('latitude', $prefill['latitude'] ?? null)" :longitude="old('longitude', $prefill['longitude'] ?? null)" :hide-coordinates="true" :confirm-selection="true" />
            @foreach(['latitude', 'longitude'] as $field)
                @error($field)<p class="text-sm text-red-700" data-server-error="{{ $field }}">{{ $message }}</p>@enderror
            @endforeach
            <p data-location-error hidden class="text-sm font-semibold text-red-700" role="alert"></p>
        </section>

        <section data-house-step="3" aria-labelledby="house-step-3" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5 sm:p-7">
            <h2 id="house-step-3" tabindex="-1" class="font-display text-2xl font-bold text-charcoal">3. Pengelola rumah</h2>
            <fieldset><legend class="mb-3 text-sm text-gray-600">Siapa yang akan mengelola data rumah dan produknya?</legend>
                <div class="space-y-2">
                    @foreach(['existing' => 'Gunakan akun yang sudah ada', 'new' => 'Buat akun baru', 'later' => 'Atur nanti'] as $value => $label)
                        <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-xl border border-gray-300 p-3"><input type="radio" name="owner_mode" value="{{ $value }}" @checked($ownerMode === $value) class="h-4 w-4 accent-primary">{{ $label }}</label>
                    @endforeach
                </div>
            </fieldset>
            <div data-owner-fields="existing" class="space-y-2">
                <label for="house-existing-owner" class="block text-sm font-semibold text-charcoal">Akun pengelola</label>
                <select id="house-existing-owner" name="owner_user_id" class="min-h-12 w-full rounded-xl border border-gray-500 bg-white px-3 text-base focus-visible:outline-2 focus-visible:outline-primary">
                    <option value="">Pilih pengelola</option>
                    @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" @selected((string) old('owner_user_id') === (string) $owner->id) @disabled($owner->umkmProfile || $owner->managedHouse)>{{ $owner->name }} — {{ $owner->email }}{{ $owner->umkmProfile || $owner->managedHouse ? ' (sudah memiliki usaha atau rumah)' : '' }}</option>
                    @endforeach
                </select>
                @error('owner_user_id')<p class="text-sm text-red-700" data-server-error="owner_user_id">{{ $message }}</p>@enderror
                <p class="text-sm text-gray-600">Akun yang sudah mengelola usaha atau rumah lain tidak dapat dipilih.</p>
            </div>
            <div data-owner-fields="new" class="space-y-4">
                <x-dashboard.form-field name="new_owner_name" label="Nama pengelola" :value="old('new_owner_name')" maxlength="255" autocomplete="section-owner name" class="min-h-12 text-base" />
                <x-dashboard.form-field name="new_owner_email" label="Email untuk masuk" type="email" :value="old('new_owner_email')" maxlength="255" autocomplete="section-owner email" class="min-h-12 text-base" />
                <x-dashboard.form-field name="new_owner_phone" label="Nomor telepon (opsional)" type="tel" :value="old('new_owner_phone')" maxlength="50" autocomplete="section-owner tel" class="min-h-12 text-base" />
                <x-dashboard.form-field name="password" label="Kata sandi" type="password" minlength="8" autocomplete="new-password" help="Gunakan minimal 8 karakter." class="min-h-12 text-base" />
                <x-dashboard.form-field name="password_confirmation" label="Ulangi kata sandi" type="password" minlength="8" autocomplete="new-password" class="min-h-12 text-base" />
                <button type="button" data-show-password aria-pressed="false" class="min-h-12 font-semibold text-primary underline">Tampilkan kata sandi</button>
                @foreach(['new_owner_name', 'new_owner_email', 'new_owner_phone', 'password', 'password_confirmation'] as $field)
                    @error($field)<p class="text-sm text-red-700" data-server-error="{{ $field }}">{{ $message }}</p>@enderror
                @endforeach
                <p class="text-sm text-gray-600">Akun dibuat saat rumah disimpan. Pengelola dapat masuk memakai email dan kata sandi yang Anda isi.</p>
            </div>
            <div data-owner-fields="later" class="rounded-xl bg-surface p-4 text-sm text-gray-600">Rumah dan UMKM-nya tetap dibuat. Akun pengelola dapat ditetapkan nanti.</div>

            <div class="rounded-xl border border-primary/20 bg-primary/5 p-4">
                <h3 class="font-semibold text-charcoal">Periksa sebelum menyimpan</h3>
                <dl class="mt-3 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3"><div><dt class="text-gray-600">Rumah</dt><dd data-review-house class="font-semibold">Nomor dan kepala keluarga yang diisi di langkah 1</dd></div><button type="button" data-go-step="1" class="min-h-12 px-2 font-semibold text-primary underline">Ubah<span class="sr-only"> data rumah</span></button></div>
                    <div class="flex items-center justify-between gap-3"><div><dt class="text-gray-600">Lokasi</dt><dd data-review-location class="font-semibold">Periksa titik di langkah 2</dd></div><button type="button" data-go-step="2" class="min-h-12 px-2 font-semibold text-primary underline">Ubah<span class="sr-only"> lokasi rumah</span></button></div>
                    <div class="flex items-center justify-between gap-3"><div><dt class="text-gray-600">Pengelola</dt><dd data-review-owner class="font-semibold">Sesuai pilihan di atas</dd></div><button type="button" data-edit-owner class="min-h-12 px-2 font-semibold text-primary underline">Ubah<span class="sr-only"> pengelola rumah</span></button></div>
                    <div><dt class="text-gray-600">UMKM</dt><dd class="font-semibold">Dibuat otomatis mengikuti nomor rumah</dd></div>
                </dl>
            </div>
            <p class="text-sm text-gray-600">Draf belum tampil untuk pengunjung. Gunakan Tampilkan untuk pengunjung setelah data dan lokasi sudah benar.</p>
        </section>

        <div class="sticky bottom-0 z-20 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-lg">
            <div data-wizard-nav hidden class="flex flex-wrap gap-3">
                <button type="button" data-wizard-back class="min-h-12 rounded-xl border border-gray-300 px-5 font-semibold text-charcoal">Kembali</button>
                <button type="button" data-wizard-next class="min-h-12 rounded-xl bg-primary px-5 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Lanjutkan</button>
            </div>
            <div data-wizard-submit class="flex flex-wrap gap-3">
                <button type="submit" name="is_published" value="0" data-save-draft class="min-h-12 rounded-xl border border-primary px-5 font-semibold text-primary">Simpan sebagai draf</button>
                <button type="submit" name="is_published" value="1" data-publish class="min-h-12 rounded-xl bg-primary px-5 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Tampilkan untuk pengunjung</button>
            </div>
            <p data-save-status class="text-sm text-gray-600" aria-live="polite"></p>
        </div>
    </form>
</div>
@endsection
