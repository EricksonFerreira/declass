@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :subtitle="$isResponsavel ? 'Atividades aguardando entrega dos seus alunos' : 'Atividades aguardando sua entrega'" />
@stop

@section('content')
@php
    $corTipo = ['prova' => 'danger', 'trabalho' => 'warning', 'atividade' => 'info'];
    $vencidas = $tarefas->filter->atraso()->count();
    $proximas = $tarefas->filter(fn ($t) => ! $t->atraso() && $t->data_entrega->lte(now()->addWeek()->endOfDay()))->count();
@endphp

<x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />

@if($tarefas->isNotEmpty())
    <div class="row">
        <div class="col-md-4 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $tarefas->count() }}</h3>
                    <p>{{ $tarefas->count() === 1 ? 'Tarefa aguardando entrega' : 'Tarefas aguardando entrega' }}</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $proximas }}</h3>
                    <p>{{ $proximas === 1 ? 'Entrega' : 'Entregas' }} nos próximos 7 dias</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $vencidas }}</h3>
                    <p>{{ $vencidas === 1 ? 'Vencida' : 'Vencidas' }}</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>
@endif

@forelse($tarefas as $tarefa)
    @php
        $turmaMatriculas = $matriculas->where('turma_id', $tarefa->turma_id);
        $primeiraMatricula = $turmaMatriculas->first();
        $corTarefa = $corTipo[$tarefa->tipo] ?? 'info';
        $vencida = $tarefa->atraso();
    @endphp
    <div class="card card-outline card-{{ $vencida ? 'danger' : $corTarefa }}">
        <div class="card-header py-2 d-flex flex-wrap align-items-center" style="gap:.75rem;">
            <div class="tarefa-data flex-shrink-0 text-center rounded {{ $vencida ? 'bg-danger' : 'bg-'.$corTarefa }}">
                <span class="tarefa-data-dia d-block">{{ $tarefa->data_entrega->format('d') }}</span>
                <span class="tarefa-data-mes d-block">{{ $tarefa->data_entrega->translatedFormat('M') }}</span>
            </div>
            <div class="flex-grow-1" style="min-width:200px;">
                <a href="{{ route('portal.turma.tarefa', [$tarefa->turma_id, $tarefa->id, 'matricula_id' => $primeiraMatricula->id]) }}" class="text-dark">
                    <strong>{{ $tarefa->titulo }}</strong>
                </a>
                <div>
                    <span class="badge badge-{{ $corTarefa }}">{{ $tarefa->tipoLabel() }}</span>
                    <span class="badge badge-secondary">{{ $tarefa->turma->nome ?? '' }}</span>
                    @if($tarefa->disciplina)
                        <span class="badge badge-light border">{{ $tarefa->disciplina->nome }}</span>
                    @endif
                </div>
            </div>
            <div class="text-right text-nowrap">
                <small class="text-muted d-block mb-1">
                    Entrega até <strong>{{ $tarefa->data_entrega->format('d/m/Y') }}</strong>
                </small>
                @if($vencida)
                    <span class="badge badge-danger">vencida</span>
                @elseif($tarefa->data_entrega->isToday())
                    <span class="badge badge-warning">entrega hoje</span>
                @elseif($tarefa->data_entrega->isTomorrow())
                    <span class="badge badge-warning">entrega amanhã</span>
                @endif
            </div>
        </div>
        @if($tarefa->descricao || $tarefa->instrucoes)
            <div class="card-body py-2">
                @if($tarefa->descricao)
                    <p class="mb-1"><small>{{ $tarefa->descricao }}</small></p>
                @endif
                @if($tarefa->instrucoes)
                    <p class="mb-0"><small class="text-muted"><i class="fas fa-info-circle"></i> {{ $tarefa->instrucoes }}</small></p>
                @endif
            </div>
        @endif
        <ul class="list-group list-group-flush">
            @foreach($turmaMatriculas as $matricula)
                @php
                    $entrega = $entregasPorAluno->get($tarefa->id.'|'.$matricula->id);
                    $situacao = $entrega?->situacao() ?? 'sem_controle';
                    $enviada = $entrega && in_array($entrega->status, ['entregue', 'avaliada']);
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
                    $nomeAluno = $matricula->aluno->pessoa->nome ?? 'Aluno';
                    $iniciais = collect(explode(' ', $nomeAluno))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
                @endphp
                <li class="list-group-item d-flex flex-wrap align-items-center py-2" style="gap:.5rem;">
                    @if($isResponsavel)
                        <span class="avatar-iniciais bg-light border text-secondary">{{ mb_strtoupper($iniciais) }}</span>
                        <span class="flex-grow-1" style="min-width:140px;">{{ $nomeAluno }}</span>
                    @endif
                    <span class="badge badge-{{ $cor }}">{{ $label }}</span>
                    <span class="text-nowrap ml-auto">
                        @if(! $isResponsavel && ! $enviada && $tarefa->tipo_entrega === 'leitura')
                            <form method="POST" class="d-inline"
                                  action="{{ route('portal.turma.tarefa.responder', [$tarefa->turma_id, $tarefa->id]) }}">
                                @csrf
                                <input type="hidden" name="modo" value="entrega">
                                <input type="hidden" name="matricula_id" value="{{ $matricula->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Marcar como vista">
                                    <i class="fas fa-check"></i> Li e entendi
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('portal.turma.tarefa', [$tarefa->turma_id, $tarefa->id, 'matricula_id' => $matricula->id]) }}"
                           class="btn {{ $enviada || $isResponsavel ? 'btn-xs btn-outline-secondary' : 'btn-primary' }}">
                            <i class="fas {{ $enviada || $isResponsavel ? 'fa-eye' : 'fa-pen' }}"></i> {{ $enviada || $isResponsavel ? 'Ver' : 'Fazer' }}
                        </a>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-tasks fa-3x mb-3 d-block"></i>
            {{ $isResponsavel ? 'Nenhuma tarefa pendente nas turmas dos seus alunos.' : 'Nenhuma tarefa pendente nas suas turmas.' }}
        </div>
    </div>
@endforelse

<div class="mt-2">
    <a href="{{ route('portal.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Voltar ao portal</a>
</div>

<style>
    .tarefa-data { width: 48px; padding: 6px 0; color: #fff; line-height: 1.1; }
    .tarefa-data-dia { font-size: 1.15rem; font-weight: 700; }
    .tarefa-data-mes { font-size: .7rem; text-transform: uppercase; }
    .avatar-iniciais { display: inline-flex; width: 34px; height: 34px; border-radius: 50%; align-items: center; justify-content: center; font-size: .8rem; font-weight: 600; flex-shrink: 0; }
</style>
@stop
