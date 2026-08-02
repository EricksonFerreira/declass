<div class="vs-panel" data-step-panel="documentacao">
    <div class="vs-panel-head">
        <div>
            <span class="vs-panel-head__eyebrow">Etapa 08</span>
            <h4 class="vs-panel-head__title">Documentação</h4>
            <p class="vs-panel-head__sub">IPVA, licenciamento e seguros organizados por tipo, com meses, vencimentos e status.</p>
        </div>
        <div class="vs-kpis">
            <div class="vs-kpi">
                <span>Tipos cadastrados</span>
                <strong><?php echo e($veiculo->documentacoesVeiculo->pluck('tipo')->unique()->count()); ?></strong>
            </div>
            <div class="vs-kpi">
                <span>Parcelas abertas</span>
                <strong><?php echo e($veiculo->documentacoesVeiculo->flatMap->parcelas->where('status', 'pendente')->count()); ?></strong>
            </div>
            <div class="vs-kpi">
                <span>Documentos cadastrados</span>
                <strong><?php echo e($veiculo->documentacoesVeiculo->count()); ?></strong>
            </div>
        </div>
    </div>

    <div class="vt-panel-actions">
        <a href="<?php echo e(route('veiculo.documentacao')); ?>" target="_blank" class="btn-xp btn-xp--sm">
            <i class="fas fa-file-alt mr-1"></i>Abrir documentação
        </a>
        <a href="<?php echo e(route('veiculo.documentacao.create', ['veiculo_id' => $veiculo->id])); ?>" target="_blank" class="btn-xp btn-xp--sm btn-xp--outline">
            <i class="fas fa-file-alt mr-1"></i>Adicionar documentação
        </a>
    </div>

    <?php
        $tipos = [
            'ipva' => 'IPVA',
            'licenciamento' => 'Licenciamento',
            'seguro' => 'Seguros',
        ];
        $documentacoesPorTipo = $veiculo->documentacoesVeiculo->groupBy('tipo');
    ?>

    <div class="table-responsive">
        <table class="table table-hover xp-table">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Itens</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo => $rotulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $documentos = $documentacoesPorTipo->get($tipo, collect()); ?>
                    <tr>
                        <td class="align-top font-weight-bold"><?php echo e($rotulo); ?></td>
                        <td>
                            <?php $__empty_1 = true; $__currentLoopData = $documentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="mb-3 border rounded p-2">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <div class="font-weight-bold"><?php echo e($doc->descricao ?: $rotulo); ?></div>
                                            <div class="text-muted text-xs">
                                                <?php echo e($doc->parcelas_total); ?> parcela(s) | Valor da parcela: R$ <?php echo e(number_format((float) $doc->valor_parcela, 2, ',', '.')); ?>

                                            </div>
                                        </div>
                                        <span class="badge badge-<?php echo e($doc->status === 'ativo' ? 'success' : 'secondary'); ?>"><?php echo e(ucfirst($doc->status)); ?></span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Mês</th>
                                                    <th>Vencimento</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__currentLoopData = $doc->parcelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parcela): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e(optional($parcela->data_vencimento)->format('m/Y') ?? '-'); ?></td>
                                                        <td><?php echo e(optional($parcela->data_vencimento)->format('d/m/Y') ?? '-'); ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php echo e($parcela->status === 'pago' ? 'success' : ($parcela->status === 'atrasado' ? 'danger' : 'warning')); ?>">
                                                                <?php echo e(ucfirst($parcela->status)); ?>

                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="text-muted">Nenhum item cadastrado.</span>
                            <?php endif; ?>
                        </td>
                        <td class="align-top">
                            <?php if($documentos->isNotEmpty()): ?>
                                <span class="badge badge-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Sem itens</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/veiculo/components/operacional/etapas/documentacao.blade.php ENDPATH**/ ?>