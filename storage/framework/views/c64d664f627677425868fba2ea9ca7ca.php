<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Campos obrigatórios pendentes',
    'icon' => 'fas fa-exclamation-circle',
    'actionLabel' => 'Ir para o passo correto',
    'alertClass' => '',
    'actionClass' => '',
    'summaryClass' => '',
    'listClass' => '',
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
    'title' => 'Campos obrigatórios pendentes',
    'icon' => 'fas fa-exclamation-circle',
    'actionLabel' => 'Ir para o passo correto',
    'alertClass' => '',
    'actionClass' => '',
    'summaryClass' => '',
    'listClass' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $outerClass = trim(
        'pre-requisitos-alerta pre-requisitos-alerta--warning d-none js-campos-obrigatorios-pendentes ' .
            $alertClass,
    );
?>

<div <?php echo e($attributes->merge(['class' => $outerClass])); ?> role="alert" aria-live="assertive">
    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
        <div class="pre-requisitos-alerta__title mb-0">
            <i class="<?php echo e($icon); ?> mr-1"></i>
            <?php echo e($title); ?>

        </div>
        <button type="button" class="btn btn-sm btn-outline-warning <?php echo e($actionClass); ?>">
            <i class="fas fa-arrow-up mr-1"></i> <?php echo e($actionLabel); ?>

        </button>
    </div>
    <div class="js-campos-obrigatorios-pendentes__resumo <?php echo e($summaryClass); ?> mt-2 small text-muted"></div>
    <div class="pre-requisitos-alerta__details js-campos-obrigatorios-pendentes__lista <?php echo e($listClass); ?> mt-2 mb-0"></div>
</div>

<?php if (! $__env->hasRenderedOnce('29a330d6-5c54-4779-9ff4-d152c28cd2ce')): $__env->markAsRenderedOnce('29a330d6-5c54-4779-9ff4-d152c28cd2ce'); ?>
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
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\campos-obrigatorios-pendentes.blade.php ENDPATH**/ ?>