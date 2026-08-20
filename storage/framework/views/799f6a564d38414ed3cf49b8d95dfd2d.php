<?php $__env->startSection('title', 'Verifica tu correo | Chambapp'); ?>

<?php $__env->startSection('content'); ?>
    <section class="auth-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    <div class="auth-card text-center">
                        <span class="auth-icon" aria-hidden="true">✉</span>
                        <p class="eyebrow justify-content-center mb-3">Casi terminamos</p>
                        <h1 class="auth-title">Verifica tu correo electrónico</h1>
                        <p class="auth-copy mx-auto">Enviamos un enlace a <strong><?php echo e(auth()->user()->email); ?></strong>. Confirma tu correo para mantener tu cuenta protegida.</p>

                        <form method="POST" action="<?php echo e(route('verification.send')); ?>">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-primary btn-lg w-100" type="submit">Reenviar correo</button>
                        </form>
                        <a class="btn btn-link mt-2" href="<?php echo e(route(auth()->user()->dashboardRoute())); ?>">Continuar por ahora</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views\auth\verify-email.blade.php ENDPATH**/ ?>