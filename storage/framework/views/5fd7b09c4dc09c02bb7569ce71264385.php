<form method="<?php echo e($spoofMethod ? 'POST' : $method); ?>" <?php echo $attributes->merge(['class' => $hasError() ? 'needs-validation' : '']); ?>>
    <style>
        .inline-space> :not(template) {
            margin-right: 1.25rem;
        }
    </style>

    <?php if (! (in_array($method, ['HEAD', 'GET', 'OPTIONS']))): ?>
        <?php echo csrf_field(); ?>
    <?php endif; ?>


    <?php if($spoofMethod): ?>
        <?php echo method_field($method); ?>
    <?php endif; ?>

    <div class="<?php echo e($rowClass ?? ''); ?>">
        <?php echo $slot; ?>

    </div>
</form>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/form.blade.php ENDPATH**/ ?>