<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'Chambapp conecta a clientes con profesionales de confianza.'); ?>">
    <meta name="robots" content="<?php echo $__env->yieldContent('robots', 'index,follow'); ?>">
    <meta name="theme-color" content="#f28c28">
    <link rel="canonical" href="<?php echo e(url()->current()); ?>">
    <link rel="manifest" href="<?php echo e(asset('manifest.webmanifest')); ?>">
    <link rel="icon" href="<?php echo e(asset('images/pwa/icon-192.png')); ?>" sizes="192x192" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/pwa/icon-192.png')); ?>">
    <title><?php echo $__env->yieldContent('title', 'Chambapp'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="public-shell">
    <a class="skip-link" href="#main-content">Saltar al contenido</a>
    <?php if (isset($component)) { $__componentOriginal8f2ea879bb839bb3b4f2f7b50b356091 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f2ea879bb839bb3b4f2f7b50b356091 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.navigation.navbar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('navigation.navbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f2ea879bb839bb3b4f2f7b50b356091)): ?>
<?php $attributes = $__attributesOriginal8f2ea879bb839bb3b4f2f7b50b356091; ?>
<?php unset($__attributesOriginal8f2ea879bb839bb3b4f2f7b50b356091); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f2ea879bb839bb3b4f2f7b50b356091)): ?>
<?php $component = $__componentOriginal8f2ea879bb839bb3b4f2f7b50b356091; ?>
<?php unset($__componentOriginal8f2ea879bb839bb3b4f2f7b50b356091); ?>
<?php endif; ?>

    <main id="main-content">
        <?php if (isset($component)) { $__componentOriginalfc2c53cdc76e51152b8f2296be83e0da = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfc2c53cdc76e51152b8f2296be83e0da = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.flash','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.flash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfc2c53cdc76e51152b8f2296be83e0da)): ?>
<?php $attributes = $__attributesOriginalfc2c53cdc76e51152b8f2296be83e0da; ?>
<?php unset($__attributesOriginalfc2c53cdc76e51152b8f2296be83e0da); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfc2c53cdc76e51152b8f2296be83e0da)): ?>
<?php $component = $__componentOriginalfc2c53cdc76e51152b8f2296be83e0da; ?>
<?php unset($__componentOriginalfc2c53cdc76e51152b8f2296be83e0da); ?>
<?php endif; ?>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php if (isset($component)) { $__componentOriginal901074e185567f5f1d92866b8152d9bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal901074e185567f5f1d92866b8152d9bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.navigation.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('navigation.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal901074e185567f5f1d92866b8152d9bb)): ?>
<?php $attributes = $__attributesOriginal901074e185567f5f1d92866b8152d9bb; ?>
<?php unset($__attributesOriginal901074e185567f5f1d92866b8152d9bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal901074e185567f5f1d92866b8152d9bb)): ?>
<?php $component = $__componentOriginal901074e185567f5f1d92866b8152d9bb; ?>
<?php unset($__componentOriginal901074e185567f5f1d92866b8152d9bb); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal339c7fedf680433726dbafc2f156956f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal339c7fedf680433726dbafc2f156956f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.toast','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal339c7fedf680433726dbafc2f156956f)): ?>
<?php $attributes = $__attributesOriginal339c7fedf680433726dbafc2f156956f; ?>
<?php unset($__attributesOriginal339c7fedf680433726dbafc2f156956f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal339c7fedf680433726dbafc2f156956f)): ?>
<?php $component = $__componentOriginal339c7fedf680433726dbafc2f156956f; ?>
<?php unset($__componentOriginal339c7fedf680433726dbafc2f156956f); ?>
<?php endif; ?>
</body>
</html>
<?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views/layouts/public.blade.php ENDPATH**/ ?>