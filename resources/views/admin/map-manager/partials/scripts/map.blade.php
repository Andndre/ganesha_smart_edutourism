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

        initMapStyleSwitcher(map);

        renderMarkers();

        // Map Click handler: trigger create mode
        map.on('click', function (e) {
            handleMapClick(e.latlng.lat, e.latlng.lng);
        });
    }

    // Peta editor memakai pin yang sama persis dengan /explore lewat window.gseMapPin,
    // supaya admin melihat apa yang dilihat turis — termasuk glyph jenis tempat yang
    // baru dipilih. Menggantikan tiga pembuat ikon bespoke plus salinan palet warna.
    //
    // Tiga state di bawah memetakan satu-satu ke tampilan lama:
    //   normal   solid berwarna      -> tetap solid berwarna
    //   sibling  lingkaran bercincin -> outline (badan putih, cincin kategori)
    //   selected cincin berdenyut    -> highlight (outline + halo emas + diperbesar)
    // Sibling sengaja bukan `dimmed`: titik-titik itu sedang ditonjolkan, bukan
    // diredupkan.

    // map_locations menyimpan enum mentah, gseMapPin memakai kunci lain. Pemetaan yang
    // sama sudah ada di sisi PHP pada ExploreController::index().
    function pinCategory(loc) {
        const category = loc.category;
        const type = loc.locationable ? loc.locationable.type : null;

        if (category === 'facility') {
            return type === 'toilet' ? 'toilets' : 'facilities';
        }
        if (category === 'toilet') return 'toilets';
        if (category === 'emergency') return 'facilities';

        return category;
    }

    function pinOptions(loc, extra) {
        const details = loc.locationable;

        return Object.assign({
            placeType: details ? details.place_type : null
        }, extra || {});
    }

    function getMarkerIcon(loc) {
        return window.gseMapPin(pinCategory(loc), pinOptions(loc));
    }

    function getSelectedMarkerIcon(loc) {
        return window.gseMapPin(pinCategory(loc), pinOptions(loc, { highlight: true }));
    }

    function getSiblingMarkerIcon(loc) {
        return window.gseMapPin(pinCategory(loc), pinOptions(loc, { outline: true }));
    }

    // Other markers belonging to the same owner (locationable_type + locationable_id)
    function getSiblingMarkers(marker) {
        const loc = marker.locationData;
        if (!loc.locationable_type || !loc.locationable_id) return [];

        return markers.filter(m => m !== marker
            && m.locationData.locationable_type === loc.locationable_type
            && m.locationData.locationable_id === loc.locationable_id);
    }

    function highlightSiblingGroup(marker) {
        const siblings = getSiblingMarkers(marker);
        siblingMarkers = siblings;
        siblings.forEach(m => {
            m.setIcon(getSiblingMarkerIcon(m.locationData));
        });
        return siblings;
    }

    function clearSiblingHighlight() {
        siblingMarkers.forEach(m => {
            m.setIcon(getMarkerIcon(m.locationData));
        });
        siblingMarkers = [];
    }

    function renderMarkers() {
        if (!map) return;
        
        // Clear all markers from map
        markers.forEach(m => map.removeLayer(m));
        markers = [];

        locations.forEach(loc => {
            if (!loc.latitude || !loc.longitude) return;

            const marker = L.marker([loc.latitude, loc.longitude], {
                icon: getMarkerIcon(loc)
            });

            // Store custom info
            marker.locationData = loc;

            // Marker Click handler: edit mode (or point-choice popup when the owner has multiple points)
            marker.on('click', function (e) {
                L.DomEvent.stopPropagation(e); // Stop from triggering map click
                if (getSiblingMarkers(marker).length > 0) {
                    showPointChoicePopup(marker);
                } else {
                    handleMarkerClick(marker);
                }
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
