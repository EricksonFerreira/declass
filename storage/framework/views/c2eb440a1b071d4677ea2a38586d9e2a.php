<div class="rs-card mb-3">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div>
            <div class="rs-card__title">Valores da reserva</div>
            <div class="rs-card__sub">Resumo financeiro simples para você acompanhar</div>
        </div>
    </div>
    <div class="rs-card__body">
        <?php
            $calcaoAtual = $reserva->calcao ?? null;
            $valorCalcao = $calcaoAtual
                ? (float) ($calcaoAtual->valor_capturado ?? $calcaoAtual->valor_autorizado ?? $calcaoAtual->valor_calculado ?? 0)
                : 0;
            $calcaoPendente = $calcaoAtual && (float) ($calcaoAtual->valor_capturado ?? 0) <= 0 && (float) ($calcaoAtual->valor_autorizado ?? 0) <= 0;
        ?>
        <div class="rs-price">
            <div class="rs-price__box">
                <div class="rs-price__label">Tarifa base</div>
                <div class="rs-price__value">R$ <?php echo e(number_format($valorTarifaBase, 2, ',', '.')); ?></div>
            </div>
            <div class="rs-price__box">
                <div class="rs-price__label">Opcionais</div>
                <div class="rs-price__value">R$ <?php echo e(number_format($valorOpcionais, 2, ',', '.')); ?></div>
            </div>
            <div class="rs-price__box">
                <div class="rs-price__label">Total</div>
                <div class="rs-price__value">R$ <?php echo e(number_format($valorTotal, 2, ',', '.')); ?></div>
            </div>
            <?php if($calcaoAtual): ?>
            <div class="rs-price__box">
                <div class="rs-price__label">Calção</div>
                <div class="rs-price__value <?php echo e($calcaoPendente ? 'text-warning' : 'text-success'); ?>">
                    R$ <?php echo e(number_format($valorCalcao, 2, ',', '.')); ?>

                </div>
                <div class="small text-muted mt-1">
                    <?php echo e($calcaoPendente ? 'Pendente' : 'Registrado'); ?>

                </div>
            </div>
            <?php endif; ?>
        </div>
        <div style="margin-top:12px" class="rs-list">
            <div class="rs-list__item">
                <div class="rs-list__label">Pagamento</div>
                <div class="rs-list__value"><?php echo e($pagamentoEfetuado ? 'Pago' : 'Pendente'); ?></div>
            </div>
            <?php if($calcaoAtual): ?>
            <div class="rs-list__item">
                <div class="rs-list__label">Calção</div>
                <div class="rs-list__value"><?php echo e($calcaoPendente ? 'Pendente' : 'Registrado'); ?></div>
            </div>
            <?php endif; ?>
            <div class="rs-list__item">
                <div class="rs-list__label">Saldo pendente</div>
                <div class="rs-list__value">R$ <?php echo e(number_format($saldoPendente, 2, ',', '.')); ?></div>
            </div>
            <?php if($percentualDesconto > 0): ?>
                <div class="rs-list__item">
                    <div class="rs-list__label">Desconto</div>
                    <div class="rs-list__value"><?php echo e(number_format($percentualDesconto, 0)); ?>% <?php if($cupomCodigo): ?> - Cupom <?php echo e($cupomCodigo); ?> <?php endif; ?></div>
                </div>
            <?php endif; ?>
        </div>
        <?php if(!$pagamentoEfetuado): ?>
            <div class="rs-actions" style="margin-top:14px;">
                <a href="<?php echo e(route('reserva.pagamento.efetuar', ['id' => $reserva->codigoReserva?->codigo ?? ('RES-' . $reserva->id)])); ?>" class="rs-btn rs-btn--primary">
                    <i class="fas fa-credit-card"></i> Pagar
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="rs-card">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-receipt"></i></div>
        <div>
            <div class="rs-card__title">Histórico de pagamentos</div>
            <div class="rs-card__sub">Transações financeiras da reserva</div>
        </div>
    </div>
    <div class="rs-card__body p-0">
        <?php $transacoes = $reserva->transacoes ?? collect(); ?>
        <?php if($transacoes->isEmpty()): ?>
            <div class="rs-card__body">
                <div class="rs-note">Nenhuma transação registrada para esta reserva.</div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="pl-3">Data</th>
                            <th>Valor</th>
                            <th>Método</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $transacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $statusTransacao = strtolower((string) ($t->status ?? ''));
                                $badgeClass = match ($statusTransacao) {
                                    'confirmado', 'pago', 'aprovado' => 'badge-success',
                                    'pendente', 'aguardando' => 'badge-warning',
                                    'cancelado', 'recusado' => 'badge-danger',
                                    default => 'badge-secondary',
                                };
                            ?>
                            <tr>
                                <td class="pl-3"><?php echo e($t->created_at ? \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') : '-'); ?></td>
                                <td class="font-weight-bold">R$ <?php echo e(number_format((float) ($t->valor ?? 0), 2, ',', '.')); ?></td>
                                <td><?php echo e($t->metodo_pagamento ?: ($t->metodo_pagamento_label ?? '-')); ?></td>
                                <td><span class="badge <?php echo e($badgeClass); ?>"><?php echo e(ucfirst($statusTransacao ?: 'N/I')); ?></span></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/cliente_financeiro.blade.php ENDPATH**/ ?>