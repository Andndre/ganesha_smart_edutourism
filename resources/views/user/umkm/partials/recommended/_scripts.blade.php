{{-- ponytail: partial dipecah untuk keterbacaan --}}
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        (function() {
            let mapInstance = null;

            const initRecommended = function() {
                // 1. Confetti trigger
                const hasConfetti = document.querySelector('.bg-primary\\/10');
                if (hasConfetti) {
                    var duration = 3 * 1000;
                    var animationEnd = Date.now() + duration;
                    var defaults = {
                        startVelocity: 30,
                        spread: 360,
                        ticks: 60,
                        zIndex: 100
                    };

                    function randomInRange(min, max) {
                        return Math.random() * (max - min) + min;
                    }

                    var interval = setInterval(function() {
                        var timeLeft = animationEnd - Date.now();

                        if (timeLeft <= 0) {
                            return clearInterval(interval);
                        }

                        var particleCount = 50 * (timeLeft / duration);
                        confetti(Object.assign({}, defaults, {
                            particleCount,
                            origin: {
                                x: randomInRange(0.1, 0.3),
                                y: Math.random() - 0.2
                            }
                        }));
                        confetti(Object.assign({}, defaults, {
                            particleCount,
                            origin: {
                                x: randomInRange(0.7, 0.9),
                                y: Math.random() - 0.2
                            }
                        }));
                    }, 250);
                }

                // 2. Map trigger
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
