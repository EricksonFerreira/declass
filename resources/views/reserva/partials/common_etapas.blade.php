@php
    $reservaCancelada = in_array(strtolower((string) ($reserva->statusReserva->nome ?? '')), ['cancelada', 'cancelado'], true);
    $etapaPagamento = $reserva->isPagamentoEfetuado();
    $etapaCheckin = (bool) $reserva->contratoCheckin;
    $etapaCheckinAssinado = $etapaCheckin && ($reserva->contratoCheckin->assinado ?? '') === 'S';
    $etapaCheckout = (bool) ($reserva->contratoCheckout ?? null);
    $etapaCheckoutAssinado = $etapaCheckout
        && (!empty($reserva->contratoCheckout->assinatura) || !empty($reserva->contratoCheckout->data_assinatura));
    $etapaRevisaoConcluida = ($reserva->revisao_checkout_concluida ?? 'N') === 'S';
@endphp

<div class="rs-flow">
    <div class="rs-flow__step {{ $reservaCancelada ? 'is-cancelled' : ($etapaPagamento ? 'is-done' : 'is-current') }}">
        <i class="rs-flow__icon fas {{ $etapaPagamento ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">1.</span>
        <span class="rs-flow__value">{{ $reservaCancelada ? 'Cancelada' : ($etapaPagamento ? 'Pagamento' : 'Pgto pendente') }}</span>
    </div>
    <div class="rs-flow__step {{ $reservaCancelada ? 'is-cancelled' : ($etapaCheckin ? 'is-done' : ($etapaPagamento ? 'is-current' : '')) }}">
        <i class="rs-flow__icon fas {{ $etapaCheckin ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">2.</span>
        <span class="rs-flow__value">Check-in</span>
    </div>
    <div class="rs-flow__step {{ $reservaCancelada ? 'is-cancelled' : ($etapaCheckinAssinado ? 'is-done' : ($etapaCheckin ? 'is-current' : '')) }}">
        <i class="rs-flow__icon fas {{ $etapaCheckinAssinado ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">3.</span>
        <span class="rs-flow__value">Contrato</span>
    </div>
    <div class="rs-flow__step {{ $reservaCancelada ? 'is-cancelled' : ($etapaCheckout ? 'is-done' : ($etapaCheckinAssinado ? 'is-current' : '')) }}">
        <i class="rs-flow__icon fas {{ $etapaCheckout ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">4.</span>
        <span class="rs-flow__value">Check-out</span>
    </div>
    <div class="rs-flow__step {{ $reservaCancelada ? 'is-cancelled' : ($etapaCheckoutAssinado ? 'is-done' : ($etapaCheckout ? 'is-current' : '')) }}">
        <i class="rs-flow__icon fas {{ $etapaCheckoutAssinado ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">5.</span>
        <span class="rs-flow__value">Assinatura</span>
    </div>
    <div class="rs-flow__step {{ $reservaCancelada ? 'is-cancelled' : ($etapaRevisaoConcluida ? 'is-done' : ($etapaCheckoutAssinado ? 'is-current' : '')) }}">
        <i class="rs-flow__icon fas {{ $etapaRevisaoConcluida ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">6.</span>
        <span class="rs-flow__value">Revisão</span>
    </div>
    <div class="rs-flow__step {{ $reservaCancelada ? 'is-cancelled' : ($etapaRevisaoConcluida ? 'is-done' : ($etapaCheckoutAssinado ? 'is-current' : '')) }}">
        <i class="rs-flow__icon fas {{ $etapaRevisaoConcluida ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">8.</span>
        <span class="rs-flow__value">Revisão concluída</span>
    </div>
</div>
