<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArMarker;
use App\Models\ArModel;
use App\Models\MapLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ARManagerController extends Controller
{
    /**
     * Display the AR asset manager panel.
     */
    public function index(): View
    {
        $models = ArModel::with('arMarkers.mapLocation')->orderBy('name')->get();

        $markers = ArMarker::with(['arModel', 'mapLocation.locationable'])->orderBy('ar_marker_id')->get();

        // Fetch locations that are eligible to have markers (CulturalObject, UmkmProfile, etc.)
        $locations = MapLocation::with('arMarker')->orderBy('name')->get();

        return view('admin.ar-manager.index', compact('models', 'markers', 'locations'));
    }

    /**
     * Store a newly created 3D Model asset.
     */
    public function storeModel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'model_3d_file' => ['required', 'file', 'max:20480'], // max 20MB GLB
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'], // max 50MB USDZ
            'audio_narration_file' => ['nullable', 'file', 'max:10240'], // max 10MB MP3
        ]);

        $modelData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('model_3d_file')) {
            $modelData['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        }

        if ($request->hasFile('model_3d_usdz_file')) {
            $file = $request->file('model_3d_usdz_file');
            $filename = Str::random(40).'.usdz';
            $modelData['model_3d_usdz_path'] = $file->storeAs('models_usdz', $filename, 'public');
        }

        if ($request->hasFile('audio_narration_file')) {
            $modelData['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
        }

        ArModel::create($modelData);

        return redirect()->route('admin.ar-manager')->with('success', 'Model 3D berhasil ditambahkan.');
    }

    /**
     * Update an existing 3D Model asset.
     */
    public function updateModel(Request $request, int $id): RedirectResponse
    {
        $model = ArModel::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'model_3d_file' => ['nullable', 'file', 'max:20480'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
            'audio_narration_file' => ['nullable', 'file', 'max:10240'],
        ]);

        $model->name = $validated['name'];
        $model->description = $validated['description'] ?? null;

        if ($request->hasFile('model_3d_file')) {
            if ($model->model_3d_path) {
                Storage::disk('public')->delete($model->model_3d_path);
            }
            $model->model_3d_path = $request->file('model_3d_file')->store('models', 'public');
        }

        if ($request->hasFile('model_3d_usdz_file')) {
            if ($model->model_3d_usdz_path) {
                Storage::disk('public')->delete($model->model_3d_usdz_path);
            }
            $file = $request->file('model_3d_usdz_file');
            $filename = Str::random(40).'.usdz';
            $model->model_3d_usdz_path = $file->storeAs('models_usdz', $filename, 'public');
        }

        if ($request->hasFile('audio_narration_file')) {
            if ($model->audio_narration_path) {
                Storage::disk('public')->delete($model->audio_narration_path);
            }
            $model->audio_narration_path = $request->file('audio_narration_file')->store('audio', 'public');
        }

        $model->save();

        return redirect()->route('admin.ar-manager')->with('success', 'Model 3D berhasil diperbarui.');
    }

    /**
     * Delete a 3D Model asset.
     */
    public function destroyModel(int $id): RedirectResponse
    {
        $model = ArModel::findOrFail($id);

        // Delete files
        if ($model->model_3d_path) {
            Storage::disk('public')->delete($model->model_3d_path);
        }
        if ($model->model_3d_usdz_path) {
            Storage::disk('public')->delete($model->model_3d_usdz_path);
        }
        if ($model->audio_narration_path) {
            Storage::disk('public')->delete($model->audio_narration_path);
        }

        $model->delete();

        return redirect()->route('admin.ar-manager')->with('success', 'Model 3D berhasil dihapus.');
    }

    /**
     * Store a newly created QR Marker.
     */
    public function storeMarker(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ar_marker_id' => ['required', 'string', 'max:255', 'unique:ar_markers,ar_marker_id'],
            'ar_model_id' => ['nullable', 'exists:ar_models,id'],
            'map_location_id' => ['nullable', 'exists:map_locations,id'],
            'ar_marker_patt_content' => ['nullable', 'string'],
        ]);

        $markerData = [
            'ar_marker_id' => $validated['ar_marker_id'],
            'ar_model_id' => $validated['ar_model_id'] ?? null,
            'map_location_id' => $validated['map_location_id'] ?? null,
        ];

        if ($request->filled('ar_marker_patt_content')) {
            $pattPath = 'ar-markers/'.$validated['ar_marker_id'].'.patt';
            Storage::disk('public')->put($pattPath, $request->input('ar_marker_patt_content'));
            $markerData['ar_marker_patt_path'] = $pattPath;
        }

        ArMarker::create($markerData);

        return redirect()->route('admin.ar-manager')->with('success', 'Marker QR berhasil ditambahkan.');
    }

    /**
     * Update an existing QR Marker.
     */
    public function updateMarker(Request $request, int $id): RedirectResponse
    {
        $marker = ArMarker::findOrFail($id);

        $validated = $request->validate([
            'ar_marker_id' => ['required', 'string', 'max:255', 'unique:ar_markers,ar_marker_id,'.$id],
            'ar_model_id' => ['nullable', 'exists:ar_models,id'],
            'map_location_id' => ['nullable', 'exists:map_locations,id'],
            'ar_marker_patt_content' => ['nullable', 'string'],
        ]);

        $marker->ar_marker_id = $validated['ar_marker_id'];
        $marker->ar_model_id = $validated['ar_model_id'] ?? null;
        $marker->map_location_id = $validated['map_location_id'] ?? null;

        if ($request->filled('ar_marker_patt_content')) {
            if ($marker->ar_marker_patt_path) {
                Storage::disk('public')->delete($marker->ar_marker_patt_path);
            }
            $pattPath = 'ar-markers/'.$validated['ar_marker_id'].'.patt';
            Storage::disk('public')->put($pattPath, $request->input('ar_marker_patt_content'));
            $marker->ar_marker_patt_path = $pattPath;
        }

        $marker->save();

        return redirect()->route('admin.ar-manager')->with('success', 'Marker QR berhasil diperbarui.');
    }

    /**
     * Delete an existing QR Marker.
     */
    public function destroyMarker(int $id): RedirectResponse
    {
        $marker = ArMarker::findOrFail($id);

        if ($marker->ar_marker_patt_path) {
            Storage::disk('public')->delete($marker->ar_marker_patt_path);
        }

        $marker->delete();

        return redirect()->route('admin.ar-manager')->with('success', 'Marker QR berhasil dihapus.');
    }
}
