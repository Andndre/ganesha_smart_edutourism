{{-- ponytail: partial dipecah untuk keterbacaan --}}
        <!-- Route Guidance / Embedded Map -->
        <div class="mt-2 border-y border-gray-100 bg-white px-4 py-5" id="map-section">
            <h3 class="text-charcoal mb-3 flex items-center gap-2 font-bold">
                <svg class="text-primary h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ __('Lokasi UMKM') }}
            </h3>

            <div class="rounded-xl border border-gray-100 bg-gray-50 p-2">
                <div id="map"></div>
            </div>
            <p class="mt-2 text-center text-xs text-gray-500">{{ __('Peta ini menunjukkan lokasi UMKM di Desa Penglipuran.') }}</p>
        </div>
