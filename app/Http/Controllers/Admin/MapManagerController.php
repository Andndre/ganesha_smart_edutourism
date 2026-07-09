<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\Facility;
use App\Models\MapLocation;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapManagerController extends Controller
{
    /**
     * Display the map interface managing all locations.
     *
     * Scoped to CulturalObject/UmkmProfile/Facility — the only types this editor's
     * forms support. Other locationables (e.g. Event) get their own management page.
     */
    public function index(): View
    {
        $locations = MapLocation::with(['locationable' => function ($morphTo) {
            $morphTo->morphWith([CulturalObject::class => ['arModel']]);
        }])
            ->whereIn('locationable_type', [CulturalObject::class, UmkmProfile::class, Facility::class])
            ->get();

        $owners = User::where('role', 'umkm_owner')->orderBy('name')->get();
        $models = ArModel::orderBy('name')->get();

        // models already linked to a different cultural object — mark as disabled
        $unavailableModelIds = ArModel::whereNotNull('cultural_object_id')
            ->pluck('cultural_object_id', 'id');

        $modelsJson = $models->map(fn ($m) => $this->modelToJson($m));

        return view('admin.map-manager.index', compact('locations', 'owners', 'models', 'unavailableModelIds', 'modelsJson'));
    }

    /**
     * JSON endpoint to refresh the AR model list (used after add-new).
     */
    public function modelsJson(): JsonResponse
    {
        $models = ArModel::orderBy('name')->get();

        return response()->json($models->map(fn ($m) => $this->modelToJson($m)));
    }

    /**
     * Add an extra map point to an existing cultural object or facility.
     * UMKM profiles stay single-point via UmkmController::syncMapLocation().
     */
    public function storePoint(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'owner_type' => ['required', 'in:cultural_object,facility'],
            'owner_id' => ['required', 'integer'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $ownerClass = $validated['owner_type'] === 'cultural_object' ? CulturalObject::class : Facility::class;
        $owner = $ownerClass::findOrFail($validated['owner_id']);

        $point = $owner->mapLocations()->create([
            'name' => $owner->getMapDisplayName(),
            'category' => $validated['owner_type'] === 'cultural_object' ? 'cultural' : 'facility',
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'is_accessible' => false,
        ]);

        return response()->json(['success' => true, 'point' => $point->load('locationable')]);
    }

    /**
     * Reposition a single existing map point without touching the owning entity.
     */
    public function updatePoint(Request $request, MapLocation $point): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $point->update($validated);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a single map point without deleting the owning entity.
     */
    public function destroyPoint(MapLocation $point): JsonResponse
    {
        $point->delete();

        return response()->json(['success' => true]);
    }

    private function modelToJson($m): array
    {
        $name = $m->getTranslations('name');

        return [
            'id' => (string) $m->id,
            'name' => $name,
            'displayName' => $name[app()->getLocale()] ?? $name['en'] ?? $name['id'] ?? '',
            'ar_marker_id' => $m->ar_marker_id,
            'thumbnail_path' => $m->thumbnail_path,
            'model_3d_path' => $m->model_3d_path,
            'isTaken' => $m->cultural_object_id !== null,
        ];
    }
}
