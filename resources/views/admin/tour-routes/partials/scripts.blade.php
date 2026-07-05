<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-gesture-handling@1.2.2/dist/leaflet-gesture-handling.min.js"></script>
@include('components.map-style-script')
<script>
    // ==========================================
    // MAP BUILDER CONFIG & INITS
    // ==========================================
    const PENGLIPURAN_LAT = {{ config('services.penglipuran.latitude') }};
    const PENGLIPURAN_LNG = {{ config('services.penglipuran.longitude') }};
    const PENGLIPURAN_ZOOM = {{ config('services.penglipuran.zoom') }};

    const locations = @json($locations);
    let selectedPoints = []; // items: { id (MapLocation id), point_id (TourRoutePoint DB id, nullable), name, category, latitude, longitude, locationable_type, locationable_id, estimated_visit_minutes, storytelling_content }
    
    @if(isset($isEdit) && $isEdit)
        // Initial points serialized from route points relation
        const initialPointsData = {{ Illuminate\Support\Js::from($route->routePoints->sortBy('order')->map(function ($point) {
            return [
                'id' => $point->id,
                'locationable_type' => $point->locationable_type,
                'locationable_id' => $point->locationable_id,
                'estimated_visit_minutes' => $point->estimated_visit_minutes,
                'storytelling_content' => $point->getTranslations('storytelling_content'),
                'missions' => $point->missions->map(fn ($m) => [
                    'id' => $m->id,
                    'type' => $m->type,
                    'title' => $m->getTranslations('title'),
                    'points' => $m->points,
                    'time_limit_seconds' => $m->time_limit_seconds,
                    'config' => $m->config,
                ])->values(),
            ];
        })->values()) }};

        // Populate selectedPoints based on initial points from DB matched against local locations
        // Note: `id` on selectedPoints entries is the MapLocation id (used throughout this
        // file for marker matching); the TourRoutePoint DB id is carried separately as
        // `point_id` so it can be round-tripped on save without colliding with that.
        function initSelectedPoints() {
            initialPointsData.forEach(p => {
                const loc = locations.find(l => l.locationable_type === p.locationable_type && l.locationable_id === p.locationable_id);
                if (loc) {
                    selectedPoints.push({
                        id: loc.id,
                        point_id: p.id,
                        name: loc.name,
                        category: loc.category,
                        latitude: loc.latitude,
                        longitude: loc.longitude,
                        locationable_type: loc.locationable_type,
                        locationable_id: loc.locationable_id,
                        estimated_visit_minutes: p.estimated_visit_minutes || 15,
                        storytelling_content: p.storytelling_content || { en: '', id: '' },
                        missions: p.missions || []
                    });
                }
            });
        }
    @endif

    let map = null;
    let markersMap = {}; // mapping local location.id to L.marker instance
    let routePolyline = null;

    document.addEventListener('DOMContentLoaded', function () {
        @if(isset($isEdit) && $isEdit)
            initSelectedPoints();
        @endif
        initRouteMap();
        @if(isset($isEdit) && $isEdit)
            updateBuilder();
        @endif
    });

    function initRouteMap() {
        map = L.map('route-map', { zoomControl: true, attributionControl: false, gestureHandling: true })
            .setView([PENGLIPURAN_LAT, PENGLIPURAN_LNG], PENGLIPURAN_ZOOM);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        initMapStyleSwitcher(map);

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
            point_id: null,
            name: loc.name,
            category: loc.category,
            latitude: loc.latitude,
            longitude: loc.longitude,
            locationable_type: loc.locationable_type,
            locationable_id: loc.locationable_id,
            estimated_visit_minutes: 15,
            storytelling_content: { en: '', id: '' },
            missions: []
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

    function updatePointStorytelling(idx, locale, val) {
        if (typeof selectedPoints[idx].storytelling_content !== 'object' || !selectedPoints[idx].storytelling_content) {
            const currentVal = selectedPoints[idx].storytelling_content || '';
            selectedPoints[idx].storytelling_content = {
                en: currentVal,
                id: currentVal
            };
        }
        selectedPoints[idx].storytelling_content[locale] = val;
        
        // Sync input hidden elements
        const hiddenElEn = document.getElementById(`hidden-story-en-${idx}`);
        if (hiddenElEn) {
            hiddenElEn.value = selectedPoints[idx].storytelling_content.en || '';
        }
        const hiddenElId = document.getElementById(`hidden-story-id-${idx}`);
        if (hiddenElId) {
            hiddenElId.value = selectedPoints[idx].storytelling_content.id || '';
        }
    }

    // Attach storytelling sync helper dynamically
    window.updatePointStorytelling = updatePointStorytelling;

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
            // Resync missionState from the point's own data on every redraw (not just once)
            // so that reordering/removing points — which shuffle which point sits at which
            // array index — never leaves window.missionState[index] pointing at a different
            // point's missions. `point.missions` is the source of truth; missions.blade.php's
            // closeMissionModal() writes edits back into it and calls updateBuilder() again.
            window.missionState[index] = point.missions || [];
            // Seed the hidden missions input from the point's own data on every redraw,
            // so a fresh render is never blank — it always reflects whatever
            // point.missions currently holds (closeMissionModal() in missions.blade.php
            // writes into point.missions before calling updateBuilder(), so this never
            // stomps a just-saved edit with an empty value).
            const missionsJson = JSON.stringify(point.missions || [])
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            let storytellingEn = '';
            let storytellingId = '';
            if (point.storytelling_content) {
                if (typeof point.storytelling_content === 'object') {
                    storytellingEn = point.storytelling_content.en || '';
                    storytellingId = point.storytelling_content.id || '';
                } else {
                    storytellingEn = point.storytelling_content;
                    storytellingId = point.storytelling_content;
                }
            }

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
                                <input type="number" min="1" value="${point.estimated_visit_minutes}" onchange="updatePointMinutes(${index}, this.value)" class="w-full rounded-lg border border-gray-200 py-1.5 pl-2 pr-12 text-xs focus:border-primary focus:outline-none">
                                <span class="absolute right-2 text-[10px] text-gray-400 font-bold">menit</span>
                            </div>
                        </div>
                        <div class="sm:col-span-3 space-y-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase">Narasi Storytelling (EN)</label>
                                <input type="text" value="${storytellingEn}" onchange="updatePointStorytelling(${index}, 'en', this.value)" placeholder="e.g. Interesting story about this temple..." class="mt-1 w-full rounded-lg border border-gray-200 py-1.5 px-3 text-xs focus:border-primary focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase">Narasi Storytelling (ID)</label>
                                <input type="text" value="${storytellingId}" onchange="updatePointStorytelling(${index}, 'id', this.value)" placeholder="Contoh: Cerita menarik untuk titik ini..." class="mt-1 w-full rounded-lg border border-gray-200 py-1.5 px-3 text-xs focus:border-primary focus:outline-none">
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="points[${index}][id]" value="${point.point_id || ''}">
                    <input type="hidden" name="points[${index}][locationable_type]" value="${point.locationable_type}">
                    <input type="hidden" name="points[${index}][locationable_id]" value="${point.locationable_id}">
                    <input type="hidden" name="points[${index}][estimated_visit_minutes]" value="${point.estimated_visit_minutes}">
                    <input type="hidden" id="hidden-story-en-${index}" name="points[${index}][storytelling_content][en]" value="${storytellingEn}">
                    <input type="hidden" id="hidden-story-id-${index}" name="points[${index}][storytelling_content][id]" value="${storytellingId}">

                    <input type="hidden" id="missions-input-${index}" name="points[${index}][missions]" value="${missionsJson}">
                    <button type="button" onclick="openMissionModal(${index})"
                        class="border-primary text-primary hover:bg-primary/5 mt-2 w-full rounded-xl border-2 py-2 text-xs font-semibold">
                        Kelola Misi (<span>${(point.missions || []).length}</span>)
                    </button>
                </div>
            `;
        });

        container.innerHTML = html;
        updateRouting();
    }

    function drawStraightLine() {
        let distance = 0;
        const latlngs = [];
        for (let i = 0; i < selectedPoints.length; i++) {
            latlngs.push([selectedPoints[i].latitude, selectedPoints[i].longitude]);
            if (i < selectedPoints.length - 1) {
                const p1 = L.latLng(selectedPoints[i].latitude, selectedPoints[i].longitude);
                const p2 = L.latLng(selectedPoints[i + 1].latitude, selectedPoints[i + 1].longitude);
                distance += p1.distanceTo(p2);
            }
        }
        distance = Math.round(distance);
        const walkingDuration = Math.round(distance / 80); // 80 m/minute

        if (routePolyline) {
            map.removeLayer(routePolyline);
        }
        routePolyline = L.layerGroup();
        
        L.polyline(latlngs, {
            color: '#4F46E5',
            weight: 6,
            opacity: 0.25
        }).addTo(routePolyline);

        L.polyline(latlngs, {
            color: '#4F46E5',
            weight: 3.5,
            opacity: 0.95,
            dashArray: '6, 8'
        }).addTo(routePolyline);

        routePolyline.addTo(map);
        map.fitBounds(L.polyline(latlngs).getBounds(), { padding: [40, 40] });

        let visitMinutesSum = selectedPoints.reduce((acc, p) => acc + parseInt(p.estimated_visit_minutes || 15), 0);
        const totalDuration = walkingDuration + visitMinutesSum;

        document.getElementById('route-distance-display').innerText = distance >= 1000 
            ? (distance / 1000).toFixed(2) + ' km' 
            : distance + ' m';
        document.getElementById('field-distance').value = distance;

        document.getElementById('route-duration-display').innerText = totalDuration >= 60 
            ? Math.floor(totalDuration / 60) + ' jam ' + (totalDuration % 60) + ' menit'
            : totalDuration + ' menit';
        document.getElementById('field-duration').value = totalDuration;
    }

    async function updateRouting() {
        document.getElementById('routing-warning').classList.add('hidden');

        if (selectedPoints.length < 2) {
            if (routePolyline) {
                map.removeLayer(routePolyline);
                routePolyline = null;
            }
            document.getElementById('route-distance-display').innerText = '0 m';
            document.getElementById('field-distance').value = 0;
            
            let visitMinutesSum = selectedPoints.reduce((acc, p) => acc + parseInt(p.estimated_visit_minutes || 15), 0);
            document.getElementById('route-duration-display').innerText = `${visitMinutesSum} menit`;
            document.getElementById('field-duration').value = visitMinutesSum;
            return;
        }

        const coords = selectedPoints.map(p => [parseFloat(p.longitude), parseFloat(p.latitude)]);
        let success = false;

        try {
            const response = await fetch('/admin/api/routing/directions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ coordinates: coords })
            });
            const data = await response.json();

            if (response.ok && data.features && data.features.length > 0) {
                const route = data.features[0];
                const distance = Math.round(route.properties.summary.distance); // in meters
                const walkingDuration = Math.round(route.properties.summary.duration / 60); // in minutes

                if (distance > 0) {
                    if (routePolyline) {
                        map.removeLayer(routePolyline);
                    }

                    routePolyline = L.layerGroup();
                    
                    L.geoJSON(route.geometry, {
                        style: {
                            color: '#4F46E5',
                            weight: 6,
                            opacity: 0.25
                        }
                    }).addTo(routePolyline);

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

                    let visitMinutesSum = selectedPoints.reduce((acc, p) => acc + parseInt(p.estimated_visit_minutes || 15), 0);
                    const totalDuration = walkingDuration + visitMinutesSum;

                    document.getElementById('route-distance-display').innerText = distance >= 1000 
                        ? (distance / 1000).toFixed(2) + ' km' 
                        : distance + ' m';
                    document.getElementById('field-distance').value = distance;

                    document.getElementById('route-duration-display').innerText = totalDuration >= 60 
                        ? Math.floor(totalDuration / 60) + ' jam ' + (totalDuration % 60) + ' menit'
                        : totalDuration + ' menit';
                    document.getElementById('field-duration').value = totalDuration;

                    success = true;
                } else {
                    document.getElementById('routing-warning').classList.remove('hidden');
                }
            } else {
                console.warn('ORS routing request failed:', data.error || data);
            }
        } catch (error) {
            console.error('Error fetching ORS route:', error);
        }

        if (!success) {
            drawStraightLine();
        }
    }

    // Ensure every point's missions hidden input is populated before submit, even for
    // points whose "Kelola Misi" modal was never opened this session (window.missionState
    // is already seeded per-point in updateBuilder(), so this just serializes it out).
    document.getElementById('route-form')?.addEventListener('submit', () => {
        selectedPoints.forEach((point, idx) => {
            if (!window.missionState[idx]) window.missionState[idx] = point.missions || [];
            if (typeof serializeMissions === 'function') serializeMissions(idx);
        });
    });
</script>
