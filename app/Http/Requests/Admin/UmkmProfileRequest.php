<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UmkmProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
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
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLocaleField('accessibility_notes');
    }

    private function normalizeLocaleField(string $field): void
    {
        $value = $this->input($field);
        if (is_string($value) && ! empty($value)) {
            $this->merge([$field => ['en' => $value, 'id' => $value]]);
        }
    }
}
