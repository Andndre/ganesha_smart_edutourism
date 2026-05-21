<div id="location-sheet"
    class="fixed inset-x-0 bottom-0 z-50 translate-y-full transform transition-transform duration-300 ease-out">
    <div
        class="pointer-events-auto rounded-t-3xl border-t border-gray-100 bg-white px-5 pb-24 pt-2 shadow-[0_-8px_30px_rgba(0,0,0,0.12)]">
        <div class="mx-auto mb-5 h-1.5 w-12 rounded-full bg-gray-200"></div>
        <div class="mb-4 flex items-start justify-between">
            <div>
                <h3 id="sheet-title" class="font-display text-charcoal text-xl font-bold">Nama Lokasi</h3>
                <p id="sheet-category" class="text-primary mt-1 text-xs font-semibold uppercase tracking-wider">Kategori
                </p>
            </div>
            <button type="button" onclick="closeSheet()"
                class="hover:text-charcoal -mr-2 rounded-full bg-gray-50 p-2 text-gray-400 active:scale-95">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p id="sheet-desc" class="mb-6 line-clamp-3 text-sm leading-relaxed text-gray-500">Pilih salah satu titik di
            peta untuk melihat detail.</p>
        <div class="flex gap-3">
            <button
                class="text-charcoal flex flex-1 items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white py-3 text-sm font-bold active:scale-95">Simpan</button>
            <a href="#" id="sheet-action"
                class="bg-primary flex flex-1 items-center justify-center gap-2 rounded-xl py-3 text-sm font-bold text-white shadow-lg active:scale-95">Arahkan</a>
        </div>
    </div>
</div>
