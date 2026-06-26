<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UmkmOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'.($id ? ','.$id : '')],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }
}
