@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Turmas por escola, ano letivo e etapa" :create-url="auth()->user()->hasPermissao('turma.create') ? route('turma.create') : null" :create-can="'turma.create'" />
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
                        <th>Turma</th>
                        <th>Escola</th>
                        <th>Ano letivo</th>
                        <th>Etapa</th>
                        <th>Turno</th>
                        <th>Sala</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($turmas as $turma)
                        <tr>
                            <td>
                                <a href="{{ route('turma.show', $turma->id) }}" class="font-weight-bold">{{ $turma->nome }}</a>
                            </td>
                            <td>{{ $turma->escola->nome ?? '—' }}</td>
                            <td>{{ $turma->anoLetivo->ano ?? '—' }}</td>
                            <td>{{ $turma->etapa->nome ?? '—' }}</td>
                            <td>{{ \App\Models\Turma::TURNOS[$turma->turno ?? ''] ?? '—' }}</td>
                            <td>{{ $turma->sala ?? '—' }}</td>
                            <td>
                                @if($turma->estaAtiva())
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('turma.show', $turma->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                @can('turma.edit')
                                    <a href="{{ route('turma.edit', $turma->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('turma.destroy')
                                    <x-delete-button :action="route('turma.destroy', $turma->id)" titulo="Excluir" mensagem="Excluir a turma &quot;{{ $turma->nome }}&quot;? Disciplinas e atribuições vinculadas são removidas junto." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-chalkboard-teacher fa-2x mb-2 d-block"></i>
                                Nenhuma turma encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($turmas->hasPages())
        <div class="card-footer">
            {{ $turmas->links() }}
        </div>
    @endif
</div>
@stop
