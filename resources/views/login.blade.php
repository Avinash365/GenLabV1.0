@php($logo = $appSettings['site_logo_url'] ?? url('assets/img/logo.svg'))
<a href="{{ url('/') }}" class="login-brand">
    <img src="{{ $logo }}" alt="Logo" style="max-height:64px; object-fit:contain;">
</a>