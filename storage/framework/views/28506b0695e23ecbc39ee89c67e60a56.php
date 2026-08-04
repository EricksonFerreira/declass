<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Pre-requisitos pendentes',
    'chips' => [],
    'details' => [],
    'missingItems' => [],
    'actionLinks' => [],
    'showWhenComplete' => true,
    'icon' => 'fas fa-exclamation-triangle',
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
    'title' => 'Pre-requisitos pendentes',
    'chips' => [],
    'details' => [],
    'missingItems' => [],
    'actionLinks' => [],
    'showWhenComplete' => true,
    'icon' => 'fas fa-exclamation-triangle',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $hasMissingItems = !empty($missingItems);
?>

<?php if($showWhenComplete || $hasMissingItems): ?>
    <div class="pre-requisitos-alerta <?php echo e($hasMissingItems ? 'pre-requisitos-alerta--warning' : 'pre-requisitos-alerta--ok'); ?>">
        <?php if(!empty($chips)): ?>
            <div class="pre-requisitos-alerta__chips">
                <?php $__currentLoopData = $chips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $label = is_array($chip) ? (string) ($chip['label'] ?? '') : '';
                        $value = is_array($chip) ? (string) ($chip['value'] ?? '0') : '0';
                    ?>
                    <?php if($label !== ''): ?>
                        <span class="pre-requisitos-alerta__chip"><?php echo e($label); ?>: <?php echo e($value); ?></span>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($details)): ?>
            <div class="pre-requisitos-alerta__details">
                <?php $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $label = is_array($detail) ? (string) ($detail['label'] ?? '') : '';
                        $value = is_array($detail) ? (string) ($detail['value'] ?? '') : '';
                    ?>
                    <?php if($label !== ''): ?>
                        <div class="pre-requisitos-alerta__detail-item">
                            <span class="pre-requisitos-alerta__detail-label"><?php echo e($label); ?></span>
                            <span class="pre-requisitos-alerta__detail-value"><?php echo e($value); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if($hasMissingItems): ?>
            <div class="pre-requisitos-alerta__title">
                <i class="<?php echo e($icon); ?> mr-1"></i>
                <?php echo e($title); ?>

            </div>
            <?php $__currentLoopData = $missingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="pre-requisitos-alerta__item"><?php echo e($item); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(!empty($actionLinks)): ?>
                <div class="pre-requisitos-alerta__links">
                    <?php $__currentLoopData = $actionLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $url = is_array($link) ? (string) ($link['url'] ?? '') : '';
                            $label = is_array($link) ? (string) ($link['label'] ?? '') : '';
                        ?>
                        <?php if($url !== '' && $label !== ''): ?>
                            <a href="<?php echo e($url); ?>" class="pre-requisitos-alerta__link" target="_blank" rel="noopener noreferrer">
                                <?php echo e($label); ?>

                            </a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (! $__env->hasRenderedOnce('35481b6e-f9ae-4044-819c-d20a188d6b35')): $__env->markAsRenderedOnce('35481b6e-f9ae-4044-819c-d20a188d6b35'); ?>
    <style>
        .pre-requisitos-alerta {
            margin-bottom: 12px;
            border-radius: 10px;
            padding: 10px;
            border: 1px solid #dbe6f3;
            background: #f8fbff;
        }

        .pre-requisitos-alerta--warning {
            border: 1px dashed #f3c56a;
            background: #ffffff;
        }

        .pre-requisitos-alerta--ok {
            border-style: solid;
        }

        .pre-requisitos-alerta__chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 6px;
        }

        .pre-requisitos-alerta__chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid #d6e2f1;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            background: #fff;
        }

        .pre-requisitos-alerta__details {
            margin-bottom: 6px;
            border: 1px solid #dbe6f3;
            border-radius: 8px;
            background: #fff;
            max-height: 180px;
            overflow-y: auto;
        }

        .pre-requisitos-alerta__detail-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            padding: 6px 10px;
            border-bottom: 1px solid #eef3f9;
            font-size: 12px;
        }

        .pre-requisitos-alerta__detail-item:last-child {
            border-bottom: 0;
        }

        .pre-requisitos-alerta__detail-label {
            color: #0f172a;
            font-weight: 600;
        }

        .pre-requisitos-alerta__detail-message {
            margin-top: 2px;
            color: #111827;
            font-size: 11px;
            line-height: 1.3;
            flex-basis: 100%;
        }

        .pre-requisitos-alerta__detail-value {
            color: #0f172a;
            font-weight: 700;
            min-width: 20px;
            text-align: right;
        }

        .pre-requisitos-alerta__title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .pre-requisitos-alerta__item {
            font-size: 12px;
            color: #111827;
            margin-bottom: 4px;
        }

        .pre-requisitos-alerta__links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .pre-requisitos-alerta__link {
            display: inline-flex;
            align-items: center;
            border: 1px solid #2563eb;
            color: #2563eb;
            background: #fff;
            border-radius: 999px;
            padding: 5px 11px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none !important;
            transition: all .15s ease;
        }

        .pre-requisitos-alerta__link:hover {
            color: #fff;
            background: #2563eb;
            border-color: #2563eb;
        }
    </style>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/pre-requisitos-alerta.blade.php ENDPATH**/ ?>