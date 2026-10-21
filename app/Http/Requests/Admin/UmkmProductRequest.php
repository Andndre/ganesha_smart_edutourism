<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UmkmProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('variants') && is_array($this->variants)) {
            $variants = $this->variants;
            foreach ($variants as $i => $v) {
                if (isset($v['label']) && is_array($v['label'])) {
                    $idVal = trim((string) ($v['label']['id'] ?? ''));
                    $enVal = trim((string) ($v['label']['en'] ?? ''));
                    if ($enVal === '' && $idVal !== '') {
                        $variants[$i]['label']['en'] = $idVal;
                    }
                }
            }
            $this->merge(['variants' => $variants]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'umkm_profile_id' => ['required', 'exists:umkm_profiles,id'],
            'umkm_product_category_id' => ['nullable', 'exists:umkm_product_categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'variants' => ['nullable', 'array', 'max:20'],
            'variants.*.label' => ['required_with:variants', 'array'],
            'variants.*.label.id' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.label.en' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.is_active' => ['nullable', 'boolean'],
            'variants.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        if ($this->route('id')) {
            $rules['is_active'] = ['nullable', 'boolean'];
        }

        return $rules;
    }
}
