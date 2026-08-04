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
        <div class="vs-panel shadow-sm border-0 mb-3">
            <div class="vs-panel-head">
                <div>
                    <span class="vs-panel-head__eyebrow">Veículos · Sinistros</span>
                    <h4 class="vs-panel-head__title mb-1">Sinistros registrados</h4>
                    <p class="vs-panel-head__sub mb-0">Acompanhe ocorrências, responsáveis, valores estimados e o andamento de cada caso.</p>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                    @can('sinistro.export')
                        <a href="{{ route('sinistro.export', request()->query()) }}" class="btn-xp btn-xp--sm btn-xp--outline">
                            <i class="fas fa-file-csv mr-1"></i> CSV
                        </a>
                    @endcan
                    <a href="{{ route('sinistro.create') }}" class="btn-xp btn-xp--sm">
                        <i class="fas fa-plus mr-1"></i> Novo sinistro
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
                        <strong class="kpi-value">{{ $totalSinistros }}</strong>
                        <small class="kpi-subtitle">Sinistros no período filtrado</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-unavailable">
                    <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Abertos</span>
                        <strong class="kpi-value">{{ $qtdAbertos }}</strong>
                        <small class="kpi-subtitle">Aguardando análise ou documentos</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
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
                                <i class="fas fa-search mr-1"></i> Filtrar
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
                                <th>Prejuízo</th>
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
                                        <div class="font-weight-bold">#{{ $sinistro->id }} · {{ $sinistro->placa_momento ?? '-' }}</div>
                                        <small class="text-muted">{{ $sinistro->label_tipo }}</small>
                                        <br>
                                        <small class="text-muted">{{ $codigoReserva ? 'Reserva #' . $codigoReserva : 'Sem reserva' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $sinistro->veiculo?->nome ?? '-' }}</div>
                                        <small class="text-muted">{{ $sinistro->cliente?->nome ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $sinistro->data_hora_ocorrido?->format('d/m/Y H:i') ?? '-' }}</div>
                                        <small class="text-muted">{{ $sinistro->label_tipo }}</small>
                                    </td>
                                    <td>
                                        <div>{{ Str::limit($sinistro->local_ocorrido ?? '-', 40) }}</div>
                                        <small class="text-muted">{{ $sinistro->seguradora ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">
                                            {{ $sinistro->valor_prejuizo_estimado !== null ? 'R$ ' . number_format((float) $sinistro->valor_prejuizo_estimado, 2, ',', '.') : '-' }}
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
    .vehicle-list-table { min-width: 1020px; margin-bottom: 0; }
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
        .vehicle-list-table thead th,
        .vehicle-list-table tbody td { font-size: .86rem; }
        .doc-filters { padding-top: .9rem; padding-bottom: .9rem; }
    }
</style>
@endsection
