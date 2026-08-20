<?php $__env->startSection('title', $category->name.' | Chambapp'); ?>
<?php $__env->startSection('meta_description', $category->description ?: 'Servicios de '.$category->name.' en Chambapp.'); ?>

<?php $__env->startSection('content'); ?>
    <section class="marketplace-page">
        <div class="container">
            <nav class="breadcrumb marketplace-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo e(route('home')); ?>">Inicio</a><span>/</span><a href="<?php echo e(route('marketplace.categories')); ?>">Categorías</a><span>/</span><strong><?php echo e($category->name); ?></strong></nav>
            <div class="marketplace-heading mb-4"><p class="eyebrow mb-2"><i class="bi bi-bookmark" aria-hidden="true"></i> Categoría</p><h1 class="page-title"><?php echo e($category->name); ?></h1><p class="section-copy mb-0"><?php echo e($category->description ?: 'Explora servicios y profesionales disponibles en esta categoría.'); ?></p></div>
            <div class="marketplace-toolbar mb-4"><div class="marketplace-toolbar__result"><?php echo e($services->total()); ?> servicios disponibles</div><a class="ui-button ui-button--outline ui-button--sm" href="<?php echo e(route('marketplace.search', ['category' => $category->slug])); ?>"><i class="bi bi-sliders" aria-hidden="true"></i> Ajustar filtros</a></div>
            <?php echo $__env->make('marketplace._results', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\marketplace\category.blade.php ENDPATH**/ ?>