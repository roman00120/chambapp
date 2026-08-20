<?php $__env->startSection('title', 'Chambapp | Encuentra al profesional perfecto para tu chamba'); ?>
<?php $__env->startSection('meta_description', 'Conecta con profesionales verificados cerca de ti. Rápido, seguro y confiable.'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $verifiedCount = (int) data_get($homeStats, 'verified_professionals', 0);
        $completedCount = (int) data_get($homeStats, 'completed_jobs', 0);
        $averageRating = data_get($homeStats, 'average_rating');
        $reviewCount = (int) data_get($homeStats, 'total_reviews', 0);
        $serviceCount = (int) data_get($homeStats, 'active_services', 0);
        $availableCount = (int) data_get($homeStats, 'available_professionals', 0);
    ?>
    <section id="inicio" class="hero-landing">
        <div class="hero-landing__wash" aria-hidden="true"></div>
        <div class="container hero-layout">
            <div class="hero-copy-column">
                <span class="hero-landing__kicker"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i> Chambas ahora, <b>soluciones al instante</b></span>
                <h1 class="hero-landing__title"><span class="hero-title-line">Encuentra al</span><span class="hero-title-line">profesional</span><span class="hero-title-line hero-title-line--accent">perfecto</span><span class="hero-title-line">para tu chamba</span></h1>
                <p class="hero-landing__copy">Conecta con profesionales verificados cerca de ti.<br>Rápido, seguro y confiable.</p>
                <div class="hero-landing__actions"><?php if(auth()->guard()->check()): ?> <?php if(auth()->user()->isClient()): ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['href' => ''.e(route('client.ondemand.create')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('client.ondemand.create')).'']); ?>Solicitar chamba ahora <i class="bi bi-arrow-right" aria-hidden="true"></i> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?><?php else: ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['href' => ''.e(route(auth()->user()->dashboardRoute())).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route(auth()->user()->dashboardRoute())).'']); ?>Ir a mi espacio <i class="bi bi-arrow-right" aria-hidden="true"></i> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?><?php endif; ?> <?php else: ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['href' => ''.e(route('register')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('register')).'']); ?>Solicitar chamba ahora <i class="bi bi-arrow-right" aria-hidden="true"></i> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?><?php endif; ?><a class="ui-button ui-button--outline" href="<?php echo e(route('register')); ?>">Soy profesional</a></div>
                <div class="hero-trust-grid" aria-label="Beneficios de Chambapp">
                    <article><span class="hero-trust-grid__icon hero-trust-grid__icon--warm"><i class="bi bi-patch-check" aria-hidden="true"></i></span><div><strong>Profesionales verificados</strong><small>Perfiles verificados y<br>calificaciones reales</small></div></article>
                    <article><span class="hero-trust-grid__icon hero-trust-grid__icon--blue"><i class="bi bi-bag-check" aria-hidden="true"></i></span><div><strong>Pagos seguros</strong><small>Paga de forma segura<br>con Mercado Pago</small></div></article>
                    <article><span class="hero-trust-grid__icon hero-trust-grid__icon--warm"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><strong>Garantía Chambapp</strong><small>Tu satisfacción es nuestra<br>prioridad</small></div></article>
                </div>
            </div>
            <div class="hero-stage">
                <div class="hero-stage__dots" aria-hidden="true"></div>
                <div class="hero-visual"><picture><img src="<?php echo e(asset('images/hero-professional.png')); ?>" width="1024" height="1536" fetchpriority="high" alt="Profesional Chambapp listo para ayudarte"></picture></div>
                <form method="GET" action="<?php echo e(route('marketplace.search')); ?>" class="hero-search-panel" aria-label="Buscar profesionales">
                    <div class="hero-search-panel__heading"><span class="hero-search-panel__mark"><img src="<?php echo e(asset('images/pwa/icon-192.png')); ?>" alt="" aria-hidden="true"></span><strong>¿Qué necesitas?</strong></div>
                    <div class="hero-search-panel__field"><label for="hero-category">Servicio</label><div class="hero-search-panel__control"><i class="bi bi-tools" aria-hidden="true"></i><select id="hero-category" name="category"><option value="">Selecciona una categoría</option><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($category->slug); ?>"><?php echo e($category->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><i class="bi bi-chevron-down" aria-hidden="true"></i></div></div>
                    <div class="hero-search-panel__field"><label for="hero-location">Ubicación</label><div class="hero-search-panel__control"><i class="bi bi-geo-alt" aria-hidden="true"></i><input id="hero-location" name="city" type="search" placeholder="Mi ubicación actual" autocomplete="address-level2"><i class="bi bi-crosshair" aria-hidden="true"></i></div></div>
                    <button class="ui-button ui-button--primary w-100" type="submit">Buscar profesionales</button>
                </form>
                <div class="hero-proof-card">
                    <div class="hero-proof-card__avatars" aria-hidden="true"><?php $__empty_1 = true; $__currentLoopData = $professionals->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $professional): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if (isset($component)) { $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.avatar','data' => ['user' => $professional->user,'src' => $professional->profile_photo,'name' => $professional->user?->name,'size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($professional->user),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($professional->profile_photo),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($professional->user?->name),'size' => 'sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $attributes = $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $component = $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><span><i class="bi bi-person-fill"></i></span><span><i class="bi bi-person-fill"></i></span><span><i class="bi bi-person-fill"></i></span><?php endif; ?></div>
                    <div><strong><?php echo e(number_format($verifiedCount)); ?> profesionales</strong><small>verificados en Chambapp</small></div>
                    <span class="hero-proof-card__divider" aria-hidden="true"></span>
                    <div><strong><i class="bi bi-star-fill" aria-hidden="true"></i> <?php echo e($averageRating !== null ? number_format((float) $averageRating, 1).'/5' : 'Sin reseñas'); ?></strong><small><?php echo e($reviewCount > 0 ? number_format($reviewCount).' calificaciones reales' : 'Calificaciones verificadas'); ?></small></div>
                </div>
                <?php if($availableCount > 0): ?><div class="hero-availability"><span></span><?php echo e(number_format($availableCount)); ?> profesionales disponibles cerca de ti</div><?php endif; ?>
            </div>
        </div>
        <div class="container hero-metrics" aria-label="Estadísticas de Chambapp">
            <article><span class="hero-metric__icon hero-metric__icon--orange"><i class="bi bi-people" aria-hidden="true"></i></span><div><strong><?php echo e(number_format($verifiedCount)); ?></strong><small>Profesionales verificados</small></div></article>
            <article><span class="hero-metric__icon hero-metric__icon--blue"><i class="bi bi-clipboard2-check" aria-hidden="true"></i></span><div><strong><?php echo e(number_format($completedCount)); ?></strong><small>Chambas completadas</small></div></article>
            <article><span class="hero-metric__icon hero-metric__icon--yellow"><i class="bi bi-star" aria-hidden="true"></i></span><div><strong><?php echo e($averageRating !== null ? number_format((float) $averageRating, 1).'/5' : '—/5'); ?></strong><small>Calificación promedio</small></div></article>
            <article><span class="hero-metric__icon hero-metric__icon--green"><i class="bi bi-tools" aria-hidden="true"></i></span><div><strong><?php echo e(number_format($serviceCount)); ?></strong><small>Servicios disponibles</small></div></article>
        </div>
    </section>

    <section id="categorias" class="reference-section"><div class="container"><div class="reference-heading"><div><span class="reference-heading__eyebrow">Explora</span><h2>Categorías populares</h2><p>Encuentra profesionales en las categorías más solicitadas</p></div><a href="<?php echo e(route('marketplace.categories')); ?>">Ver todas <i class="bi bi-arrow-up-right"></i></a></div><div class="row g-3"><?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="col-6 col-md-4 col-lg-2"><a class="reference-category" href="<?php echo e(route('marketplace.category', $category)); ?>"><span><i class="bi bi-<?php echo e($category->icon ?: 'grid'); ?>"></i></span><strong><?php echo e($category->name); ?></strong><small>Profesionales cerca</small></a></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="col-12"><?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'grid','title' => 'Categorías en preparación','description' => 'Pronto podrás explorar servicios por categoría.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'grid','title' => 'Categorías en preparación','description' => 'Pronto podrás explorar servicios por categoría.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $attributes = $__attributesOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__attributesOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $component = $__componentOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__componentOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?></div><?php endif; ?></div></div></section>

    <section id="profesionales" class="reference-section reference-section--muted"><div class="container"><div class="reference-heading"><div><span class="reference-heading__eyebrow">Talento confiable</span><h2>Profesionales destacados</h2><p>Personas verificadas que harán posible tu próxima solución</p></div><a href="<?php echo e(route('marketplace.search')); ?>">Buscar profesionales <i class="bi bi-arrow-right"></i></a></div><div class="row g-3 g-lg-4"><?php $__empty_1 = true; $__currentLoopData = $professionals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $professional): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="col-12 col-md-6 col-lg-4"><?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'reference-professional-card h-100','padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'reference-professional-card h-100','padding' => 'lg']); ?><div class="reference-professional-card__top"><?php if (isset($component)) { $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.avatar','data' => ['user' => $professional->user,'src' => $professional->profile_photo,'name' => $professional->user?->name,'size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($professional->user),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($professional->profile_photo),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($professional->user?->name),'size' => 'lg']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $attributes = $__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__attributesOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2)): ?>
<?php $component = $__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2; ?>
<?php unset($__componentOriginald04dd79f9e235eb8e58dee4526a2f3c2); ?>
<?php endif; ?><?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => 'verified','label' => 'Verificado']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'verified','label' => 'Verificado']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?></div><h3><?php echo e($professional->user?->name ?? 'Profesional Chambapp'); ?></h3><p><?php echo e($professional->bio ?: 'Listo para ayudarte con experiencia y atención cercana.'); ?></p><div class="reference-professional-card__meta"><span><i class="bi bi-star-fill"></i> <?php echo e((int) $professional->total_reviews > 0 ? number_format((float) $professional->average_rating, 1) : 'Nuevo'); ?></span><span><i class="bi bi-geo-alt"></i> <?php echo e($professional->city ?: 'Cerca de ti'); ?></span></div><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'outline','class' => 'w-100 mt-auto','href' => ''.e(route('professional.public-profile', $professional)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','class' => 'w-100 mt-auto','href' => ''.e(route('professional.public-profile', $professional)).'']); ?>Ver perfil <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="col-12"><?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'person-badge','title' => 'Profesionales en preparación','description' => 'Estamos preparando perfiles para que puedas conocerlos.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'person-badge','title' => 'Profesionales en preparación','description' => 'Estamos preparando perfiles para que puedas conocerlos.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $attributes = $__attributesOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__attributesOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $component = $__componentOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__componentOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?></div><?php endif; ?></div></div></section>

    <section id="como-funciona" class="reference-section"><div class="container"><div class="reference-how"><div><span class="reference-heading__eyebrow">Cómo funciona</span><h2>Tu próxima chamba empieza aquí</h2><p>Encuentra, compara y conecta con la persona ideal para resolverlo.</p></div><div class="reference-steps"><div><b>01</b><strong>Cuéntanos qué necesitas</strong><small>Publica tu solicitud en minutos.</small></div><div><b>02</b><strong>Recibe opciones confiables</strong><small>Conoce perfiles y cotizaciones.</small></div><div><b>03</b><strong>Haz que suceda</strong><small>Contrata y da seguimiento.</small></div></div></div></div></section>

    <section class="reference-cta"><div class="container"><div class="reference-cta__inner"><div><span class="reference-heading__eyebrow">Para profesionales</span><h2>¿Tienes una habilidad que compartir?</h2><p>Crea tu perfil, publica tus servicios y encuentra nuevas chambas.</p></div><?php if(auth()->guard()->guest()): ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['href' => ''.e(route('register')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('register')).'']); ?>Crear cuenta profesional <i class="bi bi-arrow-right"></i> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?><?php else: ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['href' => ''.e(route(auth()->user()->dashboardRoute())).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route(auth()->user()->dashboardRoute())).'']); ?>Ir a mi espacio <i class="bi bi-arrow-right"></i> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?><?php endif; ?></div></div></section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\welcome.blade.php ENDPATH**/ ?>