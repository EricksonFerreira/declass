@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Redes educacionais e mantenedoras" :create-url="auth()->user()->hasPermissao('organizacao.create') ? route('organizacao.create') : null" :create-can="'organizacao.create'" />
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
                        <th>Tipo</th>
                        <th>CNPJ</th>
                        <th>Escolas</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizacoes as $organizacao)
                        <tr>
                            <td><a href="{{ route('organizacao.show', $organizacao->id) }}" class="font-weight-bold">{{ $organizacao->nome }}</a></td>
                            <td>{{ \App\Models\Organizacao::TIPOS[$organizacao->tipo] ?? $organizacao->tipo }}</td>
                            <td>{{ $organizacao->cnpj ?? '—' }}</td>
                            <td>{{ $organizacao->escolas->count() }}</td>
                            <td>
                                @if($organizacao->estaAtiva())
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('organizacao.show', $organizacao->id) }}" class="btn btn-xs btn-info" title="Ver"><i class="fa fa-eye"></i></a>
                                @can('organizacao.edit')
                                    <a href="{{ route('organizacao.edit', $organizacao->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('organizacao.destroy')
                                    <x-delete-button :action="route('organizacao.destroy', $organizacao->id)" titulo="Excluir" mensagem="Excluir a organização &quot;{{ $organizacao->nome }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-sitemap fa-2x mb-2 d-block"></i>
                                Nenhuma organização encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($organizacoes->hasPages())
        <div class="card-footer">
            {{ $organizacoes->links() }}
        </div>
    @endif
</div>
@stop
