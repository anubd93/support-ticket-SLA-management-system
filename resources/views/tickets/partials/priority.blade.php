{{-- Expects: $priority (string value from tickets.priority) --}}
@php($priorityEnum = \App\Enums\V1\TicketPriority::from($priority))
<span class="priority {{ $priorityEnum->value }}">{{ $priorityEnum->label() }}</span>
