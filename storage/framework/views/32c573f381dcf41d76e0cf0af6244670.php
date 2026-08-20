<?php $__env->startSection('title', $jobRequest->title.' | Chambapp'); ?>

<?php
    $quoteLabels = [
        'pending' => ['Pendiente', 'warning'],
        'accepted' => ['Aceptada', 'success'],
        'rejected' => ['Rechazada', 'danger'],
        'expired' => ['Expirada', 'neutral'],
        'superseded' => ['Reemplazada', 'neutral'],
    ];
    $canCancel = in_array($jobRequest->status, [\App\Enums\JobStatus::PENDING, \App\Enums\JobStatus::ACCEPTED], true);
?>

<?php $__env->startSection('content'); ?>
    <section class="job-page">
        <div class="container">
            <div class="job-detail-heading">
                <div>
                    <a class="text-link justify-content-start mb-3" href="<?php echo e($isClient ? route('client.jobs.index') : route('professional.jobs.index')); ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Volver</a>
                    <p class="eyebrow mb-2">Detalle del trabajo</p>
                    <h1 class="page-title"><?php echo e($jobRequest->title); ?></h1>
                </div>
                <?php if (isset($component)) { $__componentOriginal4cad57415998541528befff6353295c4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4cad57415998541528befff6353295c4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.job-status-badge','data' => ['status' => $jobRequest->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('job-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($jobRequest->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4cad57415998541528befff6353295c4)): ?>
