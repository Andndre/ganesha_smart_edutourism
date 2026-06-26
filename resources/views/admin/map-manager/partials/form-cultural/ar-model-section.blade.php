<div x-data="arModelSelector()" x-init="initSelector()">
    <label class="mb-1.5 block text-sm font-semibold text-gray-700">Model 3D Aset AR</label>
    <span class="mb-2 block text-xs text-gray-500">Pilih model 3D yang sudah ada atau buat baru.</span>

    {{-- Hidden input for form submission --}}
    <input type="hidden" name="ar_model_id" x-model="selectedId">

    {{-- Trigger button --}}
    <button type="button" @click="openGridModal"
        class="flex w-full items-center justify-between gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm transition-all hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/20">
        <span class="flex items-center gap-2 min-w-0">
            <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span x-text="selectedId ? selectedModelName : 'Pilih atau tambah model 3D...'"
                :class="selectedId ? 'text-charcoal font-semibold' : 'text-gray-400'"
                class="truncate">Pilih atau tambah model 3D...</span>
        </span>
        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Compact preview when model selected --}}
    <div x-show="selectedId" x-cloak class="mt-2 flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 p-3">
        <template x-if="selectedModel?.thumbnail_path">
            <img :src="'{{ asset('storage') }}/' + selectedModel.thumbnail_path"
                class="h-10 w-10 shrink-0 rounded-lg border border-gray-100 object-cover">
        </template>
        <template x-if="!selectedModel?.thumbnail_path && selectedModel?.model_3d_path">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-[10px] font-bold text-gray-400">3D</div>
        </template>
        <div class="min-w-0 flex-1">
            <p class="truncate text-xs font-bold text-charcoal" x-text="selectedModelName"></p>
            <p x-show="selectedModel?.ar_marker_id">
                <span class="mt-0.5 inline-block rounded-full bg-primary/10 px-2 py-0.5 font-mono text-[10px] font-bold text-primary" x-text="selectedModel?.ar_marker_id || ''"></span>
            </p>
        </div>
        <button type="button" @click="clearSelection" class="shrink-0 rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- GRID MODAL --}}
    <x-modal name="ar-model-grid-modal" maxWidth="5xl" desktopLayout="center" zIndex="z-[90]"
        :closeOnOutsideClick="false">
        <div class="flex flex-col gap-4">
            <div>
                <h3 class="font-display text-charcoal text-lg font-bold">Pilih Model 3D</h3>
                <p class="mt-1 text-xs text-gray-500">Pilih model yang sudah ada atau buat baru.</p>
            </div>

            {{-- Search + Add New --}}
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="search" placeholder="Cari model 3D..."
                        class="w-full rounded-xl border border-gray-200 py-2.5 pl-9 pr-3 text-sm focus:border-primary focus:outline-none">
                </div>
                <button type="button" @click="openAddNew"
                    class="bg-primary hover:bg-primary-600 flex shrink-0 items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition-all shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Baru
                </button>
            </div>

            {{-- Grid --}}
            <div class="max-h-[55vh] overflow-y-auto -mx-1 px-1">
                <template x-if="filteredModels.length === 0">
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <svg class="mb-2 h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm" x-text="search ? 'Tidak ada model dengan nama &quot;' + search + '&quot;' : 'Belum ada model 3D'"></p>
                    </div>
                </template>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="model in filteredModels" :key="model.id">
                        <div @click="model.isTaken ? null : selectModel(model.id)"
                            :class="model.isTaken ? 'opacity-40 pointer-events-none' : 'cursor-pointer hover:border-primary hover:shadow-sm'"
                            class="relative flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white transition-all">
                            {{-- Thumbnail --}}
                            <div class="h-32 w-full overflow-hidden bg-gray-100">
                                <template x-if="model.thumbnail_path">
                                    <img :src="'{{ asset('storage') }}/' + model.thumbnail_path" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!model.thumbnail_path">
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                </template>
                            </div>
                            {{-- Body --}}
                            <div class="flex flex-col gap-1 p-3">
                                <p class="truncate text-xs font-bold text-charcoal" x-text="model.displayName"></p>
                                <p x-show="model.ar_marker_id" class="truncate font-mono text-[10px] text-gray-400" x-text="model.ar_marker_id"></p>
                            </div>
                            {{-- Disabled overlay --}}
                            <div x-show="model.isTaken"
                                class="absolute inset-0 flex items-center justify-center">
                                <span class="rounded-full bg-charcoal/70 px-3 py-1 text-[10px] font-bold text-white shadow-sm backdrop-blur-sm">Terpakai</span>
                            </div>
                            {{-- Selected check --}}
                            <div x-show="!model.isTaken && selectedId == model.id"
                                class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-primary text-white shadow-sm">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                <button type="button" x-show="selectedId" @click="clearSelection" class="text-sm font-semibold text-red-500 hover:text-red-600">
                    Hapus Pilihan
                </button>
                <button type="button" @click="closeGridModal" class="ml-auto rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-500 hover:bg-gray-50">
                    Tutup
                </button>
            </div>
        </div>
    </x-modal>
