<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['message' => null, 'variant' => 'success']));

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

foreach (array_filter((['message' => null, 'variant' => 'success']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="toast-container position-fixed bottom-0 end-0 p-3" aria-live="polite" aria-atomic="true">
    <div class="toast ui-toast ui-toast--<?php echo e($variant); ?>" role="status" data-ui-toast>
        <div class="toast-body d-flex align-items-center gap-2">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <span data-ui-toast-message><?php echo e($message ?? 'Listo.'); ?></span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\components\ui\toast.blade.php ENDPATH**/ ?>