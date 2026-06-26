<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\NormalizesMultilingualInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ARModelRequest;
use App\Models\ArModel;
use App\Services\TusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ARManagerController extends Controller
{
    use NormalizesMultilingualInput;

    public function index(): View
    {
        $models = ArModel::with('mapLocation.locationable')->orderBy('name')->get();

        return view('admin.ar-manager.index', compact('models'));
    }

    public function storeModel(ARModelRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $modelData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'ar_marker_id' => $validated['ar_marker_id'] ?? null,
        ];

        if ($tmpUuid = $request->input('tmp_model_3d_path')) {
            $modelData['model_3d_path'] = TusService::moveFromTemp($tmpUuid, 'models');
        } elseif ($request->hasFile('model_3d_file')) {
            $modelData['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
        }

        if ($tmpUuid = $request->input('tmp_model_3d_usdz_path')) {
            $modelData['model_3d_usdz_path'] = TusService::moveFromTemp($tmpUuid, 'models_usdz', Str::random(40).'.usdz');
        } elseif ($request->hasFile('model_3d_usdz_file')) {
            $file = $request->file('model_3d_usdz_file');
            $modelData['model_3d_usdz_path'] = $file->storeAs('models_usdz', Str::random(40).'.usdz', 'public');
        }

        if ($tmpUuid = $request->input('tmp_audio_narration_path')) {
            $modelData['audio_narration_path'] = TusService::moveFromTemp($tmpUuid, 'audio');
        } elseif ($request->hasFile('audio_narration_file')) {
            $modelData['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
        }

        if ($request->filled('ar_marker_patt_content') && ! empty($validated['ar_marker_id'])) {
            $pattPath = 'ar-markers/'.$validated['ar_marker_id'].'.patt';
            Storage::disk('public')->put($pattPath, $request->input('ar_marker_patt_content'));
            $modelData['ar_marker_patt_path'] = $pattPath;
        }

        $model = ArModel::create($modelData);
        $this->handleThumbnail($request, $model);

        if ($request->wantsJson() || $request->ajax()) {
            $name = $model->getTranslations('name');

            return response()->json([
                'success' => true,
                'message' => __('Model 3D berhasil ditambahkan.'),
                'model' => [
                    'id' => (string) $model->id,
                    'name' => $name,
                    'displayName' => $name[app()->getLocale()] ?? $name['en'] ?? $name['id'] ?? '',
                    'ar_marker_id' => $model->ar_marker_id,
                    'thumbnail_path' => $model->thumbnail_path,
                    'model_3d_path' => $model->model_3d_path,
                    'isTaken' => false,
                ],
            ]);
        }

        if ($request->input('redirect_to') === 'map-manager') {
            return redirect()->route('admin.map-manager', ['select_model' => $model->id])
                ->with('success', __('Model 3D berhasil ditambahkan.'));
        }

        return redirect()->route('admin.ar-manager')->with('success', __('Model 3D berhasil ditambahkan.'));
    }

    public function updateModel(ARModelRequest $request, int $id): RedirectResponse
    {
        $model = ArModel::findOrFail($id);

        $validated = $request->validated();

        $model->name = $validated['name'];
        $model->description = $validated['description'] ?? null;
        $model->ar_marker_id = $validated['ar_marker_id'] ?? null;

        if ($tmpUuid = $request->input('tmp_model_3d_path')) {
            if ($model->model_3d_path) {
                Storage::disk('public')->delete($model->model_3d_path);
            }
            $model->model_3d_path = TusService::moveFromTemp($tmpUuid, 'models');
        } elseif ($request->hasFile('model_3d_file')) {
            if ($model->model_3d_path) {
                Storage::disk('public')->delete($model->model_3d_path);
            }
            $model->model_3d_path = $request->file('model_3d_file')->store('models', 'public');
        }

        if ($tmpUuid = $request->input('tmp_model_3d_usdz_path')) {
            if ($model->model_3d_usdz_path) {
                Storage::disk('public')->delete($model->model_3d_usdz_path);
            }
            $model->model_3d_usdz_path = TusService::moveFromTemp($tmpUuid, 'models_usdz', Str::random(40).'.usdz');
        } elseif ($request->hasFile('model_3d_usdz_file')) {
            if ($model->model_3d_usdz_path) {
                Storage::disk('public')->delete($model->model_3d_usdz_path);
            }
            $model->model_3d_usdz_path = $request->file('model_3d_usdz_file')
                ->storeAs('models_usdz', Str::random(40).'.usdz', 'public');
        }

        if ($tmpUuid = $request->input('tmp_audio_narration_path')) {
            if ($model->audio_narration_path) {
                Storage::disk('public')->delete($model->audio_narration_path);
            }
            $model->audio_narration_path = TusService::moveFromTemp($tmpUuid, 'audio');
        } elseif ($request->hasFile('audio_narration_file')) {
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
        $this->handleThumbnail($request, $model);

        return redirect()->route('admin.ar-manager')->with('success', __('Model 3D berhasil diperbarui.'));
    }

    public function destroyModel(int $id): RedirectResponse
    {
        $model = ArModel::findOrFail($id);

        foreach (['model_3d_path', 'model_3d_usdz_path', 'audio_narration_path', 'ar_marker_patt_path', 'thumbnail_path'] as $file) {
            if ($model->$file) {
                Storage::disk('public')->delete($model->$file);
            }
        }

        $model->delete();

        return redirect()->route('admin.ar-manager')->with('success', __('Model 3D berhasil dihapus.'));
    }

    private function handleThumbnail(Request $request, ArModel $model): void
    {
        if (! $request->filled('thumbnail_data')) {
            return;
        }

        if ($model->thumbnail_path) {
            Storage::disk('public')->delete($model->thumbnail_path);
        }

        $data = $request->input('thumbnail_data');
        $data = substr($data, strpos($data, ',') + 1);
        $path = 'thumbnails/'.$model->id.'-'.time().'.png';
        Storage::disk('public')->put($path, base64_decode($data));
        $model->thumbnail_path = $path;
        $model->save();
    }
}
