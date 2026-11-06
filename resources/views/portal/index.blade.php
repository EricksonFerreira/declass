@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-page-title :title="$titulo" subtitle="Acompanhe o desempenho dos alunos vinculados à sua conta" />
@stop

@section('content')
<div class="row">
    @foreach($alunos as $aluno)
        @php $matricula = $matriculas->get($aluno->id); @endphp
        <div class="col-md-4 col-sm-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">{{ $aluno->pessoa->nome ?? $aluno->nome }}</h3>
                </div>
                <div class="card-body">
                    @if($matricula?->turma)
                        <p class="mb-1"><strong>Turma:</strong> {{ $matricula->turma->nome }}</p>
                        <p class="mb-1"><strong>Ano letivo:</strong> {{ $matricula->turma->anoLetivo->ano ?? '—' }}</p>
                        <p class="mb-0"><strong>Etapa:</strong> {{ $matricula->turma->etapa->nome ?? '—' }}</p>
                    @else
                        <p class="text-muted mb-0">Sem matrícula ativa no momento.</p>
                    @endif
                </div>
                <div class="card-footer d-flex" style="gap:.4rem;">
                    @if($matricula)
                        <a href="{{ route('portal.aluno', $aluno->id) }}" class="btn btn-primary btn-sm">Ver boletim</a>
                    @endif
                    <a href="{{ route('portal.financeiro') }}" class="btn btn-outline-secondary btn-sm">Financeiro</a>
                    <a href="{{ route('portal.transporte') }}" class="btn btn-outline-dark btn-sm">Transporte</a>
                    <a href="{{ route('portal.avisos') }}" class="btn btn-outline-info btn-sm">Avisos</a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@stop
