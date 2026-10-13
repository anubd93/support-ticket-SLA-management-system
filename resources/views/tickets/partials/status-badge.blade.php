{{-- Expects: $status (string value from tickets.status) --}}
@php
    $statusEnum = \App\Enums\V1\TicketStatus::from($status);
    $badgeClass = match ($statusEnum) {
        \App\Enums\V1\TicketStatus::Open => 'blue',
        \App\Enums\V1\TicketStatus::InProgress => 'teal',
        \App\Enums\V1\TicketStatus::Resolved => 'green',
        default => '',
    };
@endphp
<span class="badge {{ $badgeClass }}">{{ $statusEnum->label() }}</span>
