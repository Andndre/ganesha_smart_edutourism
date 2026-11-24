<?php

namespace App\Http\Requests\Owner;

use App\Http\Requests\Admin\TraditionalHouseRequest;
use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;

class OwnerHouseRequest extends TraditionalHouseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->house() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        foreach (['owner_user_id', 'user_id', 'profile_type', 'traditional_house_id', 'umkm_profile_ids', 'owner_mode', 'new_owner_name', 'new_owner_email', 'new_owner_phone', 'password', 'password_confirmation', 'creation_flow'] as $field) {
            $rules[$field] = ['prohibited'];
        }
        unset($rules['umkm_profile_ids.*']);

        return $rules;
    }

    public function house(): ?TraditionalHouse
    {
        $user = $this->user();
        if ($user?->isAdmin()) {
            $id = $this->query('umkm_profile_id') ?? $this->session()->get('admin_view_umkm_profile_id');
            $profile = $id ? UmkmProfile::find($id) : null;
        } else {
            $profile = $user?->isUmkmOwner() ? $user->umkmProfile()->first() : null;
        }

        return $profile?->managedHouse();
    }

    protected function houseForValidation(): ?TraditionalHouse
    {
        return $this->house();
    }

    protected function profileIds(): array
    {
        return $this->house()?->umkmProfiles()->pluck('id')->all() ?? [];
    }
}
