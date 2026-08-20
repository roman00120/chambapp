<footer class="site-footer">
    <div class="container py-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
            <div>
                <?php if (isset($component)) { $__componentOriginal8902796569482463d45f9d89e342918c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8902796569482463d45f9d89e342918c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.brand-mark','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.brand-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8902796569482463d45f9d89e342918c)): ?>
<?php $attributes = $__attributesOriginal8902796569482463d45f9d89e342918c; ?>
<?php unset($__attributesOriginal8902796569482463d45f9d89e342918c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8902796569482463d45f9d89e342918c)): ?>
<?php $component = $__componentOriginal8902796569482463d45f9d89e342918c; ?>
<?php unset($__componentOriginal8902796569482463d45f9d89e342918c); ?>
<?php endif; ?>
                <p class="small text-body-secondary mt-2 mb-0">Servicios que se sienten cerca.</p>
            </div>
            <div class="d-flex flex-column align-items-start align-items-sm-end gap-1">
                <p class="small text-body-secondary mb-0">&copy; <?php echo e(now()->year); ?> Chambapp</p>
                <div class="d-flex gap-3 small">
                    <a class="text-link" href="<?php echo e(route('legal.terms')); ?>">Términos</a>
                    <a class="text-link" href="<?php echo e(route('legal.privacy')); ?>">Privacidad</a>
                </div>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views/components/navigation/footer.blade.php ENDPATH**/ ?>