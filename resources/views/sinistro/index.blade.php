@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[[ 'name' => 'VeÃ­culos', 'url' => route('veiculo.index') ], [ 'name' => 'Sinistros' ]]" />
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
                    <span class="vs-panel-head__eyebrow">VeÃ­culos Â· Sinistros</span>
                    <h4 class="vs-panel-head__title mb-1">Sinistros registrados</h4>
                    <p class="vs-panel-head__sub mb-0">Acompanhe ocorrÃªncias, responsÃ¡veis, valores estimados e o andamento de cada caso.</p>
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
                        <small class="kpi-subtitle">Sinistros no perÃ­odo filtrado</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-2">
                <div class="kpi-card kpi-unavailable">
                    <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Abertos</span>
                        <strong class="kpi-value">{{ $qtdAbertos }}</strong>
                        <small class="kpi-subtitle">Aguardando anÃ¡lise ou documentos</small>
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
                    <p class="vs-panel-head__sub mb-0">Filtre por perÃ­odo, placa, tipo e status para localizar o sinistro certo com mais agilidade.</p>
                </div>
                <span class="vs-badge-soft is-warning">
                    <i class="fas fa-filter"></i>
                    Filtro rÃ¡pido
                </span>
            </div>
            <div class="doc-filters">
                <form method="GET" action="{{ route('sinistro.index') }}" class="doc-filters__form" id="formFiltrosSinistro">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="doc-filter-label">Data inÃ­cio</label>
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
                    <p class="vs-panel-head__sub mb-0">Clique em uma linha para ver detalhes, editar ou avanÃ§ar no fluxo de tratamento do caso.</p>
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
                                <th>VeÃ­culo / Cliente</th>
                                <th>Ocorrido</th>
                                <th>Local / Seguradora</th>
                                <th>PrejuÃ­zo</th>
                                <th>Status</th>
                                <th class="text-center">AÃ§Ãµes</th>
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
                                        <div class="font-weight-bold">#{{ $sinistro->id }} Â· {{ $sinistro->placa_momento ?? '-' }}</div>
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
@include('partials.visual.documentacao-pattern')
@endsection
