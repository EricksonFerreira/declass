<?php $layoutHelper = app('JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper'); ?>
<?php $preloaderHelper = app('JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper'); ?>

<?php
    $empresaBranding = app(\App\Support\EmpresaBranding::class);
    $faviconUrl = $empresaBranding->logoUrl();
    $empresaAtual = $empresaBranding->empresaAtual();
    $appName = $empresaAtual?->nome ?: config('app.name', 'DeCars');
    $manifestVersion = rawurlencode((string) ($empresaAtual?->id ?? '0')).'-'.substr(sha1(($empresaAtual?->nome ?? '').'|'.($empresaAtual?->logo_path ?? '').'|'.($empresaAtual?->cor_primaria ?? '')), 0, 12);
?>

<?php $__env->startSection('meta_tags'); ?>
    <?php echo \Illuminate\View\Factory::parentPlaceholder('meta_tags'); ?>
    <link rel="shortcut icon" href="<?php echo e($faviconUrl); ?>" />
    <link rel="manifest" href="<?php echo e(route('site.manifest', ['v' => $manifestVersion])); ?>">
    <meta name="theme-color" content="<?php echo e($empresaAtual?->cor_primaria ?: '#0a1628'); ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?php echo e($appName); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(route('site.manifest.icon')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('adminlte_css'); ?>
    <?php echo $__env->yieldPushContent('css'); ?>
    <?php echo $__env->yieldContent('css'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('classes_body', $layoutHelper->makeBodyClasses()); ?>

<?php $__env->startSection('body_data', $layoutHelper->makeBodyData()); ?>

<?php $__env->startSection('body'); ?>
    <div class="wrapper">

        
        <?php if($preloaderHelper->isPreloaderEnabled()): ?>
            <?php echo $__env->make('adminlte::partials.common.preloader', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>

        
        <?php if($layoutHelper->isLayoutTopnavEnabled()): ?>
            <?php echo $__env->make('adminlte::partials.navbar.navbar-layout-topnav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('adminlte::partials.navbar.navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>

        
        <?php if(!$layoutHelper->isLayoutTopnavEnabled()): ?>
            <?php echo $__env->make('adminlte::partials.sidebar.left-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>

        
        <?php if(empty($iFrameEnabled)): ?>
            <?php echo $__env->make('adminlte::partials.cwrapper.cwrapper-default', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('adminlte::partials.cwrapper.cwrapper-iframe', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>

        
        <?php if (! empty(trim($__env->yieldContent('footer')))): ?>
            <?php echo $__env->make('adminlte::partials.footer.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>

        
        <?php if(config('adminlte.right_sidebar')): ?>
            <?php echo $__env->make('adminlte::partials.sidebar.right-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('adminlte_js'); ?>
    <?php echo $__env->yieldPushContent('js'); ?>
    <?php echo $__env->yieldContent('js'); ?>

    <script src="<?php echo e(asset('js/sweetAlert.min.js')); ?>"></script>
    <?php if(session('success') || session('error')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const flashSuccess = <?php echo json_encode(session('success'), 15, 512) ?>;
                const flashError = <?php echo json_encode(session('error'), 15, 512) ?>;

                const showFlash = (title, message, icon) => {
                    if (!message || typeof Swal === 'undefined' || typeof Swal.fire !== 'function') return;

                    Swal.fire({
                        icon,
                        // title,
                        text: String(message),
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4500,
                        timerProgressBar: true,
                    });
                };

                if (flashSuccess) {
                    showFlash('Sucesso', flashSuccess, 'success');
                }

                if (flashError) {
                    showFlash('Atenção', flashError, 'error');
                }
            });
        </script>
    <?php endif; ?>

    <script>
        if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('<?php echo e(asset('sw.js')); ?>', { scope: '/' }).catch(function (error) {
                    console.warn('Service worker registration failed:', error);
                });
            });
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/vendor/adminlte/page.blade.php ENDPATH**/ ?>