<?php
    $danosCount = is_countable($danosVeiculo ?? null) ? count($danosVeiculo) : 0;
?>

<div class="card card-soft">
    <div class="card-header rev-table-header">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="rev-table-title">Danos do veículo</span>
               
            </div>
            <div class="rev-table-subtitle">
                <?php echo e($danosCount); ?> dano(s) identificado(s) nesta revisão.
            </div>
        </div>
        
    </div>

    <div class="card-body p-0">
        

        <div class="table-responsive">
            <table class="table soft-table rev-danos-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Dano</th>
                        <th>Momento</th>
                        <th>Decisão</th>
                        <th>Valor</th>
                        <th>Reparo</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $danosVeiculo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $valorReal = (float) ($dano['valor_exibido'] ?? $dano['valor_sugerido'] ?? 0);
                            $estado = !empty($dano['cobranca_pendente']) ? 'cobrar' : (($dano['status'] ?? '') === 'descartada' ? 'free' : (($dano['ja_cobrado'] ?? false) ? 'cobrado' : 'pending'));
                            $descricaoDano = $dano['descricao'] ?? 'Dano';
                            $origem = $dano['origem_label'] ?? ['label' => 'Outra reserva', 'class' => 'mini-chip--ok'];
                            $local = $dano['local'] ?? '-';
                            $statusReparo = $dano['model']->status_reparo ?? 'pendente';
                            $reparoLabel = match ($statusReparo) {
                                'em_os' => 'Em OS',
                                'em_correcao' => 'Em correção',
                                'corrigida' => 'Corrigida',
                                'nao_requer_reparo' => 'Não requer reparo',
                                default => 'Pendente',
                            };
                            $statusDanoLabel = match ($dano['status'] ?? '') {
                                'descartada' => 'Não cobrar',
                                'pendente' => 'Pendente',
                                default => 'Em revisão',
                            };
                            $decisaoLabel = match ($dano['model']->status_revisao ?? 'pendente') {
                                'cobrar_cliente' => 'Cobrar cliente',
                                'nao_cobrar' => 'Não cobrar',
                                'ja_existente' => 'Já existente',
                                'desgaste_normal' => 'Desgaste normal',
                                'possivel_sinistro' => 'Possível sinistro',
                                default => 'Pendente',
                            };
                        ?>
                        <tr class="js-dano-item" data-state="<?php echo e($estado); ?>" data-charge="<?php echo e(in_array($estado, ['cobrar', 'cobrado'], true) ? '1' : '0'); ?>" data-valor="<?php echo e($valorReal); ?>">
                            <td>
                                <?php echo e($dano['model']->id); ?>

                            </td>
                            <td>
                                <div class="font-weight-bold"><?php echo e($local); ?> - <?php echo e($descricaoDano); ?></div>
                                
                            </td>
                            <td>
                                <div>
                                    <span class="mini-chip <?php echo e($origem['class'] ?? 'mini-chip--info'); ?>"><?php echo e($origem['label'] ?? 'Outra reserva'); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="mini-chip <?php echo e($estado === 'cobrar' ? 'mini-chip--warn' : ($estado === 'free' ? 'mini-chip--ok' : 'mini-chip--info')); ?>">
                                    <?php echo e($decisaoLabel); ?>

                                </div>
                                
                            </td>
                            <td>
                                <?php if(($dano['model']->status_revisao ?? '') === 'cobrar_cliente'): ?>
                                    <?php if((float) ($dano['valor_cobranca'] ?? 0) > 0 && (float) ($dano['valor_cobranca'] ?? 0) !== (float) ($dano['valor_sugerido'] ?? 0)): ?>
                                        <div class="font-weight-bold h6 mb-1">
                                            Aprovado: R$ <?php echo e(number_format((float) ($dano['valor_cobranca'] ?? 0), 2, ',', '.')); ?>

                                        </div>
                                        <div class="small text-muted">
                                            Estimado: R$ <?php echo e(number_format((float) ($dano['valor_sugerido'] ?? 0), 2, ',', '.')); ?>

                                        </div>
                                    <?php else: ?>
                                        <div class="font-weight-bold h6 mb-0">R$ <?php echo e(number_format($valorReal, 2, ',', '.')); ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="font-weight-bold h6 mb-0">-</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="mini-chip <?php echo e($statusReparo === 'corrigida' ? 'mini-chip--ok' : ($statusReparo === 'nao_requer_reparo' ? 'mini-chip--info' : 'mini-chip--warn')); ?>">
                                    <?php echo e($reparoLabel); ?>

                                </span>
                                
                            </td>
                            <td class="text-right">
                                <div class="btn-group">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary js-open-detalhes-dano"
                                        data-dano-id="<?php echo e($dano['id']); ?>"
                                        data-dano-nome="<?php echo e(e($descricaoDano)); ?>"
                                        data-dano-local="<?php echo e(e($local)); ?>"
                                        data-dano-observacao="<?php echo e(e($dano['observacoes'] ?? '')); ?>"
                                        data-dano-valor="<?php echo e(number_format($valorReal, 2, '.', '')); ?>"
                                        data-dano-valor-aprovado="<?php echo e(number_format((float) ($dano['valor_cobranca'] ?? $valorReal), 2, '.', '')); ?>"
                                        data-dano-imagens='<?php echo json_encode($dano['imagens'] ?? [], 15, 512) ?>'
                                        data-dano-usar-calcao="<?php echo e(!empty($dano['usar_calcao']) ? '1' : '0'); ?>"
                                        data-dano-mode="<?php echo e($estado === 'free' ? 'free' : 'charge'); ?>"
                                    >
                                        Ver detalhes
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="sr-only">Abrir menu</span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <button type="button" class="dropdown-item js-open-cobrar-dano"
                                            data-toggle="modal"
                                            data-target="#modalCobrarDano"
                                            data-dano-id="<?php echo e($dano['id']); ?>"
                                            data-dano-nome="<?php echo e(e($descricaoDano)); ?>"
                                            data-dano-local="<?php echo e(e($local)); ?>"
                                            data-dano-observacao="<?php echo e(e($dano['observacoes'] ?? '')); ?>"
                                            data-dano-valor="<?php echo e(number_format($valorReal, 2, '.', '')); ?>"
                                            data-dano-valor-aprovado="<?php echo e(number_format((float) ($dano['valor_cobranca'] ?? $valorReal), 2, '.', '')); ?>"
                                            data-dano-imagens='<?php echo json_encode($dano['imagens'] ?? [], 15, 512) ?>'
                                            data-dano-usar-calcao="<?php echo e(!empty($dano['usar_calcao']) ? '1' : '0'); ?>"
                                            data-dano-mode="charge"
                                        >Alterar decisão</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhum dano encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/revisao_checkout_danos.blade.php ENDPATH**/ ?>