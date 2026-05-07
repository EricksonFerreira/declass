@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="financeiro-dashboard">
        {{-- Filtros Concisos --}}
        <div class="card shadow-sm border-0 mb-4 rs-filters-card">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('financeiro.reservas') }}" class="mb-0">
                    <div class="row align-items-end gx-2 gy-2">
                        <div class="col-xl-2 col-lg-3 col-sm-6">
                            <label class="rs-filter-label text-muted text-uppercase mb-1"
                                style="font-size: 11px; font-weight: 600;">Reserva</label>
                            <input type="number" name="reserva_id" class="form-control form-control-sm border-light-subtle"
                                placeholder="Ex: 50" value="{{ $filters['reserva_id'] ?? '' }}">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-sm-6">
                            <label class="rs-filter-label text-muted text-uppercase mb-1"
                                style="font-size: 11px; font-weight: 600;">Status</label>
                            <input type="text" name="status" class="form-control form-control-sm border-light-subtle"
                                placeholder="Pendente, Aprovado..." value="{{ $filters['status'] ?? '' }}">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-sm-6">
                            <label class="rs-filter-label text-muted text-uppercase mb-1"
                                style="font-size: 11px; font-weight: 600;">Gateway</label>
                            <input type="text" name="metodo_pagamento"
                                class="form-control form-control-sm border-light-subtle" placeholder="Ex: stripe, pix"
                                value="{{ $filters['metodo_pagamento'] ?? '' }}">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-sm-6">
                            <label class="rs-filter-label text-muted text-uppercase mb-1"
                                style="font-size: 11px; font-weight: 600;">Data Início</label>
                            <input type="date" name="data_inicio"
                                class="form-control form-control-sm border-light-subtle"
                                value="{{ $filters['data_inicio'] ?? '' }}">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-sm-6">
                            <label class="rs-filter-label text-muted text-uppercase mb-1"
                                style="font-size: 11px; font-weight: 600;">Data Fim</label>
                            <input type="date" name="data_fim" class="form-control form-control-sm border-light-subtle"
                                value="{{ $filters['data_fim'] ?? '' }}">
                        </div>
                        <div class="col-xl-2 col-lg-12 d-flex align-items-end mt-2 mt-xl-0">
                            <button class="btn btn-primary btn-sm px-3 shadow-sm mr-2 w-100 font-weight-bold"
                                type="submit">
                                <i class="fas fa-filter mr-1"></i> Filtrar
                            </button>
                            <a href="{{ route('financeiro.reservas') }}"
                                class="btn btn-light btn-sm shadow-sm w-100 text-muted" title="Limpar">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- KPIs Modernos --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-12 mb-3 mb-xl-0">
                <div class="card rs-kpi-card h-100 border-0 shadow-sm rs-border-left-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted text-uppercase rs-kpi-label mb-1">Total Faturado</p>
                                <h3 class="rs-kpi-value mb-0 text-success font-weight-bold">{{ $moedaEmpresa }}
                                    {{ number_format($totalFaturado, 2, ',', '.') }}</h3>
                                <small class="text-muted">Sem canceladas</small>
                            </div>
                            <div class="rs-kpi-icon-wrapper bg-success-light text-success">
                                <i class="fas fa-euro-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-12 mb-3 mb-xl-0">
                <div class="card rs-kpi-card h-100 border-0 shadow-sm rs-border-left-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted text-uppercase rs-kpi-label mb-1">Reservas</p>
                                <h3 class="rs-kpi-value mb-0 text-info font-weight-bold">{{ $qtdReservasFaturadas }}</h3>
                                <small class="text-muted">Com faturamento no período</small>
                            </div>
                            <div class="rs-kpi-icon-wrapper bg-info-light text-info">
                                <i class="fas fa-receipt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-12 mb-3 mb-xl-0">
                <div class="card rs-kpi-card h-100 border-0 shadow-sm rs-border-left-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted text-uppercase rs-kpi-label mb-1">Ticket Médio</p>
                                <h3 class="rs-kpi-value mb-0 text-primary font-weight-bold">{{ $moedaEmpresa }}
                                    {{ number_format($ticketMedioPorReserva, 2, ',', '.') }}</h3>
                                <small class="text-muted">Média por reserva</small>
                            </div>
                            <div class="rs-kpi-icon-wrapper bg-primary-light text-primary">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-12 mb-3 mb-xl-0">
                <div class="card rs-kpi-card h-100 border-0 shadow-sm rs-border-left-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted text-uppercase rs-kpi-label mb-1">Cancelamentos</p>
                                <h3 class="rs-kpi-value mb-0 text-danger font-weight-bold">{{ $moedaEmpresa }}
                                    {{ number_format($totalCancelado, 2, ',', '.') }}</h3>
                                <small class="text-muted">Total devolvido/estornado</small>
                            </div>
                            <div class="rs-kpi-icon-wrapper bg-danger-light text-danger">
                                <i class="fas fa-ban"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabela Consolidada --}}
        <div class="card shadow-sm border-0 rs-table-wrapper">
            <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 1.1rem;">Resumo por Reserva</h5>
                <a href="{{ route('financeiro.index', request()->query()) }}"
                    class="btn btn-sm btn-outline-secondary rounded-pill font-weight-semibold">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar para Extrato
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle rs-financial-table mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th
                                    class="text-uppercase text-secondary text-xs font-weight-bold border-top-0 border-bottom-0 rounded-tl">
                                    Reserva</th>
                                <th
                                    class="text-uppercase text-secondary text-xs font-weight-bold border-top-0 border-bottom-0">
                                    Cliente</th>
                                <th
                                    class="text-uppercase text-secondary text-xs font-weight-bold border-top-0 border-bottom-0 text-center">
                                    Transações</th>
                                <th
                                    class="text-uppercase text-secondary text-xs font-weight-bold border-top-0 border-bottom-0 text-right">
                                    Faturamento Total</th>
                                <th
                                    class="text-uppercase text-secondary text-xs font-weight-bold border-top-0 border-bottom-0 rounded-tr text-center">
                                    Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faturamentoPorReserva as $item)
                                <tr class="rs-group-row bg-white cursor-pointer" data-toggle="collapse"
                                    data-target="#col-res-{{ $item->reserva_id }}">
                                    <td>
                                        <div class="font-weight-bold text-primary rs-ref-link" title="Visualizar Reserva">
                                            RES-{{ str_pad($item->reserva_id, 4, '0', STR_PAD_LEFT) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="rs-avatar-placeholder mr-2 bg-primary-light text-primary font-weight-bold">
                                                {{ strtoupper(substr($item->cliente_nome ?? 'C', 0, 1)) }}
                                            </div>
                                            <span
                                                class="text-dark font-weight-medium">{{ $item->cliente_nome ?? 'Não Informado' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge badge-light text-secondary border px-2 py-1">{{ $item->quantidade_transacoes }}
                                            mov.</span>
                                    </td>
                                    <td class="text-right">
                                        <strong class="{{ $item->valor_total > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $item->valor_total > 0 ? '+' : '' }}{{ $moedaEmpresa }}
                                            {{ number_format((float) $item->valor_total, 2, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td class="text-center text-muted text-sm">
                                        <button class="btn btn-sm btn-link text-secondary p-0 m-0"><i
                                                class="fas fa-chevron-down"></i> Detalhes</button>
                                    </td>
                                </tr>
                                <tr class="p-0 border-0 bg-light">
                                    <td colspan="5" class="p-0 border-0">
                                        <div id="col-res-{{ $item->reserva_id }}" class="collapse w-100 border-bottom">
                                            <div class="p-3 bg-light shadow-inner">
                                                <div
                                                    class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                                    <h6 class="mb-0 text-dark font-weight-bold"><i
                                                            class="fas fa-list-ol text-primary mr-1"></i> Detalhamento de
                                                        Transações
                                                        (RES-{{ str_pad($item->reserva_id, 4, '0', STR_PAD_LEFT) }})</h6>
                                                    <a href="{{ route('reserva.show', $item->reserva_id) }}"
                                                        class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill"><i
                                                            class="fas fa-external-link-alt mr-1"></i> Ir para Reserva</a>
                                                </div>
                                                <div class="table-responsive bg-white rounded border">
                                                    <table class="table table-hover mb-0 text-sm">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th
                                                                    class="px-3 text-center border-0 text-muted text-xs text-uppercase">
                                                                    Referência</th>
                                                                <th class="border-0 text-muted text-xs text-uppercase">
                                                                    Cliente / Pagador</th>
                                                                <th class="border-0 text-muted text-xs text-uppercase">
                                                                    Origem / Método</th>
                                                                <th class="border-0 text-muted text-xs text-uppercase">
                                                                    Status</th>
                                                                <th
                                                                    class="text-right px-3 border-0 text-muted text-xs text-uppercase">
                                                                    Valor Oficial</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($detalhes->get($item->reserva_id, []) as $transacao)
                                                                @php
                                                                    $statusLower = strtolower($transacao->status ?? '');
                                                                    $isCancelado = in_array(
                                                                        $statusLower,
                                                                        $statusCancelados,
                                                                        true,
                                                                    );
                                                                    $isAprovado = in_array(
                                                                        $statusLower,
                                                                        $statusAprovados,
                                                                        true,
                                                                    );
                                                                    $isPendente = in_array(
                                                                        $statusLower,
                                                                        $statusPendentes,
                                                                        true,
                                                                    );
                                                                    $badgeClass = $isCancelado
                                                                        ? 'badge-danger'
                                                                        : ($isAprovado
                                                                            ? 'badge-success'
                                                                            : ($isPendente
                                                                                ? 'badge-warning'
                                                                                : 'badge-secondary'));

                                                                    $valor = (float) $transacao->valor;
                                                                    $tipoLabel =
                                                                        $valor < 0
                                                                            ? 'Estornada'
                                                                            : ($isCancelado
                                                                                ? 'Bloqueada/Cancelada'
                                                                                : 'Confirmada');
                                                                    $valColor =
                                                                        $valor < 0 || $isCancelado
                                                                            ? 'text-danger'
                                                                            : 'text-success';
                                                                    $clienteNome = $transacao->user?->nome ?? 'S/N';
                                                                    if (
                                                                        !empty($transacao->pagoPor) &&
                                                                        $transacao->pagoPor->id !== $transacao->user_id
                                                                    ) {
                                                                        $clienteNome .=
                                                                            ' <br><small class="text-muted"><i class="fas fa-user-edit"></i> Pagador: ' .
                                                                            $transacao->pagoPor->nome .
                                                                            '</small>';
                                                                    }

                                                                    $tipoTransacao = 'Reserva';
                                                                    $info = is_array($transacao->info_pagamento)
                                                                        ? $transacao->info_pagamento
                                                                        : @json_decode(
                                                                            (string) $transacao->info_pagamento,
                                                                            true,
                                                                        );
                                                                    if (is_array($info) && isset($info['tipo'])) {
                                                                        $tipoTransacao = ucfirst($info['tipo']);
                                                                    }
                                                                @endphp
                                                                <tr>
                                                                    <td class="px-3 text-center align-middle"
                                                                        style="white-space:nowrap; padding: 0.4rem !important;">
                                                                        <span
                                                                            class="font-weight-bold text-dark d-block mb-0">TRA-{{ $transacao->id }}</span>
                                                                        <small class="text-muted"
                                                                            style="font-size: 10px;"><i
                                                                                class="far fa-clock"></i>
                                                                            {{ \Illuminate\Support\Carbon::parse($transacao->created_at)->format('d/m/y H:i') }}</small>
                                                                    </td>
                                                                    <td class="align-middle"
                                                                        style="padding: 0.4rem !important;">
                                                                        <span
                                                                            class="text-dark font-weight-medium">{!! $clienteNome !!}</span>
                                                                    </td>
                                                                    <td class="align-middle text-uppercase text-muted"
                                                                        style="font-size:0.75rem; letter-spacing:0.02em; padding: 0.4rem !important;">
                                                                        <span
                                                                            class="text-dark font-weight-bold d-block">{{ $tipoTransacao }}</span>
                                                                        <i class="fas fa-cash-register mr-1"></i>
                                                                        {{ $transacao->metodo_pagamento_label }}
                                                                    </td>
                                                                    <td class="align-middle"
                                                                        style="padding: 0.4rem !important;">
                                                                        <span class="rs-badge-modern {{ $badgeClass }}">
                                                                            <i class="fas fa-info-circle mr-1"></i>
                                                                            {{ (mb_strlen($transacao->status) > 10 ? substr($transacao->status, 0, 10) . '..' : $transacao->status) ?? '...' }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-right px-3 align-middle"
                                                                        style="padding: 0.4rem !important;">
                                                                        <span class="font-weight-bold {{ $valColor }}"
                                                                            style="font-size: 1rem;">
                                                                            {{ strtoupper($transacao->moeda ?? 'BRL') }}
                                                                            {{ number_format(abs($valor), 2, ',', '.') }}
                                                                        </span>
                                                                        <br><small class="{{ $valColor }}"><i
                                                                                class="fas {{ $valor < 0 || $isCancelado ? 'fa-undo-alt' : 'fa-check' }}"></i>
                                                                            {{ $tipoLabel }}</small>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-muted">
                                                                        Nenhum detalhe encontrado para esta reserva no
                                                                        relatorio.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="far fa-folder-open fa-3x text-light mb-3"></i>
                                            <h6 class="text-muted font-weight-bold mb-1">Nenhum faturamento encontrado</h6>
                                            <p class="text-muted small mb-0">Tente ajustar os filtros acima para ver outros
                                                períodos.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($faturamentoPorReserva->hasPages())
                <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                    {{ $faturamentoPorReserva->links() }}
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Dashboard Base */
        .financeiro-dashboard {
            font-family: 'Inter', -apple-system, sans-serif;
        }

        .font-weight-medium {
            font-weight: 500;
        }

        .font-weight-semibold {
            font-weight: 600;
        }

        /* Filter Card */
        .rs-filters-card {
            border-radius: 12px;
        }

        .border-light-subtle {
            border-color: #e2e8f0 !important;
        }

        .form-control-sm {
            border-radius: 6px;
        }

        /* KPI Cards */
        .rs-kpi-card {
            border-radius: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid rgba(0, 0, 0, 0.03) !important;
        }

        .rs-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }

        .rs-kpi-label {
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .rs-kpi-value {
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        /* Icon Wrapper */
        .rs-kpi-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Background/Border Colors */
        .rs-border-left-success {
            border-left: 4px solid #10b981 !important;
        }

        .rs-border-left-info {
            border-left: 4px solid #06b6d4 !important;
        }

        .rs-border-left-primary {
            border-left: 4px solid #3b82f6 !important;
        }

        .rs-border-left-danger {
            border-left: 4px solid #ef4444 !important;
        }

        .bg-success-light {
            background-color: rgba(16, 185, 129, 0.12);
            color: #10b981;
        }

        .bg-info-light {
            background-color: rgba(6, 182, 212, 0.12);
            color: #06b6d4;
        }

        .bg-primary-light {
            background-color: rgba(59, 130, 246, 0.12);
            color: #3b82f6;
        }

        .bg-danger-light {
            background-color: rgba(239, 68, 68, 0.12);
            color: #ef4444;
        }

        /* Table Styles */
        .rs-table-wrapper {
            border-radius: 14px;
            overflow: hidden;
        }

        .rs-financial-table thead th {
            letter-spacing: 0.5px;
            padding-top: 1rem;
            padding-bottom: 1rem;
            background-color: #f8fafc;
        }

        .rounded-tl {
            border-top-left-radius: 8px;
        }

        .rounded-tr {
            border-top-right-radius: 8px;
        }

        .rs-financial-table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
        }

        /* Adicionado cursor no hover do group row */
        .rs-group-row:hover {
            background-color: #f8fbff !important;
            cursor: pointer;
        }

        .rs-badge-modern {
            padding: 0.35em 0.6em;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .shadow-inner {
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.04);
        }

        /* Avatars & Links */
        .rs-avatar-placeholder {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        .rs-ref-link {
            text-decoration: none;
            transition: color 0.15s;
        }

        .rs-ref-link:hover {
            text-decoration: underline;
            color: #1d4ed8 !important;
        }

        /* Pagination Override */
        .pagination {
            margin-bottom: 0;
        }
    </style>
@stop
