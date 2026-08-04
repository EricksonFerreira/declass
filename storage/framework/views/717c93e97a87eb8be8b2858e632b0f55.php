<?php $__env->startSection('title', $titulo); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => [[ 'name' => 'Manutenções Preventivas' ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
    <div class="d-flex justify-content-end mb-3">
        <a href="<?php echo e(route('manutencao.planos.index')); ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-clipboard-list mr-1"></i> Plano de Manutenção Preventiva
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted text-xs">Pendências</div>
                    <div class="h3 mb-0"><?php echo e(number_format((int) ($stats['pendentes'] ?? 0), 0, ',', '.')); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted text-xs">Atrasadas</div>
                    <div class="h3 mb-0 text-danger"><?php echo e(number_format((int) ($stats['atrasadas'] ?? 0), 0, ',', '.')); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted text-xs">Execuções</div>
                    <div class="h3 mb-0 text-success"><?php echo e(number_format((int) ($stats['executadas'] ?? 0), 0, ',', '.')); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('manutencao.preventivas.index')); ?>" class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="small mb-1">Veículo</label>
                    <select name="veiculo_id" class="form-control">
                        <option value="">Todos</option>
                        <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $veiculo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($veiculo->id); ?>" <?php echo e((int) request('veiculo_id') === (int) $veiculo->id ? 'selected' : ''); ?>>
                                <?php echo e($veiculo->nome); ?> - <?php echo e($veiculo->placa_licenca ?? 'sem placa'); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small mb-1">Item</label>
                    <select name="item_id" class="form-control">
                        <option value="">Todos</option>
                        <?php $__currentLoopData = $itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item->id); ?>" <?php echo e((int) request('item_id') === (int) $item->id ? 'selected' : ''); ?>>
                                <?php echo e($item->nome); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small mb-1">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="pendente" <?php echo e(request('status') === 'pendente' ? 'selected' : ''); ?>>Pendente</option>
                        <option value="atrasado" <?php echo e(request('status') === 'atrasado' ? 'selected' : ''); ?>>Atrasado</option>
                        <option value="concluida" <?php echo e(request('status') === 'concluida' ? 'selected' : ''); ?>>Concluída</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary btn-block" type="submit"><i class="fas fa-filter mr-1"></i>Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Pendências</strong>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>Veículo</th>
                        <th>Item</th>
                        <th>Previsão</th>
                        <th>KM</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $pendencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pendencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <?php if($pendencia->veiculo): ?>
                                    <a href="<?php echo e(route('veiculo.show', $pendencia->veiculo->id)); ?>" class="font-weight-bold">
                                        <?php echo e($pendencia->veiculo->nome); ?>

                                    </a>
                                    <div class="text-muted text-xs"><?php echo e($pendencia->veiculo->placa_licenca ?? 'sem placa'); ?></div>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($pendencia->item?->nome ?? ('Item #' . $pendencia->item_id)); ?></td>
                            <td><?php echo e($pendencia->data_prevista ? \Carbon\Carbon::parse($pendencia->data_prevista)->format('d/m/Y') : '—'); ?></td>
                            <td><?php echo e($pendencia->km_previsto !== null ? number_format((float) $pendencia->km_previsto, 0, ',', '.') : '—'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo e($pendencia->status === 'atrasado' ? 'danger' : 'warning'); ?>">
                                    <?php echo e(ucfirst($pendencia->status)); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Nenhuma pendência encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($pendencias->hasPages()): ?>
            <div class="card-footer d-flex justify-content-end">
                <?php echo e($pendencias->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Execuções recentes</strong>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>Veículo</th>
                        <th>Item</th>
                        <th>Data</th>
                        <th>KM</th>
                        <th>OS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $execucoesRecentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $execucao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <?php if($execucao->veiculo): ?>
                                    <a href="<?php echo e(route('veiculo.show', $execucao->veiculo->id)); ?>" class="font-weight-bold">
                                        <?php echo e($execucao->veiculo->nome); ?>

                                    </a>
                                    <div class="text-muted text-xs"><?php echo e($execucao->veiculo->placa_licenca ?? 'sem placa'); ?></div>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($execucao->item?->nome ?? ('Item #' . $execucao->item_id)); ?></td>
                            <td><?php echo e($execucao->data_execucao ? \Carbon\Carbon::parse($execucao->data_execucao)->format('d/m/Y') : '—'); ?></td>
                            <td><?php echo e($execucao->km_execucao !== null ? number_format((float) $execucao->km_execucao, 0, ',', '.') : '—'); ?></td>
                            <td><?php echo e($execucao->ordemServico?->numero ?? ($execucao->ordem_servico_id ? ('OS #' . $execucao->ordem_servico_id) : '—')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Nenhuma execução recente.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/manutencao/preventivas/index.blade.php ENDPATH**/ ?>