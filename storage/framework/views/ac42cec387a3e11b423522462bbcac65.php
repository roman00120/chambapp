<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['service', 'isFavorite' => false]));

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

foreach (array_filter((['service', 'isFavorite' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $professional = $service->professional;
    $isVerified = $professional?->verification_status?->value === 'verified';
    $hasReviews = (int) ($professional?->total_reviews ?? 0) > 0;
?>

<article <?php echo e($attributes->merge(['class' => 'marketplace-service-card'])); ?>>
    <div class="marketplace-service-card__media"><?php if($service->coverImage): ?><img src="<?php echo e(IlluminateSupportFacadesStorage::disk('public')->url($service->coverImage->path)); ?>" alt="<?php echo e($service->coverImage->alt_text ?: $service->title); ?>" loading="lazy"><?php else: ?><span class="marketplace-service-card__placeholder" aria-hidden="true"><i class="bi bi-tools"></i></span><?php endif; ?><div class="marketplace-service-card__favorite"><?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->isClient()): ?><form method="POST" action="<?php echo e(route('professional.favorite.toggle', $professional)); ?>"><?php echo csrf_field(); ?><button class="favorite-button <?php echo e($isFavorite ? 'is-favorite' : ''); ?>" type="submit" aria-label="<?php echo e($isFavorite ? 'Quitar de favoritos' : 'Guardar profesional en favoritos'); ?>" aria-pressed="<?php echo e($isFavorite ? 'true' : 'false'); ?>"><i class="bi bi-heart<?php echo e($isFavorite ? '-fill' : ''); ?>" aria-hidden="true"></i></button></form><?php endif; ?> <?php else: ?><a class="favorite-button" href="<?php echo e(route('login')); ?>" aria-label="Inicia sesión para guardar favoritos"><i class="bi bi-heart" aria-hidden="true"></i></a><?php endif; ?></div></div>
    <div class="marketplace-service-card__body"><div class="d-flex align-items-start justify-content-between gap-2 mb-2"><span class="service-card__category mb-0"><?php echo e($service->category?->name ?? 'Servicio'); ?></span><?php if($isVerified): ?><?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => 'verified','label' => 'Verificado','dot' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'verified','label' => 'Verificado','dot' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?><?php endif; ?></div><h2 class="marketplace-service-card__title"><a href="<?php echo e(route('marketplace.service', $service)); ?>"><?php echo e($service->title); ?></a></h2><p class="marketplace-service-card__professional"><i class="bi bi-person-circle" aria-hidden="true"></i> <a href="<?php echo e(route('professional.public-profile', $professional)); ?>"><?php echo e($professional?->user?->name ?? 'Profesional Chambapp'); ?></a></p><div class="marketplace-service-card__meta"><span><i class="bi bi-geo-alt" aria-hidden="true"></i> <?php echo e($professional?->city ?: 'Cerca de ti'); ?></span><?php if($hasReviews): ?><span><i class="bi bi-star-fill" aria-hidden="true"></i> <span class="visually-hidden">Calificación:</span><?php echo e(number_format((float) $professional->average_rating, 1)); ?> (<?php echo e($professional->total_reviews); ?>)</span><?php else: ?><span>Nuevo · Sin reseñas</span><?php endif; ?></div><div class="marketplace-service-card__footer"><strong><?php echo e($service->formattedPrice()); ?></strong><div class="d-flex flex-wrap justify-content-end gap-2"><a class="ui-button ui-button--outline ui-button--sm" href="<?php echo e(route('marketplace.service', $service)); ?>">Ver detalle</a><a class="ui-button ui-button--secondary ui-button--sm" href="<?php echo e(route('job-requests.create', $service)); ?>">Solicitar</a></div></div></div>
</article>
<?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\components\service-card.blade.php ENDPATH**/ ?>