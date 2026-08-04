@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[[ 'name' => 'Financeiro', 'url' => route('multa.index') ], [ 'name' => 'Multas' ]]" />
@stop

@section('content')
    @php
        $totalMultas = $multas->total();
        $qtdPagas = $multas->where('status_multa', 'paga')->count();
        $qtdPendentes = $multas->whereIn('status_multa', ['pendente', 'em_analise', 'aguardando_pagamento_cliente'])->count();
        $qtdAtrasadas = $multas->where('status_multa', 'atrasada')->count();
    @endphp

    <div class="container-fluid px-0 vehicle-show-page multa-show-page">
        <div class="vs-panel shadow-sm border-0 mb-3">
            <div class="vs-panel-head">
                <div>
                    <span class="vs-panel-head__eyebrow">Financeiro · Multas</span>
                    <h4 class="vs-panel-head__title mb-1">Multas registradas</h4>
                    <p class="vs-panel-head__sub mb-0">Acompanhe infrações, cobrança, vencimentos e status financeiro em uma leitura rápida.</p>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                    @can('multa.export')
                        <a href="{{ route('multa.export', request()->query()) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-file-csv mr-1"></i> CSV
                        </a>
                    @endcan
                    <a href="{{ route('multa.create') }}" class="btn-xp btn-xp--sm">
                        <i class="fas fa-plus mr-1"></i> Nova multa
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-total">
                    <div class="kpi-icon"><i class="fas fa-list"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Registros</span>
                        <strong class="kpi-value">{{ $totalMultas }}</strong>
                        <small class="kpi-subtitle">Multas no período filtrado</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-available">
                    <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Pagas</span>
                        <strong class="kpi-value">{{ $qtdPagas }}</strong>
                        <small class="kpi-subtitle">Concluídas financeiramente</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-unavailable">
                    <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Pendentes</span>
                        <strong class="kpi-value">{{ $qtdPendentes }}</strong>
                        <small class="kpi-subtitle">Exigem acompanhamento</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="config-card shadow-sm border-0 mb-3">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Filtros operacionais</span>
                    <h4 class="vs-panel-head__title mb-1">Refinar listagem</h4>
                    <p class="vs-panel-head__sub mb-0">Filtre por período, placa, status e desconto para localizar a multa certa mais rápido.</p>
                </div>
                <span class="vs-badge-soft is-warning">
                    <i class="fas fa-filter"></i>
                    Filtro rápido
                </span>
            </div>
            <div class="doc-filters">
                <form method="GET" action="{{ route('multa.index') }}" class="doc-filters__form" id="formFiltrosMulta">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="doc-filter-label">Data início</label>
                            <input type="date" name="data_inicio" class="form-control doc-filter-control" value="{{ $filtros['data_inicio'] ?? '' }}">
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="doc-filter-label">Data fim</label>
                            <input type="date" name="data_fim" class="form-control doc-filter-control" value="{{ $filtros['data_fim'] ?? '' }}">
                        </div>
                        <div class="col-lg-2 col-md-6 mb-2">
                            <label class="doc-filter-label">Placa</label>
                            <input type="text" name="placa" class="form-control doc-filter-control" value="{{ $filtros['placa'] ?? '' }}" placeholder="Placa">
                        </div>
                        <div class="col-lg-2 col-md-6 mb-2">
                            <label class="doc-filter-label">Status multa</label>
                            <select name="status_multa" class="form-control doc-filter-control">
                                <option value="">Todos</option>
                                @foreach(\App\Models\Multa::STATUS_MULTA as $key => $label)
                                    <option value="{{ $key }}" {{ ($filtros['status_multa'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 mb-2">
                            <label class="doc-filter-label">Status desconto</label>
                            <select name="status_desconto" class="form-control doc-filter-control">
                                <option value="">Todos</option>
                                @foreach(\App\Models\Multa::STATUS_DESCONTO as $key => $label)
                                    <option value="{{ $key }}" {{ ($filtros['status_desconto'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-12 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-xp btn-xp--sm w-100 mr-2">
                                <i class="fas fa-search mr-1"></i> Filtrar
                            </button>
                            <a href="{{ route('multa.index') }}" class="btn btn-light btn-sm w-100">
                                Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="config-card shadow-sm border-0">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Lista operacional</span>
                    <h4 class="vs-panel-head__title mb-1">Multas cadastradas</h4>
                    <p class="vs-panel-head__sub mb-0">Clique em uma linha para acessar os detalhes completos e as ações de cobrança.</p>
                </div>
                <span class="vs-badge-soft is-warning">
                    <i class="fas fa-list"></i>
                    {{ $totalMultas }} registro(s)
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive vehicle-table-wrap">
                    <table class="table table-hover mb-0 vehicle-list-table">
                        <thead>
                            <tr>
                                <th>Multa</th>
                                <th>Cliente / Veículo</th>
                                <th>Órgão / Datas</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($multas as $multa)
                                @php
                                    $codigoReserva = $multa->reserva?->codigoReserva?->codigo
                                        ?? ($multa->reserva_id ? 'RES-' . str_pad((string) $multa->reserva_id, 6, '0', STR_PAD_LEFT) : null);
                                    $badgeMulta = match($multa->status_multa) {
                                        'paga'       => 'status-badge--green',
                                        'cancelada'  => 'status-badge--gray',
                                        'deferida'   => 'status-badge--blue',
                                        'indeferida' => 'status-badge--amber',
                                        default      => 'status-badge--red',
                                    };
                                @endphp
                                <tr onclick="window.location='{{ route('multa.show', $multa->id) }}';" style="cursor:pointer;">
                                    <td>
                                        <div class="font-weight-bold">#{{ $multa->id }} · {{ $multa->placa_momento ?? '-' }}</div>
                                        <small class="text-muted">{{ $multa->label_status_multa }}</small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $codigoReserva ? 'Reserva #' . $codigoReserva : 'Sem reserva' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div>{{ $multa->cliente?->nome ?? '-' }}</div>
                                        <small class="text-muted">{{ $multa->veiculo?->nome ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $multa->orgao_autuador ?? '-' }}</div>
                                        <small class="text-muted">
                                            Infração: {{ $multa->data_hora_infracao?->format('d/m/Y') ?? '-' }}
                                            · Venc.: {{ $multa->data_vencimento?->format('d/m/Y') ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">R$ {{ number_format((float) $multa->valor_final, 2, ',', '.') }}</div>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $badgeMulta }}">
                                            {{ $multa->label_status_multa }}
                                        </span>
                                        <span class="status-badge {{ $multa->status_desconto === 'descontado' ? 'status-badge--green' : 'status-badge--gray' }} ml-1">
                                            {{ $multa->label_status_desconto }}
                                        </span>
                                    </td>
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <a href="{{ route('multa.show', $multa->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('multa.edit')
                                            <a href="{{ route('multa.edit', $multa->id) }}" class="btn btn-xs btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('multa.delete')
                                            <x-delete-button :action="route('multa.destroy', $multa->id)" />
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Nenhuma multa encontrada para os filtros aplicados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                {{ $multas->withQueryString()->links() }}
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .multa-show-page { padding-bottom: 8px; }
    .config-card {
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }
    .vs-panel-head {
        background: linear-gradient(135deg, #f8fafc 0%, #eef4ff 50%, #f8fbff 100%);
    }
    .vs-panel-head {
        padding: 1.25rem 1.4rem;
        border-bottom: 1px solid rgba(148, 163, 184, .18);
    }
    .vehicle-table-wrap {
        max-height: none;
        overflow: auto;
    }
    .vehicle-list-table { min-width: 1040px; margin-bottom: 0; }
    .vehicle-list-table thead th {
        white-space: nowrap;
        background: #f8fafc;
        font-weight: 800;
        color: #334155;
        border-bottom: 1px solid #e2e8f0;
    }
    .vehicle-list-table tbody td { vertical-align: middle; }
    .vehicle-list-table tbody tr:hover { background: #f8fbff; }
    .doc-filters { padding: 1rem; background: #fff; }
    .doc-filters__form .form-control,
    .doc-filters__form select {
        background: #fff;
    }
    .doc-filter-label {
        display: block;
        font-size: .75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #475569;
        margin-bottom: .35rem;
    }
    .doc-filter-control {
        border-radius: 12px;
        border-color: #dbe4ef;
        min-height: 42px;
    }
    .doc-filter-control:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .15);
    }
    .kpi-card {
        border-radius: 18px;
        padding: 1rem 1.1rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
    }
    .kpi-label {
        text-transform: uppercase;
        letter-spacing: .08em;
        font-weight: 800;
        font-size: .72rem;
    }
    .kpi-value { font-size: 1.5rem; line-height: 1.1; }
    .kpi-subtitle { color: #64748b; font-size: .78rem; }
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: .73rem;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .status-badge--green { background: #dcfce7; color: #166534; }
    .status-badge--amber { background: #fef3c7; color: #92400e; }
    .status-badge--red { background: #fee2e2; color: #991b1b; }
    .status-badge--gray { background: #f1f5f9; color: #475569; }
    .status-badge--blue { background: #dbeafe; color: #1d4ed8; }
    @media (max-width: 991.98px) {
        .vs-panel-head { padding: 1rem; }
        .vs-panel-head, .doc-filters { padding-left: 14px; padding-right: 14px; }
        .doc-filters { padding-top: .9rem; padding-bottom: .9rem; }
        .vehicle-list-table thead th,
        .vehicle-list-table tbody td { font-size: .86rem; }
    }
</style>
@endsection
