{{-- FORM 1: Cultural Object --}}
<form id="form-cultural" action="{{ route('admin.cultural-objects.store') }}" method="POST"
    enctype="multipart/form-data" class="hidden space-y-4">
    @csrf
    <div id="method-cultural"></div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Objek Budaya <span
                class="text-warning">*</span></label>
        <input type="text" name="name" required placeholder="Contoh: Pura Penataran Agung"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kategori Budaya <span
                class="text-warning">*</span></label>
        <select name="category" required
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            <option value="temple">Pura / Tempat Suci</option>
            <option value="house">Pekarangan Adat / Rumah</option>
            <option value="craft">Kerajinan Seni</option>
            <option value="tradition">Tradisi Adat / Upacara</option>
        </select>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi</label>
        <textarea name="description" rows="3" placeholder="Tulis deskripsi singkat objek budaya..."
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">ID Marker AR (Opsional)</label>
        <input type="text" name="ar_marker_id" placeholder="Contoh: MARKER_PURA_01"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Model 3D (.glb, Max 20MB)</label>
        <input type="file" name="model_3d_file" accept=".glb"
            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
        <span id="current-model-3d" class="text-[10px] text-gray-400 block mt-1"></span>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Audio Narasi (.mp3, Max
            10MB)</label>
        <input type="file" name="audio_narration_file" accept="audio/*"
            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
        <span id="current-audio" class="text-[10px] text-gray-400 block mt-1"></span>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Foto Sejarah (Dapat memilih beberapa
            file)</label>
        <input type="file" name="historical_images[]" multiple accept="image/*"
            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
        <div id="current-images" class="flex flex-wrap gap-1 mt-2"></div>
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

    <div class="flex items-center gap-2 py-1">
        <input type="checkbox" id="cultural_is_accessible" name="is_accessible" value="1" checked
            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
        <label for="cultural_is_accessible" class="text-sm font-semibold text-gray-700">Akses Ramah
            Disabilitas</label>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas</label>
        <textarea name="accessibility_notes" rows="2"
            placeholder="Contoh: Pintu masuk landai, ramah kursi roda..."
            class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none resize-none">Akses jalan datar ramah kursi roda dan stroller bayi.</textarea>
    </div>

    <div class="flex gap-2 pt-2">
        <button type="submit"
            class="flex-1 rounded-xl bg-primary py-2.5 text-sm font-semibold text-white transition-all hover:bg-primary-600">Simpan</button>
        <button type="button" onclick="cancelEditor()"
            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
    </div>
</form>
