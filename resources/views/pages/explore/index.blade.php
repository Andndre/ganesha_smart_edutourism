@extends('layouts.app')

@section('title', 'Peta Interaktif - Penglipuran Smart Tour')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* Sembunyikan atribusi leaflet yang terlalu besar di HP */
        .leaflet-control-attribution {
            display: none !important;
        }

        /* Hilangkan efek outline saat klik marker */
        .leaflet-container:focus {
            outline: none;
        }

        /* Animasi Bottom Sheet */
        .bottom-sheet-enter {
            transform: translateY(100%);
        }

        .bottom-sheet-active {
            transform: translateY(0);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Heatmap Gradient Overlay */
        .heatmap-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 1;
            opacity: 0.4;
            mix-blend-mode: multiply;
        }

        .heatmap-cell {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.8) 0%, rgba(249, 115, 22, 0.6) 30%, rgba(234, 179, 8, 0.4) 60%, rgba(34, 197, 94, 0.2) 80%, transparent 100%);
        }

        /* My Location Arrow */
        .location-arrow {
            position: absolute;
            width: 24px;
            height: 24px;
            pointer-events: none;
            z-index: 500;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .location-pulse {
            position: absolute;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(30, 81, 40, 0.3);
            animation: pulse 2s infinite;
            pointer-events: none;
            z-index: 499;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.5);
                opacity: 1;
            }

            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        /* FAB Active State */
        .fab-btn-active {
            background: #1E5128 !important;
            color: white !important;
        }
    </style>
@endpush

