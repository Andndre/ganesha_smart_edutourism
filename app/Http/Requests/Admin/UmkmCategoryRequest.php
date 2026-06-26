<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UmkmCategoryRequest extends FormRequest
{
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
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'model_3d_file' => ['nullable', 'file', 'max:20480'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
        ];
    }
}
