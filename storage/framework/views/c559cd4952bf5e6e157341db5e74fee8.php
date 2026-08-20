<?php $__env->startSection('title', $category->exists ? 'Editar categoría' : 'Nueva categoría'); ?>
<?php $__env->startSection('content'); ?>
<section class="admin-page"><div class="container"><div class="admin-form-shell"><a class="text-link" href="<?php echo e(route('admin.categories.index')); ?>">← Categorías</a><h1 class="page-title h2 mt-3"><?php echo e($category->exists ? 'Editar categoría' : 'Nueva categoría'); ?></h1><?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mt-4','padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4','padding' => 'lg']); ?><form method="POST" action="<?php echo e($category->exists ? route('admin.categories.update', $category) : route('admin.categories.store', $category)); ?>"><?php echo csrf_field(); ?> <?php if($category->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><div class="mb-3"><label class="form-label" for="category-name">Nombre</label><input class="form-control" id="category-name" name="name" value="<?php echo e(old('name', $category->name)); ?>" required maxlength="100"></div><div class="mb-3"><label class="form-label" for="category-description">Descripción</label><textarea class="form-control" id="category-description" name="description" maxlength="1000"><?php echo e(old('description', $category->description)); ?></textarea></div><div class="mb-3"><label class="form-label" for="category-icon">Ícono</label><input class="form-control" id="category-icon" name="icon" value="<?php echo e(old('icon', $category->icon)); ?>" maxlength="80"></div><div class="mb-4"><label class="form-label" for="category-sort">Orden</label><input class="form-control" id="category-sort" name="sort_order" type="number" min="0" value="<?php echo e(old('sort_order', $category->sort_order ?? 0)); ?>" required></div><button class="ui-button ui-button--primary w-100" type="submit">Guardar categoría</button></form> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?></div></div></section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\admin\categories\form.blade.php ENDPATH**/ ?>