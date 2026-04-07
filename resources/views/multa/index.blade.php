@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('multa.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters ?? []" :filters="$filters" :itensFiltered="$itensFiltered ?? 0" />
    @endif

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS <span class="badge badge-danger">{{ $multas->total() }}</span>
                </h3>

                <div class="d-flex align-items-center flex-wrap" style="gap:.5rem">
                    {{-- Filtros rápidos --}}
                    <form method="GET" action="{{ route('multa.index') }}" class="d-flex align-items-center flex-wrap" style="gap:.4rem" id="formFiltrosMulta">
                        <input type="date" name="data_inicio" class="form-control form-control-sm" style="width:130px"
                               value="{{ $filtros['data_inicio'] ?? '' }}" title="Data infração de">
                        <input type="date" name="data_fim" class="form-control form-control-sm" style="width:130px"
                               value="{{ $filtros['data_fim'] ?? '' }}" title="Data infração até">
                        <input type="text" name="placa" class="form-control form-control-sm" style="width:100px"
                               value="{{ $filtros['placa'] ?? '' }}" placeholder="Placa">
                        <select name="status_multa" class="form-control form-control-sm" style="width:130px">
                            <option value="">Status multa</option>
                            @foreach(\App\Models\Multa::STATUS_MULTA as $key => $label)
                                <option value="{{ $key }}" {{ ($filtros['status_multa'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="status_desconto" class="form-control form-control-sm" style="width:130px">
                            <option value="">Status desconto</option>
                            @foreach(\App\Models\Multa::STATUS_DESCONTO as $key => $label)
                                <option value="{{ $key }}" {{ ($filtros['status_desconto'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-xs btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('multa.index') }}" class="btn btn-xs btn-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </form>

                    {{-- Exportar --}}
                    @can('multa.export')
                    <a href="{{ route('multa.export', request()->query()) }}" class="btn btn-xs btn-outline-success" title="Exportar CSV">
                        <i class="fas fa-file-csv mr-1"></i>CSV
                    </a>
                    @endcan

                    {{-- Toggle de view --}}
                    <div class="multa-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i>Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i>Tabela
                        </button>
                    </div>

                    {{-- Paginação --}}
                    <div class="d-flex align-items-end">
                        {{ $multas->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 multa-index-body" data-view="cards">

            {{-- VIEW CARDS --}}
            <div class="multa-cards">
                @forelse($multas as $multa)
                    @php
                        $badgeMulta = match($multa->status_multa) {
                            'paga'       => 'success',
                            'cancelada'  => 'secondary',
                            'deferida'   => 'info',
                            'indeferida' => 'warning',
                            default      => 'danger',
                        };
                        $badgeDesc = match($multa->status_desconto) {
                            'descontado'    => 'success',
                            'nao_descontar' => 'secondary',
                            'estornado'     => 'warning',
                            'pendente'      => 'info',
                            default         => 'light',
                        };
                        $accentClass = match($multa->status_multa) {
                            'paga'      => 'mt-status--green',
                            'cancelada' => 'mt-status--slate',
                            default     => 'mt-status--red',
                        };
                    @endphp

                    <article class="mt {{ $accentClass }}"
                             onclick="window.location='{{ route('multa.show', $multa->id) }}';">
                        <div class="mt__accent"></div>

                        <div class="mt__head">
                            <div class="mt__avatar">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="mt__head-info">
                                <div class="mt__id">MULTA #{{ $multa->id }}</div>
                                @if($multa->reserva_id)
                                    <div class="mt__id">Reserva #{{ $multa->reserva_id }}</div>
                                @endif
                                <div class="mt__title">{{ $multa->placa_momento ?? '—' }}</div>
                                <div class="mt__sub">{{ $multa->orgao_autuador ?? '—' }}</div>
                            </div>
                            <div class="mt__badges">
                                <span class="badge badge-{{ $badgeMulta }}">{{ $multa->label_status_multa }}</span>
                                <span class="badge badge-{{ $badgeDesc }}">{{ $multa->label_status_desconto }}</span>
                            </div>
                        </div>

                        <div class="mt__meta">
                            <div class="mt__meta-row">
                                <span class="mt__meta-label"><i class="fas fa-user mr-1"></i>Cliente</span>
                                <strong class="mt__meta-value">{{ $multa->cliente?->nome ?? '—' }}</strong>
                            </div>
                            <div class="mt__meta-row">
                                <span class="mt__meta-label"><i class="fas fa-car mr-1"></i>Veículo</span>
                                <strong class="mt__meta-value">{{ $multa->veiculo?->nome ?? '—' }}</strong>
                            </div>
                            <div class="mt__meta-row">
                                <span class="mt__meta-label"><i class="fas fa-calendar mr-1"></i>Infração</span>
                                <strong class="mt__meta-value">{{ $multa->data_hora_infracao?->format('d/m/Y') ?? '—' }}</strong>
                            </div>
                            <div class="mt__meta-row">
                                <span class="mt__meta-label"><i class="fas fa-clock mr-1"></i>Vencimento</span>
                                <strong class="mt__meta-value">{{ $multa->data_vencimento?->format('d/m/Y') ?? '—' }}</strong>
                            </div>
                            <div class="mt__meta-row mt__meta-row--full">
                                <span class="mt__meta-label"><i class="fas fa-dollar-sign mr-1"></i>Valor Final</span>
                                <strong class="mt__meta-value text-danger">R$ {{ number_format((float)$multa->valor_final, 2, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="mt__foot">
                            <a href="{{ route('multa.show', $multa->id) }}" class="btn btn-xs btn-info" onclick="event.stopPropagation();">
                                <i class="fas fa-eye mr-1"></i>Ver
                            </a>
                            @can('multa.edit')
                            <a href="{{ route('multa.edit', $multa->id) }}" class="btn btn-xs btn-warning" onclick="event.stopPropagation();">
                                <i class="fas fa-edit mr-1"></i>Editar
                            </a>
                            @endcan
                            @can('multa.delete')
                            <span onclick="event.stopPropagation();">
                                <x-delete-button :action="route('multa.destroy', $multa->id)" />
                            </span>
                            @endcan
                        </div>
                    </article>
                @empty
                    <div class="text-center py-5 text-muted w-100">
                        <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                        Nenhuma multa encontrada para os filtros aplicados.
                    </div>
                @endforelse
            </div>

            {{-- VIEW TABELA --}}
            <div class="multa-table table-responsive">
                <table class="table table-striped table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Reserva</th>
                            <th>Placa</th>
                            <th>Veículo</th>
                            <th>Cliente</th>
                            <th>Órgão</th>
                            <th>Data Infração</th>
                            <th>Vencimento</th>
                            <th class="text-right">Valor Final</th>
                            <th>Status Multa</th>
                            <th>Status Desconto</th>
                            <th style="width:110px"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($multas as $multa)
                        @php
                            $badgeMulta = match($multa->status_multa) {
                                'paga'       => 'success',
                                'cancelada'  => 'secondary',
                                'deferida'   => 'info',
                                'indeferida' => 'warning',
                                default      => 'danger',
                            };
                            $badgeDesc = match($multa->status_desconto) {
                                'descontado'    => 'success',
                                'nao_descontar' => 'secondary',
                                'estornado'     => 'warning',
                                'pendente'      => 'info',
                                default         => 'light',
                            };
                        @endphp
                        <tr style="cursor:pointer" onclick="window.location='{{ route('multa.show', $multa->id) }}'">
                            <td>{{ $multa->id }}</td>
                            <td>
                                @if($multa->reserva_id)
                                    <a href="{{ route('reserva.show', $multa->reserva_id) }}" onclick="event.stopPropagation()">#{{ $multa->reserva_id }}</a>
                                @else —
                                @endif
                            </td>
                            <td>{{ $multa->placa_momento ?? '—' }}</td>
                            <td>{{ $multa->veiculo?->nome ?? '—' }}</td>
                            <td>{{ $multa->cliente?->nome ?? '—' }}</td>
                            <td>{{ $multa->orgao_autuador ?? '—' }}</td>
                            <td>{{ $multa->data_hora_infracao?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $multa->data_vencimento?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-right font-weight-bold">R$ {{ number_format((float)$multa->valor_final, 2, ',', '.') }}</td>
                            <td><span class="badge badge-{{ $badgeMulta }}">{{ $multa->label_status_multa }}</span></td>
                            <td><span class="badge badge-{{ $badgeDesc }}">{{ $multa->label_status_desconto }}</span></td>
                            <td onclick="event.stopPropagation()">
                                <a href="{{ route('multa.show', $multa->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                @can('multa.edit')
                                <a href="{{ route('multa.edit', $multa->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('multa.delete')
                                <x-delete-button :action="route('multa.destroy', $multa->id)" />
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-2 pb-2">
                {{ $multas->withQueryString()->links() }}
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
/* ---- Cards de multa ---- */
.multa-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    padding: .75rem;
}
.mt {
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
.mt:hover { box-shadow: 0 4px 18px rgba(0,0,0,.10); transform: translateY(-2px); }

.mt__accent { height: 4px; width: 100%; }
.mt-status--red   .mt__accent { background: #dc3545; }
.mt-status--green .mt__accent { background: #28a745; }
.mt-status--slate .mt__accent { background: #6c757d; }

.mt__head { display: flex; align-items: flex-start; gap: .6rem; padding: .75rem .75rem .4rem; }
.mt__avatar {
    width: 38px; height: 38px; border-radius: 8px;
    background: #fff3f3; color: #dc3545;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.mt-status--green .mt__avatar { background: #f0fff4; color: #28a745; }
.mt-status--slate .mt__avatar { background: #f5f5f5; color: #6c757d; }
.mt__head-info { flex: 1; min-width: 0; }
.mt__id   { font-size: .68rem; color: #aaa; font-weight: 600; letter-spacing: .04em; }
.mt__title{ font-size: .95rem; font-weight: 700; color: #222; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mt__sub  { font-size: .75rem; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.mt__badges { display: flex; flex-direction: column; gap: .2rem; align-items: flex-end; flex-shrink: 0; }

.mt__meta { padding: .4rem .75rem; flex: 1; }
.mt__meta-row { display: flex; justify-content: space-between; padding: .18rem 0; border-bottom: 1px solid #f0f0f0; font-size: .78rem; }
.mt__meta-row--full { border-bottom: none; }
.mt__meta-label { color: #888; }
.mt__meta-value { color: #333; text-align: right; max-width: 55%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.mt__foot { padding: .5rem .75rem; display: flex; gap: .35rem; border-top: 1px solid #f0f0f0; flex-wrap: wrap; }

/* ---- Toggle view ---- */
.multa-table { display: none; }
.multa-index-body[data-view="cards"] .multa-cards { display: flex; }
.multa-index-body[data-view="table"] .multa-cards  { display: none; }
.multa-index-body[data-view="table"] .multa-table  { display: block; }
</style>
@stop

@section('js')
<script>
(function () {
    const body    = document.querySelector('.multa-index-body');
    const buttons = document.querySelectorAll('.multa-view-toggle [data-view]');
    const STORAGE_KEY = 'multa_index_view';

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
