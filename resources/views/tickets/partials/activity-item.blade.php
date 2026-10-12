{{-- Expects: $activity (row from ticket_activities joined with users.name) --}}
@php
    $type = \App\Enums\V1\ActivityType::from($activity->type);
    $old = json_decode($activity->old_values ?? 'null', true) ?? [];
    $new = json_decode($activity->new_values ?? 'null', true) ?? [];
    $actor = $activity->user_name ?? 'System';
    $statusLabel = fn ($value) => \App\Enums\V1\TicketStatus::tryFrom($value ?? '')?->label() ?? $value;
    $priorityLabel = fn ($value) => \App\Enums\V1\TicketPriority::tryFrom($value ?? '')?->label() ?? $value;
    $dotClass = match ($type) {
        \App\Enums\V1\ActivityType::Created, \App\Enums\V1\ActivityType::Assigned => 'teal',
        \App\Enums\V1\ActivityType::Resolved, \App\Enums\V1\ActivityType::Closed => 'green',
        \App\Enums\V1\ActivityType::PriorityChanged, \App\Enums\V1\ActivityType::Reopened, \App\Enums\V1\ActivityType::SlaWarning => 'amber',
        \App\Enums\V1\ActivityType::SlaBreached => 'red',
        default => '',
    };
@endphp
<li class="{{ $dotClass }}">
    <p>
        @switch($type)
            @case(\App\Enums\V1\ActivityType::Created)
                Ticket created by {{ $actor }}@if (($new['source'] ?? null) === 'import') <span class="faint">(CSV import{{ isset($new['external_reference']) ? ', ref ' . $new['external_reference'] : '' }})</span>@endif
                @break
            @case(\App\Enums\V1\ActivityType::PriorityChanged)
                Priority changed from <span class="strong">{{ $priorityLabel($old['priority'] ?? null) }}</span>
                to <span class="strong">{{ $priorityLabel($new['priority'] ?? null) }}</span> by {{ $actor }}
                @break
            @case(\App\Enums\V1\ActivityType::CategoryChanged)
                Category changed from <span class="strong">{{ $old['category'] ?? '-' }}</span>
                to <span class="strong">{{ $new['category'] ?? '-' }}</span> by {{ $actor }}
                @break
            @case(\App\Enums\V1\ActivityType::Assigned)
                @if (! empty($new['auto']))
                    Auto-assigned to <span class="strong">{{ $new['agent'] ?? '-' }}</span>
                @elseif (! empty($new['claimed']))
                    Accepted by <span class="strong">{{ $new['agent'] ?? $actor }}</span>
                @elseif (! empty($old['agent']))
                    Reassigned from <span class="strong">{{ $old['agent'] }}</span>
                    to <span class="strong">{{ $new['agent'] ?? '-' }}</span> by {{ $actor }}
                @else
                    Assigned to <span class="strong">{{ $new['agent'] ?? '-' }}</span> by {{ $actor }}
                @endif
                @break
            @case(\App\Enums\V1\ActivityType::SlaWarning)
                <span class="strong">SLA approaching</span>, escalated to level {{ $new['level'] ?? 1 }}
                (due {{ isset($new['due_at']) ? \Illuminate\Support\Carbon::parse($new['due_at'])->format('d M Y, H:i') : '-' }})
                @break
            @case(\App\Enums\V1\ActivityType::SlaBreached)
                <span class="strong">SLA breached</span>, escalated to level {{ $new['level'] ?? 2 }}
                (was due {{ isset($new['due_at']) ? \Illuminate\Support\Carbon::parse($new['due_at'])->format('d M Y, H:i') : '-' }})
                @break
            @case(\App\Enums\V1\ActivityType::Unassigned)
                Unassigned from <span class="strong">{{ $old['agent'] ?? '-' }}</span> by {{ $actor }}
                @break
            @case(\App\Enums\V1\ActivityType::Updated)
                {{ ucfirst(implode(' and ', $new['fields'] ?? ['details'])) }} updated by {{ $actor }}
                @break
            @case(\App\Enums\V1\ActivityType::StatusChanged)
            @case(\App\Enums\V1\ActivityType::Resolved)
            @case(\App\Enums\V1\ActivityType::Closed)
            @case(\App\Enums\V1\ActivityType::Reopened)
                {{ $type->label() }}: <span class="strong">{{ $statusLabel($old['status'] ?? null) }}</span>
                to <span class="strong">{{ $statusLabel($new['status'] ?? null) }}</span> by {{ $actor }}
                @break
            @default
                {{ $type->label() }} by {{ $actor }}
        @endswitch
        @if (! empty($new['files']))
            <span class="faint">· {{ count($new['files']) }} {{ \Illuminate\Support\Str::plural('file', count($new['files'])) }} attached</span>
        @endif
    </p>
    <time>{{ \Illuminate\Support\Carbon::parse($activity->created_at)->format('d M Y, H:i') }}</time>
</li>
