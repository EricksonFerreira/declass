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
    
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }
    
    .project-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
    }
    
    .kpi-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
    }
    
    .version-row {
        transition: all 0.2s ease;
    }
    .version-row:hover {
        background-color: #f8fafc;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<!-- Cabeçalho de Detalhes Gerais -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card project-card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="badge text-white mr-3 shadow-sm" style="background-color: <?php echo e($projeto->cor ?? '#6c757d'); ?>; width: 35px; height: 35px; display: inline-block; border-radius: 50%; border: 2px solid #fff;">&nbsp;</span>
                        <div>
                            <h2 class="font-weight-bold text-dark mb-1" style="letter-spacing: -0.02em;"><?php echo e($projeto->nome); ?></h2>
                            <p class="text-muted mb-0">
                                <i class="fas fa-user-tie text-secondary mr-1"></i> Cliente: <strong><?php echo e($projeto->cliente->nome ?? 'Sem Cliente'); ?></strong>
                                <span class="mx-2 text-muted">|</span>
                                <i class="fas fa-lock<?php echo e($projeto->publico ? '-open' : ''); ?> text-secondary mr-1"></i> <?php echo e($projeto->publico ? 'Público' : 'Privado'); ?>

                                <span class="mx-2 text-muted">|</span>
                                <span class="badge badge-<?php echo e($projeto->ativo ? 'success' : 'danger'); ?> px-2 py-1 text-xs">
                                    <?php echo e($projeto->ativo ? 'Ativo' : 'Inativo'); ?>

                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center flex-wrap">
                        <a href="<?php echo e(route('clockify.projetos.index')); ?>" class="btn btn-default font-weight-bold text-secondary mr-2 mb-2 shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Voltar
                        </a>
                        <button type="button" class="btn btn-warning font-weight-bold text-dark mr-2 mb-2 shadow-sm" data-toggle="modal" data-target="#modal-edit-projeto">
                            <i class="fas fa-edit mr-1"></i> Editar Projeto
                        </button>
                        <form action="<?php echo e(route('clockify.projetos.destroy', $projeto->id)); ?>" method="POST" class="d-inline mr-2 mb-2" onsubmit="return confirm('Deseja realmente excluir este projeto e todas as suas versões de planejamento?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger font-weight-bold shadow-sm">
                                <i class="fas fa-trash mr-1"></i> Excluir Projeto
                            </button>
                        </form>
                        <a href="<?php echo e(route('clockify.projetos.planejar-equipe', $projeto->id)); ?>" class="btn btn-info font-weight-bold text-white mr-2 mb-2 shadow-sm">
                            <i class="fas fa-users mr-1"></i> Planejar Equipe
                        </a>
                        <a href="<?php echo e(route('relatorios.consumo.show', $projeto->id)); ?>" class="btn btn-secondary font-weight-bold text-white mr-2 mb-2 shadow-sm">
                            <i class="fas fa-chart-bar mr-1"></i> Consumo × Estimado
                        </a>
                        <a href="<?php echo e(route('clockify.projetos.cronogramas.novo', $projeto->id)); ?>" class="btn btn-primary font-weight-bold mb-2 shadow-sm">
                            <i class="fas fa-plus mr-1"></i> Nova Versão/Etapa
                        </a>
                    </div>
                </div>
                <?php if($projeto->descricao): ?>
                    <div class="mt-4 border-top pt-3 text-sm text-secondary">
                        <span class="text-bold text-dark d-block mb-1">Descrição do Projeto:</span>
                        <?php echo e($projeto->descricao); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php
    $horasTrabalhadas   = $projeto->total_horas_trabalhadas ?? 0;
    $custoReal          = $projeto->custo_real ?? 0;
    $faturadoTotal      = $projeto->valor_faturado ?? 0;
    $rentabilidade      = $projeto->rentabilidade ?? 0;

    // Soma das etapas
    $horasPlanejadasTotal = $projeto->cronogramas->sum('horas_planejadas');
    $custoEstimadoEtapas  = $projeto->cronogramas->sum('custo_planejado');
    $somaFaturadoEtapas   = $projeto->cronogramas->sum('valor_faturado');

    // % da meta de horas planejadas
    $pctMeta = $horasPlanejadasTotal > 0
        ? round(($horasTrabalhadas / $horasPlanejadasTotal) * 100, 1)
        : 0;

    // Projeção: se mantivermos esse ritmo, o custo final projetado é:
    $custoProjetado = ($pctMeta > 0 && $pctMeta < 100)
        ? round(($custoReal / ($pctMeta / 100)), 2)
        : $custoReal;
    $projecaoCorLabel = $custoProjetado > $faturadoTotal ? 'text-danger' : 'text-success';
    $projecaoIcone    = $custoProjetado > $faturadoTotal ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';

    // Alerta principal
    $divergencia      = $faturadoTotal - $somaFaturadoEtapas;
    // Só é divergência real se as etapas têm valor preenchido E a soma não bate
    $temDivergencia   = $somaFaturadoEtapas > 0 && abs($divergencia) >= 0.01;
    $faltaMeta        = $horasPlanejadasTotal - $horasTrabalhadas;
    $margemPerc       = $faturadoTotal > 0 ? round(($rentabilidade / $faturadoTotal) * 100, 1) : 0;

    if ($temDivergencia) {
        $alertaTitulo = 'Divergência de faturamento';
        $alertaMsg    = 'Etapas somam R$ ' . number_format($somaFaturadoEtapas, 2, ',', '.') . ' · Diferença: R$ ' . number_format($divergencia, 2, ',', '.');
        $alertaIcon   = 'fa-exclamation-triangle';
        $alertaCor    = 'danger';
    } elseif ($rentabilidade < 0) {
        $alertaTitulo = 'Projeto no vermelho';
        $alertaMsg    = 'Custo real (R$ ' . number_format($custoReal, 2, ',', '.') . ') supera o faturado.';
        $alertaIcon   = 'fa-fire';
        $alertaCor    = 'danger';
    } elseif ($margemPerc < 20 && $faturadoTotal > 0) {
        $alertaTitulo = 'Margem baixa (' . $margemPerc . '%)';
        $alertaMsg    = 'Faturado: R$ ' . number_format($faturadoTotal, 2, ',', '.') . ' · Rentab.: R$ ' . number_format($rentabilidade, 2, ',', '.');
        $alertaIcon   = 'fa-triangle-exclamation';
        $alertaCor    = 'warning';
    } else {
        $alertaTitulo = 'Sem alertas críticos';
        $alertaMsg    = 'Projeto dentro dos parâmetros esperados.';
        $alertaIcon   = 'fa-circle-check';
        $alertaCor    = 'success';
    }
?>


<div class="row mb-3">

    
    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card kpi-card bg-white p-3 h-100">
            <div class="text-xs text-bold text-muted text-uppercase mb-2">
                <i class="fas fa-file-invoice-dollar text-success mr-1"></i> Resultado Financeiro
            </div>
            <div class="h3 font-weight-bold text-dark mb-0">R$ <?php echo e(number_format($faturadoTotal, 2, ',', '.')); ?></div>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="text-xs text-muted">Custo real</span>
                <span class="text-xs font-weight-bold text-dark">R$ <?php echo e(number_format($custoReal, 2, ',', '.')); ?></span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
                <span class="text-xs text-muted">Rentabilidade</span>
                <span class="text-xs font-weight-bold <?php echo e($rentabilidade >= 0 ? 'text-success' : 'text-danger'); ?>">
                    <i class="fas fa-<?php echo e($rentabilidade >= 0 ? 'arrow-up' : 'arrow-down'); ?> mr-1"></i>R$ <?php echo e(number_format($rentabilidade, 2, ',', '.')); ?>

                </span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
                <span class="text-xs text-muted">Margem</span>
                <span class="badge badge-<?php echo e($margemPerc >= 20 ? 'success' : ($margemPerc > 0 ? 'warning' : 'danger')); ?> text-xs">
                    <?php echo e(number_format($margemPerc, 1, ',', '.')); ?>%
                </span>
            </div>
        </div>
    </div>

    
    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card kpi-card bg-white p-3 h-100">
            <div class="text-xs text-bold text-muted text-uppercase mb-2">
                <i class="fas fa-clock text-primary mr-1"></i> Horas do Projeto
            </div>
            <div class="h3 font-weight-bold <?php echo e($pctMeta > 100 ? 'text-danger' : 'text-dark'); ?> mb-0">
                <?php echo e(number_format($horasTrabalhadas, 1, ',', '.')); ?>h
                <small class="text-muted h6">realizadas</small>
            </div>
            <div class="progress my-2" style="height:8px; border-radius:6px;">
                <div class="progress-bar <?php echo e($pctMeta > 100 ? 'bg-danger' : ($pctMeta >= 80 ? 'bg-warning' : 'bg-success')); ?>"
                     style="width: <?php echo e(min($pctMeta, 100)); ?>%" role="progressbar"></div>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-xs text-muted">Planejadas</span>
                <span class="text-xs font-weight-bold"><?php echo e(number_format($horasPlanejadasTotal, 1, ',', '.')); ?>h</span>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <span class="text-xs text-muted">Consumo</span>
                <span class="text-xs font-weight-bold <?php echo e($pctMeta > 100 ? 'text-danger' : 'text-success'); ?>"><?php echo e(number_format($pctMeta, 1, ',', '.')); ?>%</span>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <span class="text-xs text-muted"><?php if($faltaMeta > 0): ?>Faltam <?php else: ?> Excedente <?php endif; ?></span>
                <span class="text-xs font-weight-bold <?php echo e($faltaMeta < 0 ? 'text-danger' : ''); ?>"><?php echo e(number_format(abs($faltaMeta), 1, ',', '.')); ?>h</span>
            </div>
        </div>
    </div>

    
    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card kpi-card bg-white p-3 h-100">
            <div class="text-xs text-bold text-muted text-uppercase mb-2">
                <i class="fas fa-chart-line text-info mr-1"></i> Projeção de Fechamento
            </div>
            <?php if($pctMeta > 0): ?>
                <div class="h3 font-weight-bold <?php echo e($projecaoCorLabel); ?> mb-0">
                    R$ <?php echo e(number_format($custoProjetado, 2, ',', '.')); ?>

                </div>
                <div class="d-flex justify-content-between mt-2">
                    <span class="text-xs text-muted">Faturado</span>
                    <span class="text-xs font-weight-bold">R$ <?php echo e(number_format($faturadoTotal, 2, ',', '.')); ?></span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="text-xs text-muted">Diferença projetada</span>
                    <span class="text-xs font-weight-bold <?php echo e($projecaoCorLabel); ?>">
                        R$ <?php echo e(number_format(abs($custoProjetado - $faturadoTotal), 2, ',', '.')); ?>

                        <?php echo e($custoProjetado > $faturadoTotal ? '▲' : '▼'); ?>

                    </span>
                </div>
                <div class="mt-2">
                    <span class="badge badge-<?php echo e($custoProjetado > $faturadoTotal ? 'danger' : 'success'); ?> text-xs">
                        <i class="fas <?php echo e($projecaoIcone); ?> mr-1"></i>
                        <?php echo e($custoProjetado > $faturadoTotal ? 'Risco de estouro' : 'Dentro do orçamento'); ?>

                    </span>
                </div>
            <?php else: ?>
                <div class="h3 font-weight-bold text-muted mb-0">—</div>
                <small class="text-muted">Sem horas registradas ainda</small>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="col-lg-3 col-sm-6 mb-3">
        <div class="card kpi-card p-3 h-100
            <?php echo e($alertaCor === 'danger' ? 'bg-danger text-white' : ($alertaCor === 'warning' ? 'bg-warning' : 'bg-white')); ?>">
            <div class="text-xs text-bold text-uppercase mb-2 <?php echo e($alertaCor === 'danger' ? 'text-white' : 'text-muted'); ?>">
                <i class="fas fa-<?php echo e($alertaIcon); ?> mr-1"></i> Principal Alerta
            </div>
            <div class="h5 font-weight-bold <?php echo e($alertaCor === 'danger' ? 'text-white' : 'text-dark'); ?> mb-2">
                <?php echo e($alertaTitulo); ?>

            </div>
            <small class="<?php echo e($alertaCor === 'danger' ? 'text-white' : 'text-muted'); ?>"><?php echo e($alertaMsg); ?></small>
        </div>
    </div>

</div>


<?php if($projeto->cronogramas->count() > 0): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card project-card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h3 class="card-title text-bold text-dark mb-0">
                    <i class="fas fa-chart-bar text-info mr-2"></i> Dispersão de Horas por Etapa
                    <small class="text-muted font-weight-normal ml-2">— planejado vs. orçado por etapa</small>
                </h3>
            </div>
            <div class="card-body pb-2">
                <?php
                    $maxHorasEtapa = $projeto->cronogramas->max(fn($c) => max($c->horas_planejadas, $c->horas_orcadas));
                    $maxHorasEtapa = max($maxHorasEtapa, 1);
                ?>
                <?php $__currentLoopData = $projeto->cronogramas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cron): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $pctPlan  = $maxHorasEtapa > 0 ? round(($cron->horas_planejadas / $maxHorasEtapa) * 100) : 0;
                        $pctOrc   = $maxHorasEtapa > 0 ? round(($cron->horas_orcadas / $maxHorasEtapa) * 100) : 0;
                        $desvio   = $cron->horas_planejadas - $cron->horas_orcadas;
                        $desvioPerc = $cron->horas_orcadas > 0
                            ? round(($cron->horas_planejadas / $cron->horas_orcadas) * 100)
                            : 0;
                        // Verde=dentro, Amarelo=próximo, Vermelho=excedeu
                        $corBarra = $desvioPerc > 100 ? 'bg-danger'
                            : ($desvioPerc >= 80  ? 'bg-warning'
                            : 'bg-success');
                        $corDesvio = $desvio > 0 ? 'text-danger' : ($desvio < 0 ? 'text-success' : 'text-muted');
                        $statusEtapa = $cron->status == 'valido' ? 'ATIVO' : strtoupper($cron->status);
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <span class="font-weight-bold text-dark text-sm"><?php echo e($cron->nome); ?></span>
                                <span class="badge badge-<?php echo e($cron->status == 'valido' ? 'success' : ($cron->status == 'rascunho' ? 'warning' : 'secondary')); ?> ml-1 text-xs"><?php echo e($statusEtapa); ?></span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-muted">Planejadas: </span>
                                <span class="text-xs font-weight-bold"><?php echo e(number_format($cron->horas_planejadas, 1, ',', '.')); ?>h</span>
                                <span class="mx-1 text-muted">·</span>
                                <span class="text-xs text-muted">Orçadas: </span>
                                <span class="text-xs font-weight-bold"><?php echo e(number_format($cron->horas_orcadas, 1, ',', '.')); ?>h</span>
                                <span class="ml-2 text-xs font-weight-bold <?php echo e($corDesvio); ?>">
                                    <?php if($desvio > 0): ?> +<?php echo e(number_format($desvio, 1, ',', '.')); ?>h ▲
                                    <?php elseif($desvio < 0): ?> <?php echo e(number_format($desvio, 1, ',', '.')); ?>h ▼
                                    <?php else: ?> ✓ Equilibrado
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="progress mb-1" style="height:10px; border-radius:6px; background:#edf2f7;">
                            <div class="progress-bar <?php echo e($corBarra); ?>" role="progressbar"
                                 style="width:<?php echo e(min($pctPlan, 100)); ?>%; transition: width 0.6s ease;"
                                 title="Planejadas: <?php echo e(number_format($cron->horas_planejadas, 1, ',', '.')); ?>h">
                            </div>
                            <?php if($pctOrc > 0 && $pctOrc <= 100): ?>
                            <div class="position-absolute" style="left:<?php echo e($pctOrc); ?>%; top:0; height:10px; width:2px; background:rgba(0,0,0,0.4); border-radius:2px;"
                                 title="Orçadas: <?php echo e(number_format($cron->horas_orcadas, 1, ',', '.')); ?>h"></div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">0h</small>
                            <small class="text-muted"><?php echo e(number_format($maxHorasEtapa, 0, ',', '.')); ?>h (máx)</small>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="d-flex align-items-center mt-2 pt-2 border-top">
                    <span class="d-inline-block mr-2" style="width:12px;height:12px;background:#28a745;border-radius:3px;"></span><small class="text-muted mr-3">Dentro do orçado</small>
                    <span class="d-inline-block mr-2" style="width:12px;height:12px;background:#ffc107;border-radius:3px;"></span><small class="text-muted mr-3">Próximo do limite (80%+)</small>
                    <span class="d-inline-block mr-2" style="width:12px;height:12px;background:#dc3545;border-radius:3px;"></span><small class="text-muted">Acima do orçado</small>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- Listagem de Versões de Planejamento -->
