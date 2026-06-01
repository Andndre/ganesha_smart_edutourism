<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
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
    let hasUnsavedChanges = false;

    function markUnsaved() {
        hasUnsavedChanges = true;
    }

    function resetUnsaved() {
        hasUnsavedChanges = false;
    }

    function checkUnsavedChanges(callback) {
        if (hasUnsavedChanges) {
            Swal.fire({
                title: 'Perubahan Belum Disimpan',
                text: 'Apakah Anda yakin ingin membuang perubahan pada form ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Buang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    resetUnsaved();
                    callback();
                }
            });
        } else {
            callback();
        }
    }

    function attachChangeListeners() {
        const editorPanel = document.getElementById('panel-editor');
        const inputs = editorPanel.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.removeEventListener('input', markUnsaved);
            input.removeEventListener('change', markUnsaved);
            input.addEventListener('input', markUnsaved);
            input.addEventListener('change', markUnsaved);
        });
    }

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

        // Audio preview on file selection
        const audioInput = document.querySelector('input[name="audio_narration_file"]');
        const audioPreview = document.getElementById('audio-preview');
        const audioPreviewContainer = document.getElementById('audio-preview-container');
        if (audioInput && audioPreview && audioPreviewContainer) {
            audioInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    audioPreview.src = URL.createObjectURL(file);
                    audioPreviewContainer.style.display = 'flex';
                } else {
                    audioPreview.src = '';
                    audioPreviewContainer.style.display = 'none';
                }
            });
        }

        // 3D Model preview on file selection
        const modelInput = document.querySelector('input[name="model_3d_file"]');
        const modelPreview = document.getElementById('model-3d-preview');
        const modelPreviewContainer = document.getElementById('model-3d-preview-container');
        if (modelInput && modelPreview && modelPreviewContainer) {
            modelInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    modelPreview.src = URL.createObjectURL(file);
                    modelPreviewContainer.style.display = 'flex';
                } else {
                    modelPreview.src = '';
                    modelPreviewContainer.style.display = 'none';
                }
            });
        }
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
        typeSelect.disabled = false;
        typeSelect.value = 'cultural';

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
        document.getElementById('form-cultural').classList.add('hidden');
        document.getElementById('form-umkm').classList.add('hidden');
        document.getElementById('form-facility').classList.add('hidden');

        // Show active form
        const formId = `form-${type}`;
        document.getElementById(formId).classList.remove('hidden');

        // Update sticky submit button to target active form
        const submitBtn = document.getElementById('btn-global-submit');
        if (submitBtn) {
            submitBtn.setAttribute('form', formId);
        }

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
                document.getElementById('btn-manage-quizzes').classList.remove('hidden');
                document.getElementById('btn-manage-quizzes').classList.add('flex');
                details.quizzes.forEach(q => addQuizField(q));
            } else {
                form.querySelector('input[name="has_quiz"]').checked = false;
                document.getElementById('btn-manage-quizzes').classList.add('hidden');
                document.getElementById('btn-manage-quizzes').classList.remove('flex');
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
        culturalForm.reset();
        culturalForm.action = "{{ route('admin.cultural-objects.store') }}";
        if (window.setCulturalEditorContent) {
            window.setCulturalEditorContent('');
        }
        document.getElementById('method-cultural').innerHTML = '';
        document.getElementById('current-model-3d').innerHTML = '';
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
            document.getElementById('btn-manage-quizzes').classList.add('hidden');
            document.getElementById('btn-manage-quizzes').classList.remove('flex');
            document.getElementById('quizzes-list').innerHTML = '';
        }

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

    // Quiz Functions
    function toggleQuizzes(checkbox) {
        const btn = document.getElementById('btn-manage-quizzes');
        const list = document.getElementById('quizzes-list');
        if (checkbox.checked) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
            if (list.children.length === 0) {
                addQuizField();
            }
            openQuizModal();
        } else {
            btn.classList.add('hidden');
            btn.classList.remove('flex');
            // We intentionally do not clear list.innerHTML here
            // so if the user accidentally unchecks, the quizzes aren't lost.
        }
    }

    function openQuizModal() {
        const modal = document.getElementById('quizzes-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeQuizModal() {
        const modal = document.getElementById('quizzes-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function addQuizField(quiz = null) {
        const list = document.getElementById('quizzes-list');
        const index = list.children.length;
        
        const html = `
            <div class="quiz-item relative bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <button type="button" onclick="this.closest('.quiz-item').remove()" class="absolute top-2 right-2 p-1 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <div class="mb-3">
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Pertanyaan ${index + 1}</label>
                    <textarea name="quiz_question[]" rows="2" required placeholder="Contoh: Apa nama tempat ini?" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">${quiz ? quiz.question : ''}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Opsi A</label>
                        <input type="text" name="quiz_option_a[]" required value="${quiz ? quiz.option_a : ''}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Opsi B</label>
                        <input type="text" name="quiz_option_b[]" required value="${quiz ? quiz.option_b : ''}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Opsi C</label>
                        <input type="text" name="quiz_option_c[]" required value="${quiz ? quiz.option_c : ''}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Opsi D</label>
                        <input type="text" name="quiz_option_d[]" required value="${quiz ? quiz.option_d : ''}" class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-primary focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Jawaban Benar</label>
                    <select name="quiz_correct_option[]" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
                        <option value="A" ${quiz && quiz.correct_option === 'A' ? 'selected' : ''}>Opsi A</option>
                        <option value="B" ${quiz && quiz.correct_option === 'B' ? 'selected' : ''}>Opsi B</option>
                        <option value="C" ${quiz && quiz.correct_option === 'C' ? 'selected' : ''}>Opsi C</option>
                        <option value="D" ${quiz && quiz.correct_option === 'D' ? 'selected' : ''}>Opsi D</option>
                    </select>
                </div>
            </div>
        `;
        list.insertAdjacentHTML('beforeend', html);
    }

    // ==========================================
    // AUTOMATED AR PATTERN & QR MARKER GENERATOR
    // ==========================================
    window.generateARMarker = function() {
        const markerInput = document.getElementById('ar_marker_id');
        const downloadContainer = document.getElementById('ar-download-container');
        const pattInput = document.getElementById('ar_marker_patt_content');
        
        if (!markerInput) return;
        
        const markerId = markerInput.value.trim();
        if (!markerId) {
            if (downloadContainer) downloadContainer.style.display = 'none';
            if (pattInput) pattInput.value = '';
            return;
        }
        
        if (downloadContainer) downloadContainer.style.display = 'block';
        
        try {
            // Retrieve dynamic slug from activeMarker details
            let slug = '';
            if (activeMarker && activeMarker.locationData && activeMarker.locationData.locationable) {
                slug = activeMarker.locationData.locationable.slug || '';
            }
            
            // If slug is still empty (creating new), slugify the name input dynamically
            if (!slug) {
                const nameInput = document.querySelector('#form-cultural input[name="name"]');
                if (nameInput && nameInput.value) {
                    slug = nameInput.value.toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/(^-|-$)+/g, '');
                }
            }
            
            // Build dynamic tourist destination URL or fallback to scan query
            const qrValue = slug 
                ? `${window.location.origin}/cultural/${slug}` 
                : `${window.location.origin}/explore?marker=${encodeURIComponent(markerId)}`;
            
            // Render QR using QRious
            const qr = new QRious({
                value: qrValue,
                size: 300,
                level: 'H'
            });
            
            // Create high-resolution 500x500 AR.js canvas
            const markerCanvas = document.createElement('canvas');
            markerCanvas.width = 500;
            markerCanvas.height = 500;
            const ctx = markerCanvas.getContext('2d');
            
            // Solid Black border (essential for AR.js tracking)
            ctx.fillStyle = '#000000';
            ctx.fillRect(0, 0, 500, 500);
            
            // White background inside the border
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(100, 100, 300, 300);
            
            // Centered QR Code
            ctx.drawImage(qr.canvas, 100, 100, 300, 300);
            
            window.arMarkerCanvas = markerCanvas;
            
            // Set initial fallback pattern (plain QR)
            const fallbackPattText = generatePattText(markerCanvas, 100, 300);
            if (pattInput) {
                pattInput.value = fallbackPattText;
            }
            
            // Load and render brand logo in center
            const logo = new Image();
            logo.onload = function() {
                // White background overlay for logo to prevent bleeding with QR modules
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(212, 212, 76, 76);
                
                // Render logo
                ctx.drawImage(logo, 217, 217, 66, 66);
                
                window.arMarkerCanvas = markerCanvas;
                
                // Regenerate pattern including the logo
                const pattText = generatePattText(markerCanvas, 100, 300);
                if (pattInput) {
                    pattInput.value = pattText;
                }
            };
            logo.src = '/icons/logo-color-notext.png';
            
        } catch (e) {
            console.error('AR Marker generation failed:', e);
        }
    };

    window.downloadARMarker = function() {
        const markerInput = document.getElementById('ar_marker_id');
        if (!markerInput || !window.arMarkerCanvas) return;
        
        const markerId = markerInput.value.trim();
        
        // Download PNG marker
        const pngUrl = window.arMarkerCanvas.toDataURL('image/png');
        const pngLink = document.createElement('a');
        pngLink.href = pngUrl;
        pngLink.download = `${markerId}.png`;
        document.body.appendChild(pngLink);
        pngLink.click();
        document.body.removeChild(pngLink);
    };

    function generatePattText(canvas, borderWidth, patternSize) {
        const ctx = canvas.getContext('2d');
        const gridSize = 16;
        const cellW = patternSize / gridSize;
        const cellH = patternSize / gridSize;
        
        const grid = [];
        for (let r = 0; r < gridSize; r++) {
            grid[r] = [];
            for (let c = 0; c < gridSize; c++) {
                const startX = borderWidth + c * cellW;
                const startY = borderWidth + r * cellH;
                
                const imgData = ctx.getImageData(startX, startY, cellW, cellH);
                const data = imgData.data;
                let sumR = 0, sumG = 0, sumB = 0;
                const count = data.length / 4;
                
                for (let i = 0; i < data.length; i += 4) {
                    sumR += data[i];
                    sumG += data[i + 1];
                    sumB += data[i + 2];
                }
                
                const normR = (sumR / count / 255).toFixed(3);
                const normG = (sumG / count / 255).toFixed(3);
                const normB = (sumB / count / 255).toFixed(3);
                
                grid[r][c] = `${normR} ${normG} ${normB}`;
            }
        }
        
        const rotations = [];
        
        function rotate90(arr) {
            const n = arr.length;
            const rotated = Array.from({ length: n }, () => []);
            for (let r = 0; r < n; r++) {
                for (let c = 0; c < n; c++) {
                    rotated[c][n - 1 - r] = arr[r][c];
                }
            }
            return rotated;
        }
        
        let currentGrid = grid;
        for (let i = 0; i < 4; i++) {
            const blockLines = [];
            for (let r = 0; r < gridSize; r++) {
                blockLines.push(currentGrid[r].join(' '));
            }
            rotations.push(blockLines.join('\n'));
            currentGrid = rotate90(currentGrid);
        }
        
        return rotations.join('\n\n') + '\n';
    }
</script>
