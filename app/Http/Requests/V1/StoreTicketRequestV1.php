<?php

namespace App\Http\Requests\V1;

use App\Enums\V1\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequestV1 extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
        ];
    }

    public function attributes(): array
    {
        return ['category_id' => 'category'];
    }
}
