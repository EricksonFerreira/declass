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
    th, td { border: 1px solid #999; padding: 3px 4px; text-align: center; vertical-align: top; }
    th { background: #eef2f7; font-size: 8.5px; }
    td.horario { background: #f8fafc; font-weight: bold; font-size: 8px; white-space: nowrap; }
    td.aula { font-size: 8.5px; }
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
        @php $turma = $item['turma']; $grade = $item['grade']; @endphp
        <div class="turma">
            <h2>{{ $turma->nome }} <small style="color:#666;font-weight:normal">— {{ $turma->escola->nome ?? '' }} · {{ $turma->anoLetivo->ano ?? '' }} · {{ \App\Models\Turma::TURNOS[$turma->turno ?? ''] ?? '' }}</small></h2>
            @if(empty($grade))
                <p style="color:#888">Sem grade configurada para esta turma.</p>
            @else
            <table>
                <thead>
                    <tr>
                        <th style="width:70px">Horário</th>
                        @foreach($grade as $dia => $aulas)
                            <th>{{ $dias[$dia] ?? $dia }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $ordens = [];
                        foreach ($grade as $aulas) { foreach ($aulas as $o => $a) { $ordens[$o] = true; } }
                        $ordens = array_keys($ordens); sort($ordens);
                    @endphp
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
                            @foreach($grade as $dia => $aulas)
                                <td class="aula">{{ $aulas[$ordem]['rotulo'] ?? '' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    @endforeach
</body>
</html>
