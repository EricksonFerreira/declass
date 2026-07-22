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

        .financeiro-dashboard {
            display: grid;
            gap: 20px;
        }

        .financeiro-hero {
            border-radius: 24px;
            padding: 22px 24px;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(56, 189, 248, .18), transparent 26%),
                radial-gradient(circle at bottom left, rgba(16, 185, 129, .15), transparent 22%),
                linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #2563eb 100%);
            box-shadow: 0 24px 60px rgba(15, 23, 42, .18);
            overflow: hidden;
            position: relative;
        }

        .financeiro-hero__eyebrow {
            font-size: .72rem;
            letter-spacing: .18em;
            text-transform: uppercase;
            font-weight: 800;
            opacity: .72;
        }

        .financeiro-hero__title {
            margin: .35rem 0 .25rem;
            font-size: 1.55rem;
            font-weight: 900;
            letter-spacing: -.02em;
        }

        .financeiro-hero__text {
            margin: 0;
            max-width: 58rem;
            color: rgba(255,255,255,.86);
            line-height: 1.6;
        }

        .financeiro-hero__chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .financeiro-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.14);
            color: #fff;
            font-size: .8rem;
            font-weight: 700;
        }

        .financeiro-panel,
        .financeiro-card,
        .financeiro-table-card {
            border: 1px solid #dbe4f0;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 14px 36px rgba(15, 23, 42, .06);
            overflow: hidden;
        }

        .financeiro-panel__header,
        .financeiro-table-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid #e8eef6;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .financeiro-panel__title,
        .financeiro-table-card__title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.01em;
        }

        .financeiro-panel__sub {
            margin: 4px 0 0;
            font-size: .82rem;
            color: #64748b;
        }

        .financeiro-panel__body {
            padding: 18px;
        }

        .financeiro-filters {
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .financeiro-filters .form-label,
        .financeiro-filters label {
            display: inline-block;
            margin-bottom: .4rem;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #64748b;
        }

        .financeiro-filters .form-control {
            height: 40px;
            border-radius: 12px;
            border-color: #d7e0ea;
            background: #fff;
            box-shadow: none;
        }

        .financeiro-filters .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, .12);
        }

        .financeiro-actions {
            display: flex;
            gap: 10px;
            align-items: stretch;
        }

        .financeiro-actions .btn {
            min-height: 40px;
            border-radius: 12px;
            font-weight: 800;
        }

        .financeiro-kpi {
            border: 1px solid #dbe4f0;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 14px 36px rgba(15, 23, 42, .06);
            overflow: hidden;
            height: 100%;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .financeiro-kpi:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 44px rgba(15, 23, 42, .10);
        }

        .financeiro-kpi__accent {
            height: 4px;
        }

        .financeiro-kpi--blue .financeiro-kpi__accent { background: linear-gradient(90deg, #2563eb, #60a5fa); }
        .financeiro-kpi--green .financeiro-kpi__accent { background: linear-gradient(90deg, #059669, #34d399); }
        .financeiro-kpi--amber .financeiro-kpi__accent { background: linear-gradient(90deg, #d97706, #fbbf24); }
        .financeiro-kpi--red .financeiro-kpi__accent { background: linear-gradient(90deg, #dc2626, #fb7185); }

        .financeiro-kpi__body {
            padding: 18px;
            position: relative;
            min-height: 128px;
        }

        .financeiro-kpi__icon {
            position: absolute;
            right: 18px;
            top: 16px;
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            background: #f8fafc;
            color: #94a3b8;
        }

        .financeiro-kpi--green .financeiro-kpi__icon { background: #ecfdf5; color: #10b981; }
        .financeiro-kpi--blue .financeiro-kpi__icon { background: #eff6ff; color: #2563eb; }
        .financeiro-kpi--amber .financeiro-kpi__icon { background: #fff7ed; color: #d97706; }
        .financeiro-kpi--red .financeiro-kpi__icon { background: #fef2f2; color: #dc2626; }

        .financeiro-kpi__label {
            margin: 0 0 .45rem;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #64748b;
        }

        .financeiro-kpi__value {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 900;
            letter-spacing: -.03em;
            color: #0f172a;
        }

        .financeiro-kpi__value--green { color: #059669; }
        .financeiro-kpi__value--blue { color: #2563eb; }
        .financeiro-kpi__value--amber { color: #d97706; }
        .financeiro-kpi__value--red { color: #dc2626; }

        .financeiro-kpi__meta {
            display: block;
            margin-top: .35rem;
            font-size: .82rem;
            color: #64748b;
        }

        .financeiro-analytics {
            border: 1px solid #dbe4f0;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 14px 36px rgba(15, 23, 42, .06);
            overflow: hidden;
        }

        .financeiro-analytics__body {
            padding: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .financeiro-subcard {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #fff;
            height: 100%;
            padding: 16px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.65);
        }

        .financeiro-table {
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #dbe4f0;
            background: #fff;
            box-shadow: 0 14px 36px rgba(15, 23, 42, .06);
        }

        .financeiro-table thead th {
            background: #f8fbff;
            border-top: 0;
            border-bottom: 1px solid #e8eef6;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: .72rem;
            color: #64748b;
            font-weight: 800;
            vertical-align: middle;
        }

        .financeiro-table td {
            vertical-align: middle;
            border-top: 1px solid #edf2f7;
            font-size: .86rem;
            padding-top: .72rem !important;
            padding-bottom: .72rem !important;
        }

        .financeiro-ref {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 800;
            font-size: .78rem;
        }

        .financeiro-badge {
            padding: .42rem .78rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .financeiro-table__empty {
            padding: 48px 16px;
            color: #64748b;
        }

        .financeiro-table__empty i {
            font-size: 3rem;
            opacity: .18;
            margin-bottom: 14px;
        }

        .financeiro-table-card__footer {
            display: flex;
            justify-content: flex-end;
            padding: 14px 18px;
            border-top: 1px solid #e8eef6;
            background: #f8fbff;
        }

        @media (max-width: 1199px) {
            .financeiro-hero {
                padding: 20px;
            }
        }

        @media (max-width: 767px) {
            .financeiro-panel__header,
            .financeiro-table-card__header {
                flex-direction: column;
                align-items: flex-start;
            }

            .financeiro-kpi__body {
                min-height: 120px;
            }

            .financeiro-kpi__value {
                font-size: 1.28rem;
            }

            .financeiro-actions {
                flex-direction: column;
            }
        }
    </style>

    <div class="financeiro-dashboard">
        <section class="financeiro-hero">
            <div class="financeiro-hero__eyebrow">Financeiro</div>
            <h1 class="financeiro-hero__title">Movimentações, gateways e faturamento em um único painel</h1>
            <p class="financeiro-hero__text">
                Use os filtros para localizar transações com precisão e acompanhe os números principais com a mesma linguagem visual das demais telas do sistema.
            </p>
            <div class="financeiro-hero__chips">
                <span class="financeiro-chip"><i class="fas fa-wallet"></i> Faturamento bruto</span>
                <span class="financeiro-chip"><i class="fas fa-chart-line"></i> Análise por status</span>
                <span class="financeiro-chip"><i class="fas fa-credit-card"></i> Gateways e métodos</span>
            </div>
        </section>

    <!-- Filters -->
    <div class="financeiro-panel financeiro-filters">
        <div class="financeiro-panel__body">
            <form method="GET" action="{{ route('financeiro.transacoes') }}" class="mb-0">
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
                        <a href="{{ route('financeiro.transacoes') }}"
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
            <div class="financeiro-kpi financeiro-kpi--blue">
                <div class="financeiro-kpi__accent"></div>
                <div class="financeiro-kpi__body">
                    <div class="financeiro-kpi__icon"><i class="fas fa-wallet"></i></div>
                    <p class="financeiro-kpi__label">Faturamento Bruto
                    </p>
                    <h3 class="financeiro-kpi__value financeiro-kpi__value--blue">{{ $moedaEmpresa }}
                        {{ number_format($totalFaturado, 2, ',', '.') }}</h3>
                    <small class="financeiro-kpi__meta">{{ $qtdTransacoes }} transacoes computadas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="financeiro-kpi financeiro-kpi--green">
                <div class="financeiro-kpi__accent"></div>
                <div class="financeiro-kpi__body">
                    <div class="financeiro-kpi__icon"><i class="fas fa-check-circle"></i></div>
                    <p class="financeiro-kpi__label">Receita Aprovada
                    </p>
                    <h3 class="financeiro-kpi__value financeiro-kpi__value--green">{{ $moedaEmpresa }}
                        {{ number_format($totalAprovado, 2, ',', '.') }}</h3>
                    <small class="financeiro-kpi__meta">Desempenho aprov.: {{ number_format($taxaAprovacao, 1, ',', '.') }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="financeiro-kpi financeiro-kpi--amber">
                <div class="financeiro-kpi__accent"></div>
                <div class="financeiro-kpi__body">
                    <div class="financeiro-kpi__icon"><i class="fas fa-hourglass-half"></i></div>
                    <p class="financeiro-kpi__label">Pendentes /
                        Falhas</p>
                    <h3 class="financeiro-kpi__value financeiro-kpi__value--amber">{{ $moedaEmpresa }}
                        {{ number_format($totalPendente + $totalFalha, 2, ',', '.') }}</h3>
                    <small class="financeiro-kpi__meta">Aguardando liquidacao</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="financeiro-kpi financeiro-kpi--red">
                <div class="financeiro-kpi__accent"></div>
                <div class="financeiro-kpi__body">
                    <div class="financeiro-kpi__icon"><i class="fas fa-ban"></i></div>
                    <p class="financeiro-kpi__label">Cancelamentos /
                        Estornos</p>
                    <h3 class="financeiro-kpi__value financeiro-kpi__value--red">{{ $moedaEmpresa }}
                        {{ number_format($totalCancelado, 2, ',', '.') }}</h3>
                    <small class="financeiro-kpi__meta">{{ $qtdCanceladas }} transacoes invalidadas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Accordion For Analytical Data -->
    <div class="financeiro-analytics mb-4" id="accordionAnalitico">
        <div class="financeiro-table-card">
            <div class="financeiro-table-card__header" id="headingAnalise">
                <div>
                    <h3 class="financeiro-table-card__title">Relatórios Dinâmicos</h3>
                    <p class="financeiro-panel__sub mb-0">Gateways, clientes e status resumidos em painéis de apoio.</p>
                </div>
                <button
                    class="btn btn-link text-dark font-weight-bold text-decoration-none shadow-none p-0"
                    type="button" data-toggle="collapse" data-target="#collapseAnalise" aria-expanded="false"
                    aria-controls="collapseAnalise">
                    <i class="fas fa-chevron-down text-muted"></i>
                </button>
            </div>
            <div id="collapseAnalise" class="collapse" aria-labelledby="headingAnalise"
                data-parent="#accordionAnalitico">
                <div class="financeiro-analytics__body">
                    <div class="row">
                        <!-- Status Table -->
                        <div class="col-md-4 mb-3">
                            <div class="financeiro-subcard">
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
                            <div class="financeiro-subcard">
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
                            <div class="financeiro-subcard">
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
                            <div class="financeiro-subcard">
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
    <div class="financeiro-table">
        <div class="financeiro-table-card__header">
            <div>
                <h3 class="financeiro-table-card__title">Histórico Master de Movimentações</h3>
                <p class="financeiro-panel__sub mb-0">Todas as transações consolidadas, com expansão por reserva.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 financeiro-table__table">
                <thead>
                    <tr>
                        <th class="px-4 text-center">Referência</th>
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
        <div class="financeiro-table-card__footer">
            {{ $transacoes->links() }}
        </div>
    </div>
</div>
@stop
