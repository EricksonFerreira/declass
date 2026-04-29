@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('multa.create')" />
@stop

@section('content')
    <div class="veiculo-filter-sticky mb-2">
        <form method="GET" action="{{ route('multa.index') }}" class="veiculo-like-filter" id="formFiltrosMulta">
            <div class="veiculo-like-filter__group">
                <input type="date" name="data_inicio" class="form-control form-control-sm veiculo-like-filter__field"
                    value="{{ $filtros['data_inicio'] ?? '' }}" title="Data infração de">
                <input type="date" name="data_fim" class="form-control form-control-sm veiculo-like-filter__field"
                    value="{{ $filtros['data_fim'] ?? '' }}" title="Data infração até">
                <input type="text" name="placa" class="form-control form-control-sm veiculo-like-filter__field veiculo-like-filter__field--plate"
                    value="{{ $filtros['placa'] ?? '' }}" placeholder="Placa">
                <select name="status_multa" class="form-control form-control-sm veiculo-like-filter__field">
                    <option value="">Status multa</option>
                    @foreach(\App\Models\Multa::STATUS_MULTA as $key => $label)
                        <option value="{{ $key }}" {{ ($filtros['status_multa'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status_desconto" class="form-control form-control-sm veiculo-like-filter__field">
                    <option value="">Status desconto</option>
                    @foreach(\App\Models\Multa::STATUS_DESCONTO as $key => $label)
                        <option value="{{ $key }}" {{ ($filtros['status_desconto'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="veiculo-like-filter__actions">
                <button type="submit" class="btn btn-xs btn-primary">
                    <i class="fas fa-search mr-1"></i>Filtrar
                </button>
                <a href="{{ route('multa.index') }}" class="btn btn-xs btn-outline-secondary">
                    <i class="fas fa-times mr-1"></i>Limpar
                </a>
            </div>
        </form>
    </div>

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">
                    REGISTROS <span class="badge badge-success">{{ $multas->total() }}</span>
                </h3>

                <div class="align-bottom d-flex flex-row justify-between" style="gap:6px;flex-wrap:wrap;">
                    @can('multa.export')
                    <a href="{{ route('multa.export', request()->query()) }}" class="btn btn-xs btn-outline-success" title="Exportar CSV">
                        <i class="fas fa-file-csv mr-1"></i>CSV
                    </a>
                    @endcan

                    <div class="veiculo-view-toggle mx-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary active" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i>Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i>Tabela
                        </button>
                    </div>

                    <div class="d-flex align-items-end">
                        {{ $multas->withQueryString()->links() }}
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

.mx-view-toggle { display:flex; gap:5px; }
.veiculo-view-toggle .btn.active,
.mx-view-toggle .btn.active {
    background: #0f172a;
    color: #fff;
    border-color: #0f172a;
}
.mx-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 16px;
    padding: 10px 8px 16px;
}
.mx {
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
.mx:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(11,18,32,.13); }
.mx__accent { height: 3px; width: 100%; background: #e2e9f4; }
.mx--green .mx__accent { background: linear-gradient(90deg, #10b981, #34d399); }
.mx--red .mx__accent { background: linear-gradient(90deg, #ef4444, #f87171); }
.mx--slate .mx__accent { background: linear-gradient(90deg, #64748b, #94a3b8); }
.mx__head { display:flex; align-items:flex-start; gap:11px; padding:14px 16px 12px; border-bottom: 1px solid #f1f5f9; }
.mx__avatar {
    width:42px; height:42px; border-radius:12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:.82rem; font-weight:800; color:#fff; letter-spacing:.04em;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}
.mx__head-info { flex:1; min-width:0; }
.mx__id { font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#94a3b8; margin-bottom:2px; }
.mx__title { font-size:.92rem; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.mx__sub { font-size:.74rem; color:#94a3b8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:1px; }
.mx__badges { display:flex; flex-direction:column; gap:6px; align-items:flex-end; margin-left:6px; }
.mx__badge {
    display:inline-flex; align-items:center; gap:6px; padding:5px 8px;
    border-radius:999px; font-size:.70rem; font-weight:700; line-height:1; white-space:nowrap;
    border:1px solid transparent;
}
.mx__badge--green { background: rgba(16,185,129,.12); color: #047857; border-color: rgba(16,185,129,.22); }
.mx__badge--red { background: rgba(239,68,68,.12); color: #b91c1c; border-color: rgba(239,68,68,.22); }
.mx__badge--slate { background: rgba(100,116,139,.12); color: #475569; border-color: rgba(100,116,139,.2); }
.mx__badge--blue { background: rgba(59,130,246,.12); color: #1d4ed8; border-color: rgba(59,130,246,.2); }
.mx__badge--amber { background: rgba(245,158,11,.14); color: #b45309; border-color: rgba(245,158,11,.28); }
.mx__meta { padding: 12px 16px 10px; display:grid; grid-template-columns: 1fr 1fr; gap:10px 12px; flex:1; }
.mx__meta-row { display:flex; flex-direction:column; gap:2px; min-width:0; }
.mx__meta-row--full { grid-column: 1 / -1; }
.mx__meta-label { font-size:.70rem; color:#94a3b8; font-weight:700; text-transform:uppercase; letter-spacing:.06em; min-width: 0; }
.mx__meta-value { font-size:.86rem; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:none; }
.mx__foot {
    margin-top: auto;
    padding: 12px 16px 14px;
    border-top: 1px solid #f1f5f9;
    display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap;
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
@media (max-width: 991.98px) {
    .mx-cards { grid-template-columns: 1fr; padding: 6px 4px 12px; }
}
.mx-index-body[data-view="table"] .mx-table {
    max-height: calc(100vh - 360px);
    overflow: auto;
    border-radius: 6px;
}
.mx-index-body[data-view="table"] .mx-table thead th {
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'multa-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
{ element: '.page-title', popover: { title: 'Lista de Multas', description: 'Aqui você acompanha todas as multas registradas: infrações de trânsito vinculadas a veículos e reservas, com controle de valores, prazos e pagamentos.' } },
        { element: '#formFiltrosMulta', popover: { title: 'Filtros', description: 'Filtre por <b>período</b> da infração, <b>placa</b> do veículo, <b>status da multa</b> (aberta, paga, contestada…) e <b>status do desconto</b>. Clique na lupa para aplicar e no X para limpar.', side: 'bottom' } },
        { element: 'a[href*="multa/create"], .btn-create-resource', popover: { title: 'Registrar nova multa', description: 'Clique aqui para abrir o formulário de cadastro de multa. Você passará pelas etapas: <b>Infração</b>, <b>Valores</b> e <b>Documentos</b>.', side: 'bottom' } },
        { element: '.mx-view-toggle', popover: { title: 'Alternar visualização', description: 'Escolha entre <b>Cards</b> (visual detalhado) ou <b>Tabela</b> (compacta). A preferência é salva automaticamente.', side: 'bottom' } },
        { element: '.mx-cards .mx:first-child', popover: { title: 'Card de multa', description: 'Cada card exibe: placa no momento, número do auto, órgão autuador, cliente vinculado, data da infração, vencimento e valor final da multa.', side: 'right' } },
        { element: '.mx-cards .mx:first-child .mx__badge', popover: { title: 'Status da multa', description: '<b>Aberta</b> (vermelho) — prazo em aberto. <b>Paga</b> (verde) — quitada. <b>Contestada</b> (âmbar) — em análise. <b>Cancelada</b> (cinza) — inválida ou removida.', side: 'left' } },
        { element: '.mx-cards .mx:first-child .mx__foot', popover: { title: 'Ações', description: 'Use <b>Ver</b> para acessar todos os detalhes e registrar pagamento, <b>Editar</b> para corrigir dados e <b>Excluir</b> para remover o registro.', side: 'top' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    }

    function buildTour() {
        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            progressText: 'Passo @{{current}} de @{{total}}',
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

