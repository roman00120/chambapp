<?php $__env->startSection('title', 'Payment #'.$payment->id.' | Administración'); ?>
<?php $__env->startSection('content'); ?>
<section class="admin-page"><div class="container"><a class="text-link" href="<?php echo e(route('admin.payments.index')); ?>">← Pagos</a><?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mt-3','padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-3','padding' => 'lg']); ?><p class="eyebrow">Payment interno #<?php echo e($payment->id); ?></p><h1 class="page-title h3"><?php echo e($payment->status->value); ?></h1><dl class="admin-detail-list"><div><dt>Referencia externa</dt><dd><?php echo e($payment->external_reference ?: 'Sin referencia'); ?></dd></div><div><dt>ID proveedor</dt><dd><?php echo e($payment->external_payment_id ?: 'Sin ID'); ?></dd></div><div><dt>Trabajo</dt><dd>#<?php echo e($payment->job_request_id); ?> · <?php echo e($payment->jobRequest?->title); ?></dd></div><div><dt>Bruto</dt><dd><?php echo e($payment->gross_amount); ?> <?php echo e($payment->currency); ?></dd></div><div><dt>Comisión histórica</dt><dd><?php echo e($payment->platform_fee_percent); ?>% · <?php echo e($payment->platform_fee); ?></dd></div><div><dt>Monto profesional</dt><dd><?php echo e($payment->professional_amount); ?></dd></div><div><dt>Fee proveedor</dt><dd><?php echo e($payment->provider_fee ?: 'No informado'); ?></dd></div></dl><h2 class="h5 mt-4">Eventos</h2><div class="admin-mini-list"><?php $__empty_1 = true; $__currentLoopData = $payment->transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div><strong><?php echo e($transaction->event_type); ?></strong><span><?php echo e($transaction->created_at->format('d/m/Y H:i')); ?> · <?php echo e($transaction->provider_event_id ?: 'interno'); ?></span></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="text-muted">Sin eventos.</p><?php endif; ?></div><p class="small text-muted mt-3 mb-0">Los payloads completos y credenciales del proveedor no se muestran en el panel.</p> <?php echo $__env->renderComponent(); ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\admin\payments\show.blade.php ENDPATH**/ ?>