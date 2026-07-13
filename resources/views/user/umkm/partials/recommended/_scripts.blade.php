{{-- ponytail: partial dipecah untuk keterbacaan --}}
    <script>
        (function() {
            let mapInstance = null;

            const initRecommended = function() {
                // Map trigger
                const mapEl = document.getElementById('map');
                if (mapEl && !mapInstance) {
                    @if ($umkm->mapLocation)
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

                        L.marker([lat, lng], {
                                icon: customIcon
                            })
                            .bindPopup(`<b>{{ $umkm->business_name }}</b>`)
                            .addTo(mapInstance);
                    @endif
                }
            };

            // Run when Leaflet is ready
            const checkAndInitMap = () => {
                if (typeof L !== 'undefined' || !document.getElementById('map')) {
                    initRecommended();
                } else {
                    setTimeout(checkAndInitMap, 50);
                }
            };
            checkAndInitMap();

            // Clean up Leaflet map instance on Livewire navigation
            document.addEventListener('livewire:navigating', function cleanup(e) {
                if (mapInstance) {
                    mapInstance.remove();
                    mapInstance = null;
                }
                document.removeEventListener('livewire:navigating', cleanup);
            });
        })();

        function scrollToMap() {
            if (navigator.vibrate) navigator.vibrate(50);
            const mapSec = document.getElementById('map-section');
            if (mapSec) {
                mapSec.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }
    </script>
