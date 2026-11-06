@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Etapas de ensino do tenant" :create-url="auth()->user()->hasPermissao('etapa.create') ? route('etapa.create') : null" :create-can="'etapa.create'" />
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
                        <th>Ordem</th>
                        <th>Nome</th>
                        <th>Segmento</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($etapas as $etapa)
                        <tr>
                            <td>{{ $etapa->ordem }}</td>
                            <td class="font-weight-bold">{{ $etapa->nome }}</td>
                            <td>{{ \App\Models\Etapa::SEGMENTOS[$etapa->segmento] ?? $etapa->segmento }}</td>
                            <td class="text-muted" style="font-size:.8rem; max-width:280px;">{{ \Illuminate\Support\Str::limit($etapa->descricao, 80) ?: '—' }}</td>
                            <td>
                                @if($etapa->estaAtiva())
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('etapa.edit')
                                    <a href="{{ route('etapa.edit', $etapa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('etapa.destroy')
                                    <x-delete-button :action="route('etapa.destroy', $etapa->id)" titulo="Excluir" mensagem="Excluir a etapa &quot;{{ $etapa->nome }}&quot;? Turmas vinculadas impedem a exclusão." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-layer-group fa-2x mb-2 d-block"></i>
                                Nenhuma etapa encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($etapas->hasPages())
        <div class="card-footer">
            {{ $etapas->links() }}
        </div>
    @endif
</div>
@stop
