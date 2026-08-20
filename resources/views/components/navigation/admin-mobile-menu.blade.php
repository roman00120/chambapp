@auth
    @if (auth()->user()->isAdmin())
        <div class="offcanvas offcanvas-start" tabindex="-1" id="admin-mobile-menu" aria-labelledby="admin-mobile-menu-title">
            <div class="offcanvas-header"><h2 class="offcanvas-title h5" id="admin-mobile-menu-title">Administración</h2><button class="btn-close" type="button" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button></div>
            <div class="offcanvas-body"><nav class="d-grid gap-2" aria-label="Menú administrativo móvil">@foreach ([['admin.dashboard','Dashboard','bi-grid-1x2'],['admin.users.index','Usuarios','bi-people'],['admin.professionals.index','Profesionales','bi-person-badge'],['admin.categories.index','Categorías','bi-tags'],['admin.services.index','Servicios','bi-tools'],['admin.jobs.index','Trabajos','bi-briefcase'],['admin.payments.index','Pagos','bi-receipt'],['admin.commissions.index','Comisiones','bi-percent'],['admin.reviews.index','Reseñas','bi-star'],['admin.reports.index','Reportes','bi-flag'],['admin.disputes.index','Disputas','bi-shield-exclamation']] as [$route, $label, $icon])<a class="sidebar-link" href="{{ route($route) }}" data-bs-dismiss="offcanvas"><i class="bi {{ $icon }}" aria-hidden="true"></i> {{ $label }}</a>@endforeach</nav></div>
        </div>
    @endif
@endauth
