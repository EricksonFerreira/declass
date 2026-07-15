@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('estacao.create')" />
@stop

@section('content')
    @if (isset($filters) && is_array($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS <span class="badge badge-success">{{ $estacaos->total() }}</span>
                </h3>

                <div class="d-flex flex-row align-items-center flex-wrap" style="gap:6px;">
                    <form action="{{ route('estacao.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>

                    <div class="ec-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i>Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i>Tabela
                        </button>
                    </div>

                    <div class="d-flex align-items-end">
                        {{ $estacaos->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 ec-index-body" data-view="cards">

            {{-- ── CARDS ── --}}
            <div class="ec-cards">
                @foreach ($estacaos as $estacao)
                    @php
                        $ativo      = $estacao->ativo === 'S';
                        $accentCls  = $ativo ? 'ec--green' : 'ec--red';
                        $iniciais   = collect(preg_split('/\s+/', trim($estacao->nome ?? 'ES')))
                            ->filter()->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                        $iniciais   = $iniciais ?: 'ES';
                    @endphp

                    <article class="ec {{ $accentCls }}"
                             onclick="window.location='{{ route('estacao.edit', $estacao->id) }}';">
                        <div class="ec__accent"></div>

                        <div class="ec__head">
                            <div class="ec__avatar">{{ $iniciais }}</div>
                            <div class="ec__head-info">
                                <div class="ec__id">ESTAÇÃO #{{ $estacao->id }}</div>
                                <div class="ec__title">{{ $estacao->nome }}</div>
                                <div class="ec__sub">{{ $estacao->cidade ?? 'Cidade não definida' }}{{ $estacao->pais ? ' · ' . $estacao->pais->nome : '' }}</div>
                            </div>
                            <div class="ec__badges">
                                <span class="ec__badge {{ $ativo ? 'ec__badge--green' : 'ec__badge--red' }}">
                                    {{ $ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        </div>

                        <div class="ec__meta">
                            <div class="ec__meta-row">
                                <span class="ec__meta-label"><i class="fas fa-envelope mr-1"></i>E-mail</span>
                                <strong class="ec__meta-value">{{ $estacao->email ?? '-' }}</strong>
                            </div>
                            <div class="ec__meta-row">
                                <span class="ec__meta-label"><i class="fas fa-phone mr-1"></i>Telefone</span>
                                <strong class="ec__meta-value">{{ $estacao->telefone ?? $estacao->celular ?? '-' }}</strong>
                            </div>
                            <div class="ec__meta-row ec__meta-row--full">
                                <span class="ec__meta-label"><i class="fas fa-map-marker-alt mr-1"></i>Endereço</span>
                                <strong class="ec__meta-value">{{ $estacao->endereco ? ($estacao->endereco . ($estacao->numero ? ', ' . $estacao->numero : '')) : '-' }}</strong>
                            </div>
                            <div class="ec__meta-row ec__meta-row--pair">
                                <span class="ec__meta-pair">
                                    <span class="ec__meta-label"><i class="fas fa-sign-in-alt mr-1"></i>Check-in</span>
                                    <span class="badge badge-{{ $estacao->checkin === 'S' ? 'success' : 'danger' }}">{{ $estacao->checkin === 'S' ? 'Sim' : 'Não' }}</span>
                                </span>
                                <span class="ec__meta-pair">
                                    <span class="ec__meta-label"><i class="fas fa-sign-out-alt mr-1"></i>Check-out</span>
                                    <span class="badge badge-{{ $estacao->checkout === 'S' ? 'success' : 'danger' }}">{{ $estacao->checkout === 'S' ? 'Sim' : 'Não' }}</span>
                                </span>
                            </div>
                            <div class="ec__meta-row ec__meta-row--pair">
                                <span class="ec__meta-pair">
                                    <span class="ec__meta-label"><i class="fas fa-check-circle mr-1"></i>Veículos ativos</span>
                                    <span class="badge badge-success">{{ $estacao->veiculos_ativos_count ?? 0 }}</span>
                                </span>
                                <span class="ec__meta-pair">
                                    <span class="ec__meta-label"><i class="fas fa-ban mr-1"></i>Veículos inativos</span>
                                    <span class="badge badge-danger">{{ $estacao->veiculos_inativos_count ?? 0 }}</span>
                                </span>
                            </div>

                        </div>

                        <div class="ec__foot">
                            <a href="{{ route('estacao.edit', $estacao->id) }}" class="btn btn-xs btn-warning" onclick="event.stopPropagation();">
                                <i class="fa fa-edit mr-1"></i>Editar
                            </a>
                            <span onclick="event.stopPropagation();">
                                <x-delete-button :action="route('estacao.destroy', $estacao->id)" />
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- ── TABLE ── --}}
            <div class="ec-table table-responsive">
                <table class="table table-striped table-bordered table-sm table-estacoes-index">
                    <thead>
                        <tr>
                            <th>Estação</th>
                            <th>Contato</th>
                            <th>Localização</th>
                            <th>Operação</th>
                            <th>Status</th>
                            <th style="width:220px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($estacaos as $estacao)
                            <tr onclick="window.location='{{ route('estacao.edit', $estacao->id) }}';" style="cursor:pointer;">
                                <td>
                                    <div class="font-weight-bold">{{ $estacao->nome }}</div>
                                    <small class="text-muted">ID: {{ $estacao->id }}</small>
                                </td>
                                <td>
                                    <div><i class="fa fa-envelope mr-1 text-muted"></i>{{ $estacao->email ?? '-' }}</div>
                                    <div><i class="fa fa-mobile-alt mr-1 text-muted"></i>{{ $estacao->celular ?? '-' }}</div>
                                </td>
                                <td>
                                    <div>{{ $estacao->pais?->nome ?? '-' }}</div>
                                    <div>{{ $estacao->cidade ?? '-' }}</div>
                                    <small class="text-muted">{{ $estacao->endereco ?? '' }}{{ $estacao->numero ? ', '.$estacao->numero : '' }}</small>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap" style="gap:6px;">
                                        <span class="badge badge-light border">CI: {{ $estacao->checkin === 'S' ? 'Sim' : 'Não' }}</span>
                                        <span class="badge badge-light border">CO: {{ $estacao->checkout === 'S' ? 'Sim' : 'Não' }}</span>
                                    </div>
                                    <div class="d-flex flex-wrap mt-1" style="gap:6px;">
                                        <span class="badge badge-success">Ativos: {{ $estacao->veiculos_ativos_count ?? 0 }}</span>
                                        <span class="badge badge-danger">Inativos: {{ $estacao->veiculos_inativos_count ?? 0 }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-xs bg-{{ $estacao->ativo === 'S' ? 'success' : 'danger' }}">
                                        {{ $estacao->ativo === 'S' ? 'ATIVO' : 'INATIVO' }}
                                    </span>
                                </td>
                                <td onclick="event.stopPropagation();" style="width:220px;">
                                    <a href="{{ route('estacao.edit', $estacao->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fa fa-edit"></i> Editar
                                    </a>
                                    <x-delete-button :action="route('estacao.destroy', $estacao->id)" />
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<style>
/* Popover container */
.driver-popover {
    border-radius: 14px !important;
    box-shadow: 0 8px 40px rgba(10,22,40,.22) !important;
    min-width: 280px !important;
    max-width: 340px !important;
    padding: 20px 20px 16px !important;
}
/* Title */
.driver-popover-title {
    font-family: inherit !important;
    font-weight: 800 !important;
    font-size: 1rem !important;
    color: #0a1628 !important;
    margin-bottom: 6px !important;
}
/* Description */
.driver-popover-description {
    font-size: .85rem !important;
    line-height: 1.6 !important;
    color: #374151 !important;
    margin-bottom: 14px !important;
}
/* Footer: progress on top, buttons below */
.driver-popover-footer {
    display: flex !important;
    flex-direction: column !important;
    gap: 10px !important;
    border-top: 1px solid #f1f5f9 !important;
    padding-top: 12px !important;
    margin-top: 0 !important;
}
.driver-popover-progress-text {
    font-size: .72rem !important;
    color: #94a3b8 !important;
    text-align: center !important;
    order: -1 !important;
}
.driver-popover-navigation-btns {
    display: flex !important;
    justify-content: flex-end !important;
    gap: 8px !important;
}
.driver-popover-footer button {
    border-radius: 8px !important;
    font-weight: 700 !important;
    font-size: .82rem !important;
    padding: .4rem 1rem !important;
    white-space: nowrap !important;
}
.driver-popover-next-btn,
.driver-popover-done-btn {
    min-width: 110px !important;
}
/* Botão Pular */
.drv-skip-btn {
    background: none !important;
    border: none !important;
    color: #94a3b8 !important;
    font-size: .78rem !important;
    font-weight: 600 !important;
    padding: .3rem .5rem !important;
    cursor: pointer !important;
    margin-right: auto !important;
    transition: color .15s !important;
}
.drv-skip-btn:hover { color: #ef4444 !important; }
/* Close button */
.driver-popover-close-btn {
    top: 12px !important;
    right: 14px !important;
    font-size: 1.1rem !important;
    color: #94a3b8 !important;
}
#driver-page-overlay { opacity: .4 !important; }
</style>
<style>
/* ── View toggle ── */
.ec-view-toggle { display:flex; gap:4px; }

/* ── Cards grid ── */
.ec-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
    gap: 16px;
    padding: 10px 0;
}

/* ── Single card ── */
.ec {
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
.ec:hover { box-shadow: 0 6px 24px rgba(0,0,0,.10); transform: translateY(-2px); }

/* accent bar */
.ec__accent { height: 5px; width: 100%; }
.ec--green .ec__accent { background: linear-gradient(90deg, #10b981, #34d399); }
.ec--red   .ec__accent { background: linear-gradient(90deg, #ef4444, #f87171); }

/* head */
.ec__head { display:flex; align-items:flex-start; gap:12px; padding:14px 16px 8px; }
.ec__avatar {
    width:44px; height:44px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:.95rem; color:#fff;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
}
.ec__head-info { flex:1; min-width:0; }
.ec__id   { font-size:.72rem; color:#94a3b8; font-weight:600; letter-spacing:.04em; }
.ec__title { font-size:.97rem; font-weight:700; color:#0f2740; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ec__sub   { font-size:.78rem; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ec__badges { display:flex; flex-direction:column; gap:4px; }

/* badges */
.ec__badge {
    display:inline-block; padding:2px 8px; border-radius:20px;
    font-size:.7rem; font-weight:700; text-transform:uppercase; white-space:nowrap;
}
.ec__badge--green { background:rgba(16,185,129,.15); color:#047857; }
.ec__badge--red   { background:rgba(239,68,68,.13);  color:#b91c1c; }
.ec__badge--slate { background:rgba(100,116,139,.12); color:#475569; }

/* meta */
.ec__meta { display:flex; flex-direction:column; gap:4px; padding:6px 16px 10px; flex:1; }
.ec__meta-row { display:flex; align-items:baseline; gap:6px; font-size:.8rem; }
.ec__meta-row--pair { gap:10px; align-items:flex-start; }
.ec__meta-pair {
    display:flex;
    align-items:baseline;
    gap:6px;
    min-width:0;
    flex:1;
}
.ec__meta-row--full { flex-wrap:wrap; }
.ec__meta-label { color:#64748b; min-width:90px; flex-shrink:0; }
.ec__meta-value { color:#1e293b; font-size:.8rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px; }

/* foot */
.ec__foot {
    display:flex; gap:6px; align-items:center;
    padding:10px 16px; border-top:1px solid #f1f5f9;
    background:#fafbfc;
}

/* ── Table (hidden by default) ── */
.ec-table { display:none; }
.table-estacoes-index td { vertical-align:middle; }

/* ── Toggle state ── */
.ec-index-body[data-view="table"] .ec-cards { display:none; }
.ec-index-body[data-view="table"] .ec-table  { display:block; }
.ec-index-body[data-view="cards"] .ec-cards  { display:grid; }
.ec-index-body[data-view="cards"] .ec-table  { display:none; }

@media (max-width:575px) {
    .ec-cards { grid-template-columns: 1fr; }
}
</style>
@stop

@section('js')
<script>
(function () {
    const body    = document.querySelector('.ec-index-body');
    const btns    = document.querySelectorAll('.ec-view-toggle [data-view]');
    const STORE   = 'ec_view_pref';

    function setView(v) {
        body.dataset.view = v;
        btns.forEach(b => b.classList.toggle('active', b.dataset.view === v));
        localStorage.setItem(STORE, v);
    }

    btns.forEach(b => b.addEventListener('click', () => setView(b.dataset.view)));
    setView(localStorage.getItem(STORE) || 'cards');
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID    = 'estacao-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        {
            element: '.content-header',
            popover: {
                title: '📍 Estações / Filiais',
                description: 'Aqui ficam todas as suas estações — pontos de retirada e devolução de veículos. Cada locadora precisa ter ao menos uma estação para operar.',
                side: 'bottom', align: 'start',
            },
        },
        {
            element: '[data-view="cards"]',
            popover: {
                title: '🗂 Visualização em cards',
                description: 'Veja suas estações em cards com nome, cidade, horários e status. Clique em qualquer card para editar.',
                side: 'bottom',
            },
        },
        {
            element: '[data-view="table"]',
            popover: {
                title: '📋 Visualização em Tabela',
                description: 'Prefere uma lista compacta? Alterne para a tabela — útil quando você tem muitas estações cadastradas.',
                side: 'bottom',
            },
        },
        {
            element: '.content-header a[href*="create"]',
            popover: {
                title: '➕ Cadastrar primeira estação',
                description: 'Clique em <strong>Nova Estação</strong> para começar. O formulário também tem um guia passo a passo para te ajudar!',
                side: 'bottom', align: 'end',
            },
        },
    ];

    const CACHE_KEY = 'tour_feito__' + TOUR_ID;

    function marcarFeito() {
        // Cache local imediato para evitar race condition
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
    }

    function buildTour() {
        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            progressText: 'Passo @{{current}} de @{{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => {
                if (!_pulou) marcarFeito();
            },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn';
                btnTour.type = 'button';
                btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () {
                    _pulou = true;
                    marcarFeito();
                    driver.destroy();
                });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn';
                btnStep.type = 'button';
                btnStep.textContent = 'Pular passo';
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
        fetch(RESETAR_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        }).then(() => buildTour().drive());
    };

    // Cache local evita mostrar o tour enquanto o POST ainda não chegou ao banco
    if (localStorage.getItem(CACHE_KEY) && !forceTour) return;

    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) {
                localStorage.setItem(CACHE_KEY, '1'); // sincroniza cache
                return;
            }
            setTimeout(() => buildTour().drive(), 350);
        });
})();
</script>
@stop
