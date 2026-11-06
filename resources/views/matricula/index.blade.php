@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Inscrições de alunos em turmas" :create-url="auth()->user()->hasPermissao('matricula.create') ? route('matricula.create') : null" :create-can="'matricula.create'" />
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
                        <th>Nº</th>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Escola</th>
                        <th>Ano letivo</th>
                        <th>Matriculado em</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matriculas as $matricula)
                        <tr>
                            <td class="text-muted">{{ $matricula->numero_chamada ?? '—' }}</td>
                            <td>
                                <span class="font-weight-bold">{{ $matricula->aluno->pessoa->nome ?? '—' }}</span>
                                @if($matricula->aluno?->ra)
                                    <small class="text-muted d-block">RA {{ $matricula->aluno->ra }}</small>
                                @endif
                            </td>
                            <td>{{ $matricula->turma->nome ?? '—' }}</td>
                            <td>{{ $matricula->turma->escola->nome ?? '—' }}</td>
                            <td>{{ $matricula->anoLetivo->ano ?? ($matricula->turma->anoLetivo->ano ?? '—') }}</td>
                            <td>{{ $matricula->data_matricula?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @php $cor = ['ativa' => 'success', 'trancada' => 'warning', 'cancelada' => 'danger', 'concluida' => 'info'][$matricula->status] ?? 'secondary'; @endphp
                                <span class="badge badge-{{ $cor }}">{{ \App\Models\Matricula::STATUS[$matricula->status] ?? $matricula->status }}</span>
                            </td>
                            <td class="text-right">
                                @can('matricula.edit')
                                    <a href="{{ route('matricula.edit', $matricula->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('matricula.destroy')
                                    <x-delete-button :action="route('matricula.destroy', $matricula->id)" titulo="Excluir" mensagem="Excluir a matrícula de &quot;{{ $matricula->aluno->pessoa->nome ?? 'aluno' }}&quot; nesta turma?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-user-graduate fa-2x mb-2 d-block"></i>
                                Nenhuma matrícula encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($matriculas->hasPages())
        <div class="card-footer">
            {{ $matriculas->links() }}
        </div>
    @endif
</div>
@stop
