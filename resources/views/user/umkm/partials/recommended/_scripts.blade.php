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

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 20,
                            maxNativeZoom: 19
                        }).addTo(mapInstance);

                        L.marker([lat, lng], {
                                icon: window.gseMapPin('umkm')
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
