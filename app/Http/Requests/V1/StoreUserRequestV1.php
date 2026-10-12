<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequestV1 extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', Rule::exists('categories', 'id')->where('is_active', true)->whereNull('deleted_at')],
        ];
    }

    public function attributes(): array
    {
        return [
            'role_id' => 'role',
            'category_ids' => 'categories',
            'category_ids.*' => 'category',
        ];
    }
}
