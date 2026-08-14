{{-- ponytail: partial dipecah untuk keterbacaan --}}
    <script>
        (function() {
            const routeData = @json($route);
            const mapCoordinates = routeData.map(stop => {
                const umkm = stop.umkm;
                if (!umkm) return null;
                const loc = umkm.map_location || umkm.mapLocation;
                return loc ? [parseFloat(loc.latitude), parseFloat(loc.longitude)] : null;
            }).filter(coord => coord !== null);

            // Initialize Map
            let mapInstance = null;
            const initMap = () => {
                const mapEl = document.getElementById('map');
                if (mapEl) {
                    mapInstance = L.map(mapEl, {
                        zoomControl: false
                    });

                    // Same tiles as the explore map so both screens read as one product
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 20,
                        maxNativeZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(mapInstance);

                    // Add markers
                    const bounds = L.latLngBounds();

                    routeData.forEach((stop, index) => {
                        const umkm = stop.umkm;
                        if (!umkm) return;
                        const loc = umkm.map_location || umkm.mapLocation;
                        if (!loc) return;

                        const lat = parseFloat(loc.latitude);
                        const lng = parseFloat(loc.longitude);
                        bounds.extend([lat, lng]);

                        L.marker([lat, lng], {
                                icon: window.gseMapPin(null, {
                                    number: index + 1,
                                    color: '#F97316'
                                })
                            })
                            .bindPopup(`<b>${umkm.business_name || 'UMKM'}</b>`)
                            .addTo(mapInstance);
                    });

                    if (mapCoordinates.length > 0) {
                        mapInstance.fitBounds(bounds, {
                            padding: [30, 30]
                        });
                    }

                    // Attempt to draw route using local OpenRouteService
                    if (mapCoordinates.length >= 2) {
                        // ORS expects [lng, lat]
                        const orsCoordinates = mapCoordinates.map(coord => [parseFloat(coord[1]), parseFloat(coord[0])]);

                        fetch('/api/routing/directions', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    coordinates: orsCoordinates
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.features && data.features.length > 0) {
                                    const geojson = data.features[0];
                                    L.geoJSON(geojson, {
                                        style: {
                                            color: '#F97316', // Primary color
                                            weight: 4,
                                            opacity: 0.8,
                                            dashArray: '10, 10'
                                        }
                                    }).addTo(mapInstance);
                                } else {
                                    // Fallback: draw straight lines if routing fails
                                    drawStraightLines();
                                }
                            })
                            .catch(err => {
                                console.error('Routing failed:', err);
                                drawStraightLines();
                            });
                    }
                }
            };
            
            const checkAndInitMap = () => {
                if (typeof L !== 'undefined' || !document.getElementById('map')) {
                    initMap();
                } else {
                    setTimeout(checkAndInitMap, 50);
                }
            };
            checkAndInitMap();

            function drawStraightLines() {
                if (mapInstance) {
                    L.polyline(mapCoordinates, {
                        color: '#F97316',
                        weight: 3,
                        opacity: 0.6,
                        dashArray: '5, 10'
                    }).addTo(mapInstance);
                }
            }

            function startNavigation() {
                if (navigator.vibrate) navigator.vibrate(50);

                // MapLocation IDs in route order — explore resolves & highlights them
                const stopIds = routeData
                    .map(stop => stop.umkm && (stop.umkm.map_location || stop.umkm.mapLocation))
                    .filter(loc => loc && loc.id)
                    .map(loc => loc.id);

                window.location.href = `/explore?action=multi_route&stops=${stopIds.join(',')}`;
            }

            // Expose required functions to window for inline HTML onclick attributes
            window.startNavigation = startNavigation;

            // Clean up Leaflet map instance on Livewire navigation
            document.addEventListener('livewire:navigating', function cleanup(e) {
                if (mapInstance) {
                    mapInstance.remove();
                    mapInstance = null;
                }
                delete window.startNavigation;
                document.removeEventListener('livewire:navigating', cleanup);
            });
        })();
    </script>
