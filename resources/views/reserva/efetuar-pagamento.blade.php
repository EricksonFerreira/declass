@extends('adminlte::page')

@section('title', 'Detalhes da Reserva')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Pagamento da Reserva #{{ $reserva->id }}</strong>
                </div>
                <div class="card-body">
                    <form id="payment-form" action="{{ route('reserva.processarPagamento', $reserva->id) }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label class="font-weight-bold">Gateway configurado na empresa</label>
                            <input type="text" class="form-control" value="{{ $gatewayNome ?? strtoupper($gatewayPadrao ?? 'stripe') }}" readonly>
                            <input type="hidden" name="gateway" value="{{ $gatewayPadrao ?? 'stripe' }}">
                            <small class="text-muted">Moeda configurada: {{ strtoupper($moedaPadrao ?? 'BRL') }}</small>
                        </div>

                        <div id="stripe-payment-method-wrapper" class="form-group">
                            <label for="payment-method" class="font-weight-bold">Metodo de Pagamento (Stripe) <small class="text-danger">*</small></label>
                            <select id="payment-method" class="form-control" name="payment_method">
                                @if(count($metodosPagamento) == 0)
                                    <option value="" disabled selected>Nenhum metodo de pagamento disponivel</option>
                                @else
                                    @foreach($metodosPagamento as $metodo)
                                        <option value="{{ $metodo['id'] }}">
                                            {{ strtoupper($metodo['card']->brand) }} - **** **** **** {{ substr($metodo['card']->last4, -4) }}
                                            (Validade: {{ $metodo['card']->exp_month }}/{{ $metodo['card']->exp_year }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>

                            <small class="text-muted d-block mt-1">
                                Voce pode <a href="{{ route('usuario.metodoPagamento', $reserva->user_id) }}" target="_blank">adicionar um novo metodo de pagamento aqui</a>.
                            </small>
                        </div>

                        <button type="submit" id="btn-submit-pagamento" class="btn btn-success btn-sm btn-block">
                            Efetuar Pagamento
                        </button>

                        <a href="{{ route('reserva.show', $reserva->id) }}" class="btn btn-link btn-xs btn-block mt-2">
                            Voltar para detalhes da reserva
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Resumo</strong>
                </div>
                <div class="card-body">
                    <div><strong>Retirada:</strong> {{ Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</div>
                    <div><strong>Devolucao:</strong> {{ Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</div>
                    <div><strong>Grupo:</strong> {{ $reserva->grupoFrota->nome ?? 'N/A' }}</div>
                    <hr>
                    <div><strong>Total:</strong> {{ strtoupper($moedaPadrao ?? 'BRL') }} {{ number_format($reserva->valor_total_final, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    (function () {
        const gateway = "{{ $gatewayPadrao ?? 'stripe' }}";
        const stripeWrapper = document.getElementById('stripe-payment-method-wrapper');
        const paymentMethodSelect = document.getElementById('payment-method');
        const submitButton = document.getElementById('btn-submit-pagamento');

        function hasStripeMethods() {
            if (!paymentMethodSelect) {
                return false;
            }

            return paymentMethodSelect.options.length > 0 && paymentMethodSelect.value !== '';
        }

        function syncGatewayUI() {
            const isStripe = gateway === 'stripe';

            if (stripeWrapper) {
                stripeWrapper.style.display = isStripe ? 'block' : 'none';
            }

            if (paymentMethodSelect) {
                paymentMethodSelect.required = !!isStripe;
            }

            if (submitButton) {
                submitButton.disabled = !!isStripe && !hasStripeMethods();
            }
        }

        syncGatewayUI();
    })();
</script>
@stop
