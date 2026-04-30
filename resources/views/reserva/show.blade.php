@extends('adminlte::page')

@section('title', 'Detalhes da Reserva')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $diasReserva    = (int) ($reserva->quantidade_dias ?? 0);
    $tarifaBase     = (float) ($reserva->tarifa_base ?? 0);
    $valorTarifaBase = $tarifaBase * $diasReserva;
    $valorOpcionais  = (float) ($reserva->valor_opcionais ?? 0);

    if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
        $valorOpcionais = (float) $reserva->opcionais->sum(function ($opcional) {
            return (float) ($opcional->pivot->preco_total ?? 0);
        });
    }

    $percentualDesconto = (float) ($reserva->percentual_desconto ?? 0);
    $taxaColeta         = (float) ($taxaColeta  ?? 0);
    $taxaEntrega        = (float) ($taxaEntrega ?? 0);
    $subtotal           = $valorTarifaBase + $valorOpcionais + $taxaColeta + $taxaEntrega;
    $valorDesconto      = $subtotal * ($percentualDesconto / 100);
    $valorTotal         = max(0, $subtotal - $valorDesconto);

    $grupoFrotaNome     = $reserva->grupoFrota?->nome ?? null;
    $cupomCodigo        = $reserva->cupomDesconto?->codigo ?? null;

    $createdAt        = $reserva->created_at ? \Carbon\Carbon::parse($reserva->created_at)->format('d/m/Y H:i') : '-';
    $inicioFormatado  = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') : '-';
    $fimFormatado     = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i')    : '-';

    $inicioData = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y') : '-';
    $inicioHora = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('H:i')   : '';
    $fimData    = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y')    : '-';
    $fimHora    = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('H:i')      : '';

    $statusReservaNome  = $reserva->statusReserva->nome ?? 'Status';
    $statusReservaSlug  = strtolower((string) $statusReservaNome);
    $statusReservaClass = match ($statusReservaSlug) {
        'confirmada'         => 'is-success',
        'cancelada'          => 'is-danger',
        'criada', 'pendente' => 'is-warning',
        default              => 'is-neutral',
    };
    $statusIcon = match ($statusReservaSlug) {
        'confirmada'         => 'fas fa-check-circle',
        'cancelada'          => 'fas fa-times-circle',
        'criada', 'pendente' => 'fas fa-clock',
        default              => 'fas fa-info-circle',
    };

    $clienteNome    = $reserva->usuario->nome ?? 'Nao informado';
    $clientePartes  = preg_split('/\s+/', trim((string) $clienteNome)) ?: [];
    $clienteIniciais = collect($clientePartes)
        ->filter()
        ->take(2)
        ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');
    $clienteIniciais = $clienteIniciais ?: 'CL';

    $nfseAtual       = $reserva->nfseAtual ?? null;
    $nfseStatusRaw   = strtolower((string) ($nfseAtual->status ?? ''));
    $nfseStatusLabel = $nfseAtual ? ucfirst($nfseAtual->status ?? 'pendente') : 'Nao emitida';
    $nfseBadgeClass  = match ($nfseStatusRaw) {
        'emitida'   => 'is-success',
        'erro'      => 'is-danger',
        'cancelada' => 'is-neutral',
        default     => 'is-warning',
    };
    $nfseMotivo        = $nfseAtual ? (app(\App\Services\NfseService::class)->mensagemErro($nfseAtual) ?? null) : null;
    $reservaConfirmada = isset($reserva->status_reserva_id) && (int) $reserva->status_reserva_id === 2;
    $nfseNaoEmitida    = !$nfseAtual || ($nfseAtual->status ?? '') !== 'emitida';
    $nfseErroDetalhado = null;
    $transacoesReserva = $reserva->transacoes ?? collect();
    $statusPagamentoAprovado  = ['succeeded', 'approved', 'paid', 'completed', 'complete'];
    $statusPagamentoPendente  = ['pending', 'processing', 'requires_action', 'requires_payment_method'];
    $statusPagamentoEstornado = ['refunded', 'refund', 'estorno', 'cancelled', 'canceled', 'cancelado', 'cancelada', 'reembolsado', 'reembolsada'];
    $transacoesPagamento = $transacoesReserva->filter(fn ($item) => (float) ($item->valor ?? 0) >= 0);
    $transacoesEstorno   = $transacoesReserva->filter(fn ($item) =>
        (float) ($item->valor ?? 0) < 0
        || in_array(strtolower((string) ($item->status ?? '')), $statusPagamentoEstornado, true));
    $transacoesPendentes = $transacoesReserva->filter(fn ($item) =>
        in_array(strtolower((string) ($item->status ?? '')), $statusPagamentoPendente, true));
    $totalPagamentos = (float) $transacoesPagamento->sum('valor');
    $totalEstornos   = abs((float) $transacoesEstorno->sum('valor'));

    if ($nfseAtual && ($nfseAtual->status ?? '') === 'erro' && is_array($nfseAtual->response ?? null)) {
        $nfseErroDetalhado = $nfseAtual->response['message'] ?? $nfseAtual->response['error'] ?? $nfseAtual->response['erro'] ?? null;
        if (!$nfseErroDetalhado && !empty($nfseAtual->response['errors'])) {
            $nfseErroDetalhado = implode(' ', (array) $nfseAtual->response['errors']);
        }
    }

    $pagamentoEfetuado = ($reserva->pagamento_efetuado ?? 'N') === 'S';
    $totalMultas = $reserva->multas?->count() ?? 0;
    $danosReserva = $reserva->veiculoDano ?? collect();
    $danosPendentes = $danosReserva->whereNull('correcao_data');
    $totalCobrancasAvaria = $reserva->avariaCobrancas?->count() ?? 0;
    $calcao = $reserva->calcao ?? null;
@endphp

