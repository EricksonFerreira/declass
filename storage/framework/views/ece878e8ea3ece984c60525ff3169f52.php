<div class="custom-file">
    
    <input type="file" id="<?php echo e($id); ?>" name="<?php echo e($name); ?>"
        <?php echo e($attributes->merge(['class' => 'custom-file-input'])); ?> <?php if($required): ?> required <?php endif; ?>>

    
    <label class="custom-file-label text-truncate" for="<?php echo e($id); ?>"
        <?php if(isset($legend)): ?> data-browse="<?php echo e($legend); ?>" <?php endif; ?>>
        <?php echo e($placeholder); ?>

    </label>
</div>

<?php if (! $__env->hasRenderedOnce('92c9fe16-58e7-4641-a679-fbd9b29fd7ac')): $__env->markAsRenderedOnce('92c9fe16-58e7-4641-a679-fbd9b29fd7ac'); ?>
<?php $__env->startPush('js'); ?>
<script>
    $(document).ready(function () {
        bsCustomFileInput.init();
    });
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php if (! $__env->hasRenderedOnce('355f7fb4-e916-43c4-87b4-f1272602671f')): $__env->markAsRenderedOnce('355f7fb4-e916-43c4-87b4-f1272602671f'); ?>
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