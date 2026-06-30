{{-- Right Map Panel (Sticky Map) --}}
<div class="lg:col-span-7 lg:h-full">
    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm h-full flex flex-col">
        <div class="mb-3 flex items-center justify-between shrink-0">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Peta Lokasi Desa Penglipuran</span>
            <button type="button" onclick="clearAllPoints()" class="text-xs font-semibold text-warning hover:underline">Hapus Semua Titik</button>
        </div>
        <div id="route-map" class="relative w-full rounded-xl border border-gray-200 shadow-inner flex-1 min-h-100 lg:min-h-0" style="z-index: 0;">
            <x-map-style-fab size="sm" class="absolute bottom-4 right-4 z-1000" />
        </div>
        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-[11px] text-gray-500 shrink-0">
            <div class="flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full" style="background-color: #1E5128"></span> Objek Budaya
            </div>
            <div class="flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full" style="background-color: #8B5CF6"></span> UMKM / Toko
            </div>
            <div class="flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full" style="background-color: #3B82F6"></span> Fasilitas Umum
            </div>
            <div class="flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full" style="background-color: #06B6D4"></span> Toilet
            </div>
            <div class="flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-full" style="background-color: #F59E0B"></span> Aksesibilitas
            </div>
        </div>
    </div>
</div>
