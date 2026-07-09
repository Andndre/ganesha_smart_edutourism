{{-- Shared create/edit form for cultural objects. $object is null on create. --}}
@php $isEdit = isset($object); @endphp

<form id="cultural-object-form"
    action="{{ $isEdit ? route('admin.cultural-objects.update', $object->id) : route('admin.cultural-objects.store') }}"
    method="POST" enctype="multipart/form-data" class="space-y-4" x-data="{ locale: 'id' }">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    <input type="hidden" name="redirect_to" value="cultural-objects">

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        {{-- Locale tabs --}}
        <div class="sticky top-0 z-10 -mx-6 mb-4 flex gap-2 border-b border-gray-100 bg-white px-6 py-3">
            <button @click="locale = 'id'" :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                class="rounded-xl px-4 py-2 text-sm font-semibold transition-all" type="button">Indonesia</button>
            <button @click="locale = 'en'" :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                class="rounded-xl px-4 py-2 text-sm font-semibold transition-all" type="button">English</button>
        </div>

        <div class="space-y-4">
            {{-- Name --}}
            <div x-show="locale === 'en'">
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Objek Budaya (EN) <span class="text-warning">*</span></label>
                <input type="text" name="name[en]" required placeholder="e.g. Penataran Agung Temple"
                    class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
                    value="{{ old('name.en', $isEdit ? ($object->getTranslation('name', 'en') ?? '') : '') }}">
            </div>
            <div x-show="locale === 'id'">
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Objek Budaya (ID) <span class="text-warning">*</span></label>
                <input type="text" name="name[id]" required placeholder="Contoh: Pura Penataran Agung"
                    class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
                    value="{{ old('name.id', $isEdit ? ($object->getTranslation('name', 'id') ?? '') : '') }}">
            </div>

            {{-- Short Description --}}
            <div x-show="locale === 'en'">
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Singkat (EN) <span class="text-warning">*</span></label>
                <input type="text" name="short_description[en]" required placeholder="e.g. Spiritual Heart of Penglipuran Village"
                    class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
                    value="{{ old('short_description.en', $isEdit ? ($object->getTranslation('short_description', 'en') ?? '') : '') }}">
            </div>
            <div x-show="locale === 'id'">
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Singkat (ID) <span class="text-warning">*</span></label>
                <input type="text" name="short_description[id]" required placeholder="Contoh: Jantung Spiritual Desa Penglipuran"
                    class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none"
                    value="{{ old('short_description.id', $isEdit ? ($object->getTranslation('short_description', 'id') ?? '') : '') }}">
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tri Hita Karana <span class="text-warning">*</span></label>
                <select name="category" required
                    class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @php $currentCategory = old('category', $isEdit ? $object->category : 'parahyangan'); @endphp
                    <option value="parahyangan" @selected($currentCategory === 'parahyangan')>Parahyangan (Hubungan dengan Tuhan)</option>
                    <option value="pawongan" @selected($currentCategory === 'pawongan')>Pawongan (Hubungan antar Manusia)</option>
                    <option value="palemahan" @selected($currentCategory === 'palemahan')>Palemahan (Hubungan dengan Alam)</option>
                </select>
            </div>

            {{-- Description --}}
            <div x-show="locale === 'en'">
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi (EN)</label>
                <x-tiptap-editor name="description[en]" id="cultural-desc-en"
                    :value="old('description.en', $isEdit ? ($object->getTranslation('description', 'en') ?? '') : '')"
                    placeholder="e.g. A detailed description of this cultural object..." has-image="true" />
            </div>
            <div x-show="locale === 'id'">
                <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi (ID)</label>
                <x-tiptap-editor name="description[id]" id="cultural-desc-id"
                    :value="old('description.id', $isEdit ? ($object->getTranslation('description', 'id') ?? '') : '')"
                    placeholder="Contoh: Deskripsi detail tentang objek budaya ini..." has-image="true" />
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        @include('admin.map-manager.partials.form-cultural.ar-model-section')
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">
        {{-- Audio Narasi --}}
        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700">Audio Narasi (ID) / (EN)</label>
            <span class="mb-2 block text-xs text-gray-500">MP3/OGG/WAV/M4A, maks. 10 MB per bahasa</span>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <span class="mb-1 block text-[10px] font-semibold text-gray-500">Audio (ID)</span>
                    @include('admin.partials.mini-audio-player', ['playerId' => 'current-audio-id'])
                    <input type="file" name="cultural_audio_file[id]" accept=".mp3,.ogg,.wav,.m4a,audio/*"
                        class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
                </div>
                <div>
                    <span class="mb-1 block text-[10px] font-semibold text-gray-500">Audio (EN)</span>
                    @include('admin.partials.mini-audio-player', ['playerId' => 'current-audio-en'])
                    <input type="file" name="cultural_audio_file[en]" accept=".mp3,.ogg,.wav,.m4a,audio/*"
                        class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
                </div>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-semibold text-gray-700">Foto Sejarah</label>
            <span class="mb-2 block text-xs text-gray-500">Dapat memilih beberapa file gambar sekaligus</span>
            <input type="file" name="historical_images[]" multiple accept="image/*"
                class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-xs text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold">
            @if ($isEdit && !empty($object->historical_images))
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach ($object->historical_images as $img)
                        <img src="{{ asset('storage/'.$img) }}" class="h-10 w-10 rounded border border-gray-100 object-cover">
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="sticky bottom-0 z-20 -mx-6 -mb-6 mt-4 flex gap-2 rounded-b-2xl border-t border-gray-100 bg-white px-6 pb-6 pt-4 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.03)]">
        <button type="submit" class="flex-1 rounded-xl bg-primary py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-600">Simpan</button>
        <a href="{{ route('admin.cultural-objects') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 shadow-sm hover:bg-gray-50">Batal</a>
    </div>
</form>
