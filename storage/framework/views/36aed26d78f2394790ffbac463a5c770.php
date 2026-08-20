<?php $__env->startSection('title', 'Recuperar contraseña | Chambapp'); ?>

<?php $__env->startSection('content'); ?>
    <section class="auth-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="auth-card">
                        <p class="eyebrow mb-3">Recupera el acceso</p>
                        <h1 class="auth-title">¿Olvidaste tu contraseña?</h1>
                        <p class="auth-copy">Escribe tu correo y te enviaremos un enlace para crear una contraseña nueva.</p>

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

                        <form method="POST" action="<?php echo e(route('password.email')); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="mb-4">
                                <label class="form-label" for="email">Correo electrónico</label>
                                <input class="form-control form-control-lg" id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" autocomplete="email" required autofocus>
                            </div>
                            <button class="btn btn-primary btn-lg w-100" type="submit">Enviar enlace</button>
                        </form>

                        <p class="auth-footer-copy mb-0"><a class="text-link" href="<?php echo e(route('login')); ?>">Volver a iniciar sesión</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\auth\forgot-password.blade.php ENDPATH**/ ?>