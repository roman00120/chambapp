<?php $__env->startSection('title', 'Mis trabajos | Chambapp'); ?>

<?php $__env->startSection('content'); ?>
    <section class="job-page"><div class="container"><div class="page-heading"><div><p class="eyebrow mb-2"><i class="bi bi-briefcase" aria-hidden="true"></i> Tu actividad</p><h1 class="page-title">Mis trabajos</h1><p class="section-copy mb-0">Consulta tus solicitudes y el avance de cada trabajo.</p></div><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['href' => ''.e(route('marketplace.search')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('marketplace.search')).'']); ?><i class="bi bi-search" aria-hidden="true"></i> Buscar servicios <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></div><div class="job-filter-bar" role="navigation" aria-label="Filtrar trabajos"><?php $__currentLoopData = ['all' => 'Todos', 'pending' => 'Pendientes', 'active' => 'Activos', 'completed' => 'Completados', 'cancelled' => 'Cancelados']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="job-filter <?php echo e($filter === $value ? 'is-active' : ''); ?>" href="<?php echo e(route('client.jobs.index', ['status' => $value])); ?>"><?php echo e($label); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><div class="job-list"><?php $__empty_1 = true; $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jobRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><article class="job-card"><div class="job-card__header"><div><p class="job-card__eyebrow"><?php echo e($jobRequest->service?->title ?? 'Solicitud de trabajo'); ?></p><h2 class="job-card__title"><a href="<?php echo e(route('job-requests.show', $jobRequest)); ?>"><?php echo e($jobRequest->title); ?></a></h2></div><?php if (isset($component)) { $__componentOriginal4cad57415998541528befff6353295c4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cad57415998541528befff6353295c4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.job-status-badge','data' => ['status' => $jobRequest->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('job-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jobRequest->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4cad57415998541528befff6353295c4)): ?>
<?php $attributes = $__attributesOriginal4cad57415998541528befff6353295c4; ?>
<?php unset($__attributesOriginal4cad57415998541528befff6353295c4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4cad57415998541528befff6353295c4)): ?>
<?php $component = $__componentOriginal4cad57415998541528befff6353295c4; ?>
<?php unset($__componentOriginal4cad57415998541528befff6353295c4); ?>
<?php endif; ?></div><div class="job-card__meta"><span><i class="bi bi-person" aria-hidden="true"></i> <?php echo e($jobRequest->professional?->user?->name ?? 'Profesional'); ?></span><span><i class="bi bi-calendar3" aria-hidden="true"></i> <?php echo e($jobRequest->formattedRequestedDate()); ?></span><span><i class="bi bi-cash-coin" aria-hidden="true"></i> <?php echo e($jobRequest->formattedAgreedPrice()); ?></span></div><div class="job-card__footer"><span class="text-muted small">Creado <?php echo e($jobRequest->created_at->locale('es')->translatedFormat('d M Y')); ?></span><a class="ui-button ui-button--outline ui-button--sm" href="<?php echo e(route('job-requests.show', $jobRequest)); ?>">Ver detalle</a></div></article><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="job-empty"><?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'bi-briefcase','title' => 'Aún no tienes trabajos.','description' => 'Encuentra un servicio y envía tu primera solicitud.','action' => 'Buscar servicios','actionHref' => route('marketplace.search')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-briefcase','title' => 'Aún no tienes trabajos.','description' => 'Encuentra un servicio y envía tu primera solicitud.','action' => 'Buscar servicios','action-href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('marketplace.search'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $attributes = $__attributesOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__attributesOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $component = $__componentOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__componentOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?></div><?php endif; ?></div><?php if($jobs->hasPages()): ?><div class="mt-4"><?php if (isset($component)) { $__componentOriginal41032d87daf360242eb88dbda6c75ed1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41032d87daf360242eb88dbda6c75ed1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination','data' => ['paginator' => $jobs]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jobs)]); ?>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\jobs\client\index.blade.php ENDPATH**/ ?>