<?php $__env->startSection('title', $titulo); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => [[ 'name' => 'Veículos', 'url' => route('veiculo.index') ], [ 'name' => 'Manutenção preventiva', 'url' => route('manutencao.preventivas.index') ], [ 'name' => 'Planos de manutenção' ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
        $totalPlanos = $planos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $planos->total() : count($planos);
        $comVeiculo = $planos->whereNotNull('veiculo_id')->count();
        $comGrupo = $planos->whereNotNull('grupo_frota_id')->count();
        $totalItens = $planos->sum(fn ($plano) => $plano->itens->count());
    ?>

    <div class="container-fluid px-0 vehicle-show-page manutencao-preventiva-shell">
        <div class="module-card mb-3">
            <div class="module-card-header manutencao-hero-compact">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Veículos · Manutenção preventiva</div>
                        <h1 class="module-header-title">Planos de manutenção</h1>
                        <p class="module-header-sub">Configure, revise e compare planos com vínculo por veículo ou por grupo de frota.</p>
                    </div>
                </div>

                <div class="module-header-right">
                    <a href="<?php echo e(route('manutencao.planos.create')); ?>" class="btn-xp btn-xp--sm">
                        <i class="fas fa-plus"></i> Novo plano
                    </a>
                    
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="kpi-card kpi-total">
                    <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Planos</span>
                        <strong class="kpi-value"><?php echo e($totalPlanos); ?></strong>
                        <small class="kpi-subtitle">Cadastros ativos e históricos</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="kpi-card kpi-available">
                    <div class="kpi-icon"><i class="fas fa-car"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Com veículo</span>
                        <strong class="kpi-value"><?php echo e($comVeiculo); ?></strong>
                        <small class="kpi-subtitle">Planos amarrados a uma unidade</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="kpi-card kpi-partial">
                    <div class="kpi-icon"><i class="fas fa-sitemap"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Com grupo</span>
                        <strong class="kpi-value"><?php echo e($comGrupo); ?></strong>
                        <small class="kpi-subtitle">Planos por modelo de frota</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="kpi-card kpi-unavailable">
                    <div class="kpi-icon"><i class="fas fa-list-check"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Itens</span>
                        <strong class="kpi-value"><?php echo e($totalItens); ?></strong>
                        <small class="kpi-subtitle">Itens vinculados aos planos</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="config-card shadow-sm border-0">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Lista operacional</span>
                    <h4 class="vs-panel-head__title mb-1">Planos cadastrados</h4>
                    <p class="vs-panel-head__sub mb-0">Veja rapidamente a composição dos planos e abra os detalhes dos itens quando precisar comparar a configuração.</p>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                    <form action="<?php echo e(route('manutencao.planos.setPerPage')); ?>" method="POST" class="mb-0">
                        <?php echo csrf_field(); ?>
                        <?php if (isset($component)) { $__componentOriginal65c0c73c271c840668c600386ae808f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65c0c73c271c840668c600386ae808f3 = $attributes; } ?>
<?php $component = App\View\Components\PerPageSelector::resolve(['perPages' => $perPages ?? [15, 30, 50, 100],'selectedPerPage' => $perPage ?? 15] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('per-page-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PerPageSelector::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal65c0c73c271c840668c600386ae808f3)): ?>
<?php $attributes = $__attributesOriginal65c0c73c271c840668c600386ae808f3; ?>
<?php unset($__attributesOriginal65c0c73c271c840668c600386ae808f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal65c0c73c271c840668c600386ae808f3)): ?>
<?php $component = $__componentOriginal65c0c73c271c840668c600386ae808f3; ?>
<?php unset($__componentOriginal65c0c73c271c840668c600386ae808f3); ?>
<?php endif; ?>
                    </form>
                    <span class="vs-badge-soft">
                        <i class="fas fa-list"></i>
                        <?php echo e($totalPlanos); ?> registro(s)
                    </span>
                </div>
            </div>

            <?php if(isset($filters) && is_array($filters) && count($filters) > 0): ?>
                <div class="planos-filters-shell">
                    <?php if (isset($component)) { $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d = $attributes; } ?>
<?php $component = App\View\Components\Filter::resolve(['additionalFilters' => $additionalFilters ?? [],'filters' => $filters,'itensFiltered' => $itensFiltered ?? []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Filter::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d)): ?>
<?php $attributes = $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d; ?>
<?php unset($__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb68d555c13273f9d26aedfa8ff6c880d)): ?>
<?php $component = $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d; ?>
<?php unset($__componentOriginalb68d555c13273f9d26aedfa8ff6c880d); ?>
<?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="card-body p-0">
                <div class="table-responsive vehicle-table-wrap">
                    <table class="table table-hover mb-0 vehicle-list-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Vínculo</th>
                                <th>Itens</th>
                                <th class="d-none d-lg-table-cell">Responsáveis</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $planos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $collapseId = 'plano-itens-'.$plano->id;
                                    $vinculoLabel = $plano->veiculo?->nome
                                        ? 'Veículo: '.$plano->veiculo->nome
                                        : ($plano->grupoFrota->nome ?? '-');
                                ?>
                                <tr>
                                    <td><?php echo e($plano->id); ?></td>
                                    <td>
                                        <div class="table-value-stack">
                                            <span><?php echo e($plano->nome); ?></span>
                                            <small><?php echo e($plano->descricao ?: 'Sem descrição'); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-value-stack">
                                            <span><?php echo e($vinculoLabel); ?></span>
                                            <small><?php echo e($plano->veiculo?->placa_licenca ?? 'Grupo de frota'); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-outline-primary" data-toggle="collapse" data-target="#<?php echo e($collapseId); ?>" aria-expanded="false">
                                            <i class="fas fa-list mr-1"></i><?php echo e($plano->itens->count()); ?> item(ns)
                                        </button>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <div class="table-value-stack">
                                            <span><?php echo e($plano->usuarioInclusao?->nome ?? '-'); ?></span>
                                            <small><?php echo e($plano->usuarioAtualizacao?->nome ?? '-'); ?></small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-wrap justify-content-center" style="gap:.35rem;">
                                            <a href="<?php echo e(route('manutencao.planos.edit', $plano->id)); ?>" class="btn btn-xs btn-warning">
                                                <i class="fa fa-edit"></i> Editar
                                            </a>
                                            <?php if (isset($component)) { $__componentOriginal2550ac35d7599d92e03b1bde3934d26a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a = $attributes; } ?>
<?php $component = App\View\Components\DeleteButton::resolve(['action' => route('manutencao.planos.destroy', $plano->id)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                                        </div>
                                    </td>
                                </tr>
                                <tr class="collapse bg-light" id="<?php echo e($collapseId); ?>">
                                    <td colspan="6">
                                        <div class="p-2">
                                            <?php if($plano->itens->isNotEmpty()): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0 bg-white vehicle-subtable">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th>Item</th>
                                                                <th>Valores</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $__currentLoopData = $plano->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemPlano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <tr>
                                                                    <td><?php echo e($itemPlano->item?->nome ?? ('Item #'.$itemPlano->item_id)); ?></td>
                                                                    <td>
                                                                        <div class="table-value-stack">
                                                                            <span><?php echo e($itemPlano->frequencia_meses ? $itemPlano->frequencia_meses . ' meses' : '-'); ?></span>
                                                                            <small><?php echo e($itemPlano->frequencia_km ? number_format((float) $itemPlano->frequencia_km, 0, ',', '.') . ' km' : '-'); ?></small>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-muted">Este plano ainda não possui itens vinculados.</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Nenhum plano cadastrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if($planos instanceof \Illuminate\Pagination\LengthAwarePaginator): ?>
                <div class="card-footer d-flex justify-content-end">
                    <?php echo e($planos->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    .manutencao-preventiva-shell { padding-bottom: 8px; }
    .module-card {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(12, 20, 40, .10);
        background: #fff;
    }
    .module-card-header {
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }
    .module-card-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255, 255, 255, 0.055) 1px, transparent 1px);
        background-size: 20px 20px;
        pointer-events: none;
    }
    .module-card-header::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981);
    }
    .module-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }
    .module-header-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #60a5fa;
        flex-shrink: 0;
        backdrop-filter: blur(8px);
    }
    .module-header-eyebrow {
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: rgba(148, 163, 184, .8);
        display: block;
        margin-bottom: 3px;
    }
    .module-header-title {
        font-size: 1.45rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 3px;
        letter-spacing: -.02em;
    }
    .module-header-sub {
        margin: 0;
        font-size: .82rem;
        color: rgba(148, 163, 184, .9);
    }
    .module-header-right {
        position: relative;
        z-index: 1;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .config-card {
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e2e9f4;
        box-shadow: 0 2px 12px rgba(12, 20, 40, .06);
    }
    .vs-panel-head {
        background: #fff;
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        border-bottom: 1px solid #e7edf6;
    }
    .vs-panel-head__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: #64748b;
    }
    .vs-panel-head__title {
        margin: 0 0 6px;
        font-size: 1.08rem;
        font-weight: 800;
        color: #0c1428;
    }
    .vs-panel-head__sub {
        margin: 0;
        color: #64748b;
        font-size: .84rem;
        max-width: 680px;
    }
    .vs-badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        background: #eef2ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .vehicle-table-wrap {
        border-top: 1px solid #edf2f7;
        max-height: 74vh;
        overflow: auto;
    }
    .planos-filters-shell {
        padding: 14px 16px 4px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .planos-filters-shell .card {
        margin-bottom: 0;
        border: 1px solid #e2e8f0;
        box-shadow: none;
    }
    .vehicle-list-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .vehicle-list-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8fafc;
        border-bottom: 1px solid #cbd5e1;
        font-size: 11px;
        text-transform: uppercase;
        color: #334155;
        letter-spacing: .02em;
        vertical-align: middle;
    }
    .vehicle-list-table tbody td {
        vertical-align: middle;
        background: #fff;
        border-top: 1px solid #edf2f7;
    }
    .vehicle-list-table tbody tr:hover td {
        background: #f8fafc;
    }
   .btn-xp--outline-light {
    background: rgba(255, 255, 255, .12); !important;
    border: 1px solid rgba(255, 255, 255, .25);
    color: rgba(255, 255, 255, .9);
    box-shadow: none;
}
.btn-xp--sm {
    padding: 6px 12px;
    font-size: .78rem;
    border-radius: 8px;
}
.btn-xp {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #4338ca, #6366f1);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 9px 16px;
    font-size: .82rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: all .18s;
    letter-spacing: -.01em;
    box-shadow: 0 4px 12px rgba(67, 56, 202, .25);
}
    .kpi-card {
        width: 100%;
        border: 0;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        color: #0f172a;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
        min-height: 96px;
        background: #fff;
    }
    .kpi-card .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex: 0 0 auto;
        color: #fff;
    }
    .kpi-meta .kpi-label {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        opacity: .9;
        font-weight: 700;
    }
    .kpi-meta .kpi-value {
        display: block;
        font-size: 28px;
        line-height: 1.05;
        margin-top: 2px;
    }
    .kpi-subtitle {
        display: block;
        color: #475569;
        margin-top: 2px;
    }
    .kpi-total { background: linear-gradient(135deg, #e2e8f0, #cbd5e1); }
    .kpi-total .kpi-icon { background: #94a3b8; }
    .kpi-available { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
    .kpi-available .kpi-icon { background: #16a34a; }
    .kpi-partial { background: linear-gradient(135deg, #fef3c7, #fde68a); }
    .kpi-partial .kpi-icon { background: #f59e0b; }
    .kpi-unavailable { background: linear-gradient(135deg, #fee2e2, #fecaca); }
    .kpi-unavailable .kpi-icon { background: #dc2626; }
    .table-value-stack {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .table-value-stack span {
        font-weight: 700;
        color: #0f172a;
    }
    .table-value-stack small {
        color: #64748b;
        font-size: .76rem;
    }
    @media (max-width: 991.98px) {
        .module-card-header {
            align-items: flex-start;
            flex-direction: column;
        }
        .module-header-right {
            width: 100%;
        }
        .module-header-right .btn-xp {
            flex: 1 1 auto;
            justify-content: center;
        }
        .vs-panel-head {
            padding-left: 14px;
            padding-right: 14px;
        }
        .vehicle-list-table thead th,
        .vehicle-list-table tbody td { font-size: .86rem; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/manutencao/planos/index.blade.php ENDPATH**/ ?>