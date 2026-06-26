<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CulturalObjectRequest extends FormRequest
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
            'category' => ['required', 'string', 'in:temple,house,craft,tradition'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'short_description' => ['nullable', 'array'],
            'short_description.en' => ['nullable', 'string', 'max:255'],
            'short_description.id' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'ar_marker_id' => ['nullable', 'string', 'max:255'],
            'ar_marker_patt_content' => ['nullable', 'string'],
            'ar_model_id' => ['nullable', 'string'],
            'new_model_name' => ['nullable', 'array'],
            'new_model_name.en' => ['nullable', 'string', 'max:255'],
            'new_model_name.id' => ['nullable', 'string', 'max:255'],
            'new_model_description' => ['nullable', 'array'],
            'new_model_description.en' => ['nullable', 'string'],
            'new_model_description.id' => ['nullable', 'string'],
            'model_3d_file' => ['nullable', 'file', 'max:20480'],
            'model_3d_usdz_file' => ['nullable', 'file', 'max:51200'],
            'audio_narration_file' => ['nullable', 'file', 'max:10240'],
            'historical_images' => ['nullable', 'array'],
            'historical_images.*' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'has_quiz' => ['nullable', 'boolean'],
            'quiz_question' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_question.*' => ['required_if:has_quiz,1', 'array'],
            'quiz_question.*.en' => ['required_if:has_quiz,1', 'string'],
            'quiz_question.*.id' => ['required_if:has_quiz,1', 'string'],
            'quiz_option_a' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_a.*' => ['required_if:has_quiz,1', 'string'],
            'quiz_option_b' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_b.*' => ['required_if:has_quiz,1', 'string'],
            'quiz_option_c' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_c.*' => ['required_if:has_quiz,1', 'string'],
            'quiz_option_d' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_option_d.*' => ['required_if:has_quiz,1', 'string'],
            'quiz_correct_option' => ['required_if:has_quiz,1', 'nullable', 'array'],
            'quiz_correct_option.*' => ['required_if:has_quiz,1', 'string', 'in:A,B,C,D'],
            'has_story' => ['nullable', 'boolean'],
            'story_title' => ['required_if:has_story,1', 'nullable', 'array'],
            'story_title.*' => ['required_if:has_story,1', 'array'],
            'story_title.*.en' => ['required_if:has_story,1', 'string', 'max:255'],
            'story_title.*.id' => ['required_if:has_story,1', 'string', 'max:255'],
            'story_content' => ['required_if:has_story,1', 'nullable', 'array'],
            'story_content.*' => ['required_if:has_story,1', 'array'],
            'story_content.*.en' => ['required_if:has_story,1', 'string'],
            'story_content.*.id' => ['required_if:has_story,1', 'string'],
            'story_type' => ['required_if:has_story,1', 'nullable', 'array'],
            'story_type.*' => ['in:history,philosophy,value'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeLocaleArrayField('story_title');
        $this->normalizeLocaleArrayField('story_content');
        $this->normalizeLocaleArrayField('quiz_question');
        $this->normalizeLocaleField('accessibility_notes');
    }

    private function normalizeLocaleField(string $field): void
    {
        $value = $this->input($field);
        if (is_string($value) && ! empty($value)) {
            $this->merge([$field => ['en' => $value, 'id' => $value]]);
        }
    }

    private function normalizeLocaleArrayField(string $field): void
    {
        $values = $this->input($field);
        if (! is_array($values)) {
            return;
        }
        $changed = false;
        foreach ($values as $index => $item) {
            if (is_string($item) && ! empty($item)) {
                $values[$index] = ['en' => $item, 'id' => $item];
                $changed = true;
            }
        }
        if ($changed) {
            $this->merge([$field => $values]);
        }
    }
}
