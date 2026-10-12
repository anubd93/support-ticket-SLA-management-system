@extends('layouts.app')

@section('title', $ticket->ticket_number)

@section('content')
@php
    $status = \App\Enums\V1\TicketStatus::from($ticket->status);
    $priority = \App\Enums\V1\TicketPriority::from($ticket->priority);
    $source = \App\Enums\V1\TicketSource::tryFrom($ticket->source);
    $createdAt = \Illuminate\Support\Carbon::parse($ticket->created_at);
    $dueAt = $ticket->sla_due_at ? \Illuminate\Support\Carbon::parse($ticket->sla_due_at) : null;
    $slaState = $sla['state'] ?? null;
    // 125 -> "2h 05m"
    $duration = fn (int $minutes) => intdiv($minutes, 60) . 'h ' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT) . 'm';
@endphp

<div class="page-head">
    <div>
        <span class="eyebrow mono">{{ $ticket->ticket_number }}</span>
        <h1>{{ $ticket->subject }}</h1>
        <p class="flex wrap">
            @include('tickets.partials.priority', ['priority' => $ticket->priority])
            <span class="faint">·</span>
            @include('tickets.partials.status-badge', ['status' => $ticket->status])
            <span class="faint">·</span>
            <span>Opened by {{ $ticket->creator_name }}, {{ $createdAt->format('d M Y, H:i') }}</span>
        </p>
    </div>
    <div class="actions">
        <a href="{{ $status->isActive() ? route('tickets.index') : route('tickets.resolved') }}" class="btn"><i class="icon-arrow-left"></i>Back</a>
        @can('update', [\App\Models\V1\Ticket::class, $ticket])
            <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn"><i class="icon-pencil"></i>Edit</a>
        @endcan
    </div>
</div>

@if ($slaState === \App\Enums\V1\SlaStatus::Breached)
    <div class="callout red">
        <i class="icon-flame"></i>
        <div><span class="strong">SLA breached.</span> This ticket was due {{ $dueAt->format('d M Y, H:i') }} and is {{ $duration($sla['overdue_minutes']) }} of business time overdue.</div>
    </div>
@elseif ($slaState === \App\Enums\V1\SlaStatus::Approaching)
    <div class="callout amber">
        <i class="icon-hourglass"></i>
        <div><span class="strong">Approaching SLA breach.</span> {{ $duration($sla['remaining_minutes']) }} of business time left, due {{ $dueAt->format('d M Y, H:i') }}.</div>
    </div>
@endif

