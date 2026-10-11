@extends('layouts.app')

@section('title', 'Resolved & closed tickets')

@section('content')
<div class="page-head">
    <div>
        <h1>Tickets</h1>
    </div>
    <div class="actions">
        <a href="{{ route('tickets.create') }}" class="btn btn-primary"><i class="icon-plus"></i>New ticket</a>
    </div>
</div>

@include('tickets.partials.tabs')

<div class="card table-wrap">
    <table class="table">
        <thead>
        <tr><th>Ticket</th><th>Customer</th><th>Priority</th><th>Status</th><th>Agent</th><th>SLA</th><th>Finished</th><th class="w-actions"></th></tr>
        </thead>
        <tbody>
        @forelse ($tickets as $ticket)
            <tr>
                <td>
                    <a href="{{ route('tickets.show', $ticket->id) }}" class="ticket-link">{{ $ticket->subject }}</a>
                    <span class="ticket-id mono">{{ $ticket->ticket_number }} · {{ $ticket->category_name }}</span>
                </td>
                <td><div class="person"><span class="avatar">{{ strtoupper(substr($ticket->creator_name, 0, 2)) }}</span><span>{{ $ticket->creator_name }}<small>{{ $ticket->creator_email }}</small></span></div></td>
                <td>@include('tickets.partials.priority', ['priority' => $ticket->priority])</td>
                <td>@include('tickets.partials.status-badge', ['status' => $ticket->status])</td>
                <td>{!! $ticket->assignee_name ? e($ticket->assignee_name) : '<span class="faint">Unassigned</span>' !!}</td>
                <td>@include('tickets.partials.sla-badge', ['ticket' => $ticket])</td>
                <td class="muted nowrap">{{ \Illuminate\Support\Carbon::parse($ticket->closed_at ?? $ticket->resolved_at)->diffForHumans() }}</td>
                <td class="w-actions"><a href="{{ route('tickets.show', $ticket->id) }}" class="icon-btn" aria-label="View"><i class="icon-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="center muted">No resolved or closed tickets yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('tickets.partials.pager', ['paginator' => $tickets])
@endsection
