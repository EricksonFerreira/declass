@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Grade semanal de aulas da turma — disciplina, professor e sala" />
@stop

@section('content')
<x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />

<style>
    .grade-horarios .intervalo-cell { background: #f1f5f9; }
    .grade-horarios .intervalo-tag {
        display: inline-block;
        font-size: .68rem;
        font-style: italic;
        color: #64748b;
        border: 1px dashed #cbd5e1;
        border-radius: .8rem;
        padding: .05rem .5rem;
        background: #fff;
    }
    .grade-horarios .celula-aula { min-height: 52px; border-radius: 3px; padding: 4px 5px; }
    .grade-horarios .celula-aula.vazia { border: 1px dashed #cbd5e1; background: #f8fafc; }
</style>

@forelse($grades as $grade)
    @php
        $turma = $grade['turma'];
        $dias = $grade['dias'];
        $ordens = $grade['ordens'];
        $slots = $grade['slots'];
        $aulas = $grade['aulas'];
        $intervalos = $grade['intervalos'];
    @endphp
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold">
                <i class="fas fa-calendar-week"></i> {{ $turma->nome }}
                <small class="text-muted font-weight-normal">
                    — {{ $grade['escola']->nome ?? '' }}
                    · {{ \App\Models\Turma::TURNOS[$grade['turno'] ?? ''] ?? $grade['turno'] }}
                    · {{ $turma->anoLetivo->ano ?? '' }}
                </small>
            </span>
        </div>
        <div class="card-body p-0">
            @if(!$grade['temMatriz'])
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clock fa-3x mb-3 d-block"></i>
                    Esta turma ainda não tem uma grade de horário configurada.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 grade-horarios">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:110px">Horário</th>
                                @foreach($dias as $dia)
                                    <th class="text-center">{{ $diasLabels[$dia] ?? $dia }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordens as $ordem)
                                <tr>
                                    <td class="text-center font-weight-bold bg-light">
                                        {{ $ordem }}ª aula
                                        @php
                                            $horarioRef = '';
                                            foreach ($dias as $d) {
                                                $s = $slots->get($d)?->get($ordem);
                                                if ($s) { $horarioRef = substr((string)$s->hora_inicio,0,5).'–'.substr((string)$s->hora_fim,0,5); break; }
                                            }
                                        @endphp
                                        <br><small class="text-muted font-weight-normal">{{ $horarioRef }}</small>
                                    </td>
                                    @foreach($dias as $dia)
                                        @php
                                            $slot = $slots->get($dia)?->get($ordem);
                                            $aula = $aulas->get($dia.'-'.$ordem);
                                            $cor = $aula ? \App\Services\TurmaGradeService::corDaDisciplina($aula->disciplina_id) : null;
                                        @endphp
                                        <td class="p-1 text-center"
                                            @if(!$slot) style="background:#f8fafc;" @endif>
                                            @if($slot)
                                                @if($aula)
                                                    <a href="{{ route('portal.turma.disciplina', [$turma->id, $aula->disciplina_id]) }}" class="d-block text-decoration-none">
                                                @endif
                                                <div class="celula-aula {{ $aula ? '' : 'vazia' }}"
                                                     @if($aula) style="background:{{ $cor['bg'] }};border-left:4px solid {{ $cor['tx'] }};"
                                                     title="{{ $aula->disciplina?->nome }}{{ $aula->user?->nome ? ' — '.$aula->user->nome : '' }}" @endif>
                                                    @if($aula)
                                                        <div class="font-weight-bold" style="color:{{ $cor['tx'] }};font-size:.82rem;">
                                                            {{ $aula->disciplina?->sigla ?: $aula->disciplina?->nome }}
                                                        </div>
                                                        <div style="font-size:.72rem;color:{{ $cor['tx'] }};">
                                                            {{ $aula->user?->nome ? \Illuminate\Support\Str::limit($aula->user->nome, 18) : '' }}
                                                        </div>
                                                        @if($aula->sala)<div style="font-size:.68rem;color:#64748b;"><i class="fa fa-door-open"></i> {{ $aula->sala }}</div>@endif
                                                    @endif
                                                </div>
                                                @if($aula)
                                                    </a>
                                                @endif
                                            @else
                                                <span class="text-muted" style="font-size:.75rem;">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @php
                                    $temIntervalo = collect($intervalos)->contains(fn ($porDia) => isset($porDia[$ordem]));
                                    $ivPrimeiro = $temIntervalo ? collect($intervalos)->map(fn ($porDia) => $porDia[$ordem] ?? null)->filter()->first() : null;
                                @endphp
                                @if($temIntervalo)
                                    <tr>
                                        <td class="text-center font-weight-bold bg-light" style="font-size:.8rem;">
                                            <i class="fa fa-coffee text-muted"></i> Intervalo
                                            @if($ivPrimeiro)<br><small class="text-muted font-weight-normal">{{ $ivPrimeiro['inicio'] }}–{{ $ivPrimeiro['fim'] }}</small>@endif
                                        </td>
                                        @foreach($dias as $dia)
                                            <td class="text-center intervalo-cell">
                                                @if(isset($intervalos[$dia][$ordem]))<span class="intervalo-tag">Intervalo</span>@endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($aulas->isEmpty())
                    <div class="card-footer text-muted" style="font-size:.78rem;">
                        <i class="fas fa-info-circle"></i>
                        A grade de horários existe, mas as aulas ainda não foram posicionadas pela escola.
                    </div>
                @else
                    <div class="p-2 border-top d-flex flex-wrap align-items-center" style="gap:.75rem; font-size:.8rem;">
                        <span class="text-muted font-weight-bold"><i class="fas fa-palette"></i> Legenda:</span>
                        @foreach($aulas->pluck('disciplina')->filter()->unique('id') as $disc)
                            @php $corLeg = \App\Services\TurmaGradeService::corDaDisciplina($disc->id); @endphp
                            <a href="{{ route('portal.turma.disciplina', [$turma->id, $disc->id]) }}" class="px-2 py-1 rounded" style="background:{{ $corLeg['bg'] }}; color:{{ $corLeg['tx'] }};">{{ $disc->nome }}</a>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-user-graduate fa-3x mb-3 d-block"></i>
            {{ $aluno->pessoa->nome ?? 'Este aluno' }} não tem matrícula ativa em uma turma.
        </div>
    </div>
@endforelse
@stop
