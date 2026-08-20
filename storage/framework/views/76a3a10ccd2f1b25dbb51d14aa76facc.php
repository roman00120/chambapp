<?php $__env->startSection('title', 'Disputa #'.$dispute->id.' | Administración'); ?>
<?php $__env->startSection('content'); ?>
<section class="admin-page"><div class="container"><a class="text-link" href="<?php echo e(route('admin.disputes.index')); ?>">← Disputas</a><?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mt-3','padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-3','padding' => 'lg']); ?><p class="eyebrow">Disputa #<?php echo e($dispute->id); ?> · Trabajo #<?php echo e($dispute->job_request_id); ?></p><h1 class="page-title h3"><?php echo e($dispute->reason); ?></h1><p><?php echo e($dispute->description ?: 'Sin descripción adicional.'); ?></p><p>Cliente: <?php echo e($dispute->jobRequest->client->name); ?> · Profesional: <?php echo e($dispute->jobRequest->professional->user->name); ?></p><p>Precio acordado: <?php echo e($dispute->jobRequest->formattedAgreedPrice()); ?> · Pago: <?php echo e($dispute->jobRequest->payment?->status?->value ?: 'Sin pago'); ?></p><p>Estado: <?php echo e($dispute->status->value); ?></p><form method="POST" action="<?php echo e(route('admin.disputes.status', $dispute)); ?>" data-confirm-form data-confirm-message="La resolución no modifica automáticamente el pago." data-confirm-submit="Actualizar disputa"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><div class="d-flex flex-wrap gap-2"><?php $__currentLoopData = ['reviewing' => 'Marcar revisando', 'resolved' => 'Resolver', 'rejected' => 'Rechazar']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><button class="ui-button ui-button--outline" name="status" value="<?php echo e($status); ?>" type="submit"><?php echo e($label); ?></button><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div></form> <?php echo $__env->renderComponent(); ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\admin\disputes\show.blade.php ENDPATH**/ ?>