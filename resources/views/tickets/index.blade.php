@extends('layouts.app')

@section('title', match (true) {
    request()->routeIs('tickets.unassigned') => 'Unassigned tickets',
    request()->routeIs('tickets.queue') => 'My queue',
    default => 'Active tickets',
})

@section('content')
<div class="page-head">
    <div>
        <h1>Tickets</h1>
    </div>
    <div class="actions">
        @can('ticket.create')
            <a href="{{ route('tickets.create') }}" class="btn btn-primary"><i class="icon-plus"></i>New ticket</a>
        @endcan
    </div>
</div>

@include('tickets.partials.tabs')
@include('tickets.partials.filters')

<div class="card table-wrap">
    <table class="table">
        <thead>
        <tr><th>Ticket</th><th>Customer</th><th>Priority</th><th>Status</th><th>Agent</th><th>SLA</th><th>Created</th><th class="w-actions"></th></tr>
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
                <td class="nowrap">@include('tickets.partials.sla-badge', ['ticket' => $ticket])</td>
                <td class="muted nowrap">{{ \Illuminate\Support\Carbon::parse($ticket->created_at)->diffForHumans() }}</td>
                <td class="w-actions">
                    <div class="dropdown">
                        <button type="button" class="icon-btn" data-dropdown aria-label="Actions"><i class="icon-ellipsis"></i></button>
                        <div class="menu">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="menu-item"><i class="icon-eye"></i>View</a>
                            @can('claim', [\App\Models\V1\Ticket::class, $ticket])
                                <button type="button" class="menu-item" data-claim="{{ route('tickets.claim', $ticket->id) }}"><i class="icon-user-check"></i>Accept</button>
                            @endcan
                            @can('update', [\App\Models\V1\Ticket::class, $ticket])
                                <a href="{{ route('tickets.edit', $ticket->id) }}" class="menu-item"><i class="icon-pencil"></i>Edit</a>
                            @endcan
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            @if (collect($filters)->filter()->isNotEmpty())
                <tr><td colspan="8" class="center muted">No tickets match your filters. <a href="{{ url()->current() }}">Clear filters</a></td></tr>
            @elseif (request()->routeIs('tickets.unassigned'))
                <tr><td colspan="8" class="center muted">Every active ticket has an agent.</td></tr>
            @elseif (request()->routeIs('tickets.queue'))
                <tr><td colspan="8" class="center muted">No tickets assigned to you. Pick one from <a href="{{ route('tickets.unassigned') }}">Unassigned</a>.</td></tr>
            @else
                <tr><td colspan="8" class="center muted">No active tickets.@can('ticket.create') <a href="{{ route('tickets.create') }}">Create one</a>.@endcan</td></tr>
            @endif
        @endforelse
        </tbody>
    </table>
</div>

@include('tickets.partials.pager', ['paginator' => $tickets])
@endsection

@push('scripts')
<script>
    
    document.querySelectorAll('[data-claim]').forEach(function (button) {
        button.addEventListener('click', async function () {
            button.disabled = true;

            try {
                const response = await fetch(button.dataset.claim, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await response.json().catch(function () { return {}; });

                if (response.ok) return window.location.assign(data.redirect);

                window.alert(data.message || 'Something went wrong. Please try again.');
            } catch (error) {
                window.alert('Could not reach the server. Please try again.');
            }

            window.location.reload();
        });
    });
</script>
@endpush
