@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('sinistro.create')" />
@stop

@section('content')
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS <span class="badge badge-danger">{{ $sinistros->total() }}</span>
                </h3>

                <div class="d-flex flex-row align-items-center flex-wrap" style="gap:6px;">
                    <form method="GET" action="{{ route('sinistro.index') }}" class="d-flex align-items-center flex-wrap" style="gap:6px;" id="formFiltrosSinistro">
                        <input type="date" name="data_inicio" class="form-control form-control-sm" style="width:130px"
                               value="{{ $filtros['data_inicio'] ?? '' }}" title="Data ocorrido de">
                        <input type="date" name="data_fim" class="form-control form-control-sm" style="width:130px"
                               value="{{ $filtros['data_fim'] ?? '' }}" title="Data ocorrido até">
                        <input type="text" name="placa" class="form-control form-control-sm" style="width:100px"
                               value="{{ $filtros['placa'] ?? '' }}" placeholder="Placa">
                        <select name="tipo_sinistro" class="form-control form-control-sm" style="width:140px">
                            <option value="">Tipo</option>
                            @foreach(\App\Models\Sinistro::TIPOS as $key => $label)
                                <option value="{{ $key }}" {{ ($filtros['tipo_sinistro'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="form-control form-control-sm" style="width:150px">
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

                    @can('sinistro.export')
                    <a href="{{ route('sinistro.export', request()->query()) }}" class="btn btn-xs btn-outline-success" title="Exportar CSV">
                        <i class="fas fa-file-csv mr-1"></i>CSV
                    </a>
                    @endcan

                    <div class="sx-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i>Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i>Tabela
                        </button>
                    </div>

                    <div class="d-flex align-items-end">
                        {{ $sinistros->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 sx-index-body" data-view="cards">
            <div class="sx-cards">
                @forelse($sinistros as $sinistro)
                    @php
                        $badgeStatus = match($sinistro->status) {
                            'encerrado', 'indenizado' => 'sx__badge--green',
                            'aprovado' => 'sx__badge--blue',
                            'cancelado' => 'sx__badge--slate',
                            'aguardando_documentos' => 'sx__badge--amber',
                            default => 'sx__badge--red',
                        };
                        $accentCls = match($sinistro->status) {
                            'encerrado', 'indenizado' => 'sx--green',
                            'cancelado' => 'sx--slate',
                            default => 'sx--red',
                        };
                        $placa = trim((string) ($sinistro->placa_momento ?? ''));
                        $iniciais = $placa !== '' ? mb_strtoupper(mb_substr($placa, 0, 3)) : 'SIN';
                    @endphp

                    <article class="sx {{ $accentCls }}"
                             onclick="window.location='{{ route('sinistro.show', $sinistro->id) }}';">
                        <div class="sx__accent"></div>

                        <div class="sx__head">
                            <div class="sx__avatar">{{ $iniciais }}</div>
                            <div class="sx__head-info">
                                <div class="sx__id">SINISTRO #{{ $sinistro->id }}</div>
                                <div class="sx__title">{{ $sinistro->placa_momento ?? 'Placa não informada' }}</div>
                                <div class="sx__sub">
                                    {{ $sinistro->label_tipo }}
                                    @if($sinistro->reserva_id)
                                        · Reserva #{{ $sinistro->reserva_id }}
                                    @endif
                                </div>
                            </div>
                            <div class="sx__badges">
                                <span class="sx__badge {{ $badgeStatus }}">{{ $sinistro->label_status }}</span>
                            </div>
                        </div>

                        <div class="sx__meta">
                            <div class="sx__meta-row">
                                <span class="sx__meta-label"><i class="fas fa-car mr-1"></i>Veículo</span>
                                <strong class="sx__meta-value">{{ $sinistro->veiculo?->nome ?? '-' }}</strong>
                            </div>
                            <div class="sx__meta-row">
                                <span class="sx__meta-label"><i class="fas fa-user mr-1"></i>Cliente</span>
                                <strong class="sx__meta-value">{{ $sinistro->cliente?->nome ?? '-' }}</strong>
                            </div>
                            <div class="sx__meta-row">
                                <span class="sx__meta-label"><i class="fas fa-calendar mr-1"></i>Ocorrido</span>
                                <strong class="sx__meta-value">{{ $sinistro->data_hora_ocorrido?->format('d/m/Y') ?? '-' }}</strong>
                            </div>
                            <div class="sx__meta-row">
                                <span class="sx__meta-label"><i class="fas fa-map-marker-alt mr-1"></i>Local</span>
                                <strong class="sx__meta-value">{{ Str::limit($sinistro->local_ocorrido ?? '-', 26) }}</strong>
                            </div>
                            <div class="sx__meta-row sx__meta-row--full">
                                <span class="sx__meta-label"><i class="fas fa-dollar-sign mr-1"></i>Prejuízo est.</span>
                                <strong class="sx__meta-value">
                                    {{ $sinistro->valor_prejuizo_estimado !== null ? 'R$ ' . number_format((float) $sinistro->valor_prejuizo_estimado, 2, ',', '.') : '-' }}
                                </strong>
                            </div>
                        </div>

                        <div class="sx__foot">
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

            <div class="sx-table table-responsive">
                <table class="table table-striped table-bordered table-sm table-sinistro-index">
                    <thead>
                        <tr>
                            <th>Sinistro</th>
                            <th>Veículo / Cliente</th>
                            <th>Ocorrido</th>
                            <th>Local / Seguradora</th>
                            <th>Prejuízo</th>
                            <th>Status</th>
                            <th style="width:220px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sinistros as $sinistro)
                            @php
                                $badgeStatusTable = match($sinistro->status) {
                                    'encerrado', 'indenizado' => 'success',
                                    'aprovado' => 'primary',
                                    'cancelado' => 'secondary',
                                    'aguardando_documentos' => 'warning',
                                    default => 'danger',
                                };
                            @endphp
                            <tr onclick="window.location='{{ route('sinistro.show', $sinistro->id) }}';" style="cursor:pointer;">
                                <td>
                                    <div class="font-weight-bold">#{{ $sinistro->id }} · {{ $sinistro->placa_momento ?? '-' }}</div>
                                    <small class="text-muted">{{ $sinistro->label_tipo }}</small>
                                </td>
                                <td>
                                    <div>{{ $sinistro->veiculo?->nome ?? '-' }}</div>
                                    <small class="text-muted">{{ $sinistro->cliente?->nome ?? '-' }}</small>
                                </td>
                                <td>
                                    <div>{{ $sinistro->data_hora_ocorrido?->format('d/m/Y H:i') ?? '-' }}</div>
                                    <small class="text-muted">
                                        @if($sinistro->reserva_id)
                                            Reserva #{{ $sinistro->reserva_id }}
                                        @else
                                            Sem reserva
                                        @endif
                                    </small>
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
                                    <span class="badge badge-{{ $badgeStatusTable }}">{{ $sinistro->label_status }}</span>
                                </td>
                                <td onclick="event.stopPropagation();" style="width:220px;">
                                    <a href="{{ route('sinistro.show', $sinistro->id) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    @can('sinistro.edit')
                                    <a href="{{ route('sinistro.edit', $sinistro->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
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
<x-page-tour tour-id="sinistro-index" :steps="[
    [
        'element' => '.page-title',
        'popover' => [
            'title' => 'Lista de Sinistros',
            'description' => 'Aqui você gerencia todos os sinistros registrados: colisões, furtos, incêndios, alagamentos e vandalismo. Clique em qualquer card ou linha para ver os detalhes.',
        ],
    ],
    [
        'element' => '#formFiltrosSinistro',
        'popover' => [
            'title' => 'Filtros',
            'description' => 'Filtre sinistros por <b>período</b> de ocorrido, <b>placa</b> do veículo, <b>tipo</b> (colisão, furto…) e <b>status</b> (aberto, aprovado, encerrado…). Clique na lupa para aplicar ou no X para limpar.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => 'a[href*=\"sinistro/create\"], .btn-create-resource',
        'popover' => [
            'title' => 'Registrar novo sinistro',
            'description' => 'Clique aqui para abrir o formulário de cadastro de sinistro em 4 etapas: Ocorrido, Terceiros, Seguro e Evidências.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => '.sx-view-toggle',
        'popover' => [
            'title' => 'Alternar visualização',
            'description' => 'Escolha entre <b>Cards</b> (visual detalhado) ou <b>Tabela</b> (compacta). A preferência é salva automaticamente.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => '.sx-cards .sx:first-child',
        'popover' => [
            'title' => 'Card de sinistro',
            'description' => 'Cada card exibe: placa no momento, tipo, vínculo com reserva, veículo, cliente, data do ocorrido, local e prejuízo estimado.',
            'side' => 'right',
        ],
    ],
    [
        'element' => '.sx-cards .sx:first-child .sx__badge',
        'popover' => [
            'title' => 'Status do sinistro',
            'description' => 'Indica a situação atual: <b>Aberto</b> (vermelho), <b>Aguardando Documentos</b> (âmbar), <b>Aprovado</b> (azul), <b>Encerrado / Indenizado</b> (verde), <b>Cancelado</b> (cinza).',
            'side' => 'left',
        ],
    ],
    [
        'element' => '.sx-cards .sx:first-child .sx__foot',
        'popover' => [
            'title' => 'Ações do card',
            'description' => 'Use <b>Ver</b> para acessar os detalhes completos, <b>Editar</b> para alterar os dados e <b>Excluir</b> para remover o registro.',
            'side' => 'top',
        ],
    ],
]" />
<style>
.sx-view-toggle { display:flex; gap:4px; }
.sx-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 16px;
    padding: 10px 0;
}
.sx {
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
.sx:hover { box-shadow: 0 6px 24px rgba(0,0,0,.10); transform: translateY(-2px); }
.sx__accent { height: 5px; width: 100%; }
.sx--green .sx__accent { background: linear-gradient(90deg, #10b981, #34d399); }
.sx--red .sx__accent { background: linear-gradient(90deg, #ef4444, #f87171); }
.sx--slate .sx__accent { background: linear-gradient(90deg, #64748b, #94a3b8); }
.sx__head { display:flex; align-items:flex-start; gap:12px; padding:14px 16px 8px; }
.sx__avatar {
    width:44px; height:44px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:.95rem; color:#fff;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
}
.sx__head-info { flex:1; min-width:0; }
.sx__id { font-size:.72rem; color:#94a3b8; font-weight:600; letter-spacing:.04em; }
.sx__title { font-size:.97rem; font-weight:700; color:#0f2740; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sx__sub { font-size:.78rem; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sx__badges { display:flex; flex-direction:column; gap:4px; }
.sx__badge {
    display:inline-block; padding:2px 8px; border-radius:20px;
    font-size:.7rem; font-weight:700; text-transform:uppercase; white-space:nowrap;
}
.sx__badge--green { background:rgba(16,185,129,.15); color:#047857; }
.sx__badge--red { background:rgba(239,68,68,.13); color:#b91c1c; }
.sx__badge--slate { background:rgba(100,116,139,.12); color:#475569; }
.sx__badge--blue { background:rgba(59,130,246,.12); color:#1d4ed8; }
.sx__badge--amber { background:rgba(245,158,11,.18); color:#92400e; }
.sx__meta { display:flex; flex-direction:column; gap:4px; padding:6px 16px 10px; flex:1; }
.sx__meta-row { display:flex; align-items:baseline; gap:6px; font-size:.8rem; }
.sx__meta-row--full { flex-wrap:wrap; }
.sx__meta-label { color:#64748b; min-width:90px; flex-shrink:0; }
.sx__meta-value { color:#1e293b; font-size:.8rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px; }
.sx__foot {
    display:flex; gap:6px; align-items:center;
    padding:10px 16px; border-top:1px solid #f1f5f9;
    background:#fafbfc; flex-wrap:wrap;
}
.sx-table { display:none; }
.table-sinistro-index td { vertical-align:middle; }
.sx-index-body[data-view="table"] .sx-cards { display:none; }
.sx-index-body[data-view="table"] .sx-table  { display:block; }
.sx-index-body[data-view="cards"] .sx-cards  { display:grid; }
.sx-index-body[data-view="cards"] .sx-table  { display:none; }
@media (max-width:575px) {
    .sx-cards { grid-template-columns: 1fr; }
}
</style>
@stop

@section('js')
<script>
(function () {
    const body = document.querySelector('.sx-index-body');
    const btns = document.querySelectorAll('.sx-view-toggle [data-view]');
    const store = 'sinistro_index_view';

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
