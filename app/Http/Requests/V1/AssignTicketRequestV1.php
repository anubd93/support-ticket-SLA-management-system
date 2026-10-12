<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AssignTicketRequestV1 extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Empty agent_id = unassign. Whether the user is an active agent is checked in the service.
     */
    public function rules(): array
    {
        return [
            'agent_id' => ['nullable', 'integer'],
        ];
    }
}
