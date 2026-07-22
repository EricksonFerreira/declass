@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('ordem-servico.create')" />
@stop

@section('content')
    <div class="os-index-page">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card p-2 os-index-card">
            <div class="card-header px-0 pt-0 pb-2 border-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap os-index-toolbar">
                    <h3 class="card-title text-sm text-bold mb-2 mb-md-0">
                        REGISTROS <span class="badge badge-success">{{ $ordens->total() }}</span>
                    </h3>
                    <div class="os-toolbar-right">
                        <div class="input-group input-group-sm os-search">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control" id="osSearch" placeholder="Filtrar: numero, veiculo, reserva, usuario...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="osSearchClear" title="Limpar filtro">&times;</button>
                            </div>
                        </div>

                        <div class="os-filter-tabs">
                            <a href="{{ route('ordem-servico.index') }}"
                                class="btn btn-xs {{ is_null($status ?? null) ? 'btn-primary' : 'btn-outline-secondary' }}">
                                <i class="fas fa-list mr-1"></i>Todas
                            </a>
                            <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_ABERTA]) }}"
                                class="btn btn-xs {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_ABERTA ? 'btn-primary' : 'btn-outline-secondary' }}">
                                <i class="fas fa-folder-open mr-1"></i>Abertas
                            </a>
                            <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_EM_ANDAMENTO]) }}"
                                class="btn btn-xs {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_EM_ANDAMENTO ? 'btn-primary' : 'btn-outline-secondary' }}">
                                <i class="fas fa-spinner mr-1"></i>Em andamento
                            </a>
                            <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_CONCLUIDA]) }}"
                                class="btn btn-xs {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_CONCLUIDA ? 'btn-primary' : 'btn-outline-secondary' }}">
                                <i class="fas fa-check-circle mr-1"></i>Concluidas
                            </a>
                            <a href="{{ route('ordem-servico.index', ['status' => \App\Models\OrdemServico::STATUS_CANCELADA]) }}"
                                class="btn btn-xs {{ ($status ?? null) === \App\Models\OrdemServico::STATUS_CANCELADA ? 'btn-primary' : 'btn-outline-secondary' }}">
                                <i class="fas fa-ban mr-1"></i>Canceladas
                            </a>
                        </div>

                        <div class="os-view-toggle">
                            <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                                <i class="fas fa-th-large mr-1"></i> Cards
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                                <i class="fas fa-table mr-1"></i> Tabela
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body px-0 pt-0 pb-1 os-index-body" data-view="cards">
                
                {{-- Cards View --}}
                <div class="os-cards">
                    @forelse($ordens as $os)
                        @php
                            $osSearch = \Illuminate\Support\Str::lower(trim(implode(' ', array_filter([
                                (string) ($os->numero ?? ''),
                                (string) ($os->id ?? ''),
                                (string) ($os->status ?? ''),
                                (string) ($os->reserva_id ?? ''),
                                (string) ($os->sinistro_id ?? ''),
                                (string) (optional($os->veiculo)->nome ?? ''),
                                (string) (optional($os->veiculo)->placa ?? ''),
                                (string) (optional($os->usuarioAbertura)->name ?? ''),
                            ]))));

                            $accentClass = match ($os->status) {
                                \App\Models\OrdemServico::STATUS_ABERTA => 'osc-status--warning',
                                \App\Models\OrdemServico::STATUS_EM_ANDAMENTO => 'osc-status--primary',
                                \App\Models\OrdemServico::STATUS_CONCLUIDA => 'osc-status--success',
                                \App\Models\OrdemServico::STATUS_CANCELADA => 'osc-status--secondary',
                                default => 'osc-status--secondary',
                            };

                            $statusBadgeClass = match ($os->status) {
                                \App\Models\OrdemServico::STATUS_ABERTA => 'badge-warning',
                                \App\Models\OrdemServico::STATUS_EM_ANDAMENTO => 'badge-primary',
                                \App\Models\OrdemServico::STATUS_CONCLUIDA => 'badge-success',
                                \App\Models\OrdemServico::STATUS_CANCELADA => 'badge-secondary',
                                default => 'badge-secondary',
                            };
                        @endphp
                        <article class="osc {{ $accentClass }}" data-search="{{ $osSearch }}" onclick="window.location='{{ route('ordem-servico.show', $os) }}';">
                            <div class="osc__accent"></div>
                            
                            <div class="osc__head">
                                <div class="osc__avatar"><i class="fas fa-file-alt"></i></div>
                                <div class="osc__head-info">
                                    <div class="osc__id">OS #{{ $os->numero ?? $os->id }}</div>
                                    <div class="osc__title">{{ optional($os->veiculo)->nome ?? 'Veículo não informado' }}</div>
                                    <div class="osc__sub">{{ optional($os->veiculo)->placa ?? 'Sem Placa' }}</div>
                                </div>
                                <div class="osc__badges">
                                    <span class="badge {{ $statusBadgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', (string) $os->status)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="osc__meta">
                                <div class="osc__meta-row">
                                    <span class="osc__meta-label"><i class="fas fa-calendar mr-1"></i>Abertura</span>
                                    <strong class="osc__meta-value">{{ $os->created_at ? $os->created_at->format('d/m/Y H:i') : '-' }}</strong>
                                </div>
                                <div class="osc__meta-row">
                                    <span class="osc__meta-label"><i class="fas fa-user mr-1"></i>Aberta por</span>
                                    <strong class="osc__meta-value">{{ optional($os->usuarioAbertura)->name ?? '-' }}</strong>
                                </div>
                                @if($os->reserva_id)
                                    <div class="osc__meta-row">
                                        <span class="osc__meta-label"><i class="fas fa-bookmark mr-1"></i>Reserva</span>
                                        <strong class="osc__meta-value">#{{ $os->reserva_id }}</strong>
                                    </div>
                                @endif
                                @if($os->sinistro_id)
                                    <div class="osc__meta-row">
                                        <span class="osc__meta-label"><i class="fas fa-exclamation-triangle mr-1 text-warning"></i>Sinistro</span>
                                        <strong class="osc__meta-value">#{{ $os->sinistro_id }}</strong>
                                    </div>
                                @endif
                                <div class="osc__meta-row">
                                    <span class="osc__meta-label"><i class="fas fa-tools mr-1"></i>Itens</span>
                                    <strong class="osc__meta-value">{{ $os->itens->count() }} item(ns)</strong>
                                </div>
                            </div>

                            <div class="osc__footer">
                                <a href="{{ route('ordem-servico.show', $os) }}" class="btn btn-sm btn-outline-primary btn-block osc__btn" onclick="event.stopPropagation();">
                                    <i class="fas fa-eye mr-1"></i>Ver Detalhes
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="w-100 text-center py-5 text-muted osc-empty-state">
                            <i class="fas fa-folder-open fa-3x mb-3 d-block text-secondary"></i>
                            Nenhuma ordem de serviço encontrada.
                        </div>
                    @endforelse
                </div>

                {{-- Table View --}}
                <div class="os-table">
                    <table class="table table-hover table-bordered table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Veiculo</th>
                                <th>Reserva</th>
                                <th>Sinistro</th>
                                <th>Status</th>
                                <th>Aberta por</th>
                                <th>Atualizacao</th>
                                <th class="text-center">Acao</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ordens as $os)
                                @php
                                    $osSearch = \Illuminate\Support\Str::lower(trim(implode(' ', array_filter([
                                        (string) ($os->numero ?? ''),
                                        (string) ($os->id ?? ''),
                                        (string) ($os->status ?? ''),
                                        (string) ($os->reserva_id ?? ''),
                                        (string) ($os->sinistro_id ?? ''),
                                        (string) (optional($os->veiculo)->nome ?? ''),
                                        (string) (optional($os->veiculo)->placa ?? ''),
                                        (string) (optional($os->usuarioAbertura)->name ?? ''),
                                    ]))));
                                @endphp
                                <tr data-search="{{ $osSearch }}" onclick="window.location='{{ route('ordem-servico.show', $os) }}';" style="cursor:pointer;">
                                    <td class="text-center">{{ $os->numero ?? $os->id }}</td>
                                    <td>
                                        <strong>{{ optional($os->veiculo)->nome ?? ('Veiculo #' . $os->veiculo_id) }}</strong><br>
                                        <small>{{ optional($os->veiculo)->placa ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($os->reserva_id)
                                            <strong>#{{ $os->reserva_id }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($os->sinistro_id)
                                            <span class="badge badge-warning text-dark"><i class="fas fa-exclamation-triangle mr-1"></i>#{{ $os->sinistro_id }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match ($os->status) {
                                                \App\Models\OrdemServico::STATUS_ABERTA => 'warning',
                                                \App\Models\OrdemServico::STATUS_EM_ANDAMENTO => 'primary',
                                                \App\Models\OrdemServico::STATUS_CONCLUIDA => 'success',
                                                \App\Models\OrdemServico::STATUS_CANCELADA => 'secondary',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $statusClass }}">
                                            {{ ucfirst(str_replace('_', ' ', (string) $os->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ optional($os->usuarioAbertura)->name ?? '-' }}</td>
                                    <td>{{ $os->updated_at ? $os->updated_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('ordem-servico.show', $os) }}" class="btn btn-xs btn-info" onclick="event.stopPropagation();">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        Nenhuma ordem de servico encontrada.
                                    </td>
                                </tr>
                            @endforelse
                            <tr id="osNoResultsRow" class="d-none">
                                <td colspan="8" class="text-center py-4 text-muted">
                                    Sem resultados para o filtro.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            @if($ordens->hasPages())
                <div class="card-footer bg-white border-0 pt-2">
                    {{ $ordens->appends(['status' => $status ?? null])->links() }}
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
<style>
    .os-index-page .card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }

    .os-index-toolbar {
        gap: 12px;
    }

    .os-toolbar-right {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
        align-items: center;
    }

    .os-search {
        width: 300px;
        max-width: 100%;
    }

    .os-filter-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: flex-end;
    }

    .os-view-toggle {
        display: flex;
        gap: 5px;
    }

    .os-view-toggle .btn.active {
        background: #0f172a;
        color: #fff;
        border-color: #0f172a;
    }

    .os-index-body[data-view="table"] .os-cards {
        display: none;
    }

    .os-index-body[data-view="cards"] .os-table {
        display: none;
    }

    .os-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 16px;
        padding: 10px 8px 16px;
    }

    /* Card styling similar to veiculo index */
    .osc {
        position: relative;
        background: #fff;
        border: 1px solid #e2e9f4;
        border-radius: 18px;
        overflow: hidden;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 10px rgba(11, 18, 32, .06);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .osc:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(11, 18, 32, .13);
    }

    .osc__accent {
        height: 3px;
        background: #e2e9f4;
    }

    .osc-status--warning .osc__accent {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
    }

    .osc-status--primary .osc__accent {
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
    }

    .osc-status--success .osc__accent {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .osc-status--secondary .osc__accent {
        background: linear-gradient(90deg, #64748b, #94a3b8);
    }

    .osc__head {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 14px 16px 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .osc__avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #fff;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .osc__head-info {
        flex: 1;
        min-width: 0;
    }

    .osc__id {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 2px;
    }

    .osc__title {
        font-size: .92rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .osc__sub {
        font-size: .74rem;
        color: #94a3b8;
    }

    .osc__meta {
        padding: 12px 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .osc__meta-row {
        display: flex;
        justify-content: space-between;
        font-size: .78rem;
        line-height: 1.4;
    }

    .osc__meta-label {
        color: #64748b;
    }

    .osc__meta-value {
        color: #1e293b;
        font-weight: 600;
    }

    .osc__footer {
        padding: 12px 16px 16px;
        border-top: 1px solid #f1f5f9;
    }

    .osc__btn {
        border-radius: 10px;
        font-weight: 700;
        font-size: .78rem;
        padding: 8px;
    }

    .os-index-card .table thead th {
        white-space: nowrap;
    }

    .os-index-card .badge {
        font-size: 11px;
        padding: 0.4rem 0.55rem;
        font-weight: 600;
    }

    @media (max-width: 767.98px) {
        .os-toolbar-right {
            width: 100%;
            justify-content: flex-start;
        }

        .os-search {
            width: 100%;
        }

        .os-filter-tabs {
            width: 100%;
            justify-content: flex-start;
        }

        .os-view-toggle {
            width: 100%;
            margin-top: 4px;
        }
    }
</style>
@stop

@section('js')
<script>
(function () {
    'use strict';

    var input = document.getElementById('osSearch');
    var clearBtn = document.getElementById('osSearchClear');
    var noResultsRow = document.getElementById('osNoResultsRow');
    var body = document.querySelector('.os-index-body');

    // Layout Toggle logic
    var toggleButtons = document.querySelectorAll('.os-view-toggle button');
    function setView(view) {
        body.setAttribute('data-view', view);
        localStorage.setItem('osIndexView', view);
        toggleButtons.forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-view') === view);
        });
    }

    var savedView = localStorage.getItem('osIndexView') || 'cards';
    setView(savedView);

    toggleButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setView(this.getAttribute('data-view'));
        });
    });

    if (!input || !clearBtn) return;

    function applyFilter() {
        var q = (input.value || '').toLowerCase().trim();
        localStorage.setItem('osIndexSearch', q);

        // Filter cards
        var cards = document.querySelectorAll('.os-cards .osc');
        var matchCards = 0;
        cards.forEach(function (card) {
            var hay = (card.dataset.search || '');
            var ok = !q || hay.indexOf(q) !== -1;
            card.style.display = ok ? '' : 'none';
            if (ok) matchCards++;
        });

        // Filter table rows
        var rows = document.querySelectorAll('.os-table tbody tr[data-search]');
        var matchRows = 0;
        rows.forEach(function (row) {
            var hay = (row.dataset.search || '');
            var ok = !q || hay.indexOf(q) !== -1;
            row.style.display = ok ? '' : 'none';
            if (ok) matchRows++;
        });

        if (noResultsRow) {
            noResultsRow.classList.toggle('d-none', matchRows !== 0 || !q);
        }

        var emptyState = document.querySelector('.osc-empty-state');
        if (emptyState) {
            emptyState.style.display = (matchCards === 0 && q) ? '' : 'none';
        }
    }

    var saved = localStorage.getItem('osIndexSearch') || '';
    if (saved) {
        input.value = saved;
    }

    input.addEventListener('input', applyFilter);
    clearBtn.addEventListener('click', function () {
        input.value = '';
        applyFilter();
        input.focus();
    });

    applyFilter();
})();
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'ordem-servico-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.page-title', popover: { title: 'Lista de Ordens de Serviço', description: 'Aqui você gerencia todas as ordens de serviço abertas para manutenção e reparo dos veículos da frota. Cada OS pode ser vinculada a danos detectados nas reservas.' } },
        { element: 'a[href*="ordem-servico/create"], .btn-create-resource', popover: { title: 'Abrir nova Ordem de Serviço', description: 'Clique aqui para criar uma nova OS. Você selecionará o veículo, os danos pendentes vinculados a ele e as observações ou serviços adicionais necessários.', side: 'bottom' } },
        { element: '.os-filter-tabs', popover: { title: 'Filtrar por status', description: 'Filtre rapidamente as OS por: <b>Todas</b>, <b>Abertas</b> (aguardando início), <b>Em andamento</b> (execução em curso), <b>Concluídas</b> ou <b>Canceladas</b>.', side: 'bottom' } },
        { element: '#osSearch', popover: { title: 'Busca rápida', description: 'Digite qualquer informação — número da OS, placa, nome do veículo ou responsável — para filtrar os registros em tempo real sem recarregar a página.', side: 'bottom' } },
        { element: '.os-view-toggle', popover: { title: 'Alternar visualização', description: 'Escolha visualizar os registros como <b>Cards</b> interativos ou em formato de <b>Tabela</b> tradicional.', side: 'bottom' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    }

    function buildTour() {
        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            allowClose: true,
            progressText: 'Passo @{{current}} de @{{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn'; btnTour.type = 'button'; btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () { _pulou = true; marcarFeito(); driver.destroy(); });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn'; btnStep.type = 'button'; btnStep.textContent = 'Pular passo';
                btnStep.addEventListener('click', function () { driver.moveNext(); });
                var nav = popover.footer.querySelector('.driver-popover-navigation-btns');
                if (nav) { nav.prepend(btnStep); }
                popover.footer.prepend(btnTour);
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
