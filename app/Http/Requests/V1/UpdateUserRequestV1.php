<?php

namespace App\Http\Requests\V1;

use Illuminate\Validation\Rule;

/**
 * Same as creating, except the email may stay the same and the password is optional (empty = keep).
 */
class UpdateUserRequestV1 extends StoreUserRequestV1
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore((int) $this->route('id'))],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ];
    }
}
