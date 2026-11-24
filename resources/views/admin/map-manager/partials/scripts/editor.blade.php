<script>
// Mini audio player helpers (shared with AR manager modal)
if (!window.toggleMiniAudio) {
    window.toggleMiniAudio = function(btn) {
        const row = btn.parentElement;
        const audio = row.querySelector('.mini-audio-el');
        if (!audio) return;
        const playIcon = btn.querySelector('.mini-audio-play');
        const pauseIcon = btn.querySelector('.mini-audio-pause');
        document.querySelectorAll('.mini-audio-el').forEach(function(a) {
            if (a === audio) return;
            a.pause();
            const b = a.parentElement?.querySelector('.mini-audio-btn');
            if (b) { b.querySelector('.mini-audio-play')?.classList.remove('hidden'); b.querySelector('.mini-audio-pause')?.classList.add('hidden'); }
        });
        if (audio.paused) {
            audio.play();
            playIcon?.classList.add('hidden');
            pauseIcon?.classList.remove('hidden');
            audio.onended = function() { playIcon?.classList.remove('hidden'); pauseIcon?.classList.add('hidden'); };
        } else {
            audio.pause();
            playIcon?.classList.remove('hidden');
            pauseIcon?.classList.add('hidden');
        }
    };
}
if (!window.setMiniAudio) {
    window.setMiniAudio = function(id, path) {
        const el = document.getElementById(id);
        if (!el) return;
        const name = path.split('/').pop();
        el.querySelector('.mini-audio-name').textContent = name;
        el.querySelector('.mini-audio-name').title = name;
        el.querySelector('.mini-audio-el').src = '/audio-stream/' + path;
        el.querySelector('.mini-audio-play')?.classList.remove('hidden');
        el.querySelector('.mini-audio-pause')?.classList.add('hidden');
        el.classList.remove('hidden');
    };
}
if (!window.resetMiniAudio) {
    window.resetMiniAudio = function(id) {
        const el = document.getElementById(id);
        if (!el) return;
        const audio = el.querySelector('.mini-audio-el');
        if (audio) { audio.pause(); audio.src = ''; }
        el.querySelector('.mini-audio-play')?.classList.remove('hidden');
        el.querySelector('.mini-audio-pause')?.classList.add('hidden');
        el.classList.add('hidden');
    };
}

// Marker sementara di mode create belum punya baris map_location, jadi bentuk lokasi
// palsu dari keadaan form saat ini. Jenis Tempat ikut dibaca supaya pilihan admin
// langsung terlihat di peta sebelum apa pun disimpan.
function draftLoc(category, type = null) {
    const placeTypeSelect = document.querySelector('#form-cultural select[name="place_type"]');

    return {
        category: category,
        locationable: {
            type: type,
            place_type: category === 'cultural' && placeTypeSelect ? (placeTypeSelect.value || null) : null
        }
    };
}

// ==========================================
// CREATE / ADD NEW LOCATION LOGIC
// ==========================================
function handleMapClick(lat, lng) {
    if (currentMode === 'add-point') {
        const owner = addPointOwner;
        currentMode = 'edit';
        addPointOwner = null;
        if (!owner) return;

        fetch('{{ route('admin.map-manager.points.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ owner_type: owner.type, owner_id: owner.id, latitude: lat, longitude: lng })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                // Reload so the new point (and its full owner details) render consistently.
                // Drops any ?place= so the page doesn't re-arm add-point mode.
                window.location.href = '{{ route('admin.map-manager') }}';
            } else {
                Swal.fire({ title: 'Gagal', text: 'Titik baru gagal disimpan.', icon: 'error' });
            }
        }).catch(() => {
            Swal.fire({ title: 'Gagal', text: 'Terjadi kesalahan jaringan saat menyimpan titik baru.', icon: 'error' });
        });
        return;
    }

    if (currentMode === 'edit') {
        // In edit mode, clicking the map moves the selected marker's position
        if (activeMarker) {
            activeMarker.setLatLng([lat, lng]);
            updateCoordinateInputs(lat, lng);
            markUnsaved();
        }
        return;
    }

    checkUnsavedChanges(() => {
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
                icon: getSelectedMarkerIcon(draftLoc('cultural')), // default
                draggable: true
            }).addTo(map);

            tempMarker.on('dragend', function (e) {
                const pos = tempMarker.getLatLng();
                updateCoordinateInputs(pos.lat, pos.lng);
                markUnsaved();
            });
        }

        // Reset and switch to default (cultural) form
        resetForms();

        updateCoordinateInputs(lat, lng);

        document.querySelectorAll('input[name="map_location_type"]').forEach(input => {
            input.checked = false;
            input.disabled = false;
        });

        switchForm('');
        setTimeout(attachChangeListeners, 100);
    });
}

