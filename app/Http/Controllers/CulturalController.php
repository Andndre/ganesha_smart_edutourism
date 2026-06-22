<?php

namespace App\Http\Controllers;

use App\Models\CulturalObject;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CulturalController extends Controller
{
    /**
     * Display a listing of the cultural objects.
     */
    public function index(): View
    {
        $objects = Cache::tags(['cultural'])->flexible('cultural_objects_all_array', [3600, 7200], function () {
            return CulturalObject::with('mapLocation.arModel')
                ->orderBy('name')
                ->get()
                ->append(['ar_marker_id', 'model_3d_path', 'audio_narration_path'])
                ->toArray();
        });

        return view('user.cultural.index', compact('objects'));
    }

    /**
     * Display the specified cultural object with its stories.
     */
    public function show(string $slug): View
    {
        $object = Cache::tags(['cultural'])->flexible("cultural_object_array_{$slug}", [3600, 7200], function () use ($slug) {
            return CulturalObject::with(['stories', 'mapLocation.arModel'])
                ->where('slug', $slug)
                ->firstOrFail()
                ->append(['ar_marker_id', 'model_3d_path', 'audio_narration_path', 'model_3d_usdz_path', 'ar_marker_patt_path'])
                ->toArray();
        });

        return view('user.cultural.show', compact('object'));
    }
}
