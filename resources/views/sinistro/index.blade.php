@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('sinistro.create')" />
@stop

@section('content')
    <div class="veiculo-filter-sticky mb-2">
        <form method="GET" action="{{ route('sinistro.index') }}" class="veiculo-like-filter" id="formFiltrosSinistro">
            <div class="veiculo-like-filter__group">
                <input type="date" name="data_inicio" class="form-control form-control-sm veiculo-like-filter__field"
                    value="{{ $filtros['data_inicio'] ?? '' }}" title="Data ocorrido de">
                <input type="date" name="data_fim" class="form-control form-control-sm veiculo-like-filter__field"
                    value="{{ $filtros['data_fim'] ?? '' }}" title="Data ocorrido até">
                <input type="text" name="placa" class="form-control form-control-sm veiculo-like-filter__field veiculo-like-filter__field--plate"
                    value="{{ $filtros['placa'] ?? '' }}" placeholder="Placa">
                <select name="tipo_sinistro" class="form-control form-control-sm veiculo-like-filter__field">
                    <option value="">Tipo</option>
                    @foreach(\App\Models\Sinistro::TIPOS as $key => $label)
                        <option value="{{ $key }}" {{ ($filtros['tipo_sinistro'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control form-control-sm veiculo-like-filter__field">
                    <option value="">Status</option>
                    @foreach(\App\Models\Sinistro::STATUS as $key => $label)
                        <option value="{{ $key }}" {{ ($filtros['status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="veiculo-like-filter__actions">
                <button type="submit" class="btn btn-xs btn-primary">
                    <i class="fas fa-search mr-1"></i>Filtrar
                </button>
                <a href="{{ route('sinistro.index') }}" class="btn btn-xs btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i>Limpar
                </a>
            </div>
        </form>
    </div>

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">
                    REGISTROS <span class="badge badge-success">{{ $sinistros->total() }}</span>
                </h3>

                <div class="align-bottom d-flex flex-row justify-between" style="gap:6px;flex-wrap:wrap;">
                    @can('sinistro.export')
                    <a href="{{ route('sinistro.export', request()->query()) }}" class="btn btn-xs btn-outline-success" title="Exportar CSV">
                        <i class="fas fa-file-csv mr-1"></i>CSV
                    </a>
                    @endcan

                    <div class="veiculo-view-toggle sx-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary active" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i>Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i>Tabela
                        </button>
                    </div>

                    <div class="d-flex align-items-end">
                        {{ $sinistros->withQueryString()->links() }}
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
        </div>
    </div>
@stop

@section('css')
<style>
.veiculo-filter-sticky {
    position: sticky;
    top: 10px;
    z-index: 1030;
}

.veiculo-like-filter {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 10px 12px;
    box-shadow: 0 2px 8px rgba(11, 18, 32, .05);
}

.veiculo-like-filter__group,
.veiculo-like-filter__actions {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
}

.veiculo-like-filter__field {
    width: 140px;
    border-radius: 8px;
    border-color: #d0daea;
}

.veiculo-like-filter__field--plate {
    width: 100px;
}

.sx-view-toggle { display:flex; gap:5px; }
.veiculo-view-toggle .btn.active,
.sx-view-toggle .btn.active {
    background: #0f172a;
    color: #fff;
    border-color: #0f172a;
}
.sx-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 16px;
    padding: 10px 8px 16px;
}
.sx {
    position: relative;
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid #e2e9f4;
    border-radius: 18px;
    overflow: hidden;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(11,18,32,.06);
    transition: transform .2s ease, box-shadow .2s ease;
}
.sx:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(11,18,32,.13); }
.sx__accent { height: 3px; width: 100%; background: #e2e9f4; }
.sx--green .sx__accent { background: linear-gradient(90deg, #10b981, #34d399); }
.sx--red .sx__accent { background: linear-gradient(90deg, #ef4444, #f87171); }
.sx--slate .sx__accent { background: linear-gradient(90deg, #64748b, #94a3b8); }
.sx__head { display:flex; align-items:flex-start; gap:11px; padding:14px 16px 12px; border-bottom: 1px solid #f1f5f9; }
.sx__avatar {
    width:42px; height:42px; border-radius:12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:.82rem; font-weight:800; color:#fff; letter-spacing:.04em;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}
.sx__head-info { flex:1; min-width:0; }
.sx__id { font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#94a3b8; margin-bottom:2px; }
.sx__title { font-size:.92rem; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sx__sub { font-size:.74rem; color:#94a3b8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:1px; }
.sx__badges { display:flex; flex-direction:column; gap:6px; align-items:flex-end; margin-left:6px; }
.sx__badge {
    display:inline-flex; align-items:center; gap:6px; padding:5px 8px;
    border-radius:999px; font-size:.70rem; font-weight:700; line-height:1; white-space:nowrap;
    border:1px solid transparent;
}
.sx__badge--green { background: rgba(16,185,129,.12); color: #047857; border-color: rgba(16,185,129,.22); }
.sx__badge--red { background: rgba(239,68,68,.12); color: #b91c1c; border-color: rgba(239,68,68,.22); }
.sx__badge--slate { background: rgba(100,116,139,.12); color: #475569; border-color: rgba(100,116,139,.2); }
.sx__badge--blue { background: rgba(59,130,246,.12); color: #1d4ed8; border-color: rgba(59,130,246,.2); }
.sx__badge--amber { background: rgba(245,158,11,.14); color: #b45309; border-color: rgba(245,158,11,.28); }
.sx__meta { padding: 12px 16px 10px; display:grid; grid-template-columns: 1fr 1fr; gap:10px 12px; flex:1; }
.sx__meta-row { display:flex; flex-direction:column; gap:2px; min-width:0; }
.sx__meta-row--full { grid-column: 1 / -1; }
.sx__meta-label { font-size:.70rem; color:#94a3b8; font-weight:700; text-transform:uppercase; letter-spacing:.06em; min-width: 0; }
.sx__meta-value { font-size:.86rem; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:none; }
.sx__foot {
    margin-top: auto;
    padding: 12px 16px 14px;
    border-top: 1px solid #f1f5f9;
    display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap;
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
@media (max-width: 991.98px) {
    .sx-cards { grid-template-columns: 1fr; padding: 6px 4px 12px; }
}
.sx-index-body[data-view="table"] .sx-table {
    max-height: calc(100vh - 360px);
    overflow: auto;
    border-radius: 6px;
}
.sx-index-body[data-view="table"] .sx-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f8fafc;
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'sinistro-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.page-title', popover: { title: 'Lista de Sinistros', description: 'Aqui você gerencia todos os sinistros registrados: colisões, furtos, incêndios, alagamentos e vandalismo. Clique em qualquer card ou linha para ver os detalhes.' } },
        { element: '#formFiltrosSinistro', popover: { title: 'Filtros', description: 'Filtre sinistros por <b>período</b> de ocorrido, <b>placa</b> do veículo, <b>tipo</b> (colisão, furto…) e <b>status</b> (aberto, aprovado, encerrado…). Clique na lupa para aplicar ou no X para limpar.', side: 'bottom' } },
        { element: 'a[href*="sinistro/create"], .btn-create-resource', popover: { title: 'Registrar novo sinistro', description: 'Clique aqui para abrir o formulário de cadastro de sinistro em 4 etapas: Ocorrido, Terceiros, Seguro e Evidências.', side: 'bottom' } },
        { element: '.sx-view-toggle', popover: { title: 'Alternar visualização', description: 'Escolha entre <b>Cards</b> (visual detalhado) ou <b>Tabela</b> (compacta). A preferência é salva automaticamente.', side: 'bottom' } },
        { element: '.sx-cards .sx:first-child', popover: { title: 'Card de sinistro', description: 'Cada card exibe: placa no momento, tipo, vínculo com reserva, veículo, cliente, data do ocorrido, local e prejuízo estimado.', side: 'right' } },
        { element: '.sx-cards .sx:first-child .sx__badge', popover: { title: 'Status do sinistro', description: 'Indica a situação atual: <b>Aberto</b> (vermelho), <b>Aguardando Documentos</b> (âmbar), <b>Aprovado</b> (azul), <b>Encerrado / Indenizado</b> (verde), <b>Cancelado</b> (cinza).', side: 'left' } },
        { element: '.sx-cards .sx:first-child .sx__foot', popover: { title: 'Ações do card', description: 'Use <b>Ver</b> para acessar os detalhes completos, <b>Editar</b> para alterar os dados e <b>Excluir</b> para remover o registro.', side: 'top' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    }

    function buildTour() {
        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            progressText: 'Passo {{current}} de {{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btn = document.createElement('button');
                btn.className = 'drv-skip-btn'; btn.type = 'button'; btn.textContent = 'Pular tour';
                btn.addEventListener('click', function () { _pulou = true; marcarFeito(); driver.destroy(); });
                popover.footer.prepend(btn);
            },
            steps: steps,
        });
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(() => buildTour().drive());
    };

    if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) { localStorage.setItem(CACHE_KEY, '1'); return; }
            setTimeout(() => buildTour().drive(), 350);
        });
})();
</script>
@stop
