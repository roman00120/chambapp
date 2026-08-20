<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['professional', 'isFavorite' => false]));

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

foreach (array_filter((['professional', 'isFavorite' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $hasReviews = (int) ($professional->total_reviews ?? 0) > 0;
?>

<article <?php echo e($attributes->merge(['class' => 'marketplace-professional-card'])); ?>>
    <div class="d-flex align-items-start justify-content-between gap-3 mb-3"><?php if (isset($component)) { $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.avatar','data' => ['user' => $professional->user,'src' => $professional->profile_photo,'name' => $professional->user?->name,'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($professional->user),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($professional->profile_photo),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($professional->user?->name),'size' => 'lg']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $attributes = $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $component = $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?><?php if($professional->verification_status?->value === 'verified'): ?><?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
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
<?php endif; ?><?php endif; ?></div>
    <h2 class="marketplace-professional-card__name"><a href="<?php echo e(route('professional.public-profile', $professional)); ?>"><?php echo e($professional->user?->name ?? 'Profesional Chambapp'); ?></a></h2>
    <p class="marketplace-professional-card__location"><i class="bi bi-geo-alt" aria-hidden="true"></i> <?php echo e(collect([$professional->city, $professional->state])->filter()->join(', ') ?: 'Cerca de ti'); ?></p>
    <p class="marketplace-professional-card__bio"><?php echo e($professional->bio ?: 'Profesional listo para ayudarte.'); ?></p>
    <div class="marketplace-professional-card__meta mb-3"><?php if($hasReviews): ?><span><i class="bi bi-star-fill" aria-hidden="true"></i> <?php echo e(number_format((float) $professional->average_rating, 1)); ?> (<?php echo e($professional->total_reviews); ?>)</span><?php else: ?><span>Nuevo · Sin reseñas todavía</span><?php endif; ?></div>
    <div class="d-flex align-items-center justify-content-between gap-2"><a class="ui-button ui-button--outline ui-button--sm" href="<?php echo e(route('professional.public-profile', $professional)); ?>">Ver perfil</a><?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->isClient()): ?><form method="POST" action="<?php echo e(route('professional.favorite.toggle', $professional)); ?>"><?php echo csrf_field(); ?><button class="favorite-button favorite-button--inline <?php echo e($isFavorite ? 'is-favorite' : ''); ?>" type="submit" aria-label="<?php echo e($isFavorite ? 'Quitar de favoritos' : 'Guardar profesional en favoritos'); ?>" aria-pressed="<?php echo e($isFavorite ? 'true' : 'false'); ?>"><i class="bi bi-heart<?php echo e($isFavorite ? '-fill' : ''); ?>" aria-hidden="true"></i></button></form><?php endif; ?> <?php else: ?><a class="favorite-button favorite-button--inline" href="<?php echo e(route('login')); ?>" aria-label="Inicia sesión para guardar favoritos"><i class="bi bi-heart" aria-hidden="true"></i></a><?php endif; ?></div>
</article>
<?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\components\professional-card.blade.php ENDPATH**/ ?>