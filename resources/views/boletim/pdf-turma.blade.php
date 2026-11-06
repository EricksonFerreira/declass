<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Boletim — {{ $turma->nome }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
    h1 { font-size: 15px; margin: 0 0 2px; }
    .sub { color: #555; font-size: 10px; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 3px 5px; text-align: center; }
    th { background: #eef2f7; font-size: 9px; }
    td.aluno { text-align: left; font-weight: bold; }
    .freq-ok { color: #1a7f37; }
    .freq-bad { color: #b42318; font-weight: bold; }
    .footer { margin-top: 14px; font-size: 9px; color: #666; }
</style>
</head>
<body>
    <h1>Boletim Escolar — {{ $turma->nome }}</h1>
    <div class="sub">
        {{ $turma->escola->nome ?? '' }} · Ano letivo {{ $turma->anoLetivo->ano ?? '' }}
        · {{ $turma->etapa->nome ?? '' }} · {{ $turma->turno ?? '' }}
        · Aulas registradas: {{ $total_aulas }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Aluno</th>
                @foreach($disciplinas as $disciplina)
                    <th>{{ \Illuminate\Support\Str::limit($disciplina->nome, 14) }}</th>
                @endforeach
                <th>Frequência</th>
            </tr>
        </thead>
        <tbody>
            @foreach($matriculas as $matricula)
                <tr>
                    <td class="aluno">
                        {{ $matricula->aluno->pessoa->nome ?? 'Aluno' }}
                        @if($matricula->numero_chamada) <small>({{ $matricula->numero_chamada }})</small> @endif
                    </td>
                    @foreach($disciplinas as $disciplina)
                        @php $media = $medias["{$matricula->id}:{$disciplina->id}"] ?? null; @endphp
                        <td>{{ $media !== null ? number_format($media, 1, ',', '.') : '—' }}</td>
                    @endforeach
                    @php $f = $frequencias->get($matricula->id); @endphp
                    <td>
                        @if($f && $f['pct'] !== null)
                            <span class="{{ $f['pct'] >= 75 ? 'freq-ok' : 'freq-bad' }}">{{ $f['pct'] }}%</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Emitido em {{ now()->format('d/m/Y H:i') }} · Médias calculadas a partir das avaliações lançadas.
    </div>
</body>
</html>
