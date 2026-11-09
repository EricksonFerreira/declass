<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Boletim — {{ $turma->nome }}</title>
<style>
    @page { margin: 34px 36px 40px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1f2937; line-height: 1.35; }

    .head { border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-bottom: 9px; }
    .head table { width: 100%; border-collapse: collapse; }
    .head td { border: none; padding: 0; vertical-align: bottom; }
    h1 { font-size: 14px; margin: 0; color: #111827; }
    .sub { color: #6b7280; font-size: 7.5px; margin-top: 2px; }
    .head-r { text-align: right; font-size: 7px; color: #6b7280; }

    table.boletim { width: 100%; border-collapse: collapse; }
    .boletim th, .boletim td { border: none; border-bottom: 1px solid #f3f4f6; padding: 3px 5px; text-align: center; font-size: 7.5px; }
    .boletim thead th { border-bottom: 1px solid #d1d5db; background: #f3f4f6; font-size: 6.5px; text-transform: uppercase; color: #6b7280; }
    .boletim tbody tr.alt td { background: #fafafa; }
    .boletim td.aluno { text-align: left; font-weight: bold; }
    .boletim td.aluno small { color: #9ca3af; font-weight: normal; }
    .boletim th.disc-head { text-transform: none; font-weight: bold; }

    .freq-ok { color: #15803d; font-weight: bold; }
    .freq-bad { color: #b91c1c; font-weight: bold; }
    .media-ok { color: #15803d; font-weight: bold; }
    .media-bad { color: #b91c1c; font-weight: bold; }

    .footer { margin-top: 12px; border-top: 1px solid #e5e7eb; padding-top: 4px; font-size: 7px; color: #9ca3af; }
    .footer .r { float: right; }
</style>
</head>
<body>
    <div class="head">
        <table>
            <tr>
                <td style="width:80px;vertical-align:middle;">
                    @include('pdf._logo', ['escola' => $turma->escola, 'style' => 'display:block;max-height:46px;max-width:74px;'])
                </td>
                <td>
                    <h1>Boletim Escolar — {{ $turma->nome }}</h1>
                    <div class="sub">
                        {{ $turma->escola->nome ?? '' }} · Ano letivo {{ $turma->anoLetivo->ano ?? '' }}
                        · {{ $turma->etapa->nome ?? '' }} · {{ $turma->turno ?? '' }}
                        · Aulas registradas: {{ $total_aulas }}
                    </div>
                </td>
                <td class="head-r">Emitido em<br>{{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <table class="boletim">
        <thead>
            <tr>
                <th style="text-align:left;">Aluno</th>
                @foreach($disciplinas as $disciplina)
                    @php $cor = \App\Services\TurmaGradeService::corDaDisciplina($disciplina->id); @endphp
                    <th class="disc-head" style="background:{{ $cor['bg'] }}; color:{{ $cor['tx'] }}; border-bottom-color:{{ $cor['tx'] }};">
                        {{ \Illuminate\Support\Str::limit($disciplina->nome, 14) }}
                    </th>
                @endforeach
                <th>Frequência</th>
            </tr>
        </thead>
        <tbody>
            @foreach($matriculas as $matricula)
                <tr class="{{ $loop->even ? 'alt' : '' }}">
                    <td class="aluno">
                        {{ $matricula->aluno->pessoa->nome ?? 'Aluno' }}
                        @if($matricula->numero_chamada) <small>({{ $matricula->numero_chamada }})</small> @endif
                    </td>
                    @foreach($disciplinas as $disciplina)
                        @php $media = $medias["{$matricula->id}:{$disciplina->id}"] ?? null; @endphp
                        <td>
                            @if($media !== null)
                                <span class="{{ $media >= $disciplina->mediaAprovacaoNaTurma() ? 'media-ok' : 'media-bad' }}">{{ number_format($media, 1, ',', '.') }}</span>
                            @else
                                —
                            @endif
                        </td>
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
        {{ $turma->escola->nome ?? '' }}
        <span class="r">Médias calculadas a partir das avaliações lançadas · Emitido em {{ now()->format('d/m/Y H:i') }}</span>
    </div>
</body>
</html>
