{{-- EDITOR PANEL: Create / Edit Form container --}}
<div id="panel-editor"
    class="hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm flex-col space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <h2 id="editor-title" class="font-bold text-charcoal text-lg">Tambah Lokasi Baru</h2>
        <button type="button" onclick="cancelEditor()"
            class="text-gray-400 hover:text-charcoal transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Type Selector --}}
    <div id="selector-container">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Tipe Lokasi <span
                class="text-warning">*</span></label>
        <select id="type-selector" onchange="switchForm(this.value)"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            <option value="cultural">Objek Budaya</option>
            <option value="umkm">UMKM / Toko</option>
            <option value="facility">Fasilitas Umum</option>
        </select>
    </div>

    @include('admin.map-manager.partials.form-cultural')
    @include('admin.map-manager.partials.form-umkm')
    @include('admin.map-manager.partials.form-facility')

    {{-- STICKY ACTION BUTTONS --}}
    <div class="sticky bottom-0 z-20 bg-white border-t border-gray-100 pt-4 pb-6 px-6 -mx-6 -mb-6 mt-4 rounded-b-2xl shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.03)] flex flex-col gap-3">
        <div class="flex gap-2">
            <button type="submit" id="btn-global-submit" form="form-cultural"
                class="flex-1 rounded-xl bg-primary py-2.5 text-sm font-semibold text-white transition-all hover:bg-primary-600 shadow-sm">Simpan</button>
            <button type="button" onclick="cancelEditor()"
                class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50 shadow-sm">Batal</button>
        </div>
        
        {{-- DELETE BUTTON FORM (Hidden on Create) --}}
        <div id="delete-container" class="hidden">
            <form id="form-delete" action="" method="POST" class="delete-form"
                data-confirm="{{ __('Apakah Anda yakin ingin menghapus lokasi ini?') }}">
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
