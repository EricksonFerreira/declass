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
    <?php if(isset($filters) && is_array($filters) && count($filters) > 0): ?>
        <?php if (isset($component)) { $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d = $attributes; } ?>
<?php $component = App\View\Components\Filter::resolve(['additionalFilters' => $additionalFilters ?? [],'filters' => $filters,'itensFiltered' => $itensFiltered ?? false] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
    <?php endif; ?>

    <div class="container-fluid px-0 vehicle-show-page manutencao-preventiva-shell">
        <div class="module-card mb-3">
            <div class="module-card-header vehicle-hero-compact">
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
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-total">
                    <div class="kpi-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Planos</span>
                        <strong class="kpi-value"><?php echo e($planos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $planos->total() : count($planos)); ?></strong>
                        <small class="kpi-subtitle">Cadastros ativos e históricos</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-available">
                    <div class="kpi-icon"><i class="fas fa-car"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Com veículo</span>
                        <strong class="kpi-value"><?php echo e($planos->whereNotNull('veiculo_id')->count()); ?></strong>
                        <small class="kpi-subtitle">Planos amarrados a uma unidade</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-partial">
                    <div class="kpi-icon"><i class="fas fa-sitemap"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Com grupo</span>
                        <strong class="kpi-value"><?php echo e($planos->whereNotNull('grupo_frota_id')->count()); ?></strong>
                        <small class="kpi-subtitle">Planos por modelo de frota</small>
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
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive vehicle-table-wrap">
                    <table class="table table-hover mb-0 vehicle-list-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Grupo de Frota</th>
                                <th>Itens</th>
                        <th class="d-none d-lg-table-cell">Criado por</th>
                        <th class="d-none d-lg-table-cell">Atualizado por</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $planos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $collapseId = 'plano-itens-'.$plano->id;
                                ?>
                                <tr>
                                    <td><?php echo e($plano->id); ?></td>
                                    <td class="font-weight-bold"><?php echo e($plano->nome); ?></td>
                                    <td><?php echo e($plano->grupoFrota->nome ?? '-'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-outline-primary" data-toggle="collapse" data-target="#<?php echo e($collapseId); ?>" aria-expanded="false">
                                            <i class="fas fa-list mr-1"></i><?php echo e($plano->itens->count()); ?> item(ns)
                                        </button>
                                    </td>
                                    <td class="d-none d-lg-table-cell"><?php echo e($plano->usuarioInclusao?->nome ?? '-'); ?></td>
                                    <td class="d-none d-lg-table-cell"><?php echo e($plano->usuarioAtualizacao?->nome ?? '-'); ?></td>
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
                                    <td colspan="7">
                                        <div class="p-2">
                                            <?php if($plano->itens->isNotEmpty()): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0 bg-white">
                                                        <thead class="thead-light">
                                                            <tr>
                                                                <th>Item</th>
                                                                <th>Meses</th>
                                                                <th>KM</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $__currentLoopData = $plano->itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemPlano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <tr>
                                                                    <td><?php echo e($itemPlano->item?->nome ?? ('Item #'.$itemPlano->item_id)); ?></td>
                                                                    <td><?php echo e($itemPlano->frequencia_meses ? $itemPlano->frequencia_meses . ' meses' : '-'); ?></td>
                                                                    <td><?php echo e($itemPlano->frequencia_km ? number_format((float) $itemPlano->frequencia_km, 0, ',', '.') . ' km' : '-'); ?></td>
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
                                    <td colspan="7" class="text-center py-4 text-muted">Nenhum plano cadastrado.</td>
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

<?php $__env->startSection('js'); ?>
    <script></script>
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
        min-width: 900px;
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
    .manutencao-card {
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 16px 44px rgba(15, 23, 42, .08);
    }
    .manutencao-card__header {
        background: linear-gradient(135deg, #f8fafc 0%, #eef4ff 50%, #f8fbff 100%);
    }
    .manutencao-card__badge,
    .manutencao-card__tip {
        border-radius: 14px;
    }
    .manutencao-card .form-control,
    .manutencao-card .form-select {
        border-radius: 12px;
        border-color: #dbe4ef;
        min-height: 42px;
    }
    .manutencao-card .form-control:focus,
    .manutencao-card .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .15);
    }
    .manutencao-section {
        border-radius: 18px;
        border: 1px solid #e5eaf2;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .04);
    }
    .manutencao-table-wrap {
        border-radius: 12px;
        overflow: hidden;
    }
    .manutencao-table-wrap .table {
        min-width: 640px;
    }
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
        .vs-panel-head {
            padding-left: 14px;
            padding-right: 14px;
        }
        .vehicle-list-table thead th,
        .vehicle-list-table tbody td {
            font-size: .86rem;
        }
        .manutencao-card__header,
        .manutencao-card .card-body {
            padding-left: 14px;
            padding-right: 14px;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/manutencao/planos/index.blade.php ENDPATH**/ ?>