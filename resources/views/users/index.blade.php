@extends('layouts.app')

@section('title', 'Users')

@section('content')
@php
    $value = fn (string $key) => $filters[$key] ?? '';
    $hasFilters = collect($filters)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
@endphp

<div class="page-head">
    <div>
        <h1>Users</h1>
        <p>Administrators, support agents and customers.</p>
    </div>
    <div class="actions">
        <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="icon-plus"></i>New user</a>
    </div>
</div>

<form method="GET" action="{{ route('users.index') }}" class="toolbar">
    <div class="input-icon" style="flex:1 1 240px;max-width:320px">
        <i class="icon-search"></i>
        <input type="search" name="keyword" class="input" maxlength="100" placeholder="Name or email" value="{{ $value('keyword') }}">
    </div>
    <select name="role" class="select" style="width:170px" aria-label="Role">
        <option value="">All roles</option>
        @foreach ($roles as $role)
            <option value="{{ $role->name }}" @selected($value('role') === $role->name)>{{ $role->display_name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Search</button>
    @if ($hasFilters)
        <a href="{{ route('users.index') }}" class="btn btn-ghost">Clear</a>
    @endif
</form>

<div class="card table-wrap">
    <table class="table">
        <thead>
        <tr><th>User</th><th>Role</th><th>Category skills</th><th class="center">Open tickets</th><th class="w-actions"></th></tr>
        </thead>
        <tbody>
        @forelse ($users as $user)
            @php
                $isAgent = $user->role === \App\Enums\V1\UserRole::Agent->value;
                $isSelf = (int) $user->id === (int) auth()->id();
            @endphp
            <tr>
                <td><div class="person"><span class="avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</span><span>{{ $user->name }}@if ($isSelf) <span class="badge">you</span>@endif<small>{{ $user->email }}</small></span></div></td>
                <td>{{ $user->role_name }}</td>
                <td class="xs">{!! $isAgent ? (e(implode(', ', $user->categories)) ?: '<span class="faint">None</span>') : '<span class="faint">&ndash;</span>' !!}</td>
                <td class="center">
                    @if ($isAgent)
                        <a href="{{ route('tickets.index', ['agent_id' => $user->id]) }}">{{ $user->open_tickets }}</a>
                    @else
                        <span class="faint">&ndash;</span>
                    @endif
                </td>
                <td class="w-actions"><a href="{{ route('users.edit', $user->id) }}" class="icon-btn" aria-label="Edit"><i class="icon-pencil"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="center muted">{{ $hasFilters ? 'No users match your filters.' : 'No users yet.' }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('tickets.partials.pager', ['paginator' => $users])
@endsection
