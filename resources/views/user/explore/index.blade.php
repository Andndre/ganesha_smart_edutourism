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
    <div class="fixed inset-x-0 bottom-0 top-0 z-0 overflow-hidden bg-[#E5E3DF]">
        <div id="map" class="absolute inset-0 z-0"></div>

        <!-- Heatmap Overlay Container -->
        <div id="heatmap-overlay" class="heatmap-overlay"></div>

        @include('user.explore.components.map-search')
        @include('user.explore.components.map-fab')
        @include('user.explore.components.location-sheet')
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // Category colors mapping matching the filter panel dots
        const categoryColors = {
            umkm: '#8B5CF6',         // Violet
            facilities: '#3B82F6',   // Blue
            toilets: '#06B6D4',      // Cyan
            accessibility: '#F59E0B',// Amber
            cultural: '#1E5128'      // Green (Default)
        };

        document.addEventListener('DOMContentLoaded', function () {
            const defaultLat = {{ $defaultLat }};
            const defaultLon = {{ $defaultLon }};

            // 1. Inisialisasi Peta
            const map = L.map('map', {
                zoomControl: false
            }).setView([defaultLat, defaultLon], 17);

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

                marker.on('click', function () {
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
                accessibility: true,
                edu_route: true,
                sos_route: true
            };

            if (targetCategory === 'fasilitas') {
                activeFilters.cultural = false;
                activeFilters.umkm = false;
                activeFilters.edu_route = false;
                activeFilters.sos_route = false;

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

            // ==========================================
            // DYNAMIC ROUTES (from controller)
            // ==========================================
            const routesData = @json($formattedRoutes);
            const routeLayers = {
                edu_route: [],
                sos_route: []
            };

            // Parse URL parameters to check if a specific route is requested

            routesData.forEach(async (route) => {
                if (route.coordinates && route.coordinates.length > 0) {
                    let routeLayer = null;
                    let bounds = null;

                    const isSmartRoute = route.is_smart_route;
                    const cat = isSmartRoute ? 'edu_route' : 'sos_route';
                    const routeColor = isSmartRoute ? '#1E5128' : '#E65100';
                    const dashPattern = isSmartRoute ? '8, 8' : null;

                    if (route.coordinates.length >= 2) {
                        try {
                            const coords = route.coordinates.map(c => [c[1], c[0]]); // Swap [lat, lng] to [lng, lat]
                            const response = await fetch('/api/routing/directions', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ coordinates: coords })
                            });

                            if (response.ok) {
                                const data = await response.json();
                                if (data.features && data.features.length > 0) {
                                    const routeFeature = data.features[0];

                                    routeLayer = L.layerGroup();

                                    // Background shadow path
                                    L.geoJSON(routeFeature.geometry, {
                                        style: {
                                            color: routeColor,
                                            weight: 6,
                                            opacity: 0.25
                                        }
                                    }).addTo(routeLayer);

                                    // Foreground path
                                    L.geoJSON(routeFeature.geometry, {
                                        style: {
                                            color: routeColor,
                                            weight: 4,
                                            dashArray: dashPattern,
                                            opacity: isSmartRoute ? 0.8 : 0.9
                                        }
                                    }).addTo(routeLayer);

                                    bounds = L.geoJSON(routeFeature.geometry).getBounds();
                                }
                            }
                        } catch (error) {
                            console.error('Failed to fetch ORS directions for route ' + route.id, error);
                        }
                    }

                    // Fallback to straight polyline if ORS failed or returned no feature
                    if (!routeLayer) {
                        const polyline = L.polyline(route.coordinates, {
                            color: routeColor,
                            weight: 4,
                            dashArray: dashPattern,
                            opacity: isSmartRoute ? 0.8 : 0.9
                        });

                        routeLayer = L.layerGroup([polyline]);
                        bounds = polyline.getBounds();
                    }

                    routeLayers[cat].push(routeLayer);

                    if (activeFilters[cat]) {
                        routeLayer.addTo(map);
                    }

                    // If this route is the target route from query parameter, focus map on it
                    if (targetRouteId && String(route.id) === String(targetRouteId)) {
                        // Ensure the filter category is active
                        if (!activeFilters[cat]) {
                            activeFilters[cat] = true;
                            const filterToggle = document.querySelector(`.filter-toggle[data-filter="${cat}"]`);
                            if (filterToggle) {
                                const checkbox = filterToggle.querySelector('.filter-checkbox');
                                if (checkbox) checkbox.classList.add('checked');
                                const input = filterToggle.querySelector('input');
                                if (input) input.checked = true;
                            }
                        }

                        if (bounds) {
                            setTimeout(() => {
                                map.fitBounds(bounds, {
                                    padding: [50, 50],
                                    animate: true,
                                    duration: 0.5
                                });
                            }, 300);
                        }
                    }
                }
            });

            // Generate Heatmap Cells
            function renderHeatmap() {
                const overlay = document.getElementById('heatmap-overlay');
                overlay.innerHTML = '';

                const mapBounds = map.getBounds();
                const mapSize = map.getSize();

                heatmapData.forEach(point => {
                    let isFilterActive = activeFilters[point.category];
                    if (point.category === 'accessibility') {
                        if (point.name && point.name.toLowerCase().includes('toilet')) {
                            isFilterActive = activeFilters['accessibility'] || activeFilters['toilets'];
                        }
                    }

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

            // Update marker visibility based on active filters and search query
            function updateVisibleMarkers() {
                const searchInput = document.getElementById('search-input');
                const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

                markerLayers.forEach(item => {
                    let isFilterActive = activeFilters[item.category] !== false;
                    if (item.category === 'accessibility') {
                        if (item.name && item.name.toLowerCase().includes('toilet')) {
                            isFilterActive = activeFilters['accessibility'] || activeFilters['toilets'];
                        }
                    }

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
            window.addEventListener('filter-change', function (e) {
                const {
                    filter,
                    active
                } = e.detail;
                activeFilters[filter] = active;

                // Toggle visibility of route layers
                if (filter === 'edu_route' || filter === 'sos_route') {
                    routeLayers[filter].forEach(layer => {
                        if (active) map.addLayer(layer);
                        else map.removeLayer(layer);
                    });
                } else {
                    // Update markers visibility
                    updateVisibleMarkers();
                }

                if (heatmapVisible) {
                    renderHeatmap();
                }
            });

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
            /** @type {{ lat: number, lng: number }|null} */
            let lastPosition = null;

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
            document.getElementById('btn-layer-map').addEventListener('click', function (e) {
                e.stopPropagation();
                toggleHeatmap();
            });

            document.getElementById('btn-my-location').addEventListener('click', function (e) {
                e.stopPropagation();
                toggleMyLocation();
            });

            document.getElementById('btn-locate').addEventListener('click', function () {
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
            map.on('moveend', function () {
                if (heatmapVisible) {
                    renderHeatmap();
                }
            });

            map.on('zoomend', function () {
                if (heatmapVisible) {
                    renderHeatmap();
                }
            });

            // Close filter panel when clicking elsewhere on map
            map.on('click', function () {
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
        function openSheet(loc) {
            document.getElementById('sheet-title').textContent = loc.name;

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
                if (loc.desc && loc.desc.trim() !== '') {
                    sheetDesc.textContent = loc.desc;
                    secDesc.style.display = 'block';
                } else {
                    secDesc.style.display = 'none';
                }
            }

            // Accessibility Section
            const secAcc = document.getElementById('section-accessibility');
            const sheetAcc = document.getElementById('sheet-accessibility');
            if (secAcc && sheetAcc) {
                if (loc.accessibility && loc.accessibility.trim() !== '') {
                    sheetAcc.textContent = loc.accessibility;
                    secAcc.style.display = 'flex';
                } else {
                    secAcc.style.display = 'none';
                }
            }

            // Arahkan (Google Maps) Button
            const routeBtn = document.getElementById('sheet-route-btn');
            if (routeBtn) {
                routeBtn.href = `https://www.google.com/maps/dir/?api=1&destination=${loc.lat},${loc.lng}`;
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

            sheet.classList.remove('translate-y-full');
            sheet.classList.add('translate-y-0');
        }

        function closeSheet() {
            sheet.classList.remove('translate-y-0');
            sheet.classList.add('translate-y-full');
        }
    </script>
@endpush