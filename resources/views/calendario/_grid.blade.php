@php
    // Espera: $meses (de CalendarioLetivoService::mesesDoCalendario), $rotulosStatus
    // Opcionais (visao do professor no admin): $diasProfessor, $aulasProfessor, $somenteMinhas
    $diasSemana = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
    $cores = [
        'nao_letivo' => 'bg-light text-muted',
        'aula' => 'bg-success-light',
        'sem_aula' => 'bg-danger-light',
        'evento' => 'bg-info-light',
        'aula_evento' => 'bg-warning-light',
    ];
    $diasProfessor = $diasProfessor ?? [];
    $aulasProfessor = $aulasProfessor ?? collect();
    $somenteMinhas = $somenteMinhas ?? false;
@endphp

@foreach($meses as $mes)
    <div class="card card-outline card-primary">
        <div class="card-header py-2"><span class="font-weight-bold">{{ $mes['rotulo'] }}</span></div>
        <div class="card-body p-1">
            <table class="table table-bordered table-sm mb-0 calendario-grid">
                <thead><tr>
                    @foreach($diasSemana as $ds)
                        <th class="text-center small">{{ $ds }}</th>
                    @endforeach
                </tr></thead>
                <tbody>
                    @foreach($mes['semanas'] as $semana)
                        <tr>
                            @foreach($semana as $dia)
                                @if($dia === null)
                                    <td class="bg-light"></td>
                                @else
                                    @php
                                        $diaProfessor = $dia['letivo'] && in_array($dia['data']->dayOfWeekIso, $diasProfessor);
                                        $classesDia = $cores[$dia['status']] ?? '';
                                        if ($diaProfessor) {
                                            $classesDia .= ' cal-prof';
                                        }
                                        if ($somenteMinhas && $dia['letivo'] && ! $diaProfessor) {
                                            $classesDia .= ' cal-dim';
                                        }
                                    @endphp
                                    <td class="{{ $classesDia }} cal-dia" style="width:14.28%; vertical-align:top;"
                                        @if($diaProfessor) title="Você leciona neste dia" @endif>
                                        <div class="d-flex justify-content-between">
                                            <span class="font-weight-bold small">{{ $dia['data']->format('d') }}</span>
                                            @if($diaProfessor)
                                                <i class="fas fa-chalkboard-teacher text-success" title="Você leciona"></i>
                                            @endif
                                            @if($dia['letivo'] && $dia['status'] === 'sem_aula')
                                                <i class="fas fa-ban text-danger" title="Aulas suspensas"></i>
                                            @elseif($dia['status'] === 'aula_evento')
                                                <i class="fas fa-star text-warning" title="Aula + evento"></i>
                                            @endif
                                        </div>
                                        @foreach(($aulasProfessor[$dia['data']->dayOfWeekIso] ?? collect()) as $aulaProf)
                                            <div class="cal-item text-success font-weight-bold" title="{{ $aulaProf['disciplina'] }}{{ $aulaProf['inicio'] ? ' — '.$aulaProf['inicio'].($aulaProf['fim'] ? ' às '.$aulaProf['fim'] : '') : '' }}">
                                                <i class="fas fa-chalkboard-teacher"></i> {{ $aulaProf['disciplina'] }}{{ $aulaProf['inicio'] ? ' '.$aulaProf['inicio'] : '' }}
                                            </div>
                                        @endforeach
                                        @foreach($dia['feriados'] as $f)
                                            <div class="cal-item text-danger" title="{{ $f->nome }}{{ $f->suspende_aulas === 'S' ? ' (sem aula)' : ' (aula normal)' }}">
                                                <i class="fas fa-umbrella-beach"></i> {{ \Illuminate\Support\Str::limit($f->nome, 22) }}
                                            </div>
                                        @endforeach
                                        @foreach($dia['eventos'] as $e)
                                            <div class="cal-item text-info" title="{{ $e->titulo }}{{ $e->suspende_aulas === 'S' ? ' (sem aula)' : '' }}">
                                                <i class="fas fa-calendar-check"></i> {{ \Illuminate\Support\Str::limit($e->titulo, 22) }}
                                            </div>
                                        @endforeach
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

@if(empty($meses))
    <div class="alert alert-secondary">Nenhum dia no período selecionado.</div>
@endif

@once
    @push('css')
    <style>
        .bg-success-light { background-color: #d4edda; }
        .bg-danger-light { background-color: #f8d7da; }
        .bg-info-light { background-color: #d1ecf1; }
        .bg-warning-light { background-color: #fff3cd; }
        .calendario-grid td.cal-dia { min-height: 64px; font-size: 12px; padding: 4px 6px !important; }
        .calendario-grid td.cal-prof { box-shadow: inset 3px 0 0 #28a745; }
        .calendario-grid td.cal-dim { opacity: .4; }
        .cal-item { font-size: 11px; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
    @endpush
@endonce
