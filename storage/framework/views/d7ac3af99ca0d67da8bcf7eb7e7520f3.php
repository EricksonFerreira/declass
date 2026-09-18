<?php $__env->startSection('content_top_nav_left'); ?>
    <li class="nav-item navbar-breadcrumb d-none d-sm-flex align-items-center">
        <ol class="breadcrumb bg-transparent mb-0 p-0 flex-nowrap">
            <?php $__currentLoopData = $breadcrumbs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $breadcrumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $name = is_array($breadcrumb)
                        ? ($breadcrumb['name'] ?? '')
                        : (method_exists($breadcrumb, 'getName') ? $breadcrumb->getName() : ($breadcrumb->name ?? ''));
                    $url = is_array($breadcrumb)
                        ? ($breadcrumb['url'] ?? null)
                        : (method_exists($breadcrumb, 'getUrl') ? $breadcrumb->getUrl() : ($breadcrumb->url ?? null));
                ?>
                <li class="breadcrumb-item <?php echo e($loop->last ? 'active' : ''); ?>">
                    <?php if(!$loop->last && $url): ?>
                        <a href="<?php echo e($url); ?>"><?php echo e($name); ?></a>
                    <?php else: ?>
                        <?php echo e($name); ?>

                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
    </li>
<?php $__env->stopSection(); ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/components/breadcrumb.blade.php ENDPATH**/ ?>