<div class="grid g-main">
    <div class="stack">
        <div class="card">
            <div class="card-head"><h2>Description</h2></div>
            <div class="card-body">
                <p class="prose">{{ $ticket->description }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Activity</h2></div>
            <div class="card-body">
                @if ($activities->isEmpty())
                    <p class="muted mb-0">No activity yet.</p>
                @else
                    <ul class="timeline">
                        @foreach ($activities as $activity)
                            @include('tickets.partials.activity-item', ['activity' => $activity])
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="stack">
        @can('changeStatus', [\App\Models\V1\Ticket::class, $ticket])
        <div class="card">
            <div class="card-head"><h2>Status</h2></div>
            <div class="card-body">
                @if ($status->allowedTransitions())
                    <form id="status-form" method="POST" action="{{ route('tickets.status', $ticket->id) }}" novalidate>
                        @csrf
                        @method('PATCH')
                        <div class="callout red" data-form-alert style="display:none">
                            <i class="icon-circle-alert"></i>
                            <div data-form-alert-text></div>
                        </div>
                        <div class="field">
                            <label class="label" for="status">Change to</label>
                            <select id="status" name="status" class="select">
                                @foreach ($status->allowedTransitions() as $next)
                                    <option value="{{ $next->value }}">{{ $next->label() }}</option>
                                @endforeach
                            </select>
                            <div class="error" data-error-for="status" style="display:none"></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Update status</button>
                    </form>
                @else
                    <p class="muted mb-0">This ticket is closed. Closed tickets cannot be changed.</p>
                @endif
            </div>
        </div>
        @endcan

        <div class="card">
            <div class="card-head"><h2>Assignment</h2></div>
            <div class="card-body">
                @can('assign', [\App\Models\V1\Ticket::class, $ticket])
                    <form id="assign-form" method="POST" action="{{ route('tickets.assign', $ticket->id) }}" novalidate>
                        @csrf
                        @method('PATCH')
                        <div class="callout red" data-form-alert style="display:none">
                            <i class="icon-circle-alert"></i>
                            <div data-form-alert-text></div>
                        </div>
                        <div class="field">
                            <label class="label" for="agent_id">Agent</label>
                            <select id="agent_id" name="agent_id" class="select">
                                <option value="">Unassigned</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}" @selected((int) $ticket->assigned_to === (int) $agent->id)>
                                        {{ $agent->name }} · {{ $agent->open_count }} open{{ $agent->is_available ? '' : ' · away' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="error" data-error-for="agent_id" style="display:none"></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Save assignment</button>
                    </form>
                @elsecan('claim', [\App\Models\V1\Ticket::class, $ticket])
                    <form id="claim-form" method="POST" action="{{ route('tickets.claim', $ticket->id) }}" novalidate>
                        @csrf
                        <div class="callout red" data-form-alert style="display:none">
                            <i class="icon-circle-alert"></i>
                            <div data-form-alert-text></div>
                        </div>
                        <p class="muted">Nobody is working on this ticket yet.</p>
                        <button type="submit" class="btn btn-primary btn-block">Accept ticket</button>
                    </form>
                @else
                    <dl class="meta">
                        <div><dt>Agent</dt><dd>{!! $ticket->assignee_name ? e($ticket->assignee_name) : '<span class="faint">Unassigned</span>' !!}</dd></div>
                    </dl>
                @endcan
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>SLA</h2><span class="ml-auto">@include('tickets.partials.sla-badge', ['ticket' => $ticket])</span></div>
            <div class="card-body">
                @if ($slaState instanceof \App\Enums\V1\SlaStatus && $slaState !== \App\Enums\V1\SlaStatus::Breached)
                    <div class="countdown" style="color: var({{ match ($slaState) { \App\Enums\V1\SlaStatus::Within => '--green', \App\Enums\V1\SlaStatus::Paused => '--muted', default => '--amber' } }})">{{ $duration($sla['remaining_minutes']) }}</div>
                    <div class="xs muted mb-1">business time left{{ $slaState === \App\Enums\V1\SlaStatus::Paused ? ' (paused)' : '' }}</div>
                @elseif ($slaState === \App\Enums\V1\SlaStatus::Breached)
                    <div class="countdown" style="color: var(--red)">{{ $duration($sla['overdue_minutes']) }}</div>
                    <div class="xs muted mb-1">business time overdue</div>
                @endif

                @if ($slaState instanceof \App\Enums\V1\SlaStatus && $sla['used_percent'] !== null)
                    @php
                        $barClass = match ($slaState) {
                            \App\Enums\V1\SlaStatus::Breached => 'red',
                            \App\Enums\V1\SlaStatus::Approaching => 'amber',
                            \App\Enums\V1\SlaStatus::Paused => 'gray',
                            default => 'green',
                        };
                    @endphp
                    <div class="bar mb-2"><span class="{{ $barClass }}" style="width: {{ $sla['used_percent'] }}%"></span></div>
                @endif

                <dl class="meta">
                    <div><dt>Policy</dt><dd>{{ $priority->label() }} · {{ $sla['policy_minutes'] ? $duration($sla['policy_minutes']) : 'none' }}</dd></div>
                    <div><dt>Due</dt><dd>{{ $dueAt ? $dueAt->format('d M Y, H:i') : 'No SLA policy' }}</dd></div>
                    @if (($sla['paused_minutes'] ?? 0) > 0)
                        <div><dt>Paused total</dt><dd>{{ $duration($sla['paused_minutes']) }}</dd></div>
                    @endif
                    @if ($ticket->resolved_at)
                        <div><dt>Resolved</dt><dd>{{ \Illuminate\Support\Carbon::parse($ticket->resolved_at)->format('d M Y, H:i') }}</dd></div>
                    @endif
                    @if ($ticket->closed_at)
                        <div><dt>Closed</dt><dd>{{ \Illuminate\Support\Carbon::parse($ticket->closed_at)->format('d M Y, H:i') }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Details</h2></div>
            <div class="card-body">
                <dl class="meta">
                    <div><dt>Ticket ID</dt><dd class="mono">{{ $ticket->ticket_number }}</dd></div>
                    <div><dt>Status</dt><dd>@include('tickets.partials.status-badge', ['status' => $ticket->status])</dd></div>
                    <div><dt>Priority</dt><dd>{{ $priority->label() }}</dd></div>
                    <div><dt>Category</dt><dd>{{ $ticket->category_name }}</dd></div>
                    <div><dt>Created by</dt><dd>{{ $ticket->creator_name }}</dd></div>
                    <div><dt>Assigned agent</dt><dd>{!! $ticket->assignee_name ? e($ticket->assignee_name) : '<span class="faint">Unassigned</span>' !!}</dd></div>
                    <div><dt>Source</dt><dd>{{ $source?->label() ?? $ticket->source }}</dd></div>
                    <div><dt>Created</dt><dd>{{ $createdAt->format('d M Y, H:i') }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // PATCH /tickets/{id}/status -> 200 (reload) | 422 { message } for a disallowed change
    const statusForm = document.getElementById('status-form');
    if (statusForm) {
        AjaxForm.handle(statusForm, function () { window.location.reload(); });
    }

    // PATCH /tickets/{id}/assign (admin) and POST /tickets/{id}/claim (agent) -> reload on success
    ['assign-form', 'claim-form'].forEach(function (id) {
        const form = document.getElementById(id);
        if (form) {
            AjaxForm.handle(form, function () { window.location.reload(); });
        }
    });
</script>
@endpush
