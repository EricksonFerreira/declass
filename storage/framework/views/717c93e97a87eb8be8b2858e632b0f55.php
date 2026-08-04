<?php $__env->startSection('title', $titulo); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => [[ 'name' => 'Veículos', 'url' => route('veiculo.index') ], [ 'name' => 'Manutenção preventiva' ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid px-0 vehicle-show-page manutencao-preventiva-shell">
        <div class="module-card mb-3">
            <div class="module-card-header vehicle-hero-compact">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Veículos · Manutenção preventiva</div>
                        <h1 class="module-header-title">Manutenções preventivas</h1>
                        <p class="module-header-sub">Monitore pendências, priorize atrasos e acompanhe o histórico recente de execução.</p>
                    </div>
                </div>
                <div class="module-header-right">
                    <a href="<?php echo e(route('manutencao.planos.index')); ?>" class="btn-xp btn-xp--sm">
                        <i class="fas fa-clipboard-list"></i> Planos
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-total">
                    <div class="kpi-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Pendências</span>
                        <strong class="kpi-value"><?php echo e(number_format((int) ($stats['pendentes'] ?? 0), 0, ',', '.')); ?></strong>
                        <small class="kpi-subtitle">Itens aguardando ação</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-unavailable">
                    <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Atrasadas</span>
                        <strong class="kpi-value"><?php echo e(number_format((int) ($stats['atrasadas'] ?? 0), 0, ',', '.')); ?></strong>
                        <small class="kpi-subtitle">Prioridade máxima</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-available">
                    <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Execuções</span>
                        <strong class="kpi-value"><?php echo e(number_format((int) ($stats['executadas'] ?? 0), 0, ',', '.')); ?></strong>
                        <small class="kpi-subtitle">Histórico concluído</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="config-card shadow-sm border-0 mb-3">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Filtros operacionais</span>
                    <h4 class="vs-panel-head__title mb-1">Refinar acompanhamento</h4>
                    <p class="vs-panel-head__sub mb-0">Filtre por veículo, item e status para encontrar o próximo ponto de ação com mais rapidez.</p>
                </div>
                <span class="vs-badge-soft is-warning">
                    <i class="fas fa-filter"></i>
                    Filtro rápido
                </span>
            </div>
            <div class="doc-filters">
                <form method="GET" action="<?php echo e(route('manutencao.preventivas.index')); ?>" class="doc-filters__form">
                    <div class="row">
                        <div class="col-lg-5 col-md-6 col-12 mb-2">
                            <label class="doc-filter-label" for="filter_veiculo">Veículo</label>
                            <select id="filter_veiculo" name="veiculo_id" class="form-control doc-filter-control">
                                <option value="">Todos os veículos</option>
                                <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($veiculo->id); ?>" <?php echo e((int) request('veiculo_id') === (int) $veiculo->id ? 'selected' : ''); ?>>
                                        <?php echo e($veiculo->nome); ?> - <?php echo e($veiculo->placa_licenca ?? 'sem placa'); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-2">
                            <label class="doc-filter-label" for="filter_item">Item</label>
                            <select id="filter_item" name="item_id" class="form-control doc-filter-control">
                                <option value="">Todos os itens</option>
                                <?php $__currentLoopData = $itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($item->id); ?>" <?php echo e((int) request('item_id') === (int) $item->id ? 'selected' : ''); ?>>
                                        <?php echo e($item->nome); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12 mb-2">
                            <label class="doc-filter-label" for="filter_status">Status</label>
                            <select id="filter_status" name="status" class="form-control doc-filter-control">
                                <option value="">Todos</option>
                                <option value="pendente" <?php echo e(request('status') === 'pendente' ? 'selected' : ''); ?>>Pendente</option>
                                <option value="atrasado" <?php echo e(request('status') === 'atrasado' ? 'selected' : ''); ?>>Atrasado</option>
                                <option value="concluida" <?php echo e(request('status') === 'concluida' ? 'selected' : ''); ?>>Concluída</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6 col-12 mb-2 d-flex align-items-end">
                            <button class="btn btn-xp btn-xp--sm w-100" type="submit">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="config-card shadow-sm border-0">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Lista operacional</span>
                    <h4 class="vs-panel-head__title mb-1">Pendências e histórico recente</h4>
                    <p class="vs-panel-head__sub mb-0">Compare o que está vencido com o que já foi executado. A leitura abaixo prioriza decisão rápida.</p>
                </div>
                <span class="vs-badge-soft is-warning">
                    <i class="fas fa-list"></i>
                    <?php echo e($pendencias->count()); ?> na página
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive vehicle-table-wrap">
                    <table class="table table-hover mb-0 vehicle-list-table">
                        <thead>
                            <tr>
                                <th>Veículo</th>
                                <th>Item</th>
                                <th>Previsão</th>
                                <th>KM</th>
                                <th>Status</th>
                                <th class="d-none d-lg-table-cell">Criado por</th>
                                <th class="d-none d-lg-table-cell">Atualizado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $pendencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pendencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <?php if($pendencia->veiculo): ?>
                                            <a href="<?php echo e(route('veiculo.show', $pendencia->veiculo->id)); ?>" class="font-weight-bold d-block">
                                                <?php echo e($pendencia->veiculo->nome); ?>

                                            </a>
                                            <small class="text-muted"><?php echo e($pendencia->veiculo->placa_licenca ?? 'sem placa'); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($pendencia->item?->nome ?? ('Item #' . $pendencia->item_id)); ?></td>
                                    <td><?php echo e($pendencia->data_prevista ? \Carbon\Carbon::parse($pendencia->data_prevista)->format('d/m/Y') : '—'); ?></td>
                                    <td><?php echo e($pendencia->km_previsto !== null ? number_format((float) $pendencia->km_previsto, 0, ',', '.') : '—'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo e($pendencia->status === 'atrasado' ? 'status-badge--red' : 'status-badge--amber'); ?>">
                                            <?php echo e(ucfirst($pendencia->status)); ?>

                                        </span>
                                    </td>
                                    <td class="d-none d-lg-table-cell"><?php echo e($pendencia->usuarioInclusao?->nome ?? '—'); ?></td>
                                    <td class="d-none d-lg-table-cell"><?php echo e($pendencia->usuarioAtualizacao?->nome ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Nenhuma pendência encontrada.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if($pendencias->hasPages()): ?>
                <div class="card-footer d-flex justify-content-end">
                    <?php echo e($pendencias->links()); ?>

                </div>
            <?php endif; ?>

            <div class="card-body p-0 border-top">
                <div class="vs-panel-head border-bottom">
                    <div>
                        <span class="vs-panel-head__eyebrow">Histórico recente</span>
                        <h4 class="vs-panel-head__title mb-1">Execuções realizadas</h4>
                        <p class="vs-panel-head__sub mb-0">Registros já concluídos para orientar decisões de manutenção.</p>
                    </div>
                    <span class="vs-badge-soft">
                        <i class="fas fa-history"></i>
                        Últimos lançamentos
                    </span>
                </div>
                <div class="table-responsive vehicle-table-wrap">
                    <table class="table table-hover mb-0 vehicle-list-table">
                        <thead>
                            <tr>
                                <th>Veículo</th>
                                <th>Item</th>
                                <th>Data</th>
                                <th>KM</th>
                                <th>OS</th>
                                <th class="d-none d-lg-table-cell">Criado por</th>
                                <th class="d-none d-lg-table-cell">Atualizado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $execucoesRecentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $execucao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <?php if($execucao->veiculo): ?>
                                            <a href="<?php echo e(route('veiculo.show', $execucao->veiculo->id)); ?>" class="font-weight-bold d-block">
                                                <?php echo e($execucao->veiculo->nome); ?>

                                            </a>
                                            <small class="text-muted"><?php echo e($execucao->veiculo->placa_licenca ?? 'sem placa'); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($execucao->item?->nome ?? ('Item #' . $execucao->item_id)); ?></td>
                                    <td><?php echo e($execucao->data_execucao ? \Carbon\Carbon::parse($execucao->data_execucao)->format('d/m/Y') : '—'); ?></td>
                                    <td><?php echo e($execucao->km_execucao !== null ? number_format((float) $execucao->km_execucao, 0, ',', '.') : '—'); ?></td>
                                    <td><?php echo e($execucao->ordemServico?->numero ?? ($execucao->ordem_servico_id ? ('OS #' . $execucao->ordem_servico_id) : '—')); ?></td>
                                    <td class="d-none d-lg-table-cell"><?php echo e($execucao->usuarioInclusao?->nome ?? '—'); ?></td>
                                    <td class="d-none d-lg-table-cell"><?php echo e($execucao->usuarioAtualizacao?->nome ?? '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Nenhuma execução recente.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    .manutencao-preventiva-shell { padding-bottom: 8px; }
    .module-card,
    .config-card {
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }
    .module-card {
        box-shadow: 0 16px 44px rgba(15, 23, 42, .08);
    }
    .module-card-header,
    .vs-panel-head {
        background: linear-gradient(135deg, #f8fafc 0%, #eef4ff 50%, #f8fbff 100%);
    }
    .vehicle-table-wrap {
        max-height: none;
        overflow: auto;
    }
    .vehicle-list-table {
        min-width: 980px;
        margin-bottom: 0;
    }
    .vehicle-list-table thead th {
        white-space: nowrap;
        background: #f8fafc;
        font-weight: 800;
        color: #334155;
        border-bottom: 1px solid #e2e8f0;
    }
    .vehicle-list-table tbody td { vertical-align: middle; }
    .vehicle-list-table tbody tr:hover { background: #f8fbff; }
    .doc-filters {
        padding: 1rem;
        background: #fff;
    }
    .doc-filter-label {
        display: block;
        font-size: .75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #475569;
        margin-bottom: .35rem;
    }
    .doc-filter-control {
        border-radius: 12px;
        border-color: #dbe4ef;
        min-height: 42px;
    }
    .doc-filter-control:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .15);
    }
    .kpi-card {
        border-radius: 18px;
        padding: 1rem 1.1rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
    }
    .kpi-label {
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 800;
        font-size: .72rem;
    }
    .kpi-value { font-size: 1.5rem; line-height: 1.1; }
    .kpi-subtitle { color: #64748b; font-size: .78rem; }
    .status-badge--amber { background: #fef3c7; color: #92400e; }
    .status-badge--red { background: #fee2e2; color: #991b1b; }
    .status-badge--green { background: #dcfce7; color: #166534; }
    .status-badge--gray { background: #f1f5f9; color: #475569; }
    @media (max-width: 991.98px) {
        .module-card-header {
            padding: 1rem;
        }
        .module-header-left {
            align-items: flex-start;
        }
        .module-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            font-size: 1.15rem;
        }
        .module-header-title {
            font-size: 1.15rem;
        }
        .module-header-sub {
            font-size: .8rem;
        }
        .vs-panel-head,
        .doc-filters {
            padding-left: 14px;
            padding-right: 14px;
        }
        .doc-filters { padding-top: .9rem; padding-bottom: .9rem; }
        .vehicle-list-table thead th,
        .vehicle-list-table tbody td {
            font-size: .86rem;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/manutencao/preventivas/index.blade.php ENDPATH**/ ?>