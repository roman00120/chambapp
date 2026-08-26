<div class="sidebar-inner">
    <x-ui.brand-mark />
    <div class="sidebar-divider"></div>

    @if (auth()->user()->canActAsClient() && auth()->user()->canActAsProfessional())
        <div class="px-2 mb-3">
            <p class="sidebar-label mb-1">Usar Chambapp como:</p>
            <div class="btn-group w-100" role="group" aria-label="Selector de modo">
                <form method="POST" action="{{ route('active-mode.switch') }}" class="w-50">
                    @csrf
                    <input type="hidden" name="mode" value="client">
                    <button type="submit" class="btn btn-sm w-100 {{ (request()->routeIs('client.*') || session('active_mode') === 'client' || (!request()->routeIs('professional.*') && !request()->routeIs('admin.*') && session('active_mode') !== 'professional')) ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="bi bi-person" aria-hidden="true"></i> Cliente
                    </button>
                </form>
                <form method="POST" action="{{ route('active-mode.switch') }}" class="w-50 ms-1">
                    @csrf
                    <input type="hidden" name="mode" value="professional">
                    <button type="submit" class="btn btn-sm w-100 {{ (request()->routeIs('professional.*') || session('active_mode') === 'professional') ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="bi bi-tools" aria-hidden="true"></i> Profesional
                    </button>
                </form>
            </div>
        </div>
        <div class="sidebar-divider"></div>
    @endif

    @if (request()->routeIs('admin.*') && auth()->user()->isAdmin())
        <p class="sidebar-label">Administración</p>
        <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'sidebar-link--active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-1x2" aria-hidden="true"></i> Dashboard</a>
        <a class="sidebar-link" href="{{ route('admin.users.index') }}"><i class="bi bi-people" aria-hidden="true"></i> Usuarios</a>
        <a class="sidebar-link" href="{{ route('admin.professionals.index') }}"><i class="bi bi-person-badge" aria-hidden="true"></i> Profesionales</a>
        <a class="sidebar-link" href="{{ route('admin.categories.index') }}"><i class="bi bi-tags" aria-hidden="true"></i> Categorías</a>
        <a class="sidebar-link" href="{{ route('admin.services.index') }}"><i class="bi bi-tools" aria-hidden="true"></i> Servicios</a>
        <a class="sidebar-link" href="{{ route('admin.jobs.index') }}"><i class="bi bi-briefcase" aria-hidden="true"></i> Trabajos</a>
        <a class="sidebar-link" href="{{ route('admin.payments.index') }}"><i class="bi bi-receipt" aria-hidden="true"></i> Pagos</a>
        <a class="sidebar-link" href="{{ route('admin.commissions.index') }}"><i class="bi bi-percent" aria-hidden="true"></i> Comisiones</a>
        <a class="sidebar-link" href="{{ route('admin.reviews.index') }}"><i class="bi bi-star" aria-hidden="true"></i> Reseñas</a>
        <a class="sidebar-link" href="{{ route('admin.reports.index') }}"><i class="bi bi-flag" aria-hidden="true"></i> Reportes</a>
        <a class="sidebar-link" href="{{ route('admin.disputes.index') }}"><i class="bi bi-shield-exclamation" aria-hidden="true"></i> Disputas</a>
    @elseif (request()->routeIs('professional.*') || (session('active_mode') === 'professional' && !request()->routeIs('client.*') && !request()->routeIs('admin.*')) || (auth()->user()->role?->value === 'professional' && !request()->routeIs('client.*') && session('active_mode') !== 'client'))
        <p class="sidebar-label">Menú Profesional</p>
        <a class="sidebar-link" href="{{ route('professional.dashboard') }}"><i class="bi bi-house-door" aria-hidden="true"></i> Inicio</a>
        <a class="sidebar-link" href="{{ route('professional.services.index') }}"><i class="bi bi-tools" aria-hidden="true"></i> Servicios</a>
        <a class="sidebar-link" href="{{ route('professional.jobs.index') }}"><i class="bi bi-clipboard-check" aria-hidden="true"></i> Solicitudes</a>
        <a class="sidebar-link" href="{{ route('professional.payments.settings') }}"><i class="bi bi-wallet2" aria-hidden="true"></i> Pagos</a>
        <a class="sidebar-link" href="{{ route('professional.commerce.featured') }}"><i class="bi bi-megaphone" aria-hidden="true"></i> Promocionar</a>
        <a class="sidebar-link" href="{{ route('professional.commerce.store') }}"><i class="bi bi-shop" aria-hidden="true"></i> Tienda de perfil</a>
        <a class="sidebar-link" href="{{ route('professional.profile.show') }}"><i class="bi bi-person-circle" aria-hidden="true"></i> Perfil</a>
        <a class="sidebar-link {{ request()->routeIs('account.security') ? 'sidebar-link--active' : '' }}" href="{{ route('account.security') }}"><i class="bi bi-shield-check" aria-hidden="true"></i> Seguridad y Reportes</a>
    @else
        <p class="sidebar-label">Menú Cliente</p>
        <a class="sidebar-link" href="{{ route('client.dashboard') }}"><i class="bi bi-house-door" aria-hidden="true"></i> Inicio</a>
        <a class="sidebar-link" href="{{ route('marketplace.search') }}"><i class="bi bi-search" aria-hidden="true"></i> Buscar servicios</a>
        <a class="sidebar-link" href="{{ route('client.favorites.index') }}"><i class="bi bi-heart" aria-hidden="true"></i> Favoritos</a>
        <a class="sidebar-link" href="{{ route('client.jobs.index') }}"><i class="bi bi-briefcase" aria-hidden="true"></i> Trabajos</a>
        <a class="sidebar-link" href="{{ route('client.payments.index') }}"><i class="bi bi-receipt" aria-hidden="true"></i> Pagos</a>
        <a class="sidebar-link {{ request()->routeIs('account.security') ? 'sidebar-link--active' : '' }}" href="{{ route('account.security') }}"><i class="bi bi-shield-check" aria-hidden="true"></i> Seguridad y Reportes</a>
    @endif

    @if (auth()->user()->isAdmin() && !request()->routeIs('admin.*'))
        <div class="sidebar-divider"></div>
        <p class="sidebar-label">Administración</p>
        <a class="sidebar-link text-warning fw-bold" href="{{ route('admin.dashboard') }}"><i class="bi bi-shield-lock" aria-hidden="true"></i> Panel Admin</a>
    @endif
</div>
