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
        $locations = MapLocation::with(['locationable', 'arModel'])
            ->whereIn('locationable_type', [CulturalObject::class, UmkmProfile::class, Facility::class])
            ->get();

        $owners = User::where('role', 'umkm_owner')->orderBy('name')->get();
        $models = ArModel::orderBy('name')->get();

        // models already linked to a different map_location — mark as disabled
        $unavailableModelIds = ArModel::whereNotNull('map_location_id')
            ->pluck('map_location_id', 'id');

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
            'isTaken' => $m->map_location_id !== null,
        ];
    }
}
