{{-- FORM 3: Facility --}}
<form id="form-facility" action="{{ route('admin.facilities.store') }}" method="POST"
    class="hidden space-y-4" x-data="{ locale: 'en' }">
    @csrf
    <div id="method-facility"></div>

    {{-- Locale tabs --}}
    <div class="sticky top-0 z-10 bg-white py-3 -mx-6 px-6 border-b border-gray-100 mb-4 flex gap-2">
        <button @click="locale = 'en'" :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all" type="button">English</button>
        <button @click="locale = 'id'" :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all" type="button">Indonesia</button>
    </div>

    <div x-show="locale === 'en'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Fasilitas (EN) <span
                class="text-warning">*</span></label>
        <input type="text" name="name[en]" required placeholder="e.g. Public Toilet at Temple"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
    </div>
    <div x-show="locale === 'id'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Fasilitas (ID) <span
                class="text-warning">*</span></label>
        <input type="text" name="name[id]" required placeholder="Contoh: Toilet Umum Pura"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tipe Fasilitas <span
                class="text-warning">*</span></label>
        <select name="type" required
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            <option value="toilet">Toilet</option>
            <option value="information">Pusat Informasi / Balai</option>
            <option value="parking">Area Parkir</option>
            <option value="emergency">Pos Keamanan / Kesehatan (Emergency)</option>
            <option value="accessibility">Layanan Disabilitas (Accessibility)</option>
        </select>
    </div>

    <div x-show="locale === 'en'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Description (EN)</label>
        <textarea name="description[en]" rows="3"
            placeholder="Add complementary information about this facility..."
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
    </div>
    <div x-show="locale === 'id'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi (ID)</label>
        <textarea name="description[id]" rows="3"
            placeholder="Tambahkan informasi pelengkap untuk fasilitas ini..."
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Latitude</label>
            <input type="text" name="latitude" readonly
                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Longitude</label>
            <input type="text" name="longitude" readonly
                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
    </div>

    <div class="flex flex-wrap gap-4 py-1">
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" name="is_active" value="1" checked
                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
            <span class="text-sm font-semibold text-gray-700">Aktifkan Fasilitas</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" id="facility_is_accessible" name="is_accessible" value="1" checked
                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
            <span class="text-sm font-semibold text-gray-700">Akses Ramah Disabilitas</span>
        </label>
    </div>

    <div class="accessibility-notes-container">
        <div x-show="locale === 'en'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas (EN)</label>
            <textarea name="accessibility_notes[en]" id="facility-accessibility-notes-en" rows="2" placeholder="e.g. Toilet is equipped with handrails..."
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none">Flat road access, friendly for wheelchairs.</textarea>
        </div>
        <div x-show="locale === 'id'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas (ID)</label>
            <textarea name="accessibility_notes[id]" id="facility-accessibility-notes-id" rows="2" placeholder="Contoh: Toilet dilengkapi dengan pegangan besi..."
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none">Akses jalan datar ramah kursi roda.</textarea>
        </div>
    </div>

</form>
