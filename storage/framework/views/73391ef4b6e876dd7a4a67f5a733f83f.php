<?php
    $filters = $filters ?? [];
    $priceType = $filters['price_type'] ?? '';
?>

<form method="GET" action="<?php echo e(route('marketplace.search')); ?>" class="marketplace-filter-form">
    <div class="marketplace-filter-form__group">
        <label class="form-label" for="filter-category">Categoría</label>
        <select class="form-select" id="filter-category" name="category">
            <option value="">Todas las categorías</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filterCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($filterCategory->slug); ?>" <?php if(($filters['category'] ?? '') === $filterCategory->slug): echo 'selected'; endif; ?>><?php echo e($filterCategory->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="marketplace-filter-form__group">
        <label class="form-label" for="filter-city">Ciudad</label>
        <select class="form-select" id="filter-city" name="city">
            <option value="">Todas las ciudades</option>
            <?php $__currentLoopData = $cities ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($city); ?>" <?php if(($filters['city'] ?? '') === $city): echo 'selected'; endif; ?>><?php echo e($city); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="marketplace-filter-form__group">
        <label class="form-label" for="filter-price-type">Tipo de precio</label>
        <select class="form-select" id="filter-price-type" name="price_type">
            <option value="">Cualquier tipo</option>
            <option value="fixed" <?php if($priceType === 'fixed'): echo 'selected'; endif; ?>>Precio fijo</option>
            <option value="starting_at" <?php if($priceType === 'starting_at'): echo 'selected'; endif; ?>>Desde</option>
            <option value="quote" <?php if($priceType === 'quote'): echo 'selected'; endif; ?>>Cotización</option>
        </select>
    </div>
    <div class="marketplace-filter-form__group">
        <span class="form-label d-block">Rango de precio</span>
        <div class="row g-2">
            <div class="col-6"><label class="visually-hidden" for="filter-min-price">Precio mínimo</label><input class="form-control" id="filter-min-price" name="min_price" type="number" min="0" placeholder="Mínimo" value="<?php echo e($filters['min_price'] ?? ''); ?>"></div>
            <div class="col-6"><label class="visually-hidden" for="filter-max-price">Precio máximo</label><input class="form-control" id="filter-max-price" name="max_price" type="number" min="0" placeholder="Máximo" value="<?php echo e($filters['max_price'] ?? ''); ?>"></div>
        </div>
    </div>
    <div class="marketplace-filter-form__group">
        <label class="form-label" for="filter-rating">Calificación mínima</label>
        <select class="form-select" id="filter-rating" name="rating">
            <option value="">Cualquier calificación</option>
            <?php $__currentLoopData = [5, 4, 3]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($rating); ?>" <?php if((string) ($filters['rating'] ?? '') === (string) $rating): echo 'selected'; endif; ?>><?php echo e($rating); ?>+ estrellas</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="form-check mb-4">
        <input class="form-check-input" id="filter-verified" name="verified" type="checkbox" value="1" <?php if(filter_var($filters['verified'] ?? false, FILTER_VALIDATE_BOOLEAN)): echo 'checked'; endif; ?>>
        <label class="form-check-label" for="filter-verified">Solo verificados</label>
    </div>
    <div class="d-flex flex-column gap-2">
        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['type' => 'submit','class' => 'w-100']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'w-100']); ?><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
        <a class="ui-button ui-button--link w-100" href="<?php echo e(route('marketplace.search', !empty($category) ? ['category' => $category->slug] : [])); ?>">Limpiar filtros</a>
    </div>
</form>
<?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\marketplace\_filter-form.blade.php ENDPATH**/ ?>