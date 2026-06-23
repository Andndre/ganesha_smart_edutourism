<?php

namespace App\Http\Controllers;

use App\Models\ArModel;
use App\Models\CulturalObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ARController extends Controller
{
    /**
     * Display the AR Scan page.
     */
    public function index(): View
    {
        return view('user.ar.index');
    }

    /**
     * Fetch the 3D model for a given cultural object slug.
     */
    public function getModel(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'nullable|string|max:255',
            'marker' => 'nullable|string|max:255',
        ]);

        $model = null;

        if ($request->filled('marker')) {
            $model = ArModel::with('mapLocation.locationable')
                ->where('ar_marker_id', $request->marker)
                ->first();
        }

        if (! $model && $request->filled('slug')) {
            $object = CulturalObject::where('slug', $request->slug)->first();
            if ($object && $object->mapLocation) {
                $model = ArModel::with('mapLocation.locationable')
                    ->where('map_location_id', $object->mapLocation->id)
                    ->first();
            }
        }

        if (! $model || ! $model->model_3d_path) {
            return response()->json(['error' => __('Model 3D tidak tersedia untuk objek ini')], 404);
        }

        $locationable = $model->mapLocation?->locationable;
        $name = $locationable?->name ?? $model->name;
        $description = $locationable?->description ?? $model->description;
        $shortDescription = $locationable?->short_description ?? $model->name;

        return response()->json([
            'success' => true,
            'name' => $name,
            'model_url' => '/storage/'.$model->model_3d_path,
            'usdz_url' => $model->model_3d_usdz_path ? asset('storage/'.(str_ends_with($model->model_3d_usdz_path, '.usdz') ? $model->model_3d_usdz_path : $model->model_3d_usdz_path.'.usdz')) : null,
            'audio_url' => $model->audio_narration_path ? asset('storage/'.$model->audio_narration_path) : null,
            'description' => $description,
            'short_description' => $shortDescription,
        ]);
    }

}
