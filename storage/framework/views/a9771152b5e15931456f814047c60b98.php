<?php $__env->startSection('title', $titulo); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => $breadcrumb ?? []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
        $totalItens = $items instanceof \Illuminate\Pagination\LengthAwarePaginator ? $items->total() : count($items);
    ?>

    <div class="container-fluid px-0 vehicle-show-page manutencao-preventiva-shell">
        <div class="module-card mb-3">
            <div class="module-card-header manutencao-hero-compact">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Manutenção · Planos de manutenção</div>
                        <h1 class="module-header-title">Itens de manutenção</h1>
                        <p class="module-header-sub">Cadastre e organize os itens usados nos planos de manutenção preventiva.</p>
                    </div>
                </div>

                <div class="module-header-right">
                    <a href="<?php echo e(route('manutencao.itens.create')); ?>" class="btn-xp btn-xp--sm">
                        <i class="fas fa-plus"></i> Novo item
                    </a>
                </div>
            </div>
        </div>

        <div class="config-card shadow-sm border-0">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Lista operacional</span>
                    <h4 class="vs-panel-head__title mb-1">Itens cadastrados</h4>
                    <p class="vs-panel-head__sub mb-0">Visualização em cards com leitura rápida para facilitar manutenção e reutilização nos planos.</p>
                </div>
                <div class="d-flex flex-column align-items-stretch" style="gap:.5rem; min-width:min(100%, 520px);">
                    <form method="GET" action="<?php echo e(route('manutencao.itens.index')); ?>" class="item-filtro-shell">
                        <div class="item-filtro-shell__search">
                            <input type="text" name="q" value="<?php echo e($q ?? ''); ?>" class="form-control" placeholder="Filtrar por nome ou descrição">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i>Filtrar
                            </button>
                        </div>
                    </form>
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.5rem;">
                        <form action="<?php echo e(route('manutencao.itens.setPerPage')); ?>" method="POST" class="mb-0">
                            <?php echo csrf_field(); ?>
                            <?php if (isset($component)) { $__componentOriginal65c0c73c271c840668c600386ae808f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65c0c73c271c840668c600386ae808f3 = $attributes; } ?>
<?php $component = App\View\Components\PerPageSelector::resolve(['perPages' => $perPages ?? [12, 24, 48, 96],'selectedPerPage' => $perPage ?? 15] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                            <?php echo e($totalItens); ?> registro(s)
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="col-12 col-sm-6 col-md-4 col-xl-2-4 mb-3">
                            <div class="item-card h-100">
                                <div class="item-card__head">
                                    <div class="item-card__icon"><?php echo e(strtoupper(mb_substr($item->nome ?? 'I', 0, 1))); ?></div>
                                    <div class="flex-grow-1 min-w-0">
                                        <h5 class="item-card__title"><?php echo e($item->nome); ?></h5>
                                        <div class="item-card__sub"><?php echo e($item->descricao ?: 'Sem descrição'); ?></div>
                                    </div>
                                </div>

                                <div class="item-card__actions">
                                    <a href="<?php echo e(route('manutencao.itens.edit', $item->id)); ?>" class="btn btn-xs btn-outline-primary">
                                        <i class="fa fa-edit mr-1"></i>Editar
                                    </a>
                                    <?php if (isset($component)) { $__componentOriginal2550ac35d7599d92e03b1bde3934d26a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a = $attributes; } ?>
<?php $component = App\View\Components\DeleteButton::resolve(['action' => route('manutencao.itens.destroy', $item->id)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-12">
                            <div class="empty-card text-center text-muted py-5">
                                Nenhum item cadastrado.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($items instanceof \Illuminate\Pagination\LengthAwarePaginator): ?>
                <div class="card-footer d-flex justify-content-end">
                    <?php echo e($items->links()); ?>

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
    .item-filtro-shell {
        width: 100%;
    }
    .item-filtro-shell__search {
        display: flex;
        gap: .5rem;
        align-items: center;
    }
    .item-filtro-shell__search .form-control {
        border-radius: 12px;
        border-color: #d8e1ec;
        min-height: 42px;
    }
    .item-filtro-shell__search .btn {
        border-radius: 12px;
        min-height: 42px;
        font-weight: 700;
        padding-inline: 1rem;
    }
    .item-card {
        background: #fff;
        border: 1px solid #e7edf5;
        border-radius: 16px;
        padding: .85rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .04);
        display: flex;
        flex-direction: column;
        gap: .7rem;
    }
    .item-card__head {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
    }
    .item-card__icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #eef4ff 0%, #dbeafe 100%);
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        flex-shrink: 0;
    }
    .item-card__title {
        margin: 0;
        font-size: .96rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .item-card__sub {
        color: #64748b;
        font-size: .78rem;
        margin-top: .15rem;
        line-height: 1.25;
    }
    .item-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }
    .item-card__actions .btn {
        flex: 1 1 auto;
        border-radius: 10px;
        font-weight: 700;
    }
    .empty-card {
        background: #fff;
        border: 1px dashed #d8e1ec;
        border-radius: 16px;
    }
    .col-xl-2-4 { flex: 0 0 20%; max-width: 20%; }
    @media (max-width: 1399.98px) {
        .col-xl-2-4 { flex: 0 0 25%; max-width: 25%; }
    }
    @media (max-width: 991.98px) {
        .col-xl-2-4 { flex: 0 0 33.3333%; max-width: 33.3333%; }
    }
    @media (max-width: 767.98px) {
        .col-xl-2-4 { flex: 0 0 100%; max-width: 100%; }
        .item-filtro-shell__search {
            flex-direction: column;
            align-items: stretch;
        }
        .module-card-header,
        .vs-panel-head {
            padding-left: 16px;
            padding-right: 16px;
        }
        .module-card-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/manutencao/itens/index.blade.php ENDPATH**/ ?>