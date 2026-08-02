<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['additionalFilters' => [], 'filters' => [], 'itensFiltered' => [], 'nomeFiltro' => null, 'statusList' => []]));

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

foreach (array_filter((['additionalFilters' => [], 'filters' => [], 'itensFiltered' => [], 'nomeFiltro' => null, 'statusList' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $filtroUrl      = $nomeFiltro ? route($nomeFiltro . '.filtro')         : (url()->current() . '/filtro');
    $removerTudoUrl = $nomeFiltro ? route($nomeFiltro . '.removerFiltro')  : (url()->current() . '/removerfiltro');
    $usesSelect2 = false;
    $filters = is_iterable($filters) ? $filters : [];
    $additionalFilters = is_iterable($additionalFilters) ? $additionalFilters : [];
    $itensFiltered = is_iterable($itensFiltered) ? $itensFiltered : [];
    $statusList = is_iterable($statusList) ? $statusList : [];
    $statusLabels = collect($statusList ?? [])->mapWithKeys(function ($status) {
        return [(string) data_get($status, 'id') => (string) data_get($status, 'nome')];
    })->all();

    $normalizeFilterOptions = function ($options): array {
        if (!is_iterable($options)) {
            return [];
        }

        $normalized = [];

        foreach ($options as $key => $option) {
            if (is_array($option)) {
                $normalized[] = [
                    'value' => $option['value'] ?? $option['id'] ?? $key,
                    'label' => $option['label'] ?? $option['nome'] ?? $option['name'] ?? (string) ($option['value'] ?? $option['id'] ?? $key),
                    'selected' => (bool) ($option['selected'] ?? false),
                ];
                continue;
            }

            $normalized[] = [
                'value' => is_int($key) ? $option : $key,
                'label' => (string) $option,
                'selected' => false,
            ];
        }

        return $normalized;
    };

    foreach (array_merge($filters, $additionalFilters) as $f) {
        if (isset($f['options'])) {
            $usesSelect2 = true;
            break;
        }
    }
?>

<div class="card mb-2">
    <div class="card-body p-2">
        <h6 class="text-secondary text-xs mb-1"><i class="fa fa-filter mr-1"></i> Filtros</h6>
        <hr class="m-0 mb-2">

        <form method="GET" action="<?php echo e($filtroUrl); ?>">
            <div class="row align-items-end">
                <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $hasOptions = isset($filter['options']);
                        $isMultiple = !empty($filter['multiple']);
                        $filterName = $filter['name'] ?? '';
                        $filterLabel = $filter['label'] ?? '';
                        $filterWhere = $filter['typeWhere'] ?? 'where';
                        $filterType = in_array($filter['type'] ?? 'text', ['text', 'date', 'number']) ? ($filter['type'] ?? 'text') : 'text';
                    ?>
                    <div class="col-auto flex-grow-1">
                        <label class="text-xs mb-0" for="<?php echo e($filterName); ?>"><?php echo e($filterLabel); ?></label>
                        <?php if($hasOptions): ?>
                            <select
                                id="<?php echo e($filterName); ?>"
                                class="filter-select2 form-control form-control-sm"
                                <?php if($isMultiple): ?>
                                    data-placeholder="<?php echo e($filter['placeholder'] ?? $filterLabel); ?>"
                                    name="<?php echo e($filterName); ?>_<?php echo e($filterWhere); ?>[]"
                                    multiple
                                <?php else: ?>
                                    name="<?php echo e($filterName); ?>_<?php echo e($filterWhere); ?>"
                                <?php endif; ?>
                            >
                                <?php $__currentLoopData = $normalizeFilterOptions($filter['options'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option['value']); ?>"
                                        <?php echo e($option['selected'] ? 'selected' : ''); ?>>
                                        <?php echo e($option['label']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        <?php else: ?>
                            <input type="<?php echo e($filterType); ?>"
                                class="form-control form-control-sm"
                                id="<?php echo e($filterName); ?>"
                                name="<?php echo e($filterName); ?>_<?php echo e($filterWhere); ?>"
                                placeholder="<?php echo e($filterLabel); ?>"
                                value="<?php echo e($filter['valor'] ?? ''); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div class="col-auto d-flex align-items-end gap-1" style="gap:6px;">
                    <?php if(count($additionalFilters) > 0): ?>
                        <button type="button" id="toggle-filters"
                            class="btn btn-sm btn-outline-secondary"
                            title="Filtros adicionais" data-toggle="tooltip">
                            <i class="fa fa-angle-double-down"></i>
                        </button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa fa-search mr-1"></i> Filtrar
                    </button>
                </div>
            </div>

            <?php if(count($additionalFilters) > 0): ?>
                <div id="additional-filters" class="row mt-2 border rounded p-2" style="display:none;">
                    <p class="col-12 mb-1 text-xs text-secondary text-bold">Filtros adicionais:</p>
                    <?php $__currentLoopData = $additionalFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $hasOptions = isset($filter['options']);
                            $isMultiple = !empty($filter['multiple']);
                            $filterName = $filter['name'] ?? '';
                            $filterLabel = $filter['label'] ?? '';
                            $filterWhere = $filter['typeWhere'] ?? 'where';
                            $filterType = in_array($filter['type'] ?? 'text', ['text', 'date', 'number']) ? ($filter['type'] ?? 'text') : 'text';
                        ?>
                        <div class="col-auto flex-grow-1">
                            <label class="text-xs mb-0" for="<?php echo e($filterName); ?>"><?php echo e($filterLabel); ?></label>
                            <?php if($hasOptions): ?>
                                <select
                                    id="<?php echo e($filterName); ?>"
                                    class="filter-select2 form-control form-control-sm"
                                    <?php if($isMultiple): ?>
                                        data-placeholder="<?php echo e($filter['placeholder'] ?? $filterLabel); ?>"
                                        name="<?php echo e($filterName); ?>_<?php echo e($filterWhere); ?>[]"
                                        multiple
                                    <?php else: ?>
                                        name="<?php echo e($filterName); ?>_<?php echo e($filterWhere); ?>"
                                    <?php endif; ?>
                                >
                                    <?php $__currentLoopData = $normalizeFilterOptions($filter['options'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($option['value']); ?>"
                                            <?php echo e($option['selected'] ? 'selected' : ''); ?>>
                                            <?php echo e($option['label']); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            <?php else: ?>
                                <input type="<?php echo e($filterType); ?>"
                                    class="form-control form-control-sm"
                                    id="<?php echo e($filterName); ?>"
                                    name="<?php echo e($filterName); ?>_<?php echo e($filterWhere); ?>"
                                    placeholder="<?php echo e($filterLabel); ?>"
                                    value="<?php echo e($filter['valor'] ?? ''); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </form>

        
        <?php if(count($itensFiltered) > 0): ?>
            <div class="mt-2 d-flex flex-wrap align-items-center" style="gap:6px;">
                <span class="text-xs text-secondary text-bold">Aplicados:</span>
                <?php $__currentLoopData = $itensFiltered; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($filter['valor']): ?>
                        <?php
                            $valorExibido = $filter['valor'];
                            if (($filter['name'] ?? '') === 'status_movimentacao_id') {
                                $valorExibido = $statusLabels[(string) $filter['valor']] ?? $filter['valor'];
                            }
                        ?>
                        <form action="<?php echo e($removerTudoUrl); ?>" method="GET" class="d-inline">
                            <input type="hidden" name="atributo" value="<?php echo e(isset($filter['name']) ? $filter['name'] : ''); ?>">
                            <button type="submit" class="badge badge-secondary border-0 font-weight-normal"
                                style="cursor:pointer;">
                                <i class="fa fa-times mr-1"></i><?php echo e(isset($filter['nomeExibir']) ? $filter['nomeExibir'] : ''); ?>:
                                <b><?php echo e($valorExibido); ?></b>
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <form action="<?php echo e($removerTudoUrl); ?>" method="GET" class="d-inline">
                    <button type="submit" class="badge badge-danger border-0 font-weight-normal"
                        style="cursor:pointer;">
                        <i class="fa fa-times mr-1"></i> Limpar tudo
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    function initToggleFilters() {
        var btn = document.getElementById('toggle-filters');
        var af  = document.getElementById('additional-filters');
        if (!btn || !af) return;

        // Bootstrap tooltip via jQuery (carregado pelo AdminLTE depois do body)
        function jqReady(fn) {
            if (typeof window.$ !== 'undefined') { fn(); }
            else { setTimeout(function () { jqReady(fn); }, 50); }
        }
        jqReady(function () { $(btn).tooltip(); });

        btn.addEventListener('click', function () {
            var visible = af.style.display !== 'none' && af.style.display !== '';
            if (visible) {
                af.style.display = 'none';
            } else {
                af.style.display = '';
            }
            var nowVisible = af.style.display !== 'none' && af.style.display !== '';
            btn.innerHTML = nowVisible
                ? '<i class="fa fa-angle-double-up"></i>'
                : '<i class="fa fa-angle-double-down"></i>';
            btn.setAttribute('title', nowVisible ? 'Ocultar filtros adicionais' : 'Exibir filtros adicionais');
            jqReady(function () { $(btn).tooltip('dispose').tooltip(); });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initToggleFilters);
    } else {
        initToggleFilters();
    }
})();
</script>

<?php if($usesSelect2): ?>
    <?php $__env->startPush('css'); ?>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.5.2/select2-bootstrap4.min.css" rel="stylesheet" />
        <style>
            .select2-container { min-width: 120px; }
            .select2-container .select2-selection--multiple { min-height: 31px; }
            .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
                font-size: .78rem;
            }
        </style>
    <?php $__env->stopPush(); ?>
    <?php $__env->startPush('js'); ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
        <script>
        (function initFilterSelect2() {
            function applySelect2() {
                document.querySelectorAll('.filter-select2:not(.select2-hidden-accessible)').forEach(function(el) {
                    var $el = $(el);
                    var placeholder = $el.data('placeholder') || '';
                    $el.select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        placeholder: placeholder,
                        allowClear: true,
                        closeOnSelect: !el.multiple
                    });
                    // ao mudar, submete o form automaticamente
                    $el.on('change', function() {
                        $(this).closest('form').submit();
                    });
                });
            }
            $(document).ready(applySelect2);
        })();
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/filter.blade.php ENDPATH**/ ?>