{{-- FORM 1: Cultural Object --}}
<form id="form-cultural" action="{{ route('admin.cultural-objects.store') }}" method="POST" enctype="multipart/form-data"
    class="hidden space-y-4" x-data="{ locale: 'id' }">
    @csrf
    @include('admin.cultural-objects.partials.language-switch')
    <div id="method-cultural"></div>
    {{-- Name --}}
    <x-dashboard.localized-field id="localized-map-manager-form-cultural-name" label="Nama objek budaya" field-name="name" required>
        <x-slot:indonesian>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Objek Budaya (ID) <span
                        class="text-warning">*</span></label>
                <input type="text" name="name[id]" required placeholder="Contoh: Pura Penataran Agung"
                    class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
                    value="{{ old('name.id', '') }}">
            </div>
        </x-slot:indonesian>
        <x-slot:english>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Objek Budaya (EN) <span
                        class="text-warning">*</span></label>
                <input type="text" name="name[en]" required placeholder="e.g. Penataran Agung Temple"
                    class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
                    value="{{ old('name.en', '') }}">
            </div>
        </x-slot:english>
    </x-dashboard.localized-field>


    {{-- Short Description --}}
    <x-dashboard.localized-field id="localized-map-manager-form-cultural-short-description" label="Deskripsi singkat" field-name="short_description" required>
        <x-slot:indonesian>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Singkat (ID) <span
                        class="text-warning">*</span></label>
                <input type="text" name="short_description[id]" required placeholder="Contoh: Jantung Spiritual Desa Penglipuran"
                    class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
                    value="{{ old('short_description.id', '') }}">
            </div>
        </x-slot:indonesian>
        <x-slot:english>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Singkat (EN) <span
                        class="text-warning">*</span></label>
                <input type="text" name="short_description[en]" required placeholder="e.g. Spiritual Heart of Penglipuran Village"
                    class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
                    value="{{ old('short_description.en', '') }}">
            </div>
        </x-slot:english>
    </x-dashboard.localized-field>


    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kategori Budaya <span
                class="text-warning">*</span></label>
        <select name="category" required
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
            <option value="parahyangan">Parahyangan (Hubungan dengan Tuhan)</option>
            <option value="pawongan">Pawongan (Hubungan antar Manusia)</option>
            <option value="palemahan">Palemahan (Hubungan dengan Alam)</option>
        </select>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Jenis Tempat</label>
        <select name="place_type"
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
            <option value="">Belum ditentukan</option>
            @foreach (\App\Models\CulturalObject::PLACE_TYPES as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Menentukan bentuk ikon di peta. Kosong berarti ikon candi bentar.</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-gray-50/60 px-4 py-3">
        <div class="flex items-start gap-2">
            <input type="checkbox" id="cultural_is_detail" name="is_detail" value="1"
                class="text-primary focus:ring-primary mt-0.5 h-4 w-4 rounded border-gray-300">
            <label for="cultural_is_detail" class="text-sm font-semibold text-gray-700">Komponen pekarangan rumah</label>
        </div>
        <p class="mt-1.5 pl-6 text-xs text-gray-500">Untuk objek yang ada di setiap rumah adat (angkul-angkul, paon,
            merajan, saka enam). Pinnya baru muncul saat peta di-zoom dekat. Tidak memengaruhi rute edutourism.</p>
    </div>

    {{-- Description --}}
    <x-dashboard.localized-field id="localized-map-manager-form-cultural-description" label="Deskripsi objek budaya" field-name="description">
        <x-slot:indonesian>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi (ID)</label>
                <x-tiptap-editor name="description[id]" id="cultural-desc-id" :value="old('description.id', '')" placeholder="Contoh: Deskripsi detail tentang objek budaya ini..." has-image="true" />
            </div>
        </x-slot:indonesian>
        <x-slot:english>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi (EN)</label>
                <x-tiptap-editor name="description[en]" id="cultural-desc-en" :value="old('description.en', '')" placeholder="e.g. A detailed description of this cultural object..." has-image="true" />
            </div>
        </x-slot:english>
    </x-dashboard.localized-field>


    @include('admin.map-manager.partials.form-cultural.ar-model-section')

    {{-- Audio Narasi --}}
    <x-dashboard.localized-field id="map-cultural-audio" label="Audio narasi" help="MP3, OGG, WAV, M4A, atau 3GP; maksimal 10 MB per bahasa.">
        <x-slot:indonesian>
            <div>
                <label class="sr-only">Audio narasi bahasa Indonesia</label>
                @include('admin.partials.mini-audio-player', ['playerId' => 'current-audio-id'])
                <input type="file" name="cultural_audio_file[id]" accept=".mp3,.ogg,.wav,.m4a,.3gp,.3gpp,audio/*,video/3gpp,audio/3gpp"
                    class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold"
                    onchange="var maxSize=10*1024*1024;if(this.files[0]&&this.files[0].size>maxSize){Swal.fire({title:'File Terlalu Besar',text:'Maksimal 10MB per file audio.',icon:'warning',confirmButtonText:'Mengerti',background:'#ffffff'});this.value='';return;}window.previewLocalAudio('current-audio-id', this)">
            </div>
        </x-slot:indonesian>
        <x-slot:english>
            <div>
                <label class="sr-only">Audio narasi bahasa Inggris</label>
                @include('admin.partials.mini-audio-player', ['playerId' => 'current-audio-en'])
                <input type="file" name="cultural_audio_file[en]" accept=".mp3,.ogg,.wav,.m4a,.3gp,.3gpp,audio/*,video/3gpp,audio/3gpp"
                    class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold"
                    onchange="var maxSize=10*1024*1024;if(this.files[0]&&this.files[0].size>maxSize){Swal.fire({title:'File Terlalu Besar',text:'Maksimal 10MB per file audio.',icon:'warning',confirmButtonText:'Mengerti',background:'#ffffff'});this.value='';return;}window.previewLocalAudio('current-audio-en', this)">
            </div>
        </x-slot:english>
    </x-dashboard.localized-field>

    <x-dashboard.image-upload id="map-cultural-historical-images" name="historical_images[]" label="Foto sejarah"
        :multiple="true" :append="true" :max-files="20" remove-existing-name="removed_historical_images[]"
        existing-hint="Foto baru ditambahkan ke galeri. Foto pertama menjadi sampul. Penghapusan diterapkan setelah disimpan."
        accept="image/jpeg,image/png,image/webp,image/gif" />

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-500">Latitude</label>
            <input type="text" name="latitude" readonly
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-500">Longitude</label>
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
        <x-dashboard.localized-field id="localized-map-manager-form-cultural-accessibility-notes" label="Catatan aksesibilitas" field-name="accessibility_notes">
            <x-slot:indonesian>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas (ID)</label>
                    <textarea name="accessibility_notes[id]" id="cultural-accessibility-notes-id" rows="2" placeholder="Contoh: Pintu masuk landai, ramah kursi roda..."
                        class="focus:border-primary w-full resize-none rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">Akses jalan datar ramah kursi roda dan stroller bayi.</textarea>
                </div>
            </x-slot:indonesian>
            <x-slot:english>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas (EN)</label>
                    <textarea name="accessibility_notes[en]" id="cultural-accessibility-notes-en" rows="2" placeholder="e.g. Ramp access, wheelchair friendly..."
                        class="focus:border-primary w-full resize-none rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">Flat road access, friendly for wheelchairs and baby strollers.</textarea>
                </div>
            </x-slot:english>
        </x-dashboard.localized-field>

    </div>

</form>


