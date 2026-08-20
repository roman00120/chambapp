<?php $__env->startSection('title', 'Calificar servicio | Chambapp'); ?>

<?php $__env->startSection('content'); ?>
    <section class="review-page"><div class="container"><div class="review-form-shell"><a class="text-link justify-content-start mb-3" href="<?php echo e(route('job-requests.show', $jobRequest)); ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al trabajo</a><p class="eyebrow mb-2">Trabajo completado</p><h1 class="page-title">¿Cómo calificarías el servicio?</h1><p class="section-copy">Tu opinión ayuda a otros clientes a elegir con confianza.</p><?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mt-4','padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4','padding' => 'lg']); ?><div class="review-form-context mb-4"><strong><?php echo e($jobRequest->service?->title ?? $jobRequest->title); ?></strong><span>Profesional: <?php echo e($jobRequest->professional?->user?->name); ?></span></div><?php if($errors->any()): ?><?php if (isset($component)) { $__componentOriginal746de018ded8594083eb43be3f1332e1 = $component; } ?>
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
<?php endif; ?><?php endif; ?><form method="POST" action="<?php echo e(route('reviews.store', $jobRequest)); ?>"><?php echo csrf_field(); ?><div class="mb-4"><fieldset><legend class="form-label">Calificación</legend><div class="rating-input" role="radiogroup" aria-label="Elige una calificación de 1 a 5 estrellas"><?php $__currentLoopData = [5 => 'Excelente', 4 => 'Muy bueno', 3 => 'Regular', 2 => 'Malo', 1 => 'Muy malo']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><input class="rating-input__radio" id="rating-<?php echo e($value); ?>" name="rating" type="radio" value="<?php echo e($value); ?>" <?php if((int) old('rating') === $value): echo 'checked'; endif; ?> required><label class="rating-input__label" for="rating-<?php echo e($value); ?>" title="<?php echo e($label); ?>"><span aria-hidden="true">★</span><span class="visually-hidden"><?php echo e($value); ?> estrellas: <?php echo e($label); ?></span></label><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><p class="rating-input__selected text-muted mt-2 mb-0" data-rating-label>Selecciona de 1 a 5 estrellas.</p></fieldset></div><div class="mb-4"><label class="form-label" for="review-comment">Comentario <span class="text-muted">(opcional)</span></label><textarea class="form-control" id="review-comment" name="comment" rows="5" maxlength="1000" placeholder="Cuéntanos cómo fue el servicio."><?php echo e(old('comment')); ?></textarea><div class="form-text">Máximo 1000 caracteres. No incluyas teléfonos, correos ni enlaces.</div></div><button class="ui-button ui-button--primary w-100" type="submit">Publicar reseña</button></form> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?></div></div></section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\reviews\create.blade.php ENDPATH**/ ?>