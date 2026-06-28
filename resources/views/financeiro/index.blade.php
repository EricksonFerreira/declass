@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <style>
        .kpi-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
        }

        .kpi-icon {
            font-size: 2rem;
            opacity: 0.15;
            position: absolute;
            right: 20px;
            top: 20px;
        }

        .table-modern {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .table-modern thead th {
            background: #f8f9fa;
            border-top: none;
            border-bottom: 2px solid #edf2f7;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #6c757d;
        }

        .table-modern td {
            vertical-align: middle;
            font-size: 0.85rem;
            border-top: 1px solid #edf2f7;
            padding-top: 0.4rem !important;
            padding-bottom: 0.4rem !important;
        }

        .table-modern td small {
            line-height: 1.1;
        }

        .badge-modern {
            padding: 0.4em 0.8em;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .filter-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .text-muted-xs {
            font-size: 0.75rem;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .val-danger {
            color: #e53e3e;
        }

        .val-success {
            color: #38a169;
        }
    </style>

    <!-- Filters -->
    <div class="card filter-card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('financeiro.index') }}" class="mb-0">
                <div class="row align-items-end gx-2 gy-2">
                    <div class="col-xl-auto col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label class="text-muted-xs font-weight-bold mb-1">Reserva Ref.</label>
                        <input type="number" name="reserva_id" class="form-control form-control-sm"
                            value="{{ $filters['reserva_id'] ?? '' }}" placeholder="Ex: 123">
                    </div>
                    <div class="col-xl-auto col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label class="text-muted-xs font-weight-bold mb-1">Cliente</label>
                        <input type="text" name="cliente" class="form-control form-control-sm"
                            value="{{ $filters['cliente'] ?? '' }}" placeholder="Nome do titular">
                    </div>
                    <div class="col-xl-auto col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label class="text-muted-xs font-weight-bold mb-1">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Status (Todos)</option>
                            @foreach ($statusDisponiveis as $status)
                                <option value="{{ $status }}"
                                    {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-auto col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label class="text-muted-xs font-weight-bold mb-1">Origem / Canal</label>
                        <select name="canal" class="form-control form-control-sm">
                            <option value="">Origem (Todas)</option>
                            <option value="reserva" {{ ($filters['canal'] ?? '') === 'reserva' ? 'selected' : '' }}>Reserva
                                Master</option>
                            <option value="multa" {{ ($filters['canal'] ?? '') === 'multa' ? 'selected' : '' }}>Multa
                            </option>
                            <option value="sinistro" {{ ($filters['canal'] ?? '') === 'sinistro' ? 'selected' : '' }}>
                                Sinistro</option>
                            <option value="calcao" {{ ($filters['canal'] ?? '') === 'calcao' ? 'selected' : '' }}>Calção
                            </option>
                        </select>
                    </div>
                    <div class="col-xl-auto col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label class="text-muted-xs font-weight-bold mb-1">Gateway</label>
                        <select name="metodo_pagamento" class="form-control form-control-sm">
                            <option value="">Gateway (Todos)</option>
                            @foreach ($metodosDisponiveis as $metodo)
                                <option value="{{ $metodo }}"
                                    {{ ($filters['metodo_pagamento'] ?? '') === $metodo ? 'selected' : '' }}>
                                    {{ ucfirst($metodo) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-auto col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label class="text-muted-xs font-weight-bold mb-1">Data Inicio</label>
                        <input type="date" name="data_inicio" class="form-control form-control-sm"
                            value="{{ $filters['data_inicio'] ?? '' }}">
                    </div>
                    <div class="col-xl-auto col-lg-2 col-md-3 col-sm-6 mb-2">
                        <label class="text-muted-xs font-weight-bold mb-1">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control form-control-sm"
                            value="{{ $filters['data_fim'] ?? '' }}">
                    </div>
                    <div class="col-xl-auto col-lg-12 col-md-12 mb-2 d-flex align-items-end mt-2 mt-xl-0">
                        <button class="btn btn-primary btn-sm px-3 shadow-sm mr-2 w-100 font-weight-bold" type="submit">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('financeiro.index') }}"
                            class="btn btn-light border btn-sm shadow-sm w-100 text-muted text-center" title="Limpar">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card kpi-card h-100 bg-white">
                <div class="card-body position-relative">
                    <div class="kpi-icon text-dark"><i class="fas fa-wallet"></i></div>
                    <p class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 0.70rem;">Faturamento Bruto
                    </p>
                    <h3 class="mb-0 text-dark font-weight-bold">{{ $moedaEmpresa }}
                        {{ number_format($totalFaturado, 2, ',', '.') }}</h3>
                    <small class="text-muted">{{ $qtdTransacoes }} transacoes computadas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card kpi-card h-100 bg-white">
                <div class="card-body position-relative">
                    <div class="kpi-icon text-success"><i class="fas fa-check-circle"></i></div>
                    <p class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 0.70rem;">Receita Aprovada
                    </p>
                    <h3 class="mb-0 text-success font-weight-bold">{{ $moedaEmpresa }}
                        {{ number_format($totalAprovado, 2, ',', '.') }}</h3>
                    <small class="text-muted">Desempenho aprov.: {{ number_format($taxaAprovacao, 1, ',', '.') }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card kpi-card h-100 bg-white">
                <div class="card-body position-relative">
                    <div class="kpi-icon text-warning"><i class="fas fa-hourglass-half"></i></div>
                    <p class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 0.70rem;">Pendentes /
                        Falhas</p>
                    <h3 class="mb-0 text-warning font-weight-bold">{{ $moedaEmpresa }}
                        {{ number_format($totalPendente + $totalFalha, 2, ',', '.') }}</h3>
                    <small class="text-muted">Aguardando liquidacao</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="card kpi-card h-100 bg-white">
                <div class="card-body position-relative">
                    <div class="kpi-icon text-danger"><i class="fas fa-ban"></i></div>
                    <p class="text-muted font-weight-bold text-uppercase mb-1" style="font-size: 0.70rem;">Cancelamentos /
                        Estornos</p>
                    <h3 class="mb-0 text-danger font-weight-bold">{{ $moedaEmpresa }}
                        {{ number_format($totalCancelado, 2, ',', '.') }}</h3>
                    <small class="text-muted">{{ $qtdCanceladas }} transacoes invalidadas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Accordion For Analytical Data -->
    <div class="accordion mb-4" id="accordionAnalitico">
        <div class="card filter-card shadow-sm border-0 mb-0">
            <div class="card-header bg-white p-0 border-0" id="headingAnalise">
                <button
                    class="btn btn-link btn-block text-left text-dark font-weight-bold text-decoration-none py-3 px-4 d-flex justify-content-between align-items-center shadow-none"
                    type="button" data-toggle="collapse" data-target="#collapseAnalise" aria-expanded="false"
                    aria-controls="collapseAnalise">
                    <span><i class="fas fa-chart-pie mr-2 text-primary"></i> Relatorios Dinamicos (Gateways, Clientes e
                        Status)</span>
                    <i class="fas fa-chevron-down text-muted"></i>
                </button>
            </div>
            <div id="collapseAnalise" class="collapse" aria-labelledby="headingAnalise"
                data-parent="#accordionAnalitico">
                <div class="card-body border-top p-4 bg-light">
                    <div class="row">
                        <!-- Status Table -->
                        <div class="col-md-4 mb-3">
                            <div class="bg-white rounded-lg p-3 shadow-sm h-100 border text-sm">
                                <h6 class="font-weight-bold mb-3 border-bottom pb-2">Vendas por Status</h6>
                                <table class="table table-sm table-borderless table-hover mb-0">
                                    <tbody>
                                        @forelse($statusBreakdown as $item)
                                            <tr>
                                                <td><span class="text-secondary">{{ ucfirst($item->status) }}</span></td>
                                                <td class="text-right text-muted">{{ $item->quantidade }}x</td>
                                                <td class="text-right font-weight-bold">
                                                    {{ number_format((float) $item->total, 2, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-2">Sem dados</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Gateway Table -->
                        <div class="col-md-4 mb-3">
                            <div class="bg-white rounded-lg p-3 shadow-sm h-100 border text-sm">
                                <h6 class="font-weight-bold mb-3 border-bottom pb-2">Metricas de Gateway</h6>
                                <table class="table table-sm table-borderless table-hover mb-0">
                                    <tbody>
                                        @forelse($gatewayBreakdown as $item)
                                            <tr>
                                                <td><span class="text-secondary">{{ ucfirst($item->gateway) }}</span></td>
                                                <td class="text-right text-muted">{{ $item->quantidade }}x</td>
                                                <td class="text-right font-weight-bold">
                                                    {{ number_format((float) $item->total, 2, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-2">Sem dados</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Top Clientes -->
                        <div class="col-md-4 mb-3">
                            <div class="bg-white rounded-lg p-3 shadow-sm h-100 border text-sm">
                                <h6 class="font-weight-bold mb-3 border-bottom pb-2">Top 5 Clientes</h6>
                                <table class="table table-sm table-borderless table-hover mb-0">
                                    <tbody>
                                        @forelse($topClientes as $item)
                                            <tr>
                                                <td class="text-truncate" style="max-width: 120px;"
                                                    title="{{ $item->cliente }}"><span
                                                        class="text-secondary">{{ $item->cliente }}</span></td>
                                                <td class="text-right text-muted">{{ $item->quantidade }}x</td>
                                                <td class="text-right font-weight-bold">
                                                    {{ number_format((float) $item->total, 2, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-2">Sem dados</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Evolução Diária -->
                        <div class="col-12 mt-2">
                            <div class="bg-white rounded-lg p-3 shadow-sm border">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                    <h6 class="font-weight-bold mb-0">Evolucao diaria (Ultimos 30 dias)</h6>
                                    <a href="{{ route('financeiro.reservas', request()->query()) }}"
                                        class="btn btn-sm btn-outline-primary py-0">Ver relatorio de faturamento por
                                        reserva <i class="fas fa-arrow-right"></i></a>
                                </div>
                                <div class="table-responsive" style="max-height: 250px;">
                                    <table class="table table-sm table-hover mb-0 text-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="text-muted border-0">Dia</th>
                                                <th class="text-center text-muted border-0">Transacoes Concluidas/Iniciadas
                                                </th>
                                                <th class="text-right text-muted border-0">Somatorio Periodo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($evolucaoDiaria as $item)
                                                <tr>
                                                    <td><span class="text-secondary"><i
                                                                class="far fa-calendar-alt text-muted mr-1"></i>
                                                            {{ \Illuminate\Support\Carbon::parse($item->dia)->format('d/m/Y') }}</span>
                                                    </td>
                                                    <td class="text-center text-muted">{{ $item->quantidade }} transacoes
                                                    </td>
                                                    <td class="text-right font-weight-bold text-dark">{{ $moedaEmpresa }}
                                                        {{ number_format((float) $item->total, 2, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-3">Nenhuma
                                                        movimentacao nos ultimos 30 dias.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Transactions Table -->
    <div class="table-modern">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-list-ol text-primary mr-2"></i> Historico Master
                de Movimentacoes</h5>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4 text-center">Referencia</th>
                        <th>Cliente / Detalhe</th>
                        <th>Canal / Metodo</th>
                        <th>Status Processamento</th>
                        <th class="text-right px-4">Valor Final</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transacoes as $item)
                        @php
                            $statusLower = strtolower($item->status ?? '');
                            $isCancelado = in_array($statusLower, $statusCancelados, true);
                            $isAprovado = in_array($statusLower, $statusAprovados, true);
                            $isPendente = in_array($statusLower, $statusPendentes, true);

                            $badgeClass = $isCancelado
                                ? 'badge-danger'
                                : ($isAprovado
                                    ? 'badge-success'
                                    : ($isPendente
                                        ? 'badge-warning'
                                        : 'badge-secondary'));

                            $valor = (float) $item->valor;
                            $tipoLabel =
                                $valor < 0
                                    ? 'Estornada do Repasse'
                                    : ($isCancelado
                                        ? 'Bloqueada ou Cancelada'
                                        : 'Confirmada com Sucesso');
                            // Make value visually clear
                            $valColor = $valor < 0 || $isCancelado ? 'val-danger' : 'val-success';

                            // Cliente: Se o pagador for diferente do dono da reserva, mostra ambos
                            $cliente = $item->usuario_nome ?? 'Sem nome vinculado';
                            if (
                                !empty($item->usuario_pagador_nome) &&
                                $item->usuario_pagador_nome !== $item->usuario_nome
                            ) {
                                $cliente =
                                    $item->usuario_nome .
                                    ' <br><small class="text-muted"><i class="fas fa-user-edit"></i> Pagador principal: ' .
                                    $item->usuario_pagador_nome .
                                    '</small>';
                            }

                            // If it's a reservation transaction or an internal code.
$refText = $item->reserva_id ? 'RES-#' . $item->reserva_id : 'TRA-#' . $item->id;
                        @endphp
                        <tr>
                            <td class="px-4 text-center align-middle" style="white-space:nowrap;">
                                <span class="font-weight-bold text-dark d-block mb-1">{{ $refText }}</span>
                                <small class="text-muted"><i class="far fa-clock"></i>
                                    {{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d/m/y H:i') }}</small>
                            </td>
                            <td class="align-middle">
                                {!! $cliente !!}
                            </td>
                            <td class="align-middle text-uppercase text-muted"
                                style="font-size:0.8rem; letter-spacing:0.02em;">
                                <i class="fas fa-cash-register mr-1"></i>
                                {{ data_get($item, 'metodo_pagamento_label') ?? data_get($item, 'gateway') ?? data_get($item, 'metodo_pagamento') ?? 'Não classificado' }}
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-modern {{ $badgeClass }}">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ mb_strtoupper($item->status ?? 'Desconhecido', 'UTF-8') }}
                                </span>
                            </td>
                            <td class="text-right px-4 align-middle">
                                <span class="font-weight-bold {{ $valColor }}" style="font-size: 1.1rem;">
                                    {{ strtoupper($item->moeda ?? 'BRL') }} {{ number_format(abs($valor), 2, ',', '.') }}
                                </span>
                                @if ($valor < 0 || $isCancelado)
                                    <br><small class="text-danger"><i class="fas fa-undo-alt"></i>
                                        {{ $tipoLabel }}</small>
                                @else
                                    <br><small class="text-success"><i class="fas fa-check"></i>
                                        {{ $tipoLabel }}</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fas fa-box-open mb-3" style="font-size: 3rem; opacity: 0.1;"></i><br>
                                <h5 class="text-secondary font-weight-normal mb-0">Nenhuma transacao ou transferencia
                                    encontrada no periodo com esses filtros.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light border-top py-3 px-4 d-flex justify-content-end">
            {{ $transacoes->links() }}
        </div>
    </div>
@stop
