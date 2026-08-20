<?php $__env->startSection('title', ($category ? $category->name : 'Buscar servicios').' | Chambapp'); ?>
<?php $__env->startSection('meta_description', $category ? 'Explora servicios de '.$category->name.' en Chambapp.' : 'Busca servicios y profesionales verificados en Chambapp.'); ?>

<?php $__env->startSection('content'); ?>
    <section class="marketplace-page">
        <div class="container">
            <div class="marketplace-heading">
                <div>
                    <p class="eyebrow mb-2"><i class="bi bi-compass" aria-hidden="true"></i> Marketplace Chambapp</p>
                    <h1 class="page-title"><?php echo e($category ? $category->name : 'Encuentra lo que necesitas.'); ?></h1>
                    <p class="section-copy mb-0">Explora servicios de profesionales verificados y encuentra una opción para tu próximo proyecto.</p>
                </div>
            </div>

            <form class="marketplace-searchbar mb-4" method="GET" action="<?php echo e(route('marketplace.search')); ?>">
                <?php if(!empty($category)): ?><input type="hidden" name="category" value="<?php echo e($category->slug); ?>"><?php endif; ?>
                <i class="bi bi-search" aria-hidden="true"></i>
                <label class="visually-hidden" for="marketplace-query">Buscar servicios</label>
                <input id="marketplace-query" name="q" type="search" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="Busca plomería, pintura, diseño..." maxlength="100">
                <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['type' => 'submit','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','size' => 'sm']); ?>Buscar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
            </form>

            <div class="marketplace-toolbar mb-4">
                <button class="ui-button ui-button--outline ui-button--sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#marketplace-filters" aria-controls="marketplace-filters"><i class="bi bi-sliders" aria-hidden="true"></i> Filtros <?php if(count(array_filter($filters ?? [])) > 1): ?><span class="filter-count"><?php echo e(count(array_filter($filters ?? [])) - (!empty($filters['sort']) ? 1 : 0)); ?></span><?php endif; ?></button>
                <div class="marketplace-toolbar__result"><?php echo e($services->total()); ?> <?php echo e($services->total() === 1 ? 'servicio encontrado' : 'servicios encontrados'); ?></div>
                <form method="GET" action="<?php echo e(route('marketplace.search')); ?>" class="marketplace-sort-form">
                    <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php if($key !== 'sort' && $value !== null && $value !== ''): ?><input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e(is_bool($value) ? (int) $value : $value); ?>"><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <label class="visually-hidden" for="marketplace-sort">Ordenar resultados</label>
                    <select class="form-select form-select-sm" id="marketplace-sort" name="sort" onchange="this.form.submit()">
                        <option value="relevant" <?php if(($filters['sort'] ?? 'relevant') === 'relevant'): echo 'selected'; endif; ?>>Relevancia</option>
                        <option value="rating" <?php if(($filters['sort'] ?? '') === 'rating'): echo 'selected'; endif; ?>>Mejor calificados</option>
                        <option value="price_low" <?php if(($filters['sort'] ?? '') === 'price_low'): echo 'selected'; endif; ?>>Precio menor</option>
                        <option value="price_high" <?php if(($filters['sort'] ?? '') === 'price_high'): echo 'selected'; endif; ?>>Precio mayor</option>
                        <option value="recent" <?php if(($filters['sort'] ?? '') === 'recent'): echo 'selected'; endif; ?>>Más recientes</option>
                    </select>
                </form>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-3">
                    <div class="offcanvas-lg offcanvas-end marketplace-filters" tabindex="-1" id="marketplace-filters" aria-labelledby="marketplace-filters-title">
                        <div class="offcanvas-header px-0 pt-0"><h2 class="offcanvas-title h5" id="marketplace-filters-title">Filtrar resultados</h2><button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" aria-label="Cerrar filtros"></button></div>
                        <div class="offcanvas-body p-0"><?php echo $__env->make('marketplace._filter-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
                    </div>
                </div>
                <div class="col-12 col-lg-9">
                    <?php if($category): ?><div class="marketplace-context mb-3"><i class="bi bi-bookmark" aria-hidden="true"></i> Mostrando servicios de <strong><?php echo e($category->name); ?></strong></div><?php endif; ?>
                    <?php echo $__env->make('marketplace._results', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\marketplace\search.blade.php ENDPATH**/ ?>