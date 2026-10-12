<aside class="sidebar">
    <div class="sidebar-head">
        <button type="button" class="icon-btn mobile-only" data-nav-toggle aria-label="Close menu"><i class="icon-x"></i></button>
        <a href="{{ route('dashboard') }}" class="logo"><span class="logo-mark"><i class="icon-life-buoy"></i></span>{{ config('app.name') }}</a>
    </div>

    <nav class="nav">
        <div class="nav-label">Overview</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="icon-layout-dashboard"></i>Dashboard</a>

        <div class="nav-label">Tickets</div>
        <a href="{{ route('tickets.index') }}" class="nav-item {{ request()->routeIs('tickets.index', 'tickets.resolved', 'tickets.show', 'tickets.edit') ? 'active' : '' }}"><i class="icon-inbox"></i>{{ auth()->user()->can('ticket.view_all') ? 'All tickets' : 'My tickets' }}</a>
        @canany(['ticket.claim', 'ticket.assign'])
            <a href="{{ route('tickets.unassigned') }}" class="nav-item {{ request()->routeIs('tickets.unassigned') ? 'active' : '' }}"><i class="icon-user-x"></i>Unassigned</a>
        @endcanany
        @can('ticket.claim')
            <a href="{{ route('tickets.queue') }}" class="nav-item {{ request()->routeIs('tickets.queue') ? 'active' : '' }}"><i class="icon-user-check"></i>My queue</a>
        @endcan
        @can('ticket.create')
            <a href="{{ route('tickets.create') }}" class="nav-item {{ request()->routeIs('tickets.create') ? 'active' : '' }}"><i class="icon-plus"></i>New ticket</a>
        @endcan

        @can('user.manage')
            <div class="nav-label">People</div>
            <a href="#" class="nav-item"><i class="icon-users"></i>Users</a>
        @endcan

        @can('settings.manage')
            <div class="nav-label">Configuration</div>
            <a href="#" class="nav-item"><i class="icon-timer"></i>SLA policies</a>
            <a href="#" class="nav-item"><i class="icon-clock"></i>Business hours</a>
            <a href="#" class="nav-item"><i class="icon-calendar-x"></i>Holidays</a>
            <a href="#" class="nav-item"><i class="icon-tag"></i>Categories</a>
        @endcan

        @can('import.run')
            <div class="nav-label">Data</div>
            <a href="#" class="nav-item"><i class="icon-file-spreadsheet"></i>CSV import</a>
        @endcan
    </nav>

    <div class="sidebar-user">
        <span class="avatar lg">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
        <div><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->role?->display_name }}</span></div>
    </div>
    <div class="sidebar-version mono">v0.1.0 · {{ app()->environment() }}</div>
</aside>
