{{-- Shared create/edit form for facilities. $facility is null on create. --}}
@php
    $isEdit = isset($facility);
    $point = $isEdit ? $facility->mapLocation : null;
@endphp

<form action="{{ $isEdit ? route('admin.facilities.update', $facility->id) : route('admin.facilities.store') }}"
    method="POST" class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm" x-data="{ locale: 'id' }">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    <input type="hidden" name="redirect_to" value="facilities">

    {{-- Locale tabs --}}
    <div class="sticky top-0 z-10 -mx-6 mb-4 flex gap-2 border-b border-gray-100 bg-white px-6 py-3">
        <button @click="locale = 'id'" :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
            class="rounded-xl px-4 py-2 text-sm font-semibold transition-all" type="button">Indonesia</button>
        <button @click="locale = 'en'" :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
            class="rounded-xl px-4 py-2 text-sm font-semibold transition-all" type="button">English</button>
    </div>

    <div x-show="locale === 'en'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Fasilitas (EN) <span class="text-warning">*</span></label>
        <input type="text" name="name[en]" required placeholder="e.g. Public Toilet at Temple"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none"
            value="{{ old('name.en', $isEdit ? ($facility->getTranslation('name', 'en') ?? '') : '') }}">
    </div>
    <div x-show="locale === 'id'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Fasilitas (ID) <span class="text-warning">*</span></label>
        <input type="text" name="name[id]" required placeholder="Contoh: Toilet Umum Pura"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none"
            value="{{ old('name.id', $isEdit ? ($facility->getTranslation('name', 'id') ?? '') : '') }}">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tipe Fasilitas <span class="text-warning">*</span></label>
        @php $currentType = old('type', $isEdit ? $facility->type : 'toilet'); @endphp
        <select name="type" required
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            <option value="toilet" @selected($currentType === 'toilet')>Toilet</option>
            <option value="information" @selected($currentType === 'information')>Pusat Informasi / Balai</option>
            <option value="parking" @selected($currentType === 'parking')>Area Parkir</option>
            <option value="emergency" @selected($currentType === 'emergency')>Pos Keamanan / Kesehatan (Emergency)</option>
            <option value="accessibility" @selected($currentType === 'accessibility')>Layanan Disabilitas (Accessibility)</option>
        </select>
    </div>

    <div x-show="locale === 'en'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Description (EN)</label>
        <x-tiptap-editor name="description[en]" id="facility-desc-en"
            value="{{ old('description.en', $isEdit ? ($facility->getTranslation('description', 'en') ?? '') : '') }}"
            placeholder="Add complementary information about this facility..." />
    </div>
    <div x-show="locale === 'id'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi (ID)</label>
        <x-tiptap-editor name="description[id]" id="facility-desc-id"
            value="{{ old('description.id', $isEdit ? ($facility->getTranslation('description', 'id') ?? '') : '') }}"
            placeholder="Tambahkan informasi pelengkap untuk fasilitas ini..." />
    </div>

    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-4">
        <p class="mb-3 text-xs text-gray-500">Titik utama fasilitas ini. Untuk menambah titik lain (mis. pintu masuk kedua), gunakan <a href="{{ route('admin.map-manager') }}" class="text-primary underline">Peta Lokasi & Titik</a> setelah menyimpan.</p>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-gray-400">Latitude</label>
                <input type="text" name="latitude" required
                    value="{{ old('latitude', $point?->latitude ?? config('services.penglipuran.latitude')) }}"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-primary focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-gray-400">Longitude</label>
                <input type="text" name="longitude" required
                    value="{{ old('longitude', $point?->longitude ?? config('services.penglipuran.longitude')) }}"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-primary focus:outline-none">
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-4 py-1">
        <label class="flex cursor-pointer select-none items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $isEdit ? $facility->is_active : true))
                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
            <span class="text-sm font-semibold text-gray-700">Aktifkan Fasilitas</span>
        </label>
        <label class="flex cursor-pointer select-none items-center gap-2">
            <input type="checkbox" name="is_accessible" value="1" @checked(old('is_accessible', $point?->is_accessible ?? false))
                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
            <span class="text-sm font-semibold text-gray-700">Akses Ramah Disabilitas</span>
        </label>
    </div>

    <div>
        <div x-show="locale === 'en'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas (EN)</label>
            <textarea name="accessibility_notes[en]" rows="2" placeholder="e.g. Toilet is equipped with handrails..."
                class="w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">{{ old('accessibility_notes.en', is_array($point?->accessibility_notes) ? ($point->accessibility_notes['en'] ?? '') : '') }}</textarea>
        </div>
        <div x-show="locale === 'id'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas (ID)</label>
            <textarea name="accessibility_notes[id]" rows="2" placeholder="Contoh: Toilet dilengkapi dengan pegangan besi..."
                class="w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">{{ old('accessibility_notes.id', is_array($point?->accessibility_notes) ? ($point->accessibility_notes['id'] ?? '') : '') }}</textarea>
        </div>
    </div>

    <div class="flex gap-2 border-t border-gray-100 pt-4">
        <button type="submit" class="flex-1 rounded-xl bg-primary py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-600">Simpan</button>
        <a href="{{ route('admin.facilities') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</a>
    </div>
</form>
