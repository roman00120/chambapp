<?php $__env->startSection('title', $service->title.' | Administración'); ?>
<?php $__env->startSection('content'); ?>
<section class="admin-page"><div class="container"><a class="text-link" href="<?php echo e(route('admin.services.index')); ?>">← Servicios</a><?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mt-3','padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-3','padding' => 'lg']); ?><p class="eyebrow">Servicio #<?php echo e($service->id); ?></p><h1 class="page-title h3"><?php echo e($service->title); ?></h1><p><?php echo e($service->description); ?></p><p>Profesional: <?php echo e($service->professional->user->name); ?> · Categoría: <?php echo e($service->category->name); ?></p><p>Precio: <?php echo e($service->formattedPrice()); ?> · <?php echo e($service->is_active ? 'Activo' : 'Inactivo'); ?> · <?php echo e($service->is_featured ? 'Destacado' : 'No destacado'); ?></p><div class="d-flex flex-wrap gap-2"><?php $__currentLoopData = ['activate' => 'Activar', 'deactivate' => 'Desactivar', 'feature' => 'Destacar', 'unfeature' => 'Quitar destacado']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><form method="POST" action="<?php echo e(route('admin.services.moderate', $service)); ?>" data-confirm-form data-confirm-message="Confirma esta acción de moderación." data-confirm-submit="Aplicar"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><input type="hidden" name="action" value="<?php echo e($action); ?>"><button class="ui-button ui-button--outline" type="submit"><?php echo e($label); ?></button></form><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?></div></section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\admin\services\show.blade.php ENDPATH**/ ?>