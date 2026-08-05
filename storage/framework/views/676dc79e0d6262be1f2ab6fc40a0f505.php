<?php
    $norm = fn ($value) => strtolower(trim((string) $value));
    $infoPagamento = fn ($transacao) => is_array($transacao->info_pagamento ?? null) ? $transacao->info_pagamento : [];

    $isCaucao = function ($transacao) use ($norm, $infoPagamento) {
        return $norm($transacao->metodo_pagamento ?? '') === 'caucao'
            || $norm(data_get($infoPagamento($transacao), 'tipo')) === 'calcao'
            || str_contains($norm(data_get($infoPagamento($transacao), 'acao')), 'caucao');
    };

    $isDevolucaoCaucao = function ($transacao) use ($norm, $isCaucao) {
        if (! $isCaucao($transacao)) {
            return false;
        }

        $status = $norm($transacao->status ?? '');

        return (float) ($transacao->valor ?? 0) < 0
            || in_array($status, ['refunded', 'refund', 'reembolsado', 'cancelled', 'canceled', 'cancelado', 'cancelada', 'devolvido'], true);
    };

    $isRecebimentoCaucao = fn ($transacao) => $isCaucao($transacao) && ! $isDevolucaoCaucao($transacao);
    $isPagamentoReserva = fn ($transacao) => ! $isCaucao($transacao) && (float) ($transacao->valor ?? 0) > 0;

    $metodoLabel = function ($transacao) use ($isCaucao, $isDevolucaoCaucao, $infoPagamento, $norm) {
        if ($isCaucao($transacao)) {
            $metodoReal = $norm(data_get($infoPagamento($transacao), 'metodo_real', data_get($infoPagamento($transacao), 'metodo')));

            return match ($metodoReal) {
                'pix' => 'PIX',
                'dinheiro', 'cash', 'especie', 'espécie' => 'Dinheiro',
                'cartao', 'cartão', 'credit_card', 'debito', 'débito' => 'Cartão',
                'transferencia', 'transferência', 'ted', 'doc' => 'Transferência',
                default => 'PIX',
            };
        }

        return $transacao->metodo_pagamento_label
            ?: ucfirst((string) ($transacao->metodo_pagamento ?? '-'));
    };

    $tipoLabel = function ($transacao) use ($isCaucao, $isDevolucaoCaucao) {
        if ($isDevolucaoCaucao($transacao)) {
            return 'Devolução de caução';
        }

        if ($isCaucao($transacao)) {
            return 'Recebimento de caução';
        }

        return 'Pagamento da reserva';
    };

    $statusLabel = function ($transacao) use ($isCaucao, $isDevolucaoCaucao, $norm) {
        if ($isDevolucaoCaucao($transacao)) {
            return 'Devolvido';
        }

        return match ($norm($transacao->status ?? '')) {
            'approved', 'succeeded', 'paid', 'completed', 'complete', 'confirmed', 'concluido' => $isCaucao($transacao) ? 'Recebido' : 'Recebido',
            'pending', 'processing', 'requires_action', 'requires_payment_method' => 'Pendente',
            'refunded', 'refund', 'reembolsado', 'cancelled', 'canceled', 'cancelado', 'cancelada' => 'Cancelado',
            default => $transacao->status ?? '-',
        };
    };

    $totalPagoReserva = (float) ($transacoesReserva ?? collect())
        ->filter($isPagamentoReserva)
        ->sum('valor');
    $totalCaucaoRecebida = (float) ($transacoesReserva ?? collect())
        ->filter($isRecebimentoCaucao)
        ->sum(fn ($transacao) => abs((float) ($transacao->valor ?? 0)));
    $totalCaucaoDevolvida = (float) ($transacoesReserva ?? collect())
        ->filter($isDevolucaoCaucao)
        ->sum(fn ($transacao) => abs((float) ($transacao->valor ?? 0)));
    $saldoCaucao = max(0, $totalCaucaoRecebida - $totalCaucaoDevolvida);
?>

