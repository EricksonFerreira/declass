@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .rg-perms { border-top: 1px solid #f0f3f8; }
        .rg-perms-inner { padding: .85rem 1.1rem; display: flex; flex-direction: column; gap: .85rem; }
        .rg-perm-group__header {
            display: flex; align-items: center; gap: .4rem;
            font-size: .78rem; font-weight: 700; color: #4b80ff; margin-bottom: .45rem;
        }
        .rg-perm-group__count {
            font-size: .7rem; font-weight: 700; padding: .1rem .4rem;
            border-radius: 10px; margin-left: auto; background: #4b80ff20; color: #4b80ff;
        }
        .rg-perm-group__items { display: flex; flex-direction: column; gap: .3rem; }
        .rg-perm-sub {
            display: flex; align-items: center; gap: .4rem; width: 100%;
            border: 1px solid #e8eef7; border-radius: 8px; background: #fff;
            padding: .3rem .55rem; font-size: .78rem; font-weight: 600; color: #374151;
            text-align: left; cursor: pointer; transition: background .15s;
        }
        .rg-perm-sub:hover { background: #f4f7fd; }
        .rg-perm-sub .fa-chevron-down { transition: transform .2s; font-size: .65rem; color: #8ca0b8; }
        .rg-perm-sub[aria-expanded="true"] .fa-chevron-down { transform: rotate(180deg); }
        .rg-perm-sub__count {
            font-size: .68rem; font-weight: 700; padding: .05rem .4rem;
            border-radius: 10px; margin-left: auto; background: #eef2f9; color: #4b80ff;
        }
        .rg-perm-sub__items {
            display: flex; flex-wrap: wrap; gap: .25rem;
            padding: .35rem 0 .15rem .4rem;
        }
        .rg-perm-item__badge {
            font-size: .68rem; font-weight: 700; padding: .15rem .45rem;
            border-radius: 5px; background: #4b80ff1c; color: #4b80ff;
            cursor: default;
        }
        .rg-perm-item__badge:hover { background: #4b80ff33; }
        .rg-btn-expand {
            border: 1px solid #d8e1ec; border-radius: 8px;
            background: #fff; color: #4b80ff; font-size: .72rem; font-weight: 600;
            padding: .2rem .6rem; transition: all .15s;
        }
        .rg-btn-expand:hover { background: #f0f4ff; }
        .rg-btn-expand[aria-expanded="true"] .fa-chevron-down { transform: rotate(180deg); }
        .rg-btn-expand .fa-chevron-down { transition: transform .2s; }
    </style>
@endpush

@php
    $grupoMeta = [
        'empresa'     => ['label' => 'Tenant / Organização', 'icon' => 'fas fa-building'],
        'escola'      => ['label' => 'Escolas',               'icon' => 'fas fa-school'],
        'escopo'      => ['label' => 'Escopo de Visão',       'icon' => 'fas fa-eye'],
        'usuario'     => ['label' => 'Usuários',              'icon' => 'fas fa-users'],
        'regra'       => ['label' => 'Perfis de Acesso',      'icon' => 'fas fa-user-shield'],
        'permissao'   => ['label' => 'Permissões',            'icon' => 'fas fa-key'],
        'documentos'  => ['label' => 'Documentos',            'icon' => 'fas fa-folder-open'],
        'notificacoes'=> ['label' => 'Notificações',          'icon' => 'fas fa-bell'],
        'dashboard'   => ['label' => 'Dashboard',             'icon' => 'fas fa-chart-bar'],
        'home'        => ['label' => 'Início',                'icon' => 'fas fa-home'],
        'profile'     => ['label' => 'Perfil',                'icon' => 'fas fa-user-circle'],
        '_outros'     => ['label' => 'Outros',                'icon' => 'fas fa-ellipsis-h'],
    ];

    $featureLabel = [
        'minha' => 'Minha Organização',
        'atualizar' => 'Atualizar',
        'alterar-senha' => 'Alterar Senha',
        'select' => 'Seleção',
        'documentos' => 'Documentos',
        'arquivo' => 'Arquivo',
        'usuarios' => 'Usuários',
        'escolas' => 'Escolas',
        'contexto' => 'Contexto',
    ];

    $acaoLabel = [
        'index'         => 'Listar',
        'show'          => 'Ver',
        'create'        => 'Criar',
        'store'         => 'Salvar',
        'edit'          => 'Editar',
        'update'        => 'Atualizar',
        'destroy'       => 'Excluir',
        'delete'        => 'Excluir',
        'filtro'        => 'Filtrar',
        'removerFiltro' => 'Limpar filtro',
        'setPerPage'    => 'Itens por página',
        'start'         => 'Iniciar',
        'stop'          => 'Parar',
        'manual'        => 'Apontamento manual',
        'save'          => 'Salvar',
        'mover'         => 'Mover',
        'approve'       => 'Aprovar',
        'reject'        => 'Rejeitar',
        'export'        => 'Exportar',
        'exportar'      => 'Exportar',
    ];
@endphp

@section('content')
    @if (!empty($filters))
        <div class="veiculo-filter-sticky">
            <x-filter
                :filters="$filters"
                :additionalFilters="$additionalFilters"
                :itensFiltered="$itensFiltered"
                :nomeFiltro="'regra'"
            />
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $regras->total() }}</span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <form action="{{ route('regra.setPerPage') }}" method="POST" class="mb-0">
                            @csrf
                            <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                        </form>
                        @include('partials.ux-view-toggle', ['viewKey' => 'regra_index_view'])
                        <a href="{{ route('regra.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus mr-1"></i> Novo Perfil
                        </a>
                    </div>
                </div>
                <div class="card-body p-0 ux-index-body" data-view="cards">
                    <div class="ux-cards">
                        @forelse ($regras as $regra)
                            @php
                                $totalPerms = count($regra->permissoes);
                                $groupedPerms = $regra->permissoes->groupBy(fn ($p) => $p->grupo_efetivo ?: '_outros');
                                $totalGrupos = $groupedPerms->count();
                                $cardId = 'rg-card-' . $regra->id;
                                $nomeRegra = trim((string) ($regra->name ?? 'Perfil'));
                                $partes = preg_split('/\s+/', $nomeRegra) ?: [];
                                $iniciais = collect($partes)->filter()->take(2)
                                    ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'PF';
                            @endphp
                            <article class="ux-card">
                                <div class="ux-card__accent"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar">{{ $iniciais }}</div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__title">{{ $regra->name }}</div>
                                        <div class="ux-card__sub"><i class="fas fa-lock mr-1" style="font-size:.7rem"></i>{{ $regra->guard_name }}</div>
                                    </div>
                                    <div class="ux-card__badges">
                                        @if (is_null($regra->empresa_id))
                                            <span class="ux-card__badge ux-card__badge--gray" title="Disponível para todas as empresas">Padrão</span>
                                        @else
                                            <span class="ux-card__badge ux-card__badge--blue" title="Perfil exclusivo desta empresa">{{ $regra->empresa->nome ?? 'Empresa' }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="ux-card__meta">
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-key mr-1"></i>Permissões</span>
                                        <strong class="ux-card__meta-value">{{ $totalPerms }}</strong>
                                    </div>
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-th-large mr-1"></i>Módulos</span>
                                        <strong class="ux-card__meta-value">{{ $totalGrupos }}</strong>
                                    </div>
                                </div>
                                @if ($totalPerms > 0)
                                    <div class="px-3 pb-2">
                                        <button type="button" class="btn btn-xs rg-btn-expand"
                                            data-toggle="collapse" data-target="#{{ $cardId }}" aria-expanded="false">
                                            <i class="fas fa-chevron-down"></i>
                                            <span class="rg-btn-expand__lbl">Ver permissões</span>
                                        </button>
                                    </div>
                                    <div id="{{ $cardId }}" class="collapse rg-perms">
                                        <div class="rg-perms-inner">
                                            @foreach ($groupedPerms as $grupo => $perms)
                                                @php $gm = $grupoMeta[$grupo] ?? $grupoMeta['_outros']; @endphp
                                                <div class="rg-perm-group">
                                                    <div class="rg-perm-group__header">
                                                        <i class="{{ $gm['icon'] }} mr-1"></i>
                                                        <span>{{ $gm['label'] !== 'Outros' ? $gm['label'] : ucfirst($grupo) }}</span>
                                                        <span class="rg-perm-group__count">{{ $perms->count() }}</span>
                                                    </div>
                                                    <div class="rg-perm-group__items">
                                                        @foreach ($perms->groupBy(fn ($p) => count(explode('.', $p->name)) >= 3 ? explode('.', $p->name)[1] : '_geral') as $feature => $featurePerms)
                                                            @php
                                                                $subId = $cardId . '-' . $loop->parent->index . '-' . $loop->index;
                                                                $featureNome = $featureLabel[$feature] ?? ($feature === '_geral' ? 'Geral' : ucfirst(str_replace(['_', '-'], ' ', $feature)));
                                                            @endphp
                                                            <button type="button" class="rg-perm-sub"
                                                                data-toggle="collapse" data-target="#{{ $subId }}" aria-expanded="false">
                                                                <i class="fas fa-chevron-down"></i>
                                                                <span>{{ $featureNome }}</span>
                                                                <span class="rg-perm-sub__count">{{ $featurePerms->count() }}</span>
                                                            </button>
                                                            <div id="{{ $subId }}" class="collapse">
                                                                <div class="rg-perm-sub__items">
                                                                    @foreach ($featurePerms as $p)
                                                                        @php
                                                                            $partesNome = explode('.', $p->name);
                                                                            $acao = count($partesNome) >= 3 ? $partesNome[2] : end($partesNome);
                                                                        @endphp
                                                                        <span class="rg-perm-item__badge" title="{{ $p->name }}">{{ $acaoLabel[$acao] ?? $acao }}</span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <div class="ux-card__foot">
                                    <a href="{{ route('regra.show', $regra->id) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                    <a href="{{ route('regra.edit', $regra->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fa fa-edit mr-1"></i>Editar
                                    </a>
                                    <x-delete-button :action="route('regra.destroy', $regra->id)" />
                                </div>
                            </article>
                        @empty
                            <div class="text-center text-muted py-4 w-100">
                                <i class="fas fa-user-shield fa-2x mb-2 d-block"></i>
                                Nenhum perfil de acesso cadastrado.
                            </div>
                        @endforelse
                    </div>
                    <div class="ux-table table-responsive">
                        <table class="table table-hover table-striped table-bordered table-sm text-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Nome</th>
                                    <th>Empresa</th>
                                    <th>Guard</th>
                                    <th>Permissões</th>
                                    <th>Módulos</th>
                                    <th style="width: 200px;" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($regras as $regra)
                                    @php
                                        $totalPerms = count($regra->permissoes);
                                        $totalGrupos = $regra->permissoes->groupBy(fn ($p) => $p->grupo_efetivo ?: '_outros')->count();
                                    @endphp
                                    <tr>
                                        <td class="align-middle">{{ $regra->id }}</td>
                                        <td class="align-middle font-weight-bold">{{ $regra->name }}</td>
                                        <td class="align-middle">
                                            <span class="badge badge-{{ is_null($regra->empresa_id) ? 'secondary' : 'primary' }}">
                                                {{ is_null($regra->empresa_id) ? 'Padrão (todas)' : ($regra->empresa->nome ?? 'Empresa') }}
                                            </span>
                                        </td>
                                        <td class="align-middle"><span class="badge badge-light border">{{ $regra->guard_name }}</span></td>
                                        <td class="align-middle"><span class="badge badge-primary">{{ $totalPerms }}</span></td>
                                        <td class="align-middle"><span class="badge badge-info">{{ $totalGrupos }}</span></td>
                                        <td class="align-middle text-center">
                                            <a href="{{ route('regra.show', $regra->id) }}" class="btn btn-xs btn-info mr-1">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            <a href="{{ route('regra.edit', $regra->id) }}" class="btn btn-xs btn-warning mr-1">
                                                <i class="fa fa-edit"></i> Editar
                                            </a>
                                            <x-delete-button :action="route('regra.destroy', $regra->id)" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-user-shield fa-2x mb-2 d-block"></i>
                                            Nenhum perfil de acesso cadastrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($regras->hasPages())
                    <div class="card-footer py-2 d-flex justify-content-end">
                        {{ $regras->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    // Alterna o rótulo do botão de expandir permissões
    document.querySelectorAll('.rg-btn-expand[data-toggle="collapse"]').forEach(function (el) {
        $(el).on('click', function () {
            var lbl = this.querySelector('.rg-btn-expand__lbl');
            if (!lbl) return;
            var expanded = this.getAttribute('aria-expanded') === 'true';
            lbl.textContent = expanded ? 'Ver permissões' : 'Ocultar permissões';
        });
    });
</script>
@stop
