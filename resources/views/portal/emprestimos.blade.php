@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-page-title :title="$titulo" subtitle="Livros emprestados pela biblioteca" />
@stop

@section('content')
@forelse($alunos as $aluno)
    @php
        $lista = $emprestimos->get($aluno->id, collect());
    @endphp
    <div class="card">
        <div class="card-header py-2">
            <strong>{{ $aluno->pessoa->nome ?? 'Aluno' }}</strong>
            <span class="badge badge-secondary ml-1">{{ $lista->count() }} registro(s)</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Livro</th>
                        <th>Empréstimo</th>
                        <th>Devolução prevista</th>
                        <th>Devolvido em</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lista as $emp)
                        @php
                            $sit = $emp->situacao();
                            $cor = ['ativo' => 'info', 'atrasado' => 'danger', 'devolvido' => 'success'][$sit];
                            $label = ['ativo' => 'No prazo', 'atrasado' => 'Atrasado', 'devolvido' => 'Devolvido'][$sit];
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $emp->livro->titulo ?? 'Livro removido' }}</strong><br>
                                <small class="text-muted">{{ $emp->livro->autor ?? '' }}</small>
                            </td>
                            <td class="text-nowrap">{{ $emp->data_emprestimo?->format('d/m/Y') }}</td>
                            <td class="text-nowrap">{{ $emp->data_devolucao_prevista?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-nowrap">{{ $emp->data_devolucao?->format('d/m/Y') ?? '—' }}</td>
                            <td><span class="badge badge-{{ $cor }}">{{ $label }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Nenhum empréstimo registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-book-open fa-2x mb-2 d-block"></i>
            Nenhum aluno vinculado a esta conta.
        </div>
    </div>
@endforelse

<div class="mt-2">
    <a href="{{ route('portal.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
</div>
@stop
