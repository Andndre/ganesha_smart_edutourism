{{-- Estimated Metrics --}}
<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <h2 class="mb-4 font-semibold text-charcoal flex items-center gap-2">
        <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        Mode & Kalkulasi Rute
    </h2>

    {{-- Routing warning banner --}}
    <div id="routing-warning" class="hidden mb-4 rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">
        <div class="flex items-start gap-2">
            <svg class="h-4 w-4 shrink-0 text-amber-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <span class="font-bold">Info:</span> Jarak rute terdeteksi 0 m (area bebas kendaraan/desa adat). Sistem secara otomatis mengaktifkan rute alternatif garis lurus.
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="rounded-xl bg-gray-50 p-4 border border-gray-100">
            <span class="text-xs text-gray-400 font-semibold block uppercase">Total Jarak</span>
            <span id="route-distance-display" class="text-xl font-bold text-charcoal block mt-1">
                {{ isset($route) && $route->distance_meters ? ($route->distance_meters >= 1000 ? number_format($route->distance_meters / 1000, 2) . ' km' : $route->distance_meters . ' m') : '0 m' }}
            </span>
            <input type="hidden" name="distance_meters" id="field-distance" value="{{ isset($route) ? $route->distance_meters : '0' }}">
        </div>
        <div class="rounded-xl bg-gray-50 p-4 border border-gray-100">
            <span class="text-xs text-gray-400 font-semibold block uppercase">Total Durasi</span>
            <span id="route-duration-display" class="text-xl font-bold text-charcoal block mt-1">
                {{ isset($route) && $route->estimated_duration_minutes ? ($route->estimated_duration_minutes >= 60 ? floor($route->estimated_duration_minutes / 60) . ' jam ' . ($route->estimated_duration_minutes % 60) . ' menit' : $route->estimated_duration_minutes . ' menit') : '0 menit' }}
            </span>
            <input type="hidden" name="estimated_duration_minutes" id="field-duration" value="{{ isset($route) ? $route->estimated_duration_minutes : '0' }}">
        </div>
    </div>
    <p class="mt-2.5 text-[11px] text-gray-400 italic leading-relaxed">
        * Durasi dihitung otomatis berdasarkan akumulasi waktu perjalanan kaki dan estimasi durasi kunjungan di setiap titik.
    </p>
</div>
