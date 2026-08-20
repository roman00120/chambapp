<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'Chambapp conecta a clientes con profesionales de confianza.')">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    <meta name="theme-color" content="#071735">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ asset('images/pwa/icon-192.png') }}" sizes="192x192" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/icon-192.png') }}">
    <title>@yield('title', 'Chambapp')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-shell">
    <a class="skip-link" href="#main-content">Saltar al contenido</a>
    <x-navigation.navbar />

    <main id="main-content">
        <x-ui.flash />
        @yield('content')
    </main>

    <x-navigation.footer />
    <x-ui.toast />
</body>
</html>
