@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Circulação do acervo: empréstimos e devoluções" :create-url="auth()->user()->hasPermissao('emprestimo.create') ? route('emprestimo.create') : null" :create-can="'emprestimo.create'" />
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
                        <th>Livro</th>
                        <th>Empréstimo</th>
                        <th>Devolução prevista</th>
                        <th>Devolvido em</th>
                        <th>Situação</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($emprestimos as $emp)
                        @php $situacao = $emp->situacao(); @endphp
                        <tr>
                            <td class="font-weight-bold">{{ $emp->aluno->pessoa->nome ?? 'Aluno #'.$emp->aluno_id }}</td>
                            <td>
                                <div>{{ $emp->livro->titulo ?? 'Livro #'.$emp->livro_id }}</div>
                                @if($emp->livro?->autor)
                                    <div class="text-muted" style="font-size:.8rem;">{{ $emp->livro->autor }}</div>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ optional($emp->data_emprestimo)->format('d/m/Y') }}</td>
                            <td class="text-nowrap {{ $situacao === 'atrasado' ? 'text-danger font-weight-bold' : '' }}">
                                {{ optional($emp->data_devolucao_prevista)->format('d/m/Y') }}
                            </td>
                            <td class="text-nowrap">{{ optional($emp->data_devolucao)->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($situacao === 'devolvido')
                                    <span class="badge badge-success">Devolvido</span>
                                @elseif($situacao === 'atrasado')
                                    <span class="badge badge-danger">Atrasado</span>
                                @else
                                    <span class="badge badge-warning">No prazo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($emp->estaAtivo())
                                    @can('emprestimo.devolver')
                                        <form action="{{ route('emprestimo.devolver', $emp->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Confirmar devolução de &quot;{{ $emp->livro->titulo ?? 'livro' }}&quot; por {{ $emp->aluno->pessoa->nome ?? 'aluno' }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success" title="Registrar devolução"><i class="fa fa-check"></i></button>
                                        </form>
                                    @endcan
                                    @can('emprestimo.destroy')
                                        <x-delete-button :action="route('emprestimo.destroy', $emp->id)" titulo="Excluir" mensagem="Excluir este empréstimo? O livro voltará a ficar disponível." />
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-book-reader fa-2x mb-2 d-block"></i>
                                Nenhum empréstimo encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($emprestimos->hasPages())
        <div class="card-footer">
            {{ $emprestimos->links() }}
        </div>
    @endif
</div>
@stop
