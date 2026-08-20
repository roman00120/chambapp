<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'required' => false,
    'autocomplete' => null,
]));

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

foreach (array_filter(([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'required' => false,
    'autocomplete' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $inputId = $attributes->get('id', $name);
    $hasError = $errors->has($name);
?>

<div <?php echo e($attributes->only('class')->merge(['class' => 'ui-form-field'])); ?>>
    <?php if($label): ?>
        <label class="form-label" for="<?php echo e($inputId); ?>"><?php echo e($label); ?></label>
    <?php endif; ?>
    <input
        <?php echo e($attributes->except('class')->merge([
            'class' => 'form-control'.($hasError ? ' is-invalid' : ''),
            'id' => $inputId,
            'type' => $type,
            'name' => $name,
            'value' => old($name, $value),
            'placeholder' => $placeholder,
            'autocomplete' => $autocomplete,
            'required' => $required,
        ])); ?>

    >
    <?php if($help): ?>
        <div class="form-text"><?php echo e($help); ?></div>
    <?php endif; ?>
    <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\components\ui\input.blade.php ENDPATH**/ ?>