{{-- FORM 3: Facility --}}
<form id="form-facility" action="{{ route('admin.facilities.store') }}" method="POST"
    class="hidden space-y-4">
    @csrf
    <div id="method-facility"></div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Fasilitas <span
                class="text-warning">*</span></label>
        <input type="text" name="name" required placeholder="Contoh: Toilet Umum Pura"
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

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi</label>
        <textarea name="description" rows="3"
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

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas</label>
        <textarea name="accessibility_notes" rows="2"
            placeholder="Contoh: Toilet dilengkapi dengan pegangan besi..."
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none">Akses jalan datar ramah kursi roda.</textarea>
    </div>

</form>
