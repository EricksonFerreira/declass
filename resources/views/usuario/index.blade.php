@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Contas de acesso: equipe, alunos e responsáveis" :create-url="auth()->user()->hasPermissao('usuario.create') ? route('usuario.create') : null" :create-can="'usuario.create'" />
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
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
                                    <span class="badge badge-secondary">Colaborador</span>
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
