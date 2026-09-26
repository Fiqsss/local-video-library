<nav class="pagination-custom" role="navigation" aria-label="Pagination">
    <div class="pagination-inner">
        <?php if($paginator->onFirstPage()): ?>
            <span class="page-link disabled"><svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Sebelum</span>
        <?php else: ?>
            <a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>"><svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Sebelum</a>
        <?php endif; ?>

        <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(is_string($element)): ?>
                <span class="page-link disabled"><?php echo e($element); ?></span>
            <?php else: ?>
                <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <span class="page-link active"><?php echo e($page); ?></span>
                    <?php else: ?>
                        <a class="page-link" href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($paginator->hasMorePages()): ?>
            <a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>">Sesudah<svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg></a>
        <?php else: ?>
            <span class="page-link disabled">Sesudah<svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg></span>
        <?php endif; ?>
    </div>
</nav><?php /**PATH D:\xampp\htdocs\videohub\videohub\resources\views/pagination/custom.blade.php ENDPATH**/ ?>