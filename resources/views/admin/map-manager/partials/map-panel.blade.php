{{-- Right Side Panel: The Interactive Map --}}
<div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm h-full flex flex-col">
    <div id="location-map"
        class="w-full rounded-xl border border-gray-200 shadow-inner flex-1 min-h-112.5 lg:min-h-0"
        style="z-index: 0;"></div>

    {{-- Legend --}}
    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-gray-500 shrink-0">
        <div class="flex items-center gap-1.5">
            <span class="h-3 w-3 rounded-full" style="background-color: #1E5128"></span> Objek Budaya
        </div>
        <div class="flex items-center gap-1.5">
            <span class="h-3 w-3 rounded-full" style="background-color: #8B5CF6"></span> UMKM / Toko
        </div>
        <div class="flex items-center gap-1.5">
            <span class="h-3 w-3 rounded-full" style="background-color: #3B82F6"></span> Fasilitas Umum
        </div>
        <div class="flex items-center gap-1.5">
            <span class="h-3 w-3 rounded-full" style="background-color: #06B6D4"></span> Toilet
        </div>
    </div>
</div>
