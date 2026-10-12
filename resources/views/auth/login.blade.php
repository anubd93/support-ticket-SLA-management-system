@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
<div class="auth">
    <div class="auth-box">
        <a href="{{ url('/') }}" class="logo"><span class="logo-mark"><i class="icon-life-buoy"></i></span>{{ config('app.name') }}</a>
        <h1>Sign in</h1>
    </br>

        <form id="login-form" class="card" method="POST" action="{{ url('/login') }}" novalidate>
            @csrf
            <div class="card-body">
                <div class="callout red" id="login-alert" style="display:none">
                    <i class="icon-circle-alert"></i>
                    <div id="login-alert-text"></div>
                </div>

                <div class="field">
                    <label class="label" for="email">Email</label>
                    <input id="email" name="email" type="email" class="input" placeholder="you@company.test" autocomplete="email" autofocus required>
                    <div class="error" id="email-error" style="display:none"></div>
                </div>

                <div class="field">
                    <label class="label" for="password">Password</label>
                    <input id="password" name="password" type="password" class="input" placeholder="••••••••" autocomplete="current-password" required>
                    <div class="error" id="password-error" style="display:none"></div>
                </div>

                <label class="check mb-2"><input type="checkbox" name="remember" value="1"> Remember me</label>

                <button type="submit" id="login-button" class="btn btn-primary btn-block">Sign in</button>
            </div>
        </form>

        @if (app()->environment('local'))
            <div class="demo">
                <div class="mb-1"><span class="strong">Demo accounts</span><span>password: <code>password</code></span></div>
                <div><span>Administrator</span><span class="mono">admin@supportdesk.com</span></div>
                <div><span>Support agent</span><span class="mono">support_agent_01@supportdesk.com</span></div>
                <div><span>Customer</span><span class="mono">user_01@test.com</span></div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Submit the login form with AJAX.
    // Expected responses: 200 { redirect }, 422 { errors }, 429 { message }.
    document.getElementById('login-form').addEventListener('submit', async function (event) {
        event.preventDefault();

        const form = this;
        const button = document.getElementById('login-button');
        const alertBox = document.getElementById('login-alert');

        // Reset previous errors.
        ['email', 'password'].forEach(function (field) {
            form.querySelector('[name="' + field + '"]').classList.remove('invalid');
            document.getElementById(field + '-error').style.display = 'none';
        });
        alertBox.style.display = 'none';

        button.disabled = true;
        button.textContent = 'Signing in...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: new FormData(form),
            });
            const data = await response.json().catch(function () { return {}; });

            if (response.ok && data.redirect) {
                window.location.assign(data.redirect);
                return;
            }

            if (response.status === 422 && data.errors) {
                Object.keys(data.errors).forEach(function (field) {
                    const input = form.querySelector('[name="' + field + '"]');
                    const errorBox = document.getElementById(field + '-error');
                    if (input) input.classList.add('invalid');
                    if (errorBox) {
                        errorBox.textContent = data.errors[field][0];
                        errorBox.style.display = 'block';
                    }
                });
            } else {
                showAlert(response.status === 419
                    ? 'Your session expired. Please refresh the page and try again.'
                    : (data.message || 'Something went wrong. Please try again.'));
            }
        } catch (error) {
            showAlert('Could not reach the server. Please try again.');
        }

        button.disabled = false;
        button.textContent = 'Sign in';

        function showAlert(message) {
            document.getElementById('login-alert-text').textContent = message;
            alertBox.style.display = 'flex';
        }
    });
</script>
@endpush
