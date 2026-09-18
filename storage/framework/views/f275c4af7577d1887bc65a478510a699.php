<!-- resources/views/components/form-checkbox.blade.php -->
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name', 'label', 'checked' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['name', 'label', 'checked' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="form-check">
    <input type="checkbox" class="form-check-input" id="<?php echo e($name); ?>" name="<?php echo e($name); ?>" <?php echo e($checked ? 'checked' : ''); ?>>
    <label class="form-check-label" for="<?php echo e($name); ?>"><?php echo e($label); ?></label>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\form-checkbox.blade.php ENDPATH**/ ?>