<div class="rs-card mb-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-file-invoice-dollar rs-card__icon"></i>
            <h2>Resumo financeiro</h2>
        </div>
    </div>
    <div class="rs-card__body">
        <div class="rs-finance-kpis">
            <div class="rs-fkpi rs-fkpi--default">
                <i class="fas fa-tag"></i>
                <div>
                    <strong>R$ <?php echo e(number_format($tarifaBase, 2, ',', '.')); ?></strong>
                    <span>Tarifa diária</span>
                </div>
            </div>
            <div class="rs-fkpi rs-fkpi--default">
                <i class="fas fa-calendar-day"></i>
                <div>
                    <strong>R$ <?php echo e(number_format($valorTarifaBase, 2, ',', '.')); ?></strong>
                    <span><?php echo e($diasReserva); ?> <?php echo e($diasReserva === 1 ? 'dia' : 'dias'); ?> &times; tarifa</span>
                </div>
            </div>
            <?php if($valorOpcionais > 0): ?>
                <div class="rs-fkpi rs-fkpi--default">
                    <i class="fas fa-puzzle-piece"></i>
                    <div>
                        <strong>R$ <?php echo e(number_format($valorOpcionais, 2, ',', '.')); ?></strong>
                        <span>Opcionais</span>
                    </div>
                </div>
            <?php endif; ?>
            <?php if($taxaColeta > 0): ?>
                <div class="rs-fkpi rs-fkpi--amber">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>R$ <?php echo e(number_format($taxaColeta, 2, ',', '.')); ?></strong>
                        <span>Retirada fora expediente</span>
                    </div>
                </div>
            <?php endif; ?>
            <?php if($taxaEntrega > 0): ?>
                <div class="rs-fkpi rs-fkpi--amber">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>R$ <?php echo e(number_format($taxaEntrega, 2, ',', '.')); ?></strong>
                        <span>Devolução fora expediente</span>
                    </div>
                </div>
            <?php endif; ?>
            <?php if($percentualDesconto > 0): ?>
                <div class="rs-fkpi rs-fkpi--green">
                    <i class="fas fa-percent"></i>
                    <div>
                        <strong>&minus; R$ <?php echo e(number_format($valorDesconto, 2, ',', '.')); ?></strong>
                        <span>Desconto (<?php echo e(number_format($percentualDesconto, 0)); ?>%)<?php if($cupomCodigo): ?> &middot; <?php echo e($cupomCodigo); ?><?php endif; ?></span>
                    </div>
                </div>
            <?php endif; ?>
            <div class="rs-fkpi rs-fkpi--total">
                <i class="fas fa-coins"></i>
                <div>
                    <strong>R$ <?php echo e(number_format($valorTotal, 2, ',', '.')); ?></strong>
                    <span>Total da reserva</span>
                </div>
            </div>
            <div class="rs-fkpi rs-fkpi--green">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>R$ <?php echo e(number_format($totalPagoReserva, 2, ',', '.')); ?></strong>
                    <span>Pago pela reserva</span>
                </div>
            </div>
            <?php if(!empty($calcao)): ?>
                <div class="rs-fkpi rs-fkpi--amber">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <strong>R$ <?php echo e(number_format($totalCaucaoRecebida, 2, ',', '.')); ?></strong>
                        <span>Caução recebida</span>
                    </div>
                </div>
                <div class="rs-fkpi rs-fkpi--default">
                    <i class="fas fa-undo-alt"></i>
                    <div>
                        <strong>R$ <?php echo e(number_format($totalCaucaoDevolvida, 2, ',', '.')); ?></strong>
                        <span>Caução devolvida</span>
                    </div>
                </div>
                <div class="rs-fkpi rs-fkpi--default">
                    <i class="fas fa-balance-scale"></i>
                    <div>
                        <strong>R$ <?php echo e(number_format($saldoCaucao, 2, ',', '.')); ?></strong>
                        <span>Saldo da caução</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="rs-card">
    <div class="rs-card__header">
        <i class="fas fa-exchange-alt rs-card__icon"></i>
        <h2>Movimentações financeiras</h2>
        <span class="rs-card__badge"><?php echo e($transacoesReserva->count()); ?></span>
    </div>
    <div class="rs-card__body p-0">
        <?php if($transacoesReserva->isEmpty()): ?>
            <div class="rs-card__body">
                <div class="rs-empty">
                    <i class="fas fa-inbox"></i>
                    <span>Nenhuma movimentação financeira vinculada.</span>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="rs-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Método</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $transacoesReserva; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $valorTx = (float) ($transacao->valor ?? 0);
                                $statusTx = strtolower((string) ($transacao->status ?? ''));
                                $isCaucaoTx = $isCaucao($transacao);
                                $isDevolucaoTx = $isDevolucaoCaucao($transacao);
                                $isPendente = in_array($statusTx, ['pending', 'processing', 'requires_action', 'requires_payment_method', 'pendente', 'aguardando'], true);
                                $jaConfirmado = !empty($transacao->confirmado_em);
                                $podeConfirmar = strtolower((string) ($transacao->metodo_pagamento ?? '')) === 'dinheiro' && $isPendente && !$jaConfirmado;
                            ?>
                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?php echo e(\Illuminate\Support\Carbon::parse($transacao->created_at)->format('d/m/Y H:i')); ?></div>
                                </td>
                                <td>
                                    <div class="font-weight-bold"><?php echo e($tipoLabel($transacao)); ?></div>
                                </td>
                                <td><?php echo e($metodoLabel($transacao)); ?></td>
                                <td>
                                    <div class="font-weight-bold <?php echo e($isDevolucaoTx ? 'text-warning' : ($isCaucaoTx ? 'text-info' : 'text-success')); ?>">
                                        R$ <?php echo e(number_format($valorTx, 2, ',', '.')); ?>

                                    </div>
                                </td>
                                <td>
                                    <?php $badgeClass = $isDevolucaoTx ? 'warning' : ($isPendente ? 'warning' : 'success'); ?>
                                    <span class="badge badge-<?php echo e($badgeClass); ?>"><?php echo e($statusLabel($transacao)); ?></span>
                                    <?php if($jaConfirmado): ?>
                                        <div class="small text-success mt-1">
                                            <i class="fas fa-check-circle"></i>
                                            <?php echo e(\Carbon\Carbon::parse($transacao->confirmado_em)->format('d/m/Y H:i')); ?>

                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($podeConfirmar): ?>
                                        <button type="button"
                                            class="btn btn-xs btn-success btn-confirmar-dinheiro"
                                            data-transacao-id="<?php echo e($transacao->id); ?>"
                                            data-reserva-id="<?php echo e($reserva->id); ?>"
                                            title="Confirmar recebimento em dinheiro">
                                            <i class="fas fa-check mr-1"></i>Confirmar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if(!empty($calcao)): ?>
