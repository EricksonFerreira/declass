@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Responsáveis de alunos" :create-url="auth()->user()->hasPermissao('responsavel.create') ? route('responsavel.create') : null" :create-can="'responsavel.create'" />
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
                        <th>Contato</th>
                        <th>Profissão</th>
                        <th>Alunos</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($responsaveis as $responsavel)
                        <tr>
                            <td>
                                <a href="{{ route('responsavel.show', $responsavel->id) }}" class="font-weight-bold">{{ $responsavel->pessoa->nome_exibicao ?? '—' }}</a>
                            </td>
                            <td>
                                {{ $responsavel->pessoa->email ?? '—' }}
                                @if($responsavel->pessoa?->celular || $responsavel->pessoa?->telefone)
                                    <div class="text-muted" style="font-size:.75rem;">{{ $responsavel->pessoa->celular ?: $responsavel->pessoa->telefone }}</div>
                                @endif
                            </td>
                            <td>{{ $responsavel->profissao ?? '—' }}</td>
                            <td>{{ $responsavel->alunos_count ?? $responsavel->alunos->count() }}</td>
                            <td>
                                @if($responsavel->esta_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('responsavel.show', $responsavel->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                @can('responsavel.edit')
                                    <a href="{{ route('responsavel.edit', $responsavel->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('responsavel.destroy')
                                    <x-delete-button :action="route('responsavel.destroy', $responsavel->id)" titulo="Excluir" mensagem="Excluir o responsável &quot;{{ $responsavel->pessoa->nome_exibicao ?? '' }}&quot;? Os dados são preservados (exclusão lógica)." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-user-friends fa-2x mb-2 d-block"></i>
                                Nenhum responsável encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($responsaveis->hasPages())
        <div class="card-footer">
            {{ $responsaveis->links() }}
        </div>
    @endif
</div>
@stop
