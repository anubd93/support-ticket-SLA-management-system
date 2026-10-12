@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
{{-- Data: DashboardServiceV1::getDashboard(). Each card links to the matching filtered ticket list. --}}
@php
    $barColor = ['critical' => 'red', 'high' => 'amber', 'medium' => 'blue', 'low' => 'gray'];
@endphp

<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome back, {{ auth()->user()->name }}.
            @if ($isAgent)
                You have <a href="{{ route('tickets.queue') }}" class="strong">{{ $counts['mine'] }} open {{ \Illuminate\Support\Str::plural('ticket', $counts['mine']) }}</a> in your queue.
            @endif
        </p>
    </div>
</div>

@if ($isStaff)
    <h2 class="section-title">Queue</h2>
    <div class="grid g-4">
        <a href="{{ route('tickets.index') }}" class="card stat green"><i class="icon-inbox"></i><div><div class="stat-label">Open tickets</div><div class="stat-value">{{ $counts['open_total'] }}</div></div></a>
        <a href="{{ route('tickets.unassigned') }}" class="card stat"><i class="icon-user-x"></i><div><div class="stat-label">Unassigned</div><div class="stat-value">{{ $counts['unassigned'] }}</div></div></a>
        <a href="{{ route('tickets.index', ['priority' => 'critical']) }}" class="card stat"><i class="icon-triangle-alert"></i><div><div class="stat-label">Critical</div><div class="stat-value">{{ $counts['critical'] }}</div></div></a>
        <a href="{{ route('tickets.resolved') }}" class="card stat"><i class="icon-circle-check"></i><div><div class="stat-label">Resolved, {{ $recentDays }} days</div><div class="stat-value">{{ $counts['finished_recent'] }}</div></div></a>
    </div>

    <h2 class="section-title">SLA</h2>
    <div class="grid g-4">
        <a href="{{ route('tickets.index', ['sla' => 'breached']) }}" class="card stat {{ $counts['breached'] ? 'red' : '' }}"><i class="icon-flame"></i><div><div class="stat-label">SLA breached</div><div class="stat-value">{{ $counts['breached'] }}</div></div></a>
        <a href="{{ route('tickets.index', ['sla' => 'approaching']) }}" class="card stat {{ $counts['approaching'] ? 'amber' : '' }}"><i class="icon-hourglass"></i><div><div class="stat-label">Approaching breach</div><div class="stat-value">{{ $counts['approaching'] }}</div></div></a>
        <a href="{{ route('tickets.index', ['sla' => 'escalated']) }}" class="card stat"><i class="icon-siren"></i><div><div class="stat-label">Escalated</div><div class="stat-value">{{ $counts['escalated'] }}</div></div></a>
        <a href="{{ route('tickets.resolved', ['sla' => 'missed']) }}" class="card stat" title="Resolved or closed within the SLA, last {{ $recentDays }} days"><i class="icon-gauge"></i><div><div class="stat-label">SLA met, {{ $recentDays }} days</div><div class="stat-value">{{ $slaMetPercent === null ? '–' : $slaMetPercent . '%' }}</div></div></a>
    </div>

    <h2 class="section-title">Needs attention</h2>
    <div class="card table-wrap">
        <table class="table">
            <thead>
            <tr><th>Ticket</th><th>Priority</th><th>Agent</th><th>SLA</th><th class="right">Due</th></tr>
            </thead>
            <tbody>
            @forelse ($attention as $ticket)
                <tr>
                    <td>
                        <a href="{{ route('tickets.show', $ticket->id) }}" class="ticket-link">{{ $ticket->subject }}</a>
                        <span class="ticket-id mono">{{ $ticket->ticket_number }} · {{ $ticket->category_name }}</span>
                    </td>
                    <td>@include('tickets.partials.priority', ['priority' => $ticket->priority])</td>
                    <td>{!! $ticket->assignee_name ? e($ticket->assignee_name) : '<span class="faint">Unassigned</span>' !!}</td>
                    <td class="nowrap">@include('tickets.partials.sla-badge', ['ticket' => $ticket])</td>
                    <td class="right muted nowrap">{{ \Illuminate\Support\Carbon::parse($ticket->sla_due_at)->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="center muted">Nothing is close to its SLA deadline.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@else
    <div class="grid g-3">
        <a href="{{ route('tickets.index') }}" class="card stat green"><i class="icon-inbox"></i><div><div class="stat-label">My open tickets</div><div class="stat-value">{{ $counts['open_total'] }}</div></div></a>
        <a href="{{ route('tickets.index', ['status' => 'waiting_for_customer']) }}" class="card stat {{ $counts['waiting'] ? 'amber' : '' }}"><i class="icon-message-square"></i><div><div class="stat-label">Waiting for your reply</div><div class="stat-value">{{ $counts['waiting'] }}</div></div></a>
        <a href="{{ route('tickets.resolved') }}" class="card stat"><i class="icon-circle-check"></i><div><div class="stat-label">Resolved, {{ $recentDays }} days</div><div class="stat-value">{{ $counts['finished_recent'] }}</div></div></a>
    </div>

    <h2 class="section-title">Recently updated</h2>
    <div class="card table-wrap">
        <table class="table">
            <thead>
            <tr><th>Ticket</th><th>Priority</th><th>Status</th><th class="right">Created</th></tr>
            </thead>
            <tbody>
            @forelse ($recent as $ticket)
                <tr>
                    <td>
                        <a href="{{ route('tickets.show', $ticket->id) }}" class="ticket-link">{{ $ticket->subject }}</a>
                        <span class="ticket-id mono">{{ $ticket->ticket_number }} · {{ $ticket->category_name }}</span>
                    </td>
                    <td>@include('tickets.partials.priority', ['priority' => $ticket->priority])</td>
                    <td>@include('tickets.partials.status-badge', ['status' => $ticket->status])</td>
                    <td class="right muted nowrap">{{ \Illuminate\Support\Carbon::parse($ticket->created_at)->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="center muted">You have no tickets yet. @can('ticket.create')<a href="{{ route('tickets.create') }}">Create one</a>.@endcan</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endif

<div class="grid {{ $isStaff ? 'g-2' : '' }}" style="margin-top:1.25rem">
    <div class="card">
        <div class="card-head"><h2>Open by priority</h2></div>
        <div class="card-body">
            <ul class="rows">
                @foreach ($byPriority as $row)
                    <li>
                        <div class="row-label">
                            <a href="{{ route('tickets.index', ['priority' => $row['priority']->value]) }}">@include('tickets.partials.priority', ['priority' => $row['priority']->value])</a>
                            <span class="strong">{{ $row['total'] }}</span>
                        </div>
                        <div class="bar"><span class="{{ $barColor[$row['priority']->value] }}" style="width:{{ $row['percent'] }}%"></span></div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @if ($isStaff)
        <div class="card table-wrap">
            <div class="card-head"><h2>By agent</h2></div>
            <table class="table">
                <thead><tr><th>Agent</th><th class="right">Open</th><th class="right">Breached</th><th class="right">Resolved, {{ $recentDays }}d</th></tr></thead>
                <tbody>
                @foreach ($agents as $agent)
                    <tr>
                        <td>
                            <a href="{{ route('tickets.index', ['agent_id' => $agent->id]) }}" class="person">
                                <span class="avatar sm">{{ strtoupper(substr($agent->name, 0, 2)) }}</span>
                                <span>{{ $agent->name }}@if (! $agent->is_available) <span class="badge">away</span>@endif</span>
                            </a>
                        </td>
                        <td class="right">{{ (int) $agent->open_total }}</td>
                        <td class="right {{ $agent->breached ? 'text-red' : '' }}">{{ (int) $agent->breached }}</td>
                        <td class="right">{{ (int) $agent->resolved_recent }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><a href="{{ route('tickets.unassigned') }}" class="faint">Unassigned</a></td>
                    <td class="right">{{ $counts['unassigned'] }}</td>
                    <td class="right faint">&ndash;</td>
                    <td class="right faint">&ndash;</td>
                </tr>
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
