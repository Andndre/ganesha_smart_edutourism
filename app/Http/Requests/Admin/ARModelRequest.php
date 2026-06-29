<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ARModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $modelId = $this->route('id');

        $rules = [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'ar_marker_patt_content' => ['nullable', 'string'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
            'audio_narration_file' => ['nullable', 'file', 'max:10240'],
        ];

        // Store: require model_3d_file unless tmp path provided
        // Update: optional
        $rules['model_3d_file'] = $modelId
            ? ['nullable', 'file', 'max:20480']
            : ['required_without:tmp_model_3d_path', 'file', 'max:20480'];

        // Unique ar_marker_id — exclude current model on update
        $rules['ar_marker_id'] = $modelId
            ? ['nullable', 'string', 'max:255', 'unique:ar_models,ar_marker_id,'.$modelId]
            : ['nullable', 'string', 'max:255', 'unique:ar_models,ar_marker_id'];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLocaleFields(['name', 'description']);
    }

    private function normalizeLocaleFields(array $fields): void
    {
        foreach ($fields as $field) {
            $value = $this->input($field);
            if (is_string($value) && ! empty($value)) {
                $this->merge([$field => ['en' => $value, 'id' => $value]]);
            }
        }
    }
}
