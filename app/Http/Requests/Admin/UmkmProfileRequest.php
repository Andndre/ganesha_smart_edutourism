<?php

namespace App\Http\Requests\Admin;

use App\Enums\UmkmProfileType;
use App\Http\Concerns\NormalizesMultilingualInput;
use App\Models\UmkmProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UmkmProfileRequest extends FormRequest
{
    use NormalizesMultilingualInput;

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $profile = $this->route('id') ? UmkmProfile::find($this->route('id')) : null;
        $isHouse = $this->input('profile_type', $profile?->profile_type?->value) === 'house';
        $rules = [
            'profile_type' => ['sometimes', Rule::enum(UmkmProfileType::class)],
            'traditional_house_id' => [$isHouse ? 'required' : 'nullable', 'integer', 'exists:traditional_houses,id'],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'umkm_owner')],
            'business_name' => ['required', 'array'],
            'business_name.en' => ['required', 'string', 'max:255'],
            'business_name.id' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'ar_marker_id' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'is_active' => ['nullable', 'boolean'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'is_accessible' => ['nullable', 'boolean'],
            'accessibility_notes' => ['nullable', 'array'],
            'accessibility_notes.en' => ['nullable', 'string'],
            'accessibility_notes.id' => ['nullable', 'string'],
        ];
        if ($isHouse) {
            $rules['business_name'] = ['prohibited'];
            unset($rules['business_name.en'], $rules['business_name.id']);
        }
        if ($profile?->isHouse()) {
            $rules['profile_type'][] = Rule::in(['house']);
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLocaleField('accessibility_notes');
    }
}
