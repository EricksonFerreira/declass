@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[[ 'name' => 'Veículos', 'url' => route('veiculo.index') ], [ 'name' => 'Sinistros' ]]" />
@stop

@section('content')
    @php
        $totalSinistros = $sinistros->total();
        $qtdAbertos = $sinistros->whereIn('status', ['aberto', 'aguardando_documentos', 'em_analise'])->count();
        $qtdEncerrados = $sinistros->whereIn('status', ['encerrado', 'indenizado'])->count();
        $qtdCancelados = $sinistros->where('status', 'cancelado')->count();
    @endphp

    <div class="container-fluid px-0 vehicle-show-page sinistro-show-page">
        <div class="module-card mb-3">
            <div class="module-card-header sinistro-hero-compact">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-car-crash"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Veículos · Sinistros</div>
                        <h1 class="module-header-title">Sinistros registrados</h1>
                        <p class="module-header-sub">Acompanhe ocorrências, responsáveis, valores estimados e o andamento de cada caso.</p>
                    </div>
                </div>

                <div class="module-header-right">
                    @can('sinistro.export')
                        <a href="{{ route('sinistro.export', request()->query()) }}" class="btn-xp btn-xp--sm btn-xp--outline-light">
                            <i class="fas fa-file-csv"></i> CSV
                        </a>
                    @endcan
                    <a href="{{ route('sinistro.create') }}" class="btn-xp btn-xp--sm">
                        <i class="fas fa-plus"></i> Novo sinistro
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-xl-4 col-md-6 mb-2">
                <div class="kpi-card kpi-total">
                    <div class="kpi-icon"><i class="fas fa-list"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Registros</span>
                        <strong class="kpi-value">{{ $totalSinistros }}</strong>
                        <small class="kpi-subtitle">Sinistros no período filtrado</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-2">
                <div class="kpi-card kpi-unavailable">
                    <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Abertos</span>
                        <strong class="kpi-value">{{ $qtdAbertos }}</strong>
                        <small class="kpi-subtitle">Aguardando análise ou documentos</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-2">
                <div class="kpi-card kpi-available">
                    <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Encerrados</span>
                        <strong class="kpi-value">{{ $qtdEncerrados }}</strong>
                        <small class="kpi-subtitle">Casos resolvidos ou indenizados</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="config-card shadow-sm border-0 mb-3">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Filtros operacionais</span>
                    <h4 class="vs-panel-head__title mb-1">Refinar listagem</h4>
                    <p class="vs-panel-head__sub mb-0">Filtre por período, placa, tipo e status para localizar o sinistro certo com mais agilidade.</p>
                </div>
                <span class="vs-badge-soft is-warning">
                    <i class="fas fa-filter"></i>
                    Filtro rápido
                </span>
            </div>
            <div class="doc-filters">
                <form method="GET" action="{{ route('sinistro.index') }}" class="doc-filters__form" id="formFiltrosSinistro">
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
                            <label class="doc-filter-label">Tipo</label>
                            <select name="tipo_sinistro" class="form-control doc-filter-control">
                                <option value="">Todos</option>
                                @foreach(\App\Models\Sinistro::TIPOS as $key => $label)
                                    <option value="{{ $key }}" {{ ($filtros['tipo_sinistro'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 mb-2">
                            <label class="doc-filter-label">Status</label>
                            <select name="status" class="form-control doc-filter-control">
                                <option value="">Todos</option>
                                @foreach(\App\Models\Sinistro::STATUS as $key => $label)
                                    <option value="{{ $key }}" {{ ($filtros['status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-12 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-xp btn-xp--sm w-100 mr-2">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('sinistro.index') }}" class="btn btn-light btn-sm w-100">
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
                    <h4 class="vs-panel-head__title mb-1">Sinistros cadastrados</h4>
                    <p class="vs-panel-head__sub mb-0">Clique em uma linha para ver detalhes, editar ou avançar no fluxo de tratamento do caso.</p>
                </div>
                <span class="vs-badge-soft is-warning">
                    <i class="fas fa-list"></i>
                    {{ $totalSinistros }} registro(s)
                </span>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive vehicle-table-wrap">
                    <table class="table table-hover mb-0 vehicle-list-table">
                        <thead>
                            <tr>
                                <th>Sinistro</th>
                                <th>Veículo / Cliente</th>
                                <th>Ocorrido</th>
                                <th>Local / Seguradora</th>
                                <th>Valores</th>
                                <th>Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sinistros as $sinistro)
                                @php
                                    $codigoReserva = $sinistro->reserva?->codigoReserva?->codigo
                                        ?? ($sinistro->reserva_id ? 'RES-' . str_pad((string) $sinistro->reserva_id, 6, '0', STR_PAD_LEFT) : null);
                                    $badgeStatus = match($sinistro->status) {
                                        'encerrado', 'indenizado' => 'status-badge--green',
                                        'aprovado' => 'status-badge--blue',
                                        'cancelado' => 'status-badge--gray',
                                        'aguardando_documentos' => 'status-badge--amber',
                                        default => 'status-badge--red',
                                    };
                                @endphp
                                <tr onclick="window.location='{{ route('sinistro.show', $sinistro->id) }}';" style="cursor:pointer;">
                                    <td>
                                        <div class="table-value-stack">
                                            <span>#{{ $sinistro->id }} · {{ $sinistro->placa_momento ?? '-' }}</span>
                                            <small>{{ $codigoReserva ? 'Reserva #' . $codigoReserva : 'Sem reserva' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-value-stack">
                                            <span>{{ $sinistro->veiculo?->nome ?? '-' }}</span>
                                            <small>{{ $sinistro->cliente?->nome ?? '-' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-value-stack">
                                            <span>{{ $sinistro->data_hora_ocorrido?->format('d/m/Y H:i') ?? '-' }}</span>
                                            <small>{{ $sinistro->label_tipo }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-value-stack">
                                            <span>{{ \Illuminate\Support\Str::limit($sinistro->local_ocorrido ?? '-', 40) }}</span>
                                            <small>{{ $sinistro->seguradora ?? '-' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-value-stack">
                                            <span>{{ $sinistro->valor_prejuizo_estimado !== null ? 'R$ ' . number_format((float) $sinistro->valor_prejuizo_estimado, 2, ',', '.') : '-' }}</span>
                                            <small>{{ $sinistro->valor_responsabilidade_cliente !== null ? 'Cliente: R$ ' . number_format((float) $sinistro->valor_responsabilidade_cliente, 2, ',', '.') : 'Sem valor cliente' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $badgeStatus }}">{{ $sinistro->label_status }}</span>
                                    </td>
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <a href="{{ route('sinistro.show', $sinistro->id) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('sinistro.edit')
                                            <a href="{{ route('sinistro.edit', $sinistro->id) }}" class="btn btn-xs btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('sinistro.delete')
                                            <x-delete-button :action="route('sinistro.destroy', $sinistro->id)" />
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Nenhum sinistro encontrado para os filtros aplicados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                {{ $sinistros->withQueryString()->links() }}
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .sinistro-show-page { padding-bottom: 8px; }
    .module-card {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(12, 20, 40, .10);
        background: #fff;
    }
    .module-card-header {
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }
    .module-card-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255, 255, 255, 0.055) 1px, transparent 1px);
        background-size: 20px 20px;
        pointer-events: none;
    }
    .module-card-header::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981);
    }
    .module-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }
    .module-header-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #60a5fa;
        flex-shrink: 0;
        backdrop-filter: blur(8px);
    }
    .module-header-eyebrow {
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: rgba(148, 163, 184, .8);
        display: block;
        margin-bottom: 3px;
    }
    .module-header-title {
        font-size: 1.45rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 3px;
        letter-spacing: -.02em;
    }
    .module-header-sub {
        margin: 0;
        font-size: .82rem;
        color: rgba(148, 163, 184, .9);
    }
    .module-header-right {
        position: relative;
        z-index: 1;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .config-card {
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e2e9f4;
        box-shadow: 0 2px 12px rgba(12, 20, 40, .06);
    }
    .vs-panel-head {
        background: #fff;
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        border-bottom: 1px solid #e7edf6;
    }
    .vs-panel-head__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: #64748b;
    }
    .vs-panel-head__title {
        margin: 0 0 6px;
        font-size: 1.08rem;
        font-weight: 800;
        color: #0c1428;
    }
    .vs-panel-head__sub {
        margin: 0;
        color: #64748b;
        font-size: .84rem;
        max-width: 680px;
    }
    .vs-badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        background: #eef2ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .doc-filters {
        padding: 16px 20px 12px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .doc-filter-label {
        display: block;
        margin-bottom: 6px;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #475569;
    }
    .doc-filter-control {
        min-height: 42px;
        border-radius: 10px;
        border-color: #dbe4f0;
        box-shadow: none;
    }
    .doc-filter-control:focus {
        border-color: rgba(67, 56, 202, .45);
        box-shadow: 0 0 0 .2rem rgba(67, 56, 202, .10);
    }
    .vehicle-table-wrap {
        border-top: 1px solid #edf2f7;
        max-height: 74vh;
        overflow: auto;
    }
    .vehicle-list-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .vehicle-list-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8fafc;
        border-bottom: 1px solid #cbd5e1;
        font-size: 11px;
        text-transform: uppercase;
        color: #334155;
        letter-spacing: .02em;
        vertical-align: middle;
    }
    .vehicle-list-table tbody td {
        vertical-align: middle;
        background: #fff;
        border-top: 1px solid #edf2f7;
    }
    .vehicle-list-table tbody tr:hover td {
        background: #f8fafc;
    }
    .kpi-card {
        width: 100%;
        border: 0;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        color: #0f172a;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
        min-height: 96px;
        background: #fff;
    }
    .kpi-card .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex: 0 0 auto;
        color: #fff;
    }
    .kpi-meta .kpi-label {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        opacity: .9;
        font-weight: 700;
    }
    .kpi-meta .kpi-value {
        display: block;
        font-size: 28px;
        line-height: 1.05;
        margin-top: 2px;
    }
    .kpi-subtitle {
        display: block;
        color: #475569;
        margin-top: 2px;
    }
    .kpi-total { background: linear-gradient(135deg, #e2e8f0, #cbd5e1); }
    .kpi-total .kpi-icon { background: #94a3b8; }
    .kpi-available { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
    .kpi-available .kpi-icon { background: #16a34a; }
    .kpi-unavailable { background: linear-gradient(135deg, #fee2e2, #fecaca); }
    .kpi-unavailable .kpi-icon { background: #dc2626; }
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
    .table-value-stack {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .table-value-stack span {
        font-weight: 700;
        color: #0f172a;
    }
    .table-value-stack small {
        color: #64748b;
        font-size: .76rem;
    }
    @media (max-width: 991.98px) {
        .module-card-header {
            align-items: flex-start;
            flex-direction: column;
        }
        .module-header-right {
            width: 100%;
        }
        .module-header-right .btn-xp {
            flex: 1 1 auto;
            justify-content: center;
        }
        .vs-panel-head,
        .doc-filters {
            padding-left: 14px;
            padding-right: 14px;
        }
        .vehicle-list-table thead th,
        .vehicle-list-table tbody td { font-size: .86rem; }
    }
</style>
@endsection
