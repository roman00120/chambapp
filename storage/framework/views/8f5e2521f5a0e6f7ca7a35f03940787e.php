<?php $__env->startSection('title', 'Reseña #'.$review->id.' | Administración'); ?>
<?php $__env->startSection('content'); ?>
<section class="admin-page"><div class="container"><a class="text-link" href="<?php echo e(route('admin.reviews.index')); ?>">← Reseñas</a><?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mt-3','padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-3','padding' => 'lg']); ?><p class="eyebrow">Reseña #<?php echo e($review->id); ?></p><h1 class="page-title h3"><?php echo e($review->rating); ?> / 5 · <?php echo e($review->client->name); ?></h1><p><?php echo e($review->comment ?: 'Sin comentario.'); ?></p><p>Profesional: <?php echo e($review->professional->user->name); ?> · Trabajo: #<?php echo e($review->job_request_id); ?></p><p>Estado: <?php echo e($review->is_hidden ? 'Oculta' : 'Visible'); ?></p><form method="POST" action="<?php echo e(route('admin.reviews.moderate', $review)); ?>" data-confirm-form data-confirm-message="La moderación no cambia el rating original." data-confirm-submit="Confirmar moderación"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><select class="form-select mb-2" name="action" required><option value="hide">Ocultar por incumplimiento</option><option value="restore">Restaurar reseña</option></select><textarea class="form-control mb-3" name="reason" maxlength="1000" placeholder="Motivo interno; requerido al ocultar"></textarea><button class="ui-button ui-button--primary" type="submit">Aplicar moderación</button></form> <?php echo $__env->renderComponent(); ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\admin\reviews\show.blade.php ENDPATH**/ ?>