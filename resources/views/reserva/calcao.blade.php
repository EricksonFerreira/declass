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
                @if((float)($calculo['valor_calculado'] ?? 0) > 0)
                    <div class="ci-date-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>R$ {{ number_format((float)$calculo['valor_calculado'], 2, ',', '.') }}</span>
                    </div>
                @endif
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

            {{-- Resumo em 3 cards (row Bootstrap) --}}
            <div class="row mb-4">
                <div class="card col-md-4 mb-3 mb-md-0">
                    <div class="calcao-info-card calcao-info-card--blue">
                        <div class="calcao-info-card__icon-wrap calcao-info-card__icon-wrap--blue">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="calcao-info-card__body">
                            <div class="calcao-info-card__label">Cliente</div>
                            <div class="calcao-info-card__value">{{ $reserva->usuario?->nome ?? '—' }}</div>
                            @if($reserva->usuario?->email)
                                <div class="calcao-info-card__sub">{{ $reserva->usuario->email }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card col-md-4 mb-3 mb-md-0">
                    <div class="calcao-info-card calcao-info-card--green">
                        <div class="calcao-info-card__icon-wrap calcao-info-card__icon-wrap--green">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="calcao-info-card__body">
                            <div class="calcao-info-card__label">Veículo / Grupo</div>
                            <div class="calcao-info-card__value">{{ $reserva->grupoFrota?->nome ?? '—' }}</div>
                            @if($reserva->modelos?->first())
                                <div class="calcao-info-card__sub">{{ $reserva->modelos->first()->nome ?? '' }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card col-md-4">
                    <div class="calcao-info-card calcao-info-card--navy">
                        <div class="calcao-info-card__icon-wrap calcao-info-card__icon-wrap--navy">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="calcao-info-card__body">
                            <div class="calcao-info-card__label calcao-info-card__label--light">Valor do Calção</div>
                            @if((float)($calculo['valor_calculado'] ?? 0) > 0)
                                <div class="calcao-info-card__value calcao-info-card__value--light calcao-info-card__value--lg">
                                    R$ {{ number_format((float)$calculo['valor_calculado'], 2, ',', '.') }}
                                </div>
                                <div class="calcao-info-card__sub calcao-info-card__sub--light">
                                    {{ $calculo['tipo_regra_aplicada'] ?? '' }}
                                    @if($calculo['origem_regra_aplicada'] ?? '') · {{ $calculo['origem_regra_aplicada'] }} @endif
                                </div>
                            @else
                                <div class="calcao-info-card__value" style="color:#fbbf24;">Não configurado</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status do calção existente (quando já há registro) --}}
            @if($calcaoExistente)
                @php
                    $sb = match($calcaoExistente->status) {
                        'autorizado'             => ['bg'=>'#ecfdf5','c'=>'#047857','icon'=>'fa-check-circle'],
                        'capturado'              => ['bg'=>'#fef2f2','c'=>'#b91c1c','icon'=>'fa-lock'],
                        'parcialmente_capturado' => ['bg'=>'#fffbeb','c'=>'#b45309','icon'=>'fa-adjust'],
                        'cancelado'              => ['bg'=>'#f8fafc','c'=>'#64748b','icon'=>'fa-ban'],
                        'falhou'                 => ['bg'=>'#fef2f2','c'=>'#b91c1c','icon'=>'fa-times-circle'],
                        default                  => ['bg'=>'#eff6ff','c'=>'#1d4ed8','icon'=>'fa-info-circle'],
                    };
                @endphp
                <div class="ci-block mb-0" style="background:{{ $sb['bg'] }};border-left-color:{{ $sb['c'] }};">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <i class="fas {{ $sb['icon'] }}" style="font-size:1.3rem;color:{{ $sb['c'] }};flex-shrink:0;"></i>
                        <div>
                            <strong style="color:{{ $sb['c'] }};font-size:.9rem;">{{ $calcaoExistente->label_status }}</strong>
                            <div style="font-size:.8rem;color:#475569;margin-top:3px;">
                                Autorizado: <strong>R$ {{ number_format((float)$calcaoExistente->valor_autorizado, 2, ',', '.') }}</strong>
                                @if($calcaoExistente->valor_capturado)
                                    &middot; Capturado: <strong>R$ {{ number_format((float)$calcaoExistente->valor_capturado, 2, ',', '.') }}</strong>
                                @endif
                                &middot; <code style="font-size:.72rem;background:rgba(0,0,0,.06);padding:1px 5px;border-radius:4px;">{{ $calcaoExistente->payment_intent_id }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ══ Formulário de autorização ══ --}}
            @if(!$calcaoExistente || $calcaoExistente->status === 'falhou')

                @if((float)($calculo['valor_calculado'] ?? 0) <= 0)
                    {{-- Sem configuração --}}
                    <div class="ci-block" style="background:#fffbeb;border-left-color:#f59e0b;">
                        <div class="ci-block-label">
                            <i class="fas fa-exclamation-triangle" style="background:#fffbeb;color:#f59e0b;"></i>
                            <div>
                                <strong>Calção não configurado</strong>
                                <span>Nenhuma regra de calção encontrada para esta reserva. Configure em <strong>Configurações › Calção</strong>.</span>
                            </div>
                        </div>
                    </div>

                @else

                <div class="ci-block">
                    <div class="ci-block-label">
                        <i  style="background:linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%)" class="fas fa-credit-card"></i>
                        <div>
                            <strong>Pré-autorizar Calção no Cartão</strong>
                            <span>
                                O valor de <strong>R$ {{ number_format((float)$calculo['valor_calculado'], 2, ',', '.') }}</strong>
                                será bloqueado (hold) no cartão — não é uma cobrança. Será liberado ou capturado no check-out.
                            </span>
                        </div>
                    </div>

                    {{-- Cabeçalho da seção de cartões --}}
                    <div class="calcao-pm-header">
                        <div class="calcao-pm-header__title">
                            <i class="fas fa-credit-card"></i>
                            @if(!empty($metodosPagamento))
                                Selecione o cartão para pré-autorização
                            @else
                                Nenhum cartão salvo — adicione um cartão
                            @endif
                        </div>
                        <button type="button" class="btn-action btn-actionprimary calcao-btn-add" id="btnAbrirModalCartao">
                            <i class="fas fa-plus"></i>
                            Adicionar cartão
                        </button>
                    </div>

                    {{-- Lista de cartões --}}
                    {{-- $pm['card'] é \Stripe\StripeObject — acesso via -> --}}
                    <div id="calcao-pm-list" class="row">
                        @forelse($metodosPagamento as $pm)
                            @php
                                $card      = $pm['card'];
                                $pmBrand   = strtolower($card->brand ?? '');
                                $pmLast4   = $card->last4 ?? '';
                                $pmMonth   = str_pad($card->exp_month ?? '', 2, '0', STR_PAD_LEFT);
                                $pmYear    = $card->exp_year ?? '';
                                $brandIcon = match($pmBrand) {
                                    'visa'       => 'fab fa-cc-visa',
                                    'mastercard' => 'fab fa-cc-mastercard',
                                    'amex'       => 'fab fa-cc-amex',
                                    'discover'   => 'fab fa-cc-discover',
                                    'jcb'        => 'fab fa-cc-jcb',
                                    default      => 'fas fa-credit-card',
                                };
                                $brandColor = match($pmBrand) {
                                    'visa'       => '#1a1f71',
                                    'mastercard' => '#eb001b',
                                    'amex'       => '#007bc1',
                                    'discover'   => '#e65c00',
                                    default      => '#475569',
                                };
                            @endphp
                            <div class="col-md-6 mb-3">
                                <label class="calcao-pm-item" for="pm_{{ $pm['id'] }}">
                                    <input type="radio" id="pm_{{ $pm['id'] }}" name="payment_method_id_radio"
                                           class="calcao-pm-radio" value="{{ $pm['id'] }}"
                                           {{ $loop->first ? 'checked' : '' }}>
                                    <div class="calcao-pm-item__radio-dot"></div>
                                    <div class="calcao-pm-item__brand-icon" style="color:{{ $brandColor }}">
                                        <i class="{{ $brandIcon }}"></i>
                                    </div>
                                    <div class="calcao-pm-item__info">
                                        <span class="calcao-pm-item__number">&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; {{ $pmLast4 }}</span>
                                        <span class="calcao-pm-item__exp">Vence {{ $pmMonth }}/{{ $pmYear }}</span>
                                    </div>
                                    <span class="calcao-pm-item__badge">Salvo</span>
                                </label>
                            </div>
                        @empty
                            <div class="col-12">
                                <div id="calcao-empty-state" class="calcao-pm-empty">
                                    <i class="fas fa-credit-card"></i>
                                    <p>Nenhum cartão salvo. Clique em <strong>Adicionar cartão</strong> para continuar.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div id="card-errors-global" class="calcao-error-msg d-none"></div>
                    <div id="calcao-status" class="mt-3 d-none"></div>

                    {{-- Botão de autorizar --}}
                    <div class="calcao-action-bar">
                        <button id="btn-autorizar-calcao" type="button"
                                class="btn-action btn-action-success calcao-btn-autorizar"
                                {{ empty($metodosPagamento) ? 'disabled' : '' }}>
                            <i class="fas fa-lock"></i>
                            Pré-autorizar R$ {{ number_format((float)$calculo['valor_calculado'], 2, ',', '.') }}
                        </button>
                    </div>
                </div>

                @endif

            @else
                {{-- Já autorizado com sucesso --}}
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

