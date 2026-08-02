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

<?php if(isset($type) && $type === 'hidden'): ?> <div class="d-none"> <?php endif; ?>
    <?php if(isset($floating) && $floating): ?> <div class="form-floating"> <?php endif; ?>

        <?php
            $resolvedId = $attributes->get('id') ?: ($name ?? null);
        ?>

        <?php if(!isset($floating) || !$floating): ?>
            <?php if (isset($component)) { $__componentOriginal54c611f5808eb808c122f307874dfe3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal54c611f5808eb808c122f307874dfe3a = $attributes; } ?>
<?php $component = App\View\Components\FormLabel::resolve(['label' => $label,'required' => $attributes->has('required')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormLabel::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($resolvedId),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hint),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'iconHint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconHint),'iconClass' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClass)]); ?>
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

        <input
            <?php echo $attributes->merge(['class' => 'form-control' . (isset($type) && $type === 'color' ? ' form-control-color' : '') . (isset($hasError) && $hasError($name) ? ' is-invalid' : '')]); ?>


            type="<?php echo e($type); ?>"

            <?php if(isset($isWired) && $isWired()): ?>
                wire:model<?php echo $wireModifier(); ?>="<?php echo e($name); ?>"
            <?php else: ?>
                value="<?php echo e(old($name, $value ?? (isset($type) && $type === 'color' ? '#000000' : ''))); ?>"
            <?php endif; ?>

            name="<?php echo e($name); ?>"

            <?php if(isset($label) && $label && $resolvedId): ?>
                id="<?php echo e($resolvedId); ?>"
            <?php endif; ?>

            
            <?php if(isset($floating) && $floating && !$attributes->get('placeholder')): ?>
                placeholder="&nbsp;"
            <?php endif; ?>
        />

        <?php if(isset($floating) && $floating): ?>
            <?php if (isset($component)) { $__componentOriginal54c611f5808eb808c122f307874dfe3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal54c611f5808eb808c122f307874dfe3a = $attributes; } ?>
<?php $component = App\View\Components\FormLabel::resolve(['label' => $label,'required' => $attributes->has('required')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormLabel::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($resolvedId),'hint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hint),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'iconHint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconHint),'iconClass' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($iconClass)]); ?>
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

    <?php if(isset($floating) && $floating): ?> </div> <?php endif; ?>

    <?php echo $help ?? null; ?>


    <?php if($errors->has($name)): ?>
    <?php if (isset($component)) { $__componentOriginal1e042957bf9ec6cdc9f016a470513d9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e042957bf9ec6cdc9f016a470513d9d = $attributes; } ?>
<?php $component = App\View\Components\FormErrors::resolve(['name' => $name] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-errors'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormErrors::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['message' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first($name))]); ?>
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

    <?php if(isset($type) && $type === 'hidden'): ?> </div> <?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/form-input.blade.php ENDPATH**/ ?>