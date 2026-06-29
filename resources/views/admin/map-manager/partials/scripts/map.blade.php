<script>
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

        const culturalCountEl = document.getElementById('count-cultural');
        if (culturalCountEl) culturalCountEl.innerText = countCultural;
        const umkmCountEl = document.getElementById('count-umkm');
        if (umkmCountEl) umkmCountEl.innerText = countUmkm;
        const facilityCountEl = document.getElementById('count-facility');
        if (facilityCountEl) facilityCountEl.innerText = countFacility;
        const toiletCountEl = document.getElementById('count-toilet');
        if (toiletCountEl) toiletCountEl.innerText = countToilet;
    }

    function initMap() {
        const mapEl = document.getElementById('location-map');
        if (!mapEl) return;
        
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
    function getSelectedMarkerIcon(category, type = null) {
        let color = categoryColors[category] || '#1E5128';
        if (category === 'facility' && type === 'toilet') {
            color = categoryColors.toilet;
        }

        return L.divIcon({
            className: 'custom-pin-selected',
            html: `
                <div class="relative flex items-center justify-center marker-selected-glow" style="width: 32px; height: 32px;">
                    <span class="absolute inline-flex h-6 w-6 animate-ping rounded-full opacity-40" style="background-color: ${color};"></span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white shadow-lg text-white" style="background-color: ${color}; z-index: 10;">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                    </div>
                </div>
            `,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
    }

    function renderMarkers() {
        if (!map) return;
        
        // Clear all markers from map
        markers.forEach(m => map.removeLayer(m));
        markers = [];

        locations.forEach(loc => {
            if (!loc.latitude || !loc.longitude) return;

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
</script>
