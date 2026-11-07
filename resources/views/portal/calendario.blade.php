@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-page-title :title="$titulo" subtitle="Veja quando há aula, feriados e eventos — sem precisar perguntar à escola" />
@stop

@section('content')
<div class="card">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('portal.calendario') }}" class="form-inline">
            @if($alunos->count() > 1)
                <select name="aluno_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    @foreach($alunos as $a)
                        <option value="{{ $a->id }}" {{ $aluno->id === $a->id ? 'selected' : '' }}>{{ $a->pessoa->nome ?? 'Aluno' }}</option>
                    @endforeach
                </select>
            @endif
            <input type="hidden" name="escopo" value="{{ $escopo }}">
            @if($escopo === 'mes')
                <a href="{{ route('portal.calendario', ['aluno_id' => $aluno->id, 'mes' => $mesAnterior]) }}" class="btn btn-outline-secondary btn-sm mr-1"><i class="fa fa-chevron-left"></i></a>
                <span class="font-weight-bold mx-2">{{ $rotuloPeriodo }}</span>
                <a href="{{ route('portal.calendario', ['aluno_id' => $aluno->id, 'mes' => $mesProximo]) }}" class="btn btn-outline-secondary btn-sm mr-2"><i class="fa fa-chevron-right"></i></a>
            @else
                <span class="font-weight-bold mr-2">{{ $rotuloPeriodo }}</span>
            @endif
            <div class="btn-group btn-group-sm ml-auto">
                <a href="{{ route('portal.calendario', ['aluno_id' => $aluno->id, 'escopo' => 'mes']) }}"
                    class="btn {{ $escopo === 'mes' ? 'btn-primary' : 'btn-outline-primary' }}">Mês</a>
                <a href="{{ route('portal.calendario', ['aluno_id' => $aluno->id, 'escopo' => 'ano']) }}"
                    class="btn {{ $escopo === 'ano' ? 'btn-primary' : 'btn-outline-primary' }}">Ano letivo</a>
            </div>
        </form>
        <div class="small text-muted mt-2">
            {{ $turma->nome }} — {{ $turma->escola->nome ?? '' }}
            <span class="ml-2">
                <span class="badge bg-success-light border">Aula</span>
                <span class="badge bg-danger-light border">Suspensas</span>
                <span class="badge bg-warning-light border">Aula + evento</span>
                <span class="badge bg-info-light border">Só evento</span>
                <span class="badge bg-light border">Sem aula</span>
            </span>
        </div>
    </div>
</div>

@if($alteracoes->isNotEmpty())
    <div class="card card-warning card-outline">
        <div class="card-header py-2"><span class="font-weight-bold"><i class="fas fa-bell"></i> Próximas alterações</span></div>
        <div class="card-body py-2">
            @foreach($alteracoes as $alt)
                <div class="small mb-1">
                    <strong>{{ $alt['data']->format('d/m') }}</strong>: {{ $rotulosStatus[$alt['status']] }}
                    — {{ $alt['feriados']->pluck('nome')->merge($alt['eventos']->pluck('titulo'))->implode(', ') }}
                </div>
            @endforeach
        </div>
    </div>
@endif

@include('calendario._grid', ['meses' => $meses])
@stop
