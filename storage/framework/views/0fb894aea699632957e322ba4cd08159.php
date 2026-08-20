<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['review', 'reportable' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['review', 'reportable' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<article class="review-card">
    <div class="review-card__header">
        <div class="d-flex align-items-center gap-2">
            <?php if (isset($component)) { $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.avatar','data' => ['name' => $review->publicClientName(),'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($review->publicClientName()),'size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $attributes = $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $component = $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
            <div><strong><?php echo e($review->publicClientName()); ?></strong><small class="d-block text-muted"><?php echo e($review->created_at?->locale('es')->translatedFormat('d M Y')); ?></small></div>
        </div>
        <span class="review-card__rating" aria-label="<?php echo e($review->rating); ?> de 5 estrellas"><?php echo e(str_repeat('★', $review->rating)); ?><span class="visually-hidden"> <?php echo e($review->rating); ?> de 5</span></span>
    </div>
    <?php if($review->comment): ?><p class="review-card__comment"><?php echo e($review->comment); ?></p><?php endif; ?>
    <div class="review-card__footer"><span><i class="bi bi-patch-check-fill" aria-hidden="true"></i> Trabajo realizado en Chambapp</span><?php if($review->jobRequest?->service): ?><span>Servicio: <?php echo e($review->jobRequest->service->title); ?></span><?php endif; ?></div>
    <?php if($reportable && auth()->check() && auth()->user()->isProfessional()): ?>
        <details class="review-card__report mt-3"><summary>Reportar reseña</summary><form method="POST" action="<?php echo e(route('reviews.report', $review)); ?>" class="mt-2"><?php echo csrf_field(); ?><div class="mb-2"><label class="visually-hidden" for="report-reason-<?php echo e($review->id); ?>">Motivo</label><select class="form-select form-select-sm" id="report-reason-<?php echo e($review->id); ?>" name="reason" required><option value="">Motivo</option><option value="offensive">Contenido ofensivo</option><option value="personal_data">Datos personales</option><option value="spam">Spam</option><option value="unrelated">No relacionado</option></select></div><textarea class="form-control form-control-sm mb-2" name="description" maxlength="500" placeholder="Detalle opcional"></textarea><button class="ui-button ui-button--outline ui-button--sm" type="submit">Enviar reporte</button></form></details>
    <?php endif; ?>
</article>
<?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views/components/review-card.blade.php ENDPATH**/ ?>