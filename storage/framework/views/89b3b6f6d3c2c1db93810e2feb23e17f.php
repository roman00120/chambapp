<?php $__env->startSection('title', $service->title.' | Chambapp'); ?>
<?php $__env->startSection('meta_description', \Illuminate\Support\Str::limit($service->description, 155)); ?>

<?php $__env->startSection('content'); ?>
    <section class="marketplace-page"><div class="container"><nav class="breadcrumb marketplace-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo e(route('home')); ?>">Inicio</a><span>/</span><a href="<?php echo e(route('marketplace.search', ['category' => $service->category->slug])); ?>"><?php echo e($service->category->name); ?></a><span>/</span><strong>Servicio</strong></nav><div class="row g-4 g-lg-5"><div class="col-12 col-lg-7"><?php if($service->images->isNotEmpty()): ?><div id="service-gallery-<?php echo e($service->id); ?>" class="carousel slide service-gallery" data-bs-ride="false"><div class="carousel-inner"><?php $__currentLoopData = $service->images->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="carousel-item <?php echo e($loop->first ? 'active' : ''); ?>"><img src="<?php echo e(\Illuminate\Support\Facades\Storage::disk('public')->url($image->path)); ?>" class="service-gallery__image" alt="<?php echo e($image->alt_text ?: $service->title); ?>" <?php if(!$loop->first): ?> loading="lazy" <?php endif; ?>></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php if($service->images->count() > 1): ?><button class="carousel-control-prev" type="button" data-bs-target="#service-gallery-<?php echo e($service->id); ?>" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Anterior</span></button><button class="carousel-control-next" type="button" data-bs-target="#service-gallery-<?php echo e($service->id); ?>" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Siguiente</span></button><?php endif; ?></div><?php else: ?><div class="service-gallery service-gallery--empty"><i class="bi bi-tools" aria-hidden="true"></i><span>Este servicio aún no tiene imágenes.</span></div><?php endif; ?></div><div class="col-12 col-lg-5"><div class="service-detail__content"><span class="service-card__category"><?php echo e($service->category->name); ?></span><h1 class="page-title"><?php echo e($service->title); ?></h1><div class="service-detail__price mb-4"><?php echo e($service->formattedPrice()); ?></div><div class="service-detail__professional mb-4"><?php if (isset($component)) { $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.avatar','data' => ['user' => $service->professional->user,'src' => $service->professional->profile_photo,'name' => $service->professional->user->name,'size' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($service->professional->user),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($service->professional->profile_photo),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($service->professional->user->name),'size' => 'md']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $attributes = $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $component = $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?><div><span class="small text-muted d-block">Ofrecido por</span><a href="<?php echo e(route('professional.public-profile', $service->professional)); ?>"><?php echo e($service->professional->user->name); ?></a><?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => 'verified','label' => 'Verificado','dot' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'verified','label' => 'Verificado','dot' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?></div></div><div class="service-detail__facts"><span><i class="bi bi-geo-alt" aria-hidden="true"></i> <?php echo e(collect([$service->professional->city, $service->professional->state])->filter()->join(', ') ?: 'Cerca de ti'); ?></span><?php if($service->professional->total_reviews > 0): ?><span><i class="bi bi-star-fill" aria-hidden="true"></i> <?php echo e(number_format((float) $service->professional->average_rating, 1)); ?> (<?php echo e($service->professional->total_reviews); ?>)</span><?php else: ?><span>Sin reseñas todavía</span><?php endif; ?></div><hr><h2 class="h5">Sobre este servicio</h2><p class="service-detail__description"><?php echo e($service->description); ?></p><div class="d-flex flex-column gap-2 mt-4"><a class="ui-button ui-button--primary w-100" href="<?php echo e(route('job-requests.create', $service)); ?>"><i class="bi bi-send" aria-hidden="true"></i> Solicitar servicio</a><?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->isClient()): ?><form method="POST" action="<?php echo e(route('professional.favorite.toggle', $service->professional)); ?>"><?php echo csrf_field(); ?><button class="ui-button ui-button--outline w-100" type="submit"><i class="bi bi-heart<?php echo e($isFavorite ? '-fill' : ''); ?>" aria-hidden="true"></i> <?php echo e($isFavorite ? 'Quitar profesional de favoritos' : 'Guardar profesional en favoritos'); ?></button></form><?php endif; ?> <?php else: ?><a class="ui-button ui-button--outline w-100" href="<?php echo e(route('login')); ?>"><i class="bi bi-heart" aria-hidden="true"></i> Inicia sesión para guardar favoritos</a><?php endif; ?></div></div></div></div></div></section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\marketplace\service-detail.blade.php ENDPATH**/ ?>