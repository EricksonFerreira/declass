@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Acervo de livros das escolas" :create-url="auth()->user()->hasPermissao('livro.create') ? route('livro.create') : null" :create-can="'livro.create'" />
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
                        <th>Autor</th>
                        <th>Categoria</th>
                        <th>Escola</th>
                        <th class="text-center">Exemplares</th>
                        <th class="text-center">Disponíveis</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($livros as $livro)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $livro->titulo }}</div>
                                @if($livro->editora)
                                    <div class="text-muted" style="font-size:.8rem;">{{ $livro->editora }}{{ $livro->ano_publicacao ? ' · '.$livro->ano_publicacao : '' }}</div>
                                @endif
                            </td>
                            <td>{{ $livro->autor ?? '—' }}</td>
                            <td><span class="badge badge-light border">{{ \App\Models\Livro::CATEGORIAS[$livro->categoria] ?? $livro->categoria }}</span></td>
                            <td>{{ $livro->escola->nome ?? '—' }}</td>
                            <td class="text-center">{{ $livro->exemplares }}</td>
                            <td class="text-center">
                                @php $disp = $livro->disponiveis(); @endphp
                                @if($disp > 0)
                                    <span class="badge badge-success">{{ $disp }}</span>
                                @else
                                    <span class="badge badge-danger">0</span>
                                @endif
                            </td>
                            <td>
                                @if($livro->estaAtivo())
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('livro.edit')
                                    <a href="{{ route('livro.edit', $livro->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('livro.destroy')
                                    <x-delete-button :action="route('livro.destroy', $livro->id)" titulo="Excluir" mensagem="Excluir o livro &quot;{{ $livro->titulo }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-book fa-2x mb-2 d-block"></i>
                                Nenhum livro cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($livros->hasPages())
        <div class="card-footer">
            {{ $livros->links() }}
        </div>
    @endif
</div>
@stop
