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
    <?php if (isset($component)) { $__componentOriginal2ff7b3043c692896c2a650ccae21c5e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4 = $attributes; } ?>
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo,'createUrl' => route('manutencao.planos.create')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

    <div class="card shadow-sm border-0 manutencao-card">
        <div class="card-header manutencao-card__header d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
            <div>
                <div class="manutencao-card__eyebrow">Planos de manutenção preventiva</div>
                <h3 class="card-title mb-0 text-sm text-bold">
                    REGISTROS <span class="badge badge-success"><?php echo e($planos instanceof \Illuminate\Pagination\LengthAwarePaginator ? $planos->total() : count($planos)); ?></span>
                </h3>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                <a href="<?php echo e(route('manutencao.planos.create')); ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i>Novo plano
                </a>
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

        <div class="card-body p-0 table-responsive manutencao-table-wrap">
            <table class="table table-striped table-hover table-bordered table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Grupo de Frota</th>
                        <th>Itens</th>
                        <th style="width: 180px;">Ações</th>
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
                            <td>
                                <div class="d-flex flex-wrap" style="gap:.35rem;">
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
                            <td colspan="5">
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
                            <td colspan="5" class="text-center py-4">Nenhum plano cadastrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($planos instanceof \Illuminate\Pagination\LengthAwarePaginator): ?>
            <div class="card-footer d-flex justify-content-end">
                <?php echo e($planos->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script></script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    .manutencao-card { border-radius: 16px; overflow: hidden; }
    .manutencao-card__header {
        background: linear-gradient(135deg, #f7fafc 0%, #eef4ff 55%, #f7fbff 100%);
        border-bottom: 1px solid #e8edf5;
        padding: 1rem 1.25rem;
    }
    .manutencao-card__eyebrow {
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #5b6b7f;
        margin-bottom: .25rem;
    }
    .manutencao-table-wrap .table {
        min-width: 860px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/manutencao/planos/index.blade.php ENDPATH**/ ?>