<?php $attributes = $__attributesOriginal4cad57415998541528befff6353295c4; ?>
<?php unset($__attributesOriginal4cad57415998541528befff6353295c4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4cad57415998541528befff6353295c4)): ?>
<?php $component = $__componentOriginal4cad57415998541528befff6353295c4; ?>
<?php unset($__componentOriginal4cad57415998541528befff6353295c4); ?>
<?php endif; ?>
            </div>

            <div class="row g-4 g-lg-5">
                <div class="col-12 col-lg-7">
                    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'lg']); ?>
                        <div class="job-detail__service">
                            <span class="service-card__category"><?php echo e($jobRequest->service?->category?->name ?? 'Servicio'); ?></span>
                            <h2 class="h4 mt-2 mb-1"><?php echo e($jobRequest->service?->title ?? 'Solicitud personalizada'); ?></h2>
                            <p class="text-muted mb-0"><?php echo e($isClient ? 'Profesional: ' : 'Cliente: '); ?><strong><?php echo e($isClient ? $jobRequest->professional?->user?->name : $jobRequest->client?->name); ?></strong></p>
                        </div>
                        <hr>
                        <h2 class="job-section-title">Descripción</h2>
                        <p class="job-description"><?php echo e($jobRequest->description); ?></p>
                        <h2 class="job-section-title">Fecha deseada</h2>
                        <p class="job-fact"><i class="bi bi-calendar3" aria-hidden="true"></i> <?php echo e($jobRequest->formattedRequestedDate()); ?></p>
                        <h2 class="job-section-title">Ubicación del trabajo</h2>
                        <?php if($isClient || $hasApprovedPayment): ?>
                            <div class="job-location"><strong><?php echo e($jobRequest->address); ?></strong><span><?php echo e($jobRequest->city); ?>, <?php echo e($jobRequest->state); ?> · C.P. <?php echo e($jobRequest->postal_code); ?></span></div>
                        <?php else: ?>
                            <?php if(in_array($jobRequest->status, [\App\Enums\JobStatus::MATCHED, \App\Enums\JobStatus::AWAITING_QUOTE], true)): ?><form method="POST" action="<?php echo e(route('job-quotes.store', $jobRequest)); ?>"><?php echo csrf_field(); ?><div class="mb-3"><label class="form-label" for="ondemand-quote-amount">Precio propuesto (MXN)</label><input class="form-control" id="ondemand-quote-amount" name="amount" type="number" min="0.01" max="99999999.99" step="0.01" required></div><div class="mb-3"><label class="form-label" for="ondemand-quote-description">Qué incluye</label><textarea class="form-control" id="ondemand-quote-description" name="description" rows="3" maxlength="300" required></textarea></div><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['class' => 'w-100','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-100','type' => 'submit']); ?>Enviar cotización <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></form><?php endif; ?>
                            <div class="job-location"><strong>Ubicación aproximada</strong><span><?php echo e($jobRequest->city); ?>, <?php echo e($jobRequest->state); ?> · C.P. <?php echo e($jobRequest->postal_code); ?></span><small class="text-muted">La dirección completa se habilitará después del pago.</small></div>
                        <?php endif; ?>
                        <div class="job-price-box mt-4"><span>Precio acordado</span><strong><?php echo e($jobRequest->formattedAgreedPrice()); ?></strong></div>
                        <?php if($hasApprovedPayment): ?>
                            <div class="contact-unlocked mt-4"><p class="eyebrow mb-2"><i class="bi bi-unlock" aria-hidden="true"></i> Contratación confirmada</p><h2 class="job-section-title mb-2">Datos para coordinar el servicio</h2><div class="contact-unlocked__grid"><span><small>Teléfono</small><strong><?php echo e($isClient ? $jobRequest->professional?->user?->phone : $jobRequest->client?->phone); ?></strong></span><span><small>Dirección</small><strong><?php echo e($jobRequest->address); ?>, <?php echo e($jobRequest->city); ?></strong></span></div></div>
                        <?php else: ?>
                            <div class="privacy-note mt-4"><i class="bi bi-shield-lock" aria-hidden="true"></i> Los datos de contacto se habilitan después de la confirmación real del pago.</div>
                        <?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'mt-4','padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4','padding' => 'lg']); ?>
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3"><div><p class="eyebrow mb-2">Historial</p><h2 class="section-title mb-0">Cotizaciones</h2></div><span class="text-muted small"><?php echo e($jobRequest->quotes->count()); ?> propuestas</span></div>
                        <?php if($jobRequest->quotes->isEmpty()): ?>
                            <?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'bi-cash-coin','title' => ''.e($isClient ? 'El profesional aún no ha enviado una cotización.' : 'Envía una cotización para que el cliente pueda contratar el trabajo.').'','description' => 'Las propuestas se mantienen dentro de Chambapp y no incluyen datos de contacto.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'bi-cash-coin','title' => ''.e($isClient ? 'El profesional aún no ha enviado una cotización.' : 'Envía una cotización para que el cliente pueda contratar el trabajo.').'','description' => 'Las propuestas se mantienen dentro de Chambapp y no incluyen datos de contacto.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $attributes = $__attributesOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__attributesOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $component = $__componentOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__componentOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
                        <?php else: ?>
                            <div class="quote-list">
                                <?php $__currentLoopData = $jobRequest->quotes->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php [$quoteLabel, $quoteVariant] = $quoteLabels[$quote->status->value] ?? ['En revisión', 'neutral']; ?>
                                    <div class="quote-card <?php echo e($quote->status === \App\Enums\QuoteStatus::ACCEPTED ? 'quote-card--accepted' : ''); ?>">
                                        <div class="quote-card__header"><div><span class="quote-card__eyebrow"><?php echo e($quote->created_at->locale('es')->translatedFormat('d M Y, g:i a')); ?></span><h3 class="quote-card__amount"><?php echo e($quote->formattedAmount()); ?></h3></div><?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => $quoteVariant,'label' => $quoteLabel]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quoteVariant),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($quoteLabel)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?></div>
                                        <p class="quote-card__description"><?php echo e($quote->description); ?></p>
                                        <?php if($quote->expires_at && $quote->status === \App\Enums\QuoteStatus::PENDING): ?><p class="quote-card__expiry"><i class="bi bi-clock" aria-hidden="true"></i> Válida hasta <?php echo e($quote->expires_at->locale('es')->translatedFormat('d M Y, g:i a')); ?></p><?php endif; ?>
                                        <?php if(! $isClient): ?>
                                            <?php $quoteMoney = app(\App\Services\PaymentCalculationService::class)->calculate((string) $quote->amount); ?>
                                            <div class="quote-earnings"><span>Precio <strong>$<?php echo e($quoteMoney->grossAmount); ?> MXN</strong></span><span>Comisión Chambapp (<?php echo e($quoteMoney->platformFeePercent); ?>%) <strong>-$<?php echo e($quoteMoney->platformFee); ?> MXN</strong></span><span>Monto profesional <strong>$<?php echo e($quoteMoney->professionalAmount); ?> MXN</strong></span></div>
                                            <p class="small text-muted mt-2 mb-0">El procesador de pagos puede aplicar cargos adicionales según las condiciones de tu cuenta.</p>
                                        <?php endif; ?>
                                        <?php if($quote->rejection_reason): ?><p class="quote-card__reason">Motivo: <?php echo e($quote->rejection_reason); ?></p><?php endif; ?>
                                        <?php if($isClient && $quote->status === \App\Enums\QuoteStatus::PENDING && ! $quote->isExpired()): ?>
                                            <div class="quote-card__actions"><form method="POST" action="<?php echo e(route('job-quotes.accept', $quote)); ?>" data-confirm-form data-confirm-message="Al aceptar, el trabajo quedará pendiente de pago dentro de Chambapp." data-confirm-submit="Aceptar cotización" data-disable-on-submit><?php echo csrf_field(); ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><i class="bi bi-check-lg" aria-hidden="true"></i> Aceptar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></form><form method="POST" action="<?php echo e(route('job-quotes.reject', $quote)); ?>" data-confirm-form data-confirm-message="La cotización será rechazada. El profesional podrá enviar otra propuesta." data-confirm-submit="Rechazar cotización"><?php echo csrf_field(); ?><div class="quote-reject-fields"><label class="visually-hidden" for="reject-reason-<?php echo e($quote->id); ?>">Motivo del rechazo</label><select class="form-select form-select-sm" id="reject-reason-<?php echo e($quote->id); ?>" name="reason" required><option value="">Motivo</option><option value="price_high">Precio alto</option><option value="changed_need">Cambió mi necesidad</option><option value="no_longer_needed">Ya no necesito el servicio</option><option value="other">Otro</option></select><label class="visually-hidden" for="reject-detail-<?php echo e($quote->id); ?>">Detalle opcional</label><input class="form-control form-control-sm" id="reject-detail-<?php echo e($quote->id); ?>" name="reason_detail" maxlength="140" placeholder="Detalle breve (opcional)"></div><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'danger','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','type' => 'submit']); ?>Rechazar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></form></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
                </div>

                <div class="col-12 col-lg-5">
                    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'job-sidebar-card mb-4','padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'job-sidebar-card mb-4','padding' => 'lg']); ?>
                        <h2 class="job-section-title mt-0">Acciones</h2>
                        <?php if($errors->any()): ?><?php if (isset($component)) { $__componentOriginal746de018ded8594083eb43be3f1332e1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal746de018ded8594083eb43be3f1332e1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.alert','data' => ['variant' => 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger']); ?><?php echo e($errors->first()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $attributes = $__attributesOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__attributesOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $component = $__componentOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__componentOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?><?php endif; ?>
                        <?php if($isClient): ?>
                            <?php if($jobRequest->status === \App\Enums\JobStatus::AWAITING_PAYMENT): ?>
                                <div class="payment-placeholder"><span class="payment-placeholder__icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></span><h3>Cotización aceptada</h3><p>Total: <strong><?php echo e($jobRequest->formattedAgreedPrice()); ?></strong></p><?php if($canPay): ?><a class="ui-button ui-button--primary w-100" href="<?php echo e(route('client.payments.summary', $jobRequest)); ?>">Pagar en Chambapp</a><?php else: ?><small>El profesional debe conectar Mercado Pago para habilitar el pago.</small><?php endif; ?></div>
                            <?php endif; ?>
                            <?php if($jobRequest->status === \App\Enums\JobStatus::AWAITING_CONFIRMATION): ?><form method="POST" action="<?php echo e(route('job-requests.complete', $jobRequest)); ?>" data-confirm-form data-confirm-message="Confirma que el trabajo terminó correctamente." data-confirm-submit="Confirmar finalización"><?php echo csrf_field(); ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['class' => 'w-100','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-100','type' => 'submit']); ?><i class="bi bi-check2-circle" aria-hidden="true"></i> Confirmar finalización <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></form><?php endif; ?>
                            <?php if($canCancel): ?><form class="mt-2" method="POST" action="<?php echo e(route('job-requests.cancel', $jobRequest)); ?>" data-confirm-form data-confirm-message="El trabajo se cancelará y no podrá continuar con este flujo." data-confirm-submit="Cancelar trabajo"><?php echo csrf_field(); ?><div class="mb-2"><label class="visually-hidden" for="cancel-reason-client">Motivo opcional</label><input class="form-control form-control-sm" id="cancel-reason-client" name="cancellation_reason" maxlength="255" placeholder="Motivo opcional"></div><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['class' => 'w-100','variant' => 'danger','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-100','variant' => 'danger','type' => 'submit']); ?>Cancelar trabajo <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></form><?php endif; ?>
                        <?php else: ?>
                            <?php if(in_array($jobRequest->status, [\App\Enums\JobStatus::PENDING, \App\Enums\JobStatus::ACCEPTED], true)): ?><form method="POST" action="<?php echo e(route('job-quotes.store', $jobRequest)); ?>"><?php echo csrf_field(); ?><div class="mb-3"><label class="form-label" for="quote-amount">Precio propuesto (MXN)</label><input class="form-control" id="quote-amount" name="amount" type="number" min="0.01" max="99999999.99" step="0.01" value="<?php echo e(old('amount')); ?>" required></div><div class="mb-3"><label class="form-label" for="quote-description">Qué incluye</label><textarea class="form-control" id="quote-description" name="description" rows="3" maxlength="300" required placeholder="Ej. Incluye instalación y materiales básicos."><?php echo e(old('description')); ?></textarea><div class="form-text">Máximo 300 caracteres. No compartas datos de contacto.</div></div><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['class' => 'w-100','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-100','type' => 'submit']); ?><i class="bi bi-send" aria-hidden="true"></i> Enviar cotización <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></form><?php endif; ?>
                            <?php if($jobRequest->status === \App\Enums\JobStatus::PAID): ?><form method="POST" action="<?php echo e(route('job-requests.on-the-way', $jobRequest)); ?>"><?php echo csrf_field(); ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['class' => 'w-100','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-100','type' => 'submit']); ?><i class="bi bi-truck"></i> Avisar que voy en camino <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></form><?php endif; ?>
                            <?php if($jobRequest->status === \App\Enums\JobStatus::ON_THE_WAY): ?><form method="POST" action="<?php echo e(route('job-requests.arrive', $jobRequest)); ?>"><?php echo csrf_field(); ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['class' => 'w-100','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-100','type' => 'submit']); ?><i class="bi bi-geo-alt"></i> Marcar que llegué <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></form><?php endif; ?>
                            <?php if($jobRequest->status === \App\Enums\JobStatus::ARRIVED): ?><form method="POST" action="<?php echo e(route('job-requests.start', $jobRequest)); ?>"><?php echo csrf_field(); ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['class' => 'w-100','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-100','type' => 'submit']); ?><i class="bi bi-play-circle" aria-hidden="true"></i> Iniciar trabajo <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></form><?php endif; ?>
                            <?php if($jobRequest->status === \App\Enums\JobStatus::IN_PROGRESS): ?><form method="POST" action="<?php echo e(route('job-requests.finish', $jobRequest)); ?>" data-confirm-form data-confirm-message="El trabajo quedará pendiente de confirmación del cliente." data-confirm-submit="Marcar como terminado"><?php echo csrf_field(); ?><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['class' => 'w-100','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-100','type' => 'submit']); ?><i class="bi bi-check2-circle" aria-hidden="true"></i> Marcar como terminado <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></form><?php endif; ?>
                            <?php if($canCancel): ?><form class="mt-2" method="POST" action="<?php echo e(route('job-requests.cancel', $jobRequest)); ?>" data-confirm-form data-confirm-message="El trabajo se cancelará y no podrá continuar con este flujo." data-confirm-submit="Cancelar trabajo"><?php echo csrf_field(); ?><div class="mb-2"><label class="visually-hidden" for="cancel-reason-pro">Motivo opcional</label><input class="form-control form-control-sm" id="cancel-reason-pro" name="cancellation_reason" maxlength="255" placeholder="Motivo opcional"></div><?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['class' => 'w-100','variant' => 'danger','type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-100','variant' => 'danger','type' => 'submit']); ?>Cancelar trabajo <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?></form><?php endif; ?>
                        <?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['padding' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => 'lg']); ?><h2 class="job-section-title mt-0">Historial del trabajo</h2><ol class="job-timeline"><li class="is-done"><span class="job-timeline__dot"></span><div><strong>Solicitud enviada</strong><small><?php echo e($jobRequest->created_at->locale('es')->translatedFormat('d M Y, g:i a')); ?></small></div></li><?php if($jobRequest->quotes->isNotEmpty()): ?><li class="is-done"><span class="job-timeline__dot"></span><div><strong>Cotización recibida</strong></div></li><?php endif; ?> <?php if($jobRequest->quotes->contains('status', \App\Enums\QuoteStatus::ACCEPTED)): ?><li class="is-done"><span class="job-timeline__dot"></span><div><strong>Cotización aceptada</strong></div></li><li class="is-done"><span class="job-timeline__dot"></span><div><strong><?php echo e($hasApprovedPayment ? 'Pago aprobado' : 'Pendiente de pago'); ?></strong></div></li><?php endif; ?> <?php if($jobRequest->started_at): ?><li class="is-done"><span class="job-timeline__dot"></span><div><strong>Trabajo iniciado</strong></div></li><?php endif; ?> <?php if($jobRequest->finished_at): ?><li class="is-done"><span class="job-timeline__dot"></span><div><strong>Trabajo terminado</strong></div></li><?php endif; ?> <?php if($jobRequest->completed_at): ?><li class="is-done"><span class="job-timeline__dot"></span><div><strong>Trabajo completado</strong></div></li><?php endif; ?></ol> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php echo $__env->make('jobs._completion-actions', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\jobs\show.blade.php ENDPATH**/ ?>