<div class="row">
    <div class="col-12">
        <div class="card project-card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h3 class="card-title text-bold text-dark mb-0">
                    <i class="fas fa-code-branch text-primary mr-2"></i> Versões de Planejamento e Cronogramas
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr class="text-xs text-uppercase">
                                <th class="pl-4">Nome da Versão / Etapa</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Período Planejado</th>
                                <th class="text-right">Horas Orçadas</th>
                                <th class="text-right">Horas Planejadas</th>
                                <th class="text-right">Preço da Hora</th>
                                <th class="text-right">Custo Estimado</th>
                                <th class="text-right">Faturado (Etapa)</th>
                                <th class="text-center" style="width: 200px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $projeto->cronogramas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cron): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="version-row">
                                    <td class="pl-4 align-middle font-weight-bold text-dark">
                                        <i class="far fa-folder mr-1 text-secondary"></i> <?php echo e($cron->nome); ?>

                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-<?php echo e($cron->status == 'valido' ? 'success' : ($cron->status == 'rascunho' ? 'warning' : 'secondary')); ?> px-2 py-1">
                                            <?php echo e($cron->status == 'valido' ? 'VÁLIDO / ATIVO' : strtoupper($cron->status)); ?>

                                        </span>
                                    </td>
                                    <td class="align-middle text-center text-sm font-weight-bold text-secondary">
                                        <?php if($cron->data_inicio && $cron->data_fim): ?>
                                            <i class="far fa-calendar-alt mr-1"></i> <?php echo e($cron->data_inicio->format('d/m/Y')); ?> a <?php echo e($cron->data_fim->format('d/m/Y')); ?>

                                            <br><small class="text-muted">(<?php echo e($cron->dias_uteis_planejamento); ?> dias úteis)</small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-sm text-secondary">
                                        <?php echo e(number_format($cron->horas_orcadas, 1, ',', '.')); ?> h
                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-sm text-dark">
                                        <?php echo e(number_format($cron->horas_planejadas, 1, ',', '.')); ?> h
                                    </td>
                                    <td class="align-middle text-right text-sm text-secondary">
                                        R$ <?php echo e(number_format($cron->valor_hora, 2, ',', '.')); ?>

                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-sm text-success">
                                        R$ <?php echo e(number_format($cron->custo_planejado, 2, ',', '.')); ?>

                                    </td>
                                    <td class="align-middle text-right font-weight-bold text-sm text-primary">
                                        R$ <?php echo e(number_format($cron->valor_faturado ?? 0.00, 2, ',', '.')); ?>

                                    </td>
                                    <td class="align-middle text-center">
                                        <!-- Editar abre o planejador com a versão correspondente -->
                                        <a href="<?php echo e(route('clockify.projetos.planejamento', [$projeto->id, 'cronograma_id' => $cron->id])); ?>" class="btn btn-xs btn-info font-weight-bold mr-1">
                                            <i class="fas fa-edit mr-1"></i> Editar
                                        </a>
                                        
                                        <!-- Excluir Versão -->
                                        <form action="<?php echo e(route('clockify.projetos.cronogramas.destroy', $cron->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta versão de planejamento? Esta ação é irreversível.')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-project-diagram fa-3x mb-3 text-muted"></i>
                                        <h5 class="font-weight-bold">Nenhum planejamento criado ainda</h5>
                                        <p class="text-sm text-muted">Clique no botão "Nova Versão/Etapa" acima para começar a programar a entrega deste projeto.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Projeto -->
