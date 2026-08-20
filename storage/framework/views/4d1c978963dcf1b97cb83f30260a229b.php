<?php $__env->startSection('title', 'Reseñas | Administración'); ?>
<?php $__env->startSection('content'); ?>
<section class="admin-page"><div class="container"><div class="page-heading"><div><p class="eyebrow">Moderación</p><h1 class="page-title">Reseñas</h1></div></div><?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mb-4','padding' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4','padding' => 'md']); ?><form class="admin-filter-bar" method="GET"><select class="form-select form-select-sm" name="rating"><option value="">Todas las calificaciones</option><?php $__currentLoopData = [5,4,3,2,1]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($rating); ?>" <?php if((string) request('rating') === (string) $rating): echo 'selected'; endif; ?>><?php echo e($rating); ?> estrellas</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><select class="form-select form-select-sm" name="hidden"><option value="">Visibles y ocultas</option><option value="0" <?php if(request('hidden') === '0'): echo 'selected'; endif; ?>>Visibles</option><option value="1" <?php if(request('hidden') === '1'): echo 'selected'; endif; ?>>Ocultas</option></select><button class="ui-button ui-button--primary ui-button--sm">Filtrar</button></form> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?><div class="admin-table-wrap"><table class="table admin-table"><thead><tr><th>Cliente</th><th>Profesional</th><th>Rating</th><th>Comentario</th><th>Estado</th><th></th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($review->client->name); ?></td><td><?php echo e($review->professional->user->name); ?></td><td><?php echo e($review->rating); ?> / 5</td><td><?php echo e(\Illuminate\Support\Str::limit($review->comment, 80)); ?></td><td><?php echo e($review->is_hidden ? 'Oculta' : 'Visible'); ?> · <?php echo e($review->reports_count); ?> reportes</td><td><a class="ui-button ui-button--outline ui-button--sm" href="<?php echo e(route('admin.reviews.show', $review)); ?>">Revisar</a></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6">Sin reseñas.</td></tr><?php endif; ?></tbody></table></div><?php if($reviews->hasPages()): ?><div class="mt-4"><?php if (isset($component)) { $__componentOriginal41032d87daf360242eb88dbda6c75ed1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41032d87daf360242eb88dbda6c75ed1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination','data' => ['paginator' => $reviews]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reviews)]); ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\admin\reviews\index.blade.php ENDPATH**/ ?>