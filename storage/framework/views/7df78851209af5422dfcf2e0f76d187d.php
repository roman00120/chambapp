<!doctype html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?php echo $__env->yieldContent('title', 'Chambapp'); ?></title>
    <style>
        :root { color-scheme: light; font-family: system-ui, sans-serif; background: #fffaf4; color: #342619; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 1.5rem; box-sizing: border-box; }
        main { width: min(100%, 32rem); padding: 2rem; border: 1px solid #eadccd; border-radius: 1rem; background: #fff; text-align: center; box-shadow: 0 1rem 3rem rgb(73 45 20 / 8%); }
        img { width: 5rem; height: 5rem; object-fit: contain; margin-bottom: 1rem; }
        h1 { margin: 0 0 .75rem; font-size: clamp(1.5rem, 6vw, 2.1rem); }
        p { color: #6d5a48; line-height: 1.6; }
        a { display: inline-block; margin-top: .75rem; padding: .75rem 1rem; border-radius: .65rem; background: #f28c28; color: #fff; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
    <main>
        <img src="<?php echo e(asset('images/pwa/icon-192.png')); ?>" alt="Chambapp">
        <?php echo $__env->yieldContent('content'); ?>
        <a href="<?php echo e(url('/')); ?>">Volver a Chambapp</a>
    </main>
</body>
</html>
<?php /**PATH C:\Users\Roman\Desktop\chambapp-master\resources\views/errors/layout.blade.php ENDPATH**/ ?>