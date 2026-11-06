<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Grade horária — {{ $turma->nome }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
    h1 { font-size: 14px; margin: 0 0 2px; }
    .sub { color: #555; font-size: 9px; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 3px 4px; text-align: center; vertical-align: top; }
    th { background: #eef2f7; font-size: 8.5px; }
    td.horario { background: #f8fafc; font-weight: bold; font-size: 8px; white-space: nowrap; }
    td.aula { font-size: 8.5px; }
    .footer { margin-top: 12px; font-size: 8px; color: #666; }
</style>
</head>
<body>
    <h1>Grade horária — {{ $turma->nome }}</h1>
    <div class="sub">
        {{ $turma->escola->nome ?? '' }} · Ano letivo {{ $turma->anoLetivo->ano ?? '' }}
        · {{ $turma->etapa->nome ?? '' }} · {{ \App\Models\Turma::TURNOS[$turma->turno ?? ''] ?? $turma->turno ?? '' }}
        · {{ $comProfessor ? 'Disciplina + professor' : 'Somente disciplinas' }}
    </div>

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

    <div class="footer">Gerado em {{ now()->format('d/m/Y H:i') }} — Declass</div>
</body>
</html>
