<?php ($unreadNotifications = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0); ?>
<header class="site-header">
    <nav class="navbar navbar-expand-lg" aria-label="Navegación principal">
        <div class="container">
            <?php if (isset($component)) { $__componentOriginal8902796569482463d45f9d89e342918c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8902796569482463d45f9d89e342918c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.brand-mark','data' => ['class' => 'navbar-brand']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.brand-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'navbar-brand']); ?>
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-navigation" aria-controls="main-navigation" aria-expanded="false" aria-label="Abrir menú"><span class="site-menu-icon" aria-hidden="true"><span></span><span></span><span></span></span></button>
            <div class="collapse navbar-collapse" id="main-navigation">
                <ul class="navbar-nav site-nav-primary">
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(route('marketplace.search')); ?>">Buscar</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(route('home')); ?>#como-funciona">Cómo funciona</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(route('marketplace.categories')); ?>">Categorías</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(route('home')); ?>#profesionales">Para profesionales <i class="bi bi-chevron-down" aria-hidden="true"></i></a></li>
                </ul>
                <ul class="navbar-nav site-nav-actions">
                <?php if(auth()->guard()->check()): ?>
                    <li class="nav-item"><a class="nav-link notification-nav-link" href="<?php echo e(route('notifications.index')); ?>" aria-label="Notificaciones"><i class="bi bi-bell" aria-hidden="true"></i><?php if($unreadNotifications > 0): ?><span class="notification-nav-link__count"><?php echo e($unreadNotifications > 9 ? '9+' : $unreadNotifications); ?></span><?php endif; ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(route(auth()->user()->dashboardRoute())); ?>">Mi inicio</a></li>
                    <li class="nav-item nav-item--greeting">Hola, <?php echo e(auth()->user()->name); ?></li>
                    <li class="nav-item"><form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button class="btn btn-outline-primary w-100" type="submit">Cerrar sesión</button></form></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(route('login')); ?>">Iniciar sesión</a></li>
                    <li class="nav-item"><a class="btn btn-outline-primary w-100" href="<?php echo e(route('register')); ?>">Crear cuenta</a></li>
                <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views/components/navigation/navbar.blade.php ENDPATH**/ ?>