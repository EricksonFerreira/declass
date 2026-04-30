@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="col-12 px-0">
    <div class="ci-card">

        {{-- ── Header escuro ── --}}
        <div class="ci-card-header">
            <div class="ci-header-left">
                <div class="ci-header-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <div class="ci-header-eyebrow">Calção</div>
                    <h4 class="ci-header-title">Reserva #{{ $reserva->id }}</h4>
                    <p class="ci-header-sub">
                        <i class="fas fa-user mr-1"></i>{{ $reserva->usuario?->nome ?? 'Cliente' }}
                        &nbsp;&middot;&nbsp;
                        <i class="fas fa-layer-group mr-1"></i>{{ $reserva->grupoFrota?->nome ?? '—' }}
                    </p>
                </div>
            </div>
            <div class="ci-header-right">
                <a href="{{ route('reserva.show', $reserva->id) }}" class="ci-btn-back">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </div>

        {{-- ── Body ── --}}
        <div class="ci-body">

            @if(session('error'))
                <div class="alert alert-danger mb-3">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}
                </div>
            @endif

            {{-- Resumo em 3 blocos --}}
            <div class="ci-info-row">
                <div class="ci-info-card">
                    <div class="ci-info-card__icon" style="background:#eff6ff;color:#2563eb;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="ci-info-card__body">
                        <span class="ci-info-card__label">Cliente</span>
                        <span class="ci-info-card__value">{{ $reserva->usuario?->nome ?? '—' }}</span>
                    </div>
                </div>

                <div class="ci-info-card">
                    <div class="ci-info-card__icon" style="background:#f0fdf4;color:#059669;">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="ci-info-card__body">
                        <span class="ci-info-card__label">Veículo / Grupo</span>
                        <span class="ci-info-card__value">{{ $reserva->grupoFrota?->nome ?? '—' }}</span>
                    </div>
                </div>

                <div class="ci-info-card ci-info-card--highlight">
                    <div class="ci-info-card__icon" style="background:rgba(255,255,255,.18);color:#fff;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="ci-info-card__body">
                        <span class="ci-info-card__label" style="color:rgba(255,255,255,.75);">Valor do Calção</span>
                        @if((float)($calculo['valor_calculado'] ?? 0) > 0)
                            <span class="ci-info-card__value" style="color:#fff;font-size:1.25rem;">
                                R$ {{ number_format((float)$calculo['valor_calculado'], 2, ',', '.') }}
                            </span>
                            <span style="font-size:.74rem;color:rgba(255,255,255,.65);margin-top:2px;display:block;">
                                Regra: {{ $calculo['tipo_regra_aplicada'] ?? '—' }} ({{ $calculo['origem_regra_aplicada'] ?? '—' }})
                            </span>
                        @else
                            <span class="ci-info-card__value" style="color:#fbbf24;">Não configurado</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Status do calção existente --}}
            @if($calcaoExistente)
                @php
                    $statusBadge = match($calcaoExistente->status) {
                        'autorizado'             => ['bg'=>'#ecfdf5','color'=>'#047857','icon'=>'fa-check-circle'],
                        'capturado'              => ['bg'=>'#fef2f2','color'=>'#b91c1c','icon'=>'fa-lock'],
                        'parcialmente_capturado' => ['bg'=>'#fffbeb','color'=>'#b45309','icon'=>'fa-adjust'],
                        'cancelado'              => ['bg'=>'#f8fafc','color'=>'#64748b','icon'=>'fa-ban'],
                        'falhou'                 => ['bg'=>'#fef2f2','color'=>'#b91c1c','icon'=>'fa-times-circle'],
                        default                  => ['bg'=>'#eff6ff','color'=>'#1d4ed8','icon'=>'fa-info-circle'],
                    };
                @endphp
                <div class="ci-block mb-3" style="background:{{ $statusBadge['bg'] }};border-left-color:{{ $statusBadge['color'] }};">
                    <div class="d-flex align-items-center gap-3" style="gap:12px;">
                        <i class="fas {{ $statusBadge['icon'] }}" style="font-size:1.3rem;color:{{ $statusBadge['color'] }};flex-shrink:0;"></i>
                        <div>
                            <strong style="color:{{ $statusBadge['color'] }};">{{ $calcaoExistente->label_status }}</strong>
                            <div style="font-size:.82rem;color:#475569;margin-top:2px;">
                                Autorizado: <strong>R$ {{ number_format((float)$calcaoExistente->valor_autorizado, 2, ',', '.') }}</strong>
                                @if($calcaoExistente->valor_capturado)
                                    &middot; Capturado: <strong>R$ {{ number_format((float)$calcaoExistente->valor_capturado, 2, ',', '.') }}</strong>
                                @endif
                                &middot; Intent: <code style="font-size:.75rem;">{{ $calcaoExistente->payment_intent_id }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Formulário de autorização --}}
            @if(!$calcaoExistente || $calcaoExistente->status === 'falhou')
                @if((float)($calculo['valor_calculado'] ?? 0) <= 0)
                    <div class="ci-block" style="background:#fffbeb;border-left-color:#f59e0b;">
                        <div class="ci-block-label">
                            <i class="fas fa-exclamation-triangle" style="background:#fffbeb;color:#f59e0b;"></i>
                            <div>
                                <strong>Calção não configurado</strong>
                                <span>Nenhuma regra de calção foi encontrada para esta reserva. Configure em <strong>Configurações › Calção</strong>.</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-credit-card"></i>
                            <div>
                                <strong>Pré-autorizar Calção no Cartão</strong>
                                <span>
                                    O valor de <strong>R$ {{ number_format((float)$calculo['valor_calculado'], 2, ',', '.') }}</strong>
                                    será bloqueado (hold) no cartão — não é uma cobrança. Será liberado ou capturado no check-out.
                                </span>
                            </div>
                        </div>

                        <div id="stripe-container">
                            {{-- Cartões salvos --}}
                            @if(!empty($metodosPagamento))
                                <div class="mb-3">
                                    <label style="font-size:.76rem;font-weight:600;color:#3d4f63;display:block;margin-bottom:8px;">Cartão salvo</label>
                                    @foreach($metodosPagamento as $pm)
                                        <label class="ci-pm-option" for="pm_{{ $pm['id'] }}">
                                            <input type="radio" id="pm_{{ $pm['id'] }}" name="payment_method_id_radio"
                                                   class="ci-pm-radio" value="{{ $pm['id'] }}">
                                            <i class="fas fa-credit-card"></i>
                                            <span>{{ strtoupper($pm['brand'] ?? '') }} ···· {{ $pm['last4'] ?? '' }}</span>
                                            <span class="ci-pm-exp">{{ $pm['exp_month'] ?? '' }}/{{ $pm['exp_year'] ?? '' }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="ci-divider">— ou —</div>
                            @endif

                            {{-- Novo cartão via Stripe Elements --}}
                            <div class="mb-3">
                                <label style="font-size:.76rem;font-weight:600;color:#3d4f63;display:block;margin-bottom:8px;">Novo cartão</label>
                                <div id="card-element" class="ci-card-input"></div>
                                <div id="card-errors" class="ci-card-error"></div>
                            </div>

                            <button id="btn-autorizar-calcao" class="btn-action btn-action-primary ci-btn-full" type="button">
                                <i class="fas fa-lock"></i>
                                Pré-autorizar R$ {{ number_format((float)$calculo['valor_calculado'], 2, ',', '.') }}
                            </button>

                            <div id="calcao-status" class="mt-3 d-none"></div>
                        </div>
                    </div>
                @endif
            @else
                {{-- Calção já autorizado com sucesso --}}
                <div class="ci-summary-card">
                    <div class="ci-summary-card__icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="ci-summary-card__content">
                        <strong>Calção autorizado com sucesso</strong>
                        <p>O valor de R$ {{ number_format((float)$calcaoExistente->valor_autorizado, 2, ',', '.') }} está pré-autorizado no cartão do cliente e será capturado ou liberado no check-out.</p>
                    </div>
                    <a href="{{ route('reserva.show', $reserva->id) }}" class="ci-btn-create" style="text-decoration:none;">
                        <i class="fas fa-arrow-left"></i>
                        Voltar à Reserva
                    </a>
                </div>
            @endif

        </div>{{-- /ci-body --}}
    </div>{{-- /ci-card --}}
</div>
@stop

@section('css')
@include('reserva.partials.vistoria_reserva_estilos_inner')
<style>
    /* ── Info row (3 cards do topo) ── */
    .ci-info-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 14px;
        margin-bottom: 1.5rem;
    }
    .ci-info-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid var(--c-border);
        box-shadow: var(--shadow-sm);
    }
    .ci-info-card--highlight {
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        border-color: transparent;
        box-shadow: 0 8px 24px rgba(12,20,40,.25);
    }
    .ci-info-card__icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .ci-info-card__body {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .ci-info-card__label {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 3px;
    }
    .ci-info-card__value {
        font-size: .97rem;
        font-weight: 700;
        color: var(--c-ink);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Cartões salvos ── */
    .ci-pm-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1.5px solid var(--c-border);
        background: #fafbff;
        cursor: pointer;
        margin-bottom: 8px;
        font-size: .85rem;
        font-weight: 600;
        color: #334155;
        transition: border-color .18s, background .18s;
    }
    .ci-pm-option:has(.ci-pm-radio:checked) {
        border-color: var(--c-indigo);
        background: #eff3ff;
        color: var(--c-indigo);
    }
    .ci-pm-radio { display: none; }
    .ci-pm-exp {
        margin-left: auto;
        font-size: .76rem;
        color: #94a3b8;
        font-weight: 500;
    }

    /* ── Divisor ── */
    .ci-divider {
        text-align: center;
        color: #94a3b8;
        font-size: .8rem;
        margin: 14px 0;
        position: relative;
    }
    .ci-divider::before,
    .ci-divider::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 42%;
        height: 1px;
        background: var(--c-border);
    }
    .ci-divider::before { left: 0; }
    .ci-divider::after  { right: 0; }

    /* ── Stripe card element ── */
    .ci-card-input {
        border: 1.5px solid #dce6f4;
        border-radius: 9px;
        background: #fcfeff;
        padding: 11px 12px;
        transition: border-color .18s, box-shadow .18s;
    }
    .ci-card-input.StripeElement--focus {
        border-color: var(--c-indigo);
        box-shadow: 0 0 0 3px rgba(67,56,202,.10);
    }
    .ci-card-error {
        font-size: .78rem;
        color: var(--c-red);
        margin-top: 6px;
        min-height: 1rem;
    }

    /* ── Botão full-width ── */
    .ci-btn-full {
        width: 100%;
        justify-content: center;
        padding: 13px 24px;
        font-size: .95rem;
    }

    @media (max-width: 767px) {
        .ci-info-row { grid-template-columns: 1fr; }
    }
