<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PackageRequest extends FormRequest
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
            'type' => ['nullable', 'in:package,ticket'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_hours' => ['nullable', 'numeric', 'min:0'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'min_capacity' => ['nullable', 'integer', 'min:1'],
            'inclusions' => ['nullable', 'array'],
            'inclusions.en' => ['nullable', 'string'],
            'inclusions.id' => ['nullable', 'string'],
            'exclusions' => ['nullable', 'array'],
            'exclusions.en' => ['nullable', 'string'],
            'exclusions.id' => ['nullable', 'string'],
            'itinerary' => ['nullable', 'array'],
            'itinerary.en' => ['nullable', 'array'],
            'itinerary.en.*.time' => ['nullable', 'string', 'max:100'],
            'itinerary.en.*.title' => ['nullable', 'string', 'max:255'],
            'itinerary.en.*.description' => ['nullable', 'string'],
            'itinerary.en.*.activities' => ['nullable', 'string'],
            'itinerary.id' => ['nullable', 'array'],
            'itinerary.id.*.time' => ['nullable', 'string', 'max:100'],
            'itinerary.id.*.title' => ['nullable', 'string', 'max:255'],
            'itinerary.id.*.description' => ['nullable', 'string'],
            'itinerary.id.*.activities' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
        ];
    }
}