</div>

<script>
window.arModelSelector = function() {
    return {
        selectedId: '',
        selectedModel: null,
        search: '',
        models: @json($modelsJson),

        get filteredModels() {
            if (!this.search) return this.models;
            const q = this.search.toLowerCase();
            return this.models.filter(function (m) {
                return (m.displayName && m.displayName.toLowerCase().includes(q))
                    || (m.ar_marker_id && m.ar_marker_id.toLowerCase().includes(q));
            });
        },

        get selectedModelName() {
            if (!this.selectedModel) return '';
            var name = this.selectedModel.name;
            if (typeof name === 'object') {
                return name['{{ app()->getLocale() }}'] || name['en'] || name['id'] || '';
            }
            return name || '';
        },

        initSelector: function () {
            if (this.selectedId) {
                this.selectedModel = this.models.find(function (m) { return m.id == this.selectedId; }.bind(this)) || null;
            }
            // Handle ?select_model=ID from add-new redirect
            var params = new URLSearchParams(window.location.search);
            var selectModel = params.get('select_model');
            if (selectModel) {
                this.selectedId = selectModel;
                this.selectedModel = this.models.find(function (m) { return m.id == selectModel; }) || null;
                var url = new URL(window.location);
                url.searchParams.delete('select_model');
                window.history.replaceState({}, '', url);
            }
        },

        openGridModal: function () {
            window.dispatchEvent(new CustomEvent('open-ar-model-grid-modal'));
        },

        closeGridModal: function () {
            window.dispatchEvent(new CustomEvent('close-ar-model-grid-modal'));
        },

        selectModel: function (id) {
            this.selectedId = id;
            this.selectedModel = this.models.find(function (m) { return m.id == id; }) || null;
            this.closeGridModal();
        },

        clearSelection: function () {
            this.selectedId = '';
            this.selectedModel = null;
        },

        openAddNew: function () {
            // Enable redirect_to on the modal form
            var redirectTo = document.getElementById('model-field-redirect-to');
            if (redirectTo) redirectTo.value = 'map-manager';
            // Open drawer ON TOP of grid modal (grid z-[90] < drawer z-100)
            window.openModelModal();
        },
    };
};

// Listen for edit-populate event from editor.blade.php
window.addEventListener('ar-model-select', function (e) {
    var el = document.querySelector('[x-data="arModelSelector()"]');
    if (el && el._x_dataStack) {
        var data = Alpine.$data(el);
        data.selectedId = e.detail.modelId || '';
        data.selectedModel = data.models.find(function (m) { return m.id == e.detail.modelId; }) || null;
    }
});

// Listen for reset event from resetForms()
window.addEventListener('ar-model-reset', function () {
    var el = document.querySelector('[x-data="arModelSelector()"]');
    if (el && el._x_dataStack) {
        var data = Alpine.$data(el);
        data.selectedId = '';
        data.selectedModel = null;
    }
});

// Listen for new model created via AJAX — refetch full list, then highlight new model in grid
window.addEventListener('ar-model-created', function (e) {
    console.log('[AR-MODEL] ar-model-created event RECEIVED', e.detail);
    var el = document.querySelector('[x-data="arModelSelector()"]');
    if (!el || !el._x_dataStack) {
        console.error('[AR-MODEL] Alpine element NOT found or not initialized');
        return;
    }
    var alpineData = Alpine.$data(el);
    var newModel = e.detail.model;
    var newModelId = newModel?.id;
    if (!newModelId) {
        console.error('[AR-MODEL] No model id in event detail');
        return;
    }
    console.log('[AR-MODEL] Fetching models-json for refresh, newModelId:', newModelId);

    fetch('{{ route('admin.map-manager.models-json') }}')
        .then(function (r) { return r.json(); })
        .then(function (models) {
            console.log('[AR-MODEL] models-json fetched, count:', models.length);
            // Put new model first, then the rest (excluding duplicate)
            var rest = models.filter(function (m) { return m.id != newModelId; });
            var created = models.find(function (m) { return m.id == newModelId; });
            alpineData.models = created ? [created].concat(rest) : models;
            alpineData.selectedId = newModelId;
            alpineData.selectedModel = created || null;
            alpineData.search = '';
            console.log('[AR-MODEL] Grid updated, new model at front');
        })
        .catch(function (err) {
            console.error('[AR-MODEL] models-json fetch failed:', err);
            // Fallback: prepend single model from event detail
            if (newModel) {
                alpineData.models = [newModel].concat(alpineData.models);
                alpineData.selectedId = newModel.id;
                alpineData.selectedModel = newModel;
                alpineData.search = '';
            }
        })
        .finally(function () {
            // Scroll grid to top so the new model is visible
            var grid = el.querySelector('.max-h-\\[55vh\\]');
            if (grid) grid.scrollTop = 0;
        });
});
</script>