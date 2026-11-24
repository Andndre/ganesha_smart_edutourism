{{-- EDITOR PANEL: Create / Edit Form container --}}
<div id="panel-editor"
    class="hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm flex-col space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <h2 id="editor-title" class="font-bold text-charcoal text-lg">Tambah Lokasi Baru</h2>
        <button type="button" onclick="cancelEditor()"
            aria-label="Tutup panel lokasi" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl text-gray-500 hover:text-charcoal transition-colors focus-visible:outline-2 focus-visible:outline-primary">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div id="selector-container" data-house-create-url="{{ route('admin.houses.create') }}">
        <div id="location-type-choices" class="space-y-4">
            <fieldset aria-describedby="location-type-help">
                <legend class="text-base font-semibold text-charcoal">Apa yang ingin ditambahkan?</legend>
                <p id="location-type-help" class="mt-1 text-sm leading-relaxed text-gray-600">Pilih satu jenis untuk titik yang sudah Anda tentukan di peta.</p>
                <div class="mt-4 grid gap-2">
                    @foreach([
                        'house' => ['Rumah warga', 'Nomor rumah, data keluarga, dan pengelola.'],
                        'umkm' => ['Usaha', 'Kafe, warung, toko, atau usaha lainnya.'],
                        'cultural' => ['Objek budaya', 'Bangunan adat, pura, atau tempat bersejarah.'],
                        'facility' => ['Fasilitas umum', 'Toilet, tempat parkir, atau fasilitas pengunjung.'],
                    ] as $value => [$label, $description])
                        <label class="flex min-h-16 cursor-pointer items-start gap-3 rounded-xl border border-gray-300 p-3 has-checked:border-primary has-checked:bg-primary/5 hover:border-primary/50">
                            <input type="radio" name="map_location_type" value="{{ $value }}" class="mt-1 h-4 w-4 shrink-0 accent-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                            <span><span class="block text-sm font-semibold text-charcoal">{{ $label }}</span><span class="mt-1 block text-xs leading-relaxed text-gray-600">{{ $description }}</span></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <p id="location-type-error" hidden class="text-sm font-semibold text-red-700" role="alert">Pilih jenis lokasi terlebih dahulu.</p>
            <div class="flex gap-2">
                <button id="location-type-continue" type="button" class="min-h-12 flex-1 rounded-xl bg-primary px-4 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Lanjutkan</button>
                <button id="location-type-cancel" type="button" class="min-h-12 rounded-xl border border-gray-300 px-4 font-semibold text-gray-600 focus-visible:outline-2 focus-visible:outline-primary">Batal</button>
            </div>
        </div>
        <div id="location-type-selected" hidden class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-surface px-3 py-2">
            <span id="location-type-label" class="text-sm font-semibold text-charcoal"></span>
            <button id="location-type-change" type="button" class="min-h-11 text-sm font-semibold text-primary underline focus-visible:outline-2 focus-visible:outline-primary">Ganti jenis lokasi</button>
        </div>
    </div>

    @include('admin.map-manager.partials.form-cultural')
    @include('admin.map-manager.partials.form-umkm')
    @include('admin.map-manager.partials.form-facility')

    {{-- Drawer modal form for Tambah Baru AR Model (outside all forms) --}}
    @include('admin.ar-manager.partials.modal-form')

    {{-- STICKY ACTION BUTTONS --}}
    <div id="map-editor-actions" class="sticky bottom-0 z-20 bg-white border-t border-gray-100 pt-4 pb-6 px-6 -mx-6 -mb-6 mt-4 rounded-b-2xl shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.03)] flex flex-col gap-3">
        <div class="flex gap-2">
            <button type="submit" id="btn-global-submit" form="form-cultural"
                class="flex-1 rounded-xl bg-primary py-2.5 text-sm font-semibold text-white transition-all hover:bg-primary-600 shadow-sm">Simpan</button>
            <button type="button" onclick="cancelEditor()"
                class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50 shadow-sm">Batal</button>
        </div>

        {{-- ADMIN UMKM MANAGE CONTAINER (Only shown on Edit UMKM) --}}
        <div id="umkm-admin-manage-container" class="hidden">
            <a href="" id="btn-umkm-admin-manage" target="_blank"
                class="w-full rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-all flex items-center justify-center gap-2 shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Manage Shop & Complaints
            </a>
        </div>

        {{-- ADD POINT BUTTON (Only shown when editing an existing cultural object/facility) --}}
        <div id="add-point-container" class="hidden">
            <button type="button" onclick="startAddPointMode()"
                class="w-full rounded-xl border border-primary/30 py-2.5 text-sm font-semibold text-primary hover:bg-primary/5 transition-all flex items-center justify-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Titik untuk Lokasi Ini
            </button>
        </div>

        {{-- DELETE BUTTON FORM (Hidden on Create) --}}
        <div id="delete-container" class="hidden">
            <form id="form-delete" action="" method="POST" class="delete-form"
                data-confirm="{{ 'Apakah Anda yakin ingin menghapus lokasi ini?' }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full rounded-xl border border-red-200 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 transition-all flex items-center justify-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Lokasi Ini
                </button>
            </form>
        </div>
    </div>
</div>