<div class="rs-page">

    {{-- ══════════════════════════════════════════
         HERO
    ══════════════════════════════════════════ --}}
    <div class="rs-hero rs-hero--{{ $statusReservaSlug }}">
        <div class="rs-hero__accent"></div>
        <div class="rs-hero__body">
            <div class="rs-hero__left">
                <div class="rs-hero__eyebrow">
                    <i class="fas fa-ticket-alt"></i>
                    Reserva #{{ $reserva->id }}
                </div>
                <h1 class="rs-hero__title">{{ $titulo }}</h1>
                <div class="rs-hero__meta">
                    <span><i class="fas fa-building"></i>{{ $reserva->empresa->nome ?? 'Empresa' }}</span>
                    <span><i class="fas fa-calendar-plus"></i>Criada em {{ $createdAt }}</span>
                    <span><i class="fas fa-user-tie"></i>
                        @if($reserva->origem_cadastro === 'site')
                            Site
                        @else
                            {{ $reserva->criadoPor?->nome ?? $reserva->criadoPor?->name ?? 'Colaborador' }}
                        @endif
                    </span>
                </div>
            </div>
            <div class="rs-hero__right">
                <span class="rs-status-badge {{ $statusReservaClass }}">
                    <i class="{{ $statusIcon }}"></i>
                    {{ strtoupper($statusReservaNome) }}
                </span>
                <div class="rs-hero__kpis">
                    <div class="rs-kpi">
                        <span>Valor total</span>
                        <strong>R$&nbsp;{{ number_format($valorTotal, 2, ',', '.') }}</strong>
                    </div>
                    <div class="rs-kpi-divider"></div>
                    <div class="rs-kpi">
                        <span>Duracao</span>
                        <strong>{{ $diasReserva }}&nbsp;{{ $diasReserva === 1 ? 'dia' : 'dias' }}</strong>
                    </div>
                    @if($percentualDesconto > 0)
                        <div class="rs-kpi-divider"></div>
                        <div class="rs-kpi rs-kpi--accent">
                            <span>Desconto</span>
                            <strong>{{ number_format($percentualDesconto, 0) }}%</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         PAINEL DE ACOES
    ══════════════════════════════════════════ --}}
    @include('reserva.components.botoes_acoes_show_reserva')

    {{-- ══════════════════════════════════════════
         ALERTA NFS-e PENDENTE
    ══════════════════════════════════════════ --}}
    @if($reservaConfirmada && $nfseNaoEmitida)
        <div class="rs-alert rs-alert--warning">
            <div class="rs-alert__icon"><i class="fas fa-file-invoice"></i></div>
            <div class="rs-alert__content">
                <strong>NFS-e pendente de emissao</strong>
                <p>{{ $nfseMotivo ?? $nfseErroDetalhado ?? 'A nota fiscal ainda nao foi emitida para esta reserva.' }}</p>
            </div>
            <form method="POST" action="{{ route('reserva.emitirNfse', $reserva->id) }}" class="js-nfse-form">
                @csrf
                <button type="submit" class="rs-btn rs-btn--warning js-nfse-btn">
                    <i class="fas fa-redo"></i>
                    Tentar emitir
                </button>
            </form>
        </div>
    @endif

    {{-- ══════════════════════════════════════════
         ABAS PRINCIPAIS
    ══════════════════════════════════════════ --}}
    <div class="rs-tabs-wrapper">
        <ul class="nav nav-tabs rs-nav-tabs" id="reservaMainTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab-visao-geral" data-toggle="tab" href="#pane-visao-geral" role="tab">
                    <i class="fas fa-home"></i>
                    <span>Visão Geral</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-checkin" data-toggle="tab" href="#pane-checkin" role="tab">
                    <i class="fas fa-file-signature"></i>
                    <span>Check-in</span>
                    @if($reserva->contratoCheckin)
                        @php $ciAssinadoTab = $reserva->contratoCheckin->assinado === 'S'; @endphp
                        <span class="rs-tab-badge {{ $ciAssinadoTab ? 'rs-tab-badge--green' : 'rs-tab-badge--amber' }}">
                            {{ $ciAssinadoTab ? 'Assinado' : 'Pendente' }}
                        </span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-checkout" data-toggle="tab" href="#pane-checkout" role="tab">
                    <i class="fas fa-car-crash"></i>
                    <span>Check-out</span>
                    @if(($danosReserva->count() ?? 0) > 0)
                        <span class="rs-tab-badge rs-tab-badge--amber">{{ $danosReserva->count() }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-financeiro" data-toggle="tab" href="#pane-financeiro" role="tab">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Financeiro</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-multas" data-toggle="tab" href="#pane-multas" role="tab">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Multas</span>
                    @if($totalMultas > 0)
                        <span class="rs-tab-badge rs-tab-badge--red">{{ $totalMultas }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-historico" data-toggle="tab" href="#pane-historico" role="tab">
                    <i class="fas fa-history"></i>
                    <span>Histórico</span>
                    <span class="rs-tab-badge rs-tab-badge--slate" id="historicoCount">—</span>
                </a>
            </li>
        </ul>

        <div class="tab-content rs-tab-content" id="reservaMainTabContent">

            {{-- ══════════ TAB 1 — VISÃO GERAL ══════════ --}}
            <div class="tab-pane fade show active" id="pane-visao-geral" role="tabpanel">
                <div class="rs-grid">
                    {{-- COLUNA PRINCIPAL --}}
                    <div class="rs-main">

                        {{-- Card: Rota da viagem --}}
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-route rs-card__icon"></i>
                                <h2>Rota da viagem</h2>
                            </div>
                            <div class="rs-route">
                                <div class="rs-route__stop rs-route__stop--origin">
                                    <div class="rs-route__dot rs-route__dot--origin">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="rs-route__info">
                                        <span class="rs-route__label">Retirada</span>
                                        <strong class="rs-route__place">{{ $reserva->estacaoRetirada->nome ?? 'Nao informado' }}</strong>
                                        <div class="rs-route__datetime">
                                            <span class="rs-route__date">{{ $inicioData }}</span>
                                            @if($inicioHora)
                                                <span class="rs-route__time">{{ $inicioHora }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="rs-route__middle">
                                    <div class="rs-route__line"></div>
                                    <div class="rs-route__duration">
                                        <i class="fas fa-moon"></i>
                                        {{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }}
                                    </div>
                                    <div class="rs-route__line"></div>
                                </div>

                                <div class="rs-route__stop rs-route__stop--dest">
                                    <div class="rs-route__dot rs-route__dot--dest">
                                        <i class="fas fa-flag-checkered"></i>
                                    </div>
                                    <div class="rs-route__info">
                                        <span class="rs-route__label">Devolucao</span>
                                        <strong class="rs-route__place">{{ $reserva->estacaoDevolucao->nome ?? 'Nao informado' }}</strong>
                                        <div class="rs-route__datetime">
                                            <span class="rs-route__date">{{ $fimData }}</span>
                                            @if($fimHora)
                                                <span class="rs-route__time">{{ $fimHora }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Veiculo --}}
                        <div class="rs-card rs-card--vehicle">
                            <div class="rs-card__header">
                                <i class="fas fa-car rs-card__icon"></i>
                                <h2>Veiculo reservado</h2>
                            </div>
                            <div class="rs-vehicle">
                                <div class="rs-vehicle__media">
                                    <img src="{{ $reserva->modelos->imagem_url ?? asset('img/no-image.jpg') }}"
                                         alt="{{ $reserva->modelos->nome ?? 'Veiculo reservado' }}">
                                </div>
                                <div class="rs-vehicle__info">
                                    <div class="rs-vehicle__brand">{{ $reserva->modelos->marca->nome ?? 'Marca nao informada' }}</div>
                                    <h3 class="rs-vehicle__name">{{ $reserva->modelos->nome ?? 'Nao informado' }}</h3>

                                    <div class="rs-vehicle__chips">
                                        @if(!empty($reserva->grupoFrota->nome))
                                            <span class="rs-chip rs-chip--blue">
                                                <i class="fas fa-layer-group"></i>
                                                {{ $reserva->grupoFrota->nome }}
                                            </span>
                                        @endif
                                        @if(!empty($reserva->modelos->transmissao))
                                            <span class="rs-chip rs-chip--slate">
                                                <i class="fas fa-cogs"></i>
                                                {{ $reserva->modelos->transmissao }}
                                            </span>
                                        @endif
                                        @if(!empty($reserva->modelos->combustivel))
                                            <span class="rs-chip rs-chip--green">
                                                <i class="fas fa-gas-pump"></i>
                                                {{ $reserva->modelos->combustivel }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="rs-vehicle__specs">
                                        <div class="rs-spec">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>Ano</span>
                                            <strong>{{ $reserva->modelos->ano ?? '-' }}</strong>
                                        </div>
                                        <div class="rs-spec">
                                            <i class="fas fa-users"></i>
                                            <span>Capacidade</span>
                                            <strong>{{ $reserva->modelos->capacidade ? $reserva->modelos->capacidade . ' pessoas' : '-' }}</strong>
                                        </div>
                                    </div>

                                    <div class="rs-vehicle__pricing">
                                        <div>
                                            <span>Tarifa diaria</span>
                                            <strong>R$ {{ number_format($tarifaBase, 2, ',', '.') }}</strong>
                                        </div>
                                        <div>
                                            <span>{{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }}</span>
                                            <strong>R$ {{ number_format($valorTarifaBase, 2, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Opcionais --}}
                        @if($reserva->opcionais->isNotEmpty())
                            <div class="rs-card">
                                <div class="rs-card__header">
                                    <i class="fas fa-plus-circle rs-card__icon"></i>
                                    <h2>Opcionais contratados</h2>
                                    <span class="rs-card__badge">{{ $reserva->opcionais->count() }} item(s)</span>
                                </div>
                                <div class="rs-optionals">
                                    @foreach($reserva->opcionais as $opcional)
                                        @php
                                            $qtd   = (int) ($opcional->pivot->quantidade ?? 0);
                                            $preco = (float) ($opcional->pivot->preco_total ?? 0);
                                            $total = $qtd * $preco;
                                        @endphp
                                        <div class="rs-optional-row">
                                            <div class="rs-optional-row__info">
                                                <strong>{{ $opcional->nome }}</strong>
                                                <span>
                                                    {{ $opcional->tipo_cobranca == 'por_dia' ? 'Cobrado por dia' : 'Valor integral' }}
                                                    &middot; {{ $qtd }}x
                                                </span>
                                            </div>
                                            <div class="rs-optional-row__price">
                                                R$ {{ number_format($total, 2, ',', '.') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- SIDEBAR --}}
                    <aside class="rs-sidebar">

                        {{-- Card: Cliente --}}
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-user rs-card__icon"></i>
                                <h2>Cliente</h2>
                            </div>
                            <div class="rs-client">
                                <div class="rs-client__avatar">{{ $clienteIniciais }}</div>
                                <div class="rs-client__main">
                                    <strong>{{ $clienteNome }}</strong>
                                    <span>{{ $reserva->usuario->email ?? 'E-mail nao informado' }}</span>
                                </div>
                            </div>
                            <div class="rs-info-rows">
                                <div class="rs-info-row">
                                    <span><i class="fas fa-phone-alt"></i>Telefone</span>
                                    <strong>{{ $reserva->usuario->telefone ?? '-' }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span><i class="fas fa-id-card"></i>Documento</span>
                                    <strong>{{ $reserva->usuario->numero_identificacao ?? '-' }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span><i class="fas fa-birthday-cake"></i>Nascimento</span>
                                    <strong>{{ $reserva->usuario->data_nascimento ? \Carbon\Carbon::parse($reserva->usuario->data_nascimento)->format('d/m/Y') : '-' }}</strong>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Resumo financeiro --}}
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-file-invoice-dollar rs-card__icon"></i>
                                <h2>Resumo financeiro</h2>
                            </div>
                            <div class="rs-summary">
                                @if($grupoFrotaNome)
                                    <div class="rs-summary__row" style="color:#6b7280;font-size:.83rem">
                                        <span><i class="fas fa-layer-group mr-1"></i>Grupo</span>
                                        <span style="font-weight:600;color:#374151">{{ $grupoFrotaNome }}</span>
                                    </div>
                                    <div class="rs-summary__divider"></div>
                                @endif
                                <div class="rs-summary__row">
                                    <span>Tarifa diária</span>
                                    <span>R$ {{ number_format($tarifaBase, 2, ',', '.') }}</span>
                                </div>
                                <div class="rs-summary__row">
                                    <span>{{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }} &times; tarifa</span>
                                    <span>R$ {{ number_format($valorTarifaBase, 2, ',', '.') }}</span>
                                </div>
                                @if($valorOpcionais > 0)
                                    <div class="rs-summary__row">
                                        <span>Opcionais</span>
                                        <span>R$ {{ number_format($valorOpcionais, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($taxaColeta > 0)
                                    <div class="rs-summary__row" style="color:#92400e;background:#fef3c7;border-radius:5px;padding:3px 6px;margin:2px 0">
                                        <span><i class="fas fa-exclamation-triangle mr-1"></i>* Retirada fora do expediente</span>
                                        <span>R$ {{ number_format($taxaColeta, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($taxaEntrega > 0)
                                    <div class="rs-summary__row" style="color:#92400e;background:#fef3c7;border-radius:5px;padding:3px 6px;margin:2px 0">
                                        <span><i class="fas fa-exclamation-triangle mr-1"></i>* Devolução fora do expediente</span>
                                        <span>R$ {{ number_format($taxaEntrega, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($percentualDesconto > 0)
                                    <div class="rs-summary__divider"></div>
                                    <div class="rs-summary__row rs-summary__row--discount" style="background:#f0fdf4;border-radius:5px;padding:3px 6px;margin:2px 0;color:#16a34a">
                                        <span>
                                            <i class="fas fa-tag mr-1"></i>Desconto ({{ number_format($percentualDesconto, 0) }}%)
                                            @if($cupomCodigo)
                                                <span class="badge badge-xs ml-1" style="background:#16a34a;color:#fff;font-size:.68rem">{{ $cupomCodigo }}</span>
                                            @endif
                                        </span>
                                        <span>&minus; R$ {{ number_format($valorDesconto, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="rs-summary__total">
                                    @if($percentualDesconto > 0)
                                        <span style="text-decoration:line-through;color:#9ca3af;font-size:.8rem;font-weight:400;margin-right:6px">R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                                    @endif
                                    <span>Total</span>
                                    <strong>R$ {{ number_format($valorTotal, 2, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Transferencias (resumo) --}}
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-exchange-alt rs-card__icon"></i>
                                <h2>Transferencias</h2>
                            </div>
                            <div class="rs-finance-kpis">
                                <div class="rs-fkpi rs-fkpi--green">
                                    <i class="fas fa-arrow-down"></i>
                                    <div>
                                        <strong>R$ {{ number_format($totalPagamentos, 2, ',', '.') }}</strong>
                                        <span>{{ $transacoesPagamento->count() }} pagamento(s)</span>
                                    </div>
                                </div>
                                <div class="rs-fkpi rs-fkpi--red">
                                    <i class="fas fa-arrow-up"></i>
                                    <div>
                                        <strong>R$ {{ number_format($totalEstornos, 2, ',', '.') }}</strong>
                                        <span>{{ $transacoesEstorno->count() }} estorno(s)</span>
                                    </div>
                                </div>
                                @if($transacoesPendentes->count() > 0)
                                    <div class="rs-fkpi rs-fkpi--amber">
                                        <i class="fas fa-clock"></i>
                                        <div>
                                            <strong>{{ $transacoesPendentes->count() }}</strong>
                                            <span>Pendente(s)</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @if($transacoesReserva->isNotEmpty())
                                <div class="rs-transactions">
                                    @foreach($transacoesReserva->take(5) as $transacao)
                                        @php
                                            $valorTx  = (float) ($transacao->valor ?? 0);
                                            $statusTx = strtolower((string) ($transacao->status ?? ''));
                                            $isEstorno = $valorTx < 0 || in_array($statusTx, $statusPagamentoEstornado, true);
                                        @endphp
                                        @php
                                            $metodoPag   = strtolower($transacao->metodo_pagamento ?? '');
                                            $isDinheiro  = in_array($metodoPag, ['dinheiro','cash','especie','espécie']);
                                            $isPendente  = in_array($statusTx, ['pending','pendente','aguardando']);
                                            $jaConfirmado= !empty($transacao->confirmado_em);
                                            $podeConfirmar = $isDinheiro && $isPendente && !$jaConfirmado;
                                        @endphp
                                        <div class="rs-tx">
                                            <div class="rs-tx__icon {{ $isEstorno ? 'rs-tx__icon--red' : 'rs-tx__icon--green' }}">
                                                <i class="fas {{ $isEstorno ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            </div>
                                            <div class="rs-tx__info">
                                                <strong>{{ strtoupper($transacao->metodo_pagamento ?? 'Nao definido') }}</strong>
                                                <span>{{ \Illuminate\Support\Carbon::parse($transacao->created_at)->format('d/m/Y H:i') }}</span>
                                                @if($jaConfirmado)
                                                    <span class="rs-tx__confirmed">
                                                        <i class="fas fa-check-circle"></i>
                                                        Confirmado {{ \Carbon\Carbon::parse($transacao->confirmado_em)->format('d/m/Y H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="rs-tx__amount {{ $isEstorno ? 'rs-tx__amount--red' : 'rs-tx__amount--green' }}">
                                                <strong>R$ {{ number_format($valorTx, 2, ',', '.') }}</strong>
                                                <span>{{ $transacao->status ?? '-' }}</span>
                                                @if($podeConfirmar)
                                                    <button type="button"
                                                        class="btn btn-xs btn-success mt-1 btn-confirmar-dinheiro"
                                                        data-transacao-id="{{ $transacao->id }}"
                                                        data-reserva-id="{{ $reserva->id }}"
                                                        title="Confirmar recebimento em dinheiro">
                                                        <i class="fas fa-check mr-1"></i>Confirmar recebimento
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if($transacoesReserva->count() > 5)
                                    <div class="text-center mt-2">
                                        <a href="#pane-financeiro" class="rs-btn rs-btn--outline rs-tab-link" data-tab="tab-financeiro">
                                            <i class="fas fa-list"></i> Ver todas ({{ $transacoesReserva->count() }})
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="rs-empty">
                                    <i class="fas fa-inbox"></i>
                                    <span>Nenhuma transferencia vinculada.</span>
                                </div>
                            @endif
                        </div>

                    </aside>
                </div>
            </div>

            {{-- ══════════ TAB 2 — CHECK-IN ══════════ --}}
            <div class="tab-pane fade" id="pane-checkin" role="tabpanel">
                @if($reserva->contratoCheckin)
                @php
                    $ci          = $reserva->contratoCheckin;
                    $ciAssinado  = $ci->assinado === 'S';
                    $ciVeiculo   = $ci->veiculo?->nome ?? ('ID ' . $ci->veiculo_id);
                    $ciBorder    = $ciAssinado ? '#10b981' : '#f59e0b';
                    $ciBgFrom    = $ciAssinado ? '#ecfdf5' : '#fffbeb';
                    $ciBgTo      = $ciAssinado ? '#d1fae5' : '#fef3c7';
                    $ciBadgeBg   = $ciAssinado ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.18)';
                    $ciBadgeClr  = $ciAssinado ? '#047857' : '#b45309';
                    $ciBadgeTxt  = $ciAssinado ? 'Contrato Assinado' : 'Aguardando Assinatura';
                    $ciBadgeIcon = $ciAssinado ? 'fa-check-circle' : 'fa-clock';

                    $fotosPaths = [
                        'Direita'      => $ci->foto_direita_path ?? null,
                        'Esquerda'     => $ci->foto_esquerda_path ?? null,
                        'Frente'       => $ci->foto_frente_path ?? null,
                        'Trás'         => $ci->foto_tras_path ?? null,
                        'Velocímetro'  => $ci->foto_velocimetro_path ?? null,
                        'Combustível'  => $ci->foto_combustivel_path ?? null,
                    ];
                @endphp

                <div class="rs-checkin-layout">

                    {{-- Contrato status card --}}
                    <div class="rs-ci-card" style="border-left:5px solid {{ $ciBorder }};background:linear-gradient(135deg,{{ $ciBgFrom }},{{ $ciBgTo }})">
                        <div class="rs-ci-card__top">
                            <div class="rs-ci-card__icon" style="background:{{ $ciBorder }}">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div class="rs-ci-card__info">
                                <div class="rs-ci-card__title">Contrato de Check-in</div>
                                <span class="rs-ci-card__badge" style="background:{{ $ciBadgeBg }};color:{{ $ciBadgeClr }}">
                                    <i class="fas {{ $ciBadgeIcon }} mr-1"></i>{{ $ciBadgeTxt }}
                                </span>
                            </div>
                        </div>

                        <div class="rs-ci-card__details">
                            <div class="rs-ci-detail">
                                <span><i class="fas fa-car mr-1"></i>Veículo</span>
                                <strong>{{ $ciVeiculo }}</strong>
                            </div>
                            <div class="rs-ci-detail">
                                <span><i class="fas fa-tachometer-alt mr-1"></i>Quilometragem</span>
                                <strong>{{ number_format((float)($ci->quilometragem_atual ?? 0), 0, ',', '.') }} km</strong>
                            </div>
                            <div class="rs-ci-detail">
                                <span><i class="fas fa-gas-pump mr-1"></i>Combustível</span>
                                <strong>{{ $ci->nivel_combustivel ?? '—' }}</strong>
                            </div>
                            <div class="rs-ci-detail">
                                <span><i class="fas fa-calendar mr-1"></i>Saída</span>
                                <strong>
                                    {{ $ci->data_saida ? \Carbon\Carbon::parse($ci->data_saida)->format('d/m/Y') : '—' }}
                                    {{ $ci->hora_saida ? substr($ci->hora_saida, 0, 5) : '' }}
                                </strong>
                            </div>
                            @if($ciAssinado && $ci->data_assinatura)
                            <div class="rs-ci-detail">
                                <span><i class="fas fa-pen-nib mr-1"></i>Assinado em</span>
                                <strong>{{ \Carbon\Carbon::parse($ci->data_assinatura)->format('d/m/Y H:i') }}</strong>
                            </div>
                            @endif
                            @if(!empty($ci->notas_saida))
                            <div class="rs-ci-detail" style="width:100%">
                                <span><i class="fas fa-sticky-note mr-1"></i>Notas de saída</span>
                                <strong>{{ $ci->notas_saida }}</strong>
                            </div>
                            @endif
                            @if(!empty($ci->observacoes_saida))
                            <div class="rs-ci-detail" style="width:100%">
                                <span><i class="fas fa-comment-alt mr-1"></i>Observações</span>
                                <strong>{{ $ci->observacoes_saida }}</strong>
                            </div>
                            @endif
                        </div>

                        <div class="rs-ci-card__actions">
                            <a href="{{ route('reserva.verContratoCheckin', $reserva->id) }}" target="_blank" class="rs-ci-btn rs-ci-btn--view">
                                <i class="fas fa-eye"></i> Ver contrato
                            </a>
                            @if(!$ciAssinado)
                                <a href="{{ route('reserva.assinarContratoCheckin', $reserva->id) }}" class="rs-ci-btn rs-ci-btn--sign">
                                    <i class="fas fa-signature"></i> Assinar
                                </a>
                                <a href="{{ route('reserva.checkin', $reserva->id) }}" class="rs-ci-btn rs-ci-btn--edit">
                                    <i class="fas fa-edit"></i> Editar contrato
                                </a>
                            @endif
                            <button type="button" class="rs-ci-btn rs-ci-btn--delete js-excluir-contrato-checkin"
                                    data-url="{{ route('reserva.checkin.excluirContrato', $reserva->id) }}"
                                    data-assinado="{{ $ciAssinado ? '1' : '0' }}">
                                <i class="fas fa-trash"></i>
                                {{ $ciAssinado ? 'Excluir e recriar' : 'Excluir contrato' }}
                            </button>
                        </div>
                    </div>

                    {{-- Upgrade de Categoria --}}
                    @if($ci->houve_upgrade_categoria)
                    <div class="rs-card" style="border-left:4px solid #fd7e14">
                        <div class="rs-card__header">
                            <i class="fas fa-arrow-circle-up rs-card__icon" style="color:#fd7e14"></i>
                            <h2>Upgrade de Categoria</h2>
                        </div>
                        <div class="rs-info-rows">
                            <div class="rs-info-row">
                                <span>Categoria Reservada</span>
                                <strong>{{ $ci->grupoFrotaOriginal?->nome ?? 'Grupo #' . $ci->grupo_frota_original_id }}</strong>
                            </div>
                            <div class="rs-info-row">
                                <span>Veiculo Entregue</span>
                                <strong>{{ $ci->veiculo?->nome ?? '—' }}</strong>
                            </div>
                            <div class="rs-info-row">
                                <span>Motivo</span>
                                <strong>{{ $ci->motivo_upgrade ?? '—' }}</strong>
                            </div>
                            <div class="rs-info-row">
                                <span>Operador</span>
                                <strong>{{ $ci->usuarioUpgrade?->nome ?? '—' }}</strong>
                            </div>
                            <div class="rs-info-row">
                                <span>Data/Hora</span>
                                <strong>{{ $ci->data_hora_upgrade?->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Fotos do veículo no check-in --}}
                    <div class="rs-card">
                        <div class="rs-card__header">
                            <i class="fas fa-camera rs-card__icon"></i>
                            <h2>Fotos do Veículo no Check-in</h2>
                        </div>
                        <div class="rs-photo-grid">
                            @foreach($fotosPaths as $label => $path)
                                @php $fotoUrl = $path ? asset('storage/' . $path) : null; @endphp
                                <div class="rs-photo-item">
                                    @if($fotoUrl)
                                        <img src="{{ $fotoUrl }}" alt="Foto {{ $label }}" class="rs-photo-thumb js-lightbox-trigger"
                                             data-src="{{ $fotoUrl }}" data-label="{{ $label }}">
                                    @else
                                        <div class="rs-photo-placeholder">
                                            <i class="fas fa-image"></i>
                                            <span>Sem foto</span>
                                        </div>
                                    @endif
                                    <div class="rs-photo-label">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Danos existentes no check-in --}}
                    <div class="rs-card">
                        <div class="rs-card__header">
                            <i class="fas fa-car-crash rs-card__icon" style="color:#64748b"></i>
                            <h2>Danos Existentes (pré-reserva)</h2>
                        </div>
                        <div id="danos-existentes-loading" class="text-center py-3">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Carregando danos...
                        </div>
                        <div id="danos-existentes-container" class="d-none"></div>
                    </div>

                    {{-- Danos detectados nesta reserva --}}
                    <div class="rs-card">
                        <div class="rs-card__header">
                            <i class="fas fa-search rs-card__icon" style="color:#ef4444"></i>
                            <h2>Danos Detectados nesta Reserva</h2>
                        </div>
                        <div id="danos-detectados-loading" class="text-center py-3">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Carregando danos...
                        </div>
                        <div id="danos-detectados-container" class="d-none"></div>
                    </div>

                    {{-- Calção --}}
                    @if($calcao || ($ci->assinado === 'S'))
                    <div class="rs-card">
                        <div class="rs-card__header">
                            <i class="fas fa-shield-alt rs-card__icon" style="color:#6f42c1"></i>
                            <h2>Calcao no Cartao</h2>
                        </div>
                        @if($calcao)
                            @php
                                $calcaoBadge = match($calcao->status) {
                                    'autorizado' => '#28a745', 'capturado' => '#dc3545',
                                    'parcialmente_capturado' => '#fd7e14', 'cancelado' => '#6c757d',
                                    'falhou' => '#dc3545', default => '#17a2b8',
                                };
                            @endphp
                            <div class="rs-info-rows">
                                <div class="rs-info-row">
                                    <span>Status</span>
                                    <strong style="color:{{ $calcaoBadge }}">{{ $calcao->label_status }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span>Valor Autorizado</span>
                                    <strong>R$ {{ number_format((float)$calcao->valor_autorizado, 2, ',', '.') }}</strong>
                                </div>
                                @if($calcao->valor_capturado)
                                <div class="rs-info-row">
                                    <span>Valor Capturado</span>
                                    <strong>R$ {{ number_format((float)$calcao->valor_capturado, 2, ',', '.') }}</strong>
                                </div>
                                @endif
                                <div class="rs-info-row">
                                    <span>Regra Aplicada</span>
                                    <strong>{{ $calcao->tipo_regra_aplicada }} / {{ $calcao->origem_regra_aplicada }}</strong>
                                </div>
                            </div>
                            @if($calcao->podeLiberar())
                            @can('calcao.cancelar')
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="liberarCalcao({{ $calcao->id }})">
                                    <i class="fas fa-unlock mr-1"></i> Liberar Hold (sem cobrar)
                                </button>
                            </div>
                            @endcan
                            @endif
                            @if($calcao->podeCapturar())
                            @can('calcao.capturar')
                            <div class="mt-1">
                                <button type="button" class="btn btn-sm btn-outline-danger w-100" data-toggle="modal" data-target="#modalCapturarCalcao">
                                    <i class="fas fa-hand-holding-usd mr-1"></i> Capturar Valor
                                </button>
                            </div>
                            @endcan
                            @endif
                            @if(empty($calcao->payment_intent_id) && ($calcao->status ?? '') !== 'cancelado')
                            @can('calcao.cancelar')
                            <div class="mt-1">
                                <button type="button" class="btn btn-sm btn-outline-success w-100" data-toggle="modal" data-target="#modalDevolverCalcaoPresencial">
                                    <i class="fas fa-hand-holding-heart mr-1"></i> Devolver Calção Presencial
                                </button>
                            </div>
                            @endcan
                            @endif
                        @else
                            @can('calcao.autorizar')
                            <div class="text-center py-2">
                                <a href="{{ route('reserva.calcao', $reserva->id) }}" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="fas fa-lock mr-1"></i> Registrar Calcao
                                </a>
                            </div>
                            <div class="text-center pb-2">
                                <button type="button" class="btn btn-sm btn-outline-success w-100" data-toggle="modal" data-target="#modalCalcaoPresencial">
                                    <i class="fas fa-cash-register mr-1"></i> Marcar Calção como Pago (Presencial)
                                </button>
                            </div>
                            @endcan
                        @endif
                    </div>
                    @endif

                </div>
                @else
                    <div class="rs-empty py-5">
                        <i class="fas fa-file-signature" style="font-size:2.5rem;color:#cbd5e1"></i>
                        <span>Nenhum contrato de check-in cadastrado para esta reserva.</span>
                        @if($pagamentoEfetuado)
                            <a href="{{ route('reserva.checkin', $reserva->id) }}" class="rs-btn rs-btn--outline mt-2">
                                <i class="fas fa-plus"></i> Criar check-in
                            </a>
                        @else
                            <div class="mt-3 d-flex align-items-center justify-content-center" style="gap:.5rem;background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:.6rem 1rem;font-size:.83rem;color:#92400e;font-weight:600">
                                <i class="fas fa-lock"></i> Pagamento necessário para criar o check-in
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- ══════════ TAB 3 — CHECK-OUT ══════════ --}}
            <div class="tab-pane fade" id="pane-checkout" role="tabpanel">
                <div class="rs-checkin-layout">

                    {{-- Link para vistoria --}}
                    <div class="rs-card">
                        <div class="rs-card__header">
                            <i class="fas fa-search rs-card__icon"></i>
                            <h2>Vistoria de Check-out</h2>
                        </div>
                        <div class="d-flex align-items-center gap-3 flex-wrap" style="gap:12px">
                            @if($pagamentoEfetuado)
                                <a href="{{ route('reserva.checkout.vistoria', $reserva->id) }}" class="rs-btn rs-btn--outline">
                                    <i class="fas fa-search"></i> Abrir vistoria / danos
                                </a>
                            @else
                                <span class="rs-btn rs-btn--outline" style="opacity:.5;cursor:not-allowed;pointer-events:none" title="Pagamento necessário para acessar o checkout">
                                    <i class="fas fa-lock mr-1"></i> Abrir vistoria / danos
                                </span>
                                <small style="color:#92400e;font-weight:600;background:#fef3c7;border:1px solid #f59e0b;border-radius:6px;padding:3px 10px;font-size:.78rem">
                                    <i class="fas fa-lock mr-1"></i>Pagamento necessário
                                </small>
                            @endif
                            @can('avaria.cobrar')
                                <a href="{{ route('reserva.avarias.cobranca', $reserva->id) }}" class="rs-btn rs-btn--outline">
                                    <i class="fas fa-hand-holding-usd"></i> Debitar avarias
                                </a>
                            @endcan
                        </div>
                    </div>

                    {{-- Avarias registradas --}}
                    <div class="rs-card">
                        <div class="rs-card__header rs-card__header--between">
                            <div class="rs-card__header-left">
                                <i class="fas fa-car-crash rs-card__icon" style="color:#f59e0b"></i>
                                <h2>Avarias Registradas</h2>
                            </div>
                            @if(($danosReserva->count() ?? 0) > 0)
                                <span class="rs-status-badge rs-status-badge--sm is-warning">
                                    {{ $danosPendentes->count() }} pendente(s)
                                </span>
                            @endif
                        </div>

                        @if(($danosReserva->count() ?? 0) > 0)
                            <div class="rs-info-rows mb-3">
                                <div class="rs-info-row">
                                    <span><i class="fas fa-list"></i>Total registradas</span>
                                    <strong>{{ $danosReserva->count() }}</strong>
                                </div>
                                <div class="rs-info-row">
                                    <span><i class="fas fa-receipt"></i>Cobranças</span>
                                    <strong>{{ $totalCobrancasAvaria }}</strong>
                                </div>
                            </div>

                            <div class="rs-mini-list">
                                @foreach($danosReserva as $d)
                                    <div class="rs-mini-item">
                                        <div>
                                            <strong>{{ strtoupper($d->local_dano ?? $d->localizacao_problema ?? '-') }}</strong>
                                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit($d->observacoes ?? $d->tipo_dano ?? '', 120) }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div><strong>R$ {{ number_format((float) ($d->custo_estimado ?? 0), 2, ',', '.') }}</strong></div>
                                            @if($d->correcao_data)
                                                <span class="badge badge-success">Corrigido</span>
                                            @else
                                                <span class="badge badge-warning">Pendente</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rs-empty">
                                <i class="fas fa-check-circle" style="color:#10b981"></i>
                                <span>Nenhuma avaria registrada nesta reserva.</span>
                            </div>
                        @endif
                    </div>

                    {{-- Cobranças de avarias --}}
                    @if($totalCobrancasAvaria > 0)
                    <div class="rs-card">
                        <div class="rs-card__header">
                            <i class="fas fa-receipt rs-card__icon" style="color:#dc2626"></i>
                            <h2>Cobranças de Avarias</h2>
                            <span class="rs-card__badge">{{ $totalCobrancasAvaria }}</span>
                        </div>
                        <div class="rs-mini-list">
                            @foreach($reserva->avariaCobrancas as $cobranca)
                                <div class="rs-mini-item">
                                    <div>
                                        <strong>{{ $cobranca->descricao ?? 'Cobrança #' . $cobranca->id }}</strong>
                                        <div class="text-muted small">{{ $cobranca->created_at ? \Carbon\Carbon::parse($cobranca->created_at)->format('d/m/Y H:i') : '' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <strong>R$ {{ number_format((float)($cobranca->valor ?? 0), 2, ',', '.') }}</strong>
                                        @if(!empty($cobranca->status))
                                            <div><span class="badge badge-secondary">{{ $cobranca->status }}</span></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            {{-- ══════════ TAB 4 — FINANCEIRO ══════════ --}}
            <div class="tab-pane fade" id="pane-financeiro" role="tabpanel">
                <div class="rs-grid">
                    <div class="rs-main">

                        {{-- Transferências completas --}}
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-exchange-alt rs-card__icon"></i>
                                <h2>Todas as Transferencias</h2>
                                <span class="rs-card__badge">{{ $transacoesReserva->count() }}</span>
                            </div>

                            <div class="rs-finance-kpis">
                                <div class="rs-fkpi rs-fkpi--green">
                                    <i class="fas fa-arrow-down"></i>
                                    <div>
                                        <strong>R$ {{ number_format($totalPagamentos, 2, ',', '.') }}</strong>
                                        <span>{{ $transacoesPagamento->count() }} pagamento(s)</span>
                                    </div>
                                </div>
                                <div class="rs-fkpi rs-fkpi--red">
                                    <i class="fas fa-arrow-up"></i>
                                    <div>
                                        <strong>R$ {{ number_format($totalEstornos, 2, ',', '.') }}</strong>
                                        <span>{{ $transacoesEstorno->count() }} estorno(s)</span>
                                    </div>
                                </div>
                                @if($transacoesPendentes->count() > 0)
                                    <div class="rs-fkpi rs-fkpi--amber">
                                        <i class="fas fa-clock"></i>
                                        <div>
                                            <strong>{{ $transacoesPendentes->count() }}</strong>
                                            <span>Pendente(s)</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if($transacoesReserva->isNotEmpty())
                                <div class="rs-transactions">
                                    @foreach($transacoesReserva as $transacao)
                                        @php
                                            $valorTx  = (float) ($transacao->valor ?? 0);
                                            $statusTx = strtolower((string) ($transacao->status ?? ''));
                                            $isEstorno = $valorTx < 0 || in_array($statusTx, $statusPagamentoEstornado, true);
                                        @endphp
                                        @php
                                            $metodoPag   = strtolower($transacao->metodo_pagamento ?? '');
                                            $isDinheiro  = in_array($metodoPag, ['dinheiro','cash','especie','espécie']);
                                            $isPendente  = in_array($statusTx, ['pending','pendente','aguardando']);
                                            $jaConfirmado= !empty($transacao->confirmado_em);
                                            $podeConfirmar = $isDinheiro && $isPendente && !$jaConfirmado;
                                        @endphp
                                        <div class="rs-tx">
                                            <div class="rs-tx__icon {{ $isEstorno ? 'rs-tx__icon--red' : 'rs-tx__icon--green' }}">
                                                <i class="fas {{ $isEstorno ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            </div>
                                            <div class="rs-tx__info">
                                                <strong>{{ strtoupper($transacao->metodo_pagamento ?? 'Nao definido') }}</strong>
                                                <span>{{ \Illuminate\Support\Carbon::parse($transacao->created_at)->format('d/m/Y H:i') }}</span>
                                                @if($jaConfirmado)
                                                    <span class="rs-tx__confirmed">
                                                        <i class="fas fa-check-circle"></i>
                                                        Confirmado {{ \Carbon\Carbon::parse($transacao->confirmado_em)->format('d/m/Y H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="rs-tx__amount {{ $isEstorno ? 'rs-tx__amount--red' : 'rs-tx__amount--green' }}">
                                                <strong>R$ {{ number_format($valorTx, 2, ',', '.') }}</strong>
                                                <span>{{ $transacao->status ?? '-' }}</span>
                                                @if($podeConfirmar)
                                                    <button type="button"
                                                        class="btn btn-xs btn-success mt-1 btn-confirmar-dinheiro"
                                                        data-transacao-id="{{ $transacao->id }}"
                                                        data-reserva-id="{{ $reserva->id }}"
                                                        title="Confirmar recebimento em dinheiro">
                                                        <i class="fas fa-check mr-1"></i>Confirmar recebimento
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="rs-empty">
                                    <i class="fas fa-inbox"></i>
                                    <span>Nenhuma transferencia vinculada.</span>
                                </div>
                            @endif
                        </div>

                    </div>

                    <aside class="rs-sidebar">

                        {{-- Resumo financeiro detalhado --}}
                        <div class="rs-card">
                            <div class="rs-card__header">
                                <i class="fas fa-file-invoice-dollar rs-card__icon"></i>
                                <h2>Resumo Detalhado</h2>
                            </div>
                            <div class="rs-summary">
                                @if($grupoFrotaNome)
                                    <div class="rs-summary__row" style="color:#6b7280;font-size:.83rem">
                                        <span><i class="fas fa-layer-group mr-1"></i>Grupo</span>
                                        <span style="font-weight:600;color:#374151">{{ $grupoFrotaNome }}</span>
                                    </div>
                                    <div class="rs-summary__divider"></div>
                                @endif
                                <div class="rs-summary__row">
                                    <span>Tarifa diária</span>
                                    <span>R$ {{ number_format($tarifaBase, 2, ',', '.') }}</span>
                                </div>
                                <div class="rs-summary__row">
                                    <span>{{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }} &times; tarifa</span>
                                    <span>R$ {{ number_format($valorTarifaBase, 2, ',', '.') }}</span>
                                </div>
                                @if($valorOpcionais > 0)
                                    <div class="rs-summary__row">
                                        <span>Opcionais</span>
                                        <span>R$ {{ number_format($valorOpcionais, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($taxaColeta > 0)
                                    <div class="rs-summary__row" style="color:#92400e;background:#fef3c7;border-radius:5px;padding:3px 6px;margin:2px 0">
                                        <span><i class="fas fa-exclamation-triangle mr-1"></i>* Retirada fora do expediente</span>
                                        <span>R$ {{ number_format($taxaColeta, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($taxaEntrega > 0)
                                    <div class="rs-summary__row" style="color:#92400e;background:#fef3c7;border-radius:5px;padding:3px 6px;margin:2px 0">
                                        <span><i class="fas fa-exclamation-triangle mr-1"></i>* Devolução fora do expediente</span>
                                        <span>R$ {{ number_format($taxaEntrega, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($percentualDesconto > 0)
                                    <div class="rs-summary__divider"></div>
                                    <div class="rs-summary__row rs-summary__row--discount" style="background:#f0fdf4;border-radius:5px;padding:3px 6px;margin:2px 0;color:#16a34a">
                                        <span>
                                            <i class="fas fa-tag mr-1"></i>Desconto ({{ number_format($percentualDesconto, 0) }}%)
                                            @if($cupomCodigo)
                                                <span class="badge badge-xs ml-1" style="background:#16a34a;color:#fff;font-size:.68rem">{{ $cupomCodigo }}</span>
                                            @endif
                                        </span>
                                        <span>&minus; R$ {{ number_format($valorDesconto, 2, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="rs-summary__total">
                                    @if($percentualDesconto > 0)
                                        <span style="text-decoration:line-through;color:#9ca3af;font-size:.8rem;font-weight:400;margin-right:6px">R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                                    @endif
                                    <span>Total</span>
                                    <strong>R$ {{ number_format($valorTotal, 2, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>

                        {{-- NFS-e card completo --}}
                        <div class="rs-card">
                            <div class="rs-card__header rs-card__header--between">
                                <div class="rs-card__header-left">
                                    <i class="fas fa-file-alt rs-card__icon"></i>
                                    <h2>Nota Fiscal (NFS-e)</h2>
                                </div>
                                <span class="rs-status-badge rs-status-badge--sm {{ $nfseBadgeClass }}">{{ $nfseStatusLabel }}</span>
                            </div>

                            @if($nfseAtual)
                                <div class="rs-info-rows">
                                    <div class="rs-info-row">
                                        <span><i class="fas fa-hashtag"></i>Numero</span>
                                        <strong>{{ $nfseAtual->numero ?? '-' }}</strong>
                                    </div>
                                    <div class="rs-info-row">
                                        <span><i class="fas fa-server"></i>Ambiente</span>
                                        <strong>{{ $nfseAtual->ambiente ?? '-' }}</strong>
                                    </div>
                                </div>

                                @if($nfseErroDetalhado || $nfseMotivo)
                                    <div class="rs-nfse-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <p>{{ $nfseErroDetalhado ?? $nfseMotivo }}</p>
                                    </div>
                                @endif

                                @if(!empty($nfseAtual->nfse_id) && (auth()->user()?->hasPermissao('nfse.download') || auth()->user()?->isAdministrador()))
                                    <div class="rs-nfse-downloads">
                                        <a href="{{ route('nfse.download', ['id' => $nfseAtual->id, 'tipo' => 'xml']) }}" class="rs-btn rs-btn--outline">
                                            <i class="fas fa-download"></i>Baixar XML
                                        </a>
                                        <a href="{{ route('nfse.download', ['id' => $nfseAtual->id, 'tipo' => 'dps']) }}" class="rs-btn rs-btn--outline">
                                            <i class="fas fa-file-alt"></i>Baixar DPS
                                        </a>
                                    </div>
                                @endif

                                @if(($nfseAtual->status ?? '') !== 'emitida')
                                    <form method="POST" action="{{ route('reserva.emitirNfse', $reserva->id) }}" class="js-nfse-form mt-3">
                                        @csrf
                                        <button type="submit" class="rs-btn rs-btn--ghost rs-btn--full js-nfse-btn">
                                            <i class="fas fa-redo"></i>Tentar emitir novamente
                                        </button>
                                    </form>
                                @endif
                            @else
                                <div class="rs-empty">
                                    <i class="fas fa-file-times"></i>
                                    <span>Nenhuma nota vinculada ainda.</span>
                                </div>
                                <form method="POST" action="{{ route('reserva.emitirNfse', $reserva->id) }}" class="js-nfse-form mt-3">
                                    @csrf
                                    <button type="submit" class="rs-btn rs-btn--ghost rs-btn--full js-nfse-btn">
                                        <i class="fas fa-redo"></i>Emitir nota fiscal
                                    </button>
                                </form>
                            @endif
                        </div>

                    </aside>
                </div>
            </div>

            {{-- ══════════ TAB 5 — MULTAS ══════════ --}}
            <div class="tab-pane fade" id="pane-multas" role="tabpanel">
                <div class="rs-card">
                    <div class="rs-card__header rs-card__header--between">
                        <div class="rs-card__header-left">
                            <i class="fas fa-exclamation-triangle rs-card__icon text-danger"></i>
                            <h2>Multas da Reserva</h2>
                        </div>
                        <div style="display:flex;gap:8px;align-items:center">
                            @can('multa.create')
                            @if($pagamentoEfetuado)
                                <a href="{{ route('multa.create', ['reserva_id' => $reserva->id]) }}" class="rs-btn rs-btn--outline">
                                    <i class="fas fa-plus"></i> Nova multa
                                </a>
                            @else
                                <span class="rs-btn rs-btn--outline" style="opacity:.5;cursor:not-allowed;pointer-events:none" title="Pagamento necessário">
                                    <i class="fas fa-lock mr-1"></i> Nova multa
                                </span>
                            @endif
                            @endcan
                            @if($totalMultas > 0)
                            <a href="{{ route('multa.index', ['reserva_id' => $reserva->id]) }}" class="rs-btn rs-btn--outline">
                                <i class="fas fa-list"></i> Ver todas
                            </a>
                            @endif
                        </div>
                    </div>

                    @if($totalMultas > 0)
                        <div class="rs-mini-list">
                            @foreach($reserva->multas as $multaItem)
                            <div class="rs-mini-item">
                                <div>
                                    <strong>
                                        <a href="{{ route('multa.show', $multaItem->id) }}">#{{ $multaItem->id }}</a>
                                        — {{ $multaItem->orgao_autuador ?? 'Orgão não informado' }}
                                    </strong>
                                    @if(!empty($multaItem->infracao))
                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($multaItem->infracao, 100) }}</div>
                                    @endif
                                    @if(!empty($multaItem->data_infracao))
                                        <div class="text-muted small">
                                            <i class="fas fa-calendar mr-1"></i>
                                            {{ \Carbon\Carbon::parse($multaItem->data_infracao)->format('d/m/Y') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right" style="flex-shrink:0">
                                    <strong class="text-danger">R$ {{ number_format((float)$multaItem->valor_final, 2, ',', '.') }}</strong>
                                    @if(!empty($multaItem->status))
                                        <div><span class="badge badge-secondary">{{ $multaItem->status }}</span></div>
                                    @endif
                                    <div class="mt-1">
                                        <a href="{{ route('multa.show', $multaItem->id) }}" class="btn btn-xs btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rs-empty py-5">
                            <i class="fas fa-check-circle" style="font-size:2.5rem;color:#10b981"></i>
                            <span>Nenhuma multa registrada nesta reserva.</span>
                            @can('multa.create')
                            @if($pagamentoEfetuado)
                                <a href="{{ route('multa.create', ['reserva_id' => $reserva->id]) }}" class="rs-btn rs-btn--outline mt-2">
                                    <i class="fas fa-plus"></i> Registrar primeira multa
                                </a>
                            @else
                                <div class="mt-3 d-flex align-items-center justify-content-center" style="gap:.5rem;background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:.6rem 1rem;font-size:.83rem;color:#92400e;font-weight:600">
                                    <i class="fas fa-lock"></i> Pagamento necessário para registrar multas
                                </div>
                            @endif
                            @endcan
                        </div>
                    @endif
                </div>
            </div>

            {{-- ══════════ TAB 6 — HISTÓRICO ══════════ --}}
            <div class="tab-pane fade" id="pane-historico" role="tabpanel">
                <div class="rs-card rs-historico-card">
                    <div class="rs-card__header">
                        <i class="fas fa-history rs-card__icon"></i>
                        <h2>Histórico da Reserva</h2>
                        <span class="ml-auto badge badge-secondary" id="historicoCountPane">—</span>
                    </div>
                    <div id="historicoLoading" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Carregando histórico...
                    </div>
                    <div id="historicoTimeline" class="rs-timeline d-none"></div>
                </div>
            </div>

        </div>{{-- /tab-content --}}
    </div>{{-- /rs-tabs-wrapper --}}

    {{-- ── Modal Captura de Calção ── --}}
    @if(isset($calcao) && $calcao?->podeCapturar())
    <div class="modal fade" id="modalCapturarCalcao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-hand-holding-usd mr-1"></i> Capturar Calcao</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Valor a capturar (max R$ {{ number_format((float)$calcao->valor_autorizado, 2, ',', '.') }})</label>
                        <input type="number" step="0.01" id="valor_captura_input" class="form-control"
                               max="{{ $calcao->valor_autorizado }}" min="0.01"
                               value="{{ $calcao->valor_autorizado }}">
                    </div>
                    <div class="form-group">
                        <label>Justificativa <span class="text-danger">*</span></label>
                        <textarea id="justificativa_captura_input" class="form-control" rows="3" placeholder="Motivo da captura..."></textarea>
                    </div>
                    <div id="calcao-capture-status"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btn-confirmar-captura">
                        <i class="fas fa-check mr-1"></i> Confirmar Captura
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Calcao Presencial --}}
    @can('calcao.autorizar')
    <div class="modal fade" id="modalCalcaoPresencial" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-cash-register mr-1"></i> Calcao Presencial</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Metodo <span class="text-danger">*</span></label>
                        <select id="calcao_presencial_metodo" class="form-control">
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">Pix</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Valor (opcional)</label>
                        <input type="number" step="0.01" min="0.01" id="calcao_presencial_valor" class="form-control" placeholder="Deixe vazio para usar o valor calculado">
                    </div>
                    <div class="form-group mb-0">
                        <label>Observacao (opcional)</label>
                        <textarea id="calcao_presencial_obs" class="form-control" rows="3" placeholder="Ex.: Recebido no balcao"></textarea>
                    </div>
                    <div id="calcao-presencial-status" class="small mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btn-salvar-calcao-presencial">
                        <i class="fas fa-check mr-1"></i> Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- Modal Devolver Calcao Presencial --}}
    @if(isset($calcao) && empty($calcao->payment_intent_id) && ($calcao->status ?? '') !== 'cancelado')
    @can('calcao.cancelar')
    <div class="modal fade" id="modalDevolverCalcaoPresencial" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-hand-holding-heart mr-1"></i> Devolver Calcao Presencial</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-2">
                        Confirma a devolucao do calcao presencial? Esta acao registra uma transacao negativa.
                    </div>
                    <div class="form-group mb-0">
                        <label>Observacao (opcional)</label>
                        <textarea id="calcao_devolver_obs" class="form-control" rows="3" placeholder="Ex.: Devolvido no checkout"></textarea>
                    </div>
                    <div id="calcao-devolver-status" class="small mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btn-devolver-calcao-presencial">
                        <i class="fas fa-check mr-1"></i> Devolver
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endcan
    @endif

    {{-- ── Modal Detalhes do Historico ── --}}
    <div class="modal fade" id="modalDetalhesLog" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span id="logModalAcao"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                        <span class="mr-3 small"><i class="fas fa-user mr-1"></i> <strong id="logModalUsuario"></strong></span>
                        <span class="mr-3 small"><i class="fas fa-clock mr-1"></i> <span id="logModalData"></span></span>
                        <span class="ml-auto"><span class="badge" id="logModalNivel"></span></span>
                    </div>
                    <div id="logModalDetalhes"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <style>
        #logModalDetalhes .card { border-radius:8px; overflow:hidden; }
        #logModalDetalhes .card-header { font-size:.8rem; }
        #logModalDetalhes .table td { vertical-align:middle; }
        #logModalDetalhes del { color:#dc2626; text-decoration:line-through; }
        #logModalDetalhes ins { color:#16a34a; text-decoration:none; font-weight:700; }
    </style>

    {{-- Lightbox overlay --}}
    <div id="rs-lightbox" class="rs-lightbox d-none" role="dialog" aria-modal="true">
        <div class="rs-lightbox__backdrop" id="rs-lightbox-backdrop"></div>
        <div class="rs-lightbox__inner">
            <div class="rs-lightbox__label" id="rs-lightbox-label"></div>
            <img src="" alt="" id="rs-lightbox-img" class="rs-lightbox__img">
            <button class="rs-lightbox__close" id="rs-lightbox-close" aria-label="Fechar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

</div>
@stop

@push('js')
<script>
const csrfToken = @json(csrf_token());
const reservaId = @json($reserva->id);

// ── Liberar calção ──
function liberarCalcao(calcaoId) {
    if (!confirm('Confirma a liberacao do calcao? O valor sera devolvido ao cartao do cliente.')) return;
    fetch(@json(route('reserva.calcao.liberar', $reserva->id)), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ observacao: 'Liberado no check-out' })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) { location.reload(); }
        else alert(d.error ?? 'Erro ao liberar calcao.');
    });
}

// ── Capturar calção ──
// -- Calcao presencial --
document.getElementById('btn-salvar-calcao-presencial')?.addEventListener('click', function () {
    const metodo = document.getElementById('calcao_presencial_metodo')?.value;
    const valor  = document.getElementById('calcao_presencial_valor')?.value;
    const obs    = document.getElementById('calcao_presencial_obs')?.value;
    const statusEl = document.getElementById('calcao-presencial-status');

    if (!metodo) {
        if (statusEl) statusEl.textContent = 'Selecione um metodo.';
        return;
    }

    if (statusEl) statusEl.textContent = 'Registrando...';

    fetch(@json(route('reserva.calcao.presencial', $reserva->id)), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ metodo, valor: valor || null, observacao: obs || null })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else if (statusEl) statusEl.textContent = d.error ?? 'Erro ao registrar.';
    })
    .catch(() => { if (statusEl) statusEl.textContent = 'Erro ao registrar.'; });
});

document.getElementById('btn-devolver-calcao-presencial')?.addEventListener('click', function () {
    const obs = document.getElementById('calcao_devolver_obs')?.value;
    const statusEl = document.getElementById('calcao-devolver-status');
    if (statusEl) statusEl.textContent = 'Processando...';

    fetch(@json(route('reserva.calcao.presencial.devolver', $reserva->id)), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ observacao: obs || null })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
        else if (statusEl) statusEl.textContent = d.error ?? 'Erro ao devolver.';
    })
    .catch(() => { if (statusEl) statusEl.textContent = 'Erro ao devolver.'; });
});

document.getElementById('btn-confirmar-captura')?.addEventListener('click', function () {
    const valor = document.getElementById('valor_captura_input')?.value;
    const just  = document.getElementById('justificativa_captura_input')?.value;
    const statusEl = document.getElementById('calcao-capture-status');

    if (!just || just.trim().length < 5) {
        statusEl.innerHTML = '<div class="alert alert-warning">Informe uma justificativa de ao menos 5 caracteres.</div>';
        return;
    }

    this.disabled = true;
    fetch(@json(route('reserva.calcao.capturar', $reserva->id)), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ valor_captura: valor, justificativa: just })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            statusEl.innerHTML = '<div class="alert alert-success">Capturado com sucesso.</div>';
            setTimeout(() => location.reload(), 1500);
        } else {
            statusEl.innerHTML = '<div class="alert alert-danger">' + (d.error ?? 'Erro ao capturar.') + '</div>';
            this.disabled = false;
        }
    });
});
</script>
@endpush

@section('css')
<style>
    .content-wrapper {
        background: #f0f4f9;
    }

    /* ═══════════════════════════
       PAGE
    ═══════════════════════════ */
    .rs-page {
        display: grid;
        gap: 20px;
        padding-bottom: 32px;
    }

    /* ═══════════════════════════
       HERO
    ═══════════════════════════ */
    .rs-hero {
        position: relative;
        border-radius: 20px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.20);
    }

    .rs-hero__accent {
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    }

    .rs-hero--confirmada .rs-hero__accent {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .rs-hero--cancelada .rs-hero__accent {
        background: linear-gradient(90deg, #ef4444, #f87171);
    }

    .rs-hero--criada .rs-hero__accent,
    .rs-hero--pendente .rs-hero__accent {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
    }

    .rs-hero__body {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        padding: 28px 30px;
        flex-wrap: wrap;
    }

    .rs-hero__left {
        display: grid;
        gap: 8px;
    }

    .rs-hero__eyebrow {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: rgba(148, 163, 184, 0.9);
    }

    .rs-hero__title {
        margin: 0;
        font-size: 1.9rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #fff;
        line-height: 1.15;
    }

    .rs-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        color: rgba(148, 163, 184, 0.85);
        font-size: 0.84rem;
    }

    .rs-hero__meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .rs-hero__right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 16px;
    }

    .rs-hero__kpis {
        display: flex;
        align-items: center;
        gap: 0;
        background: rgba(255, 255, 255, 0.07);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        overflow: hidden;
    }

    .rs-kpi {
        padding: 14px 20px;
        text-align: center;
    }

    .rs-kpi span {
        display: block;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: rgba(148, 163, 184, 0.8);
        margin-bottom: 4px;
    }

    .rs-kpi strong {
        display: block;
        font-size: 1.1rem;
        font-weight: 800;
        color: #fff;
    }

    .rs-kpi--accent strong {
        color: #fbbf24;
    }

    .rs-kpi-divider {
        width: 1px;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
    }

    /* ═══════════════════════════
       STATUS BADGE
    ═══════════════════════════ */
    .rs-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .rs-status-badge--sm {
        padding: 5px 10px;
        font-size: 0.68rem;
    }

    .rs-status-badge.is-success {
        background: rgba(16, 185, 129, 0.15);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }

    .rs-status-badge.is-danger {
        background: rgba(239, 68, 68, 0.15);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .rs-status-badge.is-warning {
        background: rgba(245, 158, 11, 0.18);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .rs-status-badge.is-neutral {
        background: rgba(100, 116, 139, 0.14);
        color: #475569;
        border: 1px solid rgba(100, 116, 139, 0.2);
    }

    /* ═══════════════════════════
       ALERT
    ═══════════════════════════ */
    .rs-alert {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 16px;
        flex-wrap: wrap;
    }

    .rs-alert--warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
    }

    .rs-alert__icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #fef3c7;
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .rs-alert__content {
        flex: 1;
    }

    .rs-alert__content strong {
        display: block;
        color: #92400e;
        font-size: 0.88rem;
        margin-bottom: 2px;
    }

    .rs-alert__content p {
        margin: 0;
        color: #b45309;
        font-size: 0.82rem;
    }

    /* ═══════════════════════════
       TABS
    ═══════════════════════════ */
    .rs-tabs-wrapper {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
    }

    .rs-nav-tabs {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        padding: 0 16px;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .rs-nav-tabs::-webkit-scrollbar { display: none; }

    .rs-nav-tabs .nav-item {
        flex-shrink: 0;
    }

    .rs-nav-tabs .nav-link {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 14px 18px;
        font-size: 0.83rem;
        font-weight: 600;
        color: #64748b;
        border: none;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        white-space: nowrap;
        transition: color 0.15s, border-color 0.15s;
        margin-bottom: -2px;
    }

    .rs-nav-tabs .nav-link:hover {
        color: #2563eb;
        border-bottom-color: #bfdbfe;
        background: transparent;
    }

    .rs-nav-tabs .nav-link.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
        background: transparent;
        font-weight: 700;
    }

    .rs-nav-tabs .nav-link i {
        font-size: 0.85rem;
    }

    .rs-tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 800;
        line-height: 1;
    }

    .rs-tab-badge--green  { background: #dcfce7; color: #15803d; }
    .rs-tab-badge--amber  { background: #fef3c7; color: #b45309; }
    .rs-tab-badge--red    { background: #fee2e2; color: #dc2626; }
    .rs-tab-badge--slate  { background: #f1f5f9; color: #475569; }

    .rs-tab-content {
        padding: 24px;
    }

    /* ═══════════════════════════
       GRID (inside tabs)
    ═══════════════════════════ */
    .rs-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(290px, 0.75fr);
        gap: 20px;
        align-items: start;
    }

    .rs-main,
    .rs-sidebar {
        display: grid;
        gap: 16px;
    }

    /* Checkin/checkout single column layout */
    .rs-checkin-layout {
        display: grid;
        gap: 20px;
    }

    /* ═══════════════════════════
       CARDS
    ═══════════════════════════ */
    .rs-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px 22px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
    }

    .rs-card__header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-card__header--between {
        justify-content: space-between;
    }

    .rs-card__header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .rs-card__icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        flex-shrink: 0;
    }

    .rs-card__header h2 {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
    }

    .rs-card__badge {
        margin-left: auto;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 999px;
    }

    .rs-mini-list {
        display: grid;
        gap: 10px;
    }

    .rs-mini-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid #f1f5f9;
        border-radius: 14px;
        background: #fbfdff;
    }

    /* ═══════════════════════════
       ROTA
    ═══════════════════════════ */
    .rs-route {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        gap: 16px;
        align-items: center;
        background: linear-gradient(135deg, #f8faff 0%, #f0f4ff 100%);
        border: 1px solid #e2eaff;
        border-radius: 14px;
        padding: 20px;
    }

    .rs-route__stop {
        display: grid;
        gap: 10px;
    }

    .rs-route__stop--dest {
        text-align: right;
    }

    .rs-route__stop--dest .rs-route__dot {
        margin-left: auto;
    }

    .rs-route__dot {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .rs-route__dot--origin {
        background: #dcfce7;
        color: #16a34a;
    }

    .rs-route__dot--dest {
        background: #fee2e2;
        color: #dc2626;
    }

    .rs-route__label {
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #64748b;
    }

    .rs-route__place {
        display: block;
        font-size: 0.96rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }

    .rs-route__datetime {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .rs-route__stop--dest .rs-route__datetime {
        align-items: flex-end;
    }

    .rs-route__date {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e40af;
    }

    .rs-route__time {
        font-size: 0.78rem;
        color: #64748b;
    }

    .rs-route__middle {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 0 8px;
    }

    .rs-route__line {
        flex: 1;
        width: 1px;
        min-height: 16px;
        background: linear-gradient(180deg, #c7d7f4, #93c5fd);
    }

    .rs-route__duration {
        background: #1e40af;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 6px 12px;
        border-radius: 999px;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* ═══════════════════════════
       VEICULO
    ═══════════════════════════ */
    .rs-vehicle {
        display: grid;
        grid-template-columns: 220px minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }

    .rs-vehicle__media {
        background: linear-gradient(145deg, #f8fafc, #eef2ff);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 160px;
    }

    .rs-vehicle__media img {
        width: 100%;
        max-height: 170px;
        object-fit: contain;
        filter: drop-shadow(0 12px 20px rgba(15, 23, 42, 0.15));
    }

    .rs-vehicle__brand {
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .rs-vehicle__name {
        margin: 4px 0 12px;
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
    }

    .rs-vehicle__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 16px;
    }

    .rs-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .rs-chip--blue  { background: #eff6ff; color: #1d4ed8; }
    .rs-chip--slate { background: #f1f5f9; color: #475569; }
    .rs-chip--green { background: #f0fdf4; color: #15803d; }

    .rs-vehicle__specs {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .rs-spec {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .rs-spec i {
        color: #64748b;
        font-size: 0.8rem;
    }

    .rs-spec span {
        font-size: 0.72rem;
        color: #64748b;
    }

    .rs-spec strong {
        font-size: 0.9rem;
        color: #0f172a;
        font-weight: 700;
    }

    .rs-vehicle__pricing {
        display: flex;
        gap: 16px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }

    .rs-vehicle__pricing div {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .rs-vehicle__pricing span {
        font-size: 0.72rem;
        color: #64748b;
    }

    .rs-vehicle__pricing strong {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
    }

    /* ═══════════════════════════
       OPCIONAIS
    ═══════════════════════════ */
    .rs-optionals {
        display: grid;
        gap: 0;
    }

    .rs-optional-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-optional-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .rs-optional-row__info strong {
        display: block;
        font-size: 0.86rem;
        color: #0f172a;
        font-weight: 700;
    }

    .rs-optional-row__info span {
        font-size: 0.75rem;
        color: #64748b;
    }

    .rs-optional-row__price {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
    }

    /* ═══════════════════════════
       CLIENTE
    ═══════════════════════════ */
    .rs-client {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-client__avatar {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #fff;
        font-size: 1rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        letter-spacing: 0.04em;
    }

    .rs-client__main {
        min-width: 0;
    }

    .rs-client__main strong {
        display: block;
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rs-client__main span {
        display: block;
        font-size: 0.78rem;
        color: #64748b;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ═══════════════════════════
       INFO ROWS
    ═══════════════════════════ */
    .rs-info-rows {
        display: grid;
        gap: 0;
    }

    .rs-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-info-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .rs-info-row span {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: #64748b;
    }

    .rs-info-row span i {
        width: 14px;
        text-align: center;
        color: #94a3b8;
    }

    .rs-info-row strong {
        font-size: 0.82rem;
        font-weight: 600;
        color: #0f172a;
        text-align: right;
    }

    /* ═══════════════════════════
       RESUMO FINANCEIRO
    ═══════════════════════════ */
    .rs-summary {
        display: grid;
        gap: 0;
    }

    .rs-summary__row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
        font-size: 0.84rem;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-summary__row:last-of-type {
        border-bottom: 0;
    }

    .rs-summary__row--discount span,
    .rs-summary__row--discount {
        color: #16a34a;
    }

    .rs-summary__row--discount i {
        margin-right: 4px;
    }

    .rs-summary__divider {
        height: 1px;
        background: #e2e8f0;
        margin: 4px 0;
    }

    .rs-summary__total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 14px;
        margin-top: 10px;
        background: #0f172a;
        border-radius: 10px;
        color: #fff;
    }

    .rs-summary__total span {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: rgba(148, 163, 184, 0.9);
    }

    .rs-summary__total strong {
        font-size: 1.1rem;
        font-weight: 800;
        color: #fff;
    }

    /* ═══════════════════════════
       FINANCEIRO KPIs
    ═══════════════════════════ */
    .rs-finance-kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        gap: 8px;
        margin-bottom: 16px;
    }

    .rs-fkpi {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border-radius: 12px;
    }

    .rs-fkpi > i {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        flex-shrink: 0;
    }

    .rs-fkpi--green {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
    }

    .rs-fkpi--green > i {
        background: #dcfce7;
        color: #16a34a;
    }

    .rs-fkpi--red {
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    .rs-fkpi--red > i {
        background: #fee2e2;
        color: #dc2626;
    }

    .rs-fkpi--amber {
        background: #fffbeb;
        border: 1px solid #fde68a;
    }

    .rs-fkpi--amber > i {
        background: #fef3c7;
        color: #d97706;
    }

    .rs-fkpi div {
        min-width: 0;
    }

    .rs-fkpi strong {
        display: block;
        font-size: 0.88rem;
        font-weight: 800;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rs-fkpi span {
        display: block;
        font-size: 0.68rem;
        color: #64748b;
    }

    /* ═══════════════════════════
       TRANSACOES
    ═══════════════════════════ */
    .rs-transactions {
        display: grid;
        gap: 0;
    }

    .rs-tx {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .rs-tx:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .rs-tx__icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        flex-shrink: 0;
    }

    .rs-tx__icon--green {
        background: #dcfce7;
        color: #16a34a;
    }

    .rs-tx__icon--red {
        background: #fee2e2;
        color: #dc2626;
    }

    .rs-tx__info {
        flex: 1;
        min-width: 0;
    }

    .rs-tx__info strong {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: #0f172a;
    }

    .rs-tx__info span {
        display: block;
        font-size: 0.72rem;
        color: #64748b;
    }

    .rs-tx__amount {
        text-align: right;
    }

    .rs-tx__amount strong {
        display: block;
        font-size: 0.84rem;
        font-weight: 700;
    }

    .rs-tx__amount--green strong { color: #16a34a; }
    .rs-tx__amount--red   strong { color: #dc2626; }

    .rs-tx__amount span {
        display: block;
        font-size: 0.68rem;
        color: #94a3b8;
    }

    .rs-tx__confirmed {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.68rem;
        color: #16a34a;
        font-weight: 600;
        margin-top: 2px;
    }
    .rs-tx__confirmed i { font-size: .72rem; }

    /* ═══════════════════════════
       NFS-e
    ═══════════════════════════ */
    .rs-nfse-error {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 10px;
        padding: 12px;
        margin-top: 12px;
        font-size: 0.8rem;
        color: #9a3412;
    }

    .rs-nfse-error i {
        margin-top: 1px;
        flex-shrink: 0;
    }

    .rs-nfse-error p {
        margin: 0;
        line-height: 1.5;
    }

    .rs-nfse-downloads {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }

    /* ═══════════════════════════
       BUTTONS
    ═══════════════════════════ */
    .rs-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.18s;
    }

    .rs-btn--warning {
        background: #d97706;
        color: #fff;
    }

    .rs-btn--warning:hover {
        background: #b45309;
        color: #fff;
        text-decoration: none;
    }

    .rs-btn--outline {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #d4dde8;
    }

    .rs-btn--outline:hover {
        background: #f1f5f9;
        color: #0f172a;
        text-decoration: none;
    }

    .rs-btn--ghost {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #d4dde8;
    }

    .rs-btn--ghost:hover {
        background: #f1f5f9;
        color: #0f172a;
        text-decoration: none;
    }

    .rs-btn--full {
        width: 100%;
        justify-content: center;
    }

    /* ═══════════════════════════
       EMPTY STATE
    ═══════════════════════════ */
    .rs-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 20px;
        color: #94a3b8;
        text-align: center;
    }

    .rs-empty i {
        font-size: 1.8rem;
    }

    .rs-empty span {
        font-size: 0.8rem;
    }

    /* ── Card Contrato de Check-in ── */
    .rs-ci-card {
        border-radius: 18px;
        border: 1px solid rgba(0,0,0,.07);
        padding: 20px 24px;
        box-shadow: 0 4px 18px rgba(15,23,42,.07);
    }
    .rs-ci-card__top {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }
    .rs-ci-card__icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }
    .rs-ci-card__title {
        font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;
    }
    .rs-ci-card__badge {
        display: inline-flex; align-items: center;
        padding: 4px 12px; border-radius: 999px;
        font-size: .76rem; font-weight: 700;
    }
    .rs-ci-card__details {
        display: flex; flex-wrap: wrap; gap: 6px 0;
        margin-bottom: 16px;
        border-top: 1px solid rgba(0,0,0,.07);
        padding-top: 14px;
    }
    .rs-ci-detail {
        width: 50%; display: flex; flex-direction: column;
        padding: 4px 12px 4px 0;
    }
    .rs-ci-detail span { font-size: .75rem; color: #64748b; margin-bottom: 2px; }
    .rs-ci-detail strong { font-size: .88rem; color: #0f172a; }
    .rs-ci-card__actions {
        display: flex; flex-wrap: wrap; gap: 8px;
        border-top: 1px solid rgba(0,0,0,.07);
        padding-top: 14px;
    }
    .rs-ci-btn {
        display: inline-flex; align-items: center; gap: 7px;
        border: none; border-radius: 10px;
        padding: 9px 16px; font-size: .83rem; font-weight: 700;
        cursor: pointer; text-decoration: none;
        transition: opacity .15s, transform .15s;
    }
    .rs-ci-btn:hover { opacity:.82; transform:translateY(-1px); text-decoration:none; }
    .rs-ci-btn--view   { background:rgba(59,130,246,.13);  color:#1d4ed8; }
    .rs-ci-btn--sign   { background:rgba(16,185,129,.13);  color:#047857; }
    .rs-ci-btn--edit   { background:rgba(245,158,11,.14);  color:#92400e; }
    .rs-ci-btn--delete { background:rgba(239,68,68,.11);   color:#b91c1c; }

    /* ── Fotos check-in ── */
    .rs-photo-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .rs-photo-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .rs-photo-thumb {
        width: 100%;
        aspect-ratio: 4/3;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: transform 0.18s, box-shadow 0.18s;
    }

    .rs-photo-thumb:hover {
        transform: scale(1.03);
        box-shadow: 0 6px 20px rgba(15,23,42,0.15);
    }

    .rs-photo-placeholder {
        width: 100%;
        aspect-ratio: 4/3;
        border-radius: 12px;
        border: 2px dashed #e2e8f0;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: #cbd5e1;
        font-size: 0.75rem;
    }

    .rs-photo-placeholder i {
        font-size: 1.4rem;
    }

    .rs-photo-label {
        font-size: 0.74rem;
        font-weight: 600;
        color: #64748b;
        text-align: center;
    }

    /* ── Lightbox ── */
    .rs-lightbox {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .rs-lightbox__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.88);
        cursor: pointer;
    }

    .rs-lightbox__inner {
        position: relative;
        z-index: 1;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .rs-lightbox__img {
        max-width: 90vw;
        max-height: 80vh;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(0,0,0,0.5);
        object-fit: contain;
    }

    .rs-lightbox__label {
        color: #fff;
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 0.06em;
    }

    .rs-lightbox__close {
        position: absolute;
        top: -44px;
        right: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        border: none;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
    }

    .rs-lightbox__close:hover {
        background: rgba(255,255,255,0.3);
    }

    /* ── Danos AJAX containers ── */
    .rs-dano-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 12px;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        margin-bottom: 8px;
        background: #fbfdff;
    }

    .rs-dano-item:last-child { margin-bottom: 0; }

    .rs-dano-thumbs {
        margin-top: 8px;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .rs-dano-thumbs img {
        width: 54px;
        height: 54px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
    }

    /* ── Histórico ── */
    .rs-historico-card .rs-card__header { display:flex; align-items:center; gap:10px; user-select:none; }
    .rs-timeline { padding: 1rem 1.5rem; }
    .rs-tl-item {
        display: flex;
        gap: 14px;
        padding-bottom: 1.4rem;
        position: relative;
    }
    .rs-tl-item:not(:last-child)::before {
        content:'';
        position:absolute;
        left: 17px;
        top: 36px;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }
    .rs-tl-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        flex-shrink: 0;
        color: #fff;
    }
    .rs-tl-icon--info    { background: #3b82f6; }
    .rs-tl-icon--success { background: #10b981; }
    .rs-tl-icon--warning { background: #f59e0b; }
    .rs-tl-icon--danger  { background: #ef4444; }
    .rs-tl-body { flex:1; min-width:0; }
    .rs-tl-acao {
        font-size: .88rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }
    .rs-tl-meta {
        font-size: .76rem;
        color: #64748b;
        margin-bottom: 4px;
    }
    .rs-tl-detalhes {
        font-size: .78rem;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6px 10px;
        margin-top: 4px;
    }
    .rs-tl-table { width:100%; border-collapse:collapse; }
    .rs-tl-table tr + tr td { border-top: 1px solid #f1f5f9; }
    .rs-tl-table td { padding: 3px 8px 3px 0; vertical-align:top; font-size:.78rem; }
    .rs-tl-table__key { color:#94a3b8; white-space:nowrap; font-weight:600; width:35%; padding-right:10px; }
    .rs-tl-bloco { margin-top:6px; }
    .rs-tl-bloco__title { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:3px; padding-bottom:2px; border-bottom:1px solid #e2e8f0; }
    .rs-tl-pre { margin:0; font-size:.72rem; background:#f1f5f9; border-radius:4px; padding:3px 6px; white-space:pre-wrap; word-break:break-all; }
    .rs-tl-item--clickable { cursor:pointer; transition:background .15s ease; border-radius:8px; margin-left:-8px; padding-left:8px; padding-right:8px; }
    .rs-tl-item--clickable:hover { background:#f1f5f9; }
    .rs-tl-chevron { display:flex; align-items:center; color:#94a3b8; font-size:.75rem; flex-shrink:0; padding-left:4px; }
    .rs-tl-details-badge { display:inline-flex; align-items:center; color:#3b82f6; font-weight:600; }

    /* ── Contrato legado ── */
    .rs-contrato-badge {
        padding: 4px 10px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .rs-contrato-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .rs-contrato-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: none;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: .82rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: opacity .15s, transform .15s;
    }
    .rs-contrato-btn:hover { opacity:.85; transform:translateY(-1px); text-decoration:none; }
    .rs-contrato-btn--view  { background:rgba(59,130,246,.12);  color:#1d4ed8; }
    .rs-contrato-btn--sign  { background:rgba(16,185,129,.12);  color:#047857; }
    .rs-contrato-btn--edit  { background:rgba(245,158,11,.13);  color:#b45309; }
    .rs-contrato-btn--delete{ background:rgba(239,68,68,.10);   color:#b91c1c; }

    /* ═══════════════════════════
       RESPONSIVE
    ═══════════════════════════ */
    @media (max-width: 1199px) {
        .rs-grid {
            grid-template-columns: 1fr;
        }

        .rs-hero__body {
            flex-direction: column;
        }

        .rs-hero__right {
            align-items: flex-start;
        }
    }

    @media (max-width: 992px) {
        .rs-photo-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .rs-hero__body {
            padding: 20px;
        }

        .rs-hero__title {
            font-size: 1.5rem;
        }

        .rs-hero__kpis {
            width: 100%;
        }

        .rs-kpi {
            flex: 1;
        }

        .rs-vehicle {
            grid-template-columns: 1fr;
        }

        .rs-vehicle__media {
            max-width: 100%;
        }

        .rs-vehicle__specs {
            grid-template-columns: 1fr 1fr;
        }

        .rs-route {
            grid-template-columns: 1fr;
        }

        .rs-route__middle {
            flex-direction: row;
            padding: 0;
        }

        .rs-route__line {
            height: 1px;
            width: auto;
            flex: 1;
            min-height: auto;
            background: linear-gradient(90deg, #c7d7f4, #93c5fd);
        }

        .rs-route__stop--dest {
            text-align: left;
        }

        .rs-route__stop--dest .rs-route__dot {
            margin-left: 0;
        }

        .rs-route__stop--dest .rs-route__datetime {
            align-items: flex-start;
        }

        .rs-alert {
            flex-direction: column;
            align-items: flex-start;
        }

        .rs-btn--full {
            width: 100%;
        }

        .rs-tab-content {
            padding: 16px;
        }

        .rs-photo-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .rs-ci-detail { width: 100%; }
        .rs-ci-card__actions { flex-direction:column; }
        .rs-ci-btn { justify-content:center; }
    }

    @media (max-width: 480px) {
        .rs-photo-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const showToast = (title, text, icon) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ title, text, icon, timer: 4200, showConfirmButton: false, toast: true, position: 'top-end' });
                    return;
                }
                alert(text || title);
            };

            const flashSuccess = @json(session('success'));
            const flashWarning = @json(session('warning'));
            const flashError = @json(session('error'));

            if (flashSuccess) showToast('Reserva', flashSuccess, 'success');
            if (flashWarning) showToast('Reserva', flashWarning, 'warning');
            if (flashError) showToast('Reserva', flashError, 'error');

            const forms = document.querySelectorAll('.js-nfse-form');
            if (!forms.length) return;

            forms.forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const btn = form.querySelector('.js-nfse-btn');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Emitindo...';
                    }
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                            }
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Falha ao emitir a NFS-e.');
                        const icon = data.status === 'success' ? 'success' : 'warning';
                        showToast('NFS-e', data.message || 'Processo concluido.', icon);
                        if (data.status === 'success') setTimeout(() => window.location.reload(), 1300);
                    } catch (error) {
                        showToast('NFS-e', error.message || 'Falha ao emitir a NFS-e.', 'error');
                    } finally {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-redo"></i> Tentar emitir';
                        }
                    }
                });
            });
        })();
    </script>

    <script>
    (function () {
        const RESERVA_ID    = @json($reserva->id);
        const CSRF_TOKEN    = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const HISTORICO_URL = "{{ route('reserva.historico', $reserva->id) }}";
        @if($reserva->contratoCheckin)
        const VEICULO_ID    = @json($reserva->contratoCheckin->veiculo_id);
        const DANOS_EXIST_URL    = "{{ route('dano.obterDanosExistentes') }}";
        const DANOS_DETECT_URL   = "{{ route('dano.obterDanosDetectados') }}";
        @endif

        /* ── Tab link helper (cross-tab navigation) ── */
        document.querySelectorAll('.rs-tab-link').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const tabId = this.dataset.tab;
                const tabEl = document.getElementById(tabId);
                if (tabEl) $(tabEl).tab('show');
            });
        });

        /* ── Ícones por nível ── */
        const nivelIcone = {
            info:    'fas fa-info-circle',
            success: 'fas fa-check-circle',
            warning: 'fas fa-exclamation-circle',
            danger:  'fas fa-times-circle',
        };

        /* ── Abre modal com detalhes do log ── */
        function openLogModal(log) {
            const modal   = document.getElementById('modalDetalhesLog');
            if (!modal) return;

            document.getElementById('logModalAcao').textContent  = log.acao || '';
            document.getElementById('logModalUsuario').textContent = log.usuario || '';
            document.getElementById('logModalData').textContent    = log.data || '';
            document.getElementById('logModalNivel').textContent   = (log.nivel || 'info').toUpperCase();

            const badge = document.getElementById('logModalNivel');
            badge.className = 'badge badge-' + ({
                info: 'info', success: 'success', warning: 'warning', danger: 'danger',
            }[log.nivel] || 'secondary');

            const det = log.detalhes || {};
            const container = document.getElementById('logModalDetalhes');
            container.innerHTML = '';

            const BLOCK_KEYS = ['antes', 'depois', 'data', 'payload'];
            const hasBlocks = BLOCK_KEYS.some(function (k) { return typeof det[k] === 'object' && det[k] !== null; });

            const entries = Object.entries(det).filter(function (_ref) { var k = _ref[0]; return k !== 'acao'; });

            if (!entries.length) {
                container.innerHTML = '<p class="text-muted text-center mb-0"><i class="fas fa-info-circle mr-1"></i>Nenhum detalhe adicional.</p>';
            } else {
                entries.forEach(function (_ref2) {
                    var k = _ref2[0], v = _ref2[1];
                    var label = k.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });

                    if (BLOCK_KEYS.includes(k) && typeof v === 'object' && v !== null) {
                        var card = document.createElement('div');
                        card.className = 'card mb-3 border';
                        card.innerHTML = '<div class="card-header py-2 px-3 font-weight-bold small bg-light">' + label + '</div>';
                        var body = document.createElement('div');
                        body.className = 'card-body p-0';

                        if (Array.isArray(v)) {
                            body.innerHTML = '<div class="p-2"><pre class="mb-0 small" style="max-height:200px;overflow:auto;white-space:pre-wrap">' + JSON.stringify(v, null, 2) + '</pre></div>';
                        } else {
                            var table = document.createElement('table');
                            table.className = 'table table-sm table-bordered mb-0 small';
                            var tbody = document.createElement('tbody');
                            Object.entries(v).forEach(function (_ref3) {
                                var sk = _ref3[0], sv = _ref3[1];
                                var slabel = sk.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
                                var sval = (sv === null || sv === undefined) ? '—' : (typeof sv === 'object' ? JSON.stringify(sv) : sv);
                                var tr = document.createElement('tr');
                                tr.innerHTML = '<td class="font-weight-bold text-nowrap" style="width:35%">' + slabel + '</td><td>' + sval + '</td>';
                                tbody.appendChild(tr);
                            });
                            table.appendChild(tbody);
                            body.appendChild(table);
                        }
                        card.appendChild(body);
                        container.appendChild(card);

                        if (k === 'antes' && typeof det.depois === 'object' && det.depois !== null) {
                            var diffCard = document.createElement('div');
                            diffCard.className = 'card mb-3 border border-success';
                            diffCard.innerHTML = '<div class="card-header py-2 px-3 font-weight-bold small bg-success text-white"><i class="fas fa-exchange-alt mr-1"></i>Comparacao (antes vs depois)</div>';
                            var diffBody = document.createElement('div');
                            diffBody.className = 'card-body p-0';
                            var diffTable = document.createElement('table');
                            diffTable.className = 'table table-sm table-bordered mb-0 small';
                            var diffThead = document.createElement('thead');
                            diffThead.className = 'thead-light';
                            diffThead.innerHTML = '<tr><th style="width:30%">Campo</th><th style="width:35%">Antes</th><th style="width:35%">Depois</th></tr>';
                            diffTable.appendChild(diffThead);
                            var diffTbody = document.createElement('tbody');
                            var allKeys = new Set([].concat(Object.keys(v), Object.keys(det.depois)));
                            allKeys.forEach(function (sk) {
                                var svBefore = v[sk];
                                var svAfter  = det.depois[sk];
                                var sBefore = (svBefore === null || svBefore === undefined) ? '—' : (typeof svBefore === 'object' ? JSON.stringify(svBefore) : String(svBefore));
                                var sAfter  = (svAfter === null || svAfter === undefined) ? '—' : (typeof svAfter === 'object' ? JSON.stringify(svAfter) : String(svAfter));
                                var slabel  = sk.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
                                var changed = String(svBefore) !== String(svAfter);
                                var tr = document.createElement('tr');
                                tr.innerHTML = '<td class="font-weight-bold">' + slabel + '</td>'
                                    + '<td class="' + (changed ? 'text-danger' : '') + '">' + (changed ? '<del>' + sBefore + '</del>' : sBefore) + '</td>'
                                    + '<td class="' + (changed ? 'text-success font-weight-bold' : '') + '">' + (changed ? '<ins>' + sAfter + '</ins>' : sAfter) + '</td>';
                                diffTbody.appendChild(tr);
                            });
                            diffTable.appendChild(diffTbody);
                            diffBody.appendChild(diffTable);
                            diffCard.appendChild(diffBody);
                            container.appendChild(diffCard);
                        }
                    } else {
                        var row = document.createElement('div');
                        row.className = 'd-flex justify-content-between align-items-start py-1 px-3 border-bottom small';
                        row.innerHTML = '<span class="font-weight-bold text-nowrap mr-2">' + label + ':</span><span class="text-right text-break">' + formatLogValue(v) + '</span>';
                        container.appendChild(row);
                    }
                });
            }

            $(modal).modal('show');
        }

        function formatLogValue(v) {
            if (v === null || v === undefined) return '<span class="text-muted">—</span>';
            if (typeof v === 'boolean') return v ? '<span class="text-success">Sim</span>' : '<span class="text-danger">Nao</span>';
            if (typeof v === 'object') return '<pre class="mb-0 small" style="max-height:120px;overflow:auto;white-space:pre-wrap">' + JSON.stringify(v, null, 2) + '</pre>';
            return String(v);
        }

        /* ── Render timeline ── */
        function renderHistorico(logs) {
            const container = document.getElementById('historicoTimeline');
            const countEl   = document.getElementById('historicoCount');
            const countPaneEl = document.getElementById('historicoCountPane');
            if (countEl) countEl.textContent = logs.length;
            if (countPaneEl) countPaneEl.textContent = logs.length;

            if (!logs.length) {
                container.innerHTML = '<p class="text-muted text-center py-3"><i class="fas fa-inbox mr-1"></i>Nenhum registro encontrado.</p>';
                container.classList.remove('d-none');
                return;
            }

            function hasDetalhes(det) {
                return det && typeof det === 'object' && Object.keys(det).length > 0;
            }

            container.innerHTML = logs.map(function (log, idx) {
                const nivel    = log.nivel || 'info';
                const icon     = nivelIcone[nivel] || nivelIcone.info;
                const det      = log.detalhes || {};
                const hasDet   = hasDetalhes(det);
                const detCount = hasDet ? Object.keys(det).length : 0;
                const logJson  = hasDet ? JSON.stringify(log).replace(/"/g, '&quot;') : '';

                return `
                <div class="rs-tl-item ${hasDet ? 'rs-tl-item--clickable' : ''}" ${hasDet ? 'data-log=\'' + logJson + '\'' : ''}>
                    <div class="rs-tl-icon rs-tl-icon--${nivel}"><i class="${icon}"></i></div>
                    <div class="rs-tl-body">
                        <div class="rs-tl-acao">${log.acao || 'Ação registrada'}</div>
                        <div class="rs-tl-meta">
                            <i class="fas fa-user mr-1"></i>${log.usuario}
                            &nbsp;&middot;&nbsp;
                            <i class="fas fa-clock mr-1"></i>${log.data}
                            ${hasDet ? '&nbsp;&middot;&nbsp;<span class="rs-tl-details-badge"><i class="fas fa-list mr-1"></i>' + detCount + ' detalhe(s)</span>' : ''}
                        </div>
                    </div>
                    ${hasDet ? '<div class="rs-tl-chevron"><i class="fas fa-chevron-right"></i></div>' : ''}
                </div>`;
            }).join('');

            container.querySelectorAll('.rs-tl-item--clickable').forEach(function (el) {
                el.addEventListener('click', function () {
                    try {
                        var logData = JSON.parse(this.dataset.log);
                        openLogModal(logData);
                    } catch (e) {
                        // ignore parse errors
                    }
                });
            });

            container.classList.remove('d-none');
        }

        /* ── Carregar histórico ── */
        let historicoCarregado = false;
        function carregarHistorico() {
            if (historicoCarregado) return;
            historicoCarregado = true;
            document.getElementById('historicoLoading')?.classList.remove('d-none');

            fetch(HISTORICO_URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('historicoLoading')?.classList.add('d-none');
                    renderHistorico(data.logs || []);
                })
                .catch(() => {
                    const el = document.getElementById('historicoLoading');
                    if (el) el.textContent = 'Erro ao carregar histórico.';
                });
        }

        /* ── Auto-load histórico when tab opened ── */
        document.getElementById('tab-historico')?.addEventListener('shown.bs.tab', carregarHistorico);
        // jQuery fallback for AdminLTE Bootstrap 4
        $('#tab-historico').on('shown.bs.tab', carregarHistorico);

        @if($reserva->contratoCheckin)
        /* ── Carregar danos (check-in tab) ── */
        let danosCarregados = false;

        function renderDanos(data, containerId, loadingId) {
            const loading   = document.getElementById(loadingId);
            const container = document.getElementById(containerId);
            if (loading) loading.classList.add('d-none');
            if (!container) return;

            const items = data.danos || data || [];
            if (!items.length) {
                container.innerHTML = '<div class="rs-empty"><i class="fas fa-check-circle" style="color:#10b981"></i><span>Nenhum dano registrado.</span></div>';
            } else {
                container.innerHTML = items.map(function (d) {
                    const imagens = Array.isArray(d.imagens) ? d.imagens : [];
                    const thumbs = imagens
                        .map(function (img) {
                            const url = img.url || (img.path ? ('/storage/' + img.path) : null);
                            if (!url) return '';
                            return `<a href="${url}" target="_blank" rel="noopener"><img src="${url}" alt="dano"></a>`;
                        })
                        .filter(Boolean)
                        .join('');

                    const thumbsHtml = thumbs ? `<div class="rs-dano-thumbs">${thumbs}</div>` : '';
                    const titulo = d.localizacao || d.local_dano || d.localizacao_problema || '—';
                    const desc = d.descricao || d.observacoes || d.tipo_dano || '';
                    const custo = d.custo_estimado !== undefined && d.custo_estimado !== null
                        ? `<strong>R$ ${parseFloat(d.custo_estimado || 0).toFixed(2).replace('.', ',')}</strong>`
                        : '';

                    return `<div class="rs-dano-item">
                        <div>
                            <strong>${titulo}</strong>
                            <div class="text-muted small">${desc}</div>
                            ${thumbsHtml}
                        </div>
                        <div class="text-right" style="flex-shrink:0">
                            ${custo}
                        </div>
                    </div>`;
                }).join('');
            }
            container.classList.remove('d-none');
        }

        function carregarDanos() {
            if (danosCarregados) return;
            danosCarregados = true;

            // Danos existentes
            fetch(DANOS_EXIST_URL + '?veiculo_id=' + VEICULO_ID + '&reserva_id=' + RESERVA_ID, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
            })
            .then(r => r.json())
            .then(data => renderDanos(data, 'danos-existentes-container', 'danos-existentes-loading'))
            .catch(() => {
                const el = document.getElementById('danos-existentes-loading');
                if (el) el.textContent = 'Erro ao carregar danos existentes.';
            });

            // Danos detectados
            fetch(DANOS_DETECT_URL + '?veiculo_id=' + VEICULO_ID + '&reserva_id=' + RESERVA_ID, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
            })
            .then(r => r.json())
            .then(data => renderDanos(data, 'danos-detectados-container', 'danos-detectados-loading'))
            .catch(() => {
                const el = document.getElementById('danos-detectados-loading');
                if (el) el.textContent = 'Erro ao carregar danos detectados.';
            });
        }

        document.getElementById('tab-checkin')?.addEventListener('shown.bs.tab', carregarDanos);
        $('#tab-checkin').on('shown.bs.tab', carregarDanos);
        @endif

        /* ── Excluir contrato check-in ── */
        document.querySelectorAll('.js-excluir-contrato-checkin').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const url       = this.dataset.url;
                const assinado  = this.dataset.assinado === '1';
                const msg = assinado
                    ? 'Este contrato <strong>já foi assinado</strong>. Ao excluir você perderá a assinatura e precisará assinar novamente. Deseja continuar?'
                    : 'O contrato de check-in será excluído. Deseja continuar?';

                Swal.fire({
                    title: 'Excluir contrato de check-in?',
                    html: msg,
                    icon: assinado ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sim, excluir',
                    cancelButtonText: 'Cancelar',
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="${CSRF_TOKEN}">
                        <input type="hidden" name="_method" value="DELETE">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                });
            });
        });

        /* ── Lightbox ── */
        const lightbox     = document.getElementById('rs-lightbox');
        const lightboxImg  = document.getElementById('rs-lightbox-img');
        const lightboxLbl  = document.getElementById('rs-lightbox-label');
        const lightboxClose = document.getElementById('rs-lightbox-close');
        const lightboxBack  = document.getElementById('rs-lightbox-backdrop');

        function openLightbox(src, label) {
            if (!lightbox) return;
            lightboxImg.src = src;
            lightboxLbl.textContent = label || '';
            lightbox.classList.remove('d-none');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            if (!lightbox) return;
            lightbox.classList.add('d-none');
            lightboxImg.src = '';
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.js-lightbox-trigger').forEach(function (el) {
            el.addEventListener('click', function () {
                openLightbox(this.dataset.src, this.dataset.label);
            });
        });

        lightboxClose?.addEventListener('click', closeLightbox);
        lightboxBack?.addEventListener('click', closeLightbox);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeLightbox();
        });

    })();

    // ── Confirmar pagamento em dinheiro ──
    document.querySelectorAll('.btn-confirmar-dinheiro').forEach(function(btn) {
        btn.addEventListener('click', async function() {
            const transacaoId = this.dataset.transacaoId;
            const reservaId   = this.dataset.reservaId;

            const conf = await Swal.fire({
                title: 'Confirmar recebimento?',
                text: 'Confirma que o pagamento em dinheiro foi recebido?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, confirmar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#16a34a',
            });

            if (!conf.isConfirmed) return;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const response = await fetch(`/reserva/${reservaId}/confirmar-pagamento-dinheiro`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ transacao_id: transacaoId }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.fire({ title: 'Confirmado!', text: data.message, icon: 'success', timer: 2500, showConfirmButton: false });
                    setTimeout(() => location.reload(), 2600);
                } else {
                    Swal.fire({ title: 'Erro', text: data.error || 'Falha ao confirmar.', icon: 'error' });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check mr-1"></i>Confirmar recebimento';
                }
            } catch(e) {
                Swal.fire({ title: 'Erro de rede', text: 'Tente novamente.', icon: 'error' });
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check mr-1"></i>Confirmar recebimento';
            }
        });
    });
    </script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'reserva-show';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.rs-hero', popover: { title: 'Cabeçalho da reserva', description: 'Aqui ficam as informações principais: número da reserva, cliente, empresa, data de criação, status atual, valor total e duração.', side: 'bottom' } },
        { element: '.rs-status-badge', popover: { title: 'Status da reserva', description: 'Indica a situação atual: Criada, Confirmada, Cancelada, etc. O status muda conforme o fluxo de check-in e checkout é realizado.', side: 'left' } },
        { element: '.rs-hero__kpis', popover: { title: 'Indicadores financeiros', description: 'Mostra o valor total da reserva, a duração em dias e o percentual de desconto aplicado, quando houver.', side: 'bottom' } },
        { element: '.rs-nav-tabs', popover: { title: 'Abas de detalhes', description: 'Navegue pelas abas para acessar: Visão Geral, Check-in (contrato), Check-out (danos e devoluções), Pagamentos, NFS-e, Multas e Caução.', side: 'bottom' } },
        { element: '#tab-visao-geral', popover: { title: 'Visão Geral', description: 'Exibe os dados completos da reserva: cliente, veículo, datas, opcionais contratados e valores detalhados.', side: 'bottom' } },
        { element: '#tab-checkin', popover: { title: 'Check-in', description: 'Gerencie o contrato de check-in. Você pode gerar, enviar para assinatura e acompanhar o status do documento.', side: 'bottom' } },
        { element: '#tab-checkout', popover: { title: 'Check-out', description: 'Registre a devolução do veículo, danos identificados e cobranças por avarias.', side: 'bottom' } },
        { element: '.rs-actions-bar', popover: { title: 'Painel de ações', description: 'Botões para as principais operações: confirmar, cancelar, processar pagamento, emitir NFS-e, iniciar check-in e checkout.', side: 'bottom' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    }

    function buildTour() {
        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            allowClose: true,
            progressText: 'Passo @{{current}} de @{{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn'; btnTour.type = 'button'; btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () { _pulou = true; marcarFeito(); driver.destroy(); });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn'; btnStep.type = 'button'; btnStep.textContent = 'Pular passo';
                btnStep.addEventListener('click', function () { driver.moveNext(); });
                var nav = popover.footer.querySelector('.driver-popover-navigation-btns');
                if (nav) { nav.prepend(btnStep); }
                popover.footer.prepend(btnTour);
            },
            steps: steps,
        });
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(() => buildTour().drive());
    };

    if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) { localStorage.setItem(CACHE_KEY, '1'); return; }
            setTimeout(() => buildTour().drive(), 350);
        });
})();
</script>
@stop
