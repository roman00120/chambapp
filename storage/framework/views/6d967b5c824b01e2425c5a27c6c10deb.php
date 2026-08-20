<?php $__env->startSection('title', 'Nueva contraseña | Chambapp'); ?>

<?php $__env->startSection('content'); ?>
    <section class="auth-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="auth-card">
                        <p class="eyebrow mb-3">Un último paso</p>
                        <h1 class="auth-title">Crea una contraseña nueva</h1>
                        <p class="auth-copy">Usa una contraseña segura que no hayas utilizado antes.</p>

                        <?php if (isset($component)) { $__componentOriginal157b4d33905e671df2b01757de5e05e8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal157b4d33905e671df2b01757de5e05e8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.form-errors','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth.form-errors'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal157b4d33905e671df2b01757de5e05e8)): ?>
<?php $attributes = $__attributesOriginal157b4d33905e671df2b01757de5e05e8; ?>
<?php unset($__attributesOriginal157b4d33905e671df2b01757de5e05e8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal157b4d33905e671df2b01757de5e05e8)): ?>
<?php $component = $__componentOriginal157b4d33905e671df2b01757de5e05e8; ?>
<?php unset($__componentOriginal157b4d33905e671df2b01757de5e05e8); ?>
<?php endif; ?>

                        <form method="POST" action="<?php echo e(route('password.update')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="token" value="<?php echo e($token); ?>">
                            <div class="mb-3">
                                <label class="form-label" for="email">Correo electrónico</label>
                                <input class="form-control form-control-lg" id="email" type="email" name="email" value="<?php echo e(old('email', $email)); ?>" autocomplete="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="reset-password">Nueva contraseña</label>
                                <div class="password-field">
                                    <input class="form-control form-control-lg" id="reset-password" type="password" name="password" autocomplete="new-password" required>
                                    <button class="password-toggle" type="button" data-password-toggle data-target="#reset-password" aria-controls="reset-password" aria-pressed="false">Mostrar</button>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="reset-password-confirmation">Confirmar contraseña</label>
                                <input class="form-control form-control-lg" id="reset-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                            </div>
                            <button class="btn btn-primary btn-lg w-100" type="submit">Actualizar contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\auth\reset-password.blade.php ENDPATH**/ ?>