<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['hint' => null, 'icon' => null, 'iconHint' => null, 'iconClass' => 'fas fa-info-circle text-muted']));

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

foreach (array_filter((['hint' => null, 'icon' => null, 'iconHint' => null, 'iconClass' => 'fas fa-info-circle text-muted']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php if($floating): ?>
    <div class="form-floating">
<?php endif; ?>

<?php
    $selectId = $attributes->get('id') ?: $id();

    $classes = 'form-control';

    if ($multiple) {
        $classes .= ' select2';
    }

    if (isset($hasError) && $hasError($name)) {
        $classes .= ' is-invalid';
    }

    $fieldName = $multiple && substr($name, -2) !== '[]'
        ? $name . '[]'
        : $name;
?>

<?php if(!$floating): ?>
    <?php if (isset($component)) { $__componentOriginal54c611f5808eb808c122f307874dfe3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal54c611f5808eb808c122f307874dfe3a = $attributes; } ?>
<?php $component = App\View\Components\FormLabel::resolve(['label' => $label,'required' => $attributes->has('required')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormLabel::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectId),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hint),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'iconHint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconHint),'iconClass' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClass)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal54c611f5808eb808c122f307874dfe3a)): ?>
<?php $attributes = $__attributesOriginal54c611f5808eb808c122f307874dfe3a; ?>
<?php unset($__attributesOriginal54c611f5808eb808c122f307874dfe3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal54c611f5808eb808c122f307874dfe3a)): ?>
<?php $component = $__componentOriginal54c611f5808eb808c122f307874dfe3a; ?>
<?php unset($__componentOriginal54c611f5808eb808c122f307874dfe3a); ?>
<?php endif; ?>
<?php endif; ?>

<select
    name="<?php echo e($fieldName); ?>"
    id="<?php echo e($selectId); ?>"
    <?php if($multiple): ?> multiple <?php endif; ?>
    <?php echo $attributes->class([$classes]); ?>

>
    <?php if($placeholder && !$multiple): ?>
        <option value="" disabled <?php if($nothingSelected()): ?> selected="selected" <?php endif; ?>>
            <?php echo e($placeholder); ?>

        </option>
    <?php endif; ?>

    <?php $__empty_1 = true; $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $optionIsRecord = is_array($option) || is_object($option);
            $isOptGroup = $optionIsRecord && is_array($option) && array_key_exists('options', $option);
            if ($isOptGroup) {
                $groupLabel = data_get($option, 'label', data_get($option, 'nome', $key));
            }
            $optionValue = $optionIsRecord
                ? data_get($option, 'id', $key)
                : $key;
            $optionLabel = $optionIsRecord
                ? data_get($option, 'nome', data_get($option, 'name', data_get($option, 'label', $optionValue)))
                : $option;
        ?>
        <?php if($isOptGroup): ?>
            <optgroup label="<?php echo e($groupLabel); ?>">
                <?php $__currentLoopData = data_get($option, 'options', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupKey => $groupOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $groupOptionIsRecord = is_array($groupOption) || is_object($groupOption);
                        $groupOptionValue = $groupOptionIsRecord
                            ? data_get($groupOption, 'id', $groupKey)
                            : $groupKey;
                        $groupOptionLabel = $groupOptionIsRecord
                            ? data_get($groupOption, 'nome', data_get($groupOption, 'name', data_get($groupOption, 'label', $groupOptionValue)))
                            : $groupOption;
                    ?>
                    <option value="<?php echo e($groupOptionValue); ?>" <?php if($isSelected($groupOptionValue)): ?> selected="selected" <?php endif; ?>>
                        <?php echo e($groupOptionLabel); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </optgroup>
        <?php else: ?>
            <option value="<?php echo e($optionValue); ?>" <?php if($isSelected($optionValue)): ?> selected="selected" <?php endif; ?>>
                <?php echo e($optionLabel); ?>

            </option>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <?php echo $slot; ?>

    <?php endif; ?>
</select>

<?php if($floating): ?>
    <?php if (isset($component)) { $__componentOriginal54c611f5808eb808c122f307874dfe3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal54c611f5808eb808c122f307874dfe3a = $attributes; } ?>
<?php $component = App\View\Components\FormLabel::resolve(['label' => $label] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormLabel::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectId),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hint),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'iconHint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconHint),'iconClass' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClass)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal54c611f5808eb808c122f307874dfe3a)): ?>
<?php $attributes = $__attributesOriginal54c611f5808eb808c122f307874dfe3a; ?>
<?php unset($__attributesOriginal54c611f5808eb808c122f307874dfe3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal54c611f5808eb808c122f307874dfe3a)): ?>
<?php $component = $__componentOriginal54c611f5808eb808c122f307874dfe3a; ?>
<?php unset($__componentOriginal54c611f5808eb808c122f307874dfe3a); ?>
<?php endif; ?>
<?php endif; ?>

<?php if($floating): ?>
    </div>
<?php endif; ?>

<?php echo $help ?? null; ?>


<?php
    $hasErrorAndShow = function($name) {
        return session()->has('errors') && session('errors')->has($name);
    };
?>

<?php if($hasErrorAndShow($name)): ?>
    <?php if (isset($component)) { $__componentOriginal1e042957bf9ec6cdc9f016a470513d9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e042957bf9ec6cdc9f016a470513d9d = $attributes; } ?>
<?php $component = App\View\Components\FormErrors::resolve(['name' => $name] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-errors'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormErrors::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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

<?php if($multiple): ?>
    <?php $__env->startPush('js'); ?>
        <script>
            $(document).ready(function () {
                $('#<?php echo e($selectId); ?>').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: <?php echo json_encode($placeholder ?? 'Selecione...', 15, 512) ?>,
                    allowClear: true,
                    closeOnSelect: false
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/form-select.blade.php ENDPATH**/ ?>