<header class="topbar">
    <button type="button" class="icon-btn mobile-only" data-nav-toggle aria-label="Open menu"><i class="icon-menu"></i></button>

    <div class="topbar-right">
        <div class="dropdown">
            <button type="button" class="icon-btn" data-dropdown aria-label="Notifications"><i class="icon-bell"></i><span class="dot"></span></button>
            <div class="menu notifications">
                <div class="menu-title" style="padding:0.75rem 0.9rem;border-bottom:1px solid var(--border)">Notifications</div>
                <a href="#" class="notification unread"><i class="icon-flame text-red"></i><span><p>SLA breached on <span class="mono">TKT-2026-000118</span></p><small>5 min ago</small></span></a>
                <a href="#" class="notification unread"><i class="icon-hourglass text-amber"></i><span><p>SLA approaching on <span class="mono">TKT-2026-000123</span></p><small>12 min ago</small></span></a>
                <a href="#" class="notification"><i class="icon-circle-check text-green"></i><span><p><span class="mono">TKT-2026-000109</span> was resolved</p><small>2 h ago</small></span></a>
            </div>
        </div>

        <div class="dropdown">
            <button type="button" class="btn btn-sm btn-ghost" data-dropdown>
                <span class="avatar sm">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                {{ auth()->user()->name }}
                <i class="icon-chevron-down"></i>
            </button>
            <div class="menu">
                <a href="#" class="menu-item"><i class="icon-user"></i>Profile</a>
                <div class="menu-sep"></div>
                <a href="{{ route('logout') }}" class="menu-item"><i class="icon-log-out"></i>Sign out</a>
            </div>
        </div>
    </div>
</header>
