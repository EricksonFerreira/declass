@extends('adminlte::page')

@section('title', 'Pagamento da Reserva #' . $reserva->id)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
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
        $valorOpcionais = (float) $reserva->opcionais->sum(fn ($o) => (float) ($o->pivot->preco_total ?? 0));
    }
    $percentualDesconto = (float) ($reserva->percentual_desconto ?? 0);
    $valorDesconto = ($valorTarifaBase + $valorOpcionais) * ($percentualDesconto / 100);
    $valorTotal = max(0, ($valorTarifaBase + $valorOpcionais) - $valorDesconto);
    $gateway = $gatewayPadrao ?? 'stripe';
@endphp

<div class="pg-page">

    {{-- ── Hero ── --}}
    <div class="pg-hero mb-4">
        <div class="pg-hero__left">
            <div class="pg-hero__icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <div>
                <div class="pg-hero__eyebrow">RESERVA #{{ $reserva->id }}</div>
                <h2 class="pg-hero__title">Faturamento e Pagamento</h2>
                <p class="pg-hero__sub">Confirme os valores e processe o pagamento da reserva.</p>
            </div>
        </div>
        <div class="pg-hero__actions">
            <a href="{{ route('reserva.show', $reserva->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Voltar à Reserva
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @foreach(['success','warning','error'] as $type)
        @if(session($type))
            <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show mb-3">
                {{ session($type) }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
    @endforeach
    @if($errors->any())
        <div class="alert alert-danger mb-3">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="row">

        {{-- ── Coluna esquerda: Pagamento ── --}}
        <div class="col-lg-7 mb-4">

            {{-- Cliente --}}
            <div class="module-card mb-3">
                <div class="module-card__header module-card__header--blue">
                    <span class="module-card__icon"><i class="fas fa-user"></i></span>
                    <div>
                        <div class="module-card__title">Cliente</div>
                        <div class="module-card__sub">Dados do locatário nesta reserva</div>
                    </div>
                </div>
                <div class="module-card__body">
                    <div class="pg-client-row">
                        <div class="pg-client-avatar">
                            {{ strtoupper(substr($reserva->usuario?->nome ?? 'C', 0, 1)) }}
                        </div>
                        <div>
                            <div class="pg-client-name">{{ $reserva->usuario?->nome ?? '—' }}</div>
                            <div class="pg-client-info">{{ $reserva->usuario?->email ?? '—' }}</div>
                            @if(!empty($reserva->usuario?->telefone))
                                <div class="pg-client-info">{{ $reserva->usuario->telefone }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Período --}}
            <div class="module-card mb-3">
                <div class="module-card__header module-card__header--gray">
                    <span class="module-card__icon"><i class="fas fa-calendar-alt"></i></span>
                    <div>
                        <div class="module-card__title">Período da Locação</div>
                        <div class="module-card__sub">Retirada e devolução</div>
                    </div>
                </div>
                <div class="module-card__body">
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <div class="pg-period-label">Retirada</div>
                            <div class="pg-period-val">
                                <i class="fas fa-map-marker-alt text-success mr-1"></i>
                                {{ $reserva->estacaoRetirada?->nome ?? 'Local não informado' }}
                            </div>
                            <div class="pg-period-date">{{ \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <div class="pg-period-label">Devolução</div>
                            <div class="pg-period-val">
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                {{ $reserva->estacaoDevolucao?->nome ?? 'Local não informado' }}
                            </div>
                            <div class="pg-period-date">{{ \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                    <div class="pg-period-dias">
                        <i class="fas fa-moon mr-1 text-primary"></i>
                        <strong>{{ $dias }}</strong> {{ $dias === 1 ? 'diária' : 'diárias' }}
                        &nbsp;·&nbsp;
                        {{ $reserva->grupoFrota?->nome ?? 'Grupo não informado' }}
                        @if($reserva->modelos?->nome)
                            · {{ $reserva->modelos->nome }}
                        @endif
                    </div>
                </div>
            </div>

            {{-- Gateway de Pagamento --}}
            <div class="module-card mb-3">
                <div class="module-card__header module-card__header--green">
                    <span class="module-card__icon"><i class="fas fa-credit-card"></i></span>
                    <div>
                        <div class="module-card__title">Processar Pagamento</div>
                        <div class="module-card__sub">
                            Gateway: <strong>{{ $gatewayNome ?? strtoupper($gateway) }}</strong>
                            &nbsp;·&nbsp; Moeda: <strong>{{ $moeda }}</strong>
                        </div>
                    </div>
                </div>
                <div class="module-card__body">

                    <div class="alert alert-light pg-payment-tip mb-3">
                        <i class="fas fa-info-circle text-primary mr-2"></i>
                        <div>
                            O valor total abaixo será cobrado no método de pagamento selecionado.
                            Após confirmar, o sistema registrará a transação e atualizará o status da reserva.
                        </div>
                    </div>

                    <form id="payment-form" action="{{ route('reserva.processarPagamento', $reserva->id) }}" method="POST">
                        @csrf

                        @if($gateway === 'stripe')
                            <div class="form-group">
                                <label class="pg-form-label">
                                    <i class="fas fa-wallet mr-1"></i> Método de Pagamento
                                </label>
                                <div class="d-flex gap-2">
                                    <select id="payment-method" class="form-control form-control-sm" name="payment_method">
                                        @if(count($metodosPagamento) == 0)
                                            <option value="" disabled selected>Nenhum método cadastrado</option>
                                        @else
                                            @foreach($metodosPagamento as $metodo)
                                                <option value="{{ $metodo['id'] }}">
                                                    {{ strtoupper($metodo['card']->brand) }} ****{{ substr($metodo['card']->last4, -4) }}
                                                    ({{ $metodo['card']->exp_month }}/{{ $metodo['card']->exp_year }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <button type="button" id="openMetodoModal"
                                        class="btn btn-sm btn-outline-primary"
                                        {{ empty($stripePublicKey) ? 'disabled' : '' }}>
                                        <i class="fas fa-plus"></i> Novo
                                    </button>
                                </div>
                                @if(empty($stripePublicKey))
                                    <small class="text-muted">Chave pública do Stripe não configurada.</small>
                                @endif
                            </div>
                        @endif

                        {{-- Valor total destaque --}}
                        <div class="pg-total-box mb-3">
                            <div class="pg-total-box__label">Total a cobrar</div>
                            <div class="pg-total-box__value">
                                {{ $simbolo }} {{ number_format($valorTotal, 2, ',', '.') }}
                            </div>
                        </div>

                        <button type="submit" id="btn-submit-pagamento" class="btn btn-success btn-block pg-btn-pay">
                            <i class="fas fa-lock mr-2"></i> Confirmar e Processar Pagamento
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Coluna direita: Resumo financeiro ── --}}
        <div class="col-lg-5 mb-4">
            <div class="module-card">
                <div class="module-card__header module-card__header--dark">
                    <span class="module-card__icon"><i class="fas fa-receipt"></i></span>
                    <div>
                        <div class="module-card__title">Resumo Financeiro</div>
                        <div class="module-card__sub">Composição do valor da reserva</div>
                    </div>
                </div>
                <div class="module-card__body">

                    {{-- Opcionais --}}
                    @if($reserva->opcionais?->isNotEmpty())
                        <div class="pg-section-label">Opcionais contratados</div>
                        <div class="pg-item-list mb-3">
                            @foreach($reserva->opcionais as $opcional)
                                <div class="pg-item-list__row">
                                    <span class="pg-item-list__name">
                                        <i class="fas fa-check-circle text-success mr-1"></i>
                                        {{ $opcional->nome }}
                                        @if(($opcional->pivot->quantidade ?? 1) > 1)
                                            <span class="badge badge-light badge-sm">{{ $opcional->pivot->quantidade }}x</span>
                                        @endif
                                    </span>
                                    @if(($opcional->pivot->preco_total ?? 0) > 0)
                                        <span class="pg-item-list__val">
                                            {{ $simbolo }} {{ number_format((float)$opcional->pivot->preco_total, 2, ',', '.') }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Detalhamento financeiro --}}
                    <div class="pg-section-label">Detalhamento</div>
                    <div class="pg-finance-table">
                        <div class="pg-finance-row">
                            <span>Tarifa base</span>
                            <span>{{ $simbolo }} {{ number_format($tarifaBase, 2, ',', '.') }}/dia</span>
                        </div>
                        <div class="pg-finance-row">
                            <span>Diárias</span>
                            <span>{{ $dias }} dias</span>
                        </div>
                        <div class="pg-finance-row">
                            <span>Subtotal diárias</span>
                            <span>{{ $simbolo }} {{ number_format($valorTarifaBase, 2, ',', '.') }}</span>
                        </div>
                        @if($valorOpcionais > 0)
                        <div class="pg-finance-row">
                            <span>Opcionais</span>
                            <span>{{ $simbolo }} {{ number_format($valorOpcionais, 2, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($percentualDesconto > 0)
                        <div class="pg-finance-row pg-finance-row--discount">
                            <span><i class="fas fa-tag mr-1"></i> Desconto ({{ number_format($percentualDesconto, 0) }}%)</span>
                            <span>− {{ $simbolo }} {{ number_format($valorDesconto, 2, ',', '.') }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="pg-total-final">
                        <span>Total</span>
                        <span>{{ $simbolo }} {{ number_format($valorTotal, 2, ',', '.') }}</span>
                    </div>

                    {{-- Nota --}}
                    <div class="alert alert-light mt-3 mb-0 pg-note">
                        <i class="fas fa-shield-alt text-success mr-1"></i>
                        <small>
                            O pagamento é processado com segurança via <strong>{{ strtoupper($gateway) }}</strong>.
                            O cliente receberá uma confirmação após o processamento.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: novo método de pagamento (Stripe) --}}
@if($gateway === 'stripe')
<div class="modal fade" id="modalMetodoPagamento" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:14px; overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(120deg,#f0f4ff,#e8f0fe);">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-credit-card mr-2 text-primary"></i> Adicionar Cartão
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Insira os dados do cartão de crédito ou débito. As informações são transmitidas
                    com segurança diretamente ao Stripe e <strong>não ficam armazenadas</strong> em nossos servidores.
                </p>
                <div class="form-group">
                    <label class="pg-form-label">Dados do cartão</label>
                    <div id="card-element" class="form-control" style="padding-top:10px; border-radius:8px;"></div>
                    <div id="card-errors" class="text-danger small mt-2" role="alert"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarMetodo">
                    <i class="fas fa-save mr-1"></i> Salvar Cartão
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@stop

@section('css')
<style>
/* ── Page ── */
.pg-page { padding-bottom: 2rem; }

/* ── Hero ── */
.pg-hero {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
    background: linear-gradient(120deg, #f0f7ff 0%, #e8f0fe 100%);
    border-radius: 14px; padding: 1.3rem 1.5rem;
}
.pg-hero__left  { display: flex; align-items: center; gap: 1rem; }
.pg-hero__icon  {
    width: 52px; height: 52px; border-radius: 14px;
    background: #4b80ff20; display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: #4b80ff; flex-shrink: 0;
}
.pg-hero__eyebrow { font-size: .72rem; font-weight: 700; letter-spacing: .08em; color: #4b80ff; margin-bottom: .1rem; }
.pg-hero__title   { font-size: 1.2rem; font-weight: 700; color: #0f2740; margin: 0 0 .15rem; }
.pg-hero__sub     { font-size: .82rem; color: #6c757d; margin: 0; }

/* ── Module card ── */
.module-card { border: 1px solid #e5eaf2; border-radius: 14px; overflow: hidden; background: #fff; }
.module-card__header {
    display: flex; align-items: center; gap: .85rem;
    padding: .85rem 1.1rem;
}
.module-card__header--blue  { background: linear-gradient(120deg,#f0f4ff,#e8f0fe); }
.module-card__header--green { background: linear-gradient(120deg,#edfff4,#e8f9f0); }
.module-card__header--gray  { background: #f8f9fa; }
.module-card__header--dark  { background: linear-gradient(120deg,#f4f6f9,#eef0f4); }
.module-card__icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: rgba(255,255,255,.6); display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: #4b80ff; flex-shrink: 0;
}
.module-card__header--green .module-card__icon { color: #28a745; }
.module-card__header--dark .module-card__icon  { color: #6c757d; }
.module-card__title { font-size: .9rem; font-weight: 700; color: #21364f; }
.module-card__sub   { font-size: .76rem; color: #6c757d; }
.module-card__body  { padding: 1.1rem; }

/* ── Client row ── */
.pg-client-row   { display: flex; align-items: center; gap: .85rem; }
.pg-client-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: linear-gradient(135deg,#4b80ff,#6f9fff);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 1.1rem; color: #fff; flex-shrink: 0;
}
.pg-client-name { font-weight: 600; color: #21364f; }
.pg-client-info { font-size: .8rem; color: #6c757d; }

/* ── Period ── */
.pg-period-label { font-size: .72rem; font-weight: 700; letter-spacing: .06em; color: #9aacbf; margin-bottom: .2rem; text-transform: uppercase; }
.pg-period-val   { font-weight: 600; color: #21364f; font-size: .88rem; }
.pg-period-date  { font-size: .8rem; color: #6c757d; }
.pg-period-dias  { background: #f4f8ff; border-radius: 8px; padding: .5rem .85rem; font-size: .82rem; color: #2c4a6e; margin-top: .85rem; }

/* ── Payment tip ── */
.pg-payment-tip { border-radius: 10px; border: 1px solid #d0e3ff; background: #f4f8ff; display: flex; align-items: flex-start; gap: .5rem; font-size: .82rem; }

/* ── Total box ── */
.pg-total-box {
    background: linear-gradient(120deg,#edfff4,#e8f9f0);
    border: 1px solid #c3ecd4; border-radius: 12px; padding: 1rem 1.2rem;
    display: flex; align-items: center; justify-content: space-between;
}
.pg-total-box__label { font-size: .82rem; font-weight: 600; color: #4a7a5a; }
.pg-total-box__value { font-size: 1.5rem; font-weight: 700; color: #1a5c35; }
.pg-btn-pay { border-radius: 10px; font-weight: 600; font-size: .95rem; padding: .65rem; }

/* ── Form label ── */
.pg-form-label { font-weight: 600; font-size: .84rem; color: #2c4a6e; margin-bottom: .35rem; display: block; }
.gap-2 { gap: .5rem; }

/* ── Finance table ── */
.pg-section-label { font-size: .73rem; font-weight: 700; letter-spacing: .06em; color: #9aacbf; text-transform: uppercase; margin-bottom: .5rem; }
.pg-finance-table { border: 1px solid #e9ecef; border-radius: 10px; overflow: hidden; margin-bottom: .85rem; }
.pg-finance-row { display: flex; justify-content: space-between; padding: .4rem .8rem; font-size: .82rem; color: #495057; border-bottom: 1px solid #f0f3f8; }
.pg-finance-row:last-child { border-bottom: none; }
.pg-finance-row--discount { color: #28a745; }
.pg-total-final {
    display: flex; justify-content: space-between; align-items: center;
    padding: .7rem .8rem;
    border: 2px solid #4b80ff;
    border-radius: 10px;
    font-weight: 700; font-size: 1.05rem; color: #0f2740;
    background: #f4f8ff;
}
.pg-item-list { }
.pg-item-list__row { display: flex; justify-content: space-between; align-items: center; padding: .3rem 0; border-bottom: 1px dashed #e9ecef; font-size: .82rem; }
.pg-item-list__row:last-child { border-bottom: none; }
.pg-item-list__name { color: #495057; }
.pg-item-list__val  { font-weight: 600; color: #2c4a6e; white-space: nowrap; margin-left: .5rem; }
.pg-note { border-radius: 8px; border: 1px solid #e0e7ef; background: #f8faff; }
</style>
@stop

@section('js')
@if($gateway === 'stripe' && !empty($stripePublicKey))
<script src="https://js.stripe.com/v3/"></script>
@endif
<script>
(function () {
    const gateway    = @json($gateway);
    const stripeKey  = @json($stripePublicKey ?? null);
    const form       = document.getElementById('payment-form');
    const submitBtn  = document.getElementById('btn-submit-pagamento');
    const paySelect  = document.getElementById('payment-method');
    const openModal  = document.getElementById('openMetodoModal');
    const saveBtn    = document.getElementById('btnSalvarMetodo');
    const cardErrors = document.getElementById('card-errors');
    const csrf       = @json(csrf_token());

    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status"></span> Processando...';
        });
    }

    if (gateway !== 'stripe') return;

    if (openModal) {
        openModal.addEventListener('click', function () { $('#modalMetodoPagamento').modal('show'); });
    }

    if (!stripeKey) return;

    const stripe     = Stripe(stripeKey);
    const elements   = stripe.elements();
    const cardElem   = elements.create('card');
    cardElem.mount('#card-element');

    if (saveBtn) {
        saveBtn.addEventListener('click', async function () {
            const orig = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Salvando...';
            if (cardErrors) cardErrors.textContent = '';

            const result = await stripe.createToken(cardElem);
            if (result.error) {
                if (cardErrors) cardErrors.textContent = result.error.message;
                saveBtn.disabled = false; saveBtn.innerHTML = orig;
                return;
            }

            try {
                const resp = await fetch(@json(route('usuario.adicionarMetodoPagamento', $reserva->user_id)), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ stripeToken: result.token.id })
                });
                const data = await resp.json();
                if (!resp.ok) throw new Error(data.message || 'Erro ao adicionar método.');

                if (paySelect && Array.isArray(data.metodos)) {
                    paySelect.innerHTML = '';
                    data.metodos.forEach(function (m) {
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = (m.brand || '').toUpperCase() + ' ****' + m.last4 + ' (' + m.exp_month + '/' + m.exp_year + ')';
                        paySelect.appendChild(opt);
                    });
                    if (paySelect.options.length) paySelect.value = paySelect.options[paySelect.options.length - 1].value;
                }
                $('#modalMetodoPagamento').modal('hide');
            } catch (err) {
                if (cardErrors) cardErrors.textContent = err.message;
            } finally {
                $('#modalMetodoPagamento').one('hidden.bs.modal', function () {
                    saveBtn.disabled = false; saveBtn.innerHTML = orig;
                });
                if (!$('#modalMetodoPagamento').hasClass('show')) {
                    saveBtn.disabled = false; saveBtn.innerHTML = orig;
                }
            }
        });

        $('#modalMetodoPagamento').on('hidden.bs.modal', function () {
            saveBtn.disabled = false;
            if (saveBtn.innerHTML.includes('spinner-border')) {
                saveBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Salvar Cartão';
            }
        });
    }
})();
</script>
@stop
