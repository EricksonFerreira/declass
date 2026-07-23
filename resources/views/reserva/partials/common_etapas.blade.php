@php
    $etapaPagamento = $reserva->isPagamentoEfetuado();
    $etapaCheckin = (bool) $reserva->contratoCheckin;
    $etapaCheckinAssinado = $etapaCheckin && ($reserva->contratoCheckin->assinado ?? '') === 'S';
    $etapaCheckout = (bool) ($reserva->contratoCheckout ?? null);
    $etapaCheckoutAssinado = $etapaCheckout && ($reserva->contratoCheckout->assinado ?? '') === 'S';
@endphp

<div class="rs-flow">
    <div class="rs-flow__step {{ $etapaPagamento ? 'is-done' : 'is-current' }}">
        <i class="rs-flow__icon fas {{ $etapaPagamento ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">1.</span>
        <span class="rs-flow__value">{{ $etapaPagamento ? 'Pagamento' : 'Pgto pendente' }}</span>
    </div>
    <div class="rs-flow__step {{ $etapaCheckin ? 'is-done' : ($etapaPagamento ? 'is-current' : '') }}">
        <i class="rs-flow__icon fas {{ $etapaCheckin ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">2.</span>
        <span class="rs-flow__value">Check-in</span>
    </div>
    <div class="rs-flow__step {{ $etapaCheckinAssinado ? 'is-done' : ($etapaCheckin ? 'is-current' : '') }}">
        <i class="rs-flow__icon fas {{ $etapaCheckinAssinado ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">3.</span>
        <span class="rs-flow__value">Contrato</span>
    </div>
    <div class="rs-flow__step {{ $etapaCheckout ? 'is-done' : ($etapaCheckinAssinado ? 'is-current' : '') }}">
        <i class="rs-flow__icon fas {{ $etapaCheckout ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">4.</span>
        <span class="rs-flow__value">Check-out</span>
    </div>
    <div class="rs-flow__step {{ $etapaCheckoutAssinado ? 'is-done' : ($etapaCheckout ? 'is-current' : '') }}">
        <i class="rs-flow__icon fas {{ $etapaCheckoutAssinado ? 'fa-check-circle' : 'fa-clock' }}"></i>
        <span class="rs-flow__label">5.</span>
        <span class="rs-flow__value">Assinatura</span>
    </div>
</div>
