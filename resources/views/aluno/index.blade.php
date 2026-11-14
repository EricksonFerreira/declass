@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Alunos do tenant" :create-url="auth()->user()->hasPermissao('aluno.create') ? route('aluno.create') : null" :create-can="'aluno.create'" />
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
                        <th>RA</th>
                        <th>Escola</th>
                        <th>Data de ingresso</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alunos as $aluno)
                        <tr>
                            <td>
                                <a href="{{ route('aluno.show', $aluno->id) }}" class="font-weight-bold">{{ $aluno->pessoa->nome_exibicao ?? '—' }}</a>
                                @if($aluno->pessoa?->nome_social)
                                    <div class="text-muted" style="font-size:.75rem;">{{ $aluno->pessoa->nome }}</div>
                                @endif
                            </td>
                            <td>{{ $aluno->registro_academico ?? '—' }}</td>
                            <td>{{ $aluno->escola->nome ?? '—' }}</td>
                            <td>{{ optional($aluno->data_ingresso)->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($aluno->esta_ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('aluno.show', $aluno->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('aluno.historico', $aluno->id) }}" class="btn btn-xs btn-dark" title="Histórico completo"><i class="fa fa-history"></i></a>
                                @can('aluno.edit')
                                    <a href="{{ route('aluno.edit', $aluno->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('aluno.destroy')
                                    <x-delete-button :action="route('aluno.destroy', $aluno->id)" titulo="Excluir" mensagem="Excluir o aluno &quot;{{ $aluno->pessoa->nome_exibicao ?? '' }}&quot;? Os dados são preservados (exclusão lógica)." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-user-graduate fa-2x mb-2 d-block"></i>
                                Nenhum aluno encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($alunos->hasPages())
        <div class="card-footer">
            {{ $alunos->links() }}
        </div>
    @endif
</div>
@stop
