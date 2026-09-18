<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>

    
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    <?php echo $__env->yieldContent('meta_tags'); ?>

    
    <title>
        <?php echo $__env->yieldContent('title_prefix', config('adminlte.title_prefix', '')); ?>
        <?php echo $__env->yieldContent('title', config('adminlte.title', 'AdminLTE 3')); ?>
        <?php echo $__env->yieldContent('title_postfix', config('adminlte.title_postfix', '')); ?>
    </title>

    
    <?php echo $__env->yieldContent('adminlte_css_pre'); ?>

    
    <?php if(!config('adminlte.enabled_laravel_mix')): ?>
        <link rel="stylesheet" href="<?php echo e(asset('vendor/fontawesome-free/css/all.min.css')); ?>">
        <link rel="stylesheet" href="<?php echo e(asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css')); ?>">
        <link rel="stylesheet" href="<?php echo e(asset('vendor/adminlte/dist/css/adminlte.min.css')); ?>">

        <?php if(config('adminlte.google_fonts.allowed', true)): ?>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        <?php endif; ?>
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo e(mix(config('adminlte.laravel_mix_css_path', 'css/app.css'))); ?>">
    <?php endif; ?>

    
    <?php echo $__env->make('adminlte::plugins', ['type' => 'css'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php if(config('adminlte.livewire')): ?>
        <?php if(intval(app()->version()) >= 7): ?>
            @livewireStyles
        <?php else: ?>
            <livewire:styles />
        <?php endif; ?>
    <?php endif; ?>

    
    <?php echo $__env->yieldContent('adminlte_css'); ?>

    
    <?php
        $faviconUrl = asset('favicon.ico');
    ?>
    <link rel="shortcut icon" href="<?php echo e($faviconUrl); ?>" />
    <link rel="icon" type="image/x-icon" href="<?php echo e($faviconUrl); ?>" />

</head>

<body class="<?php echo $__env->yieldContent('classes_body'); ?>" <?php echo $__env->yieldContent('body_data'); ?>>

    
    <?php echo $__env->yieldContent('body'); ?>

    
    <?php if(!config('adminlte.enabled_laravel_mix')): ?>
        <script src="<?php echo e(asset('vendor/jquery/jquery.min.js')); ?>"></script>
        <script src="<?php echo e(asset('vendor/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
        <script src="<?php echo e(asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js')); ?>"></script>
        <script src="<?php echo e(asset('vendor/adminlte/dist/js/adminlte.min.js')); ?>"></script>
    <?php else: ?>
        <script src="<?php echo e(mix(config('adminlte.laravel_mix_js_path', 'js/app.js'))); ?>"></script>
    <?php endif; ?>

    
    <?php echo $__env->make('adminlte::plugins', ['type' => 'js'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php if(config('adminlte.livewire')): ?>
        <?php if(intval(app()->version()) >= 7): ?>
            @livewireScripts
        <?php else: ?>
            <livewire:scripts />
        <?php endif; ?>
    <?php endif; ?>

    
    <?php echo $__env->yieldContent('adminlte_js'); ?>

</body>

</html>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\master.blade.php ENDPATH**/ ?>