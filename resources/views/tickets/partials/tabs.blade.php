<div class="tabs">
    <a href="{{ route('tickets.index') }}" class="tab {{ request()->routeIs('tickets.index') ? 'active' : '' }}">Active <span class="count">{{ $counts['active'] }}</span></a>
    @canany(['ticket.claim', 'ticket.assign'])
        <a href="{{ route('tickets.unassigned') }}" class="tab {{ request()->routeIs('tickets.unassigned') ? 'active' : '' }}">Unassigned <span class="count">{{ $counts['unassigned'] }}</span></a>
    @endcanany
    <a href="{{ route('tickets.resolved') }}" class="tab {{ request()->routeIs('tickets.resolved') ? 'active' : '' }}">Resolved &amp; closed <span class="count">{{ $counts['finished'] }}</span></a>
</div>
