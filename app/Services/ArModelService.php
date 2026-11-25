<?php

namespace App\Services;

use App\Models\ArModel;
use App\Models\CulturalObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ArModelService
{
    public function __construct(private UnusedArAssetCleaner $cleaner, private ImageOptimizerService $images) {}

    public function save(CulturalObject $object, Request $request): ArModel
    {
        $newPaths = [];

        try {
            return DB::transaction(function () use ($object, $request, &$newPaths) {
                CulturalObject::whereKey($object->id)->lockForUpdate()->firstOrFail();
                $model = $object->arModel()->first();
                $model ??= new ArModel(['cultural_object_id' => $object->id]);
                $oldPaths = [];

                foreach ([
                    ['model_3d_file', 'tmp_model_3d_path', 'model_3d_path', 'models', 'glb'],
                    ['model_3d_usdz_file', 'tmp_model_3d_usdz_path', 'model_3d_usdz_path', 'models_usdz', 'usdz'],
                ] as [$fileKey, $tempKey, $column, $directory, $extension]) {
                    if (! $request->filled($tempKey) && ! $request->hasFile($fileKey)) {
                        continue;
                    }
                    $path = $request->filled($tempKey)
                        ? TusService::moveFromTemp($request->string($tempKey)->toString(), $directory, Str::uuid().'.'.$extension)
                        : $request->file($fileKey)->storeAs($directory, Str::uuid().'.'.$extension, 'public');
                    $newPaths[] = $path;
                    $oldPaths[] = $model->$column;
                    $model->$column = $path;
                }

                if ($request->exists('ar_marker_id')) {
                    $marker = $request->input('ar_marker_id') ?: null;
                    if ($marker !== $model->ar_marker_id || $request->filled('ar_marker_patt_content')) {
                        $oldPaths[] = $model->ar_marker_patt_path;
                        $model->ar_marker_patt_path = null;
                    }
                    $model->ar_marker_id = $marker;
                    if ($marker && $request->filled('ar_marker_patt_content')) {
                        $path = 'ar-markers/'.Str::uuid().'.patt';
                        Storage::disk('public')->put($path, $request->string('ar_marker_patt_content')->toString());
                        $newPaths[] = $path;
                        $model->ar_marker_patt_path = $path;
                    }
                }

                if ($request->filled('thumbnail_data')) {
                    $binary = base64_decode(explode(',', $request->input('thumbnail_data'), 2)[1] ?? '', true);
                    if ($binary === false || $binary === '') {
                        throw ValidationException::withMessages(['thumbnail_data' => 'Pratinjau tidak valid. Pilih ulang file GLB.']);
                    }
                    $oldPaths[] = $model->thumbnail_path;
                    $model->thumbnail_path = $this->images->optimizeBinary($binary, 'thumbnails', 600, 600, 80, customFilename: Str::uuid().'.webp');
                    $newPaths[] = $model->thumbnail_path;
                }

                $model->save();
                DB::afterCommit(fn () => $this->cleaner->deleteUnused($oldPaths));

                return $model->load('culturalObject');
            });
        } catch (Throwable $exception) {
            $this->cleaner->deleteUnused($newPaths);
            throw $exception;
        }
    }

    public function delete(ArModel $model): void
    {
        $paths = [$model->model_3d_path, $model->model_3d_usdz_path, $model->thumbnail_path, $model->ar_marker_patt_path];
        DB::transaction(function () use ($model, $paths) {
            $model->delete();
            DB::afterCommit(fn () => $this->cleaner->deleteUnused($paths));
        });
    }
}
