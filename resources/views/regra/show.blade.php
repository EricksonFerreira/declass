@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $grupoMeta = [
        'reserva'      => ['label' => 'Reservas',       'icon' => 'fas fa-calendar-check',      'color' => '#4b80ff'],
        'veiculo'      => ['label' => 'Veículos',        'icon' => 'fas fa-car',                 'color' => '#28a745'],
        'usuario'      => ['label' => 'Usuários',        'icon' => 'fas fa-users',               'color' => '#17a2b8'],
        'permissao'    => ['label' => 'Permissões',      'icon' => 'fas fa-key',                 'color' => '#e0a800'],
        'regra'        => ['label' => 'Regras',          'icon' => 'fas fa-shield-alt',          'color' => '#e0a800'],
        'multa'        => ['label' => 'Multas',          'icon' => 'fas fa-file-invoice-dollar', 'color' => '#dc3545'],
        'movimentacao' => ['label' => 'Movimentações',   'icon' => 'fas fa-exchange-alt',        'color' => '#6c757d'],
        'financeiro'   => ['label' => 'Financeiro',      'icon' => 'fas fa-dollar-sign',         'color' => '#28a745'],
        'calcao'       => ['label' => 'Caução',          'icon' => 'fas fa-hand-holding-usd',    'color' => '#28a745'],
        'empresa'      => ['label' => 'Empresa',         'icon' => 'fas fa-building',            'color' => '#343a40'],
        'modelo'       => ['label' => 'Modelos',         'icon' => 'fas fa-car-side',            'color' => '#6c757d'],
        'nfse'         => ['label' => 'NFS-e',           'icon' => 'fas fa-file-alt',            'color' => '#343a40'],
        'pagamento'    => ['label' => 'Pagamento',       'icon' => 'fas fa-credit-card',         'color' => '#28a745'],
        'dashboard'    => ['label' => 'Dashboard',       'icon' => 'fas fa-chart-bar',           'color' => '#17a2b8'],
        '_outros'      => ['label' => 'Outros',          'icon' => 'fas fa-ellipsis-h',          'color' => '#6c757d'],
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

    $totalPerms  = $regra->permissoes->count();
    $groupedPerms = $regra->permissoes->groupBy(fn($p) => $p->grupo_efetivo ?: '_outros');
    $totalGrupos  = $groupedPerms->count();
@endphp

<div class="container-fluid px-0">

    {{-- ════ HERO ════ --}}
    <div class="module-card mb-4">
        <div class="module-card-header">
            <div class="module-header-left">
                <div class="module-header-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <div class="module-header-eyebrow">Configuração &bull; Regras de Acesso</div>
                    <h1 class="module-header-title">{{ $regra->name }}</h1>
                    <p class="module-header-sub">
                        Guard: <strong style="color:#93c5fd">{{ $regra->guard_name }}</strong>
                        &bull; {{ $totalPerms }} permissão{{ $totalPerms !== 1 ? 'ões' : '' }}
                        em {{ $totalGrupos }} módulo{{ $totalGrupos !== 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
            <div class="module-header-right">
                <a href="{{ route('regra.edit', $regra->id) }}" class="btn-xp btn-xp--sm mr-2">
                    <i class="fas fa-pen mr-1"></i>Editar
                </a>
                <a href="{{ route('regra.index') }}" class="btn-xp btn-xp--outline-light btn-xp--sm">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    {{-- ════ GRID PRINCIPAL ════ --}}
    <div class="row">

        {{-- ── Coluna esquerda: permissões agrupadas ── --}}
        <div class="col-lg-8">

            @if($regra->permissoes->isEmpty())
                <div class="config-card mb-4">
                    <div class="config-block">
                        <div class="empty-state">
                            <i class="fas fa-key"></i>
                            <p>Esta regra ainda não possui permissões atribuídas.</p>
                            <a href="{{ route('regra.edit', $regra->id) }}" class="btn-xp btn-xp--sm mt-2">
                                <i class="fas fa-plus mr-1"></i>Atribuir permissões
                            </a>
                        </div>
                    </div>
                </div>
            @else
                @foreach($groupedPerms as $grupo => $perms)
                    @php
                        $gm  = $grupoMeta[$grupo] ?? $grupoMeta['_outros'];
                        $hex = $gm['color'];
                    @endphp
                    <div class="config-card mb-3">
                        <div class="config-block">
                            <div class="config-block-label" style="border-left: 3px solid {{ $hex }}; padding-left: .75rem;">
                                <i class="{{ $gm['icon'] }}" style="color:{{ $hex }}"></i>
                                <div>
                                    <strong style="color:{{ $hex }}">{{ $gm['label'] }}</strong>
                                    <span>{{ $perms->count() }} permissão{{ $perms->count() !== 1 ? 'ões' : '' }}</span>
                                </div>
                            </div>

                            <div class="rg-show-perms-grid">
                                @foreach($perms as $p)
                                    @php
                                        $acao = str_contains($p->name, '.') ? explode('.', $p->name)[1] : $p->name;
                                        [$acaoLabel, $acaoColor] = $acaoBadge[$acao] ?? [$acao, '#6c757d'];
                                    @endphp
                                    <div class="rg-show-perm-item">
                                        <span class="rg-show-perm-item__badge"
                                              style="background:{{ $acaoColor }}20;color:{{ $acaoColor }};border-color:{{ $acaoColor }}40">
                                            {{ $acaoLabel }}
                                        </span>
                                        <div class="rg-show-perm-item__info">
                                            <span class="rg-show-perm-item__desc">{{ $p->descricao ?: $p->name }}</span>
                                            <span class="rg-show-perm-item__name">{{ $p->name }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>

        {{-- ── Coluna direita: resumo e ações ── --}}
        <div class="col-lg-4">

            {{-- Ações rápidas --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-bolt"></i>
                        <div><strong>Ações rápidas</strong></div>
                    </div>
                    <div class="veiculo-acoes-grid">
                        <a href="{{ route('regra.edit', $regra->id) }}" class="veiculo-acao veiculo-acao--primary">
                            <i class="fas fa-pen"></i>
                            <span>Editar regra</span>
                        </a>
                        <a href="{{ route('regra.index') }}" class="veiculo-acao">
                            <i class="fas fa-list"></i>
                            <span>Ver todas</span>
                        </a>
                        <a href="{{ route('regra.create') }}" class="veiculo-acao">
                            <i class="fas fa-plus"></i>
                            <span>Nova regra</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Resumo --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Resumo</strong>
                            <span>Visão geral da regra</span>
                        </div>
                    </div>
                    <div class="veiculo-summary">
                        <div class="veiculo-summary__row">
                            <span>ID</span>
                            <strong>#{{ $regra->id }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Guard</span>
                            <strong>{{ $regra->guard_name }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Total de permissões</span>
                            <strong class="text-primary">{{ $totalPerms }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Módulos cobertos</span>
                            <strong class="text-info">{{ $totalGrupos }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Criado em</span>
                            <strong>{{ $regra->created_at ? $regra->created_at->format('d/m/Y') : '—' }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Atualizado em</span>
                            <strong>{{ $regra->updated_at ? $regra->updated_at->format('d/m/Y') : '—' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Módulos cobertos --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-th-large"></i>
                        <div>
                            <strong>Módulos</strong>
                            <span>{{ $totalGrupos }} módulo{{ $totalGrupos !== 1 ? 's' : '' }} com acesso</span>
                        </div>
                    </div>
                    <div class="rg-show-modulos">
                        @foreach($groupedPerms as $grupo => $perms)
                            @php
                                $gm  = $grupoMeta[$grupo] ?? $grupoMeta['_outros'];
                                $hex = $gm['color'];
                            @endphp
                            <div class="rg-show-modulo-chip" style="background:{{ $hex }}15;border-color:{{ $hex }}30">
                                <i class="{{ $gm['icon'] }}" style="color:{{ $hex }}"></i>
                                <span style="color:{{ $hex }}">{{ $gm['label'] }}</span>
                                <span class="rg-show-modulo-chip__count" style="background:{{ $hex }};color:#fff">{{ $perms->count() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@stop

@section('css')
<style>
/* ── Grid de permissões ── */
.rg-show-perms-grid {
    display: flex;
    flex-direction: column;
    gap: .4rem;
    margin-top: .75rem;
}

.rg-show-perm-item {
    display: flex;
    align-items: center;
    gap: .6rem;
    background: #f8faff;
    border: 1px solid #edf1fb;
    border-radius: 8px;
    padding: .45rem .7rem;
}

.rg-show-perm-item__badge {
    font-size: .68rem;
    font-weight: 700;
    padding: .15rem .45rem;
    border-radius: 6px;
    border: 1px solid transparent;
    flex-shrink: 0;
    white-space: nowrap;
}

.rg-show-perm-item__info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.rg-show-perm-item__desc {
    font-size: .82rem;
    color: #1e293b;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rg-show-perm-item__name {
    font-size: .70rem;
    color: #94a3b8;
    font-family: monospace;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Módulos chips ── */
.rg-show-modulos {
    display: flex;
    flex-direction: column;
    gap: .4rem;
    margin-top: .6rem;
}

.rg-show-modulo-chip {
    display: flex;
    align-items: center;
    gap: .5rem;
    border: 1px solid transparent;
    border-radius: 8px;
    padding: .4rem .65rem;
    font-size: .82rem;
    font-weight: 600;
}

.rg-show-modulo-chip__count {
    margin-left: auto;
    font-size: .7rem;
    font-weight: 700;
    padding: .1rem .4rem;
    border-radius: 10px;
    line-height: 1.4;
}

/* ── Reutiliza padrão veiculo/show ── */
.module-card {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(11,18,32,.18);
}

.module-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem 1.75rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.module-header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.module-header-icon {
    width: 54px; height: 54px;
    border-radius: 14px;
    background: rgba(255,255,255,.10);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: #93c5fd;
    flex-shrink: 0;
}

.module-header-eyebrow {
    font-size: .72rem; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; color: #94a3b8; margin-bottom: .2rem;
}

.module-header-title {
    font-size: 1.45rem; font-weight: 800; color: #f1f5f9; margin: 0 0 .2rem; line-height: 1.2;
}

.module-header-sub {
    font-size: .82rem; color: #94a3b8; margin: 0;
}

.module-header-right {
    display: flex; align-items: center; gap: .5rem; flex-shrink: 0;
}

/* ── Config cards ── */
.config-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(11,18,32,.05);
}

.config-block {
    padding: 1.1rem 1.25rem;
}

.config-block + .config-block {
    border-top: 1px solid #f1f5f9;
}

.config-block-label {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    margin-bottom: 1rem;
}

.config-block-label > i {
    font-size: 1rem;
    color: #64748b;
    margin-top: .15rem;
    flex-shrink: 0;
}

.config-block-label strong {
    display: block;
    font-size: .9rem;
    color: #1e293b;
    line-height: 1.3;
}

.config-block-label span {
    display: block;
    font-size: .76rem;
    color: #94a3b8;
    margin-top: .1rem;
}

/* ── Summary rows ── */
.veiculo-summary { display: flex; flex-direction: column; gap: 0; }

.veiculo-summary__row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .5rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: .84rem;
}

.veiculo-summary__row:last-child { border-bottom: none; }
.veiculo-summary__row span { color: #64748b; }
.veiculo-summary__row strong { color: #1e293b; }

/* ── Ações grid ── */
.veiculo-acoes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: .5rem;
    margin-top: .5rem;
}

.veiculo-acao {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .3rem;
    padding: .75rem .5rem;
    border: 1px solid #e2e9f4;
    border-radius: 10px;
    background: #f8fafc;
    font-size: .78rem;
    font-weight: 600;
    color: #334155;
    text-decoration: none;
    transition: all .15s;
    text-align: center;
}

.veiculo-acao:hover {
    border-color: var(--empresa-cor-primaria, #4b80ff);
    background: color-mix(in srgb, var(--empresa-cor-primaria, #4b80ff) 8%, white 92%);
    color: var(--empresa-cor-primaria, #4b80ff);
    text-decoration: none;
}

.veiculo-acao--primary {
    border-color: var(--empresa-cor-primaria, #4b80ff);
    background: color-mix(in srgb, var(--empresa-cor-primaria, #4b80ff) 10%, white 90%);
    color: var(--empresa-cor-primaria, #4b80ff);
}

/* ── Btn-xp ── */
.btn-xp {
    display: inline-flex; align-items: center;
    font-weight: 600; border-radius: 8px; border: none;
    padding: .55rem 1.1rem; font-size: .84rem; cursor: pointer;
    text-decoration: none; transition: all .15s;
    background: var(--empresa-cor-primaria, #334155);
    color: var(--empresa-cor-primaria-contraste, #fff) !important;
}

.btn-xp:hover { opacity: .88; transform: translateY(-1px); text-decoration: none; }

.btn-xp--sm { padding: .35rem .8rem; font-size: .78rem; }

.btn-xp--outline-light {
    background: rgba(255,255,255,.12);
    color: #f1f5f9 !important;
    border: 1px solid rgba(255,255,255,.22);
}

.btn-xp--outline-light:hover { background: rgba(255,255,255,.2); }

/* ── Empty state ── */
.empty-state {
    text-align: center; padding: 2.5rem 1rem; color: #94a3b8;
}

.empty-state i { font-size: 2.5rem; margin-bottom: .75rem; display: block; }
.empty-state p { font-size: .9rem; margin: 0; }
</style>
@stop
