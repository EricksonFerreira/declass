<?php
    $empresaBranding = app(\App\Support\EmpresaBranding::class);
    $empresaAtual = $empresaBranding->empresaAtual();
    $logoUrl = $empresaBranding->logoUrl($empresaAtual);
?>

<?php $preloaderHelper = app('JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper'); ?>

<div class="<?php echo e($preloaderHelper->makePreloaderClasses()); ?>" style="<?php echo e($preloaderHelper->makePreloaderStyle()); ?>">

    <?php if (! empty(trim($__env->yieldContent('preloader')))): ?>

        
        <?php echo $__env->yieldContent('preloader'); ?>

    <?php else: ?>

        
        <img src="<?php echo e($logoUrl); ?>"
             class="img-circle <?php echo e(config('adminlte.preloader.img.effect', 'animation__shake')); ?>"
             alt="<?php echo e($empresaAtual?->nome ? 'Logo ' . $empresaAtual->nome : config('adminlte.preloader.img.alt', 'AdminLTE Preloader Image')); ?>"
             width="<?php echo e(config('adminlte.preloader.img.width', 60)); ?>"
             height="<?php echo e(config('adminlte.preloader.img.height', 60)); ?>"
             style="animation-iteration-count:infinite; object-fit: contain;">

    <?php endif; ?>

</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\vendor\adminlte\partials\common\preloader.blade.php ENDPATH**/ ?>