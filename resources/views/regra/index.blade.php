@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $grupoMeta = [
        'reserva'           => ['label' => 'Reservas',           'icon' => 'fas fa-calendar-check',      'color' => '#4b80ff'],
        'veiculo'           => ['label' => 'Veículos',           'icon' => 'fas fa-car',                 'color' => '#28a745'],
        'usuario'           => ['label' => 'Usuários',           'icon' => 'fas fa-users',               'color' => '#17a2b8'],
        'permissao'         => ['label' => 'Permissões',         'icon' => 'fas fa-key',                 'color' => '#e0a800'],
        'regra'             => ['label' => 'Regras',             'icon' => 'fas fa-shield-alt',          'color' => '#e0a800'],
        'multa'             => ['label' => 'Multas',             'icon' => 'fas fa-file-invoice-dollar', 'color' => '#dc3545'],
        'movimentacao'      => ['label' => 'Movimentações',      'icon' => 'fas fa-exchange-alt',        'color' => '#6c757d'],
        'financeiro'        => ['label' => 'Financeiro',         'icon' => 'fas fa-dollar-sign',         'color' => '#28a745'],
        'calcao'            => ['label' => 'Caução',             'icon' => 'fas fa-hand-holding-usd',    'color' => '#28a745'],
        'empresa'           => ['label' => 'Empresa',            'icon' => 'fas fa-building',            'color' => '#343a40'],
        'estacao'           => ['label' => 'Estações',           'icon' => 'fas fa-map-marker-alt',      'color' => '#17a2b8'],
        'modelo'            => ['label' => 'Modelos',            'icon' => 'fas fa-car-side',            'color' => '#6c757d'],
        'nfse'              => ['label' => 'NFS-e',              'icon' => 'fas fa-file-alt',            'color' => '#343a40'],
        'pagamento'         => ['label' => 'Pagamento',          'icon' => 'fas fa-credit-card',         'color' => '#28a745'],
        'dashboard'         => ['label' => 'Dashboard',          'icon' => 'fas fa-chart-bar',           'color' => '#17a2b8'],
        '_outros'           => ['label' => 'Outros',             'icon' => 'fas fa-ellipsis-h',          'color' => '#6c757d'],
    ];

    $acaoBadge = [
        'index'   => ['Listar',    '#4b80ff'],
        'show'    => ['Ver',       '#17a2b8'],
        'create'  => ['Criar',     '#28a745'],
        'store'   => ['Salvar',    '#28a745'],
        'edit'    => ['Editar',    '#e0a800'],
        'update'  => ['Atualizar', '#e0a800'],
        'destroy' => ['Excluir',   '#dc3545'],
        'delete'  => ['Excluir',   '#dc3545'],
    ];
@endphp

