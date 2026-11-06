@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Registro de aulas e chamada" :create-url="auth()->user()->hasPermissao('diario.create') ? route('diario.create') : null" :create-can="'diario.create'" />
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
                        <th>Data</th>
                        <th>Turma</th>
                        <th>Disciplina</th>
                        <th>Professor</th>
                        <th>Conteúdo</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($diarios as $diario)
                        <tr>
                            <td class="text-nowrap">{{ $diario->data?->format('d/m/Y') }}</td>
                            <td>{{ $diario->turma->nome ?? '—' }}</td>
                            <td>{{ $diario->disciplina->nome ?? '—' }}</td>
                            <td>{{ $diario->professor->nome ?? '—' }}</td>
                            <td class="text-truncate" style="max-width:280px;" title="{{ $diario->conteudo }}">{{ \Illuminate\Support\Str::limit($diario->conteudo, 80) }}</td>
                            <td class="text-right text-nowrap">
                                @can('diario.frequencia')
                                    <a href="{{ route('diario.frequencia', $diario->id) }}" class="btn btn-xs btn-success" title="Chamada"><i class="fa fa-clipboard-check"></i></a>
                                @endcan
                                @can('diario.edit')
                                    <a href="{{ route('diario.edit', $diario->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('diario.destroy')
                                    <x-delete-button :action="route('diario.destroy', $diario->id)" titulo="Excluir" mensagem="Excluir este registro de aula? A chamada vinculada também será removida." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-chalkboard fa-2x mb-2 d-block"></i>
                                Nenhuma aula registrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($diarios->hasPages())
        <div class="card-footer">
            {{ $diarios->links() }}
        </div>
    @endif
</div>
@stop
