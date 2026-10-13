@extends('layouts.guest')

@section('title', 'Access denied')

@section('content')
<div class="auth">
    <div class="auth-box">
        <h1>Access denied</h1>
        <p>{{ $exception->getMessage() ?: 'You do not have permission to do this.' }}</p>
        <a href="{{ url('/dashboard') }}" class="btn btn-block"><i class="icon-arrow-left"></i>Back to dashboard</a>
    </div>
</div>
@endsection
