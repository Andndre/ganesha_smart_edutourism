<?php

namespace App\Http\Requests\Admin;

use App\Http\Concerns\NormalizesMultilingualInput;
use App\Http\Concerns\ValidatesArAssets;
use App\Models\ArModel;
use App\Models\CulturalObject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CulturalObjectRequest extends FormRequest
{
    use NormalizesMultilingualInput;
    use ValidatesArAssets;

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
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('cultural_objects', 'id')->whereNull('parent_id'),
                // Induk harus objek root: hierarki sengaja dua level (lihat parentOptions()).
                Rule::notIn([$this->route('id')]),
            ],
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
            ...$this->arAssetRules(ArModel::where('cultural_object_id', $this->route('id'))->value('id')),
            'remove_ar_model' => ['nullable', 'boolean'],
            // mimetypes (content-sniffed) + extensions (filename) instead of `mimes`: .m4a
            // is frequently sniffed as audio/mp4 or video/mp4, which `mimes:m4a` rejects.
            // video/3gpp and audio/3gpp supported for mobile phone/voice recordings.
            'cultural_audio_file' => ['nullable', 'array'],
            'cultural_audio_file.en' => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/ogg,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,video/mp4,video/3gpp,audio/3gpp', 'extensions:mp3,ogg,wav,m4a,3gp,3gpp', 'max:10240'],
            'cultural_audio_file.id' => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/ogg,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,video/mp4,video/3gpp,audio/3gpp', 'extensions:mp3,ogg,wav,m4a,3gp,3gpp', 'max:10240'],
            'historical_images' => ['nullable', 'array', 'max:20'],
            'historical_images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'removed_historical_images' => ['nullable', 'array', 'max:20'],
            'removed_historical_images.*' => ['required', 'string', 'distinct', Rule::in($this->existingImages())],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $validator->errors()->hasAny(['historical_images', 'removed_historical_images'])
                && ! $validator->errors()->has('removed_historical_images.*')) {
                $remaining = array_diff($this->existingImages(), $this->input('removed_historical_images') ?? []);
                if (count($remaining) + count($this->file('historical_images', [])) > 20) {
                    $validator->errors()->add('historical_images', 'Maksimal 20 foto per objek. Hapus beberapa foto sebelum menambahkan foto baru.');
                }
            }
            $hasGlb = $this->hasFile('model_3d_file') || $this->filled('tmp_model_3d_path');
            $hasUsdz = $this->hasFile('model_3d_usdz_file') || $this->filled('tmp_model_3d_usdz_path');
            $existing = $this->route('id') ? ArModel::where('cultural_object_id', $this->route('id'))->first() : null;
            if ($this->boolean('remove_ar_model') && ($hasGlb || $hasUsdz)) {
                $validator->errors()->add('remove_ar_model', 'Batalkan penghapusan model sebelum mengunggah file pengganti.');
            } elseif (($hasUsdz || $this->filled('ar_marker_id')) && ! $hasGlb && ! $existing) {
                $validator->errors()->add('model_3d_file', 'Unggah file GLB untuk menambahkan model 3D.');
            }
        }];
    }

    /** @return list<string> */
    private function existingImages(): array
    {
        return $this->route('id') ? (CulturalObject::find($this->route('id'))?->historical_images ?? []) : [];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLocaleField('accessibility_notes');

        // Browser tidak mengirim checkbox yang tidak dicentang, sehingga `is_detail`
        // hilang dari input dan update() tidak menyentuh kolomnya — centangnya jadi
        // mustahil dicabut. Disetel eksplisit di sini supaya berlaku untuk store
        // maupun update sekaligus.
        $this->merge(['is_detail' => $this->boolean('is_detail')]);

        // Select kosong mengirim string "", bukan null, dan FK akan menolaknya.
        if ($this->input('parent_id') === '') {
            $this->merge(['parent_id' => null]);
        }
    }
}
