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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $fmtHora  = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.').'h';
    $fmtPct   = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.').'%';
    $fmtMoney = fn ($v) => $v === null ? '—' : 'R$ '.number_format($v, 2, ',', '.');
    $corPct   = fn ($v) => $v === null ? 'secondary' : ($v > 100 ? 'danger' : 'success');
    $maxHora  = max(collect($linhasFuncao)->max(fn ($l) => max($l['consumida'], $l['estimada'] ?? 0)), 1);
?>

<?php if(session('success')): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> <?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($e); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>


<div class="card">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between" style="gap:1rem;">
        <div>
            <h4 class="mb-1 font-weight-bold"><?php echo e($projeto->nome); ?></h4>
            <div class="text-muted" style="font-size:.85rem;">
                <?php echo e($projeto->cliente?->nome ?? 'Sem cliente'); ?>

                <span class="mx-2">·</span>
                <span class="badge badge-<?php echo e($projeto->status_cor); ?>"><?php echo e($projeto->status_label); ?></span>
            </div>
        </div>
        <div class="d-flex align-items-center" style="gap:.5rem;">
            <form method="POST" action="<?php echo e(route('relatorios.consumo.status', $projeto->id)); ?>" class="d-flex align-items-center" style="gap:.4rem;">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <select name="status" class="form-control form-control-sm">
                    <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php echo e($projeto->status === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">Atualizar status</button>
            </form>
            <a href="<?php echo e(route('clockify.projetos.show', $projeto->id)); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-project-diagram mr-1"></i> Ver projeto
            </a>
        </div>
    </div>
</div>


<?php if($previsao): ?>
    <?php if($previsao['estourado']): ?>
        <div class="alert alert-danger d-flex align-items-center" style="gap:.6rem;">
            <i class="fas fa-exclamation-triangle fa-lg"></i>
            <div>
                <strong>Estimativa esgotada.</strong>
                O projeto já consumiu <?php echo e($fmtHora($totais['consumida'])); ?> de <?php echo e($fmtHora($totais['estimada'])); ?> estimadas
                (<?php echo e($fmtPct($totais['pct'])); ?>). Novas horas aumentam o estouro.
            </div>
        </div>
    <?php elseif(($totais['pct'] ?? 0) >= 80 || ($previsao['dias'] ?? 99) <= 10): ?>
        <div class="alert alert-warning d-flex align-items-center" style="gap:.6rem;">
            <i class="fas fa-exclamation-triangle fa-lg"></i>
            <div>
                <strong>Atenção: <?php echo e($fmtPct($totais['pct'])); ?> da estimativa consumida.</strong>
                Na velocidade atual (~<?php echo e(number_format($previsao['media_diaria'], 1, ',', '.')); ?>h/dia),
                a estimativa esgota em <strong>~<?php echo e($previsao['dias']); ?> <?php echo e($previsao['dias'] === 1 ? 'dia' : 'dias'); ?></strong>.
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>


<div class="row mb-3">
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">HH Consumida</div>
            <div class="h4 font-weight-bold mb-0"><?php echo e($fmtHora($totais['consumida'])); ?></div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">HH Estimada</div>
            <div class="h4 font-weight-bold mb-0"><?php echo e($totais['estimada'] > 0 ? $fmtHora($totais['estimada']) : '—'); ?></div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">% Consumido</div>
            <div class="h4 font-weight-bold mb-0">
                <?php if($totais['pct'] !== null): ?>
                    <span class="badge badge-<?php echo e($corPct($totais['pct'])); ?> p-2"><?php echo e($fmtPct($totais['pct'])); ?></span>
                <?php else: ?>
                    <span class="text-muted">—</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Custo consumido</div>
            <div class="h4 font-weight-bold mb-0"><?php echo e($totais['custo'] > 0 ? $fmtMoney($totais['custo']) : '—'); ?></div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Valor do contrato</div>
            <div class="h4 font-weight-bold mb-0"><?php echo e($fmtMoney($projeto->valor_faturado)); ?></div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Efetividade</div>
            <div class="h4 font-weight-bold mb-0 <?php echo e(($totais['efetividade'] ?? 0) < 0 ? 'text-danger' : 'text-success'); ?>">
                <?php echo e($totais['efetividade'] !== null ? $fmtMoney($totais['efetividade']) : '—'); ?>

            </div>
        </div>
    </div>
</div>

<div class="row">
    
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Consumo por função</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3">Função</th>
                            <th class="text-right">HH Consumida</th>
                            <th class="text-right">HH Estimada</th>
                            <th class="text-center">% Consumido</th>
                            <th class="text-center pr-3" style="width:90px;">Estimativa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $linhasFuncao; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $estimativa = $projeto->estimativas->firstWhere('funcao_id', $l['funcao_id']); ?>
                            <tr>
                                <td class="pl-3 font-weight-bold"><?php echo e($l['nome']); ?></td>
                                <td class="text-right"><?php echo e($fmtHora($l['consumida'])); ?></td>
                                <td class="text-right"><?php echo e($fmtHora($l['estimada'])); ?></td>
                                <td class="text-center">
                                    <?php if($l['pct'] !== null): ?>
                                        <span class="badge badge-<?php echo e($corPct($l['pct'])); ?>"><?php echo e($fmtPct($l['pct'])); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pr-3">
                                    <?php if($estimativa): ?>
                                        <form method="POST" action="<?php echo e(route('relatorios.consumo.estimativas.destroy', $estimativa->id)); ?>"
                                              onsubmit="return confirm('Remover a estimativa de <?php echo e($l['nome']); ?>?')">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Remover estimativa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma hora registrada neste projeto ainda.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="card-body border-top">
                <div class="text-xs text-muted text-uppercase font-weight-bold mb-3">
                    <span class="mr-3"><i class="fas fa-square" style="color:#f59e0b;"></i> Consumida</span>
                    <span><i class="fas fa-square" style="color:#60a5fa;"></i> Estimada</span>
                </div>
                <?php $__currentLoopData = $linhasFuncao; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-3">
                        <div class="text-xs font-weight-bold mb-1"><?php echo e($l['nome']); ?></div>
                        <div class="d-flex align-items-center mb-1" style="gap:.5rem;">
                            <div style="height:14px;width:<?php echo e(round(($l['consumida'] / $maxHora) * 100)); ?>%;background:#f59e0b;border-radius:3px;min-width:2px;"></div>
                            <span class="text-xs"><?php echo e($fmtHora($l['consumida'])); ?></span>
                        </div>
                        <div class="d-flex align-items-center" style="gap:.5rem;">
                            <div style="height:14px;width:<?php echo e(round((($l['estimada'] ?? 0) / $maxHora) * 100)); ?>%;background:#60a5fa;border-radius:3px;min-width:2px;"></div>
                            <span class="text-xs text-muted"><?php echo e($fmtHora($l['estimada'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="card-footer">
                <form method="POST" action="<?php echo e(route('relatorios.consumo.estimativas.store', $projeto->id)); ?>" class="form-inline flex-wrap" style="gap:.5rem;">
                    <?php echo csrf_field(); ?>
                    <select name="funcao_id" class="form-control form-control-sm" style="width:180px;">
                        <option value="">— Nova função —</option>
                        <?php $__currentLoopData = $funcoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($id); ?>"><?php echo e($nome); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="text" name="nova_funcao" class="form-control form-control-sm" placeholder="Ou digite a função" style="width:160px;">
                    <input type="number" name="horas_estimadas" class="form-control form-control-sm" placeholder="Horas" step="0.01" min="0.01" style="width:110px;" required>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Adicionar estimativa</button>
                </form>
                <small class="text-muted d-block mt-2">
                    Se a função já existir no projeto, o valor é atualizado. Funções novas ficam disponíveis para todos os projetos.
                </small>
            </div>
        </div>
    </div>

    
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-1"></i> Horas por colaborador</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3">Colaborador</th>
                            <th>Função</th>
                            <th class="text-right">HH</th>
                            <th class="text-right">Custo/h</th>
                            <th class="text-right pr-3">Custo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $membros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="pl-3">
                                    <div class="font-weight-bold"><?php echo e($m->nome); ?></div>
                                    <small class="text-muted"><?php echo e($m->email); ?></small>
                                </td>
                                <td><?php echo e($m->funcao_nome); ?></td>
                                <td class="text-right"><?php echo e($fmtHora($m->horas)); ?></td>
                                <td class="text-right"><?php echo e($m->custo_hora > 0 ? $fmtMoney($m->custo_hora) : '—'); ?></td>
                                <td class="text-right pr-3"><?php echo e($m->custo > 0 ? $fmtMoney($m->custo) : '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Nenhum lançamento de horas neste projeto.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($membros->isNotEmpty() && $membros->sum('custo') <= 0): ?>
                <div class="card-footer text-muted" style="font-size:.78rem;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Defina o custo/hora de cada colaborador no cadastro de usuários para medir a efetividade do contrato.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\relatorios\consumo_show.blade.php ENDPATH**/ ?>