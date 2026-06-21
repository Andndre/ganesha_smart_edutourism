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
                        zoomControl: false,
                        attributionControl: false
                    });

                    // Add CartoDB Positron tiles for a clean look
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        maxZoom: 20
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

                        const iconHtml = `
                        <div class="marker-pin"></div>
                        <div class="marker-number">${index + 1}</div>
                    `;

                        const customIcon = L.divIcon({
                            className: 'custom-div-icon',
                            html: iconHtml,
                            iconSize: [30, 42],
                            iconAnchor: [15, 42]
                        });

                        L.marker([lat, lng], {
                                icon: customIcon
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

                // Extract coordinate strings: "lat,lng|lat,lng"
                const coordsStr = mapCoordinates.map(coord => coord.join(',')).join('|');

                // Redirect to explore page with action=multi_route and coordinates
                window.location.href = `/explore?action=multi_route&stops=${encodeURIComponent(coordsStr)}`;
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
