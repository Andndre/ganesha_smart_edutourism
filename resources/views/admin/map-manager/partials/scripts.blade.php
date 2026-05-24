<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // ==========================================
    // CONFIG & INITS
    // ==========================================
    const PENGLIPURAN_LAT = -8.421750367447837;
    const PENGLIPURAN_LNG = 115.35900208148409;
    const PENGLIPURAN_ZOOM = 17;

    // Loaded locations from Controller
    const locations = @json($locations);
    const storageUrl = "{{ asset('storage') }}";

    let map = null;
    let markers = []; // List of L.marker instances
    let activeMarker = null; // Currently selected/edited marker
    let tempMarker = null; // Temp marker when creating new location
    let currentMode = 'idle'; // 'idle', 'create', 'edit'

    // Set up category colors
    const categoryColors = {
        umkm: '#8B5CF6',         // Violet
        facility: '#3B82F6',     // Blue
        toilet: '#06B6D4',       // Cyan
        cultural: '#1E5128'      // Green
    };

    document.addEventListener('DOMContentLoaded', function () {
        initCounts();
        initMap();
    });

    function initCounts() {
        let countCultural = 0;
        let countUmkm = 0;
        let countFacility = 0;
        let countToilet = 0;

        locations.forEach(loc => {
            if (loc.category === 'cultural') countCultural++;
            else if (loc.category === 'umkm') countUmkm++;
            else if (loc.category === 'facility') {
                if (loc.locationable && loc.locationable.type === 'toilet') countToilet++;
                else countFacility++;
            }
        });

        document.getElementById('count-cultural').innerText = countCultural;
        document.getElementById('count-umkm').innerText = countUmkm;
        document.getElementById('count-facility').innerText = countFacility;
        document.getElementById('count-toilet').innerText = countToilet;
    }

    function initMap() {
        map = L.map('location-map', { zoomControl: true, attributionControl: false })
            .setView([PENGLIPURAN_LAT, PENGLIPURAN_LNG], PENGLIPURAN_ZOOM);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        renderMarkers();

        // Map Click handler: trigger create mode
        map.on('click', function (e) {
            handleMapClick(e.latlng.lat, e.latlng.lng);
        });
    }

    // Dynamic marker icon helper
    function getMarkerIcon(category, type = null) {
        let color = categoryColors[category] || '#1E5128';
        if (category === 'facility' && type === 'toilet') {
            color = categoryColors.toilet;
        }

        return L.divIcon({
            className: 'custom-pin',
            html: `
                <div class="flex items-center justify-center rounded-full border-2 border-white shadow-md hover:scale-110 transition-transform duration-200" 
                     style="background-color: ${color}; width: 22px; height: 22px;">
                </div>
            `,
            iconSize: [22, 22],
            iconAnchor: [11, 11]
        });
    }

    // Dynamic icon for selected/draggable marker
    getSelectedMarkerIcon = function (category, type = null) {
        let color = categoryColors[category] || '#1E5128';
        if (category === 'facility' && type === 'toilet') {
            color = categoryColors.toilet;
        }

        return L.divIcon({
            className: 'custom-pin-selected',
            html: `
                <div class="relative flex items-center justify-center animate-bounce" style="width: 32px; height: 32px; margin-top: -10px;">
                    <span class="absolute inline-flex h-6 w-6 animate-ping rounded-full opacity-40" style="background-color: ${color};"></span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white shadow-lg text-white" style="background-color: ${color}; z-index: 10;">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                    </div>
                </div>
            `,
            iconSize: [32, 32],
            iconAnchor: [16, 32]
        });
    }

    function renderMarkers() {
        // Clear all markers from map
        markers.forEach(m => map.removeLayer(m));
        markers = [];

        locations.forEach(loc => {
            if (!loc.latitude || !loc.longitude) return;

            const isToilet = (loc.category === 'facility' && loc.locationable && loc.locationable.type === 'toilet');
            const marker = L.marker([loc.latitude, loc.longitude], {
                icon: getMarkerIcon(loc.category, loc.locationable ? loc.locationable.type : null)
            });

            // Store custom info
            marker.locationData = loc;

            // Marker Click handler: edit mode
            marker.on('click', function (e) {
                L.DomEvent.stopPropagation(e); // Stop from triggering map click
                handleMarkerClick(marker);
            });

            // Attach marker to the map and array
            marker.addTo(map);
            markers.push(marker);
        });
    }

    function filterMarkers() {
        const showCultural = document.getElementById('filter-cultural').checked;
        const showUmkm = document.getElementById('filter-umkm').checked;
        const showFacility = document.getElementById('filter-facility').checked;
        const showToilet = document.getElementById('filter-toilet').checked;

        markers.forEach(m => {
            const loc = m.locationData;
            let visible = false;

            if (loc.category === 'cultural' && showCultural) visible = true;
            else if (loc.category === 'umkm' && showUmkm) visible = true;
            else if (loc.category === 'facility') {
                const isToilet = loc.locationable && loc.locationable.type === 'toilet';
                if (isToilet && showToilet) visible = true;
                if (!isToilet && showFacility) visible = true;
            }

            if (visible) {
                if (!map.hasLayer(m)) m.addTo(map);
            } else {
                if (map.hasLayer(m)) map.removeLayer(m);
            }
        });
    }

    // ==========================================
    // CREATE / ADD NEW LOCATION LOGIC
    // ==========================================
    function handleMapClick(lat, lng) {
        if (currentMode === 'edit') {
            // In edit mode, clicking the map moves the selected marker's position
            if (activeMarker) {
                activeMarker.setLatLng([lat, lng]);
                updateCoordinateInputs(lat, lng);
            }
            return;
        }

        currentMode = 'create';

        // Show panel & reset forms
        document.getElementById('panel-idle').classList.add('hidden');
        document.getElementById('panel-editor').classList.remove('hidden');
        document.getElementById('editor-title').innerText = "Tambah Lokasi Baru";
        document.getElementById('selector-container').classList.remove('hidden');
        document.getElementById('delete-container').classList.add('hidden');

        // Remove active marker animations if editing before
        resetSelectedMarkerVisuals();

        // Place temporary marker
        if (tempMarker) {
            tempMarker.setLatLng([lat, lng]);
        } else {
            tempMarker = L.marker([lat, lng], {
                icon: getSelectedMarkerIcon('cultural'), // default
                draggable: true
            }).addTo(map);

            tempMarker.on('dragend', function (e) {
                const pos = tempMarker.getLatLng();
                updateCoordinateInputs(pos.lat, pos.lng);
            });
        }

        // Reset and switch to default (cultural) form
        resetForms();

        updateCoordinateInputs(lat, lng);

        const typeSelect = document.getElementById('type-selector');
        typeSelect.disabled = false;
        typeSelect.value = 'cultural';

        switchForm('cultural');
    }

    function updateCoordinateInputs(lat, lng) {
        const fixedLat = parseFloat(lat).toFixed(8);
        const fixedLng = parseFloat(lng).toFixed(8);

        document.querySelectorAll('input[name="latitude"]').forEach(input => input.value = fixedLat);
        document.querySelectorAll('input[name="longitude"]').forEach(input => input.value = fixedLng);
    }

    function switchForm(type) {
        // Hide all forms
        document.getElementById('form-cultural').classList.add('hidden');
        document.getElementById('form-umkm').classList.add('hidden');
        document.getElementById('form-facility').classList.add('hidden');

        // Show active form
        const formId = `form-${type}`;
        document.getElementById(formId).classList.remove('hidden');

        // Update temp marker color to match type
        if (tempMarker) {
            let catType = type;
            if (type === 'facility') {
                const subType = document.querySelector('#form-facility select[name="type"]').value;
                tempMarker.setIcon(getSelectedMarkerIcon('facility', subType));
            } else {
                tempMarker.setIcon(getSelectedMarkerIcon(type));
            }
        }
    }

    // Attach listener to facility type select to update icon color
    document.querySelector('#form-facility select[name="type"]').addEventListener('change', function () {
        if (tempMarker && currentMode === 'create') {
            tempMarker.setIcon(getSelectedMarkerIcon('facility', this.value));
        }
        if (activeMarker && currentMode === 'edit') {
            activeMarker.setIcon(getSelectedMarkerIcon('facility', this.value));
        }
    });

    // ==========================================
    // EDIT LOCATION LOGIC
    // ==========================================
    function handleMarkerClick(marker) {
        // Remove temp marker if it exists
        if (tempMarker) {
            map.removeLayer(tempMarker);
            tempMarker = null;
        }

        // Reset any previous active marker visuals
        resetSelectedMarkerVisuals();

        currentMode = 'edit';
        activeMarker = marker;

        const loc = marker.locationData;
        const details = loc.locationable;

        // Change marker icon to selected
        const type = details ? details.type : null;
        marker.setIcon(getSelectedMarkerIcon(loc.category, type));

        // Enable dragging for this marker
        marker.dragging.enable();
        marker.on('dragend', function (e) {
            const pos = marker.getLatLng();
            updateCoordinateInputs(pos.lat, pos.lng);
        });

        // Toggle panel
        document.getElementById('panel-idle').classList.add('hidden');
        document.getElementById('panel-editor').classList.remove('hidden');
        document.getElementById('editor-title').innerText = "Edit Lokasi";

        // Disable type selector since we can't transform type
        const typeSelect = document.getElementById('type-selector');
        typeSelect.value = loc.category;
        typeSelect.disabled = true;

        // Show delete option
        document.getElementById('delete-container').classList.remove('hidden');

        resetForms();
        updateCoordinateInputs(loc.latitude, loc.longitude);

        if (loc.category === 'cultural') {
            switchForm('cultural');
            const form = document.getElementById('form-cultural');
            form.action = `/admin/cultural-objects/${details.id}`;
            document.getElementById('method-cultural').innerHTML = '@method("PUT")';

            form.querySelector('input[name="name"]').value = details.name;
            form.querySelector('select[name="category"]').value = details.category;
            form.querySelector('textarea[name="description"]').value = details.description || '';
            form.querySelector('input[name="ar_marker_id"]').value = details.ar_marker_id || '';

            // File previews
            document.getElementById('current-model-3d').innerHTML = details.model_3d_path
                ? `File saat ini: <a href="${storageUrl}/${details.model_3d_path}" target="_blank" class="text-primary hover:underline font-semibold">${details.model_3d_path.split('/').pop()}</a>`
                : 'Belum ada model 3D';

            document.getElementById('current-audio').innerHTML = details.audio_narration_path
                ? `File saat ini: <a href="${storageUrl}/${details.audio_narration_path}" target="_blank" class="text-primary hover:underline font-semibold">${details.audio_narration_path.split('/').pop()}</a>`
                : 'Belum ada audio narasi';

            const imgContainer = document.getElementById('current-images');
            imgContainer.innerHTML = '';
            if (details.historical_images && details.historical_images.length > 0) {
                details.historical_images.forEach(img => {
                    const imgEl = document.createElement('img');
                    imgEl.src = `${storageUrl}/${img}`;
                    imgEl.className = "w-10 h-10 object-cover rounded border border-gray-100";
                    imgContainer.appendChild(imgEl);
                });
            }

            form.querySelector('input[name="is_accessible"]').checked = loc.is_accessible;
            form.querySelector('textarea[name="accessibility_notes"]').value = loc.accessibility_notes || '';

            // Setup Delete Action
            document.getElementById('form-delete').action = `/admin/cultural-objects/${details.id}`;

        } else if (loc.category === 'umkm') {
            switchForm('umkm');
            const form = document.getElementById('form-umkm');
            form.action = `/admin/umkm/profiles/${details.id}`;
            document.getElementById('method-umkm').innerHTML = '@method("PUT")';

            form.querySelector('input[name="business_name"]').value = details.business_name;
            form.querySelector('input[name="owner_name"]').value = details.owner_name;
            document.getElementById('umkm-owner-user-id').value = details.user_id || '';
            document.getElementById('umkm-owner-search').value = details.owner_name || '';
            form.querySelector('select[name="category"]').value = details.category;
            form.querySelector('textarea[name="description"]').value = details.description || '';
            form.querySelector('input[name="rating"]').value = details.rating || '5.0';
            form.querySelector('input[name="ar_marker_id"]').value = details.ar_marker_id || '';
            form.querySelector('input[name="is_active"]').checked = details.is_active;
            form.querySelector('input[name="is_accessible"]').checked = loc.is_accessible;
            form.querySelector('textarea[name="accessibility_notes"]').value = loc.accessibility_notes || '';

            // Setup Delete Action
            document.getElementById('form-delete').action = `/admin/umkm/profiles/${details.id}`;

        } else if (loc.category === 'facility') {
            switchForm('facility');
            const form = document.getElementById('form-facility');
            form.action = `/admin/facilities/${details.id}`;
            document.getElementById('method-facility').innerHTML = '@method("PUT")';

            form.querySelector('input[name="name"]').value = details.name;
            form.querySelector('select[name="type"]').value = details.type;
            form.querySelector('textarea[name="description"]').value = details.description || '';
            form.querySelector('input[name="is_active"]').checked = details.is_active;
            form.querySelector('input[name="is_accessible"]').checked = loc.is_accessible;
            form.querySelector('textarea[name="accessibility_notes"]').value = loc.accessibility_notes || '';

            // Setup Delete Action
            document.getElementById('form-delete').action = `/admin/facilities/${details.id}`;
        }

        // Center map to marker
        map.panTo(marker.getLatLng());
    }

    // ==========================================
    // UTILITIES / RESETS
    // ==========================================
    function cancelEditor() {
        currentMode = 'idle';
        document.getElementById('panel-idle').classList.remove('hidden');
        document.getElementById('panel-editor').classList.add('hidden');

        // Remove temporary marker
        if (tempMarker) {
            map.removeLayer(tempMarker);
            tempMarker = null;
        }

        resetSelectedMarkerVisuals();
        resetForms();
    }

    function resetSelectedMarkerVisuals() {
        if (activeMarker) {
            const loc = activeMarker.locationData;
            const details = loc.locationable;
            const type = details ? details.type : null;

            // Revert icon to normal
            activeMarker.setIcon(getMarkerIcon(loc.category, type));

            // Disable dragging & listeners
            activeMarker.dragging.disable();
            activeMarker.off('dragend');

            activeMarker = null;
        }
    }

    function resetForms() {
        // Reset inputs and methods in forms
        const culturalForm = document.getElementById('form-cultural');
        culturalForm.reset();
        culturalForm.action = "{{ route('admin.cultural-objects.store') }}";
        document.getElementById('method-cultural').innerHTML = '';
        document.getElementById('current-model-3d').innerHTML = '';
        document.getElementById('current-audio').innerHTML = '';
        document.getElementById('current-images').innerHTML = '';

        const umkmForm = document.getElementById('form-umkm');
        umkmForm.reset();
        umkmForm.action = "{{ route('admin.umkm.profile.store') }}";
        document.getElementById('method-umkm').innerHTML = '';
        document.getElementById('umkm-owner-user-id').value = '';
        document.getElementById('umkm-owner-search').value = '';
        document.getElementById('umkm-owner-name').value = '';

        const facilityForm = document.getElementById('form-facility');
        facilityForm.reset();
        facilityForm.action = "{{ route('admin.facilities.store') }}";
        document.getElementById('method-facility').innerHTML = '';
    }
</script>
