<?php ($setErrorsBag($errors ?? null)); ?>



<?php $__env->startSection('input_group_item'); ?>

    
    <input type="checkbox" id="<?php echo e($id); ?>" name="<?php echo e($name); ?>" value="true"
        <?php echo e($attributes->merge(['class' => $makeItemClass()])); ?>>

<?php $__env->stopSection(true); ?>



<?php $__env->startPush('js'); ?>
<script>

    $(() => {
        $('#<?php echo e($id); ?>').bootstrapSwitch( <?php echo json_encode($config, 15, 512) ?> );

        // Add support to auto select the previous submitted value in case of
        // validation errors.

        <?php if($errors->any() && $enableOldSupport): ?>
            let oldState = <?php echo json_encode((bool)$getOldValue($errorKey), 15, 512) ?>;
            $('#<?php echo e($id); ?>').bootstrapSwitch('state', oldState);
        <?php endif; ?>
    })

</script>
<?php $__env->stopPush(); ?>




<?php if (! $__env->hasRenderedOnce('ba2f4d52-efe5-461d-acfb-2a749f7488b8')): $__env->markAsRenderedOnce('ba2f4d52-efe5-461d-acfb-2a749f7488b8'); ?>
<?php $__env->startPush('css'); ?>
<style type="text/css">

    
    .input-group .bootstrap-switch-handle-on,
    .input-group .bootstrap-switch-handle-off {
        height: 2.25rem !important;
    }

    
    .input-group-lg .bootstrap-switch-handle-on,
    .input-group-lg .bootstrap-switch-handle-off {
        height: 2.875rem !important;
        font-size: 1.25rem !important;
    }

    
    .input-group-sm .bootstrap-switch-handle-on,
    .input-group-sm .bootstrap-switch-handle-off {
        height: 1.8125rem !important;
        font-size: .875rem !important;
    }

    

    .adminlte-invalid-iswgroup > .bootstrap-switch-wrapper,
    .adminlte-invalid-iswgroup > .input-group-prepend > *,
    .adminlte-invalid-iswgroup > .input-group-append > * {
        box-shadow: 0 .25rem 0.5rem rgba(255,0,0,.25);
    }

</style>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php echo $__env->make('adminlte::components.form.input-group-component', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\form\input-switch.blade.php ENDPATH**/ ?>