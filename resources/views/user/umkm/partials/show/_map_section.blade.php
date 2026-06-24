{{-- ponytail: partial dipecah untuk keterbacaan --}}
@if ($umkm->mapLocation)
    <!-- Store Location Map -->
    <div class="border-y border-gray-100 bg-white px-5 py-6 lg:rounded-3xl lg:border lg:border-gray-100 lg:shadow-sm lg:px-7 lg:py-7" id="map-section">
        <h3 class="text-charcoal mb-3 flex items-center gap-2 font-bold">
            <svg class="text-primary h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ __('Lokasi Toko') }}
        </h3>

        <div class="rounded-xl border border-gray-100 bg-gray-50 p-2">
            <div id="map"></div>
        </div>
        <p class="mt-2 text-center text-xs text-gray-500">{{ __('Peta menunjukkan lokasi toko UMKM di Desa Penglipuran.') }}</p>
    </div>

    @push('scripts')
        <script>
            (function() {
                let mapInstance = null;

                const initMap = function() {
                    const mapEl = document.getElementById('map');
                    if (mapEl && !mapInstance) {
                        const lat = {{ $umkm->mapLocation->latitude }};
                        const lng = {{ $umkm->mapLocation->longitude }};

                        mapInstance = L.map(mapEl, {
                            zoomControl: false,
                            attributionControl: false
                        }).setView([lat, lng], 17);

                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            maxZoom: 20
                        }).addTo(mapInstance);

                        const customIcon = L.divIcon({
                            className: 'custom-div-icon',
                            html: `<div class="marker-pin"></div>`,
                            iconSize: [30, 42],
                            iconAnchor: [15, 42]
                        });

                        L.marker([lat, lng], { icon: customIcon })
                            .bindPopup(`<b>{{ $umkm->business_name }}</b>`)
                            .addTo(mapInstance);
                    }
                };

                const checkAndInitMap = () => {
                    if (typeof L !== 'undefined' && document.getElementById('map')) {
                        initMap();
                    } else {
                        setTimeout(checkAndInitMap, 50);
                    }
                };
                checkAndInitMap();

                document.addEventListener('livewire:navigating', function cleanup(e) {
                    if (mapInstance) {
                        mapInstance.remove();
                        mapInstance = null;
                    }
                    document.removeEventListener('livewire:navigating', cleanup);
                });
            })();
        </script>
    @endpush
@endif
