@php
    // Switching tabs keeps the search; status and SLA differ per list, so they are dropped.
    $keep = request()->only(['keyword', 'priority', 'category_id', 'agent_id', 'customer', 'from', 'to']);
@endphp
<div class="tabs">
    <a href="{{ route('tickets.index', $keep) }}" class="tab {{ request()->routeIs('tickets.index') ? 'active' : '' }}">Active <span class="count">{{ $counts['active'] }}</span></a>
    @canany(['ticket.claim', 'ticket.assign'])
        <a href="{{ route('tickets.unassigned', \Illuminate\Support\Arr::except($keep, 'agent_id')) }}" class="tab {{ request()->routeIs('tickets.unassigned') ? 'active' : '' }}">Unassigned <span class="count">{{ $counts['unassigned'] }}</span></a>
    @endcanany
    <a href="{{ route('tickets.resolved', $keep) }}" class="tab {{ request()->routeIs('tickets.resolved') ? 'active' : '' }}">Resolved &amp; closed <span class="count">{{ $counts['finished'] }}</span></a>
</div>
