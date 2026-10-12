<header class="topbar">
    <button type="button" class="icon-btn mobile-only" data-nav-toggle aria-label="Open menu"><i class="icon-menu"></i></button>

    <div class="topbar-right">
        {{-- $bellNotifications / $bellUnreadCount come from the view composer in AppServiceProvider --}}
        <div class="dropdown">
            <button type="button" class="icon-btn" data-dropdown aria-label="Notifications"><i class="icon-bell"></i>@if ($bellUnreadCount)<span class="dot" data-bell-dot></span>@endif</button>
            <div class="menu notifications">
                <div class="menu-title" style="padding:0.75rem 0.9rem;border-bottom:1px solid var(--border)">Notifications @if ($bellUnreadCount)({{ $bellUnreadCount }})@endif</div>
                @forelse ($bellNotifications as $notification)
                    @php
                        $icon = match ($notification->data['event'] ?? '') {
                            'created' => 'icon-inbox',
                            'assigned' => 'icon-user-check',
                            'replied' => 'icon-message-square',
                            'internal_note' => 'icon-lock text-amber',
                            'resolved' => 'icon-circle-check text-green',
                            'sla_warning' => 'icon-hourglass text-amber',
                            'sla_breached' => 'icon-flame text-red',
                            default => 'icon-bell',
                        };
                    @endphp
                    <a href="{{ route('notifications.open', $notification->id) }}" class="notification {{ $notification->read_at ? '' : 'unread' }}">
                        <i class="{{ $icon }}"></i>
                        <span><p>{{ $notification->data['message'] ?? 'Ticket update' }}</p><small>{{ \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans() }}</small></span>
                    </a>
                @empty
                    <p class="muted" style="padding:0.9rem;margin:0">No notifications yet.</p>
                @endforelse
                @if ($bellUnreadCount)
                    <button type="button" class="menu-foot" data-read-all="{{ route('notifications.read_all') }}" style="width:100%;border:0;background:none;cursor:pointer;font:inherit">Mark all as read</button>
                @endif
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

<script>
    // POST /notifications/read-all -> 200: clear the unread styling without a reload
    document.querySelectorAll('[data-read-all]').forEach(function (button) {
        button.addEventListener('click', async function () {
            const response = await fetch(button.dataset.readAll, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            }).catch(function () { return null; });

            if (!response || !response.ok) return;

            document.querySelectorAll('.notification.unread').forEach(function (item) { item.classList.remove('unread'); });
            document.querySelectorAll('[data-bell-dot]').forEach(function (dot) { dot.remove(); });
            button.remove();
        });
    });
</script>
