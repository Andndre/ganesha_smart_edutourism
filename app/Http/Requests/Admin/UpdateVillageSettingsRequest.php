<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVillageSettingsRequest extends FormRequest
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
            'open_time' => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i', 'after:open_time'],
            'house_cultural_object_id' => ['sometimes', 'nullable', 'integer', Rule::exists('cultural_objects', 'id')->whereNull('parent_id')],
            'featured_cultural_object_id' => ['sometimes', 'nullable', 'integer', Rule::exists('cultural_objects', 'id')->whereNull('parent_id')],
        ];
    }

    public function messages(): array
    {
        return [
            'house_cultural_object_id.exists' => 'Pilih objek budaya induk yang masih tersedia untuk seluruh rumah.',
            'featured_cultural_object_id.exists' => 'Pilih objek budaya induk yang masih tersedia sebagai unggulan.',
        ];
    }
}
