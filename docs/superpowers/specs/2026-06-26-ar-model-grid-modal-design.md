# AR Model Selector: Dropdown → Modal Grid Redesign

## Objective

Replace the `<select>` dropdown in `ar-model-section.blade.php` with a modal-based grid selector, similar to the AR Manager listing, including a "Tambah Baru" flow that reuses the existing drawer modal form.

## Scope

Single file rewrite: `ar-model-section.blade.php`.

Controller touches: `MapManagerController@index` — pass `$unavailableModelIds`; `ARManagerController@storeModel` — accept optional `redirect_to` param.

## Specification

### 1. Trigger Button

Replace `<select name="ar_model_id">` with:
- **Button** `[Pilih / Tambah Model 3D]` — opens the grid modal (center, `maxWidth="5xl"`)
- Below button: compact label showing selected model name + marker badge (if any), or "Belum ada model 3D"
- Hidden `<input name="ar_model_id">` tracks the selected value
- Selected model's GLB preview is shown inline (compact), same as current behavior

### 2. Grid Modal (`desktopLayout="center"`, `maxWidth="5xl"`)

Header:
- Title "Pilih Model 3D" + subtitle "Pilih model yang sudah ada atau buat baru"
- Search bar (Alpine `x-model`, filter grid cards by name)
- "Tambah Baru" button — opens the drawer modal

Grid:
- 3 columns on desktop, 2 on tablet, 1 on mobile
- Each card shows: thumbnail, name, AR marker ID badge
- **Disabled cards** (model already linked to another cultural object): `opacity-40 pointer-events-none`, overlay badge "Terpakai"
- Clicking an available card → selects it, closes grid modal, updates parent form's hidden input + preview

Footer:
- "Hapus Pilihan" button (visible only when a model is selected) — clears selection
- "Tutup" button

### 3. Tambah Baru Flow (Drawer Modal)

"Tambah Baru" button in grid modal → dispatches `open-model-modal` custom event → opens the **existing** drawer modal (`admin.ar-manager.partials.modal-form`).

On successful save:
- `ARManagerController@storeModel` checks `redirect_to` query param
- Redirects back to map-manager page with `?select_model=<new_model_id>`
- `ar-model-section` reads `select_model` from URL on page load → auto-selects the newly created model

### 4. Disabled Model Logic

```php
// MapManagerController@index
$unavailableModelIds = ArModel::whereNotNull('map_location_id')
    ->where('map_location_id', '!=', request('map_location_id') ?? $locationId)
    ->pluck('id');
```

Pass to view, used in Blade to mark disabled cards.

### 5. Data Flow

| State | Mechanism |
|-------|-----------|
| Selected model ID | Hidden `<input name="ar_model_id" x-model="selectedModelId">` |
| Selection display | Model name + marker badge shown after button |
| Clear selection | Reset `selectedModelId` to empty string |
| Grid filter | Alpine `x-model="search"` + `x-text` filter by name |
| Disabled models | Blade `@if(in_array($model->id, $unavailableModelIds))` |

### 6. Model Preview After Selection

Compact preview below the trigger button:
- If model has GLB: small `<model-viewer>` (h-32)
- Model name
- Marker ID badge (if available)

### 7. File Changes

| File | Change |
|------|--------|
| `MapManagerController.php` | Pass `$unavailableModelIds` to view |
| `ar-model-section.blade.php` | Full rewrite: button + grid modal + Alpine logic |
| `ARManagerController.php` | `storeModel()`: check `redirect_to` param |
| No new files | Modal form partial is `@include`'d, not duplicated |

## Non-Goals

- No changes to AR Manager CRUD endpoints
- No migration or schema changes
- No new JS dependencies
- No Livewire conversion
