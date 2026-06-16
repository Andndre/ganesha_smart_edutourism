@extends('layouts.app')

@section('title', 'Peta Interaktif - Penglipuran Smart Tour')

@section('content')
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

    <div class="absolute inset-0 z-0 overflow-hidden bg-[#E5E3DF]">
        <div id="map" class="absolute inset-0 z-0"></div>

        <!-- Heatmap Overlay Container -->
        <div id="heatmap-overlay" class="heatmap-overlay"></div>

        @include('user.explore.components.map-search')
        @include('user.explore.components.map-fab')
    </div>

    @include('user.explore.components.location-sheet')

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // Execution wrapper to handle HTMX
        (function() {
            function initMap() {
                // Return early if map container doesn't exist
                if (!document.getElementById('map')) return;
                
                // If map is already initialized, don't re-initialize
                if (window.mapInstance) return;

                // Category colors mapping matching the filter panel dots
                const categoryColors = {
                    umkm: '#8B5CF6', // Violet
                    facilities: '#3B82F6', // Blue
                    toilets: '#06B6D4', // Cyan
                    accessibility: '#F59E0B', // Amber
                    cultural: '#1E5128' // Green (Default)
                };

                // Shared global user GPS location variable
                let lastPosition = null;
                let shouldCenterOnNextLocation = false;
                let map = null;
                let isGpsLoading = false;
                let activeLocation = null;

                function updateRouteButtonUI() {
                    const iconEl = document.getElementById('route-btn-icon');
                    const textEl = document.getElementById('route-btn-text');
                    if (!iconEl || !textEl) return;

                    if (isGpsLoading) {
                        iconEl.innerHTML = `
                            <svg class="animate-spin h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        `;
                        textEl.textContent = 'Mencari GPS...';
                    } else {
                        iconEl.innerHTML = `
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        `;
                        textEl.textContent = 'Arahkan';
                    }
                }

                const defaultLat = {{ $defaultLat }};
                const defaultLon = {{ $defaultLon }};

                // 1. Inisialisasi Peta
                map = L.map('map', {
                    zoomControl: false
                }).setView([defaultLat, defaultLon], 17);
                window.mapInstance = map;

                // 2. Tambahkan Tile Layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

            // 3. Data from ExploreController
            const locations = @json($locations);

            function getMarkerIcon(category) {
                const color = categoryColors[category] || '#1E5128';
                return L.divIcon({
                    className: 'custom-pin',
                    html: `<div style="background-color: ${color}; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });
            }

            const markerLayers = [];

            // 5. Render Marker
            locations.forEach(loc => {
                const marker = L.marker([loc.lat, loc.lng], {
                    icon: getMarkerIcon(loc.cat)
                }).addTo(map);

                marker.on('click', function(e) {
                    if (e && e.originalEvent) {
                        e.originalEvent.stopPropagation();
                    }
                    openSheet(loc);
                    map.flyTo([loc.lat - 0.0005, loc.lng], 18, {
                        animate: true,
                        duration: 0.5
                    });
                });

                markerLayers.push({
                    marker: marker,
                    category: loc.cat,
                    name: loc.name,
                    desc: loc.desc,
                    lat: loc.lat,
                    lng: loc.lng,
                    accessibility: loc.accessibility,
                    detail_url: loc.detail_url
                });
            });

            // ==========================================
            // HEATMAP OVERLAY DATA (from controller)
            // ==========================================
            const heatmapData = @json($heatmapData);

            const urlParams = new URLSearchParams(window.location.search);
            const targetRouteId = urlParams.get('route');
            const targetCategory = urlParams.get('category');

            const activeFilters = {
                cultural: true,
                umkm: true,
                facilities: true,
                toilets: true,
                accessibility: true
            };

            if (targetCategory === 'fasilitas') {
                activeFilters.cultural = false;
                activeFilters.umkm = false;

                // Sync UI Checkboxes in map-search
                setTimeout(() => {
                    document.querySelectorAll('.filter-toggle').forEach(toggle => {
                        const filterName = toggle.dataset.filter;
                        if (!activeFilters[filterName]) {
                            const checkbox = toggle.querySelector('.filter-checkbox');
                            const input = toggle.querySelector('input');
                            if (checkbox) checkbox.classList.remove('checked');
                            if (input) input.checked = false;
                        }
                    });
                }, 100);
            }

            let heatmapVisible = false;



            // Generate Heatmap Cells
            function renderHeatmap() {
                const overlay = document.getElementById('heatmap-overlay');
                overlay.innerHTML = '';

                const mapBounds = map.getBounds();
                const mapSize = map.getSize();

                heatmapData.forEach(point => {
                    let isFilterActive = activeFilters[point.category];

                    if (!isFilterActive) return;

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

            // Real-time GPS Tracking updates via Laravel Reverb
            const liveUserMarkers = {};
            function setupExploreEchoListener() {
                if (window.Echo) {
                    // Listen for other visitors' locations
                    window.Echo.channel('village-map')
                        .listen('VisitorLocationUpdated', (e) => {
                            // Check if this session already exists in heatmapData
                            const existingIndex = heatmapData.findIndex(p => p.session_id === e.session_id);
                            
                            const newPoint = {
                                lat: parseFloat(e.latitude),
                                lng: parseFloat(e.longitude),
                                intensity: 0.9,
                                category: 'cultural', // default category for visitors
                                name: 'Pengunjung Aktif',
                                is_live_user: true,
                                session_id: e.session_id
                            };

                            if (existingIndex !== -1) {
                                heatmapData[existingIndex] = newPoint;
                            } else {
                                heatmapData.push(newPoint);
                            }

                            if (heatmapVisible) {
                                renderHeatmap();
                            }

                            // Update or create marker on map
                            if (liveUserMarkers[e.session_id]) {
                                liveUserMarkers[e.session_id].setLatLng([e.latitude, e.longitude]);
                            } else {
                                const liveIcon = L.divIcon({
                                    className: 'custom-div-icon',
                                    html: `
                                        <div class="relative flex h-4 w-4">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-4 w-4 bg-blue-600 border-2 border-white shadow"></span>
                                        </div>
                                    `,
                                    iconSize: [16, 16],
                                    iconAnchor: [8, 8]
                                });
                                
                                const marker = L.marker([e.latitude, e.longitude], { icon: liveIcon })
                                    .bindPopup('Wisatawan (Live)')
                                    .addTo(map);
                                
                                liveUserMarkers[e.session_id] = marker;
                            }
                        });
                } else {
                    setTimeout(setupExploreEchoListener, 500);
                }
            }
            
            setupExploreEchoListener();

            // Clean up Reverb subscription and window listeners when navigating away via HTMX
            document.addEventListener('htmx:beforeSwap', function cleanupExplore() {
                if (window.Echo) {
                    window.Echo.leave('village-map');
                }
                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }
                window.removeEventListener('filter-change', onFilterChange);
                document.removeEventListener('htmx:beforeSwap', cleanupExplore);
            });

            // Update marker visibility based on active filters and search query
            function updateVisibleMarkers() {
                const searchInput = document.getElementById('search-input');
                const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

                markerLayers.forEach(item => {
                    let isFilterActive = activeFilters[item.category] !== false;

                    const matchesSearch = !query ||
                        item.name.toLowerCase().includes(query) ||
                        (item.desc && item.desc.toLowerCase().includes(query));

                    if (isFilterActive && matchesSearch) {
                        if (!map.hasLayer(item.marker)) {
                            map.addLayer(item.marker);
                        }
                    } else {
                        if (map.hasLayer(item.marker)) {
                            map.removeLayer(item.marker);
                        }
                    }
                });
            }

            // Listen for search input typing
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.addEventListener('input', updateVisibleMarkers);
            }

            // Listen for filter changes from map-search component
            function onFilterChange(e) {
                const {
                    filter,
                    active
                } = e.detail;
                activeFilters[filter] = active;

                // Update markers visibility
                updateVisibleMarkers();

                if (heatmapVisible) {
                    renderHeatmap();
                }
            }
            window.addEventListener('filter-change', onFilterChange);

            // ==========================================
            // MY LOCATION FUNCTIONALITY
            // ==========================================
            /** @type {L.Marker|null} */
            let locationMarker = null;
            /** @type {L.Marker|null} */
            let locationArrow = null;
            /** @type {L.Marker|null} */
            let locationPulse = null;
            /** @type {number|null} */
            let watchId = null;
            /** @type {number} */
            let currentHeading = 0;

            /**
             * @param {Object} e
             * @param {L.LatLng} e.latlng
             * @param {number|null} [e.heading]
             */
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

                locationPulse = L.marker(latlng, {
                    icon: pulseIcon
                }).addTo(map);

                // Determine heading
                let heading = null;
                if (e.heading !== undefined && e.heading !== null) {
                    heading = e.heading;
                    currentHeading = heading;
                } else if (lastPosition) {
                    heading = calculateHeading(lastPosition, latlng);
                    currentHeading = heading;
                }

                // Create and add unified location marker
                locationMarker = L.marker(latlng, {
                    icon: L.divIcon({
                        className: 'location-marker',
                        html: createLocationMarkerHtml(heading),
                        iconSize: [40, 40],
                        iconAnchor: [20, 20]
                    })
                }).addTo(map);

                lastPosition = {
                    lat: latlng.lat,
                    lng: latlng.lng
                };

                // Center map on user location if explicitly requested via button click
                if (shouldCenterOnNextLocation) {
                    map.flyTo(latlng, 17, {
                        animate: true,
                        duration: 0.5
                    });
                    shouldCenterOnNextLocation = false;
                }
            }

            function calculateHeading(from, to) {
                const dLng = to.lng - from.lng;
                const dLat = to.lat - from.lat;
                const angle = Math.atan2(dLng, dLat) * (180 / Math.PI);
                return (angle + 360) % 360;
            }

            function createLocationMarkerHtml(heading) {
                const hasHeading = heading !== undefined && heading !== null;

                let html = `
                        <div class="relative flex items-center justify-center" style="width: 40px; height: 40px;">
                    `;

                if (hasHeading) {
                    // Modern Translucent Flashlight Beam (gradient cone pointing in the heading direction)
                    html += `
                            <svg class="absolute pointer-events-none" style="transform: rotate(${heading}deg); transform-origin: 40px 40px; width: 80px; height: 80px; top: -20px; left: -20px; opacity: 0.5; z-index: 1;" viewBox="0 0 80 80">
                                <defs>
                                    <radialGradient id="beam-gradient" cx="50%" cy="50%" r="50%">
                                        <stop offset="0%" stop-color="#1E5128" stop-opacity="0.8"/>
                                        <stop offset="35%" stop-color="#1E5128" stop-opacity="0.4"/>
                                        <stop offset="100%" stop-color="#1E5128" stop-opacity="0"/>
                                    </radialGradient>
                                </defs>
                                <path d="M40 40 L17 0 A 40 40 0 0 1 63 0 Z" fill="url(#beam-gradient)" />
                            </svg>
                        `;
                }

                // Central Solid Pin
                html += `
                            <div class="absolute" style="background: #1E5128; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3); z-index: 2;"></div>
                        </div>
                    `;

                return html;
            }

            function onLocationError(e, silent = false) {
                console.warn('Geolocation error:', e.message);
                shouldCenterOnNextLocation = false;
                isGpsLoading = false;
                updateRouteButtonUI();
                if (!silent) {
                    Swal.fire({
                        title: 'Akses Lokasi Gagal',
                        text: 'Tidak dapat mendapatkan lokasi Anda. Pastikan GPS aktif dan izin lokasi telah diberikan.',
                        icon: 'warning',
                        confirmButtonColor: '#1E5128',
                        confirmButtonText: 'Baik, Saya Mengerti'
                    });
                }
            }

            function startLocationTracking(silent = false) {
                isGpsLoading = true;
                updateRouteButtonUI();

                if (!navigator.geolocation) {
                    isGpsLoading = false;
                    updateRouteButtonUI();
                    if (!silent) {
                        Swal.fire({
                            title: 'Fitur Tidak Didukung',
                            text: 'Perangkat atau peramban Anda tidak mendukung deteksi lokasi.',
                            icon: 'error',
                            confirmButtonColor: '#1E5128',
                            confirmButtonText: 'Baik'
                        });
                    }
                    return;
                }

                // Get initial position
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        isGpsLoading = false;
                        updateRouteButtonUI();

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
                            (err) => onLocationError(err, silent), {
                                enableHighAccuracy: false, // Diganti false sementara agar lebih cepat / tidak timeout
                                maximumAge: 10000,
                                timeout: 15000
                            }
                        );
                    },
                    (err) => {
                        isGpsLoading = false;
                        updateRouteButtonUI();
                        onLocationError(err, silent);
                    }, {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: 10000
                    }
                );
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
                if (lastPosition) {
                    map.flyTo([lastPosition.lat, lastPosition.lng], 17, {
                        animate: true,
                        duration: 0.5
                    });
                } else {
                    shouldCenterOnNextLocation = true;
                    if (isGpsLoading) {
                        Swal.fire({
                            title: 'Mencari Lokasi...',
                            text: 'Sedang mengambil koordinat GPS Anda. Mohon tunggu sebentar.',
                            icon: 'info',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else if (watchId === null) {
                        startLocationTracking(false);
                    } else {
                        Swal.fire({
                            title: 'Mencari Lokasi...',
                            text: 'Sedang mengambil koordinat GPS Anda. Mohon tunggu sebentar.',
                            icon: 'info',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                }
            });

            document.getElementById('btn-locate').addEventListener('click', function() {
                // Focus bounds only on visible markers
                const visibleCoords = markerLayers
                    .filter(item => map.hasLayer(item.marker))
                    .map(item => [item.lat, item.lng]);

                if (visibleCoords.length > 0) {
                    const bounds = L.latLngBounds(visibleCoords);
                    map.fitBounds(bounds, {
                        padding: [50, 50],
                        animate: true,
                        duration: 0.5
                    });
                } else {
                    map.flyTo([defaultLat, defaultLon], 17, {
                        animate: true,
                        duration: 0.5
                    });
                }
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

            // Close filter panel and clear active user route when clicking elsewhere on map
            map.on('click', function() {
                if (userRouteLayer) {
                    map.removeLayer(userRouteLayer);
                    userRouteLayer = null;
                }
                const panel = document.getElementById('filter-panel');
                if (panel && !panel.classList.contains('hidden')) {
                    panel.classList.add('hidden');
                }
            });

            // Start location tracking automatically on load (silent = true)
            startLocationTracking(true);

            // Auto-routing redirect support from UMKM Recommended page
            const targetLat = urlParams.get('lat');
            const targetLng = urlParams.get('lng');
            const targetName = urlParams.get('name');
            const action = urlParams.get('action');

            if (action === 'route' && targetLat && targetLng) {
                const latNum = parseFloat(targetLat);
                const lngNum = parseFloat(targetLng);

                // Find matching location in locations array (allowing a small tolerance)
                const targetLoc = locations.find(loc =>
                    Math.abs(parseFloat(loc.lat) - latNum) < 0.0001 &&
                    Math.abs(parseFloat(loc.lng) - lngNum) < 0.0001
                ) || {
                    lat: latNum,
                    lng: lngNum,
                    name: targetName || 'Tujuan',
                    cat: 'umkm',
                    desc: '',
                    is_accessible: false,
                    accessibility: '',
                    detail_url: null,
                    images: []
                };

                // Trigger opening the sheet and route calculation after a short timeout to let Leaflet load
                setTimeout(() => {
                    openSheet(targetLoc);
                    map.flyTo([targetLoc.lat - 0.0005, targetLoc.lng], 18, {
                        animate: true,
                        duration: 0.8
                    });

                    // Auto-trigger click on the route directions button
                    const routeBtn = document.getElementById('sheet-route-btn');
                    if (routeBtn) {
                        routeBtn.click();
                    }
                }, 800);
            }

            if (action === 'multi_route') {
                const stopsParam = urlParams.get('stops');
                if (stopsParam) {
                    const stops = stopsParam.split('|').map(s => {
                        const parts = s.split(',');
                        return [parseFloat(parts[0]), parseFloat(parts[1])];
                    });

                    // Trigger drawing the route after map and user GPS load
                    setTimeout(() => {
                        // Fit bounds to all stops to center map
                        const bounds = L.latLngBounds(stops);
                        map.fitBounds(bounds, {
                            padding: [50, 50]
                        });

                        // Show SweetAlert GPS loading spinner
                        Swal.fire({
                            title: 'Mendeteksi Lokasi...',
                            text: 'Mohon tunggu, sedang memuat rute navigasi belanja...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Wait for user GPS location
                        let checkCount = 0;
                        const checkInterval = setInterval(() => {
                            checkCount++;
                            if (lastPosition) {
                                clearInterval(checkInterval);
                                Swal.close();

                                // Draw the route from user's current position through all stops
                                const allCoords = [
                                    [parseFloat(lastPosition.lng), parseFloat(lastPosition.lat)]
                                ];
                                // ORS route coordinates are [lng, lat]
                                stops.forEach(stop => {
                                    allCoords.push([stop[1], stop[0]]);
                                });

                                drawMultiStopRoute(allCoords);
                            } else if (!isGpsLoading || checkCount >= 16) {
                                // Timeout fallback: just draw lines between the stops (without user position)
                                clearInterval(checkInterval);
                                Swal.close();

                                const allCoords = stops.map(stop => [stop[1], stop[0]]);
                                drawMultiStopRoute(allCoords);
                            }
                        }, 500);
                    }, 800);
                }
            }
        });

        // ==========================================
        // LOGIKA BOTTOM SHEET
        // ==========================================

        function stripHtmlAndTruncate(html, maxLength = 120) {
            if (!html) return '';
            const doc = new DOMParser().parseFromString(html, 'text/html');
            let text = doc.body.textContent || '';
            text = text.replace(/\s+/g, ' ').trim();
            if (text.length > maxLength) {
                return text.slice(0, maxLength) + '...';
            }
            return text;
        }

        /**
         * @param {Object} loc
         * @param {string} loc.name
         * @param {string} loc.cat
         * @param {string} [loc.desc]
         * @param {string} [loc.accessibility]
         * @param {number} loc.lat
         * @param {number} loc.lng
         * @param {string} [loc.detail_url]
         */
        function showGpsFallbackAlert(href) {
            Swal.fire({
                title: 'GPS Belum Aktif',
                text: 'Lokasi Anda belum terdeteksi di peta ini. Apakah Anda ingin membuka Google Maps untuk petunjuk arah luar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1E5128',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Buka Google Maps',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open(href, '_blank');
                }
            });
        }

        function openSheet(loc) {
            activeLocation = loc;
            updateRouteButtonUI();
            document.getElementById('sheet-title').textContent = loc.name;

            // AR Badge
            const arBadge = document.getElementById('sheet-ar-badge');
            if (arBadge) {
                if (loc.has_ar) {
                    arBadge.classList.remove('hidden');
                    arBadge.classList.add('inline-flex');
                } else {
                    arBadge.classList.add('hidden');
                    arBadge.classList.remove('inline-flex');
                }
            }

            // Alpine cover images are loaded dynamically via event payload below

            // Kategori mapping for display
            const categoryLabels = {
                cultural: 'Objek Budaya',
                umkm: 'UMKM',
                facilities: 'Fasilitas',
                toilets: 'Toilet',
                accessibility: 'Aksesibilitas'
            };
            const label = categoryLabels[loc.cat] || 'Lokasi';
            const color = categoryColors[loc.cat] || '#1E5128';

            // Style category badge dynamically
            const badge = document.getElementById('sheet-category-badge');
            const dot = document.getElementById('sheet-category-dot');
            const text = document.getElementById('sheet-category-text');

            if (badge && dot && text) {
                text.textContent = label;
                dot.style.backgroundColor = color;
                badge.style.borderColor = color + '30'; // subtle border opacity
                badge.style.color = color;
            }

            // Description Section
            const secDesc = document.getElementById('section-desc');
            const sheetDesc = document.getElementById('sheet-desc');
            if (secDesc && sheetDesc) {
                const cleanText = stripHtmlAndTruncate(loc.desc, 120);
                if (cleanText !== '') {
                    sheetDesc.textContent = cleanText;
                    secDesc.style.display = 'block';
                } else {
                    secDesc.style.display = 'none';
                }
            }

            // Accessibility Section
            const secAcc = document.getElementById('section-accessibility');
            const sheetAcc = document.getElementById('sheet-accessibility');
            if (secAcc && sheetAcc) {
                if (loc.is_accessible) {
                    sheetAcc.textContent = loc.accessibility && loc.accessibility.trim() !== '' ?
                        loc.accessibility :
                        'Akses ramah disabilitas tersedia.';
                    secAcc.style.display = 'flex';
                } else {
                    secAcc.style.display = 'none';
                }
            }

            // Clear existing user route when showing a new location sheet
            if (userRouteLayer) {
                map.removeLayer(userRouteLayer);
                userRouteLayer = null;
            }

            // Arahkan Button - Use live OpenRouteService routing if user location is active
            const routeBtn = document.getElementById('sheet-route-btn');
            if (routeBtn) {
                routeBtn.href = `https://www.google.com/maps/dir/?api=1&destination=${loc.lat},${loc.lng}`;
                routeBtn.onclick = function(e) {
                    e.preventDefault();
                    if (lastPosition) {
                        closeSheet();
                        drawUserToLocationRoute(lastPosition, {
                            lat: loc.lat,
                            lng: loc.lng
                        });
                    } else if (isGpsLoading) {
                        // Show premium loading SweetAlert and wait for location to load
                        Swal.fire({
                            title: 'Mendeteksi Lokasi...',
                            text: 'Mohon tunggu, sedang menghubungkan ke satelit GPS...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Poll every 500ms for up to 8 seconds to see if GPS becomes available
                        let checkCount = 0;
                        const checkInterval = setInterval(() => {
                            checkCount++;
                            if (lastPosition) {
                                clearInterval(checkInterval);
                                Swal.close();
                                closeSheet();
                                drawUserToLocationRoute(lastPosition, {
                                    lat: loc.lat,
                                    lng: loc.lng
                                });
                            } else if (!isGpsLoading || checkCount >= 16) {
                                // Timeout or error occurred
                                clearInterval(checkInterval);
                                Swal.close();
                                // Show fallback confirmation
                                showGpsFallbackAlert(this.href);
                            }
                        }, 500);
                    } else {
                        showGpsFallbackAlert(this.href);
                    }
                };
            }

            // Detail Button
            const detailBtn = document.getElementById('sheet-detail-btn');
            if (detailBtn) {
                if (loc.detail_url) {
                    detailBtn.href = loc.detail_url;
                    detailBtn.style.display = 'flex';
                } else {
                    detailBtn.style.display = 'none';
                }
            }

            window.dispatchEvent(new CustomEvent('open-location-sheet', {
                detail: {
                    images: loc.images || []
                }
            }));
        }

        function closeSheet() {
            activeLocation = null;
            window.dispatchEvent(new CustomEvent('close-location-sheet'));
        }

        let userRouteLayer = null;

        async function drawUserToLocationRoute(fromLoc, toLoc) {
            if (userRouteLayer) {
                map.removeLayer(userRouteLayer);
                userRouteLayer = null;
            }

            const coords = [
                [parseFloat(fromLoc.lng), parseFloat(fromLoc.lat)],
                [parseFloat(toLoc.lng), parseFloat(toLoc.lat)]
            ];

            try {
                const response = await fetch('/api/routing/directions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        coordinates: coords
                    })
                });

                const contentType = response.headers.get('content-type') || '';

                if (response.ok && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (data.features && data.features.length > 0) {
                        const routeFeature = data.features[0];
                        userRouteLayer = L.layerGroup();

                        // Background shadow path
                        L.geoJSON(routeFeature.geometry, {
                            style: {
                                color: '#1E5128',
                                weight: 8,
                                opacity: 0.2
                            }
                        }).addTo(userRouteLayer);

                        // Foreground dashed path
                        L.geoJSON(routeFeature.geometry, {
                            style: {
                                color: '#1E5128',
                                weight: 4.5,
                                dashArray: '6, 8',
                                opacity: 0.95
                            }
                        }).addTo(userRouteLayer);

                        userRouteLayer.addTo(map);

                        const bounds = L.geoJSON(routeFeature.geometry).getBounds();
                        map.fitBounds(bounds, {
                            padding: [60, 60],
                            animate: true,
                            duration: 0.8
                        });
                        return;
                    }
                }
            } catch (error) {
                console.error('ORS routing failed:', error);
            }

            // Fallback straight line
            const polyline = L.polyline([
                [fromLoc.lat, fromLoc.lng],
                [toLoc.lat, toLoc.lng]
            ], {
                color: '#1E5128',
                weight: 4.5,
                dashArray: '6, 8',
                opacity: 0.95
            });
            userRouteLayer = L.layerGroup([polyline]);
            userRouteLayer.addTo(map);
            map.fitBounds(polyline.getBounds(), {
                padding: [60, 60]
            });
        }

        async function drawMultiStopRoute(coords) {
            if (userRouteLayer) {
                map.removeLayer(userRouteLayer);
                userRouteLayer = null;
            }

            try {
                const response = await fetch('/api/routing/directions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        coordinates: coords
                    })
                });

                const contentType = response.headers.get('content-type') || '';

                if (response.ok && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (data.features && data.features.length > 0) {
                        const routeFeature = data.features[0];
                        userRouteLayer = L.layerGroup();

                        // Background shadow path
                        L.geoJSON(routeFeature.geometry, {
                            style: {
                                color: '#F97316',
                                weight: 8,
                                opacity: 0.2
                            }
                        }).addTo(userRouteLayer);

                        // Foreground dashed path
                        L.geoJSON(routeFeature.geometry, {
                            style: {
                                color: '#F97316',
                                weight: 4.5,
                                dashArray: '6, 8',
                                opacity: 0.95
                            }
                        }).addTo(userRouteLayer);

                        userRouteLayer.addTo(map);

                        const bounds = L.geoJSON(routeFeature.geometry).getBounds();
                        map.fitBounds(bounds, {
                            padding: [60, 60],
                            animate: true,
                            duration: 0.8
                        });
                        return;
                    }
                }
            } catch (error) {
                console.error('ORS routing failed:', error);
            }

            // Fallback straight lines
            const leafletCoords = coords.map(c => [c[1], c[0]]);
            const polyline = L.polyline(leafletCoords, {
                color: '#F97316',
                weight: 4.5,
                dashArray: '6, 8',
                opacity: 0.95
            });
            userRouteLayer = L.layerGroup([polyline]);
            userRouteLayer.addTo(map);
            map.fitBounds(polyline.getBounds(), {
                padding: [60, 60]
            });
            // Make globals accessible for the bottom sheet and route functions
            window.activeLocation = activeLocation;
            window.userRouteLayer = userRouteLayer;
        }
    }

    const handleExploreLoad = function(evt) {
        const container = evt.detail.elt;
        if (container.querySelector('#map') || container.id === 'map') {
            initMap();
        }
    };
    document.body.addEventListener('htmx:load', handleExploreLoad);

    document.addEventListener('htmx:beforeSwap', function cleanupMap(e) {
        if (window.mapInstance) {
            window.mapInstance.remove();
            window.mapInstance = null;
        }
        document.body.removeEventListener('htmx:load', handleExploreLoad);
        document.removeEventListener('htmx:beforeSwap', cleanupMap);
    });
    })();
    </script>
@endsection
