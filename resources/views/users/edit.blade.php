@extends('layouts.app')

@section('title', 'Edit ' . $user->name)

@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow">{{ $user->email }}</span>
        <h1>Edit user</h1>
        <p>Changing an agent to another role moves their open tickets back to Unassigned.</p>
    </div>
    <div class="actions">
        <a href="{{ route('users.index') }}" class="btn"><i class="icon-arrow-left"></i>Back</a>
    </div>
</div>

<form id="user-form" class="card" method="POST" action="{{ route('users.update', $user->id) }}" novalidate>
    @csrf
    @method('PUT')
    <div class="card-body">
        @include('users.partials.form', ['user' => $user])

        <div class="form-foot">
            <a href="{{ route('users.index') }}" class="btn">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // PUT /users/{id} -> 200 { redirect } | 422 { errors } | 422 { message } (e.g. own role)
    AjaxForm.handle(document.getElementById('user-form'));
</script>
@endpush
