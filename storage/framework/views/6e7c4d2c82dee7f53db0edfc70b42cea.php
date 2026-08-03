<?php
    $reservaCancelada = in_array(strtolower((string) ($reserva->statusReserva->nome ?? '')), ['cancelada', 'cancelado'], true);
    $etapaPagamento = (bool) ($reserva->isPagamentoEfetuado() ?? false);
    $etapaCheckin = (bool) $reserva->contratoCheckin;
    $etapaCheckinAssinado = $etapaCheckin && ($reserva->contratoCheckin->assinado ?? '') === 'S';
    $calcao = $reserva->calcao ?? null;
    $etapaCalcao = (bool) $calcao;
    $etapaCalcaoRegistrado = $etapaCalcao && in_array((string) ($calcao->status ?? ''), [
        \App\Models\Calcao::STATUS_AUTORIZADO,
        \App\Models\Calcao::STATUS_CAPTURADO,
        \App\Models\Calcao::STATUS_PARCIALMENTE_CAPTURADO,
        \App\Models\Calcao::STATUS_CANCELADO,
    ], true);
    $etapaCheckout = (bool) ($reserva->contratoCheckout ?? null);
    $etapaCheckoutAssinado = $etapaCheckout
        && (! empty($reserva->contratoCheckout->assinatura) || ! empty($reserva->contratoCheckout->data_assinatura));
    $etapaRevisaoConcluida = ($reserva->revisao_checkout_concluida ?? 'N') === 'S';
?>

<style>
    .rs-flow {
        display: grid;
        grid-template-columns: repeat(10, minmax(0, 1fr));
        gap: 6px;
        margin-bottom: 14px;
    }
    .rs-flow__step {
        display: flex;
        flex-direction: column;
        gap: 3px;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-height: 62px;
        border-radius: 11px;
        border: 1px solid #dbe4ee;
        background: #fff;
        padding: 7px 4px;
    }
    .rs-flow__step.is-done { border-color: #bbf7d0; background: #f0fdf4; }
    .rs-flow__step.is-current { border-color: #fde68a; background: #fffbeb; }
    .rs-flow__step.is-cancelled { border-color: #fecaca; background: #fef2f2; }
    .rs-flow__icon { font-size: .76rem; }
    .rs-flow__step.is-done .rs-flow__icon { color: #16a34a; }
    .rs-flow__step.is-current .rs-flow__icon { color: #d97706; }
    .rs-flow__step.is-cancelled .rs-flow__icon { color: #dc2626; }
    .rs-flow__step:not(.is-done):not(.is-current) .rs-flow__icon { color: #94a3b8; }
    .rs-flow__label { font-weight: 800; color: #64748b; font-size: .64rem; line-height: 1; }
    .rs-flow__value { font-weight: 700; color: #0f172a; font-size: .72rem; line-height: 1.05; }
    @media (max-width: 992px) {
        .rs-flow { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 576px) {
        .rs-flow { grid-template-columns: 1fr; }
        .rs-flow__step { min-height: 52px; padding: 6px 5px; }
        .rs-flow__value { font-size: .76rem; }
    }
</style>

<div class="rs-flow">
    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaPagamento ? 'is-done' : 'is-current')); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaPagamento ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">1.</span>
        <span class="rs-flow__value"><?php echo e($reservaCancelada ? 'Cancelada' : ($etapaPagamento ? 'Pagamento' : 'Pgto pendente')); ?></span>
    </div>
    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaCheckin ? 'is-done' : ($etapaPagamento ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaCheckin ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">2.</span>
        <span class="rs-flow__value">Check-in</span>
    </div>
    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaCheckinAssinado ? 'is-done' : ($etapaCheckin ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaCheckinAssinado ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">3.</span>
        <span class="rs-flow__value">Contrato</span>
    </div>
    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaCheckinAssinado ? 'is-done' : ($etapaCheckin ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaCheckinAssinado ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">4.</span>
        <span class="rs-flow__value">Assinatura</span>
    </div>
    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaCalcaoRegistrado ? 'is-done' : ($etapaCheckinAssinado ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaCalcaoRegistrado ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">5.</span>
        <span class="rs-flow__value">Calção</span>
    </div>
    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaCheckout ? 'is-done' : ($etapaCalcaoRegistrado ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaCheckout ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">6.</span>
        <span class="rs-flow__value">Check-out</span>
    </div>
    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaCheckoutAssinado ? 'is-done' : ($etapaCheckout ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaCheckoutAssinado ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">7.</span>
        <span class="rs-flow__value">Assinatura</span>
    </div>
    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaRevisaoConcluida ? 'is-done' : ($etapaCheckoutAssinado ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaRevisaoConcluida ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">8.</span>
        <span class="rs-flow__value">Revisão</span>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/reserva/etapas.blade.php ENDPATH**/ ?>