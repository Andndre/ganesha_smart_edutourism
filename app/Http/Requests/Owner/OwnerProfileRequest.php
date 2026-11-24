<?php

namespace App\Http\Requests\Owner;

use App\Models\UmkmProfile;
use Illuminate\Foundation\Http\FormRequest;

class OwnerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $profile = $this->user()?->isAdmin()
            ? UmkmProfile::find($this->query('umkm_profile_id') ?? $this->session()->get('admin_view_umkm_profile_id'))
            : $this->user()?->umkmProfile()->first();
        $rules = [
            'profile_type' => ['prohibited'],
            'traditional_house_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'owner_user_id' => ['prohibited'],
            'business_name' => ['required', 'array'],
            'business_name.en' => ['required', 'string', 'max:255'],
            'business_name.id' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
        ];
        if ($profile?->isHouse()) {
            $rules['business_name'] = ['prohibited'];
            unset($rules['business_name.en'], $rules['business_name.id']);
        }

        return $rules;
    }
}
