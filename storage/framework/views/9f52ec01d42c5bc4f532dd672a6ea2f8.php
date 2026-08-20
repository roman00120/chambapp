<?php $__env->startSection('title', 'Categorías | Administración'); ?>
<?php $__env->startSection('content'); ?>
<section class="admin-page"><div class="container"><div class="page-heading"><div><p class="eyebrow">Catálogo</p><h1 class="page-title">Categorías</h1></div><a class="ui-button ui-button--primary" href="<?php echo e(route('admin.categories.create')); ?>">Nueva categoría</a></div><div class="admin-table-wrap"><table class="table admin-table"><thead><tr><th>Nombre</th><th>Slug</th><th>Servicios</th><th>Estado</th><th></th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($category->name); ?></td><td><code><?php echo e($category->slug); ?></code></td><td><?php echo e($category->services_count); ?></td><td><?php echo e($category->is_active ? 'Activa' : 'Inactiva'); ?></td><td><div class="d-flex gap-2"><a class="ui-button ui-button--outline ui-button--sm" href="<?php echo e(route('admin.categories.edit', $category)); ?>">Editar</a><form method="POST" action="<?php echo e(route('admin.categories.toggle', $category)); ?>" data-confirm-form data-confirm-message="Confirma el cambio de estado." data-confirm-submit="Actualizar"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><button class="ui-button ui-button--outline ui-button--sm" type="submit"><?php echo e($category->is_active ? 'Desactivar' : 'Activar'); ?></button></form></div></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5">Sin categorías.</td></tr><?php endif; ?></tbody></table></div><?php if($categories->hasPages()): ?><div class="mt-4"><?php if (isset($component)) { $__componentOriginal41032d87daf360242eb88dbda6c75ed1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41032d87daf360242eb88dbda6c75ed1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination','data' => ['paginator' => $categories]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $attributes = $__attributesOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $component = $__componentOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__componentOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?></div><?php endif; ?></div></section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\admin\categories\index.blade.php ENDPATH**/ ?>