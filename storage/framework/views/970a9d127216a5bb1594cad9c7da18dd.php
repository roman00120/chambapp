<?php if($paginator->hasPages()): ?>
    <nav aria-label="Paginación de servicios">
        <ul class="pagination flex-wrap gap-1 mb-0">
            <li class="page-item <?php echo e($paginator->onFirstPage() ? 'disabled' : ''); ?>">
                <a class="page-link" href="<?php echo e($paginator->previousPageUrl() ?: '#'); ?>" aria-label="Página anterior">&laquo;</a>
            </li>
            <?php $__currentLoopData = $paginator->getUrlRange(1, $paginator->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="page-item <?php echo e($page == $paginator->currentPage() ? 'active' : ''); ?>"><a class="page-link" href="<?php echo e($url); ?>"><?php echo e($page); ?></a></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <li class="page-item <?php echo e($paginator->hasMorePages() ? '' : 'disabled'); ?>">
                <a class="page-link" href="<?php echo e($paginator->nextPageUrl() ?: '#'); ?>" aria-label="Página siguiente">&raquo;</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
<?php /**PATH C:\Users\USER\Desktop\chambapp\resources\views\components\pagination.blade.php ENDPATH**/ ?>