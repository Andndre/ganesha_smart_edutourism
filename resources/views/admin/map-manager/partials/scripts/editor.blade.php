<script>
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

            form.querySelector('input[name="name"]').value = details.name;
            form.querySelector('input[name="short_description"]').value = details.short_description || '';
            form.querySelector('select[name="category"]').value = details.category;
            form.querySelector('textarea[name="description"]').value = details.description || '';
            if (window.setCulturalEditorContent) {
                window.setCulturalEditorContent(details.description || '');
            }
            form.querySelector('input[name="ar_marker_id"]').value = details.ar_marker_id || '';
            setTimeout(() => {
                if (typeof window.generateARMarker === 'function') {
                    window.generateARMarker();
                }
            }, 100);

            // File previews
            document.getElementById('current-model-3d').innerHTML = details.model_3d_path
                ? `File saat ini: <a href="${storageUrl}/${details.model_3d_path}" target="_blank" class="text-primary hover:underline font-semibold">${details.model_3d_path.split('/').pop()}</a>`
                : 'Belum ada model 3D';

            document.getElementById('current-model-3d-usdz').innerHTML = details.model_3d_usdz_path
                ? `File saat ini: <a href="${storageUrl}/${details.model_3d_usdz_path}" target="_blank" class="text-primary hover:underline font-semibold">${details.model_3d_usdz_path.split('/').pop()}</a>`
                : 'Belum ada model 3D iOS (.usdz)';

            const modelPreviewContainer = document.getElementById('model-3d-preview-container');
            const modelPreview = document.getElementById('model-3d-preview');
            if (modelPreview && modelPreviewContainer) {
                if (details.model_3d_path) {
                    modelPreview.src = `${storageUrl}/${details.model_3d_path}`;
                    modelPreviewContainer.style.display = 'flex';
                } else {
                    modelPreview.src = '';
                    modelPreviewContainer.style.display = 'none';
                }
            }

            document.getElementById('current-audio').innerHTML = details.audio_narration_path
                ? `File saat ini: <a href="${storageUrl}/${details.audio_narration_path}" target="_blank" class="text-primary hover:underline font-semibold">${details.audio_narration_path.split('/').pop()}</a>`
                : 'Belum ada audio narasi';

            const audioPreviewContainer = document.getElementById('audio-preview-container');
            const audioPreview = document.getElementById('audio-preview');
            if (audioPreview && audioPreviewContainer) {
                if (details.audio_narration_path) {
                    audioPreview.src = `{{ route('audio.stream', ['path' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', details.audio_narration_path);
                    audioPreviewContainer.style.display = 'flex';
                } else {
                    audioPreview.src = '';
                    audioPreviewContainer.style.display = 'none';
                }
            }

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

            // Populate Quizzes
            const quizzesList = document.getElementById('quizzes-list');
            if (quizzesList) quizzesList.innerHTML = '';
            
            if (details.quizzes && details.quizzes.length > 0) {
                form.querySelector('input[name="has_quiz"]').checked = true;
                const manageBtn = document.getElementById('btn-manage-quizzes');
                if (manageBtn) {
                    manageBtn.classList.remove('hidden');
                    manageBtn.classList.add('flex');
                }
                details.quizzes.forEach(q => addQuizField(q));
            } else {
                form.querySelector('input[name="has_quiz"]').checked = false;
                const manageBtn = document.getElementById('btn-manage-quizzes');
                if (manageBtn) {
                    manageBtn.classList.add('hidden');
                    manageBtn.classList.remove('flex');
                }
            }

            // Populate Stories
            ['history', 'philosophy', 'value'].forEach(cat => {
                const list = document.getElementById(`stories-list-${cat}`);
                if (list) list.innerHTML = '';
            });
            if (typeof switchStoryTab === 'function') {
                switchStoryTab('history');
            }
            
            if (details.stories && details.stories.length > 0) {
                form.querySelector('input[name="has_story"]').checked = true;
                const manageStoriesBtn = document.getElementById('btn-manage-stories');
                if (manageStoriesBtn) {
                    manageStoriesBtn.classList.remove('hidden');
                    manageStoriesBtn.classList.add('flex');
                }
                details.stories.forEach(s => addStoryField(s));
            } else {
                form.querySelector('input[name="has_story"]').checked = false;
                const manageStoriesBtn = document.getElementById('btn-manage-stories');
                if (manageStoriesBtn) {
                    manageStoriesBtn.classList.add('hidden');
                    manageStoriesBtn.classList.remove('flex');
                }
            }

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
        culturalForm.action = "{{ route('admin.cultural-objects.store') }}";
        if (window.setCulturalEditorContent) {
            window.setCulturalEditorContent('');
        }
        document.getElementById('method-cultural').innerHTML = '';
        document.getElementById('current-model-3d').innerHTML = '';
        const usdzEl = document.getElementById('current-model-3d-usdz');
        if (usdzEl) usdzEl.innerHTML = '';
        document.getElementById('current-audio').innerHTML = '';
        document.getElementById('current-images').innerHTML = '';

        const pattInput = document.getElementById('ar_marker_patt_content');
        if (pattInput) {
            pattInput.value = '';
        }
        const downloadContainer = document.getElementById('ar-download-container');
        if (downloadContainer) {
            downloadContainer.style.display = 'none';
        }

        const modelPreviewContainer = document.getElementById('model-3d-preview-container');
        const modelPreview = document.getElementById('model-3d-preview');
        if (modelPreview && modelPreviewContainer) {
            modelPreview.src = '';
            modelPreviewContainer.style.display = 'none';
        }

        const audioPreviewContainer = document.getElementById('audio-preview-container');
        const audioPreview = document.getElementById('audio-preview');
        if (audioPreview && audioPreviewContainer) {
            audioPreview.src = '';
            audioPreviewContainer.style.display = 'none';
        }

        // Reset Quizzes
        if(culturalForm.querySelector('input[name="has_quiz"]')) {
            culturalForm.querySelector('input[name="has_quiz"]').checked = false;
            const manageBtn = document.getElementById('btn-manage-quizzes');
            if (manageBtn) {
                manageBtn.classList.add('hidden');
                manageBtn.classList.remove('flex');
            }
            const quizzesList = document.getElementById('quizzes-list');
            if (quizzesList) quizzesList.innerHTML = '';
        }

        // Reset Stories
        if(culturalForm.querySelector('input[name="has_story"]')) {
            culturalForm.querySelector('input[name="has_story"]').checked = false;
            const manageBtn = document.getElementById('btn-manage-stories');
            if (manageBtn) {
                manageBtn.classList.add('hidden');
                manageBtn.classList.remove('flex');
            }
            ['history', 'philosophy', 'value'].forEach(cat => {
                const storiesList = document.getElementById(`stories-list-${cat}`);
                if (storiesList) storiesList.innerHTML = '';
            });
            if (typeof switchStoryTab === 'function') {
                switchStoryTab('history');
            }
        }
    }

    const umkmForm = document.getElementById('form-umkm');
    if (umkmForm) {
        umkmForm.reset();
        umkmForm.action = "{{ route('admin.umkm.profile.store') }}";
        document.getElementById('method-umkm').innerHTML = '';
        document.getElementById('umkm-owner-user-id').value = '';
        document.getElementById('umkm-owner-search').value = '';
        const ownerNameEl = document.getElementById('umkm-owner-name');
        if (ownerNameEl) ownerNameEl.value = '';
    }

    const facilityForm = document.getElementById('form-facility');
    if (facilityForm) {
        facilityForm.reset();
        facilityForm.action = "{{ route('admin.facilities.store') }}";
        document.getElementById('method-facility').innerHTML = '';
    }
}
</script>
