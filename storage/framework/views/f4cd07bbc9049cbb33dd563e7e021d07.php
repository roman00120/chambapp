<?php $__env->startSection('title', 'Trabajo #'.$job->id.' | Administración'); ?>
<?php $__env->startSection('content'); ?>
<section class="admin-page"><div class="container"><a class="text-link" href="<?php echo e(route('admin.jobs.index')); ?>">← Trabajos</a><?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mt-3','padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-3','padding' => 'lg']); ?><p class="eyebrow">Trabajo #<?php echo e($job->id); ?></p><h1 class="page-title h3"><?php echo e($job->title); ?></h1><p><?php echo e($job->client->name); ?> · <?php echo e($job->professional?->user?->name ?? 'Sin profesional asignado'); ?> · <?php echo e($job->formattedAgreedPrice()); ?></p><p class="small text-muted">Modo: <?php echo e($job->service_mode?->value ?? 'scheduled'); ?> · Categoría: <?php echo e($job->category?->name ?? $job->service?->category?->name ?? 'Sin categoría'); ?> · Invitaciones: <?php echo e($job->invitations->count()); ?></p><?php if (isset($component)) { $__componentOriginal4cad57415998541528befff6353295c4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cad57415998541528befff6353295c4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.job-status-badge','data' => ['status' => $job->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('job-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($job->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4cad57415998541528befff6353295c4)): ?>
<?php $attributes = $__attributesOriginal4cad57415998541528befff6353295c4; ?>
<?php unset($__attributesOriginal4cad57415998541528befff6353295c4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4cad57415998541528befff6353295c4)): ?>
<?php $component = $__componentOriginal4cad57415998541528befff6353295c4; ?>
<?php unset($__componentOriginal4cad57415998541528befff6353295c4); ?>
<?php endif; ?><hr><h2 class="h5">Timeline administrativo</h2><ol class="job-timeline"><li class="is-done">Solicitud · <?php echo e($job->created_at->format('d/m/Y H:i')); ?></li><?php if($job->search_started_at): ?><li class="is-done">Búsqueda iniciada · <?php echo e($job->search_started_at->format('d/m/Y H:i')); ?></li><?php endif; ?> <?php if($job->matched_at): ?><li class="is-done">Match · <?php echo e($job->matched_at->format('d/m/Y H:i')); ?></li><?php endif; ?> <?php if($job->on_the_way_at): ?><li class="is-done">En camino · <?php echo e($job->on_the_way_at->format('d/m/Y H:i')); ?></li><?php endif; ?> <?php if($job->arrived_at): ?><li class="is-done">Llegada · <?php echo e($job->arrived_at->format('d/m/Y H:i')); ?></li><?php endif; ?> <?php if($job->accepted_at): ?><li class="is-done">Cotización aceptada · <?php echo e($job->accepted_at->format('d/m/Y H:i')); ?></li><?php endif; ?> <?php if($job->payment?->paid_at): ?><li class="is-done">Pago aprobado · <?php echo e($job->payment->paid_at->format('d/m/Y H:i')); ?></li><?php endif; ?> <?php if($job->started_at): ?><li class="is-done">Inicio · <?php echo e($job->started_at->format('d/m/Y H:i')); ?></li><?php endif; ?> <?php if($job->finished_at): ?><li class="is-done">Esperando confirmación · <?php echo e($job->finished_at->format('d/m/Y H:i')); ?></li><?php endif; ?> <?php if($job->completed_at): ?><li class="is-done">Completado · <?php echo e($job->completed_at->format('d/m/Y H:i')); ?></li><?php endif; ?> <?php if($job->dispute): ?><li class="is-done">Disputa · <?php echo e($job->dispute->created_at->format('d/m/Y H:i')); ?></li><?php endif; ?></ol><p class="small text-muted mb-0">El panel no permite saltos arbitrarios de estado.</p> <?php echo $__env->renderComponent(); ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\admin\jobs\show.blade.php ENDPATH**/ ?>