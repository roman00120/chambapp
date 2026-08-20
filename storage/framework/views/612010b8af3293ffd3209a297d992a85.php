<?php $__env->startSection('title', 'Estado del pago | Chambapp'); ?>

<?php $__env->startSection('content'); ?>
    <section class="payment-page"><div class="container"><?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'payment-return-card','padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'payment-return-card','padding' => 'lg']); ?><span class="payment-placeholder__icon"><i class="bi bi-<?php echo e($state === 'success' ? 'hourglass-split' : ($state === 'pending' ? 'clock-history' : 'exclamation-triangle')); ?>" aria-hidden="true"></i></span><?php if($state === 'success'): ?><h1 class="page-title">Estamos verificando tu pago</h1><p class="section-copy">La confirmación definitiva llegará mediante Mercado Pago y se reflejará aquí cuando el servidor la valide.</p><?php elseif($state === 'pending'): ?><h1 class="page-title">Tu pago está siendo procesado</h1><p class="section-copy">El trabajo continúa pendiente de pago hasta recibir una confirmación válida.</p><?php else: ?><h1 class="page-title">No pudimos completar el pago</h1><p class="section-copy">Puedes intentar nuevamente desde el resumen del pago. No mostramos detalles internos del procesador.</p><?php endif; ?><a class="ui-button ui-button--outline mt-3" href="<?php echo e(route('client.jobs.index')); ?>">Volver a mis trabajos</a> <?php echo $__env->renderComponent(); ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\payments\return.blade.php ENDPATH**/ ?>