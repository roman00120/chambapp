@php($unreadNotifications = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0)
<header class="site-header">
    <nav class="navbar navbar-expand-lg" aria-label="Navegación principal">
        <div class="container">
            <x-ui.brand-mark class="navbar-brand" />
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-navigation" aria-controls="main-navigation" aria-expanded="false" aria-label="Abrir menú"><span class="site-menu-icon" aria-hidden="true"><span></span><span></span><span></span></span></button>
            <div class="collapse navbar-collapse" id="main-navigation">
                <ul class="navbar-nav site-nav-primary">
                    <li class="nav-item"><a class="nav-link" href="{{ route('marketplace.search') }}">Buscar</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#como-funciona">Cómo funciona</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('marketplace.categories') }}">Categorías</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#profesionales">Para profesionales <i class="bi bi-chevron-down" aria-hidden="true"></i></a></li>
                </ul>
                <ul class="navbar-nav site-nav-actions">
                @auth
                    <li class="nav-item"><a class="nav-link notification-nav-link" href="{{ route('notifications.index') }}" aria-label="Notificaciones"><i class="bi bi-bell" aria-hidden="true"></i>@if ($unreadNotifications > 0)<span class="notification-nav-link__count">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route(auth()->user()->dashboardRoute()) }}">Mi inicio</a></li>
                    <li class="nav-item nav-item--greeting">Hola, {{ auth()->user()->name }}</li>
                    <li class="nav-item"><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-primary w-100" type="submit">Cerrar sesión</button></form></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Iniciar sesión</a></li>
                    <li class="nav-item"><a class="btn btn-outline-primary w-100" href="{{ route('register') }}">Crear cuenta</a></li>
                @endauth
                </ul>
            </div>
        </div>
    </nav>
</header>
