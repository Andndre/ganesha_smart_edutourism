<?php

namespace App\Http\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesArAssets
{
    /** @return array<string, array<int, mixed>> */
    protected function arAssetRules(?int $modelId = null): array
    {
        return [
            'model_3d_file' => ['nullable', 'file', 'extensions:glb', 'max:10240'],
            'model_3d_usdz_file' => ['nullable', 'file', 'extensions:usdz', 'max:51200'],
            'tmp_model_3d_path' => ['nullable', 'string', 'regex:/^[a-f0-9-]{36}\\.glb$/i'],
            'tmp_model_3d_usdz_path' => ['nullable', 'string', 'regex:/^[a-f0-9-]{36}\\.usdz$/i'],
            'ar_marker_id' => ['nullable', 'string', 'max:255', Rule::unique('ar_models', 'ar_marker_id')->ignore($modelId)],
            'ar_marker_patt_content' => ['nullable', 'string', 'max:200000'],
            'thumbnail_data' => ['nullable', 'string', 'max:2097152', 'regex:/^data:image\\/(png|webp|jpeg);base64,/'],
        ];
    }
}