function updateCoordinateInputs(lat, lng) {
    const fixedLat = parseFloat(lat).toFixed(8);
    const fixedLng = parseFloat(lng).toFixed(8);

    document.querySelectorAll('input[name="latitude"]').forEach(input => input.value = fixedLat);
    document.querySelectorAll('input[name="longitude"]').forEach(input => input.value = fixedLng);
    const selector = document.getElementById('selector-container');
    selector.dataset.latitude = fixedLat;
    selector.dataset.longitude = fixedLng;
}

function switchForm(type) {
    if (type === 'house') {
        const selector = document.getElementById('selector-container');
        const url = new URL(selector.dataset.houseCreateUrl);
        url.searchParams.set('latitude', selector.dataset.latitude);
        url.searchParams.set('longitude', selector.dataset.longitude);
        window.location.assign(url.href);
        return;
    }
    // Hide all forms
    const formCultural = document.getElementById('form-cultural');
    const formUmkm = document.getElementById('form-umkm');
    const formFacility = document.getElementById('form-facility');
    
    if (formCultural) formCultural.classList.add('hidden');
    if (formUmkm) formUmkm.classList.add('hidden');
    if (formFacility) formFacility.classList.add('hidden');

    // Hide UMKM admin manage container by default
    const manageContainer = document.getElementById('umkm-admin-manage-container');
    if (manageContainer) {
        manageContainer.classList.add('hidden');
    }

    // Show active form
    const formId = `form-${type}`;
    const targetForm = document.getElementById(formId);
    if (targetForm) targetForm.classList.remove('hidden');

    // Update sticky submit button to target active form
    const submitBtn = document.getElementById('btn-global-submit');
    if (submitBtn) {
        submitBtn.setAttribute('form', formId);
        submitBtn.hidden = !targetForm;
        submitBtn.style.display = targetForm ? '' : 'none';
    }
    document.getElementById('umkm-profile-type').classList.toggle('hidden', currentMode === 'create');
    const choosing = currentMode === 'create' && !type;
    const show = (id, visible) => {
        const element = document.getElementById(id);
        element.hidden = !visible;
        element.style.display = visible ? '' : 'none';
    };
    show('selector-container', currentMode === 'create');
    show('location-type-choices', choosing);
    show('location-type-selected', currentMode === 'create' && !!type);
    show('map-editor-actions', !choosing);
    document.getElementById('location-type-label').textContent = {
        umkm: 'Usaha', cultural: 'Objek budaya', facility: 'Fasilitas umum'
    }[type] || '';
    document.querySelectorAll('input[name="map_location_type"]').forEach(input => {
        input.checked = input.value === type;
        input.disabled = currentMode === 'edit';
    });
    if (type) document.getElementById('location-type-error').hidden = true;
    if (currentMode === 'create' && type === 'umkm') setUmkmProfileType('business', undefined, false);


    // Update temp marker color to match type
    if (tempMarker) {
        if (type === 'facility') {
            const selectType = document.querySelector('#form-facility select[name="type"]');
            const subType = selectType ? selectType.value : 'toilet';
            tempMarker.setIcon(getSelectedMarkerIcon(draftLoc('facility', subType)));
        } else {
            tempMarker.setIcon(getSelectedMarkerIcon(draftLoc(type)));
        }
    }
}

