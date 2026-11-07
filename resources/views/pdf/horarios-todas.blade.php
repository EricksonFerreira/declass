<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Grades horárias</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
    h1 { font-size: 15px; margin: 0 0 2px; }
    .sub { color: #555; font-size: 9px; margin-bottom: 6px; }
    .turma { page-break-inside: avoid; margin-bottom: 18px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 3px 4px; text-align: center; vertical-align: middle; }
    th { background: #eef2f7; font-size: 8.5px; }
    td.horario { background: #f8fafc; font-weight: bold; font-size: 8px; white-space: nowrap; }
    .aula-box { border-radius: 2px; padding: 2px 3px; }
    .disc { font-weight: bold; font-size: 9px; }
    .prof { font-size: 7.5px; }
    .sala { font-size: 7px; color: #64748b; }
    .vazia { background: #f8fafc; color: #94a3b8; }
    td.intervalo { background: #f1f5f9; color: #64748b; font-size: 7.5px; font-style: italic; }
    h2 { font-size: 11px; margin: 10px 0 4px; border-bottom: 1px solid #ccc; padding-bottom: 2px; }
</style>
</head>
<body>
    <h1>Grades horárias — todas as turmas</h1>
    <div class="sub">
        {{ $comProfessor ? 'Exibição: disciplina + professor' : 'Exibição: somente disciplinas' }}
        · Gerado em {{ now()->format('d/m/Y H:i') }}
    </div>

    @foreach($turmas as $item)
        @php
            $turma = $item['turma'];
            $export = $item['export'];
            $grade = $export['grade'];
            $intervalos = $export['intervalos'];
            $diasGrade = array_keys($grade);
            $ordens = [];
            foreach ($grade as $aulas) { foreach ($aulas as $o => $a) { $ordens[$o] = true; } }
            $ordens = array_keys($ordens); sort($ordens);
        @endphp
        <div class="turma">
            <h2>{{ $turma->nome }} <small style="color:#666;font-weight:normal">— {{ $turma->escola->nome ?? '' }} · {{ $turma->anoLetivo->ano ?? '' }} · {{ \App\Models\Turma::TURNOS[$turma->turno ?? ''] ?? '' }}</small></h2>
            @if(empty($grade))
                <p style="color:#888">Sem grade configurada para esta turma.</p>
            @else
            <table>
                <thead>
                    <tr>
                        <th style="width:80px">Horário</th>
                        @foreach($diasGrade as $dia)
                            <th>{{ $dias[$dia] ?? $dia }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($ordens as $ordem)
                        <tr>
                            <td class="horario">
                                {{ $ordem }}ª aula
                                @php
                                    $h = '';
                                    foreach ($grade as $aulas) {
                                        if (isset($aulas[$ordem])) { $h = $aulas[$ordem]['inicio'].'–'.$aulas[$ordem]['fim']; break; }
                                    }
                                @endphp
                                <br><span style="font-weight:normal;color:#666">{{ $h }}</span>
                            </td>
                            @foreach($diasGrade as $dia)
                                @php $cel = $grade[$dia][$ordem] ?? null; @endphp
                                <td>
                                    @if($cel && $cel['disciplina'])
                                        @php $cor = $cel['cor'] ?? ['bg' => '#e2e8f0', 'tx' => '#1e293b']; @endphp
                                        <div class="aula-box" style="background:{{ $cor['bg'] }};border-left:3px solid {{ $cor['tx'] }};">
                                            <div class="disc" style="color:{{ $cor['tx'] }}">{{ $cel['disciplina'] }}</div>
                                            @if($cel['professor'])<div class="prof" style="color:{{ $cor['tx'] }}">{{ $cel['professor'] }}</div>@endif
                                            @if($cel['sala'])<div class="sala">&#128205; {{ $cel['sala'] }}</div>@endif
                                        </div>
                                    @else
                                        <div class="vazia" style="padding:2px">—</div>
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
                                <td class="horario intervalo">
                                    Intervalo
                                    @if($ivPrimeiro)<br><span style="font-weight:normal">{{ $ivPrimeiro['inicio'] }}–{{ $ivPrimeiro['fim'] }}</span>@endif
                                </td>
                                @foreach($diasGrade as $dia)
                                    <td class="intervalo">
                                        @if(isset($intervalos[$dia][$ordem]))
                                            Intervalo
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    @endforeach
</body>
</html>
