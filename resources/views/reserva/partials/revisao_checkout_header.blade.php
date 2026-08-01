<div class="rev-top">
    <div>
        <h1 class="rev-title">
            Revisão do Check-out
            <span class="rev-badge {{ $checkoutAssinado ? 'rev-badge--green' : '' }}">
                <i class="fas {{ $checkoutAssinado ? 'fa-check-circle' : 'fa-clock' }}"></i>
                {{ $checkoutAssinado ? 'Check-out assinado' : 'Aguardando assinatura' }}
            </span>
        </h1>
        <div class="rev-sub">
            Confira danos, valores de cobrança e pendências antes de concluir a reserva.
        </div>
    </div>
    <div class="rev-actions">
        <a href="{{ route('reserva.show', $codigoReserva) }}" class="rev-btn">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
        {{-- <a href="{{ route('reserva.verContratoCheckout', ['codigo' => $codigoReserva]) }}" target="_blank" class="rev-btn">
            <i class="fas fa-file-pdf mr-1"></i> Ver contrato (PDF)
        </a> --}}
        @php $revisaoCheckoutConcluida = ($reserva->revisao_checkout_concluida ?? 'N') === 'S'; @endphp

        @if($checkoutAssinado && ! $revisaoCheckoutConcluida)
            @include('reserva.partials.revisao_checkout_add_dano_button')
        @endif
    </div>
</div>
