<?php

namespace App\Http\Controllers;

use App\Models\CulturalObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            'usdz_url' => $object->model_3d_usdz_path ? route('usdz.serve', ['path' => str_ends_with($object->model_3d_usdz_path, '.usdz') ? $object->model_3d_usdz_path : $object->model_3d_usdz_path.'.usdz']) : null,
            'description' => $object->description,
            'short_description' => $object->short_description,
        ]);
    }

    /**
     * Serve USDZ files with the correct MIME type for iOS Quick Look.
     */
    public function serveUsdz(string $path): BinaryFileResponse
    {
        $fullPath = storage_path('app/public/'.$path);

        if (! file_exists($fullPath)) {
            // Support serving legacy/incorrectly named .zip USDZ files
            if (str_ends_with($path, '.zip.usdz')) {
                $strippedPath = substr($path, 0, -5);
                $fullPath = storage_path('app/public/'.$strippedPath);
            }
        }

        if (! file_exists($fullPath)) {
            abort(404);
        }

        $filename = basename($path);
        if (str_ends_with($filename, '.zip.usdz')) {
            $filename = substr($filename, 0, -5);
        }
        if (str_ends_with($filename, '.zip')) {
            $filename = substr($filename, 0, -4).'.usdz';
        }

        return response()->file($fullPath, [
            'Content-Type' => 'model/vnd.usdz+zip',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