// Attach listener to facility type select to update icon color
const facilityTypeSelect = document.querySelector('#form-facility select[name="type"]');
if (facilityTypeSelect) {
    facilityTypeSelect.addEventListener('change', function () {
        if (tempMarker && currentMode === 'create') {
            tempMarker.setIcon(getSelectedMarkerIcon(draftLoc('facility', this.value)));
        }
        if (activeMarker && currentMode === 'edit') {
            activeMarker.setIcon(getSelectedMarkerIcon(draftLoc('facility', this.value)));
        }
    });
}

// Preview langsung untuk Jenis Tempat: glyph marker berganti begitu pilihan diubah,
// tanpa menunggu simpan. Cerminan dari listener tipe fasilitas di atas.
const placeTypeSelect = document.querySelector('#form-cultural select[name="place_type"]');
if (placeTypeSelect) {
    placeTypeSelect.addEventListener('change', function () {
        if (tempMarker && currentMode === 'create') {
            tempMarker.setIcon(getSelectedMarkerIcon(draftLoc('cultural')));
        }
        if (activeMarker && currentMode === 'edit' && activeMarker.locationData.category === 'cultural') {
            // Tidak mengubah locationData: pilihan yang belum disimpan tidak boleh
            // menetap, jadi render ulang mengembalikannya ke nilai tersimpan.
            activeMarker.setIcon(window.gseMapPin('cultural', {
                highlight: true,
                placeType: this.value || null
            }));
        }
    });
}

// ==========================================
// MULTI-POINT: sibling popup, point delete, add-point mode
// ==========================================
function showPointChoicePopup(marker) {
    clearSiblingHighlight();
    highlightSiblingGroup(marker);

    const loc = marker.locationData;
    const details = loc.locationable;
    const locale = "{{ app()->getLocale() }}";
    const ownerName = escapeHtml(details
        ? (typeof details.name === 'object' ? (details.name[locale] || details.name.en || details.name.id || '') : details.name)
        : loc.name);

    window.__pointPopupEdit = function () {
        marker.closePopup();
        handleMarkerClick(marker);
    };
    window.__pointPopupDelete = function () {
        marker.closePopup();
        deletePointOnly(marker);
    };

    marker.bindPopup(`
        <div class="text-sm">
            <p class="font-bold mb-1">${ownerName}</p>
            <p class="text-xs text-gray-500 mb-2">Salah satu dari beberapa titik lokasi ini</p>
            <div class="flex gap-1">
                <button type="button" onclick="window.__pointPopupEdit()" class="rounded-lg bg-primary text-white text-xs px-2 py-1 font-semibold">Ubah Lokasi</button>
                <button type="button" onclick="window.__pointPopupDelete()" class="rounded-lg border border-red-200 text-red-600 text-xs px-2 py-1 font-semibold">Hapus</button>
            </div>
        </div>
    `).openPopup();
}