</style>
@stop

@push('js')
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    const stripeKey  = @json($stripePublicKey);
    const reservaId  = @json($reserva->id);
    const autorizarUrl = @json(route('reserva.calcao.autorizar', $reserva->id));
    const csrfToken  = @json(csrf_token());
    const valorCalcao = @json((float)($calculo['valor_calculado'] ?? 0));

    if (!stripeKey || valorCalcao <= 0) return;

    const stripe   = Stripe(stripeKey);
    const elements = stripe.elements();
    const cardElement = elements.create('card', {
        style: {
            base: { fontSize: '14px', color: '#0c1428', fontFamily: 'inherit', '::placeholder': { color: '#94a3b8' } },
            invalid: { color: '#ef4444' },
        }
    });
    cardElement.mount('#card-element');
    cardElement.on('change', e => {
        document.getElementById('card-errors').textContent = e.error ? e.error.message : '';
    });

    const btn      = document.getElementById('btn-autorizar-calcao');
    const statusEl = document.getElementById('calcao-status');

    btn?.addEventListener('click', async () => {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
        statusEl.className = 'mt-3 d-none';
        statusEl.textContent = '';

        const radioSelecionado = document.querySelector('input[name=payment_method_id_radio]:checked');
        let paymentMethodId = radioSelecionado?.value ?? null;

        if (!paymentMethodId) {
            const { paymentMethod, error } = await stripe.createPaymentMethod({ type: 'card', card: cardElement });
            if (error) {
                document.getElementById('card-errors').textContent = error.message;
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lock"></i> Pré-autorizar R$ {{ number_format((float)($calculo["valor_calculado"] ?? 0), 2, ",", ".") }}';
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
                statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i>' + (data.message ?? 'Calção autorizado com sucesso!');
                setTimeout(() => window.location.href = @json(route('reserva.show', $reserva->id)), 1800);
            } else {
                const msg = data.error ?? 'Erro ao autorizar o calção.';
                statusEl.className = 'mt-3';
                statusEl.innerHTML = `
                    <div class="ci-block" style="background:#fef2f2;border-left-color:#ef4444;">
                        <div class="d-flex align-items-center" style="gap:10px;">
                            <i class="fas fa-times-circle" style="color:#ef4444;font-size:1.1rem;flex-shrink:0;"></i>
                            <div>
                                <strong style="color:#b91c1c;font-size:.88rem;">Falha na pré-autorização</strong>
                                <p style="color:#64748b;font-size:.82rem;margin:2px 0 0;">${msg}</p>
                            </div>
                        </div>
                    </div>`;
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-lock"></i> Tentar novamente';
            }
        } catch (e) {
            statusEl.className = 'mt-3';
            statusEl.innerHTML = `
                <div class="ci-block" style="background:#fef2f2;border-left-color:#ef4444;">
                    <div class="d-flex align-items-center" style="gap:10px;">
                        <i class="fas fa-wifi" style="color:#ef4444;font-size:1.1rem;flex-shrink:0;"></i>
                        <div>
                            <strong style="color:#b91c1c;font-size:.88rem;">Falha de comunicação</strong>
                            <p style="color:#64748b;font-size:.82rem;margin:2px 0 0;">Verifique sua conexão e tente novamente.</p>
                        </div>
                    </div>
                </div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Tentar novamente';
        }
    });
}());
</script>
@endpush
