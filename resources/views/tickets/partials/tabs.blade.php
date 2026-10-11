<div class="tabs">
    <a href="{{ route('tickets.index') }}" class="tab {{ request()->routeIs('tickets.index') ? 'active' : '' }}">Active <span class="count">{{ $counts['active'] }}</span></a>
    <a href="{{ route('tickets.resolved') }}" class="tab {{ request()->routeIs('tickets.resolved') ? 'active' : '' }}">Resolved &amp; closed <span class="count">{{ $counts['finished'] }}</span></a>
</div>
