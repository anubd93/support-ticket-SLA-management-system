{{-- Expects: $reply (row from ticket_replies joined with users.name and roles.name), $files (its attachments) --}}
@php
    $role = \App\Enums\V1\UserRole::tryFrom($reply->user_role ?? '');
@endphp
<li class="msg {{ $reply->is_internal ? 'internal' : '' }}">
    <span class="avatar">{{ strtoupper(substr($reply->user_name, 0, 2)) }}</span>
    <div class="msg-body">
        <div class="msg-head">
            <strong>{{ $reply->user_name }}</strong>
            @if ($reply->is_internal)
                <span class="badge amber"><i class="icon-lock"></i>Internal note</span>
            @else
                <span class="badge {{ $role === \App\Enums\V1\UserRole::Customer ? '' : 'teal' }}">{{ $role?->label() ?? 'User' }}</span>
            @endif
            <time>{{ \Illuminate\Support\Carbon::parse($reply->created_at)->format('d M Y, H:i') }}</time>
        </div>
        {{-- e() escapes the text first, then nl2br keeps the line breaks --}}
        <p>{!! nl2br(e($reply->body)) !!}</p>
        @include('tickets.partials.attachment-list', ['files' => $files ?? []])
    </div>
</li>
