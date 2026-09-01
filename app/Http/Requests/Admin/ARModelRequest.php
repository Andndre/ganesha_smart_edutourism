<?php

namespace App\Http\Requests\Admin;

use App\Http\Concerns\NormalizesMultilingualInput;
use Illuminate\Foundation\Http\FormRequest;

class ARModelRequest extends FormRequest
{
    use NormalizesMultilingualInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $modelId = $this->route('id');

        $rules = [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'ar_marker_patt_content' => ['nullable', 'string'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
            // mimetypes (content-sniffed) + extensions (filename) instead of `mimes`: .m4a
            // is frequently sniffed as audio/mp4 or video/mp4, which `mimes:m4a` rejects.
            'audio_narration_file' => ['nullable', 'array'],
            'audio_narration_file.en' => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/ogg,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,video/mp4', 'extensions:mp3,ogg,wav,m4a', 'max:10240'],
            'audio_narration_file.id' => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/ogg,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,video/mp4', 'extensions:mp3,ogg,wav,m4a', 'max:10240'],
        ];

        // Store: require model_3d_file unless tmp path provided
        // Update: optional
        $rules['model_3d_file'] = $modelId
            ? ['nullable', 'file', 'max:20480']
            : ['required_without:tmp_model_3d_path', 'file', 'max:20480'];

        // Unique ar_marker_id — exclude current model on update
        $rules['ar_marker_id'] = $modelId
            ? ['nullable', 'string', 'max:255', 'unique:ar_models,ar_marker_id,'.$modelId]
            : ['nullable', 'string', 'max:255', 'unique:ar_models,ar_marker_id'];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLocaleFields(['name', 'description']);
    }
}
