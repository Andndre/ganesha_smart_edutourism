<?php

namespace App\Http\Requests\Admin;

use App\Http\Concerns\NormalizesMultilingualInput;
use App\Models\CulturalObject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'category' => ['required', 'string', 'in:parahyangan,pawongan,palemahan'],
            'place_type' => ['nullable', 'string', Rule::in(array_keys(CulturalObject::PLACE_TYPES))],
            'is_detail' => ['nullable', 'boolean'],
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
            // mimetypes (content-sniffed) + extensions (filename) instead of `mimes`: .m4a
            // is frequently sniffed as audio/mp4 or video/mp4, which `mimes:m4a` rejects.
            'cultural_audio_file' => ['nullable', 'array'],
            'cultural_audio_file.en' => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/ogg,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,video/mp4', 'extensions:mp3,ogg,wav,m4a', 'max:10240'],
            'cultural_audio_file.id' => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/ogg,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,video/mp4', 'extensions:mp3,ogg,wav,m4a', 'max:10240'],
            'historical_images' => ['nullable', 'array'],
            'historical_images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLocaleField('accessibility_notes');

        // Browser tidak mengirim checkbox yang tidak dicentang, sehingga `is_detail`
        // hilang dari input dan update() tidak menyentuh kolomnya — centangnya jadi
        // mustahil dicabut. Disetel eksplisit di sini supaya berlaku untuk store
        // maupun update sekaligus.
        $this->merge(['is_detail' => $this->boolean('is_detail')]);
    }
}