<div class="rg-page">

    {{-- Hero --}}
    <div class="rg-hero mb-4">
        <div class="rg-hero__left">
            <div class="rg-hero__icon"><i class="fas fa-shield-alt"></i></div>
            <div>
                <h2 class="rg-hero__title">Regras de Acesso</h2>
                <p class="rg-hero__sub">Agrupe permissões em perfis e atribua-os aos usuários para controlar o acesso ao sistema.</p>
            </div>
        </div>
        <a href="{{ route('regra.create') }}" class="btn btn-primary rg-hero__btn">
            <i class="fas fa-plus mr-1"></i> Nova Regra
        </a>
    </div>

    {{-- Filtros --}}
    @if(isset($filters) && count($filters) > 0)
        <x-filter :additionalFilters="$additionalFilters" :filters="$filters" :itensFiltered="$itensFiltered" />
    @endif

    {{-- Painel principal --}}
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem">
                <h3 class="card-title text-sm text-bold mb-0">
                    REGISTROS <span class="badge badge-success">{{ $regras->total() }}</span>
                </h3>
                <div class="d-flex align-items-center flex-wrap" style="gap:6px">
                    <form action="{{ route('regra.setPerPage') }}" method="POST">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    <div class="rg-view-toggle">
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards">
                            <i class="fas fa-th-large mr-1"></i> Cards
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
                            <i class="fas fa-table mr-1"></i> Tabela
                        </button>
                    </div>
                    <div class="d-flex align-items-end">
                        {{ $regras->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1 rg-index-body" data-view="cards">

    {{-- Vazio --}}
    @if($regras->isEmpty())
        <div class="alert alert-light text-center py-5">
            <i class="fas fa-shield-alt fa-3x text-muted mb-3 d-block"></i>
            <p class="mb-0 text-muted">Nenhuma regra encontrada.</p>
            <a href="{{ route('regra.create') }}" class="btn btn-primary btn-sm mt-3">
                <i class="fas fa-plus mr-1"></i> Criar primeira regra
            </a>
        </div>
    @else
        <div class="rg-grid">
            @foreach($regras as $regra)
                @php
                    $totalPerms = count($regra->permissoes);
                    $groupedPerms = $regra->permissoes->groupBy(
                        fn($p) => $p->grupo_efetivo ?: '_outros'
                    );
                    $totalGrupos = $groupedPerms->count();
                    $cardId = 'rg-card-' . $regra->id;
                @endphp
                <div class="rg-card">

                    {{-- Card header --}}
                    <div class="rg-card__header">
                        <div class="rg-card__icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="rg-card__info">
                            <h5 class="rg-card__name">{{ $regra->name }}</h5>
                            <div class="rg-card__meta">
                                <span class="rg-card__guard"><i class="fas fa-lock mr-1" style="font-size:.7rem"></i>{{ $regra->guard_name }}</span>
                            </div>
                        </div>
                        <div class="rg-card__actions">
                            <a href="{{ route('regra.edit', $regra->id) }}" class="btn btn-xs btn-outline-warning" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            <x-delete-button :action="route('regra.destroy', $regra->id)" />
                        </div>
                    </div>

                    {{-- Stats bar --}}
                    <div class="rg-card__stats">
                        <span class="rg-badge rg-badge--blue">
                            <i class="fas fa-key mr-1"></i>{{ $totalPerms }} permissão{{ $totalPerms !== 1 ? 'ões' : '' }}
                        </span>
                        <span class="rg-badge rg-badge--gray">
                            <i class="fas fa-th-large mr-1"></i>{{ $totalGrupos }} módulo{{ $totalGrupos !== 1 ? 's' : '' }}
                        </span>
                        @if($totalPerms > 0)
                            <button type="button"
                                class="btn btn-xs rg-btn-expand ml-auto"
                                data-toggle="collapse"
                                data-target="#{{ $cardId }}"
                                aria-expanded="false">
                                <i class="fas fa-chevron-down"></i>
                                <span class="rg-btn-expand__lbl">Ver permissões</span>
                            </button>
                        @endif
                    </div>

                    {{-- Permissões colapsáveis agrupadas --}}
                    @if($totalPerms > 0)
                        <div id="{{ $cardId }}" class="collapse rg-card__perms">
                            <div class="rg-perms-inner">
                                @foreach($groupedPerms as $grupo => $perms)
                                    @php
                                        $gm = $grupoMeta[$grupo] ?? $grupoMeta['_outros'];
                                        $hex = $gm['color'];
                                    @endphp
                                    <div class="rg-perm-group">
                                        <div class="rg-perm-group__header" style="color:{{ $hex }}">
                                            <i class="{{ $gm['icon'] }} mr-1"></i>
                                            <span>{{ $gm['label'] }}</span>
                                            <span class="rg-perm-group__count" style="background:{{ $hex }}20;color:{{ $hex }}">
                                                {{ $perms->count() }}
                                            </span>
                                        </div>
                                        <div class="rg-perm-group__items">
                                            @foreach($perms as $p)
                                                @php
                                                    $acao = str_contains($p->name, '.') ? explode('.', $p->name)[1] : $p->name;
                                                    [$acaoLabel, $acaoColor] = $acaoBadge[$acao] ?? [$acao, '#6c757d'];
                                                @endphp
                                                <div class="rg-perm-item" title="{{ $p->name }}">
                                                    <span class="rg-perm-item__badge" style="background:{{ $acaoColor }}20;color:{{ $acaoColor }}">
                                                        {{ $acaoLabel }}
                                                    </span>
                                                    <span class="rg-perm-item__desc">
                                                        {{ $p->descricao ?: $acao }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>

        {{-- Table view --}}
        <div class="rg-table table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Guard</th>
                        <th>Permissões</th>
                        <th>Módulos</th>
                        <th style="width:120px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($regras as $regra)
                        @php
                            $totalPerms = count($regra->permissoes);
                            $totalGrupos = $regra->permissoes->groupBy(fn($p) => $p->grupo_efetivo ?: '_outros')->count();
                        @endphp
                        <tr>
                            <td>{{ $regra->id }}</td>
                            <td><strong>{{ $regra->name }}</strong></td>
                            <td><span class="badge badge-secondary">{{ $regra->guard_name }}</span></td>
                            <td><span class="badge badge-primary">{{ $totalPerms }}</span></td>
                            <td><span class="badge badge-info">{{ $totalGrupos }}</span></td>
                            <td>
                                <a href="{{ route('regra.edit', $regra->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fa fa-edit"></i> Editar
                                </a>
                                <x-delete-button :action="route('regra.destroy', $regra->id)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-2">
            {{ $regras->links() }}
        </div>
    @endif

        </div>{{-- card-body --}}
    </div>{{-- card --}}

</div>{{-- rg-page --}}
@stop

@section('css')
<style>
/* ── Page ── */
.rg-page { padding-bottom: 2rem; }

/* ── Hero ── */
.rg-hero {
    display: flex; align-items: center; justify-content: space-between;
    background: linear-gradient(120deg, #f0f4ff 0%, #e8f0fe 100%);
    border-radius: 14px; padding: 1.25rem 1.5rem; gap: 1rem;
}
.rg-hero__left { display: flex; align-items: center; gap: 1rem; }
.rg-hero__icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: #4b80ff20; display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: #4b80ff; flex-shrink: 0;
}
.rg-hero__title { font-size: 1.2rem; font-weight: 700; color: #0f2740; margin: 0 0 .2rem; }
.rg-hero__sub   { font-size: .82rem; color: #6c757d; margin: 0; }
.rg-hero__btn   { border-radius: 10px; font-weight: 600; white-space: nowrap; }

/* ── Stat chip ── */
.rg-stat {
    display: flex; align-items: center; gap: .8rem;
    background: #e8f0fe; border-radius: 12px; padding: .7rem 1rem;
}
.rg-stat__icon { font-size: 1.3rem; color: #4b80ff; opacity: .8; }
.rg-stat__val  { font-size: 1.25rem; font-weight: 700; color: #0f2740; line-height: 1; }
.rg-stat__lbl  { font-size: .74rem; color: #6c757d; }

/* ── Grid de cards ── */
.rg-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 1rem;
}

/* ── Card ── */
.rg-card {
    border: 1px solid #e5eaf2; border-radius: 14px;
    background: #fff; overflow: hidden;
    transition: box-shadow .15s;
}
.rg-card:hover { box-shadow: 0 4px 18px rgba(75,128,255,.1); }

/* ── Card header ── */
.rg-card__header {
    display: flex; align-items: flex-start; gap: .85rem;
    padding: 1rem 1.1rem .75rem;
    background: linear-gradient(120deg, #f8faff 0%, #f0f4ff 100%);
    border-bottom: 1px solid #edf1fb;
}
.rg-card__icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: #4b80ff18; display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: #4b80ff; flex-shrink: 0; margin-top: .1rem;
}
.rg-card__info  { flex: 1; min-width: 0; }
.rg-card__name  { font-size: .97rem; font-weight: 700; color: #0f2740; margin: 0 0 .2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rg-card__guard { font-size: .74rem; color: #8ca0b8; background: #f0f4ff; padding: .1rem .45rem; border-radius: 6px; }
.rg-card__actions { display: flex; gap: .3rem; flex-shrink: 0; }

/* ── Stats bar ── */
.rg-card__stats {
    display: flex; align-items: center; gap: .4rem;
    padding: .6rem 1.1rem; border-bottom: 1px solid #f0f3f8; flex-wrap: wrap;
}
.rg-badge {
    display: inline-flex; align-items: center;
    font-size: .72rem; font-weight: 600; padding: .2rem .55rem; border-radius: 20px;
}
.rg-badge--blue { background: #e8f0fe; color: #4b80ff; }
.rg-badge--gray { background: #f0f3f8; color: #6c757d; }

/* ── Expand button ── */
.rg-btn-expand {
    border: 1px solid #d8e1ec; border-radius: 8px;
    background: #fff; color: #4b80ff; font-size: .72rem; font-weight: 600;
    padding: .2rem .6rem; transition: all .15s;
}
.rg-btn-expand:hover { background: #f0f4ff; }
.rg-btn-expand[aria-expanded="true"] .fa-chevron-down { transform: rotate(180deg); }
.rg-btn-expand .fa-chevron-down { transition: transform .2s; }
.rg-btn-expand__lbl { margin-left: .25rem; }

/* ── Permissões panel ── */
.rg-card__perms { border-top: 1px solid #f0f3f8; }
.rg-perms-inner { padding: .85rem 1.1rem; display: flex; flex-direction: column; gap: .85rem; }

/* ── Grupo de permissões ── */
.rg-perm-group__header {
    display: flex; align-items: center; gap: .4rem;
    font-size: .78rem; font-weight: 700; margin-bottom: .45rem;
}
.rg-perm-group__count {
    font-size: .7rem; font-weight: 700;
    padding: .1rem .4rem; border-radius: 10px; margin-left: auto;
}
.rg-perm-group__items { display: flex; flex-direction: column; gap: .2rem; }

/* ── Item de permissão ── */
.rg-perm-item {
    display: flex; align-items: center; gap: .4rem;
    font-size: .78rem; padding: .2rem .35rem; border-radius: 6px;
    background: #f8faff;
}
.rg-perm-item__badge {
    font-size: .68rem; font-weight: 700; padding: .1rem .4rem;
    border-radius: 5px; flex-shrink: 0;
}
.rg-perm-item__desc { color: #495057; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── View toggle ── */
.rg-view-toggle { display: flex; gap: 5px; }
.rg-view-toggle .btn.active { background: #0f172a; color: #fff; border-color: #0f172a; }

.rg-index-body[data-view="table"] .rg-grid  { display: none; }
.rg-index-body[data-view="cards"] .rg-table { display: none; }

.rg-index-body[data-view="table"] .rg-table {
    max-height: calc(100vh - 360px);
    overflow: auto;
    border-radius: 6px;
}
.rg-index-body[data-view="table"] .rg-table thead th {
    position: sticky; top: 0; z-index: 2; background: #f8fafc;
}

@media (max-width: 576px) {
    .rg-hero { flex-direction: column; align-items: flex-start; }
    .rg-hero__btn { align-self: stretch; text-align: center; }
    .rg-grid { grid-template-columns: 1fr; }
}
</style>
@stop

@section('js')
<script>
(function () {
    const body = document.querySelector('.rg-index-body');
    const buttons = document.querySelectorAll('.rg-view-toggle [data-view]');
    if (!body || !buttons.length) return;

    function setView(view) {
        body.setAttribute('data-view', view);
        localStorage.setItem('regraIndexView', view);
        buttons.forEach(btn => btn.classList.toggle('active', btn.dataset.view === view));
    }

    const saved = localStorage.getItem('regraIndexView') || 'cards';
    setView(saved);
    buttons.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));

    // Expand/collapse label
    document.querySelectorAll('[data-target]').forEach(function (el) {
        el.addEventListener('click', function () {
            const lbl = this.querySelector('.rg-btn-expand__lbl');
            if (!lbl) return;
            const expanded = this.getAttribute('aria-expanded') === 'true';
            lbl.textContent = expanded ? 'Ver permissões' : 'Ocultar permissões';
        });
    });
})();
</script>
@stop
