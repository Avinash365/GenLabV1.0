@php($logo = $appSettings['site_logo_url'] ?? url('assets/img/logo.svg'))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,600">
    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body class="antialiased">
<div class="flex flex-col justify-center min-h-screen py-6 sm:py-12">
    <div>
        <a href="{{ url('/') }}" class="login-brand">
            <img src="{{ $logo }}" alt="Logo" style="max-height:64px; object-fit:contain;">
        </a>
    </div>
    <div class="w-full px-6 py-4 mx-auto bg-white rounded-lg shadow-md sm:max-w-md">
        {{ $slot }}
    </div>
</div>
<!-- Scripts -->
<script src="{{ mix('js/app.js') }}" defer></script>
</body>
</html>