@extends('layouts.admin')

@section('title', 'Tambah Rute Wisata')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .leaflet-control-attribution { display: none !important; }
        .custom-pin-selected {
            transition: all 0.3s ease;
        }
        @media (min-width: 1024px) {
            #admin-main {
                height: 100vh;
                overflow: hidden !important;
                display: flex;
                flex-direction: column;
                padding-bottom: 2rem !important;
            }
        }
    </style>
@endpush

@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.tour-routes') }}" class="rounded-xl p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-charcoal">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Tambah Rute Wisata Baru</h1>
        <p class="mt-0.5 text-sm text-gray-500">Pilih titik kunjungan pada peta secara berurutan untuk membuat rute perjalanan.</p>
    </div>
</div>

<form action="{{ route('admin.tour-routes.store') }}" method="POST" id="route-form" class="lg:flex-1 lg:flex lg:flex-col lg:min-h-0">
    @csrf
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:flex-1 lg:min-h-0">
        
        {{-- Left Form Panel (Metadata & Selected Points) --}}
        <div class="lg:col-span-5 space-y-6 lg:h-full lg:overflow-y-auto lg:pr-4">
            
            {{-- General Route Info --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 font-semibold text-charcoal flex items-center gap-2">
                    <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Informasi Rute
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Rute <span class="text-warning">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Rute Budaya & Sejarah Penglipuran"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kategori / Tema <span class="text-warning">*</span></label>
                            <select name="difficulty" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                                <option>Mudah</option>
                                <option>Sedang</option>
                                <option>Sulit</option>
                                <option>Edukasi</option>
                                <option>Alam</option>
                                <option>Belanja</option>
                                <option>Difabel</option>
                            </select>
                        </div>
                        <div class="flex flex-col justify-end pb-2">
                            <label class="relative flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="is_smart_route" value="1" class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4">
                                <span class="text-sm font-semibold text-gray-700">Smart Route (AI)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Rute</label>
                        <textarea name="description" rows="3" placeholder="Tulis deskripsi singkat mengenai rute perjalanan ini..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- Estimated Metrics --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 font-semibold text-charcoal flex items-center gap-2">
                    <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Kalkulasi Jarak & Durasi (OSRM)
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-gray-50 p-4 border border-gray-100">
                        <span class="text-xs text-gray-400 font-semibold block uppercase">Total Jarak</span>
                        <span id="route-distance-display" class="text-xl font-bold text-charcoal block mt-1">0 m</span>
                        <input type="hidden" name="distance_meters" id="field-distance" value="0">
                    </div>
                    <div class="rounded-xl bg-gray-50 p-4 border border-gray-100">
                        <span class="text-xs text-gray-400 font-semibold block uppercase">Total Durasi</span>
                        <span id="route-duration-display" class="text-xl font-bold text-charcoal block mt-1">0 menit</span>
                        <input type="hidden" name="estimated_duration_minutes" id="field-duration" value="0">
                    </div>
                </div>
                <p class="mt-2.5 text-[11px] text-gray-400 italic leading-relaxed">
                    * Durasi dihitung otomatis berdasarkan akumulasi waktu perjalanan kaki (OSRM) dan estimasi durasi kunjungan di setiap titik.
                </p>
            </div>

            {{-- Selected Points List --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-semibold text-charcoal flex items-center gap-2">
                        <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        Urutan Kunjungan Rute
                    </h2>
                    <span id="points-count-badge" class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-500">0 Titik</span>
                </div>

                {{-- Empty state --}}
                <div id="points-empty-state" class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-xs text-gray-400">
                    Belum ada titik yang dipilih. Silakan klik penanda lokasi di peta di sebelah kanan untuk menambahkan titik kunjungan.
                </div>

                {{-- Reorderable List Container --}}
                <div id="points-list-container" class="space-y-3">
                    {{-- Rendered dynamically by javascript --}}
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button type="submit"
                    class="flex-1 rounded-xl bg-primary py-3 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                    Simpan Rute Wisata
                </button>
                <a href="{{ route('admin.tour-routes') }}"
                    class="rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-semibold text-gray-500 transition-all hover:bg-gray-50">
                    Batal
                </a>
            </div>

        </div>

        {{-- Right Map Panel (Sticky Map) --}}
        <div class="lg:col-span-7 lg:h-full">
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm h-full flex flex-col">
                <div class="mb-3 flex items-center justify-between shrink-0">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Peta Lokasi Desa Penglipuran</span>
                    <button type="button" onclick="clearAllPoints()" class="text-xs font-semibold text-warning hover:underline">Hapus Semua Titik</button>
                </div>
                <div id="route-map" class="w-full rounded-xl border border-gray-200 shadow-inner flex-1 min-h-[400px] lg:min-h-0" style="z-index: 0;"></div>
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-[11px] text-gray-500 shrink-0">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full" style="background-color: #1E5128"></span> Objek Budaya
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full" style="background-color: #8B5CF6"></span> UMKM / Toko
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full" style="background-color: #3B82F6"></span> Fasilitas Umum
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full" style="background-color: #06B6D4"></span> Toilet
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 rounded-full" style="background-color: #F59E0B"></span> Aksesibilitas
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // ==========================================
    // MAP BUILDER CONFIG & INITS
    // ==========================================
    const PENGLIPURAN_LAT = -8.421750367447837;
    const PENGLIPURAN_LNG = 115.35900208148409;
    const PENGLIPURAN_ZOOM = 17;

    const locations = @json($locations);
    let selectedPoints = []; // items: { id, name, category, latitude, longitude, locationable_type, locationable_id, estimated_visit_minutes, storytelling_content }
    
    let map = null;
    let markersMap = {}; // mapping local location.id to L.marker instance
    let routePolyline = null;

    document.addEventListener('DOMContentLoaded', function () {
        initRouteMap();
    });

    function initRouteMap() {
        map = L.map('route-map', { zoomControl: true, attributionControl: false })
            .setView([PENGLIPURAN_LAT, PENGLIPURAN_LNG], PENGLIPURAN_ZOOM);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        renderLocationMarkers();
    }

    function renderLocationMarkers() {
        // Clear existing markers if any
        for (let key in markersMap) {
            map.removeLayer(markersMap[key]);
        }
        markersMap = {};

        locations.forEach(loc => {
            if (!loc.latitude || !loc.longitude) return;

            // Find if this location is currently selected and what index
            const selIdx = selectedPoints.findIndex(p => p.id === loc.id);
            const isSelected = selIdx !== -1;
            const seqNumber = isSelected ? (selIdx + 1) : null;

            const icon = getMarkerIcon(loc.category, seqNumber);

            const marker = L.marker([loc.latitude, loc.longitude], { icon: icon }).addTo(map);

            // Bind detailed popup
            let popupContent = `
                <div class="p-1">
                    <h4 class="font-bold text-charcoal text-sm mb-1">${loc.name}</h4>
                    <span class="inline-block px-2 py-0.5 text-[10px] font-semibold text-white rounded mb-3 bg-${getCategoryColorClass(loc.category)}">
                        ${loc.category.toUpperCase()}
                    </span>
                    <div class="mt-1">
            `;

            if (isSelected) {
                popupContent += `
                        <button type="button" onclick="handleRemoveFromPopup(${loc.id})" class="w-full bg-warning hover:bg-warning-600 text-white font-bold py-1 px-3 rounded text-xs transition-colors">
                            Hapus dari Rute (Titik #${seqNumber})
                        </button>
                `;
            } else {
                popupContent += `
                        <button type="button" onclick="handleAddToPopup(${loc.id})" class="w-full bg-primary hover:bg-primary-600 text-white font-bold py-1 px-3 rounded text-xs transition-colors">
                            Tambah ke Rute
                        </button>
                `;
            }

            popupContent += `
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
            markersMap[loc.id] = marker;
        });
    }

    // Dynamic marker styling helper
    function getMarkerIcon(category, seqNumber = null) {
        const categoryColors = {
            umkm: '#8B5CF6',         // Violet
            facilities: '#3B82F6',   // Blue
            toilets: '#06B6D4',      // Cyan
            accessibility: '#F59E0B',// Amber
            cultural: '#1E5128'      // Green (Default)
        };
        const color = categoryColors[category] || '#1E5128';
        
        if (seqNumber !== null) {
            return L.divIcon({
                className: 'custom-pin-selected',
                html: `
                    <div class="relative flex items-center justify-center" style="width: 32px; height: 32px;">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-40" style="background-color: ${color};"></span>
                        <div class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-white shadow-lg text-white font-extrabold text-xs" style="background-color: ${color}; z-index: 10;">
                            ${seqNumber}
                        </div>
                    </div>
                `,
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
        } else {
            return L.divIcon({
                className: 'custom-pin-normal',
                html: `
                    <div class="flex items-center justify-center rounded-full border-2 border-white shadow-md hover:scale-110 transition-transform duration-200" style="background-color: ${color}; width: 22px; height: 22px;">
                    </div>
                `,
                iconSize: [22, 22],
                iconAnchor: [11, 11]
            });
        }
    }

    function getCategoryColorClass(cat) {
        switch (cat) {
            case 'umkm': return 'violet-500';
            case 'facilities': return 'blue-500';
            case 'toilets': return 'cyan-500';
            case 'accessibility': return 'amber-500';
            default: return 'primary';
        }
    }

    // ==========================================
    // SELECTION & ORDERING LOGIC
    // ==========================================
    function handleAddToPopup(locId) {
        const loc = locations.find(l => l.id === locId);
        if (!loc) return;

        // Add to selected list
        selectedPoints.push({
            id: loc.id,
            name: loc.name,
            category: loc.category,
            latitude: loc.latitude,
            longitude: loc.longitude,
            locationable_type: loc.locationable_type,
            locationable_id: loc.locationable_id,
            estimated_visit_minutes: 15,
            storytelling_content: ''
        });

        // Close popup, rebuild markers & update layout
        map.closePopup();
        renderLocationMarkers();
        updateBuilder();
    }

    function handleRemoveFromPopup(locId) {
        const idx = selectedPoints.findIndex(p => p.id === locId);
        if (idx !== -1) {
            selectedPoints.splice(idx, 1);
        }
        map.closePopup();
        renderLocationMarkers();
        updateBuilder();
    }

    function removePoint(idx) {
        selectedPoints.splice(idx, 1);
        renderLocationMarkers();
        updateBuilder();
    }

    function movePoint(idx, dir) {
        const targetIdx = idx + dir;
        if (targetIdx < 0 || targetIdx >= selectedPoints.length) return;

        // Swap items
        const temp = selectedPoints[idx];
        selectedPoints[idx] = selectedPoints[targetIdx];
        selectedPoints[targetIdx] = temp;

        renderLocationMarkers();
        updateBuilder();
    }

    function updatePointMinutes(idx, val) {
        const minutes = parseInt(val) || 15;
        selectedPoints[idx].estimated_visit_minutes = minutes;
        updateRouting(); // Re-calculate total duration immediately
    }

    function updatePointStorytelling(idx, val) {
        selectedPoints[idx].storytelling_content = val;
        // Sync input hidden element
        const hiddenEl = document.getElementById(`hidden-story-${idx}`);
        if (hiddenEl) {
            hiddenEl.value = val;
        }
    }

    function clearAllPoints() {
        selectedPoints = [];
        renderLocationMarkers();
        updateBuilder();
    }

    // ==========================================
    // SCREEN REDRAW AND API SYNC
    // ==========================================
    function updateBuilder() {
        const container = document.getElementById('points-list-container');
        const emptyState = document.getElementById('points-empty-state');
        const badge = document.getElementById('points-count-badge');

        badge.innerText = `${selectedPoints.length} Titik`;

        if (selectedPoints.length === 0) {
            emptyState.style.display = 'block';
            container.innerHTML = '';
            updateRouting();
            return;
        }

        emptyState.style.display = 'none';
        
        let html = '';
        selectedPoints.forEach((point, index) => {
            html += `
                <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex flex-col gap-3 relative transition-all hover:border-gray-200">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary font-display text-xs font-bold text-white">
                                ${index + 1}
                            </span>
                            <div>
                                <h4 class="font-semibold text-charcoal text-sm">${point.name}</h4>
                                <p class="text-[10px] uppercase font-bold tracking-wider text-gray-400 mt-0.5">${point.category}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="movePoint(${index}, -1)" ${index === 0 ? 'disabled' : ''} class="p-1 text-gray-400 hover:text-charcoal disabled:opacity-30">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            <button type="button" onclick="movePoint(${index}, 1)" ${index === selectedPoints.length - 1 ? 'disabled' : ''} class="p-1 text-gray-400 hover:text-charcoal disabled:opacity-30">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <button type="button" onclick="removePoint(${index})" class="p-1 text-warning hover:text-warning-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-4 items-center">
                        <div class="sm:col-span-1">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase">Kunjungan</label>
                            <div class="relative mt-1 flex items-center">
                                <input type="number" min="1" value="${point.estimated_visit_minutes}" onchange="updatePointMinutes(${index}, this.value)" class="w-full rounded-lg border border-gray-200 py-1.5 px-2 text-xs focus:border-primary focus:outline-none">
                                <span class="absolute right-2 text-[10px] text-gray-400 font-bold">m</span>
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase">Narasi Storytelling</label>
                            <input type="text" value="${point.storytelling_content || ''}" onchange="updatePointStorytelling(${index}, this.value)" placeholder="Cerita menarik untuk titik ini..." class="mt-1 w-full rounded-lg border border-gray-200 py-1.5 px-3 text-xs focus:border-primary focus:outline-none">
                        </div>
                    </div>
                    
                    <input type="hidden" name="points[${index}][locationable_type]" value="${point.locationable_type}">
                    <input type="hidden" name="points[${index}][locationable_id]" value="${point.locationable_id}">
                    <input type="hidden" name="points[${index}][estimated_visit_minutes]" value="${point.estimated_visit_minutes}">
                    <input type="hidden" id="hidden-story-${index}" name="points[${index}][storytelling_content]" value="${point.storytelling_content || ''}">
                </div>
            `;
        });

        container.innerHTML = html;
        updateRouting();
    }

    async function updateRouting() {
        if (selectedPoints.length < 2) {
            // Reset routing line
            if (routePolyline) {
                map.removeLayer(routePolyline);
                routePolyline = null;
            }
            // Reset distance display
            document.getElementById('route-distance-display').innerText = '0 m';
            document.getElementById('field-distance').value = 0;
            
            // Total duration is just visit times
            let visitMinutesSum = selectedPoints.reduce((acc, p) => acc + parseInt(p.estimated_visit_minutes || 15), 0);
            document.getElementById('route-duration-display').innerText = `${visitMinutesSum} menit`;
            document.getElementById('field-duration').value = visitMinutesSum;
            return;
        }

        // Build coordinates format: Lng,Lat;Lng,Lat;...
        const coords = selectedPoints.map(p => `${p.longitude},${p.latitude}`).join(';');
        const url = `https://router.project-osrm.org/route/v1/foot/${coords}?overview=full&geometries=geojson`;

        try {
            const response = await fetch(url);
            const data = await response.json();

            if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                const route = data.routes[0];
                const distance = Math.round(route.distance); // in meters
                const walkingDuration = Math.round(route.duration / 60); // in minutes

                // Draw route line on map
                if (routePolyline) {
                    map.removeLayer(routePolyline);
                }

                routePolyline = L.layerGroup();
                
                // Background shadow path
                L.geoJSON(route.geometry, {
                    style: {
                        color: '#4F46E5',
                        weight: 6,
                        opacity: 0.25
                    }
                }).addTo(routePolyline);

                // Dotted foreground path
                L.geoJSON(route.geometry, {
                    style: {
                        color: '#4F46E5',
                        weight: 3.5,
                        opacity: 0.95,
                        dashArray: '6, 8'
                    }
                }).addTo(routePolyline);

                routePolyline.addTo(map);
                map.fitBounds(L.geoJSON(route.geometry).getBounds(), { padding: [40, 40] });

                // Accumulate visit durations
                let visitMinutesSum = selectedPoints.reduce((acc, p) => acc + parseInt(p.estimated_visit_minutes || 15), 0);
                const totalDuration = walkingDuration + visitMinutesSum;

                // Sync inputs
                document.getElementById('route-distance-display').innerText = distance >= 1000 
                    ? (distance / 1000).toFixed(2) + ' km' 
                    : distance + ' m';
                document.getElementById('field-distance').value = distance;

                document.getElementById('route-duration-display').innerText = totalDuration >= 60 
                    ? Math.floor(totalDuration / 60) + ' jam ' + (totalDuration % 60) + ' menit'
                    : totalDuration + ' menit';
                document.getElementById('field-duration').value = totalDuration;

            } else {
                console.warn('OSRM routing request failed:', data.code);
            }
        } catch (error) {
            console.error('Error fetching OSRM route:', error);
        }
    }
</script>
@endpush
