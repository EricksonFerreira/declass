<?php $__env->startSection('title', $titulo); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => [[ 'name' => 'Veículos', 'url' => route('veiculo.index') ], [ 'name' => 'Sinistros' ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
    <?php
        $totalSinistros = $sinistros->total();
        $qtdAbertos = $sinistros->whereIn('status', ['aberto', 'aguardando_documentos', 'em_analise'])->count();
        $qtdEncerrados = $sinistros->whereIn('status', ['encerrado', 'indenizado'])->count();
        $qtdCancelados = $sinistros->where('status', 'cancelado')->count();
    ?>

    <div class="container-fluid px-0 vehicle-show-page sinistro-show-page">
        <div class="module-card mb-3">
            <div class="module-card-header vehicle-hero-compact">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Veículos · Sinistros</div>
                        <h1 class="module-header-title">Sinistros registrados</h1>
                        <p class="module-header-sub">Acompanhe ocorrências, responsáveis, valores estimados e o andamento de cada caso.</p>
                    </div>
                </div>
                <div class="module-header-right">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sinistro.export')): ?>
                        <a href="<?php echo e(route('sinistro.export', request()->query())); ?>" class="btn-xp btn-xp--outline-light btn-xp--sm">
                            <i class="fas fa-file-csv"></i> CSV
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('sinistro.create')); ?>" class="btn-xp btn-xp--sm">
                        <i class="fas fa-plus"></i> Novo sinistro
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-total">
                    <div class="kpi-icon"><i class="fas fa-list"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Registros</span>
                        <strong class="kpi-value"><?php echo e($totalSinistros); ?></strong>
                        <small class="kpi-subtitle">Sinistros no período filtrado</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-unavailable">
                    <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Abertos</span>
                        <strong class="kpi-value"><?php echo e($qtdAbertos); ?></strong>
                        <small class="kpi-subtitle">Aguardando análise ou documentos</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-available">
                    <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Encerrados</span>
                        <strong class="kpi-value"><?php echo e($qtdEncerrados); ?></strong>
                        <small class="kpi-subtitle">Casos resolvidos ou indenizados</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="config-card shadow-sm border-0 mb-3">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Filtros operacionais</span>
                    <h4 class="vs-panel-head__title mb-1">Refinar listagem</h4>
                    <p class="vs-panel-head__sub mb-0">Filtre por período, placa, tipo e status para localizar o sinistro certo com mais agilidade.</p>
                </div>
                <span class="vs-badge-soft is-warning">
                    <i class="fas fa-filter"></i>
                    Filtro rápido
                </span>
            </div>
            <div class="doc-filters">
                <form method="GET" action="<?php echo e(route('sinistro.index')); ?>" class="doc-filters__form" id="formFiltrosSinistro">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="doc-filter-label">Data início</label>
                            <input type="date" name="data_inicio" class="form-control doc-filter-control" value="<?php echo e($filtros['data_inicio'] ?? ''); ?>">
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="doc-filter-label">Data fim</label>
                            <input type="date" name="data_fim" class="form-control doc-filter-control" value="<?php echo e($filtros['data_fim'] ?? ''); ?>">
                        </div>
                        <div class="col-lg-2 col-md-6 mb-2">
                            <label class="doc-filter-label">Placa</label>
                            <input type="text" name="placa" class="form-control doc-filter-control" value="<?php echo e($filtros['placa'] ?? ''); ?>" placeholder="Placa">
                        </div>
                        <div class="col-lg-2 col-md-6 mb-2">
                            <label class="doc-filter-label">Tipo</label>
                            <select name="tipo_sinistro" class="form-control doc-filter-control">
                                <option value="">Todos</option>
                                <?php $__currentLoopData = \App\Models\Sinistro::TIPOS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(($filtros['tipo_sinistro'] ?? '') === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 mb-2">
                            <label class="doc-filter-label">Status</label>
                            <select name="status" class="form-control doc-filter-control">
                                <option value="">Todos</option>
                                <?php $__currentLoopData = \App\Models\Sinistro::STATUS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>" <?php echo e(($filtros['status'] ?? '') === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-12 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-xp btn-xp--sm w-100 mr-2">
                                <i class="fas fa-search mr-1"></i> Filtrar
                            </button>
                            <a href="<?php echo e(route('sinistro.index')); ?>" class="btn btn-light btn-sm w-100">
                                Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="config-card shadow-sm border-0">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Lista operacional</span>
                    <h4 class="vs-panel-head__title mb-1">Sinistros cadastrados</h4>
                    <p class="vs-panel-head__sub mb-0">Clique em uma linha para ver detalhes, editar ou avançar no fluxo de tratamento do caso.</p>
                </div>
                <span class="vs-badge-soft is-warning">
                    <i class="fas fa-list"></i>
                    <?php echo e($totalSinistros); ?> registro(s)
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive vehicle-table-wrap">
                    <table class="table table-hover mb-0 vehicle-list-table">
                        <thead>
                            <tr>
                                <th>Sinistro</th>
                                <th>Veículo / Cliente</th>
                                <th>Ocorrido</th>
                                <th>Local / Seguradora</th>
                                <th>Prejuízo</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $sinistros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sinistro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $codigoReserva = $sinistro->reserva?->codigoReserva?->codigo
                                        ?? ($sinistro->reserva_id ? 'RES-' . str_pad((string) $sinistro->reserva_id, 6, '0', STR_PAD_LEFT) : null);
                                    $badgeStatus = match($sinistro->status) {
                                        'encerrado', 'indenizado' => 'status-badge--green',
                                        'aprovado' => 'status-badge--blue',
                                        'cancelado' => 'status-badge--gray',
                                        'aguardando_documentos' => 'status-badge--amber',
                                        default => 'status-badge--red',
                                    };
                                ?>
                                <tr onclick="window.location='<?php echo e(route('sinistro.show', $sinistro->id)); ?>';" style="cursor:pointer;">
                                    <td>
                                        <div class="font-weight-bold">#<?php echo e($sinistro->id); ?> · <?php echo e($sinistro->placa_momento ?? '-'); ?></div>
                                        <small class="text-muted"><?php echo e($sinistro->label_tipo); ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo e($codigoReserva ? 'Reserva #' . $codigoReserva : 'Sem reserva'); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo e($sinistro->veiculo?->nome ?? '-'); ?></div>
                                        <small class="text-muted"><?php echo e($sinistro->cliente?->nome ?? '-'); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo e($sinistro->data_hora_ocorrido?->format('d/m/Y H:i') ?? '-'); ?></div>
                                        <small class="text-muted"><?php echo e($sinistro->label_tipo); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo e(Str::limit($sinistro->local_ocorrido ?? '-', 40)); ?></div>
                                        <small class="text-muted"><?php echo e($sinistro->seguradora ?? '-'); ?></small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">
                                            <?php echo e($sinistro->valor_prejuizo_estimado !== null ? 'R$ ' . number_format((float) $sinistro->valor_prejuizo_estimado, 2, ',', '.') : '-'); ?>

                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo e($badgeStatus); ?>"><?php echo e($sinistro->label_status); ?></span>
                                    </td>
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <a href="<?php echo e(route('sinistro.show', $sinistro->id)); ?>" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sinistro.edit')): ?>
                                            <a href="<?php echo e(route('sinistro.edit', $sinistro->id)); ?>" class="btn btn-xs btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sinistro.delete')): ?>
                                            <?php if (isset($component)) { $__componentOriginal2550ac35d7599d92e03b1bde3934d26a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a = $attributes; } ?>
<?php $component = App\View\Components\DeleteButton::resolve(['action' => route('sinistro.destroy', $sinistro->id)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('delete-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\DeleteButton::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2550ac35d7599d92e03b1bde3934d26a)): ?>
<?php $attributes = $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a; ?>
<?php unset($__attributesOriginal2550ac35d7599d92e03b1bde3934d26a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2550ac35d7599d92e03b1bde3934d26a)): ?>
<?php $component = $__componentOriginal2550ac35d7599d92e03b1bde3934d26a; ?>
<?php unset($__componentOriginal2550ac35d7599d92e03b1bde3934d26a); ?>
<?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Nenhum sinistro encontrado para os filtros aplicados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <?php echo e($sinistros->withQueryString()->links()); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    .sinistro-show-page { padding-bottom: 8px; }
    .vehicle-table-wrap { max-height: none; }
    .vehicle-list-table thead th { white-space: nowrap; }
    .vehicle-list-table tbody td { vertical-align: middle; }
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: .73rem;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .status-badge--green { background: #dcfce7; color: #166534; }
    .status-badge--amber { background: #fef3c7; color: #92400e; }
    .status-badge--red { background: #fee2e2; color: #991b1b; }
    .status-badge--gray { background: #f1f5f9; color: #475569; }
    .status-badge--blue { background: #dbeafe; color: #1d4ed8; }
    @media (max-width: 991.98px) {
        .module-card-header { padding: 1rem; }
        .module-header-left { align-items: flex-start; }
        .module-header-icon {
            width: 44px; height: 44px; border-radius: 12px; font-size: 1.15rem;
        }
        .module-header-title { font-size: 1.15rem; }
        .module-header-sub { font-size: .8rem; }
        .vs-panel-head, .doc-filters { padding-left: 14px; padding-right: 14px; }
        .vehicle-list-table thead th,
        .vehicle-list-table tbody td { font-size: .86rem; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/sinistro/index.blade.php ENDPATH**/ ?>