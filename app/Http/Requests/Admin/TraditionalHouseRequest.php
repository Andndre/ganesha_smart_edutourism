<?php

namespace App\Http\Requests\Admin;

use App\Models\ArModel;
use App\Models\UmkmProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TraditionalHouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'integer', 'min:1', 'max:9999', Rule::unique('traditional_houses')->ignore($this->route('house'))],
            'head_name' => ['required', 'string', 'max:255'],
            'male_count' => ['required', 'integer', 'min:0', 'max:999'],
            'female_count' => ['required', 'integer', 'min:0', 'max:999'],
            'description' => ['nullable', 'array'],
            'description.id' => ['nullable', 'string', 'max:10000'],
            'description.en' => ['nullable', 'string', 'max:10000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery_images' => ['nullable', 'array', 'max:8'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'ar_marker_id' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('traditional_houses')->ignore($this->route('house'))],
            'is_open_to_visitors' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'latitude' => [Rule::requiredIf($this->boolean('is_published')), 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => [Rule::requiredIf($this->boolean('is_published')), 'nullable', 'numeric', 'between:-180,180'],
            'umkm_profile_ids' => [Rule::requiredIf($this->boolean('is_published')), 'array'],
            'umkm_profile_ids.*' => ['integer', 'distinct', 'exists:umkm_profiles,id'],
            'cultural_object_ids' => ['nullable', 'array'],
            'cultural_object_ids.*' => ['integer', 'distinct', 'exists:cultural_objects,id'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->filled('ar_marker_id') && ArModel::where('ar_marker_id', $this->input('ar_marker_id'))->exists()) {
                $validator->errors()->add('ar_marker_id', 'Marker ID is already used by a cultural object.');
            }

            if (! $this->boolean('is_published') || $validator->errors()->has('umkm_profile_ids')) {
                return;
            }

            $ids = $this->input('umkm_profile_ids', []);
            if (! is_array($ids) || ! UmkmProfile::active()->whereIn('id', $ids)->exists()) {
                $validator->errors()->add('umkm_profile_ids', 'A published house needs at least one active UMKM.');
            }
        }];
    }
}
