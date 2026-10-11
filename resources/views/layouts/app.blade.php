<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · {{ config('app.name') }}</title>
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
</script>
@stack('scripts')
</body>
</html>
