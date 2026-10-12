<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\V1\Concerns\AttachmentRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketReplyRequestV1 extends FormRequest
{
    use AttachmentRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:10000'],
            'is_internal' => ['nullable', 'boolean'],
            ...$this->attachmentRules(),
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Please write a message.',
            ...$this->attachmentMessages(),
        ];
    }
}
