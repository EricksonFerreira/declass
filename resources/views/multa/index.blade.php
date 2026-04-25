@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('multa.create')" />
@stop

@section('content')
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS <span class="badge badge-danger">{{ $multas->total() }}</span>
                </h3>

                <div class="d-flex flex-row align-items-center flex-wrap" style="gap:6px;">
                    <form method="GET" action="{{ route('multa.index') }}" class="d-flex align-items-center flex-wrap" style="gap:6px;" id="formFiltrosMulta">
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
                        <select name="status_desconto" class="form-control form-control-sm" style="width:140px">
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

                    @can('multa.export')
                    <a href="{{ route('multa.export', request()->query()) }}" class="btn btn-xs btn-outline-success" title="Exportar CSV">
                        <i class="fas fa-file-csv mr-1"></i>CSV
                    </a>
                    @endcan

                    <div class="mx-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i>Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i>Tabela
                        </button>
                    </div>

                    <div class="d-flex align-items-end">
                        {{ $multas->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 mx-index-body" data-view="cards">
            <div class="mx-cards">
                @forelse($multas as $multa)
                    @php
                        $badgeMulta = match($multa->status_multa) {
                            'paga'       => 'mx__badge--green',
                            'cancelada'  => 'mx__badge--slate',
                            'deferida'   => 'mx__badge--blue',
                            'indeferida' => 'mx__badge--amber',
                            default      => 'mx__badge--red',
                        };
                        $badgeDesc = match($multa->status_desconto) {
                            'descontado'    => 'mx__badge--green',
                            'nao_descontar' => 'mx__badge--slate',
                            'estornado'     => 'mx__badge--amber',
                            'pendente'      => 'mx__badge--blue',
                            default         => 'mx__badge--slate',
                        };
                        $accentCls = match($multa->status_multa) {
                            'paga'      => 'mx--green',
                            'cancelada' => 'mx--slate',
                            default     => 'mx--red',
                        };
                        $placa = trim((string) ($multa->placa_momento ?? ''));
                        $iniciais = $placa !== '' ? mb_strtoupper(mb_substr($placa, 0, 3)) : 'MTA';
                    @endphp

                    <article class="mx {{ $accentCls }}"
                             onclick="window.location='{{ route('multa.show', $multa->id) }}';">
                        <div class="mx__accent"></div>

                        <div class="mx__head">
                            <div class="mx__avatar">{{ $iniciais }}</div>
                            <div class="mx__head-info">
                                <div class="mx__id">MULTA #{{ $multa->id }}</div>
                                <div class="mx__title">{{ $multa->placa_momento ?? 'Placa não informada' }}</div>
                                <div class="mx__sub">
                                    {{ $multa->orgao_autuador ?? 'Órgão não informado' }}
                                    @if($multa->reserva_id)
                                        · Reserva #{{ $multa->reserva_id }}
                                    @endif
                                </div>
                            </div>
                            <div class="mx__badges">
                                <span class="mx__badge {{ $badgeMulta }}">{{ $multa->label_status_multa }}</span>
                                <span class="mx__badge {{ $badgeDesc }}">{{ $multa->label_status_desconto }}</span>
                            </div>
                        </div>

                        <div class="mx__meta">
                            <div class="mx__meta-row">
                                <span class="mx__meta-label"><i class="fas fa-user mr-1"></i>Cliente</span>
                                <strong class="mx__meta-value">{{ $multa->cliente?->nome ?? '-' }}</strong>
                            </div>
                            <div class="mx__meta-row">
                                <span class="mx__meta-label"><i class="fas fa-car mr-1"></i>Veículo</span>
                                <strong class="mx__meta-value">{{ $multa->veiculo?->nome ?? '-' }}</strong>
                            </div>
                            <div class="mx__meta-row">
                                <span class="mx__meta-label"><i class="fas fa-calendar mr-1"></i>Infração</span>
                                <strong class="mx__meta-value">{{ $multa->data_hora_infracao?->format('d/m/Y') ?? '-' }}</strong>
                            </div>
                            <div class="mx__meta-row">
                                <span class="mx__meta-label"><i class="fas fa-clock mr-1"></i>Vencimento</span>
                                <strong class="mx__meta-value">{{ $multa->data_vencimento?->format('d/m/Y') ?? '-' }}</strong>
                            </div>
                            <div class="mx__meta-row mx__meta-row--full">
                                <span class="mx__meta-label"><i class="fas fa-dollar-sign mr-1"></i>Valor final</span>
                                <strong class="mx__meta-value">R$ {{ number_format((float) $multa->valor_final, 2, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="mx__foot">
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

            <div class="mx-table table-responsive">
                <table class="table table-striped table-bordered table-sm table-multa-index">
                    <thead>
                        <tr>
                            <th>Multa</th>
                            <th>Cliente / Veículo</th>
                            <th>Órgão / Datas</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th style="width:220px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($multas as $multa)
                            @php
                                $badgeMultaTable = match($multa->status_multa) {
                                    'paga'       => 'success',
                                    'cancelada'  => 'secondary',
                                    'deferida'   => 'info',
                                    'indeferida' => 'warning',
                                    default      => 'danger',
                                };
                                $badgeDescTable = match($multa->status_desconto) {
                                    'descontado'    => 'success',
                                    'nao_descontar' => 'secondary',
                                    'estornado'     => 'warning',
                                    'pendente'      => 'info',
                                    default         => 'light',
                                };
                            @endphp
                            <tr onclick="window.location='{{ route('multa.show', $multa->id) }}';" style="cursor:pointer;">
                                <td>
                                    <div class="font-weight-bold">#{{ $multa->id }} · {{ $multa->placa_momento ?? '-' }}</div>
                                    <small class="text-muted">
                                        @if($multa->reserva_id)
                                            Reserva #{{ $multa->reserva_id }}
                                        @else
                                            Sem reserva
                                        @endif
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
                                    <span class="badge badge-{{ $badgeMultaTable }}">{{ $multa->label_status_multa }}</span>
                                    <span class="badge badge-{{ $badgeDescTable }}">{{ $multa->label_status_desconto }}</span>
                                </td>
                                <td onclick="event.stopPropagation();" style="width:220px;">
                                    <a href="{{ route('multa.show', $multa->id) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    @can('multa.edit')
                                    <a href="{{ route('multa.edit', $multa->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
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
.mx-view-toggle { display:flex; gap:4px; }
.mx-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 16px;
    padding: 10px 0;
}
.mx {
    position: relative;
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid #e5eaf2;
    border-radius: 14px;
    overflow: hidden;
    cursor: pointer;
    transition: box-shadow .18s, transform .15s;
}
.mx:hover { box-shadow: 0 6px 24px rgba(0,0,0,.10); transform: translateY(-2px); }
.mx__accent { height: 5px; width: 100%; }
.mx--green .mx__accent { background: linear-gradient(90deg, #10b981, #34d399); }
.mx--red .mx__accent { background: linear-gradient(90deg, #ef4444, #f87171); }
.mx--slate .mx__accent { background: linear-gradient(90deg, #64748b, #94a3b8); }
.mx__head { display:flex; align-items:flex-start; gap:12px; padding:14px 16px 8px; }
.mx__avatar {
    width:44px; height:44px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:.95rem; color:#fff;
    background: linear-gradient(135deg, #dc2626, #f97316);
}
.mx__head-info { flex:1; min-width:0; }
.mx__id { font-size:.72rem; color:#94a3b8; font-weight:600; letter-spacing:.04em; }
.mx__title { font-size:.97rem; font-weight:700; color:#0f2740; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.mx__sub { font-size:.78rem; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.mx__badges { display:flex; flex-direction:column; gap:4px; }
.mx__badge {
    display:inline-block; padding:2px 8px; border-radius:20px;
    font-size:.7rem; font-weight:700; text-transform:uppercase; white-space:nowrap;
}
.mx__badge--green { background:rgba(16,185,129,.15); color:#047857; }
.mx__badge--red { background:rgba(239,68,68,.13); color:#b91c1c; }
.mx__badge--slate { background:rgba(100,116,139,.12); color:#475569; }
.mx__badge--blue { background:rgba(59,130,246,.12); color:#1d4ed8; }
.mx__badge--amber { background:rgba(245,158,11,.18); color:#92400e; }
.mx__meta { display:flex; flex-direction:column; gap:4px; padding:6px 16px 10px; flex:1; }
.mx__meta-row { display:flex; align-items:baseline; gap:6px; font-size:.8rem; }
.mx__meta-row--full { flex-wrap:wrap; }
.mx__meta-label { color:#64748b; min-width:90px; flex-shrink:0; }
.mx__meta-value { color:#1e293b; font-size:.8rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px; }
.mx__foot {
    display:flex; gap:6px; align-items:center;
    padding:10px 16px; border-top:1px solid #f1f5f9;
    background:#fafbfc; flex-wrap:wrap;
}
.mx-table { display:none; }
.table-multa-index td { vertical-align:middle; }
.mx-index-body[data-view="table"] .mx-cards { display:none; }
.mx-index-body[data-view="table"] .mx-table  { display:block; }
.mx-index-body[data-view="cards"] .mx-cards  { display:grid; }
.mx-index-body[data-view="cards"] .mx-table  { display:none; }
@media (max-width:575px) {
    .mx-cards { grid-template-columns: 1fr; }
}
</style>
@stop

@section('js')
<script>
(function () {
    const body = document.querySelector('.mx-index-body');
    const btns = document.querySelectorAll('.mx-view-toggle [data-view]');
    const store = 'multa_index_view';

    function setView(view) {
        if (!body) return;
        body.dataset.view = view;
        btns.forEach(btn => btn.classList.toggle('active', btn.dataset.view === view));
        localStorage.setItem(store, view);
    }

    btns.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));
    setView(localStorage.getItem(store) || 'cards');
})();
</script>
@stop
