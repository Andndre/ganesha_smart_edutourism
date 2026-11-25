<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CulturalObject;
use App\Models\Facility;
use App\Models\MapLocation;
use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $houses = TraditionalHouse::orderBy('number')->get(['id', 'number']);

        return view('admin.map-manager.index', compact('locations', 'owners', 'houses'));
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

        DB::transaction(fn () => $point->update($validated));

        return response()->json(['success' => true]);
    }

    /**
     * Delete a single map point without deleting the owning entity.
     */
    public function destroyPoint(MapLocation $point): JsonResponse
    {
        abort_if($point->locationable instanceof TraditionalHouse || ($point->locationable instanceof UmkmProfile && $point->locationable->isHouse()), 422, 'Kelola titik Rumah melalui profil rumah.');
        $point->delete();

        return response()->json(['success' => true]);
    }
}
