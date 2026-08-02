<?php $__env->startSection('title', $titulo); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => $breadcrumb] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Breadcrumb::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $attributes = $__attributesOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__attributesOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $component = $__componentOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__componentOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal2ff7b3043c692896c2a650ccae21c5e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4 = $attributes; } ?>
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PageTitle::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4)): ?>
<?php $attributes = $__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4; ?>
<?php unset($__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ff7b3043c692896c2a650ccae21c5e4)): ?>
<?php $component = $__componentOriginal2ff7b3043c692896c2a650ccae21c5e4; ?>
<?php unset($__componentOriginal2ff7b3043c692896c2a650ccae21c5e4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('financeiro.fluxo_caixa._styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php
        $prefixoMoeda = $moeda === 'BRL' ? 'R$' : $moeda;
        $receitasPendentes = (float) ($pendentes->receitas ?? 0);
        $despesasPendentes = (float) ($pendentes->despesas ?? 0);
    ?>

    <div class="fc-page">
        <div class="card fc-card fc-filter mb-4">
            <div class="card-body py-3">
                <form method="GET" action="<?php echo e(route('financeiro.analise-veiculos')); ?>" class="row align-items-end">
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="data_inicio" class="fc-label">Data inicio</label>
                        <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?php echo e($dataInicio); ?>">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="data_fim" class="fc-label">Data fim</label>
                        <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?php echo e($dataFim); ?>">
                    </div>
                    <div class="col-lg-3 col-md-4 mb-2">
                        <label for="veiculo_id" class="fc-label">Veiculo</label>
                        <select name="veiculo_id" id="veiculo_id" class="form-control">
                            <option value="">Todos os veiculos</option>
                            <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($veiculo->id); ?>" <?php echo e((int) $veiculoId === (int) $veiculo->id ? 'selected' : ''); ?>>
                                    <?php echo e($veiculo->nome); ?> <?php if($veiculo->placa_licenca): ?> - <?php echo e($veiculo->placa_licenca); ?> <?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-8 mb-2">
                        <label for="busca" class="fc-label">Busca</label>
                        <input type="text" name="busca" id="busca" class="form-control" value="<?php echo e($busca); ?>" placeholder="Placa, veiculo ou descricao">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2 fc-actions">
                        <button type="submit" class="btn btn-primary fc-btn flex-grow-1">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="<?php echo e(route('financeiro.analise-veiculos')); ?>" class="btn btn-outline-secondary fc-btn">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--receita h-100">
                    <div>
                        <span>Receitas por frota</span>
                        <strong class="text-success"><?php echo e($prefixoMoeda); ?> <?php echo e(number_format($receitas, 2, ',', '.')); ?></strong>
                    </div>
                    <small>Pendentes: <?php echo e($prefixoMoeda); ?> <?php echo e(number_format($receitasPendentes, 2, ',', '.')); ?></small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--despesa h-100">
                    <div>
                        <span>Despesas por frota</span>
                        <strong class="text-danger"><?php echo e($prefixoMoeda); ?> <?php echo e(number_format($despesas, 2, ',', '.')); ?></strong>
                    </div>
                    <small>Pendentes: <?php echo e($prefixoMoeda); ?> <?php echo e(number_format($despesasPendentes, 2, ',', '.')); ?></small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--saldo h-100">
                    <div>
                        <span>Resultado realizado</span>
                        <strong class="<?php echo e($resultado >= 0 ? 'text-success' : 'text-danger'); ?>">
                            <?php echo e($prefixoMoeda); ?> <?php echo e(number_format($resultado, 2, ',', '.')); ?>

                        </strong>
                    </div>
                    <small>Receitas menos despesas pagas</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--projetado h-100">
                    <div>
                        <span>Margem operacional</span>
                        <strong class="<?php echo e($margem >= 0 ? 'text-success' : 'text-danger'); ?>">
                            <?php echo e(number_format($margem, 1, ',', '.')); ?>%
                        </strong>
                    </div>
                    <small>Baseada nas receitas realizadas</small>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-car-side"></i>
                        <span>DRE por carro<span class="fc-subtitle">Lucratividade por placa no periodo filtrado</span></span>
                    </h5>
                    <a href="<?php echo e(route('financeiro.fluxo-caixa.index', request()->query())); ?>" class="btn btn-outline-secondary fc-btn">
                        <i class="fas fa-list"></i> Ver lançamentos
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table fc-table">
                    <thead>
                        <tr>
                            <th class="px-3">Veiculo</th>
                            <th>Placa</th>
                            <th class="text-right">Receitas</th>
                            <th class="text-right">Despesas</th>
                            <th class="text-right">Resultado</th>
                            <th class="text-right">Margem</th>
                            <th class="text-center">Lancamentos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $analise; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $receitaItem = (float) $item->receitas;
                                $despesaItem = (float) $item->despesas;
                                $resultadoItem = (float) $item->resultado;
                                $margemItem = $receitaItem > 0 ? ($resultadoItem / $receitaItem) * 100 : 0;
                            ?>
                            <tr>
                                <td class="px-3 font-weight-bold">
                                    <?php echo e($item->veiculo_nome); ?>

                                    <small class="d-block text-muted">#<?php echo e($item->veiculo_id); ?></small>
                                </td>
                                <td>
                                    <span class="fc-badge bg-success-light">
                                        <i class="fas fa-id-card"></i><?php echo e($item->placa ?: '-'); ?>

                                    </span>
                                </td>
                                <td class="text-right font-weight-bold text-success">
                                    <?php echo e($prefixoMoeda); ?> <?php echo e(number_format($receitaItem, 2, ',', '.')); ?>

                                </td>
                                <td class="text-right font-weight-bold text-danger">
                                    <?php echo e($prefixoMoeda); ?> <?php echo e(number_format($despesaItem, 2, ',', '.')); ?>

                                </td>
                                <td class="text-right font-weight-bold <?php echo e($resultadoItem >= 0 ? 'text-success' : 'text-danger'); ?>">
                                    <?php echo e($prefixoMoeda); ?> <?php echo e(number_format($resultadoItem, 2, ',', '.')); ?>

                                </td>
                                <td class="text-right">
                                    <span class="fc-badge <?php echo e($resultadoItem >= 0 ? 'fc-badge--receita' : 'fc-badge--despesa'); ?>">
                                        <?php echo e(number_format($margemItem, 1, ',', '.')); ?>%
                                    </span>
                                </td>
                                <td class="text-center"><?php echo e((int) $item->quantidade_lancamentos); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-chart-pie d-block mb-3" style="font-size: 2.4rem; opacity: .18;"></i>
                                    Nenhum lançamento com veiculo vinculado nesse periodo.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end">
                <?php echo e($analise->links()); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/financeiro/analise_veiculos.blade.php ENDPATH**/ ?>