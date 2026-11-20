@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Contas de acesso: equipe, alunos e responsáveis" :create-url="auth()->user()->hasPermissao('usuario.create') ? route('usuario.create') : null" :create-can="'usuario.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .ux-tabs { display: flex; gap: .4rem; flex-wrap: wrap; width: 100%; margin-bottom: .55rem; padding-bottom: .6rem; border-bottom: 1px solid #f1f5f9; }
        .ux-tab { display: inline-flex; align-items: center; gap: .4rem; padding: .38rem .9rem; border-radius: 999px; border: 1.5px solid #e2e8f0; background: #fff; font-size: .76rem; font-weight: 700; color: #64748b; text-decoration: none; transition: all .13s; }
        .ux-tab:hover { border-color: #c7d2fe; color: #4f46e5; text-decoration: none; }
        .ux-tab.is-on { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .ux-tab .ux-tab-n { font-size: .64rem; font-weight: 800; background: #f1f5f9; color: #64748b; border-radius: 999px; padding: .1rem .45rem; }
        .ux-tab.is-on .ux-tab-n { background: rgba(255,255,255,.22); color: #fff; }
    </style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        @php($abas = [
            'funcionarios' => ['Funcionários', 'fa-briefcase'],
            'alunos' => ['Alunos', 'fa-graduation-cap'],
            'responsaveis' => ['Responsáveis', 'fa-users'],
            'todos' => ['Todos', 'fa-list'],
        ])
        <div class="ux-tabs">
            @foreach($abas as $abv => [$abl, $abi])
                <a href="{{ route('usuario.index', ['grupo' => $abv]) }}" class="ux-tab {{ $grupo === $abv ? 'is-on' : '' }}">
                    <i class="fa {{ $abi }}"></i> {{ $abl }}
                    <span class="ux-tab-n">{{ $contagemGrupos[$abv] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'usuario_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
                <div class="ux-cards">
            @forelse($usuarios as $usuario)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar"><i class="fas fa-user"></i></div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $usuario->nome }}</div>
                            <div class="ux-card__sub">{{ $usuario->email }}</div>
                        </div>
                        <div class="ux-card__badges">
                            @php($tipo = $usuario->tipo_conta)
                                @if($tipo === 'aluno')
                                    <span class="badge badge-primary">Aluno</span>
                                @elseif($tipo === 'responsavel')
                                    <span class="badge badge-info">Responsável</span>
                                @else
                                    <span class="badge badge-dark">{{ ucwords(str_replace('_', ' ', $usuario->regra->name ?? 'Colaborador')) }}</span>
                                @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Perfil</span>
                            <span class="ux-card__meta-value">{{ $usuario->regra->name ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Vínculo</span>
                            <span class="ux-card__meta-value">@if($usuario->aluno)
                                    @can('aluno.show')
                                        <a href="{{ route('aluno.show', $usuario->aluno->id) }}">{{ $usuario->aluno->nome }}</a>
                                    @else
                                        {{ $usuario->aluno->nome }}
                                    @endcan
                                @elseif($usuario->responsavel)
                                    @can('responsavel.show')
                                        <a href="{{ route('responsavel.show', $usuario->responsavel->id) }}">{{ $usuario->responsavel->nome }}</a>
                                    @else
                                        {{ $usuario->responsavel->nome }}
                                    @endcan
                                @else
                                    —
                                @endif</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Escolas</span>
                            <span class="ux-card__meta-value">{{ $usuario->escolas->pluck('nome')->join(', ') ?: '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Status</span>
                            <span class="ux-card__meta-value">@if($usuario->ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('usuario.show')
                                    <a href="{{ route('usuario.show', $usuario->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                @endcan
                                @can('usuario.edit')
                                    <a href="{{ route('usuario.edit', $usuario->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('usuario.alterarSenha')
                                    <a href="{{ route('usuario.alterarSenha', $usuario->id) }}" class="btn btn-xs btn-secondary" title="Senha"><i class="fa fa-key"></i></a>
                                @endcan
                                @can('usuario.destroy')
                                    <x-delete-button :action="route('usuario.destroy', $usuario->id)" titulo="Excluir" mensagem="Excluir o usuário &quot;{{ $usuario->nome }}&quot;?" />
                                @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-user fa-2x mb-2 d-block"></i>
                    Nenhum usuário encontrado.
                </div>
            @endforelse
        </div>
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Tipo</th>
                        <th>Perfil</th>
                        <th>Vínculo</th>
                        <th>Escolas</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        <tr>
                            <td class="font-weight-bold">{{ $usuario->nome }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>
                                @php($tipo = $usuario->tipo_conta)
                                @if($tipo === 'aluno')
                                    <span class="badge badge-primary">Aluno</span>
                                @elseif($tipo === 'responsavel')
                                    <span class="badge badge-info">Responsável</span>
                                @else
                                    <span class="badge badge-dark">{{ ucwords(str_replace('_', ' ', $usuario->regra->name ?? 'Colaborador')) }}</span>
                                @endif
                            </td>
                            <td>{{ $usuario->regra->name ?? '—' }}</td>
                            <td>
                                @if($usuario->aluno)
                                    @can('aluno.show')
                                        <a href="{{ route('aluno.show', $usuario->aluno->id) }}">{{ $usuario->aluno->nome }}</a>
                                    @else
                                        {{ $usuario->aluno->nome }}
                                    @endcan
                                @elseif($usuario->responsavel)
                                    @can('responsavel.show')
                                        <a href="{{ route('responsavel.show', $usuario->responsavel->id) }}">{{ $usuario->responsavel->nome }}</a>
                                    @else
                                        {{ $usuario->responsavel->nome }}
                                    @endcan
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $usuario->escolas->pluck('nome')->join(', ') ?: '—' }}</td>
                            <td>
                                @if($usuario->ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('usuario.show')
                                    <a href="{{ route('usuario.show', $usuario->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                @endcan
                                @can('usuario.edit')
                                    <a href="{{ route('usuario.edit', $usuario->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('usuario.alterarSenha')
                                    <a href="{{ route('usuario.alterarSenha', $usuario->id) }}" class="btn btn-xs btn-secondary" title="Senha"><i class="fa fa-key"></i></a>
                                @endcan
                                @can('usuario.destroy')
                                    <x-delete-button :action="route('usuario.destroy', $usuario->id)" titulo="Excluir" mensagem="Excluir o usuário &quot;{{ $usuario->nome }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Nenhum usuário encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($usuarios->hasPages())
        <div class="card-footer">
            {{ $usuarios->links() }}
        </div>
    @endif
</div>
@stop
