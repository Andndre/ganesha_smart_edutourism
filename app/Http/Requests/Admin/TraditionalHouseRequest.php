<?php

namespace App\Http\Requests\Admin;

use App\Models\ArModel;
use App\Models\TraditionalHouse;
use App\Models\UmkmProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TraditionalHouseRequest extends FormRequest
{
    protected function houseForValidation(): ?TraditionalHouse
    {
        return $this->route('house');
    }

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
            'number' => ['required', 'integer', 'min:1', 'max:9999', Rule::unique('traditional_houses')->ignore($this->houseForValidation())],
            'owner_user_id' => [Rule::excludeIf($this->has('owner_mode') && $this->input('owner_mode') !== 'existing'), 'nullable', 'integer', Rule::exists('users', 'id')->where('role', 'umkm_owner')],
            'activate_house_umkm' => ['nullable', 'boolean'],
            'creation_flow' => ['sometimes', 'in:guided'],
            'owner_mode' => ['sometimes', 'in:later,existing,new'],
            'new_owner_name' => ['exclude_unless:owner_mode,new', 'required', 'string', 'max:255'],
            'new_owner_email' => ['exclude_unless:owner_mode,new', 'required', 'email', 'max:255', Rule::unique('users', 'email')],
            'new_owner_phone' => ['exclude_unless:owner_mode,new', 'nullable', 'string', 'max:50'],
            'password' => ['exclude_unless:owner_mode,new', 'required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['exclude_unless:owner_mode,new', 'required', 'string'],
            'head_name' => ['required', 'string', 'max:255'],
            'male_count' => [$this->houseForValidation() ? 'required' : 'nullable', 'integer', 'min:0', 'max:999'],
            'female_count' => [$this->houseForValidation() ? 'required' : 'nullable', 'integer', 'min:0', 'max:999'],
            'description' => ['nullable', 'array'],
            'description.id' => ['nullable', 'string', 'max:10000'],
            'description.en' => ['nullable', 'string', 'max:10000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'gallery_images' => ['nullable', 'array', 'max:8'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_gallery_images' => ['nullable', 'array'],
            'remove_gallery_images.*' => ['string', 'distinct', Rule::in($this->houseForValidation()?->gallery ?? [])],
            'is_accessible' => ['nullable', 'boolean'],
            'accessibility_notes' => ['nullable', 'array'],
            'accessibility_notes.id' => ['nullable', 'string', 'max:1000'],
            'accessibility_notes.en' => ['nullable', 'string', 'max:1000'],
            'ar_marker_id' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('traditional_houses')->ignore($this->houseForValidation())],
            'is_open_to_visitors' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'latitude' => [Rule::requiredIf($this->boolean('is_published')), 'required_with:longitude', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => [Rule::requiredIf($this->boolean('is_published')), 'required_with:latitude', 'nullable', 'numeric', 'between:-180,180'],
            'umkm_profile_ids' => ['prohibited'],
            'cultural_object_ids' => ['prohibited'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('owner_mode') === 'existing' && ! $this->filled('owner_user_id')) {
                $validator->errors()->add('owner_user_id', 'Pilih akun pengelola atau pilih Atur nanti.');
            }
            if ($this->user()?->isAdmin() && $this->input('owner_mode', 'existing') === 'existing' && $this->filled('owner_user_id') && ! $validator->errors()->has('owner_user_id')) {
                $profile = UmkmProfile::where('user_id', $this->integer('owner_user_id'))->first();
                if ($profile && (! $profile->isHouse() || $profile->traditional_house_id !== $this->houseForValidation()?->id)) {
                    $validator->errors()->add('owner_user_id', 'Akun ini sudah mengelola '.($profile->isHouse() ? 'rumah lain' : $profile->getTranslation('business_name', 'id')).'. Pilih akun lain atau buat akun baru.');
                }
            }
            if (! $validator->errors()->has('remove_gallery_images') && ! $validator->errors()->has('remove_gallery_images.*')
                && ! $validator->errors()->has('gallery_images') && ! $validator->errors()->has('gallery_images.*')) {
                $gallery = array_diff($this->houseForValidation()?->gallery ?? [], $this->input('remove_gallery_images', []));
                if (count($gallery) + count($this->file('gallery_images', [])) > 8) {
                    $validator->errors()->add('gallery_images', 'Galeri dapat berisi maksimal 8 foto. Hapus foto lama sebelum menambah foto baru.');
                }
            }
            if (! $validator->errors()->has('ar_marker_id') && $this->filled('ar_marker_id') && ArModel::where('ar_marker_id', $this->input('ar_marker_id'))->exists()) {
                $validator->errors()->add('ar_marker_id', 'ID penanda sudah digunakan oleh objek budaya.');
            }

            if (! $this->boolean('is_published') || $validator->errors()->has('umkm_profile_ids')) {
                return;
            }
            if (! $this->houseForValidation()) {
                return;
            }
            if ($this->boolean('activate_house_umkm') && $this->houseForValidation()->umkmProfile()->where('profile_type', 'house')->exists()) {
                return;
            }

            if ($this->user()?->isAdmin() && $this->filled('owner_user_id')) {
                $ownerProfile = UmkmProfile::where('user_id', $this->integer('owner_user_id'))->first();
                if (! $ownerProfile || $ownerProfile->is_active) {
                    return;
                }
            }
            if (! $this->houseForValidation()->umkmProfile()->active()->exists()) {
                $validator->errors()->add('is_published', 'Aktifkan profil UMKM Rumah sebelum menampilkan rumah untuk pengunjung.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'number.unique' => 'Rumah No. :input sudah terdaftar. Buka rumah tersebut untuk mengubah datanya.',
            'cultural_object_ids.prohibited' => 'Ruang budaya mengikuti pengaturan desa dan tidak perlu dipilih per rumah.',
            'umkm_profile_ids.prohibited' => 'Satu rumah hanya memiliki satu profil UMKM yang terhubung otomatis. Tidak perlu memilih usaha tambahan.',
        ];
    }
}
