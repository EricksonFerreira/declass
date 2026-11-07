@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Calendário Letivo" subtitle="Dia a dia da turma: aulas, feriados e eventos — saiba quando há aula" />
@stop

@section('content')
@php
    $linkMes = fn (?string $mes) => route('calendario.index', array_merge(request()->except('mes'), $mes ? ['mes' => $mes] : []));
    $mesHoje = now()->format('Y-m');
@endphp
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('calendario.index') }}" id="form-calendario">
            <div class="form-row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Turma</label>
                    <select name="turma_id" class="form-control" onchange="this.form.submit()">
                        @foreach($turmas as $id => $label)
                            <option value="{{ $id }}" {{ $turma && $turma->id === $id ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Escopo</label>
                    <select name="escopo" class="form-control" onchange="this.form.submit()">
                        <option value="ano" {{ $escopo === 'ano' ? 'selected' : '' }}>Ano letivo completo</option>
                        <option value="sem1" {{ $escopo === 'sem1' ? 'selected' : '' }}>1º semestre</option>
                        <option value="sem2" {{ $escopo === 'sem2' ? 'selected' : '' }}>2º semestre</option>
                        <option value="periodo" {{ $escopo === 'periodo' ? 'selected' : '' }}>Período letivo (bimestre…)</option>
                    </select>
                </div>
                @if($escopo === 'periodo' && $turma)
                    <div class="col-md-3">
                        <label class="form-label">Período letivo</label>
                        <select name="periodo_letivo_id" class="form-control" onchange="this.form.submit()">
                            @foreach($turma->anoLetivo?->periodos ?? collect() as $p)
                                <option value="{{ $p->id }}" {{ $periodoLetivoId === $p->id ? 'selected' : '' }}>
                                    {{ $p->nome }} ({{ $p->data_inicio->format('d/m') }}–{{ $p->data_fim->format('d/m') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
                </div>
            </div>

            @if($turma && count($mesesDisponiveis))
                <div class="form-row align-items-end mt-2 pt-2 border-top">
                    <div class="col-auto">
                        @if($mesAnterior)
                            <a class="btn btn-sm btn-outline-secondary" href="{{ $linkMes($mesAnterior) }}" title="{{ $mesesDisponiveis[$mesAnterior] }}">
                                <i class="fa fa-chevron-left"></i> Anterior
                            </a>
                        @else
                            <span class="btn btn-sm btn-outline-secondary disabled"><i class="fa fa-chevron-left"></i> Anterior</span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-0">Mês</label>
                        <select name="mes" class="form-control form-control-sm" onchange="this.form.submit()">
                            @foreach($mesesDisponiveis as $chaveMes => $rotuloMes)
                                <option value="{{ $chaveMes }}" {{ $mesSelecionado === $chaveMes ? 'selected' : '' }}>{{ $rotuloMes }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        @if($proximoMes)
                            <a class="btn btn-sm btn-outline-secondary" href="{{ $linkMes($proximoMes) }}" title="{{ $mesesDisponiveis[$proximoMes] }}">
                                Próximo <i class="fa fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="btn btn-sm btn-outline-secondary disabled">Próximo <i class="fa fa-chevron-right"></i></span>
                        @endif
                    </div>
                    @if(isset($mesesDisponiveis[$mesHoje]))
                        <div class="col-auto">
                            <a class="btn btn-sm btn-outline-primary" href="{{ $linkMes($mesHoje) }}">
                                <i class="fa fa-calendar-day"></i> Hoje
                            </a>
                        </div>
                    @endif
                    @if($aulasProfessor->isNotEmpty())
                        <div class="col-auto form-check mb-1 ml-auto">
                            <input type="checkbox" class="form-check-input" id="minhas_aulas" name="minhas_aulas" value="1"
                                {{ $somenteMinhas ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small" for="minhas_aulas"
                                title="Destaca os dias em que você tem aula nesta turma">Só dias que leciono</label>
                        </div>
                    @endif
                </div>
            @endif
        </form>
    </div>
</div>

@if(!$turma)
    <div class="card"><div class="card-body text-center text-muted py-4">
        @if($turmas->isEmpty())
            Nenhuma turma disponível para você. Professores veem apenas turmas com atribuição ativa.
        @else
            Selecione uma turma para ver o calendário.
        @endif
    </div></div>
@else
    <div class="card">
        <div class="card-body py-2">
            <span class="font-weight-bold mr-3">
                {{ $turma->nome }} — {{ $turma->horario->nome ?? 'matriz padrão do turno' }}
            </span>
            <span class="text-muted small mr-3">{{ $rotuloPeriodo }}: {{ $inicio->format('d/m/Y') }} a {{ $fim->format('d/m/Y') }}</span>
            <span class="badge bg-success-light border mr-1">Aula</span>
            <span class="badge bg-danger-light border mr-1">Suspenso</span>
            <span class="badge bg-warning-light border mr-1">Aula + evento</span>
            <span class="badge bg-info-light border mr-1">Só evento</span>
            <span class="badge bg-light border">Sem aula</span>
            @if($aulasProfessor->isNotEmpty())
                <span class="badge bg-success-light border mr-1" title="Dias em que você tem aula nesta turma"><i class="fas fa-chalkboard-teacher"></i> Você leciona</span>
            @endif
        </div>
    </div>

    @if($alteracoes->isNotEmpty())
        <div class="card card-warning card-outline">
            <div class="card-header py-2"><span class="font-weight-bold"><i class="fas fa-bell"></i> Próximas alterações na rotina</span></div>
            <div class="card-body py-2">
                @foreach($alteracoes as $alt)
                    <div class="small mb-1">
                        <strong>{{ $alt['data']->format('d/m') }}</strong>
                        ({{ ['1'=>'seg','2'=>'ter','3'=>'qua','4'=>'qui','5'=>'sex','6'=>'sáb','7'=>'dom'][$alt['data']->dayOfWeekIso] }}):
                        {{ $rotulosStatus[$alt['status']] }}
                        — {{ $alt['feriados']->pluck('nome')->merge($alt['eventos']->pluck('titulo'))->implode(', ') }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @include('calendario._grid', [
        'meses' => $mesExibido ? [$mesExibido] : [],
        'diasProfessor' => $diasProfessor,
        'aulasProfessor' => $aulasProfessor,
        'somenteMinhas' => $somenteMinhas,
    ])
@endif
@stop
