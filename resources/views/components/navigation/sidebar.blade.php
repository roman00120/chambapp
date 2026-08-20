<div class="sidebar-inner">
    <x-ui.brand-mark />
    <div class="sidebar-divider"></div>
    @if (auth()->user()->isClient())
        <p class="sidebar-label">Menú principal</p>
        <a class="sidebar-link" href="{{ route('client.dashboard') }}"><i class="bi bi-house-door" aria-hidden="true"></i> Inicio</a>
        <a class="sidebar-link" href="{{ route('marketplace.search') }}"><i class="bi bi-search" aria-hidden="true"></i> Buscar servicios</a>
        <a class="sidebar-link" href="{{ route('client.favorites.index') }}"><i class="bi bi-heart" aria-hidden="true"></i> Favoritos</a>
        <a class="sidebar-link" href="{{ route('client.jobs.index') }}"><i class="bi bi-briefcase" aria-hidden="true"></i> Trabajos</a>
        <a class="sidebar-link" href="{{ route('client.payments.index') }}"><i class="bi bi-receipt" aria-hidden="true"></i> Pagos</a>
    @elseif (auth()->user()->isProfessional())
        <p class="sidebar-label">Menú principal</p>
        <a class="sidebar-link" href="{{ route('professional.dashboard') }}"><i class="bi bi-house-door" aria-hidden="true"></i> Inicio</a>
        <a class="sidebar-link" href="{{ route('professional.services.index') }}"><i class="bi bi-tools" aria-hidden="true"></i> Servicios</a>
        <a class="sidebar-link" href="{{ route('professional.jobs.index') }}"><i class="bi bi-clipboard-check" aria-hidden="true"></i> Solicitudes</a>
        <a class="sidebar-link" href="{{ route('professional.payments.settings') }}"><i class="bi bi-wallet2" aria-hidden="true"></i> Pagos</a>
        <a class="sidebar-link" href="{{ route('professional.commerce.featured') }}"><i class="bi bi-megaphone" aria-hidden="true"></i> Promocionar</a>
        <a class="sidebar-link" href="{{ route('professional.commerce.store') }}"><i class="bi bi-shop" aria-hidden="true"></i> Tienda de perfil</a>
        <a class="sidebar-link" href="{{ route('professional.profile.show') }}"><i class="bi bi-person-circle" aria-hidden="true"></i> Perfil</a>
    @else
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
    @endif
</div>
