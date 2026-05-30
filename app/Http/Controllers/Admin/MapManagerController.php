<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapLocation;
use App\Models\User;
use Illuminate\View\View;

class MapManagerController extends Controller
{
    /**
     * Display the map interface managing all locations.
     */
    public function index(): View
    {
        $locations = MapLocation::with(['locationable' => function (\Illuminate\Database\Eloquent\Relations\MorphTo $morphTo) {
            $morphTo->morphWith([
                \App\Models\CulturalObject::class => ['quizzes'],
            ]);
        }])->get();
        
        $owners = User::where('role', 'umkm_owner')->orderBy('name')->get();

        return view('admin.map-manager.index', compact('locations', 'owners'));
    }
}
