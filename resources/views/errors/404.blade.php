@extends('layouts.guest')

@section('title', 'Not found')

@section('content')
<div class="auth">
    <div class="auth-box">
        <h1>Not found</h1>
        <p>The page or ticket you are looking for does not exist.</p>
        <a href="{{ url('/dashboard') }}" class="btn btn-block"><i class="icon-arrow-left"></i>Back to dashboard</a>
    </div>
</div>
@endsection
