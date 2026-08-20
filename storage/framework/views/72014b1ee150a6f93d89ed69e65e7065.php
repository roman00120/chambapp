<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status', 'audience' => request()->user()?->isProfessional() ? 'professional' : 'client']));

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

foreach (array_filter((['status', 'audience' => request()->user()?->isProfessional() ? 'professional' : 'client']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $value = $status?->value ?? (string) $status;
    [$label, $variant] = match ($value) {
        'pending' => ['Pendiente', 'warning'],
        'searching' => ['Buscando profesional', 'info'],
        'matched' => ['Profesional encontrado', 'success'],
        'awaiting_quote' => ['Esperando cotización', 'warning'],
        'accepted' => ['Aceptado', 'success'],
        'awaiting_payment' => ['Pendiente de pago', 'warning'],
        'paid' => ['Pagado', 'success'],
        'on_the_way' => ['En camino', 'info'],
        'arrived' => ['Llegó', 'info'],
        'processing' => ['Procesando', 'info'],
        'approved' => ['Pago aprobado', 'success'],
        'refunded' => ['Reembolsado', 'neutral'],
        'partially_refunded' => ['Reembolso parcial', 'warning'],
        'cancelled' => ['Cancelado', 'danger'],
        'rejected' => ['Rechazado', 'danger'],
        'in_progress' => ['En proceso', 'info'],
        'awaiting_confirmation' => [$audience === 'professional' ? 'Esperando confirmación del cliente' : 'Esperando tu confirmación', 'warning'],
        'completed' => ['Completado', 'success'],
        'disputed' => ['En disputa', 'danger'],
        'expired' => ['Expirada', 'neutral'],
        default => ['En revisión', 'neutral'],
    };
?>

<?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => $variant,'label' => $label]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($variant),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\components\job-status-badge.blade.php ENDPATH**/ ?>