<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'for' => null,
    'required' => false,
    'class' => '',
    'error' => '',
    'hint' => null,
    'icon' => null,
    'iconHint' => null,
    'iconClass' => 'fas fa-info-circle text-muted',
]));

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

foreach (array_filter(([
    'label',
    'for' => null,
    'required' => false,
    'class' => '',
    'error' => '',
    'hint' => null,
    'icon' => null,
    'iconHint' => null,
    'iconClass' => 'fas fa-info-circle text-muted',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php if($label): ?>
    <label
        <?php if($for): ?> for="<?php echo e($for); ?>" <?php endif; ?>
        <?php echo $attributes->merge([
            'class' => trim(($class ?: '') . ' text-xs')
        ]); ?>

        style="display:inline-flex;align-items:center;gap:.25rem;flex-wrap:wrap;"
    >
        <?php echo e($label); ?> 

        <?php if(!empty($icon)): ?>
            <i
                class="<?php echo e($iconClass); ?>"
                title="<?php echo e($iconHint ?? $hint ?? ''); ?>"
                data-toggle="tooltip"
                data-placement="top"
                aria-label="<?php echo e($iconHint ?? $hint ?? ''); ?>"
                style="cursor: help;"
            ></i>
        <?php endif; ?>

        <?php if($required): ?>
            <span class="text-danger">*</span>
        <?php endif; ?>

        <?php if(!empty($hint)): ?>
            <i
                class="fas fa-info-circle ml-1 text-muted"
                title="<?php echo e($hint); ?>"
                data-toggle="tooltip"
                data-placement="top"
                aria-label="<?php echo e($hint); ?>"
                style="cursor: help;"
            ></i>
        <?php endif; ?>

        <?php if($error && $errors->has($error)): ?>
            <?php if (isset($component)) { $__componentOriginal1e042957bf9ec6cdc9f016a470513d9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e042957bf9ec6cdc9f016a470513d9d = $attributes; } ?>
<?php $component = App\View\Components\FormErrors::resolve(['name' => $error] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-errors'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormErrors::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first($error))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e042957bf9ec6cdc9f016a470513d9d)): ?>
<?php $attributes = $__attributesOriginal1e042957bf9ec6cdc9f016a470513d9d; ?>
<?php unset($__attributesOriginal1e042957bf9ec6cdc9f016a470513d9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e042957bf9ec6cdc9f016a470513d9d)): ?>
<?php $component = $__componentOriginal1e042957bf9ec6cdc9f016a470513d9d; ?>
<?php unset($__componentOriginal1e042957bf9ec6cdc9f016a470513d9d); ?>
<?php endif; ?>
        <?php endif; ?>
    </label>
<?php endif; ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/form-label.blade.php ENDPATH**/ ?>