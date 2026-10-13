<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportRequestV1 extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:' . config('tickets.import.max_kb')],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Please upload a .csv file.',
            'file.max' => 'The file may not be larger than ' . round(config('tickets.import.max_kb') / 1024) . ' MB.',
        ];
    }
}
