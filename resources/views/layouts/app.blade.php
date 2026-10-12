<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <link rel="icon" href="data:,">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://unpkg.com/lucide-static@0.469.0/font/lucide.css">
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body>
<div class="app">
    @include('layouts.partials.sidebar')
    <div class="backdrop"></div>

    <div>
        @include('layouts.partials.topbar')

        <main class="page">
            @yield('content')
        </main>
    </div>
</div>

<script>
    // Dropdowns and mobile sidebar.
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-dropdown]');
        const openDropdown = document.querySelector('.dropdown.open');

        if (toggle) {
            const dropdown = toggle.closest('.dropdown');
            if (openDropdown && openDropdown !== dropdown) openDropdown.classList.remove('open');
            dropdown.classList.toggle('open');
        } else if (openDropdown && !event.target.closest('.menu')) {
            openDropdown.classList.remove('open');
        }

        if (event.target.closest('[data-nav-toggle]') || event.target.classList.contains('backdrop')) {
            document.body.classList.toggle('nav-open');
        }
    });

    // AJAX form helper used by page scripts.
    // Errors show in [data-error-for="field"] boxes and the form's [data-form-alert] box.
    const AjaxForm = {
        async submit(form) {
            const response = await fetch(form.action, {
                method: 'POST', // PUT/PATCH/DELETE are sent through the hidden _method field
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: new FormData(form),
            });
            const data = await response.json().catch(function () { return {}; });

            return { response: response, data: data };
        },

        clearErrors(form) {
            form.querySelectorAll('.invalid').forEach(function (input) { input.classList.remove('invalid'); });
            form.querySelectorAll('[data-error-for]').forEach(function (box) { box.style.display = 'none'; });
            AjaxForm.alert(form, null);
        },

        showErrors(form, errors) {
            Object.keys(errors).forEach(function (field) {
                const input = form.querySelector('[name="' + field + '"]');
                const box = form.querySelector('[data-error-for="' + field + '"]');
                if (input) input.classList.add('invalid');
                if (box) {
                    box.textContent = errors[field][0];
                    box.style.display = 'block';
                }
            });
        },

        alert(form, message) {
            const box = form.querySelector('[data-form-alert]');
            if (!box) return;
            box.querySelector('[data-form-alert-text]').textContent = message || '';
            box.style.display = message ? 'flex' : 'none';
        },

        // Submit with loading state; on success follow data.redirect or call onSuccess.
        handle(form, onSuccess) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                const button = form.querySelector('[type="submit"]');
                const label = button.textContent;

                AjaxForm.clearErrors(form);
                button.disabled = true;
                button.textContent = 'Saving...';

                try {
                    const result = await AjaxForm.submit(form);

                    if (result.response.ok) {
                        if (!onSuccess && result.data.redirect) return window.location.assign(result.data.redirect);
                        if (onSuccess) onSuccess(result.data);
                    } else if (result.response.status === 422 && result.data.errors) {
                        AjaxForm.showErrors(form, result.data.errors);
                    } else {
                        AjaxForm.alert(form, result.response.status === 419
                            ? 'Your session expired. Please refresh the page and try again.'
                            : (result.data.message || 'Something went wrong. Please try again.'));
                    }
                } catch (error) {
                    AjaxForm.alert(form, 'Could not reach the server. Please try again.');
                }

                button.disabled = false;
                button.textContent = label;
            });
        },
    };
</script>
@stack('scripts')
</body>
</html>
