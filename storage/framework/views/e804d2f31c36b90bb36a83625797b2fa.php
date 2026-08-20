<?php $__env->startSection('title', 'Chamba ahora | Chambapp'); ?>
<?php $__env->startSection('content'); ?>
<section class="form-page"><div class="container container--narrow">
    <a class="text-link" href="<?php echo e(route('client.dashboard')); ?>"><i class="bi bi-arrow-left"></i> Volver</a>
    <div class="page-heading mt-3"><p class="eyebrow">Servicio inmediato</p><h1 class="page-title">¿Qué necesitas resolver ahora?</h1><p class="text-muted">Encontraremos profesionales disponibles cerca de ti. La dirección exacta se protege hasta el pago.</p></div>
    <?php if($errors->any()): ?><?php if (isset($component)) { $__componentOriginal746de018ded8594083eb43be3f1332e1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal746de018ded8594083eb43be3f1332e1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.alert','data' => ['variant' => 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger']); ?><?php echo e($errors->first()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $attributes = $__attributesOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__attributesOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $component = $__componentOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__componentOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?><?php endif; ?>
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'lg']); ?><form method="POST" action="<?php echo e(route('client.ondemand.store')); ?>" enctype="multipart/form-data" data-geolocation-form><?php echo csrf_field(); ?>
        <div class="mb-3"><label class="form-label" for="category_id">Categoría</label><select class="form-select" id="category_id" name="category_id" required><option value="">Selecciona una categoría</option><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($category->id); ?>" <?php if(old('category_id') == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div class="mb-3"><label class="form-label" for="title">Título breve</label><input class="form-control" id="title" name="title" maxlength="160" value="<?php echo e(old('title')); ?>" placeholder="Ej. Fuga de agua en la cocina" required></div>
        <div class="mb-3"><label class="form-label" for="description">¿Qué pasó?</label><textarea class="form-control" id="description" name="description" rows="4" maxlength="1200" required><?php echo e(old('description')); ?></textarea></div>
        <input type="hidden" name="latitude" data-latitude value="<?php echo e(old('latitude')); ?>"><input type="hidden" name="longitude" data-longitude value="<?php echo e(old('longitude')); ?>">
        <div class="on-demand-location mb-3"><div class="d-flex flex-wrap align-items-center justify-content-between gap-2"><div><h2 class="h6 mb-1">Ubicación</h2><p class="small text-muted mb-0" data-geolocation-status>Comparte tu ubicación para encontrar ayuda cerca.</p></div><button class="ui-button ui-button--outline ui-button--sm" type="button" data-geolocate><i class="bi bi-crosshair"></i> Usar mi ubicación</button></div><div class="row g-2 mt-2"><div class="col-12"><label class="form-label" for="address">Dirección o referencia</label><input class="form-control" id="address" name="address" maxlength="255" value="<?php echo e(old('address')); ?>" placeholder="Calle, número y referencia"></div><div class="col-6"><label class="form-label" for="city">Ciudad</label><input class="form-control" id="city" name="city" maxlength="100" value="<?php echo e(old('city')); ?>" required></div><div class="col-6"><label class="form-label" for="state">Estado</label><input class="form-control" id="state" name="state" maxlength="100" value="<?php echo e(old('state')); ?>" required></div><div class="col-6"><label class="form-label" for="postal_code">C.P.</label><input class="form-control" id="postal_code" name="postal_code" maxlength="10" value="<?php echo e(old('postal_code')); ?>"></div></div></div>
        <div class="mb-4"><label class="form-label" for="photos">Fotos opcionales (máximo 3)</label><input class="form-control" id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple><div class="form-text">No incluyas teléfonos, correos ni datos sensibles.</div></div>
        <div class="d-flex flex-column flex-sm-row gap-2"><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><i class="bi bi-search"></i> Buscar profesional ahora <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['href' => ''.e(route('client.scheduled.create')).'','variant' => 'outline']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('client.scheduled.create')).'','variant' => 'outline']); ?>Programar para después <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></div>
    </form> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
</div></section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\client\on-demand\create.blade.php ENDPATH**/ ?>