<div class="rs-card mt-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-shield-alt rs-card__icon"></i>
            <h2>Caução</h2>
        </div>
        <?php
            $statusCalcao = strtolower((string) ($calcao->status ?? ''));
            $labelCalcao = match ($statusCalcao) {
                'devolvida' => 'DEVOLVIDA',
                'retida_integralmente' => 'RETIDA',
                'parcialmente_retida', 'parcialmente_capturado', 'capturado' => 'RETIDA PARCIALMENTE',
                default => strtoupper((string) ($calcao->label_status ?? $calcao->status ?? '-')),
            };
        ?>
        <span class="rs-status-badge rs-status-badge--sm <?php echo e(in_array($statusCalcao, ['autorizado', 'capturado', 'parcialmente_capturado', 'devolvida', 'retida_integralmente', 'parcialmente_retida'], true) ? 'rs-status-badge--green' : 'rs-status-badge--amber'); ?>">
            <?php echo e($labelCalcao); ?>

        </span>
    </div>
    <div class="rs-card__body">
        <div class="rs-finance-kpis">
            <div class="rs-fkpi rs-fkpi--default">
                <i class="fas fa-calculator"></i>
                <div>
                    <strong>R$ <?php echo e(number_format((float) ($calcao->valor_calculado ?? 0), 2, ',', '.')); ?></strong>
                    <span>Valor calculado</span>
                </div>
            </div>
            <div class="rs-fkpi rs-fkpi--green">
                <i class="fas fa-lock"></i>
                <div>
                    <strong>R$ <?php echo e(number_format((float) ($calcao->valor_capturado ?? $calcao->valor_autorizado ?? 0), 2, ',', '.')); ?></strong>
                    <span>Valor da caução</span>
                </div>
            </div>
            <?php if(!empty($calcao->data_hora_captura)): ?>
                <div class="rs-fkpi rs-fkpi--default">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong><?php echo e(\Illuminate\Support\Carbon::parse($calcao->data_hora_captura)->format('d/m/Y H:i')); ?></strong>
                        <span>Capturada em</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>


<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/common_financeiro.blade.php ENDPATH**/ ?>