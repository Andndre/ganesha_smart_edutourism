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

// ==========================================
// CREATE / ADD NEW LOCATION LOGIC
// ==========================================
function handleMapClick(lat, lng) {
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
                icon: getSelectedMarkerIcon('cultural'), // default
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

        const typeSelect = document.getElementById('type-selector');
        if (typeSelect) {
            typeSelect.disabled = false;
            typeSelect.value = 'cultural';
        }

        switchForm('cultural');
        setTimeout(attachChangeListeners, 100);
    });
}

function updateCoordinateInputs(lat, lng) {
    const fixedLat = parseFloat(lat).toFixed(8);
    const fixedLng = parseFloat(lng).toFixed(8);

    document.querySelectorAll('input[name="latitude"]').forEach(input => input.value = fixedLat);
    document.querySelectorAll('input[name="longitude"]').forEach(input => input.value = fixedLng);
}

function switchForm(type) {
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
    }

    // Update temp marker color to match type
    if (tempMarker) {
        if (type === 'facility') {
            const selectType = document.querySelector('#form-facility select[name="type"]');
            const subType = selectType ? selectType.value : 'toilet';
            tempMarker.setIcon(getSelectedMarkerIcon('facility', subType));
        } else {
            tempMarker.setIcon(getSelectedMarkerIcon(type));
        }
    }
}

// Attach listener to facility type select to update icon color
const facilityTypeSelect = document.querySelector('#form-facility select[name="type"]');
if (facilityTypeSelect) {
    facilityTypeSelect.addEventListener('change', function () {
        if (tempMarker && currentMode === 'create') {
            tempMarker.setIcon(getSelectedMarkerIcon('facility', this.value));
        }
        if (activeMarker && currentMode === 'edit') {
            activeMarker.setIcon(getSelectedMarkerIcon('facility', this.value));
        }
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
        if (typeSelect) {
            typeSelect.value = loc.category;
            typeSelect.disabled = true;
        }

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
            setTiptapContent(form.querySelector('textarea[name="description[en]"]'), details.description?.en || details.description || '');
            setTiptapContent(form.querySelector('textarea[name="description[id]"]'), details.description?.id || details.description || '');
            // Select active model via custom event to Alpine component
            window.dispatchEvent(new CustomEvent('ar-model-select', {
                detail: { modelId: loc.ar_model ? String(loc.ar_model.id) : '' }
            }));

            // Populate AR model name/description fields (for inline editing)
            const arModelData = loc.ar_model || null;
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

            const arModel = loc.ar_model || null;
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
    if (culturalForm) {
        culturalForm.reset();
        if (typeof window.clearAllTiptapEditors === 'function') {
            window.clearAllTiptapEditors(culturalForm);
        }
        culturalForm.action = "{{ route('admin.cultural-objects.store') }}";
        document.getElementById('method-cultural').innerHTML = '';

        window.dispatchEvent(new CustomEvent('ar-model-reset'));

        document.getElementById('current-images').innerHTML = '';
        ['current-audio-en', 'current-audio-id'].forEach(window.resetMiniAudio);

        updateAccessibilityNotesVisibility(culturalForm);
    }

    const umkmForm = document.getElementById('form-umkm');
    if (umkmForm) {
        umkmForm.reset();
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
</script>
