<?php

namespace App\Http\Requests\Owner;

use App\Http\Concerns\NormalizesMultilingualInput;
use Illuminate\Foundation\Http\FormRequest;

class OwnerLocationRequest extends FormRequest
{
    use NormalizesMultilingualInput;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
}
