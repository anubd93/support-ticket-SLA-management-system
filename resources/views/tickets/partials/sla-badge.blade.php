{{--
    Expects: $ticket (row with status, sla_due_at, sla_warning_at, resolved_at, closed_at).
    Active: paused, breached, approaching or within. Finished: met or missed.
--}}
@php
    $status = \App\Enums\V1\TicketStatus::from($ticket->status);
    $dueAt = $ticket->sla_due_at ? \Illuminate\Support\Carbon::parse($ticket->sla_due_at) : null;
    $warningAt = $ticket->sla_warning_at ? \Illuminate\Support\Carbon::parse($ticket->sla_warning_at) : null;
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
    <span class="badge red">Breached</span> <span class="xs muted">due {{ $dueAt->format('d M, H:i') }}</span>
@elseif ($warningAt && $warningAt->isPast())
    <span class="badge amber">Approaching</span> <span class="xs muted">due {{ $dueAt->format('d M, H:i') }}</span>
@else
    <span class="badge green">Within SLA</span> <span class="xs muted">due {{ $dueAt->format('d M, H:i') }}</span>
@endif
