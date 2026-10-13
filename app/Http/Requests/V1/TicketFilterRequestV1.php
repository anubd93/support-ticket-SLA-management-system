<?php

namespace App\Http\Requests\V1;

use App\Enums\V1\TicketPriority;
use App\Enums\V1\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketFilterRequestV1 extends FormRequest
{
    public const SLA_OPTIONS = ['within', 'approaching', 'breached', 'paused', 'escalated', 'met', 'missed'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(TicketStatus::class)],
            'priority' => ['nullable', Rule::enum(TicketPriority::class)],
            'category_id' => ['nullable', 'integer'],
            'agent_id' => ['nullable', 'regex:/^(none|\d+)$/'],
            'customer' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => array_filter(['nullable', 'date_format:Y-m-d', $this->filled('from') ? 'after_or_equal:from' : null]),
            'sla' => ['nullable', Rule::in(self::SLA_OPTIONS)],
        ];
    }

    public function attributes(): array
    {
        return [
            'keyword' => 'ticket ID or keyword',
            'category_id' => 'category',
            'agent_id' => 'agent',
            'from' => 'created from',
            'to' => 'created to',
            'sla' => 'SLA status',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->url();
    }
}
