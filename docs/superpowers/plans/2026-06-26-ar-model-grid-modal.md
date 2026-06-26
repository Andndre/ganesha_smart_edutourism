# AR Model Grid Modal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development for implementation. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Replace the `<select>` dropdown AR model picker with a modal grid selector + add-new flow that reuses the existing drawer modal form.

**Architecture:** Single Blade partial rewrite (`ar-model-section.blade.php`) with Alpine.js inline + embedded `<x-modal>`. Reuses `admin.ar-manager.partials.modal-form` for the add-new drawer. Two controllers get minor changes.

**Tech Stack:** Laravel 13, Blade, Alpine.js, TailwindCSS v4

## Global Constraints

- UI strings in Indonesian (admin context)
- Grid modal: `desktopLayout="center"`, `maxWidth="5xl"`
- Add-new drawer: reuse existing `admin.ar-manager.partials.modal-form`
- Disabled models: `opacity-40 pointer-events-none` + overlay badge "Terpakai"
- Search via Alpine: no Livewire
- Alpine component registered as `window.arModelSelector` function, not `Alpine.data()` (since Alpine initializes before `@stack('scripts')`)
- Chunked upload (TUS) must keep working
- `editor.blade.php` references to old select logic must be updated

---

### Task 1: Controller Changes

**Files:**
- Modify: `app/Http/Controllers/Admin/MapManagerController.php`
- Modify: `app/Http/Controllers/Admin/ARManagerController.php`

**Interfaces:**
- MapManagerController produces `$unavailableModelIds` (dict of `id => map_location_id`)
- ARManagerController accepts `redirect_to` POST param

- [ ] **Step 1: MapManagerController — pass unavailable model IDs**

In `MapManagerController@index`, after existing `$models` line:

```php
// models already linked to a different map_location — mark as disabled
$unavailableModelIds = ArModel::whereNotNull('map_location_id')
    ->pluck('map_location_id', 'id');
```

Update compact:

```php
return view('admin.map-manager.index', compact('locations', 'owners', 'models', 'unavailableModelIds'));
```

- [ ] **Step 2: ARManagerController — accept redirect_to**

In `storeModel()`, replace the final redirect:

```php
if ($request->input('redirect_to') === 'map-manager') {
    return redirect()->route('admin.map-manager', ['select_model' => $model->id])
        ->with('success', __('Model 3D berhasil ditambahkan.'));
}

return redirect()->route('admin.ar-manager')->with('success', __('Model 3D berhasil ditambahkan.'));
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Admin/MapManagerController.php app/Http/Controllers/Admin/ARManagerController.php
git commit -m "feat: pass unavailable model IDs, support redirect_to in AR store"
```

---

### Task 2: Add `redirect_to` hidden field to AR modal form

**Files:**
- Modify: `resources/views/admin/ar-manager/partials/modal-form.blade.php`

- [ ] **Step 1: Add hidden field after the CSRF field**

In `modal-form.blade.php`, after the CSRF line:

```blade
<input type="hidden" name="redirect_to" id="model-field-redirect-to" value="">
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/admin/ar-manager/partials/modal-form.blade.php
git commit -m "feat: add redirect_to hidden field to AR modal form"
```

---

### Task 3: Rewrite `ar-model-section.blade.php` — Full replacement

**Files:**
- Overwrite: `resources/views/admin/map-manager/partials/form-cultural/ar-model-section.blade.php`

**Interfaces:**
- Consumes: `$models` (ArModel collection), `$unavailableModelIds` (dict)
- Produces: `<input name="ar_model_id">` (hidden) with selected model ID
- Opens grid modal `ar-model-grid-modal` and references existing drawer `model-modal`

- [ ] **Step 1: Write the new partial**

