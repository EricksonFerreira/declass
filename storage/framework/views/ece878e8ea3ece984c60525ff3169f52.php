<div class="custom-file">
    
    <input type="file" id="<?php echo e($id); ?>" name="<?php echo e($name); ?>"
        <?php echo e($attributes->merge(['class' => 'custom-file-input'])); ?> <?php if($required): ?> required <?php endif; ?>>

    
    <label class="custom-file-label text-truncate" for="<?php echo e($id); ?>"
        <?php if(isset($legend)): ?> data-browse="<?php echo e($legend); ?>" <?php endif; ?>>
        <?php echo e($placeholder); ?>

    </label>
</div>

<?php if (! $__env->hasRenderedOnce('c3f83ed8-2195-4acf-99fd-a45d6837f22a')): $__env->markAsRenderedOnce('c3f83ed8-2195-4acf-99fd-a45d6837f22a'); ?>
<?php $__env->startPush('js'); ?>
<script>
    $(document).ready(function () {
        bsCustomFileInput.init();
    });
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php if (! $__env->hasRenderedOnce('fca4cd18-b49a-445e-a9f3-5819b3c6fd01')): $__env->markAsRenderedOnce('fca4cd18-b49a-445e-a9f3-5819b3c6fd01'); ?>
<?php $__env->startPush('css'); ?>
<style type="text/css">
    
    .input-group-sm .custom-file-label:after {
        height: 1.8125rem;
        line-height: 1.25;
    }
    .input-group-sm .custom-file-label {
        height: calc(1.8125rem + 2px);
        line-height: 1.25;
    }
    .input-group-sm .custom-file {
        height: calc(1.8125rem + 2px);
        font-size: .875rem;
    }

    
    .input-group-lg .custom-file-label:after {
        height: 2.875rem;
        line-height: 1.6;
    }
    .input-group-lg .custom-file-label {
        height: calc(2.875rem + 2px);
        line-height: 1.6;
    }
    .input-group-lg .custom-file {
        height: calc(2.875rem + 2px);
        font-size: 1.25rem;
    }
</style>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\input-file.blade.php ENDPATH**/ ?>