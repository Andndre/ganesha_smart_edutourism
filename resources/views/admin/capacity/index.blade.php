@extends('layouts.dashboard')

@section('title', 'Kapasitas Wisatawan')

@section('content')

    <div class="mb-8 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-charcoal text-2xl font-bold">Sistem Peringatan Kapasitas</h1>
            <p class="mt-0.5 text-sm text-gray-500">Pemantauan kepadatan wisatawan secara real-time per zona.</p>
        </div>
        <div class="flex items-center gap-2">
            <span
                class="bg-primary/10 text-primary flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold">
                <span class="relative flex h-2 w-2">
                    <span class="bg-primary absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"></span>
                    <span class="bg-primary relative inline-flex h-2 w-2 rounded-full"></span>
                </span>
                Live — Diperbarui tiap menit
            </span>
        </div>
    </div>

    @include('admin.capacity.partials.stats')

    @include('admin.capacity.partials.zones-list')

    @include('admin.capacity.partials.map')

    @include('admin.capacity.partials.modals')

@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <style>
        .leaflet-control-attribution {
            display: none !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    <script>
        // Global variables for Map and Drawn Items
        let map;
        let drawnItems;
        
        let modalMap = null;
        let modalDrawnItems;
        let modalCurrentLayer = null;
        let modalDrawControl;

        function initModalMap() {
            if (!modalMap) {
                modalMap = L.map('modal-map', {
                    zoomControl: true,
                    attributionControl: false,
                    gestureHandling: true
                }).setView([defaultLat, defaultLon], 16);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                }).addTo(modalMap);

                modalDrawnItems = new L.FeatureGroup();
                modalMap.addLayer(modalDrawnItems);

                modalDrawControl = new L.Control.Draw({
                    draw: {
                        polyline: false,
                        circle: false,
                        marker: false,
                        circlemarker: false,
                        rectangle: false,
                        polygon: {
                            allowIntersection: false,
                            showArea: true,
                        }
                    },
                    edit: false
                });
                modalMap.addControl(modalDrawControl);

                modalMap.on(L.Draw.Event.CREATED, function (e) {
                    const layer = e.layer;
                    
                    if (modalCurrentLayer) {
                        modalDrawnItems.removeLayer(modalCurrentLayer);
                    }
                    modalCurrentLayer = layer;
                    modalDrawnItems.addLayer(layer);

                    const latlngs = layer.getLatLngs()[0].map(latlng => ({
                        lat: latlng.lat,
                        lng: latlng.lng
                    }));
                    
                    document.getElementById('modal-polygon').value = JSON.stringify(latlngs);
                    document.getElementById('btn-clear-polygon').classList.remove('hidden');
                    document.querySelector('#modal-map .leaflet-draw-toolbar').style.display = 'none';
                });
            }
        }

        function resetModalMap(polygonCoordinates = null) {
            initModalMap();
            
            if (modalCurrentLayer) {
                modalDrawnItems.removeLayer(modalCurrentLayer);
                modalCurrentLayer = null;
            }
            document.getElementById('modal-polygon').value = '';
            document.getElementById('btn-clear-polygon').classList.add('hidden');
            document.querySelector('#modal-map .leaflet-draw-toolbar').style.display = 'block';

            if (polygonCoordinates && Array.isArray(polygonCoordinates) && polygonCoordinates.length > 0) {
                const latlngs = polygonCoordinates.map(p => [p.lat, p.lng]);
                modalCurrentLayer = L.polygon(latlngs, {
                    color: '#3b82f6',
                    fillOpacity: 0.2,
                    weight: 2
                }).addTo(modalDrawnItems);
                
                document.getElementById('modal-polygon').value = JSON.stringify(polygonCoordinates);
                document.getElementById('btn-clear-polygon').classList.remove('hidden');
                document.querySelector('#modal-map .leaflet-draw-toolbar').style.display = 'none';
                
                modalMap.fitBounds(modalCurrentLayer.getBounds(), { padding: [20, 20] });
            } else {
                modalMap.setView([defaultLat, defaultLon], 16);
            }

            setTimeout(() => {
                modalMap.invalidateSize();
            }, 300);
        }

        function openThresholdModal(data) {
            document.getElementById('modal-title').innerHTML = 'Edit Zona <span id="modal-zone-name" class="text-gray-400"></span>';
            document.getElementById('modal-zone-name').innerText = data.name;
            document.getElementById('modal-name').value = data.name;
            document.getElementById('modal-max-capacity').value = data.max_capacity;
            document.getElementById('modal-warning-threshold').value = data.warning_threshold;
            document.getElementById('modal-critical-threshold').value = data.critical_threshold;
            
            document.getElementById('identifier-group').style.display = 'none';
            document.getElementById('modal-identifier').removeAttribute('required');

            const form = document.getElementById('modal-threshold-form');
            form.action = `/admin/capacity/${data.id}/thresholds`;
            document.getElementById('form-method').value = 'PUT';

            window.dispatchEvent(new CustomEvent('open-threshold-modal'));
            
            // Re-init map and draw existing polygon if any
            resetModalMap(data.polygon_coordinates);
        }

        function openCreateZoneModal() {
            document.getElementById('modal-title').innerText = 'Buat Zona Baru';
            document.getElementById('modal-name').value = '';
            document.getElementById('modal-max-capacity').value = '';
            document.getElementById('modal-warning-threshold').value = '70';
            document.getElementById('modal-critical-threshold').value = '90';
            
            document.getElementById('identifier-group').style.display = 'none';
            document.getElementById('modal-identifier').value = '';
            document.getElementById('modal-identifier').removeAttribute('required');

            const form = document.getElementById('modal-threshold-form');
            form.action = `/admin/capacity`;
            document.getElementById('form-method').value = 'POST';

            window.dispatchEvent(new CustomEvent('open-threshold-modal'));
            
            // Empty map
            resetModalMap(null);
        }

        function closeThresholdModal() {
            window.dispatchEvent(new CustomEvent('close-threshold-modal'));
        }

        document.getElementById('btn-clear-polygon').addEventListener('click', function() {
            if (modalCurrentLayer) {
                modalDrawnItems.removeLayer(modalCurrentLayer);
                modalCurrentLayer = null;
            }
            document.getElementById('modal-polygon').value = '';
            this.classList.add('hidden');
            document.querySelector('#modal-map .leaflet-draw-toolbar').style.display = 'block';
        });

        (function() {
            // Chart init
            const canvas = document.getElementById('capacityChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const dpr = window.devicePixelRatio || 1;
            const W = canvas.offsetWidth;
            const H = 160;
            canvas.width = W * dpr;
            canvas.height = H * dpr;
            canvas.style.height = H + 'px';
            ctx.scale(dpr, dpr);

            const data = {!! json_encode($hourlyData) !!};
            const max = Math.max(...data, 100) * 1.15;
            const padL = 36,
                padR = 12,
                padT = 12,
                padB = 28;
            const chartW = W - padL - padR;
            const chartH = H - padT - padB;

            // Area fill
            ctx.beginPath();
            data.forEach((v, i) => {
                const x = padL + (i / (data.length - 1)) * chartW;
                const y = padT + chartH - (v / max) * chartH;
                i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            });
            ctx.lineTo(padL + chartW, padT + chartH);
            ctx.lineTo(padL, padT + chartH);
            ctx.closePath();
            const grad = ctx.createLinearGradient(0, padT, 0, padT + chartH);
            grad.addColorStop(0, '#1e512825');
            grad.addColorStop(1, '#1e512800');
            ctx.fillStyle = grad;
            ctx.fill();

            // Line
            ctx.beginPath();
            data.forEach((v, i) => {
                const x = padL + (i / (data.length - 1)) * chartW;
                const y = padT + chartH - (v / max) * chartH;
                i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            });
            ctx.strokeStyle = '#1e5128';
            ctx.lineWidth = 2;
            ctx.stroke();

            // Hour labels (every 4h)
            const labels = {!! json_encode($hourlyLabels) !!};
            ctx.fillStyle = '#9ca3af';
            ctx.font = '10px Plus Jakarta Sans, sans-serif';
            ctx.textAlign = 'center';
            labels.forEach((label, i) => {
                if (i % 4 === 0 || i === labels.length - 1) {
                    const x = padL + (i / (labels.length - 1)) * chartW;
                    ctx.fillText(label, x, H - 6);
                }
            });
        })();

        // Initialize Leaflet Map
        const defaultLat = {{ $defaultLat }};
        const defaultLon = {{ $defaultLon }};
        const zonesData = {!! json_encode($zones) !!};

        map = L.map('map', {
            zoomControl: true,
            gestureHandling: true
        }).setView([defaultLat, defaultLon], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        // Add drawn items layer
        drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);



        // Render existing zones on map
        // Sort so 'desa_penglipuran' is added first (bottom layer) so inner zones are clickable
        zonesData.sort((a, b) => {
            if (a.zone_identifier === 'desa_penglipuran') return -1;
            if (b.zone_identifier === 'desa_penglipuran') return 1;
            return 0;
        });

        const contextMenu = document.getElementById('map-context-menu');
        const btnContextEdit = document.getElementById('btn-context-edit');
        let selectedZoneForEdit = null;

        // Hide context menu when clicking anywhere else
        document.addEventListener('click', function(e) {
            if (!contextMenu.contains(e.target)) {
                contextMenu.classList.add('hidden');
            }
        });

        // Hide context menu on map drag/zoom
        map.on('movestart zoomstart', function() {
            contextMenu.classList.add('hidden');
        });

        btnContextEdit.addEventListener('click', function() {
            if (selectedZoneForEdit) {
                openThresholdModal(selectedZoneForEdit);
                contextMenu.classList.add('hidden');
            }
        });

        zonesData.forEach(zone => {
            if (zone.polygon_coordinates && Array.isArray(zone.polygon_coordinates)) {
                const latlngs = zone.polygon_coordinates.map(p => [p.lat, p.lng]);
                
                let color = '#3b82f6';
                let pct = zone.max_capacity > 0 ? (zone.current_count / zone.max_capacity) * 100 : 0;
                if (pct >= zone.critical_threshold) color = '#ef4444';
                else if (pct >= zone.warning_threshold) color = '#f59e0b';

                const polygon = L.polygon(latlngs, {
                    color: color,
                    fillOpacity: 0.2,
                    weight: 2
                }).addTo(drawnItems);

                polygon.bindPopup(`<b>${zone.name}</b><br>${zone.current_count} / ${zone.max_capacity} orang`);
                
                polygon.on('contextmenu', function(e) {
                    L.DomEvent.stopPropagation(e);
                    
                    // Set title
                    document.getElementById('context-menu-title').innerText = zone.name;

                    // Show custom context menu at mouse position
                    const containerPoint = map.mouseEventToContainerPoint(e.originalEvent);
                    contextMenu.style.left = containerPoint.x + 'px';
                    contextMenu.style.top = containerPoint.y + 'px';
                    contextMenu.classList.remove('hidden');
                    
                    selectedZoneForEdit = zone;
                });
            }
        });

        let heatmapData = {!! json_encode($heatmapData) !!};
        let realHeatmapLayer = null;
        let realHeatmapVisible = false;

        document.getElementById('btn-admin-heatmap').addEventListener('click', function(e) {
            e.stopPropagation();
            toggleAdminHeatmap();
        });
        const liveUserMarkers = {};

        heatmapData.forEach(point => {
            if (point.is_live_user) {
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

                const marker = L.marker([point.lat, point.lng], {
                        icon: liveIcon
                    })
                    .bindPopup('Wisatawan (Live)')
                    .addTo(map);

                liveUserMarkers[point.session_id] = marker;
            }
        });

        function renderRealHeatmap() {
            if (realHeatmapLayer) {
                map.removeLayer(realHeatmapLayer);
                realHeatmapLayer = null;
            }
            
            if (!realHeatmapVisible) return;

            const points = [];
            heatmapData.forEach(point => {
                points.push([point.lat, point.lng, point.intensity || 0.5]);
            });

            realHeatmapLayer = L.heatLayer(points, {
                radius: 25,
                blur: 15,
                maxZoom: 18,
                max: 3.0,
                gradient: {0.4: 'blue', 0.6: 'cyan', 0.7: 'lime', 0.8: 'yellow', 1: 'red'}
            }).addTo(map);
        }

        function toggleAdminHeatmap() {
            realHeatmapVisible = !realHeatmapVisible;
            const btn = document.getElementById('btn-admin-heatmap');
            
            if (realHeatmapVisible) {
                btn.style.backgroundColor = '#1E5128';
                btn.style.color = 'white';
                renderRealHeatmap();
            } else {
                btn.style.backgroundColor = 'white';
                btn.style.color = '#4b5563';
                if (realHeatmapLayer) {
                    map.removeLayer(realHeatmapLayer);
                    realHeatmapLayer = null;
                }
            }
        }

        function setupEchoListener() {
            if (window.Echo) {
                window.Echo.channel('village-map')
                    .listen('.VisitorLocationUpdated', (e) => {
                        const existingIndex = heatmapData.findIndex(p => p.session_id === e.session_id);

                        const newPoint = {
                            lat: parseFloat(e.latitude),
                            lng: parseFloat(e.longitude),
                            intensity: 0.9,
                            category: 'cultural',
                            name: 'Pengunjung Aktif',
                            is_live_user: true,
                            session_id: e.session_id
                        };

                        if (existingIndex !== -1) {
                            heatmapData[existingIndex] = newPoint;
                        } else {
                            heatmapData.push(newPoint);
                        }

                        if (realHeatmapVisible) renderRealHeatmap();

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

                            const marker = L.marker([e.latitude, e.longitude], {
                                    icon: liveIcon
                                })
                                .bindPopup('Wisatawan (Live)')
                                .addTo(map);

                            liveUserMarkers[e.session_id] = marker;
                        }
                    })
                    .listen('.VisitorLocationRemoved', (e) => {
                        const existingIndex = heatmapData.findIndex(p => p.session_id === e.session_id);
                        if (existingIndex !== -1) {
                            heatmapData.splice(existingIndex, 1);
                        }
                        
                        if (liveUserMarkers[e.session_id]) {
                            map.removeLayer(liveUserMarkers[e.session_id]);
                            delete liveUserMarkers[e.session_id];
                        }
                        
                        if (realHeatmapVisible) renderRealHeatmap();
                    });
            } else {
                setTimeout(setupEchoListener, 500);
            }
        }

        setupEchoListener();
    </script>
@endpush
