<?php $__env->startSection('title', 'Categorías de servicios | Chambapp'); ?>
<?php $__env->startSection('meta_description', 'Explora categorías de servicios y encuentra profesionales en Chambapp.'); ?>

<?php $__env->startSection('content'); ?>
    <section class="marketplace-page">
        <div class="container">
            <div class="marketplace-heading mb-4"><p class="eyebrow mb-2"><i class="bi bi-grid" aria-hidden="true"></i> Explora por categoría</p><h1 class="page-title">Encuentra una categoría para empezar.</h1><p class="section-copy mb-0">Navega por los servicios disponibles en Chambapp.</p></div>
            <div class="row g-3 g-lg-4">
                <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="col-6 col-md-4 col-lg-3"><a class="marketplace-category-card" href="<?php echo e(route('marketplace.category', $category)); ?>"><span class="marketplace-category-card__icon"><i class="bi bi-<?php echo e($category->icon ?: 'grid'); ?>" aria-hidden="true"></i></span><h2><?php echo e($category->name); ?></h2><p><?php echo e($category->description ?: 'Descubre profesionales y servicios de esta categoría.'); ?></p><span class="marketplace-category-card__link">Explorar <i class="bi bi-arrow-right" aria-hidden="true"></i></span></a></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12"><?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'bi-grid','title' => 'No hay categorías activas.','description' => 'Estamos preparando nuevas opciones para explorar.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-grid','title' => 'No hay categorías activas.','description' => 'Estamos preparando nuevas opciones para explorar.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $attributes = $__attributesOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__attributesOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $component = $__componentOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__componentOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\marketplace\categories.blade.php ENDPATH**/ ?>