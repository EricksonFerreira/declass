<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Boletim — {{ $aluno->pessoa->nome ?? 'Aluno' }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
    h1 { font-size: 15px; margin: 0 0 2px; }
    .sub { color: #555; font-size: 10px; margin-bottom: 10px; }
    .box { border: 1px solid #999; padding: 6px 8px; margin-bottom: 10px; }
    .box table { width: 100%; border-collapse: collapse; }
    .box td { padding: 1px 4px; }
    table.notas { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 3px 5px; }
    th { background: #eef2f7; font-size: 9px; text-align: left; }
    td.num, th.num { text-align: center; width: 80px; }
    .disc { font-size: 11px; font-weight: bold; margin: 10px 0 4px; }
    .disc span { float: right; font-weight: normal; }
    .freq-ok { color: #1a7f37; font-weight: bold; }
    .freq-bad { color: #b42318; font-weight: bold; }
    .footer { margin-top: 14px; font-size: 9px; color: #666; }
</style>
</head>
<body>
    <h1>Boletim Escolar</h1>
    <div class="sub">
        {{ $turma->escola->nome ?? '' }} · Ano letivo {{ $turma->anoLetivo->ano ?? '' }}
        · {{ $turma->etapa->nome ?? '' }} · Turma {{ $turma->nome }}
    </div>

    <div class="box">
        <table>
            <tr>
                <td><strong>Aluno:</strong> {{ $aluno->pessoa->nome ?? '—' }}</td>
                <td><strong>Nº chamada:</strong> {{ $matricula->numero_chamada ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Escola:</strong> {{ $turma->escola->nome ?? '—' }}</td>
                <td><strong>Aulas registradas:</strong> {{ $total_aulas }}</td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Frequência:</strong>
                    @if($frequencia['pct'] !== null)
                        <span class="{{ $frequencia['pct'] >= 75 ? 'freq-ok' : 'freq-bad' }}">{{ $frequencia['pct'] }}%</span>
                        ({{ $frequencia['presente'] }} presenças · {{ $frequencia['justificada'] }} justificadas · {{ $frequencia['falta'] }} faltas)
                    @else
                        —
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @foreach($disciplinas as $disciplina)
        @php $avals = $avaliacoes->get($disciplina->id, collect()); @endphp
        <div class="disc">
            {{ $disciplina->nome }}
            <span>
                @php $media = $medias[$disciplina->id] ?? null; @endphp
                @if($media !== null) Média: {{ number_format($media, 1, ',', '.') }} @endif
            </span>
        </div>
        <table class="notas">
            <thead>
                <tr>
                    <th>Avaliação</th>
                    <th>Tipo</th>
                    <th>Data</th>
                    <th class="num">Nota</th>
                </tr>
            </thead>
            <tbody>
                @forelse($avals as $av)
                    @php $nota = $av->notas->first()?->nota; @endphp
                    <tr>
                        <td>{{ $av->nome }}</td>
                        <td>{{ \App\Models\Avaliacao::TIPOS[$av->tipo] ?? $av->tipo }}</td>
                        <td>{{ $av->data?->format('d/m/Y') ?? '—' }}</td>
                        <td class="num">
                            {{ $nota !== null ? number_format($nota, 1, ',', '.').' / '.number_format($av->nota_maxima, 1, ',', '.') : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:#777;">Nenhuma avaliação registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    <div class="footer">
        Emitido em {{ now()->format('d/m/Y H:i') }} · {{ $turma->escola->nome ?? '' }}
    </div>
</body>
</html>
