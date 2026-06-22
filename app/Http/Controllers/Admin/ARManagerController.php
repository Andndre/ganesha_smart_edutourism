<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ARManagerController extends Controller
{
    public function index(): View
    {
        $models = ArModel::with('mapLocation.locationable')->orderBy('name')->get();

        return view('admin.ar-manager.index', compact('models'));
    }

    public function storeModel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ar_marker_id' => ['nullable', 'string', 'max:255', 'unique:ar_models,ar_marker_id'],
            'ar_marker_patt_content' => ['nullable', 'string'],
            'model_3d_file' => ['required', 'file', 'max:20480'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
            'audio_narration_file' => ['nullable', 'file', 'max:10240'],
        ]);

        $modelData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'ar_marker_id' => $validated['ar_marker_id'] ?? null,
        ];

        if ($request->hasFile('model_3d_file')) {
            $modelData['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        }

        if ($request->hasFile('model_3d_usdz_file')) {
            $file = $request->file('model_3d_usdz_file');
            $modelData['model_3d_usdz_path'] = $file->storeAs('models_usdz', Str::random(40).'.usdz', 'public');
        }

        if ($request->hasFile('audio_narration_file')) {
            $modelData['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
        }

        if ($request->filled('ar_marker_patt_content') && ! empty($validated['ar_marker_id'])) {
            $pattPath = 'ar-markers/'.$validated['ar_marker_id'].'.patt';
            Storage::disk('public')->put($pattPath, $request->input('ar_marker_patt_content'));
            $modelData['ar_marker_patt_path'] = $pattPath;
        }

        ArModel::create($modelData);

        return redirect()->route('admin.ar-manager')->with('success', 'Model 3D berhasil ditambahkan.');
    }

    public function updateModel(Request $request, int $id): RedirectResponse
    {
        $model = ArModel::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ar_marker_id' => ['nullable', 'string', 'max:255', 'unique:ar_models,ar_marker_id,'.$id],
            'ar_marker_patt_content' => ['nullable', 'string'],
            'model_3d_file' => ['nullable', 'file', 'max:20480'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
            'audio_narration_file' => ['nullable', 'file', 'max:10240'],
        ]);

        $model->name = $validated['name'];
        $model->description = $validated['description'] ?? null;
        $model->ar_marker_id = $validated['ar_marker_id'] ?? null;

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
            $model->model_3d_usdz_path = $request->file('model_3d_usdz_file')
                ->storeAs('models_usdz', Str::random(40).'.usdz', 'public');
        }

        if ($request->hasFile('audio_narration_file')) {
            if ($model->audio_narration_path) {
                Storage::disk('public')->delete($model->audio_narration_path);
            }
            $model->audio_narration_path = $request->file('audio_narration_file')->store('audio', 'public');
        }

        if ($request->filled('ar_marker_patt_content') && ! empty($validated['ar_marker_id'])) {
            if ($model->ar_marker_patt_path) {
                Storage::disk('public')->delete($model->ar_marker_patt_path);
            }
            $pattPath = 'ar-markers/'.$validated['ar_marker_id'].'.patt';
            Storage::disk('public')->put($pattPath, $request->input('ar_marker_patt_content'));
            $model->ar_marker_patt_path = $pattPath;
        } elseif (empty($validated['ar_marker_id'])) {
            // Marker ID dihapus, hapus file patt juga
            if ($model->ar_marker_patt_path) {
                Storage::disk('public')->delete($model->ar_marker_patt_path);
            }
            $model->ar_marker_patt_path = null;
        }

        $model->save();

        return redirect()->route('admin.ar-manager')->with('success', 'Model 3D berhasil diperbarui.');
    }

    public function destroyModel(int $id): RedirectResponse
    {
        $model = ArModel::findOrFail($id);

        foreach (['model_3d_path', 'model_3d_usdz_path', 'audio_narration_path', 'ar_marker_patt_path'] as $file) {
            if ($model->$file) {
                Storage::disk('public')->delete($model->$file);
            }
        }

        $model->delete();

        return redirect()->route('admin.ar-manager')->with('success', 'Model 3D berhasil dihapus.');
    }
}
