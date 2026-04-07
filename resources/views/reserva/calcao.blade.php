@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="col-12 px-0">
    <div class="card card-outline card-primary">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">
                <i class="fas fa-credit-card mr-2"></i>{{ $titulo }}
            </h3>
            <a href="{{ route('reserva.show', $reserva->id) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>

        <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Resumo da reserva --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="info-box shadow-none border">
                        <span class="info-box-icon bg-light-blue"><i class="fas fa-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cliente</span>
                            <span class="info-box-number small">{{ $reserva->usuario?->nome ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box shadow-none border">
                        <span class="info-box-icon bg-light-blue"><i class="fas fa-car"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Veiculo / Grupo</span>
                            <span class="info-box-number small">
                                {{ $reserva->grupoFrota?->nome ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box shadow-none border">
                        <span class="info-box-icon bg-green"><i class="fas fa-shield-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Valor do Calcao</span>
                            <span class="info-box-number">
                                @if((float)($calculo['valor_calculado'] ?? 0) > 0)
                                    R$ {{ number_format((float)$calculo['valor_calculado'], 2, ',', '.') }}
                                    <small class="d-block text-muted">
                                        Regra: {{ $calculo['tipo_regra_aplicada'] ?? '—' }}
                                        ({{ $calculo['origem_regra_aplicada'] ?? '—' }})
                                    </small>
                                @else
                                    <span class="text-warning">Nao configurado</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status do calção existente --}}
            @if($calcaoExistente)
                @php
                    $statusBadge = match($calcaoExistente->status) {
                        'autorizado'             => 'success',
                        'capturado'              => 'danger',
                        'parcialmente_capturado' => 'warning',
                        'cancelado'              => 'secondary',
                        'falhou'                 => 'danger',
                        default                  => 'info',
                    };
                @endphp
                <div class="alert alert-info">
                    <strong><i class="fas fa-info-circle mr-1"></i>Calcao registrado:</strong>
                    Status: <span class="badge badge-{{ $statusBadge }}">{{ $calcaoExistente->label_status }}</span>
                    &middot; Autorizado: R$ {{ number_format((float)$calcaoExistente->valor_autorizado, 2, ',', '.') }}
                    @if($calcaoExistente->valor_capturado)
                        &middot; Capturado: R$ {{ number_format((float)$calcaoExistente->valor_capturado, 2, ',', '.') }}
                    @endif
                    &middot; Intent: <code>{{ $calcaoExistente->payment_intent_id }}</code>
                </div>
            @endif

            {{-- Formulário de autorização --}}
            @if(!$calcaoExistente || $calcaoExistente->status === 'falhou')
                @if((float)($calculo['valor_calculado'] ?? 0) <= 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Nenhuma configuracao de calcao encontrada para esta reserva.
                        Configure em <strong>Configuracoes &gt; Calcao</strong>.
                    </div>
                @else
                <div class="card card-default">
                    <div class="card-header"><h4 class="card-title">Pre-autorizar Calcao no Cartao</h4></div>
                    <div class="card-body">
                        <p class="text-muted small">
                            O valor de <strong>R$ {{ number_format((float)$calculo['valor_calculado'], 2, ',', '.') }}</strong>
                            sera bloqueado (hold) no cartao — nao e uma cobrança. Sera liberado ou capturado no check-out.
                        </p>

                        <div id="stripe-container">
                            {{-- Metodos de pagamento salvos --}}
                            @if(!empty($metodosPagamento))
                            <div class="form-group">
                                <label>Cartao salvo</label>
                                @foreach($metodosPagamento as $pm)
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="pm_{{ $pm['id'] }}" name="payment_method_id_radio"
                                           class="custom-control-input" value="{{ $pm['id'] }}">
                                    <label class="custom-control-label" for="pm_{{ $pm['id'] }}">
                                        <i class="fas fa-credit-card mr-1"></i>
                                        {{ strtoupper($pm['brand'] ?? '') }} ···· {{ $pm['last4'] ?? '' }}
                                        ({{ $pm['exp_month'] ?? '' }}/{{ $pm['exp_year'] ?? '' }})
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <div class="text-center text-muted my-2">— ou —</div>
                            @endif

                            {{-- Novo cartão via Stripe Elements --}}
                            <div class="form-group">
                                <label>Novo cartao</label>
                                <div id="card-element" class="form-control" style="height:40px;padding-top:10px;"></div>
                                <div id="card-errors" class="text-danger small mt-1"></div>
                            </div>

                            <button id="btn-autorizar-calcao" class="btn btn-primary btn-block" type="button">
                                <i class="fas fa-lock mr-1"></i>
                                Pre-autorizar R$ {{ number_format((float)$calculo['valor_calculado'], 2, ',', '.') }}
                            </button>
                            <div id="calcao-status" class="mt-3 d-none"></div>
                        </div>
                    </div>
                </div>
                @endif
            @endif

        </div>
    </div>
</div>
@stop

@push('js')
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripeKey = @json($stripePublicKey);
    const reservaId = @json($reserva->id);
    const autorizarUrl = @json(route('reserva.calcao.autorizar', $reserva->id));
    const csrfToken = @json(csrf_token());
    const valorCalcao = @json((float)($calculo['valor_calculado'] ?? 0));

    if (stripeKey && valorCalcao > 0) {
        const stripe = Stripe(stripeKey);
        const elements = stripe.elements();
        const cardElement = elements.create('card', { style: { base: { fontSize: '15px' } } });
        cardElement.mount('#card-element');

        cardElement.on('change', e => {
            document.getElementById('card-errors').textContent = e.error ? e.error.message : '';
        });

        document.getElementById('btn-autorizar-calcao')?.addEventListener('click', async () => {
            const btn = document.getElementById('btn-autorizar-calcao');
            const statusEl = document.getElementById('calcao-status');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processando...';
            statusEl.className = 'mt-3 d-none';

            // Verifica se há cartão salvo selecionado
            const radioSelecionado = document.querySelector('input[name=payment_method_id_radio]:checked');
            let paymentMethodId = radioSelecionado?.value ?? null;

            if (!paymentMethodId) {
                const { paymentMethod, error } = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                });
                if (error) {
                    document.getElementById('card-errors').textContent = error.message;
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock mr-1"></i> Pre-autorizar';
                    return;
                }
                paymentMethodId = paymentMethod.id;
            }

            try {
                const resp = await fetch(autorizarUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ payment_method_id: paymentMethodId }),
                });
                const data = await resp.json();

                if (data.success) {
                    statusEl.className = 'mt-3 alert alert-success';
                    statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i>' + data.message;
                    setTimeout(() => window.location.href = @json(route('reserva.show', $reserva->id)), 1800);
                } else {
                    statusEl.className = 'mt-3 alert alert-danger';
                    statusEl.textContent = data.error ?? 'Erro ao autorizar o calcao.';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock mr-1"></i> Pre-autorizar';
                }
            } catch (e) {
                statusEl.className = 'mt-3 alert alert-danger';
                statusEl.textContent = 'Falha de comunicacao. Tente novamente.';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lock mr-1"></i> Pre-autorizar';
            }
        });
    }
</script>
@endpush
