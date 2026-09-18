<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'subtitle' => null,
    'showHeader' => true,
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
    'title' => null,
    'subtitle' => null,
    'showHeader' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php if (! $__env->hasRenderedOnce('0e8f9004-bafc-45d2-ac81-9d008e043215')): $__env->markAsRenderedOnce('0e8f9004-bafc-45d2-ac81-9d008e043215'); ?>
    <?php $__env->startPush('css'); ?>
        <style>
            .card-estacao { border-radius: 14px; overflow: hidden; }
            .card-header-estacao {
                background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
                padding: 1.1rem 1.5rem;
            }
            .card-header-estacao h4 { color: #0f2740; font-weight: 700; }
        </style>
    <?php $__env->stopPush(); ?>
<?php endif; ?>

<div class="card card-estacao shadow-sm border-0">
    <?php if($showHeader && (!empty($title) || !empty($subtitle) || isset($actions))): ?>
        <div class="card-header card-header-estacao border-0">
            <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.75rem;">
                <div>
                    <?php if(!empty($title)): ?>
                        <h4 class="mb-1"><?php echo e($title); ?></h4>
                    <?php endif; ?>
                    <?php if(!empty($subtitle)): ?>
                        <p class="mb-0 text-muted"><?php echo e($subtitle); ?></p>
                    <?php endif; ?>
                </div>
                <?php if(isset($actions)): ?>
                    <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                        <?php echo e($actions); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="card-body p-3">
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\page-header-card.blade.php ENDPATH**/ ?>