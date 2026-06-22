{{-- Real-time Map --}}
<div class="relative mt-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-charcoal font-semibold">Pemantauan Lokasi Real-time & Geofence</h3>
        <p class="text-xs text-gray-500">Anda dapat menggambar poligon zona pada map ini melalui form Edit/Buat Zona.</p>
    </div>
    <div class="relative h-[500px] w-full overflow-hidden rounded-xl border border-gray-200">
        <div id="map" class="absolute inset-0 z-0"></div>
        
        <!-- Heatmap Control FAB -->
        <button id="btn-admin-heatmap"
            class="absolute bottom-4 right-4 z-1000 flex h-10 w-10 items-center justify-center rounded-full border border-gray-100 bg-white text-gray-600 shadow-md transition-all hover:bg-gray-50 active:scale-95"
            title="Toggle Real Heatmap">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
            </svg>
        </button>
    </div>
</div>

{{-- Historical 24h chart --}}
<div class="mt-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <h3 class="text-charcoal mb-4 font-semibold">Tren Kunjungan 24 Jam Terakhir</h3>
    <canvas id="capacityChart" class="w-full" height="160"></canvas>
</div>
