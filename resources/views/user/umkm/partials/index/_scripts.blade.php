{{-- ponytail: partial dipecah untuk keterbacaan --}}
    <script>
        (function() {
            let activeModalCategoryId = null;
            let currentModalTab = 'image';

            function updateCardHighlight(id) {
                const card = document.getElementById(`card-cat-${id}`);
                const checkbox = document.getElementById(`checkbox-cat-${id}`);
                if (!card || !checkbox) return;

                if (checkbox.checked) {
                    card.classList.add('ring-2', 'ring-primary', 'border-primary', 'bg-primary/[0.01]');
                    card.classList.remove('border-gray-100');
                } else {
                    card.classList.remove('ring-2', 'ring-primary', 'border-primary', 'bg-primary/[0.01]');
                    card.classList.add('border-gray-100');
                }

                updateSelectedPills();
            }

            function deselectCategory(id, event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                const checkbox = document.getElementById(`checkbox-cat-${id}`);
                if (checkbox) {
                    checkbox.checked = false;
                    checkbox.dispatchEvent(new Event('change'));
                }
            }

            function updateSelectedPills() {
                const container = document.getElementById('selected-categories-pills');
                if (!container) return;

                const checkedBoxes = document.querySelectorAll('input[name="category_ids[]"]:checked');

                if (checkedBoxes.length === 0) {
                    container.innerHTML = '';
                    container.classList.add('hidden');
                    container.classList.remove('flex');
                    return;
                }

                container.classList.remove('hidden');
                container.classList.add('flex');

                let html = '';
                checkedBoxes.forEach(box => {
                    const id = box.value;
                    const card = document.getElementById(`card-cat-${id}`);
                    const name = card ? card.querySelector('h3').innerText : 'Kategori';

                    html += `
                    <div class="flex items-center gap-1.5 bg-primary/[0.08] text-primary text-xs font-bold px-3 py-1.5 rounded-full border border-primary/20 transition-all select-none shrink-0">
                        <span>${name}</span>
                        <button type="button" onclick="deselectCategory(${id}, event)" class="hover:text-primary-700 hover:bg-primary/10 rounded-full p-0.5 transition-colors focus:outline-none ml-0.5">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                `;
                });

                container.innerHTML = html;
            }

            function switchModalTab(tab) {
                currentModalTab = tab;

                const imageContainer = document.getElementById('modal-category-image-container');
                const modelContainer = document.getElementById('modal-category-3d-container');
                const tabImageBtn = document.getElementById('tab-btn-image');
                const tab3dBtn = document.getElementById('tab-btn-3d');

                if (tab === 'image') {
                    imageContainer.classList.remove('hidden');
                    modelContainer.classList.add('hidden');

                    tabImageBtn.className =
                        'rounded-xl px-4 py-2 text-xs font-bold transition-all bg-primary text-white shadow-sm';
                    tab3dBtn.className =
                        'rounded-xl px-4 py-2 text-xs font-bold transition-all bg-gray-50 text-gray-500 hover:bg-gray-100';
                } else {
                    imageContainer.classList.add('hidden');
                    modelContainer.classList.remove('hidden');

                    tabImageBtn.className =
                        'rounded-xl px-4 py-2 text-xs font-bold transition-all bg-gray-50 text-gray-500 hover:bg-gray-100';
                    tab3dBtn.className =
                    'rounded-xl px-4 py-2 text-xs font-bold transition-all bg-primary text-white shadow-sm';
                }
            }

            function openCategoryModal(category, event) {
                // Prevent opening modal if clicking checkbox container
                if (event.target.closest('[onclick="event.stopPropagation()"]')) {
                    return;
                }

                activeModalCategoryId = category.id;

                // Set content
                document.getElementById('modal-category-name').innerText = category.name;
                document.getElementById('modal-category-description').innerText = category.description ||
                    "Belum ada deskripsi untuk kategori ini.";

                const imgEl = document.getElementById('modal-category-image');
                const fallbackEl = document.getElementById('modal-category-fallback');

                if (category.image_path) {
                    imgEl.src = `/storage/${category.image_path}`;
                    imgEl.classList.remove('hidden');
                    fallbackEl.classList.add('hidden');
                } else {
                    imgEl.src = '';
                    imgEl.classList.add('hidden');
                    fallbackEl.classList.remove('hidden');
                }

                // Configure 3D Model Viewer
                const modelViewer = document.getElementById('modal-category-3d');
                const tabsContainer = document.getElementById('modal-tabs-container');

                if (category.model_3d_path) {
                    modelViewer.src = `/storage/${category.model_3d_path}`;
                    if (category.model_3d_usdz_path) {
                        let usdzPath = category.model_3d_usdz_path;
                        if (!usdzPath.endsWith('.usdz')) {
                            usdzPath += '.usdz';
                        }
                        modelViewer.setAttribute('ios-src', `/usdz-file/${usdzPath}`);
                    } else {
                        modelViewer.removeAttribute('ios-src');
                    }
                    tabsContainer.classList.remove('hidden');
                    tabsContainer.classList.add('flex');
                } else {
                    modelViewer.src = '';
                    modelViewer.removeAttribute('ios-src');
                    tabsContainer.classList.remove('flex');
                    tabsContainer.classList.add('hidden');
                }

                // Default to Image tab
                switchModalTab('image');

                // Update action button text and style based on select state
                const checkbox = document.getElementById(`checkbox-cat-${category.id}`);
                const btn = document.getElementById('modal-toggle-select-btn');
                if (checkbox && checkbox.checked) {
                    btn.innerText = 'Batal Pilih Kategori';
                    btn.className =
                        'w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3.5 rounded-xl active:scale-[0.98] transition-transform flex justify-center items-center gap-2';
                } else {
                    btn.innerText = 'Pilih Kategori Ini';
                    btn.className =
                        'w-full bg-primary hover:bg-primary-600 text-white font-semibold py-3.5 rounded-xl active:scale-[0.98] transition-transform shadow-lg shadow-primary/20 flex justify-center items-center gap-2';
                }

                // Show modal with Alpine.js
                window.dispatchEvent(new CustomEvent('open-category-detail'));
            }

            function closeCategoryModal() {
                window.dispatchEvent(new CustomEvent('close-category-detail'));
                activeModalCategoryId = null;

                // Clear 3D Model viewer source when closed to prevent performance leaks
                setTimeout(() => {
                    const modelViewer = document.getElementById('modal-category-3d');
                    if (modelViewer) {
                        modelViewer.src = '';
                        modelViewer.removeAttribute('ios-src');
                    }
                }, 300);
            }

            function toggleSelectFromModal() {
                if (!activeModalCategoryId) return;

                const checkbox = document.getElementById(`checkbox-cat-${activeModalCategoryId}`);
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }

                closeCategoryModal();
            }

            const initUmkm = function() {
                // Check highlights on load for pre-selected items (if any)
                document.querySelectorAll('input[name="category_ids[]"]').forEach(box => {
                    const id = box.value;
                    updateCardHighlight(id);
                });

                // Configure Meshopt Decoder before model-viewer renders
                const ModelViewerElement = customElements.get('model-viewer');
                if (ModelViewerElement) {
                    ModelViewerElement.meshoptDecoderLocation =
                        'https://unpkg.com/meshoptimizer@0.17.0/meshopt_decoder.js';
                }

                const searchInput = document.getElementById('search-input');
                const clearBtn = document.getElementById('clear-search-btn');

                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const query = this.value.toLowerCase().trim();

                        // Show or hide clear button
                        if (clearBtn) {
                            if (query.length > 0) {
                                clearBtn.classList.remove('hidden');
                            } else {
                                clearBtn.classList.add('hidden');
                            }
                        }

                        // Filter cards
                        let visibleCount = 0;
                        const cards = document.querySelectorAll('.category-card');
                        cards.forEach(card => {
                            const name = card.getAttribute('data-name');
                            const description = card.getAttribute('data-description');
                            if (!query || name.includes(query) || description.includes(query)) {
                                card.classList.remove('hidden');
                                visibleCount++;
                            } else {
                                card.classList.add('hidden');
                            }
                        });

                        // Toggle empty state
                        const emptyState = document.getElementById('empty-state');
                        if (emptyState) {
                            if (visibleCount === 0) {
                                emptyState.classList.remove('hidden');
                                emptyState.classList.add('flex');
                            } else {
                                emptyState.classList.add('hidden');
                                emptyState.classList.remove('flex');
                            }
                        }
                    });

                    if (clearBtn) {
                        clearBtn.addEventListener('click', function() {
                            searchInput.value = '';
                            clearBtn.classList.add('hidden');
                            searchInput.dispatchEvent(new Event('input'));
                            searchInput.focus();
                        });
                    }
                }
            };

            // Expose required functions to window for inline HTML onclick/onchange attributes
            window.updateCardHighlight = updateCardHighlight;
            window.deselectCategory = deselectCategory;
            window.switchModalTab = switchModalTab;
            window.openCategoryModal = openCategoryModal;
            window.closeCategoryModal = closeCategoryModal;
            window.toggleSelectFromModal = toggleSelectFromModal;

            // Run immediately
            initUmkm();

            document.addEventListener('livewire:navigating', function cleanupUmkm(e) {
                delete window.updateCardHighlight;
                delete window.deselectCategory;
                delete window.switchModalTab;
                delete window.openCategoryModal;
                delete window.closeCategoryModal;
                delete window.toggleSelectFromModal;
                document.removeEventListener('livewire:navigating', cleanupUmkm);
            });
        })();
    </script>

    <script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>
