<?php $__env->startSection('title', 'Iniciar sesión | Chambapp'); ?>

<?php $__env->startSection('content'); ?>
    <section class="auth-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="auth-card">
                        <p class="eyebrow mb-3">Qué bueno verte</p>
                        <h1 class="auth-title">Inicia sesión</h1>
                        <p class="auth-copy">Continúa con tu cuenta de Chambapp.</p>

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

                        <form method="POST" action="<?php echo e(route('login.store')); ?>" novalidate>
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label" for="email">Correo electrónico</label>
                                <input class="form-control form-control-lg <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" autocomplete="email" required autofocus>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0" for="login-password">Contraseña</label>
                                    <a class="small text-link" href="<?php echo e(route('password.request')); ?>">¿La olvidaste?</a>
                                </div>
                                <div class="password-field">
                                    <input class="form-control form-control-lg <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="login-password" type="password" name="password" autocomplete="current-password" required>
                                    <button class="password-toggle" type="button" data-password-toggle data-target="#login-password" aria-controls="login-password" aria-pressed="false">Mostrar</button>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" id="remember" type="checkbox" name="remember" value="1">
                                <label class="form-check-label" for="remember">Recordar sesión</label>
                            </div>

                            <button class="btn btn-primary btn-lg w-100" type="submit">Iniciar sesión</button>
                        </form>

                        <p class="auth-footer-copy mb-0">¿Todavía no tienes cuenta? <a class="text-link" href="<?php echo e(route('register')); ?>">Crear cuenta</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views/auth/login.blade.php ENDPATH**/ ?>