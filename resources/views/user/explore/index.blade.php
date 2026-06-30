@extends('layouts.app')

@section('title', __('Peta Interaktif - Penglipuran Smart Tour'))

@section('content')
    <div class="absolute inset-0 z-0 overflow-hidden bg-[#E5E3DF]">
        <div id="map" class="absolute inset-0 z-0"></div>

        <!-- Heatmap Overlay Container -->

        @include('user.explore.components.map-search')
        @include('user.explore.components.map-fab')
    </div>

    <x-map-style-modal />
    @include('user.explore.components.location-sheet')


    @include('components.map-style-script')

    <script>
        // Execution wrapper to handle Livewire and page navigation
        (function() {
            // Category colors mapping matching the filter panel dots
            const categoryColors = {
                umkm: '#8B5CF6', // Violet
                facilities: '#3B82F6', // Blue
                toilets: '#06B6D4', // Cyan
                accessibility: '#F59E0B', // Amber
                cultural: '#1E5128' // Green (Default)
            };

            // Shared variables across wrapper
            let lastPosition = null;
            let shouldCenterOnNextLocation = false;
            let map = null;
            let isGpsLoading = false;
            let activeLocation = null;
            let userRouteLayer = null;
            let watchId = null;
            let currentHeading = 0;
            let locationMarker = null;
            let locationArrow = null;
            let locationPulse = null;
            const markerLayers = [];

            const activeFilters = {
                cultural: true,
                umkm: true,
                facilities: true,
                toilets: true,
                accessibility: true
            };

            let heatmapVisible = false;
            let realHeatmapVisible = false;
            let realHeatmapLayer = null;
            const liveUserMarkers = {};
            let heatmapData = [];

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

            function initMap() {
                // Return early if map container doesn't exist
                if (!document.getElementById('map')) return;

                // If map is already initialized, don't re-initialize
                if (window.mapInstance) return;

                const defaultLat = {{ $defaultLat }};
                const defaultLon = {{ $defaultLon }};

                // 1. Inisialisasi Peta
                map = L.map('map', {
                    zoomControl: false
                }).setView([defaultLat, defaultLon], 17);
                window.mapInstance = map;

                setTimeout(() => map.invalidateSize(), 0);
                setTimeout(() => map.invalidateSize(), 200);

                // 2. Tambahkan Tile Layer
                let tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                initMapStyleSwitcher(map);

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
                heatmapData = @json($heatmapData);

                const urlParams = new URLSearchParams(window.location.search);
                const targetRouteId = urlParams.get('route');
                const targetCategory = urlParams.get('category');

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

                // Real-time GPS Tracking updates via Laravel Reverb
                setupExploreEchoListener();

                // Listen for search input typing
                const searchInput = document.getElementById('search-input');
                if (searchInput) {
                    searchInput.addEventListener('input', updateVisibleMarkers);
                }

                // Listen for filter changes from map-search component
                window.addEventListener('filter-change', onFilterChange);

                // ==========================================
                // BUTTON EVENT LISTENERS
                // ==========================================
                const mockGpsBtn = document.getElementById('btn-mock-gps');
                if (mockGpsBtn) {
                    mockGpsBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        window._mockGpsActive = !window._mockGpsActive;
                        if (window._mockGpsActive) {
                            mockGpsBtn.classList.add('bg-primary', 'text-white');
                            mockGpsBtn.classList.remove('bg-white', 'text-gray-600');
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'info',
                                title: 'Mode Simulator GPS Aktif! Klik sembarang di peta untuk memindah lokasi Anda.',
                                showConfirmButton: false, timer: 3000
                            });
                        } else {
                            mockGpsBtn.classList.remove('bg-primary', 'text-white');
                            mockGpsBtn.classList.add('bg-white', 'text-gray-600');
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'info',
                                title: 'Mode Simulator dinonaktifkan.',
                                showConfirmButton: false, timer: 2000
                            });
                        }
                    });

                    // Add map click listener for Mock GPS
                    map.on('click', function(e) {
                        if (window._mockGpsActive) {
                            if (typeof window.setMockLocation === 'function') {
                                window.setMockLocation(e.latlng.lat, e.latlng.lng);
                            }
                            
                            // Immediately move local user marker for feedback
                            if (typeof locationMarker !== 'undefined' && locationMarker) {
                                locationMarker.setLatLng(e.latlng);
                                if (!map.hasLayer(locationMarker)) locationMarker.addTo(map);
                            }
                            if (typeof locationPulse !== 'undefined' && locationPulse) {
                                locationPulse.setLatLng(e.latlng);
                                if (!map.hasLayer(locationPulse)) locationPulse.addTo(map);
                            }
                            
                            // Visual feedback toast
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'success',
                                title: `Ping dikirim dari: ${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`,
                                showConfirmButton: false, timer: 1500
                            });
                        }
                    });
                }

                document.getElementById('btn-layer-map').addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleLiveUsers();
                });

                const btnRealHeatmap = document.getElementById('btn-real-heatmap');
                if (btnRealHeatmap) {
                    btnRealHeatmap.addEventListener('click', function(e) {
                        e.stopPropagation();
                        toggleRealHeatmap();
                    });
                }

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

                // (Moved heatmap logic to Leaflet.heat native handling)

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
            }

            // Render Real Heatmap
            function renderRealHeatmap() {
                if (realHeatmapLayer) {
                    map.removeLayer(realHeatmapLayer);
                    realHeatmapLayer = null;
                }
                
                if (!realHeatmapVisible) return;

                const points = [];
                heatmapData.forEach(point => {
                    let isFilterActive = point.category ? activeFilters[point.category] : true;
                    if (point.is_live_user) isFilterActive = true;
                    
                    if (isFilterActive) {
                        points.push([point.lat, point.lng, point.intensity || 0.5]);
                    }
                });

                realHeatmapLayer = L.heatLayer(points, {
                    radius: 25,
                    blur: 15,
                    maxZoom: 18,
                    max: 3.0, // Butuh sekitar 3 orang bertumpuk agar titik jadi merah
                    gradient: {0.4: 'blue', 0.6: 'cyan', 0.7: 'lime', 0.8: 'yellow', 1: 'red'}
                }).addTo(map);
            }

            // Toggle Real Heatmap
            function toggleRealHeatmap() {
                realHeatmapVisible = !realHeatmapVisible;
                const btn = document.getElementById('btn-real-heatmap');
                
                if (realHeatmapVisible) {
                    btn.classList.add('fab-btn-active');
                    renderRealHeatmap();
                } else {
                    btn.classList.remove('fab-btn-active');
                    if (realHeatmapLayer) {
                        map.removeLayer(realHeatmapLayer);
                        realHeatmapLayer = null;
                    }
                }
            }

            // Render initial live user markers from server data
            function renderInitialLiveMarkers() {
                // Remove any existing live markers from map
                Object.values(liveUserMarkers).forEach(marker => map.removeLayer(marker));
                // Clear the object
                Object.keys(liveUserMarkers).forEach(key => delete liveUserMarkers[key]);

                heatmapData.forEach(point => {
                    if (point.is_live_user) {
                        const displayName = point.user_name || 'Wisatawan';
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

                        const marker = L.marker([point.lat, point.lng], { icon: liveIcon })
                            .bindPopup(displayName)
                            .addTo(map);

                        liveUserMarkers[point.session_id] = marker;
                    }
                });
            }

            // Toggle Live Users
            function toggleLiveUsers() {
                heatmapVisible = !heatmapVisible;
                const btn = document.getElementById('btn-layer-map');

                if (heatmapVisible) {
                    btn.classList.add('fab-btn-active');
                    renderInitialLiveMarkers();
                } else {
                    btn.classList.remove('fab-btn-active');
                    // Remove all live user markers from map
                    Object.values(liveUserMarkers).forEach(marker => map.removeLayer(marker));
                }
            }

            function setupExploreEchoListener() {
                if (window.Echo) {
                    // Listen for other visitors' locations
                    window.Echo.channel('village-map')
                        .listen('.VisitorLocationUpdated', (e) => {
                            console.log('📡 [Reverb] VisitorLocationUpdated received:', e);
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

                            if (realHeatmapVisible) {
                                renderRealHeatmap();
                            }

                            // Update or create marker (only add to map if heatmap is visible)
                            if (liveUserMarkers[e.session_id]) {
                                liveUserMarkers[e.session_id].setLatLng([e.latitude, e.longitude]);
                                liveUserMarkers[e.session_id].setPopupContent(e.user_name || 'Wisatawan');
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

                                const marker = L.marker([e.latitude, e.longitude], {
                                        icon: liveIcon
                                    })
                                    .bindPopup(e.user_name || 'Wisatawan');

                                if (heatmapVisible) {
                                    marker.addTo(map);
                                }

                                liveUserMarkers[e.session_id] = marker;
                            }
                        })
                        .listen('.VisitorLocationRemoved', (e) => {
                            // Remove from heatmapData array
                            const idx = heatmapData.findIndex(p => p.session_id === e.session_id);
                            if (idx !== -1) {
                                heatmapData.splice(idx, 1);
                            }

                            if (realHeatmapVisible) {
                                renderRealHeatmap();
                            }

                            // Remove marker
                            if (liveUserMarkers[e.session_id]) {
                                map.removeLayer(liveUserMarkers[e.session_id]);
                                delete liveUserMarkers[e.session_id];
                            }
                        });
                } else {
                    // Retry if Echo is not initialized yet (since app.js is loaded asynchronously via Vite)
                    setTimeout(setupExploreEchoListener, 500);
                }
            }

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

            function onFilterChange(e) {
                const {
                    filter,
                    active
                } = e.detail;
                activeFilters[filter] = active;

                // Update markers visibility
                updateVisibleMarkers();

                if (realHeatmapVisible) {
                    renderRealHeatmap();
                }
            }

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
                                enableHighAccuracy: false,
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
            }

            // Expose required sheet functions to window for inline HTML onclick attributes
            window.openSheet = openSheet;
            window.closeSheet = closeSheet;

            // Execute when Leaflet is ready (handles async loading via Livewire)
            const checkAndInitMap = () => {
                if (typeof L !== 'undefined') {
                    initMap();
                } else {
                    setTimeout(checkAndInitMap, 50);
                }
            };
            checkAndInitMap();

            document.addEventListener('livewire:navigating', function cleanupMap(e) {
                if (window.mapInstance) {
                    window.mapInstance.remove();
                    window.mapInstance = null;
                }
                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                }
                window.removeEventListener('filter-change', onFilterChange);
                delete window.openSheet;
                delete window.closeSheet;
                document.removeEventListener('livewire:navigating', cleanupMap);
            });
        })();
    </script>
@endsection