@section('content')
    <div class="fixed inset-x-0 bottom-0 top-14 z-0 overflow-hidden bg-[#E5E3DF]">
        <div id="map" class="absolute inset-0 z-0"></div>

        <!-- Heatmap Overlay Container -->
        <div id="heatmap-overlay" class="heatmap-overlay"></div>

        @include('pages.explore.components.map-search')
        @include('pages.explore.components.map-fab')
        @include('pages.explore.components.location-sheet')
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Inisialisasi Peta (Koordinat Desa Penglipuran: -8.4216, 115.3588)
            const map = L.map('map', {
                zoomControl: false
            }).setView([-8.4216, 115.3588], 17);

            // 2. Tambahkan Tile Layer
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 20,
            }).addTo(map);

            // 3. Data Mockup Locations
            const locations = [{
                    lat: -8.4216,
                    lng: 115.3588,
                    name: "Pura Penataran",
                    cat: "Budaya",
                    desc: "Kawasan suci utama desa. Harap berpakaian sopan saat memasuki area ini."
                },
                {
                    lat: -8.4230,
                    lng: 115.3585,
                    name: "Hutan Bambu",
                    cat: "Edukasi",
                    desc: "Hutan bambu seluas 45 hektar yang melestarikan keseimbangan ekosistem."
                },
                {
                    lat: -8.4225,
                    lng: 115.3589,
                    name: "Kopi Luwak Pak Wayan",
                    cat: "UMKM",
                    desc: "Kedai kopi otentik dengan pemandangan langsung ke arsitektur rumah tradisional."
                }
            ];

            // 4. Custom Icon
            const customIcon = L.divIcon({
                className: 'custom-pin',
                html: `<div style="background-color: #1E5128; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            // 5. Render Marker
            locations.forEach(loc => {
                const marker = L.marker([loc.lat, loc.lng], {
                    icon: customIcon
                }).addTo(map);

                marker.on('click', function() {
                    openSheet(loc.name, loc.cat, loc.desc);
                    map.flyTo([loc.lat - 0.0005, loc.lng], 18, {
                        animate: true,
                        duration: 0.5
                    });
                });
            });

            // ==========================================
            // HEATMAP OVERLAY DATA (Mock visitor density)
            // ==========================================
            const heatmapData = [{
                    lat: -8.4216,
                    lng: 115.3588,
                    intensity: 0.9,
                    category: 'umkm'
                },
                {
                    lat: -8.4218,
                    lng: 115.3590,
                    intensity: 0.7,
                    category: 'facilities'
                },
                {
                    lat: -8.4220,
                    lng: 115.3585,
                    intensity: 0.5,
                    category: 'toilets'
                },
                {
                    lat: -8.4222,
                    lng: 115.3592,
                    intensity: 0.6,
                    category: 'accessibility'
                },
                {
                    lat: -8.4230,
                    lng: 115.3585,
                    intensity: 0.4,
                    category: 'umkm'
                },
                {
                    lat: -8.4215,
                    lng: 115.3582,
                    intensity: 0.8,
                    category: 'umkm'
                },
                {
                    lat: -8.4219,
                    lng: 115.3586,
                    intensity: 0.3,
                    category: 'facilities'
                },
            ];

            const activeFilters = {
                umkm: true,
                facilities: true,
                toilets: true,
                accessibility: true,
                edu_route: true,
                sos_route: true
            };

            let heatmapVisible = false;

            // ==========================================
            // MOCK ROUTES (Edu-Tourism & SOS)
            // ==========================================
            const eduRoute = L.polyline([
                [-8.4216, 115.3588],
                [-8.4225, 115.3589],
                [-8.4230, 115.3585]
            ], {
                color: '#1E5128', // Penglipuran Green
                weight: 4,
                dashArray: '8, 8',
                opacity: 0.8
            }).addTo(map);

            const sosRoute = L.polyline([
                [-8.4216, 115.3588],
                [-8.4218, 115.3590],
                [-8.4210, 115.3595]
            ], {
                color: '#E65100', // Alert Amber
                weight: 4,
                opacity: 0.9
            }).addTo(map);

            // Generate Heatmap Cells
            function renderHeatmap() {
                const overlay = document.getElementById('heatmap-overlay');
                overlay.innerHTML = '';

                const mapBounds = map.getBounds();
                const mapSize = map.getSize();

                heatmapData.forEach(point => {
                    if (!activeFilters[point.category]) return;

                    const latLng = L.latLng(point.lat, point.lng);
                    if (!mapBounds.contains(latLng)) return;

                    const pointPos = map.latLngToContainerPoint(latLng);
                    const size = 80 + (point.intensity * 60);

                    const cell = document.createElement('div');
                    cell.className = 'heatmap-cell';
                    cell.style.left = (pointPos.x - size / 2) + 'px';
                    cell.style.top = (pointPos.y - size / 2) + 'px';
                    cell.style.width = size + 'px';
                    cell.style.height = size + 'px';
                    cell.style.opacity = point.intensity * 0.6;

                    overlay.appendChild(cell);
                });
            }

            // Toggle Heatmap Visibility
            function toggleHeatmap() {
                heatmapVisible = !heatmapVisible;
                const overlay = document.getElementById('heatmap-overlay');
                const btn = document.getElementById('btn-layer-map');

                if (heatmapVisible) {
                    overlay.style.display = 'block';
                    btn.classList.add('fab-btn-active');
                    renderHeatmap();
                } else {
                    overlay.style.display = 'none';
                    btn.classList.remove('fab-btn-active');
                }
            }

            // Listen for filter changes from map-search component
            window.addEventListener('filter-change', function(e) {
                const {
                    filter,
                    active
                } = e.detail;
                activeFilters[filter] = active;

                // Toggle visibility of route layers
                if (filter === 'edu_route') {
                    if (active) map.addLayer(eduRoute);
                    else map.removeLayer(eduRoute);
                }
                
                if (filter === 'sos_route') {
                    if (active) map.addLayer(sosRoute);
                    else map.removeLayer(sosRoute);
                }

                if (heatmapVisible) {
                    renderHeatmap();
                }
            });

            // ==========================================
            // MY LOCATION FUNCTIONALITY
            // ==========================================
            let locationMarker = null;
            let locationArrow = null;
            let locationPulse = null;
            let watchId = null;
            let currentHeading = 0;
            let lastPosition = null;

            function onLocationFound(e) {
                const latlng = e.latlng;

                // Remove existing markers
                if (locationPulse) locationPulse.remove();
                if (locationArrow) locationArrow.remove();
                if (locationMarker) map.removeLayer(locationMarker);

                // Create pulse effect marker
                const pulseIcon = L.divIcon({
                    className: 'location-pulse-marker',
                    html: `<div class="location-pulse"></div>`,
                    iconSize: [40, 40],
                    iconAnchor: [20, 20]
                });

                locationMarker = L.marker(latlng, {
                    icon: L.divIcon({
                        className: 'location-marker',
                        html: `<div style="background: #1E5128; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>`,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    })
                }).addTo(map);

                // Create arrow for direction
                if (e.heading !== undefined && e.heading !== null) {
                    currentHeading = e.heading;
                } else if (lastPosition) {
                    // Calculate heading from last position
                    currentHeading = calculateHeading(lastPosition, latlng);
                }

                const arrowHtml = createDirectionArrow(currentHeading);
                locationArrow = L.marker(latlng, {
                    icon: L.divIcon({
                        className: 'direction-arrow',
                        html: arrowHtml,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    }),
                    rotationAngle: currentHeading
                }).addTo(map);

                lastPosition = {
                    lat: latlng.lat,
                    lng: latlng.lng
                };

                // Center map on user location
                map.flyTo(latlng, 17, {
                    animate: true,
                    duration: 0.5
                });
            }

            function calculateHeading(from, to) {
                const dLng = to.lng - from.lng;
                const dLat = to.lat - from.lat;
                const angle = Math.atan2(dLng, dLat) * (180 / Math.PI);
                return (angle + 360) % 360;
            }

            function createDirectionArrow(heading) {
                return `<svg class="location-arrow" viewBox="0 0 24 24" fill="#1E5128" style="transform: rotate(${heading}deg)">
                    <path d="M12 2L19 20H12H5L12 2Z" />
                </svg>`;
            }

            function onLocationError(e) {
                console.warn('Geolocation error:', e.message);
                alert('Tidak dapat mendapatkan lokasi Anda. Pastikan GPS aktif dan izin diberikan.');
            }

            function startLocationTracking() {
                if (!navigator.geolocation) {
                    alert('Geolocation tidak didukung oleh browser ini.');
                    return;
                }

                const btn = document.getElementById('btn-my-location');
                btn.classList.add('fab-btn-active');

                // Get initial position
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const latlng = L.latLng(pos.coords.latitude, pos.coords.longitude);
                        onLocationFound({
                            latlng: latlng,
                            heading: pos.coords.heading
                        });

                        // Start watching position
                        watchId = navigator.geolocation.watchPosition(
                            (pos) => {
                                const newLatlng = L.latLng(pos.coords.latitude, pos.coords.longitude);
                                onLocationFound({
                                    latlng: newLatlng,
                                    heading: pos.coords.heading
                                });
                            },
                            onLocationError, {
                                enableHighAccuracy: true,
                                maximumAge: 1000,
                                timeout: 10000
                            }
                        );
                    },
                    onLocationError, {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: 10000
                    }
                );
            }

            function stopLocationTracking() {
                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }

                if (locationPulse) locationPulse.remove();
                if (locationArrow) locationArrow.remove();
                if (locationMarker) map.removeLayer(locationMarker);

                locationPulse = null;
                locationArrow = null;
                locationMarker = null;

                const btn = document.getElementById('btn-my-location');
                btn.classList.remove('fab-btn-active');
            }

            let isTrackingLocation = false;

            function toggleMyLocation() {
                if (isTrackingLocation) {
                    stopLocationTracking();
                    isTrackingLocation = false;
                } else {
                    startLocationTracking();
                    isTrackingLocation = true;
                }
            }

            // ==========================================
            // BUTTON EVENT LISTENERS
            // ==========================================
            document.getElementById('btn-layer-map').addEventListener('click', function(e) {
                e.stopPropagation();
                toggleHeatmap();
            });

            document.getElementById('btn-my-location').addEventListener('click', function(e) {
                e.stopPropagation();
                toggleMyLocation();
            });

            document.getElementById('btn-locate').addEventListener('click', function() {
                map.flyTo([-8.4216, 115.3588], 17, {
                    animate: true,
                    duration: 0.5
                });
            });

            // Update heatmap on map move/zoom
            map.on('moveend', function() {
                if (heatmapVisible) {
                    renderHeatmap();
                }
            });

            map.on('zoomend', function() {
                if (heatmapVisible) {
                    renderHeatmap();
                }
            });

            // Close filter panel when clicking elsewhere on map
            map.on('click', function() {
                const panel = document.getElementById('filter-panel');
                if (panel && !panel.classList.contains('hidden')) {
                    panel.classList.add('hidden');
                }
            });
        });

        // ==========================================
        // LOGIKA BOTTOM SHEET
        // ==========================================
        const sheet = document.getElementById('location-sheet');

        function openSheet(name, category, desc) {
            document.getElementById('sheet-title').textContent = name;
            document.getElementById('sheet-category').textContent = category;
            document.getElementById('sheet-desc').textContent = desc;

            sheet.classList.remove('translate-y-full');
            sheet.classList.add('translate-y-0');
        }

        function closeSheet() {
            sheet.classList.remove('translate-y-0');
            sheet.classList.add('translate-y-full');
        }
    </script>
@endpush
