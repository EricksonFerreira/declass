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
    $valorDesconto      = ($valorTarifaBase + $valorOpcionais) * ($percentualDesconto / 100);
    $valorTotal         = max(0, ($valorTarifaBase + $valorOpcionais) - $valorDesconto);

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
         GRID PRINCIPAL
    ══════════════════════════════════════════ --}}
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
                    <div class="rs-summary__row">
                        <span>Tarifa diaria</span>
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
                    @if($percentualDesconto > 0)
                        <div class="rs-summary__divider"></div>
                        <div class="rs-summary__row rs-summary__row--discount">
                            <span><i class="fas fa-tag"></i>Desconto ({{ number_format($percentualDesconto, 0) }}%)</span>
                            <span>&minus; R$ {{ number_format($valorDesconto, 2, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="rs-summary__total">
                        <span>Total</span>
                        <strong>R$ {{ number_format($valorTotal, 2, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            {{-- Card: Transferencias financeiras --}}
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
                            <div class="rs-tx">
                                <div class="rs-tx__icon {{ $isEstorno ? 'rs-tx__icon--red' : 'rs-tx__icon--green' }}">
                                    <i class="fas {{ $isEstorno ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                </div>
                                <div class="rs-tx__info">
                                    <strong>{{ strtoupper($transacao->metodo_pagamento ?? 'Nao definido') }}</strong>
                                    <span>{{ \Illuminate\Support\Carbon::parse($transacao->created_at)->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="rs-tx__amount {{ $isEstorno ? 'rs-tx__amount--red' : 'rs-tx__amount--green' }}">
                                    <strong>R$ {{ number_format($valorTx, 2, ',', '.') }}</strong>
                                    <span>{{ $transacao->status ?? '-' }}</span>
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

            {{-- Card: NFS-e --}}
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

            {{-- ── Card: Calção (Feature 4) ── --}}
            @php $calcao = $reserva->calcao ?? null; @endphp
            @if($calcao || ($reserva->contratoCheckin && $reserva->contratoCheckin->assinado === 'S'))
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
                @else
                    @can('calcao.autorizar')
                    <div class="text-center py-2">
                        <a href="{{ route('reserva.calcao', $reserva->id) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-lock mr-1"></i> Registrar Calcao
                        </a>
                    </div>
                    @endcan
                @endif
            </div>
            @endif

            {{-- ── Card: Upgrade de Categoria (Feature 1) ── --}}
            @if($reserva->contratoCheckin && $reserva->contratoCheckin->houve_upgrade_categoria)
            <div class="rs-card" style="border-left:4px solid #fd7e14">
                <div class="rs-card__header">
                    <i class="fas fa-arrow-circle-up rs-card__icon" style="color:#fd7e14"></i>
                    <h2>Upgrade de Categoria</h2>
                </div>
                <div class="rs-info-rows">
                    <div class="rs-info-row">
                        <span>Categoria Reservada</span>
                        <strong>{{ $reserva->contratoCheckin->grupoFrotaOriginal?->nome ?? 'Grupo #' . $reserva->contratoCheckin->grupo_frota_original_id }}</strong>
                    </div>
                    <div class="rs-info-row">
                        <span>Veiculo Entregue</span>
                        <strong>{{ $reserva->contratoCheckin->veiculo?->nome ?? '—' }}</strong>
                    </div>
                    <div class="rs-info-row">
                        <span>Motivo</span>
                        <strong>{{ $reserva->contratoCheckin->motivo_upgrade ?? '—' }}</strong>
                    </div>
                    <div class="rs-info-row">
                        <span>Operador</span>
                        <strong>{{ $reserva->contratoCheckin->usuarioUpgrade?->nome ?? '—' }}</strong>
                    </div>
                    <div class="rs-info-row">
                        <span>Data/Hora</span>
                        <strong>{{ $reserva->contratoCheckin->data_hora_upgrade?->format('d/m/Y H:i') ?? '—' }}</strong>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Card: Multas da reserva ── --}}
            @php $totalMultas = $reserva->multas?->count() ?? 0; @endphp
            @if($totalMultas > 0)
            <div class="rs-card">
                <div class="rs-card__header">
                    <i class="fas fa-exclamation-triangle rs-card__icon text-danger"></i>
                    <h2>Multas ({{ $totalMultas }})</h2>
                </div>
                <div class="rs-info-rows">
                    @foreach($reserva->multas->take(3) as $multaItem)
                    <div class="rs-info-row">
                        <span>
                            <a href="{{ route('multa.show', $multaItem->id) }}">#{{ $multaItem->id }}</a>
                            — {{ $multaItem->orgao_autuador ?? 'S/O' }}
                        </span>
                        <strong>R$ {{ number_format((float)$multaItem->valor_final, 2, ',', '.') }}</strong>
                    </div>
                    @endforeach
                </div>
                <div class="text-center mt-1">
                    <a href="{{ route('multa.index', ['reserva_id' => $reserva->id]) }}" class="btn btn-xs btn-outline-danger">
                        Ver todas
                    </a>
                    @can('multa.create')
                    <a href="{{ route('multa.create', ['reserva_id' => $reserva->id]) }}" class="btn btn-xs btn-outline-secondary">
                        + Nova multa
                    </a>
                    @endcan
                </div>
            </div>
            @endif

        </aside>
    </div>

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
       GRID
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
    }
</style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const forms = document.querySelectorAll('.js-nfse-form');

            if (!forms.length) return;

            const showToast = (title, text, icon) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ title, text, icon, timer: 4200, showConfirmButton: false, toast: true, position: 'top-end' });
                    return;
                }
                alert(text || title);
            };

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
@stop
