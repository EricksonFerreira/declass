@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $grupoMeta = [
        'clockify'     => ['label' => 'Operação',         'icon' => 'fas fa-clock',           'color' => '#4b80ff'],
        'usuario'      => ['label' => 'Usuários',         'icon' => 'fas fa-users',           'color' => '#0ea5e9'],
        'regra'        => ['label' => 'Perfis de Acesso', 'icon' => 'fas fa-user-shield',     'color' => '#7c3aed'],
        'permissao'    => ['label' => 'Permissões',       'icon' => 'fas fa-key',             'color' => '#e0a800'],
        'importacao'   => ['label' => 'Importação',       'icon' => 'fas fa-file-import',     'color' => '#f59e0b'],
        'documentos'   => ['label' => 'Documentos',       'icon' => 'fas fa-folder-open',     'color' => '#10b981'],
        'dashboard'    => ['label' => 'Dashboard',        'icon' => 'fas fa-chart-bar',       'color' => '#06b6d4'],
        'home'         => ['label' => 'Painel',           'icon' => 'fas fa-chart-pie',       'color' => '#64748b'],
        'empresa'      => ['label' => 'Empresa',          'icon' => 'fas fa-building',        'color' => '#334155'],
        'assinatura'   => ['label' => 'Assinatura',       'icon' => 'fas fa-file-signature',  'color' => '#ec4899'],
        'licitacoes'   => ['label' => 'Licitações',       'icon' => 'fas fa-gavel',           'color' => '#a855f7'],
        'relatorios'   => ['label' => 'Relatórios',       'icon' => 'fas fa-chart-line',      'color' => '#14b8a6'],
        'relatorio'    => ['label' => 'Relatórios',       'icon' => 'fas fa-chart-line',      'color' => '#14b8a6'],
        'onboarding'   => ['label' => 'Onboarding',       'icon' => 'fas fa-rocket',          'color' => '#f97316'],
        'comandos'     => ['label' => 'Comandos',         'icon' => 'fas fa-terminal',        'color' => '#475569'],
        'planos'       => ['label' => 'Planos',           'icon' => 'fas fa-layer-group',     'color' => '#8b5cf6'],
        '_outros'      => ['label' => 'Outros',           'icon' => 'fas fa-ellipsis-h',      'color' => '#6c757d'],
    ];

    $featureLabel = [
        'calendario'    => 'Calendário',
        'clientes'      => 'Clientes',
        'projetos'      => 'Projetos',
        'tags'          => 'Tags',
        'times'         => 'Equipes',
        'timeoff'       => 'Folgas e Férias',
        'timesheet'     => 'Planilha de Horas',
        'tracker'       => 'Rastreador',
        'funcoes'       => 'Funções',
        'relatorios'    => 'Relatórios',
        'equipe'        => 'Equipe',
        'produtividade' => 'Produtividade',
    ];

    $acaoBadge = [
        'index'         => ['Listar',          '#4b80ff'],
        'show'          => ['Ver',             '#0ea5e9'],
        'create'        => ['Criar',           '#10b981'],
        'store'         => ['Salvar',          '#10b981'],
        'edit'          => ['Editar',          '#f59e0b'],
        'update'        => ['Atualizar',       '#f59e0b'],
        'destroy'       => ['Excluir',         '#ef4444'],
        'delete'        => ['Excluir',         '#ef4444'],
        'filtro'        => ['Filtrar',         '#64748b'],
        'removerFiltro' => ['Limpar filtro',   '#64748b'],
        'setPerPage'    => ['Itens/página',    '#64748b'],
        'start'         => ['Iniciar',         '#10b981'],
        'stop'          => ['Parar',           '#ef4444'],
        'manual'        => ['Manual',          '#8b5cf6'],
        'save'          => ['Salvar',          '#10b981'],
        'mover'         => ['Mover',           '#0ea5e9'],
        'approve'       => ['Aprovar',         '#10b981'],
        'reject'        => ['Rejeitar',        '#ef4444'],
        'export'        => ['Exportar',        '#06b6d4'],
        'exportar'      => ['Exportar',        '#06b6d4'],
    ];

    $totalPerms   = $regra->permissoes->count();

    // Árvore: módulo -> funcionalidade -> permissões
    $arvore = $regra->permissoes
        ->sortBy('name')
        ->groupBy(fn ($p) => $p->grupo_efetivo ?: '_outros')
        ->map(fn ($perms) => $perms->groupBy(fn ($p) => count(explode('.', $p->name)) >= 3 ? explode('.', $p->name)[1] : '_geral'));

    $totalGrupos    = $arvore->count();
    $totalFeatures  = $arvore->sum(fn ($g) => $g->count());
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
                    <div class="module-header-eyebrow">Gerenciar &bull; Perfis de Acesso</div>
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

        {{-- ── Coluna esquerda: árvore de permissões ── --}}
        <div class="col-lg-8">

            @if($regra->permissoes->isEmpty())
                <div class="config-card mb-4">
                    <div class="config-block">
                        <div class="empty-state">
                            <i class="fas fa-key"></i>
                            <p>Este perfil ainda não possui permissões atribuídas.</p>
                            <a href="{{ route('regra.edit', $regra->id) }}" class="btn-xp btn-xp--sm mt-2">
                                <i class="fas fa-plus mr-1"></i>Atribuir permissões
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="d-flex justify-content-end mb-2" style="gap:6px">
                    <button type="button" class="btn btn-xs btn-outline-secondary" id="expandAll">
                        <i class="fas fa-expand-alt mr-1"></i>Expandir tudo
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" id="collapseAll">
                        <i class="fas fa-compress-alt mr-1"></i>Recolher tudo
                    </button>
                </div>

                <div class="config-card mb-4">
                    @foreach($arvore as $grupo => $features)
                        @php
                            $gm  = $grupoMeta[$grupo] ?? ['label' => ucfirst(str_replace(['_', '-'], ' ', $grupo)), 'icon' => 'fas fa-puzzle-piece', 'color' => '#6c757d'];
                            $hex = $gm['color'];
                            $modId = 'rg-mod-' . $loop->index;
                            $modTotal = $features->sum(fn ($f) => $f->count());
                        @endphp
                        <div class="rg-tree-mod">
                            <button type="button" class="rg-tree-mod__head" data-toggle="collapse"
                                data-target="#{{ $modId }}" aria-expanded="true" style="border-left-color:{{ $hex }}">
                                <i class="{{ $gm['icon'] }} rg-tree-mod__icon" style="color:{{ $hex }}"></i>
                                <span class="rg-tree-mod__label">{{ $gm['label'] }}</span>
                                <span class="rg-tree-mod__count" style="background:{{ $hex }}18;color:{{ $hex }}">
                                    {{ $modTotal }} permissã{{ $modTotal !== 1 ? 'ões' : 'o' }}
                                </span>
                                <i class="fas fa-chevron-down rg-tree-mod__chev"></i>
                            </button>

                            <div id="{{ $modId }}" class="collapse show">
                                <div class="rg-tree-mod__body">
                                    @foreach($features as $feature => $perms)
                                        @php
                                            $featId = $modId . '-' . $loop->index;
                                            $featNome = $featureLabel[$feature] ?? ($feature === '_geral' ? 'Geral' : ucfirst(str_replace(['_', '-'], ' ', $feature)));
                                        @endphp
                                        <div class="rg-tree-feat">
                                            <button type="button" class="rg-tree-feat__head" data-toggle="collapse"
                                                data-target="#{{ $featId }}" aria-expanded="true">
                                                <i class="fas fa-chevron-down rg-tree-feat__chev"></i>
                                                <span class="rg-tree-feat__label">{{ $featNome }}</span>
                                                <span class="rg-tree-feat__count">{{ $perms->count() }}</span>
                                            </button>
                                            <div id="{{ $featId }}" class="collapse show">
                                                <div class="rg-tree-perms">
                                                    @foreach($perms as $p)
                                                        @php
                                                            $partesNome = explode('.', $p->name);
                                                            $acao = count($partesNome) >= 3 ? end($partesNome) : $partesNome[1] ?? $p->name;
                                                            [$acaoLabel, $acaoColor] = $acaoBadge[$acao] ?? [ucfirst(str_replace(['_', '-'], ' ', $acao)), '#6c757d'];
                                                        @endphp
                                                        <div class="rg-show-perm-item">
                                                            <span class="rg-show-perm-item__badge"
                                                                  style="background:{{ $acaoColor }}1a;color:{{ $acaoColor }};border-color:{{ $acaoColor }}40">
                                                                {{ $acaoLabel }}
                                                            </span>
                                                            <div class="rg-show-perm-item__info">
                                                                <span class="rg-show-perm-item__desc">{{ $p->descricao ?: $acaoLabel }}</span>
                                                                <span class="rg-show-perm-item__name">{{ $p->name }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
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
                            <span>Editar perfil</span>
                        </a>
                        <a href="{{ route('regra.index') }}" class="veiculo-acao">
                            <i class="fas fa-list"></i>
                            <span>Ver todos</span>
                        </a>
                        <a href="{{ route('regra.create') }}" class="veiculo-acao">
                            <i class="fas fa-plus"></i>
                            <span>Novo perfil</span>
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
                            <span>Visão geral do perfil</span>
                        </div>
                    </div>
                    <div class="veiculo-summary">
                        <div class="veiculo-summary__row">
                            <span>ID</span>
                            <strong>#{{ $regra->id }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Tipo</span>
                            <strong>{{ is_null($regra->empresa_id) ? 'Padrão (global)' : 'Empresa' }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Guard</span>
                            <strong>{{ $regra->guard_name }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Permissões</span>
                            <strong class="text-primary">{{ $totalPerms }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Módulos</span>
                            <strong class="text-info">{{ $totalGrupos }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Funcionalidades</span>
                            <strong class="text-info">{{ $totalFeatures }}</strong>
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
                        @foreach($arvore as $grupo => $features)
                            @php
                                $gm  = $grupoMeta[$grupo] ?? ['label' => ucfirst($grupo), 'icon' => 'fas fa-puzzle-piece', 'color' => '#6c757d'];
                                $hex = $gm['color'];
                            @endphp
                            <div class="rg-show-modulo-chip" style="background:{{ $hex }}12;border-color:{{ $hex }}30">
                                <i class="{{ $gm['icon'] }}" style="color:{{ $hex }}"></i>
                                <span style="color:{{ $hex }}">{{ $gm['label'] }}</span>
                                <span class="rg-show-modulo-chip__count" style="background:{{ $hex }};color:#fff">{{ $features->sum(fn ($f) => $f->count()) }}</span>
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
/* ── Árvore de permissões ── */
.rg-tree-mod + .rg-tree-mod { border-top: 1px solid #eef2f8; }

.rg-tree-mod__head {
    display: flex; align-items: center; gap: .6rem; width: 100%;
    background: #fbfcff; border: none; border-left: 3px solid transparent;
    padding: .8rem 1.1rem; cursor: pointer; text-align: left;
}
.rg-tree-mod__head:hover { background: #f4f7fd; }
.rg-tree-mod__icon { font-size: 1rem; width: 20px; text-align: center; }
.rg-tree-mod__label { font-size: .88rem; font-weight: 700; color: #1e293b; }
.rg-tree-mod__count {
    font-size: .7rem; font-weight: 700; padding: .12rem .5rem; border-radius: 10px;
}
.rg-tree-mod__chev { margin-left: auto; font-size: .7rem; color: #94a3b8; transition: transform .2s; }
.rg-tree-mod__head[aria-expanded="false"] .rg-tree-mod__chev { transform: rotate(-90deg); }

.rg-tree-mod__body { padding: .35rem 1.1rem .9rem 1.6rem; }

/* ── Nó de funcionalidade ── */
.rg-tree-feat__head {
    display: flex; align-items: center; gap: .45rem; width: 100%;
    background: none; border: none; padding: .45rem .55rem;
    border-radius: 8px; cursor: pointer; text-align: left;
}
.rg-tree-feat__head:hover { background: #f4f7fd; }
.rg-tree-feat__chev { font-size: .62rem; color: #8ca0b8; transition: transform .2s; }
.rg-tree-feat__head[aria-expanded="false"] .rg-tree-feat__chev { transform: rotate(-90deg); }
.rg-tree-feat__label { font-size: .8rem; font-weight: 600; color: #334155; }
.rg-tree-feat__count {
    margin-left: auto; font-size: .68rem; font-weight: 700;
    background: #eef2f9; color: #4b80ff; padding: .05rem .42rem; border-radius: 10px;
}

.rg-tree-perms {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: .4rem;
    padding: .25rem 0 .6rem 1.3rem;
}

/* ── Item de permissão ── */
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

@section('js')
<script>
    (function () {
        var heads = document.querySelectorAll('.rg-tree-mod__head, .rg-tree-feat__head');
        var targets = [];
        heads.forEach(function (h) {
            var sel = h.getAttribute('data-target');
            if (sel) targets.push(sel);
        });

        document.getElementById('expandAll')?.addEventListener('click', function () {
            targets.forEach(function (sel) { $(sel).collapse('show'); });
        });
        document.getElementById('collapseAll')?.addEventListener('click', function () {
            targets.forEach(function (sel) { $(sel).collapse('hide'); });
        });
    })();
</script>
@stop