{{-- ══ Modal: Adicionar novo cartão ══ --}}
<div class="modal fade" id="modalNovoCartao" tabindex="-1" aria-labelledby="modalNovoCartaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content calcao-modal">
            <div class="calcao-modal__header">
                <div class="calcao-modal__header-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div>
                    <h5 class="calcao-modal__title" id="modalNovoCartaoLabel">Adicionar novo cartão</h5>
                    <p class="calcao-modal__sub">O cartão será salvo e ficará disponível para pré-autorização.</p>
                </div>
                <button type="button" class="calcao-modal__close" data-dismiss="modal" aria-label="Fechar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="calcao-modal__body">
                <label class="calcao-modal__label">Dados do cartão</label>
                <div id="modal-card-element" class="calcao-card-input"></div>
                <div id="modal-card-errors" class="calcao-error-msg mt-2"></div>
            </div>
            <div class="calcao-modal__footer">
                <button type="button" class="btn-action btn-action-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" id="btn-salvar-cartao" class="btn-action btn-actionprimary">
                    <i class="fas fa-plus"></i>
                    Adicionar cartão
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
@include('reserva.partials.vistoria_reserva_estilos_inner')
<style>
    /* ── Summary cards topo ── */
    .calcao-info-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px 22px;
        border-radius: 16px;
        background: #fff;
        border: 1.5px solid var(--c-border);
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .calcao-info-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
    }
    .calcao-info-card--blue::after  { background: linear-gradient(90deg, #2563eb, #60a5fa); }
    .calcao-info-card--green::after { background: linear-gradient(90deg, #059669, #34d399); }
    .calcao-info-card--navy {
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        border-color: transparent;
        box-shadow: 0 8px 28px rgba(12,20,40,.28);
    }
    .calcao-info-card--navy::after { background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981); }

    .calcao-info-card__icon-wrap {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .calcao-info-card__icon-wrap--blue  { background: #eff6ff; color: #2563eb; }
    .calcao-info-card__icon-wrap--green { background: #ecfdf5; color: #059669; }
    .calcao-info-card__icon-wrap--navy  { background: rgba(255,255,255,.12); color: #fff; border: 1px solid rgba(255,255,255,.18); }

    .calcao-info-card__body { display: flex; flex-direction: column; gap: 2px; min-width: 0; }

    .calcao-info-card__label {
        font-size: .68rem; font-weight: 700;
        letter-spacing: .1em; text-transform: uppercase;
        color: #64748b; margin-bottom: 3px;
    }
    .calcao-info-card__label--light { color: rgba(255,255,255,.55); }
    .calcao-info-card__value {
        font-size: .97rem; font-weight: 700; color: var(--c-ink);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .calcao-info-card__value--light { color: #fff; }
    .calcao-info-card__value--lg    { font-size: 1.35rem; letter-spacing: -.01em; }
    .calcao-info-card__sub {
        font-size: .72rem; color: #94a3b8; font-weight: 500;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .calcao-info-card__sub--light { color: rgba(255,255,255,.45); }

    /* ── Cabeçalho da seção de cartões ── */
    .calcao-pm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        gap: 12px;
    }
    .calcao-pm-header__title {
        font-size: .82rem;
        font-weight: 700;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .calcao-pm-header__title i { color: var(--c-indigo); }
    .calcao-btn-add { white-space: nowrap; flex-shrink: 0; font-size: .8rem; padding: 7px 14px; }

    /* ── Lista de cartões (grid via Bootstrap row/col) ── */
    #calcao-pm-list { margin-bottom: 18px; }

    .calcao-pm-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 18px;
        border-radius: 14px;
        border: 2px solid var(--c-border);
        background: #fff;
        cursor: pointer;
        transition: border-color .18s, background .18s, box-shadow .18s;
        margin: 0;
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .calcao-pm-item::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--c-indigo), #0ea5e9);
        opacity: 0;
        transition: opacity .18s;
    }
    .calcao-pm-item:hover { background: #f8f9ff; border-color: #c7d2fe; }
    .calcao-pm-item:has(.calcao-pm-radio:checked) {
        border-color: var(--c-indigo);
        background: #eef2ff;
        box-shadow: 0 4px 16px rgba(67,56,202,.12);
    }
    .calcao-pm-item:has(.calcao-pm-radio:checked)::before { opacity: 1; }
    .calcao-pm-radio { display: none; }

    /* Dot radio visual */
    .calcao-pm-item__radio-dot {
        width: 18px; height: 18px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        flex-shrink: 0;
        position: relative;
        transition: border-color .18s;
    }
    .calcao-pm-item__radio-dot::after {
        content: '';
        position: absolute;
        inset: 3px;
        border-radius: 50%;
        background: var(--c-indigo);
        opacity: 0;
        transition: opacity .18s;
    }
    .calcao-pm-item:has(.calcao-pm-radio:checked) .calcao-pm-item__radio-dot {
        border-color: var(--c-indigo);
    }
    .calcao-pm-item:has(.calcao-pm-radio:checked) .calcao-pm-item__radio-dot::after {
        opacity: 1;
    }

    /* Brand icon */
    .calcao-pm-item__brand-icon {
        font-size: 2.2rem;
        flex-shrink: 0;
        line-height: 1;
        width: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .calcao-pm-item__info {
        display: flex;
        flex-direction: column;
        gap: 3px;
        flex: 1;
        min-width: 0;
    }
    .calcao-pm-item__number {
        font-size: .85rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: .08em;
        font-family: 'Courier New', monospace;
    }
    .calcao-pm-item__exp {
        font-size: .72rem;
        color: #94a3b8;
        font-weight: 500;
    }
    .calcao-pm-item__badge {
        font-size: .65rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 999px;
        background: #e0e7ff;
        color: #4338ca;
        white-space: nowrap;
        flex-shrink: 0;
        align-self: flex-start;
    }
    .calcao-pm-item--new .calcao-pm-item__badge {
        background: #dcfce7;
        color: #15803d;
    }

    /* ── Empty state ── */
    .calcao-pm-empty {
        text-align: center;
        padding: 2.5rem 1.5rem;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        color: #94a3b8;
    }
    .calcao-pm-empty i { font-size: 2rem; display: block; margin-bottom: .75rem; opacity: .5; }
    .calcao-pm-empty p { font-size: .85rem; margin: 0; line-height: 1.5; }

    /* ── Barra de ação ── */
    .calcao-action-bar {
        padding-top: 16px;
        border-top: 1px solid var(--c-border);
    }
    .calcao-btn-autorizar {
        width: 100%;
        justify-content: center;
        padding: 14px 24px;
        font-size: .95rem;
    }
    .calcao-btn-autorizar:disabled { opacity: .5; cursor: not-allowed; transform: none !important; }

    /* ── Mensagem de erro inline ── */
    .calcao-error-msg {
        font-size: .8rem;
        color: var(--c-red);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ── Modal ── */
    .calcao-modal {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(12,20,40,.22);
    }
    .calcao-modal__header {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 20px 22px 16px;
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        position: relative;
    }
    .calcao-modal__header::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981);
    }
    .calcao-modal__header-icon {
        width: 42px; height: 42px;
        border-radius: 11px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.18);
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        font-size: .95rem;
        flex-shrink: 0;
    }
    .calcao-modal__title {
        font-size: .97rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 2px;
    }
    .calcao-modal__sub {
        font-size: .75rem;
        color: rgba(148,163,184,.8);
        margin: 0;
    }
    .calcao-modal__close {
        margin-left: auto;
        background: rgba(255,255,255,.10);
        border: 1px solid rgba(255,255,255,.18);
        color: rgba(255,255,255,.7);
        border-radius: 8px;
        width: 32px; height: 32px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: background .18s;
        align-self: center;
    }
    .calcao-modal__close:hover { background: rgba(255,255,255,.18); color: #fff; }
    .calcao-modal__body {
        padding: 20px 22px;
        background: #f8faff;
    }
    .calcao-modal__label {
        display: block;
        font-size: .76rem;
        font-weight: 700;
        color: #3d4f63;
        margin-bottom: 8px;
    }
    .calcao-modal__footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 22px;
        background: #f0f2ff;
        border-top: 1px solid #dce2ff;
    }
    .calcao-card-input {
        border: 1.5px solid #dce6f4;
        border-radius: 10px;
        background: #fff;
        padding: 12px 14px;
        transition: border-color .18s, box-shadow .18s;
    }
    .calcao-card-input.StripeElement--focus {
        border-color: var(--c-indigo);
        box-shadow: 0 0 0 3px rgba(67,56,202,.10);
    }
    .calcao-card-input.StripeElement--invalid { border-color: var(--c-red); }

    @media (max-width: 767px) {
        .calcao-pm-header { flex-direction: column; align-items: flex-start; }
        .calcao-btn-add { width: 100%; justify-content: center; }
        .calcao-info-card { margin-bottom: 12px; }
    }
</style>
@stop

@push('js')
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    const STRIPE_KEY      = @json($stripePublicKey);
    const AUTORIZAR_URL   = @json(route('reserva.calcao.autorizar', $reserva->id));
    const SALVAR_CARTAO_URL = @json(route('reserva.calcao.salvar-cartao', $reserva->id));
    const SHOW_URL        = @json(route('reserva.show', $reserva->id));
    const CSRF            = @json(csrf_token());
    const VALOR           = @json((float)($calculo['valor_calculado'] ?? 0));

    if (!STRIPE_KEY || VALOR <= 0) return;

    const stripe = Stripe(STRIPE_KEY);

    /* ─────────────────────────────────────────
       Stripe Element para o MODAL
    ───────────────────────────────────────── */
    const elements     = stripe.elements();
    const modalElement = elements.create('card', {
        style: {
            base: {
                fontSize: '14px',
                color: '#0c1428',
                fontFamily: 'inherit',
                '::placeholder': { color: '#94a3b8' },
            },
            invalid: { color: '#ef4444' },
        },
        hidePostalCode: true,
    });

    let modalMounted = false;

    // Monta o element quando o modal abre
    $('#modalNovoCartao').on('shown.bs.modal', function () {
        if (!modalMounted) {
            modalElement.mount('#modal-card-element');
            modalMounted = true;
        }
        modalElement.on('change', e => {
            const err = document.getElementById('modal-card-errors');
            err.textContent = e.error ? e.error.message : '';
            err.classList.toggle('d-none', !e.error);
        });
    });

    // Abre modal
    document.getElementById('btnAbrirModalCartao')?.addEventListener('click', () => {
        $('#modalNovoCartao').modal('show');
    });

    /* ─────────────────────────────────────────
       Salvar novo cartão:
       1. Tokeniza no Stripe via JS (createPaymentMethod)
       2. Envia pm.id ao servidor para fazer attach ao cliente
       3. Injeta o cartão retornado na lista
    ───────────────────────────────────────── */
    document.getElementById('btn-salvar-cartao')?.addEventListener('click', async () => {
        const btn   = document.getElementById('btn-salvar-cartao');
        const errEl = document.getElementById('modal-card-errors');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        errEl.textContent = '';
        errEl.classList.add('d-none');

        // Passo 1 — tokenizar no Stripe
        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'card',
            card: modalElement,
        });

        if (error) {
            errEl.textContent = error.message;
            errEl.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus"></i> Adicionar cartão';
            return;
        }

        // Passo 2 — enviar ao servidor para vincular ao cliente Stripe
        try {
            const resp = await fetch(SALVAR_CARTAO_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ payment_method_id: paymentMethod.id }),
            });
            const data = await resp.json();

            if (!data.success) {
                errEl.textContent = data.error ?? 'Erro ao salvar o cartão.';
                errEl.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plus"></i> Adicionar cartão';
                return;
            }

            // Passo 3 — injetar na lista com dados vindos do servidor
            adicionarCartaoNaLista(data.pm);
        } catch (e) {
            errEl.textContent = 'Erro de comunicação. Tente novamente.';
            errEl.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus"></i> Adicionar cartão';
            return;
        }

        // Fecha o modal e reseta
        $('#modalNovoCartao').modal('hide');
        modalElement.clear();
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus"></i> Adicionar cartão';
        document.getElementById('btn-autorizar-calcao').disabled = false;
    });

    function brandIcon(brand) {
        const map = { visa: 'fab fa-cc-visa', mastercard: 'fab fa-cc-mastercard', amex: 'fab fa-cc-amex' };
        return map[String(brand).toLowerCase()] ?? 'fas fa-credit-card';
    }

    const brandColorMap = { visa: '#1a1f71', mastercard: '#eb001b', amex: '#007bc1' };

    function adicionarCartaoNaLista(pm) {
        // Remove empty state se existir
        const emptyWrap = document.getElementById('calcao-empty-state');
        if (emptyWrap) emptyWrap.closest('.col-12')?.remove();

        const list  = document.getElementById('calcao-pm-list');
        // pm pode ser objeto flat {id, brand, last4, exp_month, exp_year} vindo do servidor
        const id    = pm.id;
        const brand = pm.brand ?? '';
        const last4 = pm.last4 ?? '';
        const month = String(pm.exp_month ?? '').padStart(2, '0');
        const year  = pm.exp_year ?? '';
        const color = brandColorMap[brand.toLowerCase()] ?? '#475569';

        const col = document.createElement('div');
        col.className = 'col-md-6 mb-3';
        col.innerHTML = `
            <label class="calcao-pm-item calcao-pm-item--new" for="pm_${id}">
                <input type="radio" id="pm_${id}" name="payment_method_id_radio"
                       class="calcao-pm-radio" value="${id}" checked>
                <div class="calcao-pm-item__radio-dot"></div>
                <div class="calcao-pm-item__brand-icon" style="color:${color}">
                    <i class="${brandIcon(brand)}"></i>
                </div>
                <div class="calcao-pm-item__info">
                    <span class="calcao-pm-item__number">&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; ${last4}</span>
                    <span class="calcao-pm-item__exp">Vence ${month}/${year}</span>
                </div>
                <span class="calcao-pm-item__badge">Novo</span>
            </label>`;

        list.appendChild(col);

        // Desmarca os outros e marca este
        document.querySelectorAll('.calcao-pm-radio').forEach(r => { r.checked = r.value === id; });
    }

    /* ─────────────────────────────────────────
       Pré-autorizar com o cartão selecionado
    ───────────────────────────────────────── */
    document.getElementById('btn-autorizar-calcao')?.addEventListener('click', async () => {
        const btn      = document.getElementById('btn-autorizar-calcao');
        const statusEl = document.getElementById('calcao-status');
        const globalErr = document.getElementById('card-errors-global');

        const radio = document.querySelector('input[name=payment_method_id_radio]:checked');
        if (!radio) {
            globalErr.textContent = 'Selecione ou adicione um cartão para continuar.';
            globalErr.classList.remove('d-none');
            return;
        }
        globalErr.classList.add('d-none');

        const paymentMethodId = radio.value;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando pré-autorização...';
        statusEl.className = 'mt-3 d-none';

        try {
            const resp = await fetch(AUTORIZAR_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ payment_method_id: paymentMethodId }),
            });
            const data = await resp.json();

            if (data.success) {
                btn.innerHTML = '<i class="fas fa-check"></i> Autorizado!';
                statusEl.className = 'mt-3';
                statusEl.innerHTML = `
                    <div class="ci-block" style="background:#ecfdf5;border-left-color:#059669;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <i class="fas fa-check-circle" style="color:#059669;font-size:1.2rem;flex-shrink:0;"></i>
                            <div>
                                <strong style="color:#047857;font-size:.88rem;">Calção autorizado com sucesso!</strong>
                                <p style="color:#475569;font-size:.8rem;margin:2px 0 0;">${data.message ?? ''}</p>
                            </div>
                        </div>
                    </div>`;
                setTimeout(() => window.location.href = SHOW_URL, 1800);
            } else {
                const msg = data.error ?? 'Erro ao autorizar o calção.';
                statusEl.className = 'mt-3';
                statusEl.innerHTML = `
                    <div class="ci-block" style="background:#fef2f2;border-left-color:#ef4444;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <i class="fas fa-times-circle" style="color:#ef4444;font-size:1.2rem;flex-shrink:0;"></i>
                            <div>
                                <strong style="color:#b91c1c;font-size:.88rem;">Falha na pré-autorização</strong>
                                <p style="color:#475569;font-size:.8rem;margin:2px 0 0;">${msg}</p>
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
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i class="fas fa-wifi" style="color:#ef4444;font-size:1.2rem;flex-shrink:0;"></i>
                        <div>
                            <strong style="color:#b91c1c;font-size:.88rem;">Falha de comunicação</strong>
                            <p style="color:#475569;font-size:.8rem;margin:2px 0 0;">Verifique sua conexão e tente novamente.</p>
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
