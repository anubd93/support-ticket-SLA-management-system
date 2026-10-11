@extends('layouts.app')

@section('title', $ticket->ticket_number)

@section('content')
@php
    $status = \App\Enums\V1\TicketStatus::from($ticket->status);
    $priority = \App\Enums\V1\TicketPriority::from($ticket->priority);
    $source = \App\Enums\V1\TicketSource::tryFrom($ticket->source);
    $createdAt = \Illuminate\Support\Carbon::parse($ticket->created_at);
    $dueAt = $ticket->sla_due_at ? \Illuminate\Support\Carbon::parse($ticket->sla_due_at) : null;
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
        @if ($status !== \App\Enums\V1\TicketStatus::Closed)
            <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn"><i class="icon-pencil"></i>Edit</a>
        @endif
    </div>
</div>

@if ($status->isActive() && ! $status->pausesSla() && $dueAt?->isPast())
    <div class="callout red">
        <i class="icon-flame"></i>
        <div><span class="strong">SLA breached.</span> This ticket was due {{ $dueAt->format('d M Y, H:i') }}.</div>
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

        <div class="card">
            <div class="card-head"><h2>SLA</h2><span class="ml-auto">@include('tickets.partials.sla-badge', ['ticket' => $ticket])</span></div>
            <div class="card-body">
                <dl class="meta">
                    <div><dt>Due</dt><dd>{{ $dueAt ? $dueAt->format('d M Y, H:i') : 'No SLA policy' }}</dd></div>
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
</script>
@endpush
