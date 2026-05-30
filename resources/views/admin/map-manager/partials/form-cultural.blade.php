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
        <label class="mb-1 block text-sm font-semibold text-gray-700">ID Marker AR</label>
        <span class="mb-2 block text-xs text-gray-500">Opsional. Digunakan untuk integrasi Augmented Reality</span>
        <input type="text" name="ar_marker_id" placeholder="Contoh: MARKER_PURA_01"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Model 3D</label>
        <span class="mb-2 block text-xs text-gray-500">Format .glb, maksimal 20MB</span>
        <input type="file" name="model_3d_file" accept=".glb"
            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
        <span id="current-model-3d" class="text-[10px] text-gray-400 block mt-1"></span>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Audio Narasi</label>
        <span class="mb-2 block text-xs text-gray-500">Format .mp3, maksimal 10MB</span>
        <input type="file" name="audio_narration_file" accept="audio/*"
            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
        <span id="current-audio" class="text-[10px] text-gray-400 block mt-1"></span>
    </div>

    <div class="pt-4 border-t border-gray-100">
        <label class="flex items-center space-x-2 cursor-pointer mb-3">
            <input type="checkbox" id="has_quiz" name="has_quiz" value="1" class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary" onchange="toggleQuizzes(this)">
            <span class="text-sm font-semibold text-gray-700">Tambahkan Kuis Edutourism?</span>
        </label>
        
        <button type="button" id="btn-manage-quizzes" onclick="openQuizModal()" class="hidden w-full rounded-xl border-2 border-primary text-primary py-2.5 text-sm font-semibold hover:bg-primary/5 transition-colors items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Kelola Soal Kuis
        </button>

        <div id="quizzes-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-charcoal/50 backdrop-blur-sm p-4 justify-center items-center">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="font-display font-bold text-lg text-charcoal">Kelola Kuis Edutourism</h3>
                        <p class="text-xs text-gray-500 mt-1">Soal-soal ini akan muncul saat turis tiba di lokasi ini.</p>
                    </div>
                    <button type="button" onclick="closeQuizModal()" class="text-gray-400 hover:text-red-500 rounded-lg p-2 hover:bg-red-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto flex-1 space-y-6" id="quizzes-list">
                    <!-- Quizzes will be appended here -->
                </div>
                
                <div class="p-5 border-t border-gray-100 bg-gray-50/50 space-y-3">
                    <button type="button" onclick="addQuizField()" class="w-full rounded-xl border-2 border-dashed border-gray-200 py-3 text-sm font-semibold text-gray-500 hover:border-primary hover:text-primary hover:bg-green-50 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Soal Kuis
                    </button>
                    <button type="button" onclick="closeQuizModal()" class="w-full rounded-xl bg-primary py-3 text-sm font-semibold text-white hover:bg-primary-600 transition-all shadow-lg shadow-primary/20">Selesai & Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Foto Sejarah</label>
        <span class="mb-2 block text-xs text-gray-500">Dapat memilih beberapa file gambar sekaligus</span>
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
