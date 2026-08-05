<?php
    $reservaCancelada = in_array(strtolower((string) ($reserva->statusReserva->nome ?? '')), ['cancelada', 'cancelado'], true);
    $etapaPagamento = (bool) ($reserva->isPagamentoEfetuado() ?? false);
    $etapaCheckin = (bool) $reserva->contratoCheckin;
    $etapaCheckinAssinado = $etapaCheckin && ($reserva->contratoCheckin->assinado ?? '') === 'S';
    $calcao = $reserva->calcao ?? null;
    $calcaoConfiguradoEmpresa = (bool) ($calcaoConfiguradoEmpresa ?? false);
    $calcaoAplicavel = (bool) ($calcaoAplicavel ?? $calcaoConfiguradoEmpresa);
    $etapaCalcao = (bool) $calcao;
    $etapaCalcaoRegistrado = $etapaCalcao && in_array((string) ($calcao->status ?? ''), [
        \App\Models\Calcao::STATUS_AUTORIZADO,
        \App\Models\Calcao::STATUS_CAPTURADO,
        \App\Models\Calcao::STATUS_PARCIALMENTE_CAPTURADO,
        \App\Models\Calcao::STATUS_CANCELADO,
    ], true);
    $etapaCalcaoDispensado = ! $calcaoAplicavel;
    $etapaCheckout = (bool) ($reserva->contratoCheckout ?? null);
    $etapaCheckoutAssinado = $etapaCheckout
        && (! empty($reserva->contratoCheckout->assinatura) || ! empty($reserva->contratoCheckout->data_assinatura));
    $etapaRevisaoConcluida = ($reserva->revisao_checkout_concluida ?? 'N') === 'S';
    $checkoutPodeSerAtivo = (bool) ($reserva->checkout_realizado_em ?? null) || $etapaCheckout || $etapaCheckoutAssinado || $etapaRevisaoConcluida;
?>

<style>
    .rs-flow {
        display: grid;
        grid-template-columns: repeat(8, minmax(0, 1fr));
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
        border-radius: 12px;
        border: 1px solid #111827;
        background: #000;
        padding: 7px 4px;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
    }

    .rs-flow__step.is-done {
        border-color: #111827;
        background: #111827;
    }

    .rs-flow__step.is-current {
        border-color: #f59e0b;
        background: #f59e0b;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, .08), 0 8px 20px rgba(15, 23, 42, .08);
    }

    .rs-flow__step.is-calcao {
        border-color: #111827;
        background: #111827;
    }

    .rs-flow__step.is-calcao .rs-flow__icon,
    .rs-flow__step.is-calcao .rs-flow__label,
    .rs-flow__step.is-calcao .rs-flow__value {
        color: #fff;
    }

    .rs-flow__step.is-cancelled {
        border-color: #7f1d1d;
        background: #111827;
    }

    .rs-flow__icon,
    .rs-flow__label,
    .rs-flow__value {
        color: #fff;
    }

    .rs-flow__icon {
        font-size: .76rem;
    }

    .rs-flow__label {
        font-weight: 800;
        font-size: .64rem;
        line-height: 1;
        letter-spacing: .08em;
    }

    .rs-flow__value {
        font-weight: 700;
        font-size: .72rem;
        line-height: 1.05;
    }

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
        <span class="rs-flow__label">- 1 -</span>
        <span class="rs-flow__value"><?php echo e($reservaCancelada ? 'Cancelada' : ($etapaPagamento ? 'Pagamento' : 'Pgto pendente')); ?></span>
    </div>

    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaCheckin ? 'is-done' : ($etapaPagamento ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaCheckin ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">- 2 -</span>
        <span class="rs-flow__value">Check-in</span>
    </div>

    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaCheckinAssinado ? 'is-done' : ($etapaCheckin ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaCheckinAssinado ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">- 3 -</span>
        <span class="rs-flow__value">Contrato</span>
    </div>

    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaCheckinAssinado ? 'is-done' : ($etapaCheckin ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaCheckinAssinado ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">- 4 -</span>
        <span class="rs-flow__value">Assinatura</span>
    </div>

    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : (($etapaCalcaoRegistrado || $etapaCalcaoDispensado) ? 'is-calcao' : ($etapaCheckinAssinado ? 'is-current' : ''))); ?> <?php echo e(! $reservaCancelada && !($etapaCalcaoRegistrado || $etapaCalcaoDispensado) && $etapaCheckinAssinado ? 'is-calcao' : ''); ?>">
        <i class="rs-flow__icon fas <?php echo e(($etapaCalcaoRegistrado || $etapaCalcaoDispensado) ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">- 5 -</span>
        <span class="rs-flow__value">Calção</span>
    </div>

    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaCheckout ? 'is-done' : '')); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaCheckout ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">- 6 -</span>
        <span class="rs-flow__value">Check-out</span>
    </div>

    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaCheckoutAssinado ? 'is-done' : ($checkoutPodeSerAtivo ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaCheckoutAssinado ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">- 7 -</span>
        <span class="rs-flow__value">Assinatura</span>
    </div>

    <div class="rs-flow__step <?php echo e($reservaCancelada ? 'is-cancelled' : ($etapaRevisaoConcluida ? 'is-done' : ($etapaCheckoutAssinado ? 'is-current' : ''))); ?>">
        <i class="rs-flow__icon fas <?php echo e($etapaRevisaoConcluida ? 'fa-check-circle' : 'fa-clock'); ?>"></i>
        <span class="rs-flow__label">- 8 -</span>
        <span class="rs-flow__value">Revisão</span>
    </div>
</div>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/components/reserva/etapas.blade.php ENDPATH**/ ?>