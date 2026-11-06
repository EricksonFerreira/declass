@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Componentes curriculares do tenant" :create-url="auth()->user()->hasPermissao('disciplina.create') ? route('disciplina.create') : null" :create-can="'disciplina.create'" />
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
                        <th>Sigla</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disciplinas as $disciplina)
                        <tr>
                            <td><span class="badge badge-info">{{ $disciplina->sigla ?? '—' }}</span></td>
                            <td class="font-weight-bold">{{ $disciplina->nome }}</td>
                            <td class="text-muted" style="font-size:.8rem; max-width:280px;">{{ \Illuminate\Support\Str::limit($disciplina->descricao, 80) ?: '—' }}</td>
                            <td>
                                @if($disciplina->estaAtiva())
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('disciplina.edit')
                                    <a href="{{ route('disciplina.edit', $disciplina->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('disciplina.destroy')
                                    <x-delete-button :action="route('disciplina.destroy', $disciplina->id)" titulo="Excluir" mensagem="Excluir a disciplina &quot;{{ $disciplina->nome }}&quot;? Ofertas em turmas são removidas junto." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-book fa-2x mb-2 d-block"></i>
                                Nenhuma disciplina encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($disciplinas->hasPages())
        <div class="card-footer">
            {{ $disciplinas->links() }}
        </div>
    @endif
</div>
@stop
