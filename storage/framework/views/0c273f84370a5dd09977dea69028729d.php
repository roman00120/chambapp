<div class="sidebar-inner">
    <?php if (isset($component)) { $__componentOriginal8902796569482463d45f9d89e342918c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8902796569482463d45f9d89e342918c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.brand-mark','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.brand-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8902796569482463d45f9d89e342918c)): ?>
<?php $attributes = $__attributesOriginal8902796569482463d45f9d89e342918c; ?>
<?php unset($__attributesOriginal8902796569482463d45f9d89e342918c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8902796569482463d45f9d89e342918c)): ?>
<?php $component = $__componentOriginal8902796569482463d45f9d89e342918c; ?>
<?php unset($__componentOriginal8902796569482463d45f9d89e342918c); ?>
<?php endif; ?>
    <div class="sidebar-divider"></div>
    <?php if(auth()->user()->isClient()): ?>
        <p class="sidebar-label">Menú principal</p>
        <a class="sidebar-link" href="<?php echo e(route('client.dashboard')); ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Inicio</a>
        <a class="sidebar-link" href="<?php echo e(route('marketplace.search')); ?>"><i class="bi bi-search" aria-hidden="true"></i> Buscar servicios</a>
        <a class="sidebar-link" href="<?php echo e(route('client.favorites.index')); ?>"><i class="bi bi-heart" aria-hidden="true"></i> Favoritos</a>
        <a class="sidebar-link" href="<?php echo e(route('client.jobs.index')); ?>"><i class="bi bi-briefcase" aria-hidden="true"></i> Trabajos</a>
        <a class="sidebar-link" href="<?php echo e(route('client.payments.index')); ?>"><i class="bi bi-receipt" aria-hidden="true"></i> Pagos</a>
    <?php elseif(auth()->user()->isProfessional()): ?>
        <p class="sidebar-label">Menú principal</p>
        <a class="sidebar-link" href="<?php echo e(route('professional.dashboard')); ?>"><i class="bi bi-house-door" aria-hidden="true"></i> Inicio</a>
        <a class="sidebar-link" href="<?php echo e(route('professional.services.index')); ?>"><i class="bi bi-tools" aria-hidden="true"></i> Servicios</a>
        <a class="sidebar-link" href="<?php echo e(route('professional.jobs.index')); ?>"><i class="bi bi-clipboard-check" aria-hidden="true"></i> Solicitudes</a>
        <a class="sidebar-link" href="<?php echo e(route('professional.payments.settings')); ?>"><i class="bi bi-wallet2" aria-hidden="true"></i> Pagos</a>
        <a class="sidebar-link" href="<?php echo e(route('professional.profile.show')); ?>"><i class="bi bi-person-circle" aria-hidden="true"></i> Perfil</a>
    <?php else: ?>
        <p class="sidebar-label">Administración</p>
        <a class="sidebar-link <?php echo e(request()->routeIs('admin.dashboard') ? 'sidebar-link--active' : ''); ?>" href="<?php echo e(route('admin.dashboard')); ?>"><i class="bi bi-grid-1x2" aria-hidden="true"></i> Dashboard</a>
        <a class="sidebar-link" href="<?php echo e(route('admin.users.index')); ?>"><i class="bi bi-people" aria-hidden="true"></i> Usuarios</a>
        <a class="sidebar-link" href="<?php echo e(route('admin.professionals.index')); ?>"><i class="bi bi-person-badge" aria-hidden="true"></i> Profesionales</a>
        <a class="sidebar-link" href="<?php echo e(route('admin.categories.index')); ?>"><i class="bi bi-tags" aria-hidden="true"></i> Categorías</a>
        <a class="sidebar-link" href="<?php echo e(route('admin.services.index')); ?>"><i class="bi bi-tools" aria-hidden="true"></i> Servicios</a>
        <a class="sidebar-link" href="<?php echo e(route('admin.jobs.index')); ?>"><i class="bi bi-briefcase" aria-hidden="true"></i> Trabajos</a>
        <a class="sidebar-link" href="<?php echo e(route('admin.payments.index')); ?>"><i class="bi bi-receipt" aria-hidden="true"></i> Pagos</a>
        <a class="sidebar-link" href="<?php echo e(route('admin.commissions.index')); ?>"><i class="bi bi-percent" aria-hidden="true"></i> Comisiones</a>
        <a class="sidebar-link" href="<?php echo e(route('admin.reviews.index')); ?>"><i class="bi bi-star" aria-hidden="true"></i> Reseñas</a>
        <a class="sidebar-link" href="<?php echo e(route('admin.reports.index')); ?>"><i class="bi bi-flag" aria-hidden="true"></i> Reportes</a>
        <a class="sidebar-link" href="<?php echo e(route('admin.disputes.index')); ?>"><i class="bi bi-shield-exclamation" aria-hidden="true"></i> Disputas</a>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\components\navigation\sidebar.blade.php ENDPATH**/ ?>