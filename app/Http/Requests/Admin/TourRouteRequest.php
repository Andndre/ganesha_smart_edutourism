<?php

namespace App\Http\Requests\Admin;

use App\Http\Concerns\NormalizesMultilingualInput;
use App\Models\TourRoute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TourRouteRequest extends FormRequest
{
    use NormalizesMultilingualInput;

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
            'difficulty' => ['required', 'string', 'in:Mudah,Sedang,Sulit,Edukasi,Alam,Belanja,Difabel,easy,moderate,challenging'],
            'gamification_key' => ['nullable', 'string', Rule::in(TourRoute::GAMIFICATION_KEYS)],
            'estimated_duration_minutes' => ['required', 'integer', 'min:1'],
            'distance_meters' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'points' => ['nullable', 'array'],
            'points.*.locationable_type' => ['required', 'string'],
            'points.*.locationable_id' => ['required', 'integer'],
            'points.*.estimated_visit_minutes' => ['nullable', 'integer', 'min:1'],
            'points.*.storytelling_content' => ['nullable', 'array'],
            'points.*.storytelling_content.en' => ['nullable', 'string'],
            'points.*.storytelling_content.id' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLocaleFields(['name', 'description']);

        // Empty "Tidak ada" option -> null, so the column stays clean.
        if ($this->input('gamification_key') === '') {
            $this->merge(['gamification_key' => null]);
        }

        // Normalize points[].storytelling_content from string to locale array
        if ($this->has('points') && \is_array($this->input('points'))) {
            $points = $this->input('points');
            foreach ($points as $index => $point) {
                if (isset($point['storytelling_content']) && \is_string($point['storytelling_content'])) {
                    $points[$index]['storytelling_content'] = [
                        'en' => $point['storytelling_content'],
                        'id' => $point['storytelling_content'],
                    ];
                }
            }
            $this->merge(['points' => $points]);
        }
    }
}
