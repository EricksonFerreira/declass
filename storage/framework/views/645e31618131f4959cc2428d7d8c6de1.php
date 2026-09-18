<?php $__env->startSection('title', 'Consumo × Estimado'); ?>

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => 'Consumo × Estimado por Projeto'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

<?php $__env->startSection('css'); ?>
<style>
.consumo-link-projeto { color: var(--empresa-cor-primaria, #007bff); }
.consumo-link-projeto:hover { color: var(--empresa-cor-primaria, #007bff); text-decoration: underline; filter: brightness(.8); }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $fmtHora = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.');
    $fmtPct  = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.').'%';
    $corPct  = fn ($v) => $v === null ? 'text-muted' : ($v > 100 ? 'text-danger' : 'text-success');
    $bgPct   = fn ($v) => $v === null ? '' : ($v > 100 ? 'bg-danger' : ($v >= 80 ? 'bg-warning' : 'bg-success'));
?>

<div class="card">
    <div class="card-header">
        <form method="GET" action="<?php echo e(route('relatorios.consumo')); ?>" class="form-inline flex-wrap" style="gap:.5rem;">
            <input type="text" name="busca" class="form-control form-control-sm" placeholder="Buscar projeto..."
                   value="<?php echo e($filtros['busca'] ?? ''); ?>" style="width:200px;">
            <select name="cliente_id" class="form-control form-control-sm">
                <option value="">Todos os clientes</option>
                <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($id); ?>" <?php echo e((string) ($filtros['cliente_id'] ?? '') === (string) $id ? 'selected' : ''); ?>><?php echo e($nome); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="status" class="form-control form-control-sm">
                <option value="">Todos os status</option>
                <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php echo e(($filtros['status'] ?? '') === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="ano" class="form-control form-control-sm">
                <option value="">Todos os anos</option>
                <?php $__currentLoopData = $anos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($ano); ?>" <?php echo e((string) ($filtros['ano'] ?? '') === (string) $ano ? 'selected' : ''); ?>><?php echo e($ano); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filtrar</button>
            <a href="<?php echo e(route('relatorios.consumo')); ?>" class="btn btn-sm btn-outline-secondary">Limpar</a>
        </form>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-sm mb-0" style="font-size:.82rem;">
            <thead class="thead-light">
                <tr>
                    <th rowspan="2" class="align-middle pl-3">Projeto</th>
                    <th rowspan="2" class="align-middle">Cliente</th>
                    <th rowspan="2" class="align-middle">Status</th>
                    <?php $__currentLoopData = $funcoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th colspan="3" class="text-center border-left"><?php echo e($nome); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <th colspan="3" class="text-center border-left bg-light font-weight-bold">Compilado</th>
                </tr>
                <tr>
                    <?php $__currentLoopData = $funcoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="text-right border-left" style="font-weight:600;">Consumida</th>
                        <th class="text-right" style="font-weight:600;">Estimada</th>
                        <th class="text-center" style="font-weight:600;">%</th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <th class="text-right border-left" style="font-weight:600;">Consumida</th>
                    <th class="text-right" style="font-weight:600;">Estimada</th>
                    <th class="text-center" style="font-weight:600;">%</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $linhas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $p = $linha['projeto'];
                        $r = $linha['resumo'];
                        $rowClass = $p->status === 'cancelado' ? 'text-muted' : ($p->status === 'em_andamento' ? 'table-warning' : '');
                    ?>
                    <tr class="<?php echo e($rowClass); ?>">
                        <td class="pl-3">
                            <a href="<?php echo e(route('relatorios.consumo.show', $p->id)); ?>" class="font-weight-bold consumo-link-projeto">
                                <?php echo e($p->nome); ?>

                            </a>
                        </td>
                        <td><?php echo e($p->cliente?->nome ?? '—'); ?></td>
                        <td><span class="badge badge-<?php echo e($p->status_cor); ?>"><?php echo e($p->status_label); ?></span></td>
                        <?php $__currentLoopData = $funcoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fid => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $f = $r['por_funcao'][$fid] ?? null; ?>
                            <td class="text-right border-left"><?php echo e($fmtHora($f['consumida'] ?? null)); ?></td>
                            <td class="text-right"><?php echo e($fmtHora($f['estimada'] ?? null)); ?></td>
                            <td class="text-center">
                                <?php if(($f['pct'] ?? null) !== null): ?>
                                    <span class="badge <?php echo e($bgPct($f['pct'])); ?>"><?php echo e($fmtPct($f['pct'])); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td class="text-right border-left font-weight-bold"><?php echo e($fmtHora($r['consumida'])); ?></td>
                        <td class="text-right font-weight-bold"><?php echo e($fmtHora($r['estimada'])); ?></td>
                        <td class="text-center">
                            <?php if($r['pct'] !== null): ?>
                                <span class="badge <?php echo e($bgPct($r['pct'])); ?>"><?php echo e($fmtPct($r['pct'])); ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="<?php echo e(3 + (count($funcoes) * 3) + 3); ?>" class="text-center text-muted py-4">
                            <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                            Nenhum projeto encontrado com os filtros aplicados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer text-muted" style="font-size:.78rem;">
        <i class="fas fa-info-circle mr-1"></i>
        Horas <strong>consumidas</strong> são os lançamentos reais da equipe (rastreador/timesheet);
        <strong>estimadas</strong> são as horas previstas por função, configuradas no detalhe de cada projeto.
        Semáforo: <span class="badge bg-success">&lt; 80%</span> saudável, <span class="badge bg-warning">≥ 80%</span> atenção, <span class="badge bg-danger">&gt; 100%</span> estouro.
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/relatorios/consumo.blade.php ENDPATH**/ ?>