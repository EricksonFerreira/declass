@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('sinistro.create')" />
@stop

@section('content')

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS <span class="badge badge-danger">{{ $sinistros->total() }}</span>
                </h3>

                <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                    {{-- Filtros rápidos --}}
                    <form method="GET" action="{{ route('sinistro.index') }}" class="d-flex align-items-center flex-wrap" style="gap:.4rem" id="formFiltrosSinistro">
                        <input type="date" name="data_inicio" class="form-control form-control-sm" style="width:130px"
                               value="{{ $filtros['data_inicio'] ?? '' }}" title="Data ocorrido de">
                        <input type="date" name="data_fim" class="form-control form-control-sm" style="width:130px"
                               value="{{ $filtros['data_fim'] ?? '' }}" title="Data ocorrido até">
                        <input type="text" name="placa" class="form-control form-control-sm" style="width:100px"
                               value="{{ $filtros['placa'] ?? '' }}" placeholder="Placa">
                        <select name="tipo_sinistro" class="form-control form-control-sm" style="width:130px">
                            <option value="">Tipo</option>
                            @foreach(\App\Models\Sinistro::TIPOS as $key => $label)
                                <option value="{{ $key }}" {{ ($filtros['tipo_sinistro'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="form-control form-control-sm" style="width:140px">
                            <option value="">Status</option>
                            @foreach(\App\Models\Sinistro::STATUS as $key => $label)
                                <option value="{{ $key }}" {{ ($filtros['status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-xs btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('sinistro.index') }}" class="btn btn-xs btn-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </form>

                    {{-- Exportar --}}
                    @can('sinistro.export')
                    <a href="{{ route('sinistro.export', request()->query()) }}" class="btn btn-xs btn-outline-success" title="Exportar CSV">
                        <i class="fas fa-file-csv mr-1"></i>CSV
                    </a>
                    @endcan

                    {{-- Toggle de view --}}
                    <div class="sn-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i>Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i>Tabela
                        </button>
                    </div>

                    {{-- Paginação --}}
                    <div class="d-flex align-items-end">
                        {{ $sinistros->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 sn-index-body" data-view="cards">

            {{-- VIEW CARDS --}}
            <div class="sn-cards">
                @forelse($sinistros as $sinistro)
                    @php
                        $badgeStatus = match($sinistro->status) {
                            'encerrado'  => 'success',
                            'indenizado' => 'info',
                            'aprovado'   => 'primary',
                            'cancelado'  => 'secondary',
                            'aguardando_documentos' => 'warning',
                            default      => 'danger',
                        };
                        $iconeTipo = match($sinistro->tipo_sinistro) {
                            'furto_roubo' => 'fa-user-secret',
                            'incendio'    => 'fa-fire',
                            'vandalismo'  => 'fa-hammer',
                            'alagamento'  => 'fa-water',
                            default       => 'fa-car-crash',
                        };
                        $accentClass = match($sinistro->status) {
                            'encerrado', 'indenizado' => 'sn-status--green',
                            'cancelado'               => 'sn-status--slate',
                            default                   => 'sn-status--red',
                        };
                    @endphp

                    <article class="sn {{ $accentClass }}"
                             onclick="window.location='{{ route('sinistro.show', $sinistro->id) }}';">
                        <div class="sn__accent"></div>

                        <div class="sn__head">
                            <div class="sn__avatar">
                                <i class="fas {{ $iconeTipo }}"></i>
                            </div>
                            <div class="sn__head-info">
                                <div class="sn__id">SINISTRO #{{ $sinistro->id }}</div>
                                @if($sinistro->reserva_id)
                                    <div class="sn__id">Reserva #{{ $sinistro->reserva_id }}</div>
                                @endif
                                <div class="sn__title">{{ $sinistro->placa_momento ?? '—' }}</div>
                                <div class="sn__sub">{{ $sinistro->label_tipo }}</div>
                            </div>
                            <div class="sn__badges">
                                <span class="badge badge-{{ $badgeStatus }}">{{ $sinistro->label_status }}</span>
                            </div>
                        </div>

                        <div class="sn__meta">
                            <div class="sn__meta-row">
                                <span class="sn__meta-label"><i class="fas fa-car mr-1"></i>Veículo</span>
                                <strong class="sn__meta-value">{{ $sinistro->veiculo?->nome ?? '—' }}</strong>
                            </div>
                            <div class="sn__meta-row">
                                <span class="sn__meta-label"><i class="fas fa-user mr-1"></i>Cliente</span>
                                <strong class="sn__meta-value">{{ $sinistro->cliente?->nome ?? '—' }}</strong>
                            </div>
                            <div class="sn__meta-row">
                                <span class="sn__meta-label"><i class="fas fa-calendar mr-1"></i>Ocorrido</span>
                                <strong class="sn__meta-value">{{ $sinistro->data_hora_ocorrido?->format('d/m/Y') ?? '—' }}</strong>
                            </div>
                            <div class="sn__meta-row">
                                <span class="sn__meta-label"><i class="fas fa-map-marker-alt mr-1"></i>Local</span>
                                <strong class="sn__meta-value">{{ Str::limit($sinistro->local_ocorrido ?? '—', 30) }}</strong>
                            </div>
                            @if($sinistro->valor_prejuizo_estimado)
                            <div class="sn__meta-row sn__meta-row--full">
                                <span class="sn__meta-label"><i class="fas fa-dollar-sign mr-1"></i>Prejuízo Est.</span>
                                <strong class="sn__meta-value text-danger">R$ {{ number_format((float)$sinistro->valor_prejuizo_estimado, 2, ',', '.') }}</strong>
                            </div>
                            @endif
                        </div>

                        <div class="sn__foot">
                            <a href="{{ route('sinistro.show', $sinistro->id) }}" class="btn btn-xs btn-info" onclick="event.stopPropagation();">
                                <i class="fas fa-eye mr-1"></i>Ver
                            </a>
                            @can('sinistro.edit')
                            <a href="{{ route('sinistro.edit', $sinistro->id) }}" class="btn btn-xs btn-warning" onclick="event.stopPropagation();">
                                <i class="fas fa-edit mr-1"></i>Editar
                            </a>
                            @endcan
                            @can('sinistro.delete')
                            <span onclick="event.stopPropagation();">
                                <x-delete-button :action="route('sinistro.destroy', $sinistro->id)" />
                            </span>
                            @endcan
                        </div>
                    </article>
                @empty
                    <div class="text-center py-5 text-muted w-100">
                        <i class="fas fa-shield-alt fa-2x mb-2 d-block"></i>
                        Nenhum sinistro encontrado para os filtros aplicados.
                    </div>
                @endforelse
            </div>

            {{-- VIEW TABELA --}}
            <div class="sn-table table-responsive">
                <table class="table table-striped table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Placa</th>
                            <th>Veículo</th>
                            <th>Cliente</th>
                            <th>Data Ocorrido</th>
                            <th>Local</th>
                            <th>Seguradora</th>
                            <th class="text-right">Prejuízo Est.</th>
                            <th>Status</th>
                            <th style="width:110px"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($sinistros as $sinistro)
                        @php
                            $badgeStatus = match($sinistro->status) {
                                'encerrado', 'indenizado' => 'success',
                                'aprovado'   => 'primary',
                                'cancelado'  => 'secondary',
                                'aguardando_documentos' => 'warning',
                                default      => 'danger',
                            };
                        @endphp
                        <tr style="cursor:pointer" onclick="window.location='{{ route('sinistro.show', $sinistro->id) }}'">
                            <td>{{ $sinistro->id }}</td>
                            <td>{{ $sinistro->label_tipo }}</td>
                            <td>{{ $sinistro->placa_momento ?? '—' }}</td>
                            <td>{{ $sinistro->veiculo?->nome ?? '—' }}</td>
                            <td>{{ $sinistro->cliente?->nome ?? '—' }}</td>
                            <td>{{ $sinistro->data_hora_ocorrido?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>{{ Str::limit($sinistro->local_ocorrido ?? '—', 30) }}</td>
                            <td>{{ $sinistro->seguradora ?? '—' }}</td>
                            <td class="text-right">
                                {{ $sinistro->valor_prejuizo_estimado !== null ? 'R$ ' . number_format((float)$sinistro->valor_prejuizo_estimado, 2, ',', '.') : '—' }}
                            </td>
                            <td><span class="badge badge-{{ $badgeStatus }}">{{ $sinistro->label_status }}</span></td>
                            <td onclick="event.stopPropagation()">
                                <a href="{{ route('sinistro.show', $sinistro->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                @can('sinistro.edit')
                                <a href="{{ route('sinistro.edit', $sinistro->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('sinistro.delete')
                                <x-delete-button :action="route('sinistro.destroy', $sinistro->id)" />
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-2 pb-2">
                {{ $sinistros->withQueryString()->links() }}
            </div>
        </div>
    </div>

@stop

@section('css')
<style>
/* ---- Cards de sinistro ---- */
.sn-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding: .75rem;
}
.sn {
    position: relative;
    background: #fff;
    border: 1px solid #e4e9f0;
    border-radius: 10px;
    width: 280px;
    min-width: 260px;
    flex: 1 1 260px;
    max-width: 340px;
    overflow: hidden;
    cursor: pointer;
    transition: box-shadow .18s, transform .15s;
    display: flex;
    flex-direction: column;
}
.sn:hover { box-shadow: 0 4px 18px rgba(0,0,0,.10); transform: translateY(-2px); }

.sn__accent { height: 4px; width: 100%; }
.sn-status--red   .sn__accent { background: #dc3545; }
.sn-status--green .sn__accent { background: #28a745; }
.sn-status--slate .sn__accent { background: #6c757d; }

.sn__head { display: flex; align-items: flex-start; gap: .6rem; padding: .75rem .75rem .4rem; }
.sn__avatar {
    width: 38px; height: 38px; border-radius: 8px;
    background: #fff3f3; color: #dc3545;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.sn-status--green .sn__avatar { background: #f0fff4; color: #28a745; }
.sn-status--slate .sn__avatar { background: #f5f5f5; color: #6c757d; }
.sn__head-info { flex: 1; min-width: 0; }
.sn__id   { font-size: .68rem; color: #aaa; font-weight: 600; letter-spacing: .04em; }
.sn__title{ font-size: .95rem; font-weight: 700; color: #222; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sn__sub  { font-size: .75rem; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sn__badges { display: flex; flex-direction: column; gap: .2rem; align-items: flex-end; flex-shrink: 0; }

.sn__meta { padding: .4rem .75rem; flex: 1; }
.sn__meta-row { display: flex; justify-content: space-between; padding: .18rem 0; border-bottom: 1px solid #f0f0f0; font-size: .78rem; }
.sn__meta-row--full { border-bottom: none; }
.sn__meta-label { color: #888; }
.sn__meta-value { color: #333; text-align: right; max-width: 55%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.sn__foot { padding: .5rem .75rem; display: flex; gap: .35rem; border-top: 1px solid #f0f0f0; flex-wrap: wrap; }

/* ---- Toggle view ---- */
.sn-table { display: none; }
.sn-index-body[data-view="cards"] .sn-cards { display: flex; }
.sn-index-body[data-view="table"] .sn-cards  { display: none; }
.sn-index-body[data-view="table"] .sn-table  { display: block; }
</style>
@stop

@section('js')
<script>
(function () {
    const body    = document.querySelector('.sn-index-body');
    const buttons = document.querySelectorAll('.sn-view-toggle [data-view]');
    const STORAGE_KEY = 'sinistro_index_view';

    function setView(view) {
        if (!body) return;
        body.dataset.view = view;
        buttons.forEach(b => b.classList.toggle('active', b.dataset.view === view));
        try { localStorage.setItem(STORAGE_KEY, view); } catch(e) {}
    }

    buttons.forEach(b => b.addEventListener('click', () => setView(b.dataset.view)));

    const saved = (() => { try { return localStorage.getItem(STORAGE_KEY); } catch(e) { return null; } })();
    setView(saved || 'cards');
})();
</script>
@stop
