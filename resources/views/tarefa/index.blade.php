@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Dever de casa e atividades por turma" :create-url="auth()->user()->hasPermissao('tarefa.create') ? route('tarefa.create') : null" :create-can="'tarefa.create'" />
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
                        <th>Título</th>
                        <th>Turma</th>
                        <th>Disciplina</th>
                        <th>Entrega</th>
                        <th>Progresso</th>
                        <th>Autor</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tarefas as $tarefa)
                        <tr>
                            <td class="font-weight-bold" style="max-width:260px;">
                                {{ \Illuminate\Support\Str::limit($tarefa->titulo, 60) }}
                                @if($tarefa->atraso())
                                    <span class="badge badge-danger">vencida</span>
                                @endif
                            </td>
                            <td>{{ $tarefa->turma->nome ?? '—' }}</td>
                            <td>{{ $tarefa->disciplina->nome ?? '—' }}</td>
                            <td class="text-nowrap">{{ $tarefa->data_entrega->format('d/m/Y') }}</td>
                            <td class="text-center">
                                @if($tarefa->entregasTotal() > 0)
                                    {{ $tarefa->entregasEntregues() }}/{{ $tarefa->entregasTotal() }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><small>{{ $tarefa->autor->name ?? '—' }}</small></td>
                            <td class="text-right">
                                @can('tarefa.entregas')
                                    <a href="{{ route('tarefa.entregas', $tarefa->id) }}" class="btn btn-xs btn-info" title="Entregas"><i class="fa fa-check-double"></i></a>
                                @endcan
                                @can('tarefa.edit')
                                    <a href="{{ route('tarefa.edit', $tarefa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('tarefa.destroy')
                                    <x-delete-button :action="route('tarefa.destroy', $tarefa->id)" titulo="Excluir" mensagem="Excluir a tarefa &quot;{{ $tarefa->titulo }}&quot;? As entregas também serão removidas." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-tasks fa-2x mb-2 d-block"></i>
                                Nenhuma tarefa cadastrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($tarefas->hasPages())
        <div class="card-footer">
            {{ $tarefas->links() }}
        </div>
    @endif
</div>
@stop
