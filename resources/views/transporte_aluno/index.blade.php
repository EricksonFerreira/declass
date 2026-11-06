@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Alunos vinculados às rotas de transporte" :create-url="auth()->user()->hasPermissao('transporte_aluno.create') ? route('transporte_aluno.create') : null" :create-can="'transporte_aluno.create'" />
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
                        <th>Aluno</th>
                        <th>RA</th>
                        <th>Rota</th>
                        <th>Escola</th>
                        <th>Veículo</th>
                        <th>Parada</th>
                        <th>Situação</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($passageiros as $p)
                        <tr>
                            <td class="font-weight-bold">{{ $p->aluno->pessoa->nome ?? '—' }}</td>
                            <td>{{ $p->aluno->registro_academico ?? '—' }}</td>
                            <td>{{ $p->rota->nome ?? '—' }}</td>
                            <td>{{ $p->rota->escola->nome ?? '—' }}</td>
                            <td>{{ $p->rota->veiculo->placa ?? '—' }}</td>
                            <td>{{ $p->parada->nome ?? '—' }}</td>
                            <td>
                                @if($p->estaAtivo())
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('transporte_aluno.edit')
                                    <a href="{{ route('transporte_aluno.edit', $p->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('transporte_aluno.destroy')
                                    <x-delete-button :action="route('transporte_aluno.destroy', $p->id)" titulo="Excluir" mensagem="Remover {{ $p->aluno->pessoa->nome ?? 'o aluno' }} da rota &quot;{{ $p->rota->nome ?? '' }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-user-friends fa-2x mb-2 d-block"></i>
                                Nenhum passageiro vinculado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($passageiros->hasPages())
        <div class="card-footer">
            {{ $passageiros->links() }}
        </div>
    @endif
</div>
@stop
