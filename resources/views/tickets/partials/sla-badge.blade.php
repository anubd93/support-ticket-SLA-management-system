{{--
    Expects: $ticket (row with status, sla_due_at, resolved_at, closed_at).
    Simple version until the SLA module: met/missed, breached, paused, or time left.
--}}
@php
    $status = \App\Enums\V1\TicketStatus::from($ticket->status);
    $dueAt = $ticket->sla_due_at ? \Illuminate\Support\Carbon::parse($ticket->sla_due_at) : null;
    $finishedAt = $ticket->resolved_at ?? $ticket->closed_at;
@endphp

@if (! $dueAt)
    <span class="faint">&ndash;</span>
@elseif (! $status->isActive())
    @if ($finishedAt && \Illuminate\Support\Carbon::parse($finishedAt)->lte($dueAt))
        <span class="badge green">Met</span>
    @else
        <span class="badge red">Missed</span>
    @endif
@elseif ($status->pausesSla())
    <span class="badge"><i class="icon-pause"></i>Paused</span>
@elseif ($dueAt->isPast())
    <span class="badge red">Breached</span> <span class="xs muted">{{ $dueAt->diffForHumans(null, true) }} over</span>
@else
    <span class="badge green">Within SLA</span> <span class="xs muted">{{ now()->diffForHumans($dueAt, true) }} left</span>
@endif