```blade
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
                <span class="mt-0.5 inline-block rounded-full bg-primary/10 px-2 py-0.5 font-mono text-[10px] font-bold text-primary" x-text="selectedModel.ar_marker_id"></span>
            </p>
        </div>
        <button type="button" @click="clearSelection" class="shrink-0 rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- GRID MODAL --}}
    <x-modal name="ar-model-grid-modal" maxWidth="5xl" desktopLayout="center">
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
                            <div class="aspect-4/3 overflow-hidden bg-gray-100">
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

    {{-- Include the drawer modal form for Tambah Baru --}}
    @include('admin.ar-manager.partials.modal-form')
</div>

<script>
window.arModelSelector = function() {
    return {
        selectedId: '',
        selectedModel: null,
        search: '',
        models: @json($models->map(function ($m) use ($unavailableModelIds) {
            $name = $m->getTranslations('name');
            return [
                'id' => (string) $m->id,
                'name' => $name,
                'displayName' => $name[app()->getLocale()] ?? $name['en'] ?? $name['id'] ?? '',
                'ar_marker_id' => $m->ar_marker_id,
                'thumbnail_path' => $m->thumbnail_path,
                'model_3d_path' => $m->model_3d_path,
                'isTaken' => $m->map_location_id !== null,
            ];
        })),

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
            this.closeGridModal();
        },

        openAddNew: function () {
            this.closeGridModal();
            // Enable redirect_to on the modal form
            var redirectTo = document.getElementById('model-field-redirect-to');
            if (redirectTo) redirectTo.value = 'map-manager';
            setTimeout(function () {
                window.openModelModal();
            }, 300);
        },
    };
};

// Listen for edit-populate event from editor.blade.php
document.addEventListener('ar-model-select', function (e) {
    var el = document.querySelector('[x-data="arModelSelector()"]');
    if (el && el.__x) {
        el.__x.$data.selectedId = e.detail.modelId || '';
        el.__x.$data.selectedModel = el.__x.$data.models.find(function (m) { return m.id == e.detail.modelId; }) || null;
    }
});

// Listen for reset event from resetForms()
document.addEventListener('ar-model-reset', function () {
    var el = document.querySelector('[x-data="arModelSelector()"]');
    if (el && el.__x) {
        el.__x.$data.selectedId = '';
        el.__x.$data.selectedModel = null;
    }
});
</script>
```

Note: `aspect-4/3` is a Tailwind custom utility. If it doesn't exist, the fallback works — it just won't enforce aspect ratio. Add it if needed, or use `h-32 w-full` instead.

- [ ] **Step 2: Commit**

```bash
git add resources/views/admin/map-manager/partials/form-cultural/ar-model-section.blade.php
git commit -m "feat: replace AR model dropdown with modal grid selector"
```

---

### Task 4: Update editor.blade.php to use new selector

**Files:**
- Modify: `resources/views/admin/map-manager/partials/scripts/editor.blade.php`

- [ ] **Step 1: Replace old modelSelect references with custom events**

In editor.blade.php, find the block (lines 177-184):

```javascript
            // Select active model and trigger toggle
            const modelSelect = document.getElementById('ar_model_id_select');
            if (modelSelect) {
                modelSelect.value = loc.ar_model ? loc.ar_model.id : 'none';
                if (typeof toggleModelSelect === 'function') {
                    toggleModelSelect(modelSelect.value);
                }
            }
```

Replace with:

```javascript
            // Select active model via custom event to Alpine component
            window.dispatchEvent(new CustomEvent('ar-model-select', {
                detail: { modelId: loc.ar_model ? String(loc.ar_model.id) : '' }
            }));
```

Next, in `resetForms()` (lines 390-397), find:

```javascript
        const modelSelect = document.getElementById('ar_model_id_select');
        if (modelSelect) {
            modelSelect.value = 'none';
            if (typeof toggleModelSelect === 'function') {
                toggleModelSelect('none');
            }
        }
```

Replace with:

```javascript
        window.dispatchEvent(new CustomEvent('ar-model-reset'));
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/admin/map-manager/partials/scripts/editor.blade.php
git commit -m "fix: update editor script to use Alpine event-based model selector"
```

---

### Task 5: Final verification

**Files:**
- Run: `php artisan tinker` to verify `ArModel::whereNotNull('map_location_id')->pluck('map_location_id', 'id')` syntax
- Run: `vendor/bin/pint --dirty --format agent` to format

- [ ] **Step 1: Format code**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 2: Quick test — load map-manager page**

```bash
php artisan serve &>/dev/null &
```

Verify the page loads without Blade errors. The grid modal should open on click, show model cards, search should filter, disabled models should appear greyed out.

- [ ] **Step 3: Verify add-new flow**

Open grid modal → click "Tambah Baru" → drawer opens → fill form → save → redirects back with `?select_model=ID` → auto-selects the new model.

- [ ] **Step 4: Commit any final fixes**

```bash
git add -A
git commit -m "chore: formatting and final adjustments for AR model grid modal"
```
