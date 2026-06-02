<?php

namespace App\Http\Controllers;

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

        $object = null;

        if ($request->filled('slug')) {
            $object = CulturalObject::where('slug', $request->slug)->first();
        }

        if (! $object && $request->filled('marker')) {
            $object = CulturalObject::where('ar_marker_id', $request->marker)->first();
        }

        if (! $object) {
            return response()->json(['error' => 'Objek tidak ditemukan'], 404);
        }

        if (! $object->model_3d_path) {
            return response()->json(['error' => 'Model 3D tidak tersedia untuk objek ini'], 404);
        }

        return response()->json([
            'success' => true,
            'name' => $object->name,
            'model_url' => '/storage/'.$object->model_3d_path,
            'usdz_url' => $object->model_3d_usdz_path ? '/storage/'.$object->model_3d_usdz_path : null,
            'description' => $object->description,
        ]);
    }
}
