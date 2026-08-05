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
    <div class="fc-page">
        <div class="card fc-card fc-filter mb-4">
            <div class="card-body py-3">
                <form method="GET" action="<?php echo e(route('financeiro.fluxo-caixa.dashboard')); ?>" class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label for="data_inicio" class="fc-label">Data início</label>
                        <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?php echo e($dataInicio); ?>">
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label for="data_fim" class="fc-label">Data fim</label>
                        <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?php echo e($dataFim); ?>">
                    </div>
                    <div class="col-md-4 fc-actions">
                        <button type="submit" class="btn btn-primary fc-btn flex-grow-1">
                            <i class="fas fa-sync-alt"></i> Atualizar relatório
                        </button>
                        <a href="<?php echo e(route('financeiro.fluxo-caixa.index')); ?>" class="btn btn-outline-secondary fc-btn">
                            <i class="fas fa-list"></i> Lançamentos
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--receita h-100">
                    <div>
                        <span>Receitas realizadas</span>
                        <strong class="text-success"><?php echo e($moeda === 'BRL' ? 'R$' : $moeda); ?> <?php echo e(number_format($receitasPagas, 2, ',', '.')); ?></strong>
                    </div>
                    <small>Previsto pendente: <?php echo e($moeda === 'BRL' ? 'R$' : $moeda); ?> <?php echo e(number_format($receitasPendentes, 2, ',', '.')); ?></small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--despesa h-100">
                    <div>
                        <span>Despesas realizadas</span>
                        <strong class="text-danger"><?php echo e($moeda === 'BRL' ? 'R$' : $moeda); ?> <?php echo e(number_format($despesasPagas, 2, ',', '.')); ?></strong>
                    </div>
                    <small>Previsto pendente: <?php echo e($moeda === 'BRL' ? 'R$' : $moeda); ?> <?php echo e(number_format($despesasPendentes, 2, ',', '.')); ?></small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--saldo h-100">
                    <div>
                        <span>Saldo realizado</span>
                        <strong class="<?php echo e($saldoRealizado >= 0 ? 'text-success' : 'text-danger'); ?>">
                            <?php echo e($moeda === 'BRL' ? 'R$' : $moeda); ?> <?php echo e(number_format($saldoRealizado, 2, ',', '.')); ?>

                        </strong>
                    </div>
                    <small>Dinheiro em caixa</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--projetado h-100">
                    <div>
                        <span>Saldo projetado</span>
                        <strong class="<?php echo e($saldoProjetado >= 0 ? 'text-success' : 'text-danger'); ?>">
                            <?php echo e($moeda === 'BRL' ? 'R$' : $moeda); ?> <?php echo e(number_format($saldoProjetado, 2, ',', '.')); ?>

                        </strong>
                    </div>
                    <small>Considera pendentes</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card fc-card h-100">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h5 class="fc-title"><i class="fas fa-chart-column"></i><span>Fluxo diário<span class="fc-subtitle">Receitas vs despesas por dia</span></span></h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 320px;">
                            <canvas id="cashFlowChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card fc-card h-100">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h5 class="fc-title"><i class="fas fa-tags"></i><span>Maiores categorias<span class="fc-subtitle">Concentração financeira do período</span></span></h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table fc-table">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2">Categoria</th>
                                        <th class="py-2">Tipo</th>
                                        <th class="py-2 text-right px-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $topCategorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $top): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="px-3 py-2 font-weight-bold"><?php echo e($top->categoria); ?></td>
                                            <td class="py-2">
                                                <span class="fc-badge <?php echo e($top->tipo === 'receita' ? 'fc-badge--receita' : 'fc-badge--despesa'); ?>">
                                                    <?php echo e($top->tipo === 'receita' ? 'Receita' : 'Despesa'); ?>

                                                </span>
                                            </td>
                                            <td class="py-2 text-right px-3 font-weight-bold <?php echo e($top->tipo === 'receita' ? 'text-success' : 'text-danger'); ?>">
                                                <?php echo e($moeda === 'BRL' ? 'R$' : $moeda); ?> <?php echo e(number_format($top->total, 2, ',', '.')); ?>

                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-5 text-muted">Nenhum lançamento no período.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('cashFlowChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chartLabels, 15, 512) ?>,
                    datasets: [
                        { label: 'Receitas', data: <?php echo json_encode($chartReceitas, 15, 512) ?>, backgroundColor: '#059669', borderRadius: 6 },
                        { label: 'Despesas', data: <?php echo json_encode($chartDespesas, 15, 512) ?>, backgroundColor: '#dc2626', borderRadius: 6 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#edf2f7' } },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { boxWidth: 12, usePointStyle: true, pointStyle: 'circle' }
                        }
                    }
                }
            });
        });
    </script>

    <?php echo $__env->make('financeiro.fluxo_caixa._styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/financeiro/fluxo_caixa/dashboard.blade.php ENDPATH**/ ?>