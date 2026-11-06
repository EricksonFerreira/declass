@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-page-title :title="$titulo" subtitle="Tarefas das turmas dos seus alunos" />
@stop

@section('content')
@forelse($tarefas as $tarefa)
    @php
        $turmaMatriculas = $matriculas->where('turma_id', $tarefa->turma_id);
    @endphp
    <div class="card">
        <div class="card-header py-2 d-flex flex-wrap align-items-center" style="gap:.5rem;">
            <div class="flex-grow-1">
                <strong>{{ $tarefa->titulo }}</strong>
                <span class="badge badge-secondary ml-1">{{ $tarefa->turma->nome ?? '' }}</span>
                @if($tarefa->disciplina)
                    <span class="badge badge-light border ml-1">{{ $tarefa->disciplina->nome }}</span>
                @endif
            </div>
            <small class="text-muted">
                Entrega até <strong>{{ $tarefa->data_entrega->format('d/m/Y') }}</strong>
                @if($tarefa->atraso()) <span class="badge badge-danger">vencida</span> @endif
            </small>
        </div>
        <div class="card-body py-2">
            @if($tarefa->descricao)
                <p class="mb-1"><small>{{ $tarefa->descricao }}</small></p>
            @endif
            @if($tarefa->instrucoes)
                <p class="mb-2"><small class="text-muted"><i class="fas fa-info-circle"></i> {{ $tarefa->instrucoes }}</small></p>
            @endif

            <table class="table table-sm mb-0">
                <tbody>
                    @foreach($turmaMatriculas as $matricula)
                        @php
                            $entrega = $entregasPorAluno->get($tarefa->id.'|'.$matricula->id);
                            $situacao = $entrega?->situacao() ?? 'sem_controle';
                            $cores = [
                                'avaliada' => 'success',
                                'entregue' => 'info',
                                'atrasada' => 'danger',
                                'pendente' => 'warning',
                            ];
                            $labels = [
                                'avaliada' => 'Avaliada',
                                'entregue' => 'Entregue',
                                'atrasada' => 'Atrasada',
                                'pendente' => 'Pendente',
                            ];
                            $cor = $cores[$situacao] ?? 'secondary';
                            $label = $labels[$situacao] ?? 'Sem controle';
                            if ($situacao === 'avaliada' && $entrega?->nota !== null) {
                                $label .= ' — '.$entrega->nota;
                            }
                        @endphp
                        <tr>
                            <td style="width:200px;">{{ $matricula->aluno->pessoa->nome ?? 'Aluno' }}</td>
                            <td><span class="badge badge-{{ $cor }}">{{ $label }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-tasks fa-2x mb-2 d-block"></i>
            Nenhuma tarefa pendente nas turmas dos seus alunos.
        </div>
    </div>
@endforelse

<div class="mt-2">
    <a href="{{ route('portal.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
</div>
@stop
