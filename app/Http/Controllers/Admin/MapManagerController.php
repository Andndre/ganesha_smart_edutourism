<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\MapLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\View\View;

class MapManagerController extends Controller
{
    /**
     * Display the map interface managing all locations.
     */
    public function index(): View
    {
        $locations = MapLocation::with([
            'locationable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    CulturalObject::class => ['quizzes', 'stories'],
                ]);
            },
            'arModel',
        ])->get();

        $owners = User::where('role', 'umkm_owner')->orderBy('name')->get();
        $models = ArModel::orderBy('name')->get();

        // models already linked to a different map_location — mark as disabled
        $unavailableModelIds = ArModel::whereNotNull('map_location_id')
            ->pluck('map_location_id', 'id');

        return view('admin.map-manager.index', compact('locations', 'owners', 'models', 'unavailableModelIds'));
    }
}
