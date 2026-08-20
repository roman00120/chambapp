<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['variant' => 'info', 'title' => null, 'dismissible' => false]));

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

foreach (array_filter((['variant' => 'info', 'title' => null, 'dismissible' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'ui-alert ui-alert--'.$variant])); ?> role="alert">
    <i class="bi bi-<?php echo e($variant === 'success' ? 'check-circle' : ($variant === 'danger' ? 'exclamation-triangle' : ($variant === 'warning' ? 'exclamation-circle' : 'info-circle'))); ?>" aria-hidden="true"></i>
    <div class="ui-alert__content">
        <?php if($title): ?>
            <strong><?php echo e($title); ?></strong>
        <?php endif; ?>
        <div><?php echo e($slot); ?></div>
    </div>
    <?php if($dismissible): ?>
        <button type="button" class="ui-alert__close" data-bs-dismiss="alert" aria-label="Cerrar aviso">&times;</button>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views/components/ui/alert.blade.php ENDPATH**/ ?>