function deletePointOnly(marker) {
    const loc = marker.locationData;

    Swal.fire({
        title: 'Hapus Titik Ini?',
        text: 'Titik lain milik lokasi ini akan tetap ada.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (!result.isConfirmed) return;

        fetch(`/admin/map-manager/points/${loc.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (!data.success) {
                Swal.fire({ title: 'Gagal', text: 'Titik gagal dihapus.', icon: 'error' });
                return;
            }

            map.removeLayer(marker);
            markers = markers.filter(m => m !== marker);
            const idx = locations.findIndex(l => l.id === loc.id);
            if (idx !== -1) locations.splice(idx, 1);
            clearSiblingHighlight();
            if (activeMarker === marker) {
                activeMarker = null;
                cancelEditor();
            }
        }).catch(() => {
            Swal.fire({ title: 'Gagal', text: 'Terjadi kesalahan jaringan saat menghapus titik.', icon: 'error' });
        });
    });
}

function startAddPointMode() {
    if (!activeMarker) return;
    const loc = activeMarker.locationData;
    if (!loc.locationable || !loc.locationable.id) return;

    addPointOwner = {
        type: loc.category === 'cultural' ? 'cultural_object' : 'facility',
        id: loc.locationable.id
    };
    currentMode = 'add-point';

    Swal.fire({
        toast: true,
        position: 'top',
        icon: 'info',
        title: 'Klik peta untuk menaruh titik baru',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// ==========================================
// EDIT LOCATION LOGIC
// ==========================================
function handleMarkerClick(marker) {
    checkUnsavedChanges(() => {
        // Remove temp marker if it exists
        if (tempMarker) {
            map.removeLayer(tempMarker);
            tempMarker = null;
        }

        // Reset any previous active marker visuals
        resetSelectedMarkerVisuals();

        currentMode = 'edit';
        activeMarker = marker;
        highlightSiblingGroup(marker);

        const loc = marker.locationData;
        const details = loc.locationable;

        // Change marker icon to selected
        const type = details ? details.type : null;
        marker.setIcon(getSelectedMarkerIcon(loc));

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

        // Show delete option
        document.getElementById('delete-container').classList.remove('hidden');

        resetForms();
        updateCoordinateInputs(loc.latitude, loc.longitude);

        if (loc.category === 'cultural') {
            switchForm('cultural');
            const form = document.getElementById('form-cultural');
            form.action = `/admin/cultural-objects/${details.id}`;
            document.getElementById('method-cultural').innerHTML = '@method("PUT")';

            form.querySelector('input[name="name[en]"]').value = details.name?.en || details.name || '';
            form.querySelector('input[name="name[id]"]').value = details.name?.id || details.name || '';
            form.querySelector('input[name="short_description[en]"]').value = details.short_description?.en || details.short_description || '';
            form.querySelector('input[name="short_description[id]"]').value = details.short_description?.id || details.short_description || '';
            form.querySelector('select[name="category"]').value = details.category;
            // resetForms() memanggil form.reset() tepat sebelum ini, jadi dua kontrol
            // berikut kembali kosong. Tanpa diisi ulang, membuka objek yang sudah punya
            // jenis tempat akan menampilkannya kosong — dan karena select tetap ikut
            // terkirim saat simpan, nilai yang tersimpan malah terhapus jadi null.
            form.querySelector('select[name="place_type"]').value = details.place_type || '';
            form.querySelector('input[type="checkbox"][name="is_detail"]').checked = !!details.is_detail;
            setTiptapContent(form.querySelector('textarea[name="description[en]"]'), details.description?.en || details.description || '');
            setTiptapContent(form.querySelector('textarea[name="description[id]"]'), details.description?.id || details.description || '');
            // Select active model via custom event to Alpine component
            window.dispatchEvent(new CustomEvent('ar-model-select', {
                detail: { modelId: details.ar_model ? String(details.ar_model.id) : '' }
            }));

            // Populate AR model name/description fields (for inline editing)
            const arModelData = details.ar_model || null;
            if (arModelData) {
                const nameEn = typeof arModelData.name === 'object' ? (arModelData.name.en || '') : arModelData.name || '';
                const nameId = typeof arModelData.name === 'object' ? (arModelData.name.id || '') : arModelData.name || '';
                const descEn = typeof arModelData.description === 'object' ? (arModelData.description.en || '') : arModelData.description || '';
                const descId = typeof arModelData.description === 'object' ? (arModelData.description.id || '') : arModelData.description || '';
                const nameEnInput = document.querySelector('input[name="new_model_name[en]"]');
                const nameIdInput = document.querySelector('input[name="new_model_name[id]"]');
                const descEnInput = document.querySelector('textarea[name="new_model_description[en]"]');
                const descIdInput = document.querySelector('textarea[name="new_model_description[id]"]');
                if (nameEnInput) nameEnInput.value = nameEn;
                if (nameIdInput) nameIdInput.value = nameId;
                if (descEnInput) setTiptapContent(descEnInput, descEn);
                if (descIdInput) setTiptapContent(descIdInput, descId);
            }

            const arModel = details.ar_model || null;
            document.getElementById('map-cultural-historical-images').closest('[data-image-upload]')
                .setExistingImages((details.historical_images || []).map(img => `${storageUrl}/${img}`));

            // Populate existing audio narration indicators
            ['en', 'id'].forEach(function(locale) {
                var path = details.audio_narration_paths && details.audio_narration_paths[locale];
                if (path) {
                    window.setMiniAudio('current-audio-' + locale, path);
                } else {
                    window.resetMiniAudio('current-audio-' + locale);
                }
            });

            form.querySelector('input[type="checkbox"][name="is_accessible"]').checked = loc.is_accessible;
            const culturalAccEn = (typeof loc.accessibility_notes === 'object') ? (loc.accessibility_notes?.en || '') : (loc.accessibility_notes || '');
            const culturalAccId = (typeof loc.accessibility_notes === 'object') ? (loc.accessibility_notes?.id || '') : (loc.accessibility_notes || '');
            form.querySelector('textarea[name="accessibility_notes[en]"]').value = culturalAccEn;
            form.querySelector('textarea[name="accessibility_notes[id]"]').value = culturalAccId;
            updateAccessibilityNotesVisibility(form);

            // Setup Delete Action
            document.getElementById('form-delete').action = `/admin/cultural-objects/${details.id}`;

        } else if (loc.category === 'umkm') {
            switchForm('umkm');
            const form = document.getElementById('form-umkm');
            form.action = `/admin/umkm/profiles/${details.id}`;
            document.getElementById('method-umkm').innerHTML = '@method("PUT")';

            form.querySelector('input[name="business_name[en]"]').value = details.business_name?.en || details.business_name || '';
            form.querySelector('input[name="business_name[id]"]').value = details.business_name?.id || details.business_name || '';
            setUmkmProfileType(details.profile_type || 'business', details.traditional_house_id, details.profile_type === 'house');
            form.querySelector('input[name="owner_name"]').value = details.owner_name;
            document.getElementById('umkm-owner-user-id').value = details.user_id || '';
            document.getElementById('umkm-owner-search').value = details.owner_name || '';
            setTiptapContent(form.querySelector('textarea[name="description[en]"]'), details.description?.en || details.description || '');
            setTiptapContent(form.querySelector('textarea[name="description[id]"]'), details.description?.id || details.description || '');
            form.querySelector('input[name="rating"]').value = details.rating || '5.0';
            form.querySelector('input[type="checkbox"][name="is_active"]').checked = details.is_active;
            form.querySelector('input[type="checkbox"][name="is_accessible"]').checked = loc.is_accessible;
            const umkmAccEn = (typeof loc.accessibility_notes === 'object') ? (loc.accessibility_notes?.en || '') : (loc.accessibility_notes || '');
            const umkmAccId = (typeof loc.accessibility_notes === 'object') ? (loc.accessibility_notes?.id || '') : (loc.accessibility_notes || '');
            form.querySelector('textarea[name="accessibility_notes[en]"]').value = umkmAccEn;
            form.querySelector('textarea[name="accessibility_notes[id]"]').value = umkmAccId;
            updateAccessibilityNotesVisibility(form);

            // Setup Delete Action
            document.getElementById('form-delete').action = `/admin/umkm/profiles/${details.id}`;

            // Setup Manage Action
            const manageContainer = document.getElementById('umkm-admin-manage-container');
            const manageBtn = document.getElementById('btn-umkm-admin-manage');
            if (manageContainer && manageBtn) {
                manageBtn.href = `/owner/dashboard?umkm_profile_id=${details.id}`;
                manageContainer.classList.remove('hidden');
            }

        } else if (loc.category === 'facility') {
            switchForm('facility');
            const form = document.getElementById('form-facility');
            form.action = `/admin/facilities/${details.id}`;
            document.getElementById('method-facility').innerHTML = '@method("PUT")';

            form.querySelector('input[name="name[en]"]').value = details.name?.en || details.name || '';
            form.querySelector('input[name="name[id]"]').value = details.name?.id || details.name || '';
            form.querySelector('select[name="type"]').value = details.type;
            setTiptapContent(form.querySelector('textarea[name="description[en]"]'), details.description?.en || details.description || '');
            setTiptapContent(form.querySelector('textarea[name="description[id]"]'), details.description?.id || details.description || '');
            form.querySelector('input[type="checkbox"][name="is_active"]').checked = details.is_active;
            form.querySelector('input[type="checkbox"][name="is_accessible"]').checked = loc.is_accessible;
            const facilityAccEn = (typeof loc.accessibility_notes === 'object') ? (loc.accessibility_notes?.en || '') : (loc.accessibility_notes || '');
            const facilityAccId = (typeof loc.accessibility_notes === 'object') ? (loc.accessibility_notes?.id || '') : (loc.accessibility_notes || '');
            form.querySelector('textarea[name="accessibility_notes[en]"]').value = facilityAccEn;
            form.querySelector('textarea[name="accessibility_notes[id]"]').value = facilityAccId;
            updateAccessibilityNotesVisibility(form);

            // Setup Delete Action
            document.getElementById('form-delete').action = `/admin/facilities/${details.id}`;
        }

        // "Tambah Titik" is only meaningful for existing cultural objects/facilities (not UMKM, not create-mode)
        const addPointContainer = document.getElementById('add-point-container');
        if (addPointContainer) {
            const canAddPoint = details && details.id && (loc.category === 'cultural' || loc.category === 'facility');
            addPointContainer.classList.toggle('hidden', !canAddPoint);
        }

        // Center map to marker
        map.panTo(marker.getLatLng());

        setTimeout(attachChangeListeners, 100);
    });
}

// ==========================================
// UTILITIES / RESETS
// ==========================================
function cancelEditor() {
    checkUnsavedChanges(() => {
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
    });
}

function resetSelectedMarkerVisuals() {
    if (activeMarker) {
        const loc = activeMarker.locationData;
        const details = loc.locationable;
        const type = details ? details.type : null;

        // Revert icon to normal
        activeMarker.setIcon(getMarkerIcon(loc));

        // Disable dragging & listeners
        activeMarker.dragging.disable();
        activeMarker.off('dragend');

        activeMarker = null;
    }

    clearSiblingHighlight();
    addPointOwner = null;
    const addPointContainer = document.getElementById('add-point-container');
    if (addPointContainer) addPointContainer.classList.add('hidden');
}

function resetForms() {
    // Reset inputs and methods in forms
    const culturalForm = document.getElementById('form-cultural');
    if (culturalForm) {
        culturalForm.reset();
        if (typeof window.clearAllTiptapEditors === 'function') {
            window.clearAllTiptapEditors(culturalForm);
        }
        culturalForm.action = "{{ route('admin.cultural-objects.store') }}";
        document.getElementById('method-cultural').innerHTML = '';

        window.dispatchEvent(new CustomEvent('ar-model-reset'));

        document.getElementById('map-cultural-historical-images').closest('[data-image-upload]').setExistingImages([]);
        ['current-audio-en', 'current-audio-id'].forEach(window.resetMiniAudio);

        updateAccessibilityNotesVisibility(culturalForm);
    }

    const umkmForm = document.getElementById('form-umkm');
    if (umkmForm) {
        umkmForm.reset();
        setUmkmProfileType('business', null, false);
        if (typeof window.clearAllTiptapEditors === 'function') {
            window.clearAllTiptapEditors(umkmForm);
        }
        umkmForm.action = "{{ route('admin.umkm.profile.store') }}";
        document.getElementById('method-umkm').innerHTML = '';
        document.getElementById('umkm-owner-user-id').value = '';
        document.getElementById('umkm-owner-search').value = '';
        const ownerNameEl = document.getElementById('umkm-owner-name');
        if (ownerNameEl) ownerNameEl.value = '';
        updateAccessibilityNotesVisibility(umkmForm);
    }

    const facilityForm = document.getElementById('form-facility');
    if (facilityForm) {
        facilityForm.reset();
        if (typeof window.clearAllTiptapEditors === 'function') {
            window.clearAllTiptapEditors(facilityForm);
        }
        facilityForm.action = "{{ route('admin.facilities.store') }}";
        document.getElementById('method-facility').innerHTML = '';
        updateAccessibilityNotesVisibility(facilityForm);
    }
}

function updateAccessibilityNotesVisibility(formElement) {
    if (!formElement) return;
    const checkbox = formElement.querySelector('input[type="checkbox"][name="is_accessible"]');
    const container = formElement.querySelector('.accessibility-notes-container');
    if (!checkbox || !container) return;

    container.style.display = checkbox.checked ? 'block' : 'none';
}

// Arrive from the cultural-objects list with ?place=cultural_object:12 — arms add-point mode for an
// object that has no marker yet, which is otherwise unreachable (no marker = nothing to click).
(function () {
    const place = new URLSearchParams(window.location.search).get('place');
    if (!place) return;

    const [type, id] = place.split(':');
    if (!['cultural_object', 'facility'].includes(type) || !/^\d+$/.test(id || '')) return;

    addPointOwner = { type, id };
    currentMode = 'add-point';

    Swal.fire({
        toast: true,
        position: 'top',
        icon: 'info',
        title: 'Klik peta untuk menaruh titik lokasinya',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });
})();

// Initialize accessibility notes toggle event listeners
document.addEventListener('DOMContentLoaded', () => {
    ['form-cultural', 'form-umkm', 'form-facility'].forEach(formId => {
        const form = document.getElementById(formId);
        if (!form) return;

        const checkbox = form.querySelector('input[type="checkbox"][name="is_accessible"]');
        if (checkbox) {
            checkbox.addEventListener('change', () => {
                updateAccessibilityNotesVisibility(form);
            });
            // Initial call to set correct state
            updateAccessibilityNotesVisibility(form);
        }
    });
});

function setUmkmProfileType(type, houseId, locked = false) {
    const form = document.getElementById('form-umkm');
    if (!form) return;
    form.querySelectorAll('input[name="profile_type"]').forEach(input => {
        input.checked = input.value === type;
        input.disabled = locked && input.value !== 'house';
    });
    const houseSelect = document.getElementById('umkm-house-id');
    if (houseId !== undefined) houseSelect.value = houseId || '';
    houseSelect.required = type === 'house';
    const nameGroup = document.getElementById('umkm-manual-name');
    nameGroup.hidden = type === 'house';
    nameGroup.querySelectorAll('input').forEach(input => {
        input.disabled = type === 'house';
        input.required = type !== 'house';
    });
    const houseLink = document.getElementById('umkm-house-edit-link');
    houseLink.hidden = !houseSelect.value;
    houseLink.style.display = houseSelect.value ? '' : 'none';
    houseLink.href = houseSelect.value ? `/admin/houses/${encodeURIComponent(houseSelect.value)}/edit` : '#';
}
document.querySelectorAll('#form-umkm input[name="profile_type"]').forEach(input => {
    input.addEventListener('change', () => setUmkmProfileType(input.value, undefined));
});
document.getElementById('umkm-house-id')?.addEventListener('change', () => {
    const type = document.querySelector('#form-umkm input[name="profile_type"]:checked')?.value || 'business';
    setUmkmProfileType(type, undefined, document.querySelector('#form-umkm input[value="business"]').disabled);
});
document.getElementById('location-type-continue')?.addEventListener('click', () => {
    const selected = document.querySelector('input[name="map_location_type"]:checked');
    if (!selected) {
        document.getElementById('location-type-error').hidden = false;
        document.querySelector('input[name="map_location_type"]').focus();
        return;
    }
    switchForm(selected.value);
    if (selected.value !== 'house') {
        const form = document.getElementById(`form-${selected.value}`);
        const name = selected.value === 'umkm' ? 'business_name[id]' : 'name[id]';
        const input = form?.querySelector(`input[name="${name}"]`) || form?.querySelector('select:not([disabled]), input[type="text"]');
        input?.focus();
    }
});
document.querySelectorAll('input[name="map_location_type"]').forEach(input => {
    input.addEventListener('change', () => document.getElementById('location-type-error').hidden = true);
});
document.getElementById('location-type-change')?.addEventListener('click', () => {
    checkUnsavedChanges(() => {
        const selector = document.getElementById('selector-container');
        resetForms();
        updateCoordinateInputs(selector.dataset.latitude, selector.dataset.longitude);
        resetUnsaved();
        switchForm('');
        document.querySelector('input[name="map_location_type"]').focus();
    });
});
document.getElementById('location-type-cancel')?.addEventListener('click', cancelEditor);
</script>
