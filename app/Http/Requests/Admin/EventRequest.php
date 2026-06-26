<?php

namespace App\Http\Requests\Admin;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
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
            'category' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'start_time' => ['nullable', 'string'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['nullable', 'string'],
            'location_name' => ['required', 'array'],
            'location_name.en' => ['required', 'string', 'max:255'],
            'location_name.id' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_free' => ['nullable', 'boolean'],
            'price' => ['required_if:is_free,0', 'nullable', 'numeric', 'min:0'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $startDatetime = Carbon::parse(
                $this->input('start_date').' '.($this->input('start_time') ?: '00:00')
            );
            $endDatetime = Carbon::parse(
                $this->input('end_date').' '.($this->input('end_time') ?: '23:59')
            );

            if ($endDatetime->lt($startDatetime)) {
                $validator->errors()->add('end_date', __('Tanggal & waktu selesai harus setelah waktu mulai.'));
            }
        });
    }
}
