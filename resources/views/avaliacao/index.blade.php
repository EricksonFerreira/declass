@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Instrumentos de avaliação por turma e disciplina" :create-url="auth()->user()->hasPermissao('avaliacao.create') ? route('avaliacao.create') : null" :create-can="'avaliacao.create'" />
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
                        <th>Turma</th>
                        <th>Disciplina</th>
                        <th>Período</th>
                        <th>Data</th>
                        <th>Nota máx.</th>
                        <th>Professor</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($avaliacoes as $avaliacao)
                        <tr>
                            <td class="font-weight-bold">{{ $avaliacao->nome }}</td>
                            <td><span class="badge badge-info">{{ \App\Models\Avaliacao::TIPOS[$avaliacao->tipo] ?? $avaliacao->tipo }}</span></td>
                            <td>{{ $avaliacao->turma->nome ?? '—' }}</td>
                            <td>{{ $avaliacao->disciplina->nome ?? '—' }}</td>
                            <td>{{ $avaliacao->periodo ?? '—' }}</td>
                            <td>{{ $avaliacao->data?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ number_format($avaliacao->nota_maxima, 1, ',', '.') }}</td>
                            <td>{{ $avaliacao->professor->nome ?? '—' }}</td>
                            <td class="text-right text-nowrap">
                                @can('avaliacao.notas')
                                    <a href="{{ route('avaliacao.notas', $avaliacao->id) }}" class="btn btn-xs btn-success" title="Lançar notas"><i class="fa fa-pencil-alt"></i></a>
                                @endcan
                                @can('avaliacao.edit')
                                    <a href="{{ route('avaliacao.edit', $avaliacao->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('avaliacao.destroy')
                                    <x-delete-button :action="route('avaliacao.destroy', $avaliacao->id)" titulo="Excluir" mensagem="Excluir a avaliação &quot;{{ $avaliacao->nome }}&quot;? As notas lançadas também serão removidas." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-check fa-2x mb-2 d-block"></i>
                                Nenhuma avaliação encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($avaliacoes->hasPages())
        <div class="card-footer">
            {{ $avaliacoes->links() }}
        </div>
    @endif
</div>
@stop
