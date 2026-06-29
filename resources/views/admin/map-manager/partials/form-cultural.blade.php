{{-- FORM 1: Cultural Object --}}
<form id="form-cultural" action="{{ route('admin.cultural-objects.store') }}" method="POST" enctype="multipart/form-data"
    class="hidden space-y-4" x-data="{ locale: 'id' }">
    @csrf
    <div id="method-cultural"></div>

    {{-- Locale tabs --}}
    <div class="sticky top-0 z-10 bg-white py-3 -mx-6 px-6 border-b border-gray-100 mb-4 flex gap-2">
        <button @click="locale = 'id'" :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all" type="button">Indonesia</button>
        <button @click="locale = 'en'" :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition-all" type="button">English</button>
    </div>

    {{-- Name --}}
    <div x-show="locale === 'en'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Objek Budaya (EN) <span
                class="text-warning">*</span></label>
        <input type="text" name="name[en]" required placeholder="e.g. Penataran Agung Temple"
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
            value="{{ old('name.en', '') }}">
    </div>
    <div x-show="locale === 'id'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Objek Budaya (ID) <span
                class="text-warning">*</span></label>
        <input type="text" name="name[id]" required placeholder="Contoh: Pura Penataran Agung"
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
            value="{{ old('name.id', '') }}">
    </div>

    {{-- Short Description --}}
    <div x-show="locale === 'en'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Singkat (EN) <span
                class="text-warning">*</span></label>
        <input type="text" name="short_description[en]" required placeholder="e.g. Spiritual Heart of Penglipuran Village"
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
            value="{{ old('short_description.en', '') }}">
    </div>
    <div x-show="locale === 'id'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Singkat (ID) <span
                class="text-warning">*</span></label>
        <input type="text" name="short_description[id]" required placeholder="Contoh: Jantung Spiritual Desa Penglipuran"
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
            value="{{ old('short_description.id', '') }}">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kategori Budaya <span
                class="text-warning">*</span></label>
        <select name="category" required
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
            <option value="temple">Pura / Tempat Suci</option>
            <option value="house">Pekarangan Adat / Rumah</option>
            <option value="craft">Kerajinan Seni</option>
            <option value="tradition">Tradisi Adat / Upacara</option>
        </select>
    </div>

    {{-- Description --}}
    <div x-show="locale === 'en'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi (EN)</label>
        <x-tiptap-editor name="description[en]" id="cultural-desc-en" value="{{ old('description.en', '') }}" placeholder="e.g. A detailed description of this cultural object..." has-image="true" />
    </div>
    <div x-show="locale === 'id'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi (ID)</label>
        <x-tiptap-editor name="description[id]" id="cultural-desc-id" value="{{ old('description.id', '') }}" placeholder="Contoh: Deskripsi detail tentang objek budaya ini..." has-image="true" />
    </div>

    @include('admin.map-manager.partials.form-cultural.ar-model-section')

    @include('admin.map-manager.partials.form-cultural.quiz-section')

    @include('admin.map-manager.partials.form-cultural.story-section')

    {{-- Audio Narasi --}}
    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Audio Narasi (ID) / (EN)</label>
        <span class="mb-2 block text-xs text-gray-500">MP3/OGG/WAV/M4A, maks. 10 MB per bahasa</span>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <span class="mb-1 block text-[10px] font-semibold text-gray-500">Audio (ID)</span>
                @include('admin.partials.mini-audio-player', ['playerId' => 'current-audio-id'])
                <input type="file" name="cultural_audio_file[id]" accept=".mp3,.ogg,.wav,.m4a,audio/*"
                    class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold"
                    onchange="var maxSize=10*1024*1024;if(this.files[0]&&this.files[0].size>maxSize){Swal.fire({title:'File Terlalu Besar',text:'Maksimal 10MB per file audio.',icon:'warning',confirmButtonColor:'#1E5128',confirmButtonText:'Mengerti',background:'#ffffff'});this.value=''}">
            </div>
            <div>
                <span class="mb-1 block text-[10px] font-semibold text-gray-500">Audio (EN)</span>
                @include('admin.partials.mini-audio-player', ['playerId' => 'current-audio-en'])
                <input type="file" name="cultural_audio_file[en]" accept=".mp3,.ogg,.wav,.m4a,audio/*"
                    class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold"
                    onchange="var maxSize=10*1024*1024;if(this.files[0]&&this.files[0].size>maxSize){Swal.fire({title:'File Terlalu Besar',text:'Maksimal 10MB per file audio.',icon:'warning',confirmButtonColor:'#1E5128',confirmButtonText:'Mengerti',background:'#ffffff'});this.value=''}">
            </div>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-semibold text-gray-700">Foto Sejarah</label>
        <span class="mb-2 block text-xs text-gray-500">Dapat memilih beberapa file gambar sekaligus</span>
        <input type="file" name="historical_images[]" multiple accept="image/*"
            class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold"
            onchange="var maxSize=5*1024*1024;var oversized=Array.from(this.files||[]).find(function(f){return f.size>maxSize});if(oversized){Swal.fire({title:'Ukuran File Terlalu Besar',text:'Maksimal 5MB per gambar.',icon:'warning',confirmButtonColor:'#1E5128',confirmButtonText:'Mengerti',background:'#ffffff'});this.value=''}">
        <div id="current-images" class="mt-2 flex flex-wrap gap-1"></div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-400">Latitude</label>
            <input type="text" name="latitude" readonly
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-400">Longitude</label>
            <input type="text" name="longitude" readonly
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
    </div>

    <div class="flex items-center gap-2 py-1">
        <input type="checkbox" id="cultural_is_accessible" name="is_accessible" value="1"
            class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300">
        <label for="cultural_is_accessible" class="text-sm font-semibold text-gray-700">Akses Ramah
            Disabilitas</label>
    </div>

    <div class="accessibility-notes-container">
        <div x-show="locale === 'en'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas (EN)</label>
            <textarea name="accessibility_notes[en]" id="cultural-accessibility-notes-en" rows="2" placeholder="e.g. Ramp access, wheelchair friendly..."
                class="focus:border-primary w-full resize-none rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">Flat road access, friendly for wheelchairs and baby strollers.</textarea>
        </div>
        <div x-show="locale === 'id'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas (ID)</label>
            <textarea name="accessibility_notes[id]" id="cultural-accessibility-notes-id" rows="2" placeholder="Contoh: Pintu masuk landai, ramah kursi roda..."
                class="focus:border-primary w-full resize-none rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">Akses jalan datar ramah kursi roda dan stroller bayi.</textarea>
        </div>
    </div>

</form>


