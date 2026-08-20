<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['padding' => 'md']));

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

foreach (array_filter((['padding' => 'md']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<article <?php echo e($attributes->merge(['class' => 'ui-card ui-card--padding-'.$padding])); ?>>
    <?php if(isset($header)): ?>
        <div class="ui-card__header"><?php echo e($header); ?></div>
    <?php endif; ?>

    <div class="ui-card__body"><?php echo e($slot); ?></div>

    <?php if(isset($footer)): ?>
        <div class="ui-card__footer"><?php echo e($footer); ?></div>
    <?php endif; ?>
</article>
<?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\components\ui\card.blade.php ENDPATH**/ ?>