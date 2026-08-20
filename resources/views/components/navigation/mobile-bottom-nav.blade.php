@auth
    <nav class="mobile-bottom-nav d-lg-none" aria-label="Navegación principal móvil">
        @if (auth()->user()->isClient())
            <a class="mobile-bottom-nav__item {{ request()->routeIs('client.dashboard') ? 'is-active' : '' }}" href="{{ route('client.dashboard') }}">
                <i class="bi bi-house-door" aria-hidden="true"></i><span>Inicio</span>
            </a>
            <a class="mobile-bottom-nav__item {{ request()->routeIs('marketplace.*') ? 'is-active' : '' }}" href="{{ route('marketplace.search') }}">
                <i class="bi bi-search" aria-hidden="true"></i><span>Buscar</span>
            </a>
            <a class="mobile-bottom-nav__item {{ request()->routeIs('client.jobs.*', 'job-requests.*') ? 'is-active' : '' }}" href="{{ route('client.jobs.index') }}">
                <i class="bi bi-briefcase" aria-hidden="true"></i><span>Trabajos</span>
            </a>
            <a class="mobile-bottom-nav__item {{ request()->routeIs('client.favorites.*') ? 'is-active' : '' }}" href="{{ route('client.favorites.index') }}">
                <i class="bi bi-heart" aria-hidden="true"></i><span>Favoritos</span>
            </a>
        @elseif (auth()->user()->isProfessional())
            <a class="mobile-bottom-nav__item {{ request()->routeIs('professional.dashboard') ? 'is-active' : '' }}" href="{{ route('professional.dashboard') }}">
                <i class="bi bi-house-door" aria-hidden="true"></i><span>Inicio</span>
            </a>
            <a class="mobile-bottom-nav__item {{ request()->routeIs('professional.services.*') ? 'is-active' : '' }}" href="{{ route('professional.services.index') }}">
                <i class="bi bi-tools" aria-hidden="true"></i><span>Servicios</span>
            </a>
            <a class="mobile-bottom-nav__item {{ request()->routeIs('professional.jobs.*', 'job-requests.*') ? 'is-active' : '' }}" href="{{ route('professional.jobs.index') }}">
                <i class="bi bi-radar" aria-hidden="true"></i><span>Chambas</span>
            </a>
            <a class="mobile-bottom-nav__item {{ request()->routeIs('professional.earnings') ? 'is-active' : '' }}" href="{{ route('professional.earnings') }}">
                <i class="bi bi-cash-coin" aria-hidden="true"></i><span>Ganancias</span>
            </a>
            <a class="mobile-bottom-nav__item {{ request()->routeIs('professional.profile.*') ? 'is-active' : '' }}" href="{{ route('professional.profile.show') }}">
                <i class="bi bi-person" aria-hidden="true"></i><span>Perfil</span>
            </a>
        @else
            <a class="mobile-bottom-nav__item {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-grid-1x2" aria-hidden="true"></i><span>Inicio</span>
            </a>
            <button class="mobile-bottom-nav__item" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-mobile-menu" aria-controls="admin-mobile-menu">
                <i class="bi bi-list" aria-hidden="true"></i><span>Menú</span>
            </button>
            <a class="mobile-bottom-nav__item {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}">
                <i class="bi bi-people" aria-hidden="true"></i><span>Usuarios</span>
            </a>
            <a class="mobile-bottom-nav__item {{ request()->routeIs('admin.reports.*') ? 'is-active' : '' }}" href="{{ route('admin.reports.index') }}">
                <i class="bi bi-flag" aria-hidden="true"></i><span>Reportes</span>
            </a>
        @endif
    </nav>
@endauth
