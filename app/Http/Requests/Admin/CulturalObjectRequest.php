<?php

namespace App\Http\Requests\Admin;

use App\Http\Concerns\NormalizesMultilingualInput;
use Illuminate\Foundation\Http\FormRequest;

class CulturalObjectRequest extends FormRequest
{
    use NormalizesMultilingualInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:temple,house,craft,tradition'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'short_description' => ['nullable', 'array'],
            'short_description.en' => ['nullable', 'string', 'max:255'],
            'short_description.id' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'ar_marker_id' => ['nullable', 'string', 'max:255'],
            'ar_marker_patt_content' => ['nullable', 'string'],
            'ar_model_id' => ['nullable', 'string'],
            'new_model_name' => ['nullable', 'array'],
            'new_model_name.en' => ['nullable', 'string', 'max:255'],
            'new_model_name.id' => ['nullable', 'string', 'max:255'],
            'new_model_description' => ['nullable', 'array'],
            'new_model_description.en' => ['nullable', 'string'],
            'new_model_description.id' => ['nullable', 'string'],
            'model_3d_file' => ['nullable', 'file', 'max:20480'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
            'audio_narration_file' => ['nullable', 'array'],
            'audio_narration_file.en' => ['nullable', 'file', 'mimes:mp3,ogg,wav,m4a', 'max:10240'],
            'audio_narration_file.id' => ['nullable', 'file', 'mimes:mp3,ogg,wav,m4a', 'max:10240'],
            'cultural_audio_file' => ['nullable', 'array'],
            'cultural_audio_file.en' => ['nullable', 'file', 'mimes:mp3,ogg,wav,m4a', 'max:10240'],
            'cultural_audio_file.id' => ['nullable', 'file', 'mimes:mp3,ogg,wav,m4a', 'max:10240'],
            'historical_images' => ['nullable', 'array'],
            'historical_images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLocaleField('accessibility_notes');
    }
}
