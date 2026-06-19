@extends('layouts.app')
@section('title', 'Katalog UMKM - Penglipuran')
@section('header_title', 'Katalog UMKM')

@section('content')
    <div class="px-4 pb-40 pt-[calc(env(safe-area-inset-top)+6rem)]">
        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="relative z-20 mb-4 rounded-xl border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow-sm"
                role="alert">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Session Error -->
        @if (session('error'))
            <div class="relative z-20 mb-4 rounded-xl border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow-sm"
                role="alert">
                <span class="block font-medium sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Missing Categories Warning (if partial multi-stop) -->
        @if (session('missing_categories'))
            <div class="relative z-20 mb-4 rounded-xl border border-yellow-400 bg-yellow-50 px-4 py-3 text-yellow-800 shadow-sm"
                role="alert">
                <span class="block font-medium sm:inline">Beberapa pesanan Anda tidak tersedia di UMKM manapun:</span>
                <ul class="mt-1 list-disc pl-5 text-sm">
                    @foreach (session('missing_categories') as $missingName)
                        <li>{{ $missingName }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6">
            <h2 class="text-charcoal text-xl font-bold">Jelajah UMKM</h2>
            <p class="mt-1 text-sm text-gray-500">Pilih satu atau lebih kategori yang Anda inginkan. Sistem kami akan
                membantu mencarikan lokasi UMKM yang memiliki produk tersebut.</p>
        </div>

        <form action="{{ route('umkm.recommend') }}" method="POST">
            @csrf

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                @foreach ($categories as $category)
                    <div id="card-cat-{{ $category->id }}"
                        class="category-card relative flex h-full cursor-pointer flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all hover:border-gray-200 hover:shadow-md"
                        data-name="{{ strtolower($category->name) }}"
                        data-description="{{ strtolower($category->description ?? '') }}"
                        onclick="openCategoryModal({{ json_encode($category) }}, event)">
                        <div class="relative aspect-square bg-gray-100">
                            @if ($category->image_path)
                                <img src="{{ asset('storage/' . $category->image_path) }}" alt="{{ $category->name }}"
                                    class="h-full w-full object-cover">
                            @else
                                <div class="text-primary absolute inset-0 flex items-center justify-center opacity-50">
                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            @endif

                            <!-- Checkbox Container at Top-Right -->
                            <div class="absolute right-2 top-2 z-10" onclick="event.stopPropagation()">
                                <label class="flex cursor-pointer items-center justify-center">
                                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                        id="checkbox-cat-{{ $category->id }}"
                                        class="w-5.5 h-5.5 text-primary focus:ring-primary accent-primary cursor-pointer rounded-full border-gray-300 transition-all focus:ring-offset-0"
                                        onchange="updateCardHighlight({{ $category->id }})">
                                </label>
                            </div>
                        </div>
                        <div class="flex-1 p-3">
                            <h3 class="text-charcoal text-sm font-bold">{{ $category->name }}</h3>
                            @if ($category->description)
                                <p class="mt-1 line-clamp-2 text-xs text-gray-500">{{ $category->description }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Empty State for Search -->
            <div id="empty-state" class="hidden flex-col items-center justify-center py-12 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-50 text-gray-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-charcoal text-base font-bold">Kategori Tidak Ditemukan</h3>
                <p class="mt-1 text-xs text-gray-500">Coba gunakan kata kunci pencarian yang lain.</p>
            </div>

            @php
                $hasActiveSession = false;
                if (auth()->check() || session()->has('guest_token')) {
                    $hasActiveSession = \App\Models\RouteSession::where('status', 'active')
                        ->where(function ($q) {
                            $q->where('user_id', auth()->id())->orWhere('guest_token', session('guest_token'));
                        })
                        ->exists();
                }
            @endphp
            <!-- Sticky Bottom Bar for Button -->
            <div
                class="{{ $hasActiveSession ? 'mb-18' : '' }} fixed bottom-[calc(env(safe-area-inset-bottom)+4rem)] left-0 right-0 z-40 border-t border-gray-200 bg-white/80 px-4 pb-8 pt-4 backdrop-blur-md transition-all">
                <!-- Selected Categories Pills Container -->
                <div id="selected-categories-pills"
                    class="no-scrollbar mb-3 hidden flex-row flex-nowrap gap-2 overflow-x-auto pb-1">
                    <!-- Dynamic pills will be injected here -->
                </div>

                <button type="submit"
                    class="bg-primary flex w-full items-center justify-center gap-2 rounded-xl py-3.5 font-semibold text-white shadow-lg transition-transform active:scale-[0.98]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Temukan UMKM
                </button>
            </div>
        </form>
    </div>
    <!-- Multi-Stop Recommendation Modal -->
    @if (session('multi_stop_recommendations'))
        <x-modal name="multi-stop" maxWidth="sm" :defaultOpen="true">
            <div class="text-center">
                <div
                    class="bg-primary/10 text-primary mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <h3 class="text-charcoal mb-2 text-xl font-bold">Satu Tempat Tidak Cukup!</h3>
                <p class="mb-6 text-sm text-gray-500">Tapi jangan khawatir, kami telah menyusun <span
                        class="text-charcoal font-bold">rute terdekat</span> agar Anda bisa mendapatkan semua barang pilihan
                    Anda dari beberapa UMKM sekaligus.</p>
                <div class="space-y-3">
                    <a href="{{ route('umkm.multi_recommended') }}"
                        class="bg-primary block w-full rounded-xl py-3.5 font-bold text-white shadow-lg transition-transform active:scale-[0.98]">
                        Lihat Rute Belanja
                    </a>
                    <button @click="isOpen = false"
                        class="block w-full rounded-xl bg-gray-100 py-3.5 font-bold text-gray-600 transition-transform active:scale-[0.98]">
                        Batal
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Category Detail Modal -->
    <x-modal name="category-detail" maxWidth="sm">
        <!-- Close Button (Mobile only, desktop has close button in x-modal) -->
        <button type="button" onclick="closeCategoryModal()"
            class="absolute right-4 top-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition-all hover:text-gray-600 active:scale-95 md:hidden"
            title="Tutup">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Premium Tab Switcher (Only visible if 3D model is active) -->
        <div id="modal-tabs-container" class="mb-4 hidden justify-center gap-2 border-b border-gray-100 pb-2.5">
            <button type="button" id="tab-btn-image" onclick="switchModalTab('image')"
                class="bg-primary rounded-xl px-4 py-2 text-xs font-bold text-white shadow-sm transition-all">
                Gambar
            </button>
            <button type="button" id="tab-btn-3d" onclick="switchModalTab('3d')"
                class="rounded-xl bg-gray-50 px-4 py-2 text-xs font-bold text-gray-500 transition-all hover:bg-gray-100">
                Tampilan 3D
            </button>
        </div>

        <!-- Category Image Container -->
        <div class="text-primary flex aspect-video w-full items-center justify-center overflow-hidden rounded-2xl border border-gray-100 bg-gray-50"
            id="modal-category-image-container">
            <img id="modal-category-image" src="" alt="" class="hidden h-full w-full object-cover">
            <svg id="modal-category-fallback" class="h-14 w-14 opacity-50" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </div>

        <!-- Category 3D Viewer Container -->
        <div class="relative hidden aspect-video w-full overflow-hidden rounded-2xl border border-gray-100 bg-gray-100"
            id="modal-category-3d-container">
            <model-viewer id="modal-category-3d" class="h-full w-full" camera-controls auto-rotate shadow-intensity="1"
                touch-action="pan-y" draco-decoder-location="https://www.gstatic.com/draco/versioned/decoders/1.5.6/">
            </model-viewer>
        </div>

        <!-- Content -->
        <div class="mt-4">
            <h3 id="modal-category-name" class="font-display text-charcoal text-xl font-bold">Nama Kategori</h3>
            <p id="modal-category-description" class="mt-2 min-h-12.5 text-sm leading-relaxed text-gray-500">Deskripsi
                kategori...</p>
        </div>

        <!-- Action Button -->
        <div class="mt-6">
            <button type="button" id="modal-toggle-select-btn" onclick="toggleSelectFromModal()"
                class="bg-primary shadow-primary/20 flex w-full items-center justify-center gap-2 rounded-xl py-3.5 font-semibold text-white shadow-lg transition-transform active:scale-[0.98]">
                Pilih Kategori Ini
            </button>
        </div>
    </x-modal>

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
@endsection
