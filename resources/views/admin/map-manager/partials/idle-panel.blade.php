{{-- IDLE PANEL: Default instructions & filters --}}
<div id="panel-idle" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm space-y-5">
    <div>
        <h2 class="font-semibold text-charcoal flex items-center gap-2">
            <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Panduan Navigasi Peta
        </h2>
        <p class="mt-2 text-xs text-gray-500 leading-relaxed">
            1. <strong>Klik area kosong</strong> pada peta untuk meletakkan pin baru dan menambahkan data
            lokasi.<br>
            2. <strong>Klik marker/penanda</strong> yang sudah ada untuk melihat detail, mengubah informasi,
            atau menyeret (drag) lokasinya.<br>
            3. <strong>Gunakan filter</strong> di bawah untuk menyaring tampilan penanda di peta.
        </p>
    </div>

    <div class="border-t border-gray-100 pt-4">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Filter Kategori Peta</h3>
        <div class="space-y-2.5">
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" id="filter-cultural" checked
                    class="rounded border-gray-300 text-primary focus:ring-primary h-4.5 w-4.5"
                    onchange="filterMarkers()">
                <span class="h-3.5 w-3.5 rounded-full" style="background-color: #1E5128"></span>
                <span class="text-xs font-semibold text-gray-700">Objek Budaya (<span
                        id="count-cultural">0</span>)</span>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" id="filter-umkm" checked
                    class="rounded border-gray-300 text-primary focus:ring-primary h-4.5 w-4.5"
                    onchange="filterMarkers()">
                <span class="h-3.5 w-3.5 rounded-full" style="background-color: #8B5CF6"></span>
                <span class="text-xs font-semibold text-gray-700">UMKM / Toko (<span
                        id="count-umkm">0</span>)</span>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" id="filter-facility" checked
                    class="rounded border-gray-300 text-primary focus:ring-primary h-4.5 w-4.5"
                    onchange="filterMarkers()">
                <span class="h-3.5 w-3.5 rounded-full" style="background-color: #3B82F6"></span>
                <span class="text-xs font-semibold text-gray-700">Fasilitas Umum (<span
                        id="count-facility">0</span>)</span>
            </label>
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" id="filter-toilet" checked
                    class="rounded border-gray-300 text-primary focus:ring-primary h-4.5 w-4.5"
                    onchange="filterMarkers()">
                <span class="h-3.5 w-3.5 rounded-full" style="background-color: #06B6D4"></span>
                <span class="text-xs font-semibold text-gray-700">Toilet (<span
                        id="count-toilet">0</span>)</span>
            </label>
        </div>
    </div>

    <div class="border-t border-gray-100 pt-4 space-y-3">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Bulk Import Objek Budaya (Excel)</h3>
        <p class="text-[10px] text-gray-500 leading-relaxed">
            Gunakan fitur ini untuk menambahkan banyak lokasi objek budaya sekaligus menggunakan berkas Excel (.xlsx).
        </p>
        <div class="flex flex-col gap-2">
            <a href="{{ route('admin.cultural-objects.import-template') }}" 
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 px-4 py-2.5 text-xs font-semibold text-charcoal transition-all active:scale-95">
                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Unduh Template Excel
            </a>
            
            <form action="{{ route('admin.cultural-objects.import-xlsx') }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                @csrf
                <div class="relative flex items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-white p-4 hover:border-primary/50 transition-colors">
                    <input type="file" name="file" accept=".xlsx" required
                        class="absolute inset-0 cursor-pointer opacity-0"
                        onchange="document.getElementById('import-file-name').textContent = this.files[0] ? this.files[0].name : 'Pilih file Excel (.xlsx)';" />
                    <div class="text-center space-y-1">
                        <svg class="mx-auto h-6.5 w-6.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span id="import-file-name" class="block text-[10px] font-semibold text-gray-500">Pilih file Excel (.xlsx)</span>
                    </div>
                </div>
                <button type="submit"
                    class="bg-primary hover:bg-primary-600 shadow-primary/10 flex w-full items-center justify-center gap-2 rounded-xl py-2.5 text-xs font-semibold text-white shadow-lg transition-all active:scale-95">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Unggah & Import Data
                </button>
            </form>
        </div>
    </div>
</div>
