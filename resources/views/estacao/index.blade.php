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
                            <div class="ec__meta-row">
                                <span class="ec__meta-label"><i class="fas fa-sign-in-alt mr-1"></i>Check-in</span>
                                <strong class="ec__meta-value">{{ $estacao->checkin === 'S' ? 'Sim' : 'Não' }}</strong>
                            </div>
                            <div class="ec__meta-row">
                                <span class="ec__meta-label"><i class="fas fa-sign-out-alt mr-1"></i>Check-out</span>
                                <strong class="ec__meta-value">{{ $estacao->checkout === 'S' ? 'Sim' : 'Não' }}</strong>
                            </div>
                            <div class="ec__meta-row ec__meta-row--full">
                                <span class="ec__meta-label"><i class="fas fa-map-marker-alt mr-1"></i>Endereço</span>
                                <strong class="ec__meta-value">{{ $estacao->endereco ? ($estacao->endereco . ($estacao->numero ? ', ' . $estacao->numero : '')) : '-' }}</strong>
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
                                    <span class="badge badge-light border mr-1">CI: {{ $estacao->checkin === 'S' ? 'Sim' : 'Não' }}</span>
                                    <span class="badge badge-light border">CO: {{ $estacao->checkout === 'S' ? 'Sim' : 'Não' }}</span>
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

<x-page-tour tour-id="estacao-index" :steps="[
    [
        'element' => '.content-header',
        'popover' => [
            'title'       => '📍 Estações / Filiais',
            'description' => 'Aqui ficam todas as suas estações — pontos de retirada e devolução de veículos. Cada locadora precisa ter ao menos uma estação para operar.',
            'side'        => 'bottom',
            'align'       => 'start',
        ],
    ],
    [
        'element' => '[data-view=\"cards\"]',
        'popover' => [
            'title'       => '🗂 Visualização em Cards',
            'description' => 'Veja suas estações em cards com nome, cidade, horários e status. Clique em qualquer card para editar.',
            'side'        => 'bottom',
        ],
    ],
    [
        'element' => '[data-view=\"table\"]',
        'popover' => [
            'title'       => '📋 Visualização em Tabela',
            'description' => 'Prefere lista compacta? Alterne para a tabela — útil quando você tem muitas estações cadastradas.',
            'side'        => 'bottom',
        ],
    ],
    [
        'element' => '.content-header a[href*=\"create\"]',
        'popover' => [
            'title'       => '➕ Cadastrar primeira estação',
            'description' => 'Clique em <strong>Nova Estação</strong> para começar. O formulário também tem um guia passo a passo para te ajudar!',
            'side'        => 'bottom',
            'align'       => 'end',
        ],
    ],
]" />
@stop
