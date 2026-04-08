@extends('adminlte::page')

@section('title', 'Pagamento da Reserva')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <div class="d-flex flex-wrap align-items-center justify-content-between">
        <h3 class="mb-0 font-weight-bold">{{ $titulo }}</h3>
        <a href="{{ route('reserva.show', $reserva->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
@stop

@section('content')
@php
    $moeda = strtoupper((string) ($moedaPadrao ?? 'BRL'));
    $simbolo = $moeda === 'USD' ? '$' : ($moeda === 'EUR' ? 'EUR' : 'R$');
    $dias = (int) ($reserva->quantidade_dias ?? 0);
    $tarifaBase = (float) ($reserva->tarifa_base ?? 0);
    $valorTarifaBase = $tarifaBase * $dias;
    $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);
    if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
        $valorOpcionais = (float) $reserva->opcionais->sum(function ($opcional) {
            return (float) ($opcional->pivot->preco_total ?? 0);
        });
    }
    $percentualDesconto = (float) ($reserva->percentual_desconto ?? 0);
    $valorDesconto = ($valorTarifaBase + $valorOpcionais) * ($percentualDesconto / 100);
    $valorTotal = max(0, ($valorTarifaBase + $valorOpcionais) - $valorDesconto);
@endphp
<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-credit-card mr-2"></i> Pagamento
                </h3>
                <span class="badge badge-light">Reserva #{{ $reserva->id }}</span>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form id="payment-form" action="{{ route('reserva.processarPagamento', $reserva->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold">Gateway configurado</label>
                        <input type="text" class="form-control" value="{{ $gatewayNome ?? strtoupper($gatewayPadrao ?? 'STRIPE') }}" readonly>
                        <small class="text-muted">Moeda: {{ $moeda }}</small>
                    </div>

                    @if(($gatewayPadrao ?? 'stripe') === 'stripe')
                        <div id="stripe-payment-method-wrapper" class="form-group">
                            <label for="payment-method" class="font-weight-bold">Metodo de pagamento</label>
                            <div class="d-flex flex-column flex-md-row" style="gap:10px;">
                                <select id="payment-method" class="form-control" name="payment_method">
                                    @if(count($metodosPagamento) == 0)
                                        <option value="" disabled selected>Nenhum metodo cadastrado</option>
                                    @else
                                        @foreach($metodosPagamento as $metodo)
                                            <option value="{{ $metodo['id'] }}">
                                                {{ strtoupper($metodo['card']->brand) }} - **** {{ substr($metodo['card']->last4, -4) }}
                                                ({{ $metodo['card']->exp_month }}/{{ $metodo['card']->exp_year }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <button type="button" class="btn btn-outline-primary" id="openMetodoModal" {{ empty($stripePublicKey) ? 'disabled' : '' }}>
                                    <i class="fas fa-plus mr-1"></i> Novo metodo
                                </button>
                            </div>
                            @if(empty($stripePublicKey))
                                <small class="text-muted d-block mt-2">Chave publica do Stripe nao configurada para esta empresa.</small>
                            @endif
                        </div>
                    @endif

                    <button type="submit" id="btn-submit-pagamento" class="btn btn-success btn-block">
                        Efetuar Pagamento
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-4">
        <div class="card card-outline card-secondary shadow-sm h-100">
            <div class="card-header">
                <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-receipt mr-2"></i>Resumo completo</h3>
            </div>
            <div class="card-body">
                <div class="border rounded p-3 mb-3 bg-light">
                    <div class="small text-muted">Cliente</div>
                    <div class="font-weight-bold">{{ $reserva->usuario->nome ?? '-' }}</div>
                    <div class="text-muted">{{ $reserva->usuario->email ?? '-' }}</div>
                    @if(!empty($reserva->usuario?->telefone))
                        <div class="text-muted">{{ $reserva->usuario->telefone }}</div>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Retirada</span>
                        <strong>{{ \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div class="text-muted">{{ $reserva->estacaoRetirada->nome ?? 'Local nao informado' }}</div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Devolucao</span>
                        <strong>{{ \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div class="text-muted">{{ $reserva->estacaoDevolucao->nome ?? 'Local nao informado' }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted">Veiculo</div>
                    <div class="font-weight-bold">{{ $reserva->grupoFrota->nome ?? 'Grupo nao informado' }}</div>
                    <div class="text-muted">Modelo: {{ $reserva->modelos->nome ?? 'Nao informado' }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-muted">Opcionais</div>
                    <ul class="pl-3 mb-0">
                        @forelse($reserva->opcionais ?? [] as $opcional)
                            <li>{{ $opcional->nome }} ({{ $opcional->pivot->quantidade ?? 1 }}x)</li>
                        @empty
                            <li>Sem opcionais.</li>
                        @endforelse
                    </ul>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span>Diarias</span>
                    <strong>{{ $dias }} dias</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tarifa base</span>
                    <strong>{{ $simbolo }} {{ number_format($valorTarifaBase, 2, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Opcionais</span>
                    <strong>{{ $simbolo }} {{ number_format($valorOpcionais, 2, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Desconto</span>
                    <strong>{{ number_format($percentualDesconto, 0) }}%</strong>
                </div>
                <div class="d-flex justify-content-between font-weight-bold" style="font-size:16px;">
                    <span>Total</span>
                    <span>{{ $simbolo }} {{ number_format($valorTotal, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@if(($gatewayPadrao ?? 'stripe') === 'stripe')
<div class="modal fade" id="modalMetodoPagamento" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar metodo de pagamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">Dados do cartao</label>
                    <div id="card-element" class="form-control" style="padding-top:10px;"></div>
                    <div id="card-errors" class="text-danger small mt-2" role="alert"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarMetodo">Salvar cartao</button>
            </div>
        </div>
    </div>
</div>
@endif
@stop

@section('js')
@if(($gatewayPadrao ?? 'stripe') === 'stripe' && !empty($stripePublicKey))
<script src="https://js.stripe.com/v3/"></script>
@endif
<script>
    (function () {
        const gateway = "{{ $gatewayPadrao ?? 'stripe' }}";
        const stripeKey = @json($stripePublicKey ?? null);
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('btn-submit-pagamento');
        const paymentSelect = document.getElementById('payment-method');
        const openModalBtn = document.getElementById('openMetodoModal');
        const saveButton = document.getElementById('btnSalvarMetodo');
        const cardErrors = document.getElementById('card-errors');
        const csrfToken = @json(csrf_token());

        function hasPaymentMethod() {
            return paymentSelect && paymentSelect.value && paymentSelect.value !== '';
        }

        if (form && submitButton) {
            form.addEventListener('submit', () => {
                submitButton.disabled = true;
                submitButton.textContent = 'Processando...';
            });
        }

        if (gateway !== 'stripe') {
            return;
        }

        if (openModalBtn) {
            openModalBtn.addEventListener('click', () => {
                $('#modalMetodoPagamento').modal('show');
            });
        }

        if (!stripeKey) {
            return;
        }

        const stripe = Stripe(stripeKey);
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        if (saveButton) {
            saveButton.addEventListener('click', async () => {
                const originalHtml = saveButton.innerHTML;
                // Set loading state
                saveButton.disabled = true;
                saveButton.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>Carregando...';
                if (cardErrors) {
                    cardErrors.textContent = '';
                }

                const result = await stripe.createToken(cardElement);
                if (result.error) {
                    if (cardErrors) {
                        cardErrors.textContent = result.error.message || 'Nao foi possivel validar o cartao.';
                    }
                    // restore
                    saveButton.disabled = false;
                    saveButton.innerHTML = originalHtml;
                    return;
                }

                try {
                    const response = await fetch("{{ route('usuario.adicionarMetodoPagamento', $reserva->user_id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ stripeToken: result.token.id })
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Nao foi possivel adicionar o metodo de pagamento.');
                    }

                    if (paymentSelect && Array.isArray(data.metodos)) {
                        paymentSelect.innerHTML = '';
                        data.metodos.forEach((metodo) => {
                            const option = document.createElement('option');
                            option.value = metodo.id;
                            option.textContent = `${(metodo.brand || '').toUpperCase()} - **** ${metodo.last4} (${metodo.exp_month}/${metodo.exp_year})`;
                            paymentSelect.appendChild(option);
                        });
                        if (paymentSelect.options.length > 0) {
                            paymentSelect.value = paymentSelect.options[paymentSelect.options.length - 1].value;
                        }
                    }

                    $('#modalMetodoPagamento').modal('hide');
                } catch (error) {
                    if (cardErrors) {
                        cardErrors.textContent = error.message;
                    }
                } finally {
                    // restore when modal hidden or on error
                    // if modal is still open, restore immediately
                    if (!$('#modalMetodoPagamento').hasClass('show')) {
                        // modal closed
                        saveButton.disabled = false;
                        saveButton.innerHTML = originalHtml;
                    } else {
                        // ensure we restore when modal hides
                        $('#modalMetodoPagamento').one('hidden.bs.modal', function () {
                            saveButton.disabled = false;
                            saveButton.innerHTML = originalHtml;
                        });
                    }
                }
            });

            // also restore button when modal is manually closed
            $('#modalMetodoPagamento').on('hidden.bs.modal', function () {
                saveButton.disabled = false;
                // restore default text if spinner present
                if (saveButton.innerHTML.indexOf('spinner-border') !== -1) {
                    // attempt to set back to default label localized in blade
                    saveButton.innerHTML = '<i class="fas fa-save mr-1"></i> Salvar cartao';
                }
            });
        }
    })();
</script>
@stop
