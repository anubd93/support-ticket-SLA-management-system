@extends('layouts.app')

@section('title', 'New user')

@section('content')
<div class="page-head">
    <div>
        <h1>New user</h1>
        <p>Create an administrator, support agent or customer account.</p>
    </div>
    <div class="actions">
        <a href="{{ route('users.index') }}" class="btn"><i class="icon-arrow-left"></i>Back</a>
    </div>
</div>

<form id="user-form" class="card" method="POST" action="{{ route('users.store') }}" novalidate>
    @csrf
    <div class="card-body">
        @include('users.partials.form', ['user' => null])

        <div class="form-foot">
            <a href="{{ route('users.index') }}" class="btn">Cancel</a>
            <button type="submit" class="btn btn-primary">Create user</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // POST /users -> 201 { redirect } | 422 { errors }
    AjaxForm.handle(document.getElementById('user-form'));
</script>
@endpush