<div class="modal fade" id="modal-edit-projeto" tabindex="-1" role="dialog" aria-labelledby="modalEditProjetoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold text-dark" id="modalEditProjetoLabel">Editar Projeto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo e(route('clockify.projetos.update', $projeto->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="text-sm">Nome do Projeto <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control form-control-sm" required value="<?php echo e($projeto->nome); ?>">
                    </div>
                    <div class="form-group">
                        <label class="text-sm">Cliente</label>
                        <select name="clockify_cliente_id" class="form-control form-control-sm select2">
                            <option value="">Nenhum Cliente</option>
                            <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cliente->id); ?>" <?php if($projeto->clockify_cliente_id == $cliente->id): echo 'selected'; endif; ?>><?php echo e($cliente->nome); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="text-sm">Descrição</label>
                        <textarea name="descricao" rows="2" class="form-control form-control-sm" placeholder="Resumo do projeto..."><?php echo e($projeto->descricao); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="text-sm">Valor Faturado (Total Real)</label>
                        <input type="text" name="valor_faturado" class="form-control form-control-sm currency-mask" value="<?php echo e(number_format($projeto->valor_faturado, 2, ',', '.')); ?>" placeholder="Ex: 15.000,00" />
                    </div>
                    <div class="form-group">
                        <label class="text-sm">Cor do Projeto</label>
                        <input type="color" name="cor" class="form-control form-control-sm" style="padding: 2px; height: 31px;" value="<?php echo e($projeto->cor ?? '#0284c7'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="text-sm">Visibilidade</label>
                        <select name="publico" class="form-control form-control-sm">
                            <option value="1" <?php if($projeto->publico): echo 'selected'; endif; ?>>Público</option>
                            <option value="0" <?php if(!$projeto->publico): echo 'selected'; endif; ?>>Privado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="text-sm">Status</label>
                        <select name="ativo" class="form-control form-control-sm">
                            <option value="1" <?php if($projeto->ativo): echo 'selected'; endif; ?>>Ativo</option>
                            <option value="0" <?php if(!$projeto->ativo): echo 'selected'; endif; ?>>Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm font-weight-bold text-dark">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('js'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
<script>
$(document).ready(function(){
  $('.currency-mask').inputmask('currency', {
    prefix: 'R$ ',
    groupSeparator: '.',
    radixPoint: ',',
    autoGroup: true,
    digits: 2,
    rightAlign: false,
    removeMaskOnSubmit: true
  });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\clockify\projetos_show.blade.php ENDPATH**/ ?>