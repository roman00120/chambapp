<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'Tu espacio seguro en Chambapp.')">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#f28c28">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ asset('images/pwa/icon-192.png') }}" sizes="192x192" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/icon-192.png') }}">
    <title>@yield('title', 'Chambapp')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    <a class="skip-link" href="#main-content">Saltar al contenido</a>

    <div class="app-layout">
        <aside class="desktop-sidebar d-none d-lg-flex">
            <x-navigation.sidebar />
        </aside>

        <div class="app-layout__main">
            <x-navigation.navbar />

            <main id="main-content">
                <x-ui.flash />
                @yield('content')
            </main>

            <x-navigation.footer />
        </div>
    </div>

    <x-navigation.admin-mobile-menu />
    <x-navigation.mobile-bottom-nav />
    <x-ui.toast />
    <x-ui.confirm-modal />
</body>
</html>
