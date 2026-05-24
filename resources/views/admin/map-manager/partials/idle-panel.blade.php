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
</div>
