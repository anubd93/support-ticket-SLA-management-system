<aside class="sidebar">
    <div class="sidebar-head">
        <button type="button" class="icon-btn mobile-only" data-nav-toggle aria-label="Close menu"><i class="icon-x"></i></button>
        <a href="{{ route('dashboard') }}" class="logo"><span class="logo-mark"><i class="icon-life-buoy"></i></span>{{ config('app.name') }}</a>
    </div>

    <nav class="nav">
        <div class="nav-label">Overview</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="icon-layout-dashboard"></i>Dashboard</a>

        {{-- Links below are placeholders until their modules are built. --}}
        <div class="nav-label">Tickets</div>
        <a href="#" class="nav-item"><i class="icon-inbox"></i>Active</a>
        <a href="#" class="nav-item"><i class="icon-user-x"></i>Unassigned</a>
        <a href="#" class="nav-item"><i class="icon-user-check"></i>My queue</a>
        <a href="#" class="nav-item"><i class="icon-circle-check"></i>Resolved &amp; closed</a>
        <a href="#" class="nav-item"><i class="icon-plus"></i>New ticket</a>

        <div class="nav-label">People</div>
        <a href="#" class="nav-item"><i class="icon-users"></i>Users</a>

        <div class="nav-label">Configuration</div>
        <a href="#" class="nav-item"><i class="icon-timer"></i>SLA policies</a>
        <a href="#" class="nav-item"><i class="icon-clock"></i>Business hours</a>
        <a href="#" class="nav-item"><i class="icon-calendar-x"></i>Holidays</a>
        <a href="#" class="nav-item"><i class="icon-tag"></i>Categories</a>

        <div class="nav-label">Data</div>
        <a href="#" class="nav-item"><i class="icon-file-spreadsheet"></i>CSV import</a>
    </nav>

    <div class="sidebar-user">
        <span class="avatar lg">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
        <div><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span></div>
    </div>
    <div class="sidebar-version mono">v0.1.0 · {{ app()->environment() }}</div>
</aside>
