<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Boletim — {{ $aluno->pessoa->nome ?? 'Aluno' }}</title>
<style>
    @page { margin: 34px 36px 40px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1f2937; line-height: 1.35; }

    .head { border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-bottom: 9px; }
    .head table { width: 100%; border-collapse: collapse; }
    .head td { border: none; padding: 0; vertical-align: bottom; }
    h1 { font-size: 14px; margin: 0; color: #111827; }
    .sub { color: #6b7280; font-size: 7.5px; margin-top: 2px; }
    .head-r { text-align: right; font-size: 7px; color: #6b7280; }

    .box { border: 1px solid #e5e7eb; background: #f9fafb; padding: 6px 9px; margin-bottom: 10px; }
    .box table { width: 100%; border-collapse: collapse; }
    .box td { border: none; padding: 1px 4px; vertical-align: top; }
    .lbl { color: #9ca3af; font-size: 6.5px; text-transform: uppercase; }

    .freq-ok { color: #15803d; font-weight: bold; }
    .freq-bad { color: #b91c1c; font-weight: bold; }

    .grid { width: 100%; border-collapse: collapse; }
    .celula { width: 50%; vertical-align: top; border: none; padding: 0 0 8px; }
    .celula.e { padding-right: 4px; }
    .celula.d { padding-left: 4px; }

    .card { border: 1px solid #e5e7eb; }
    .card-head { padding: 3px 6px; }
    .card-head table { width: 100%; border-collapse: collapse; }
    .card-head td { border: none; padding: 0; }
    .disc-nome { font-size: 8.5px; font-weight: bold; }
    .media { font-size: 7px; font-weight: bold; padding: 1px 6px; }

    table.notas { width: 100%; border-collapse: collapse; }
    .notas th { border: none; border-bottom: 1px solid #d1d5db; background: #f3f4f6; font-size: 6.5px; text-transform: uppercase; color: #6b7280; padding: 2px 5px; text-align: left; }
    .notas td { border: none; border-bottom: 1px solid #f3f4f6; padding: 2px 5px; font-size: 7.5px; }
    .notas tr.alt td { background: #fafafa; }
    .notas td.num, .notas th.num { text-align: center; }
    .nota-v { font-weight: bold; }
    .vazio { text-align: center; color: #9ca3af; }

    .sec-titulo { font-size: 9px; font-weight: bold; color: #111827; border-bottom: 1px solid #d1d5db; padding-bottom: 2px; margin: 4px 0 0; }
    .sec-titulo span { float: right; font-weight: normal; font-size: 7.5px; color: #6b7280; }

    .footer { margin-top: 12px; border-top: 1px solid #e5e7eb; padding-top: 4px; font-size: 7px; color: #9ca3af; }
    .footer .r { float: right; }
    .avoid { page-break-inside: avoid; }
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
                    <h1>Boletim Escolar</h1>
                    <div class="sub">
                        {{ $turma->escola->nome ?? '' }} · Ano letivo {{ $turma->anoLetivo->ano ?? '' }}
                        · {{ $turma->etapa->nome ?? '' }} · Turma {{ $turma->nome }}
                    </div>
                </td>
                <td class="head-r">Emitido em<br>{{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="box">
        <table>
            <tr>
                <td style="width:60%;">
                    <div class="lbl">Aluno</div>
                    <strong>{{ $aluno->pessoa->nome ?? '—' }}</strong>
                </td>
                <td style="width:20%;">
                    <div class="lbl">Nº chamada</div>
                    {{ $matricula->numero_chamada ?? '—' }}
                </td>
                <td style="width:20%;">
                    <div class="lbl">Aulas registradas</div>
                    {{ $total_aulas }}
                </td>
            </tr>
            <tr>
                <td colspan="3" style="padding-top:4px;">
                    <div class="lbl">Frequência</div>
                    @if($frequencia['pct'] !== null)
                        <span class="{{ $frequencia['pct'] >= 75 ? 'freq-ok' : 'freq-bad' }}">{{ $frequencia['pct'] }}%</span>
                        <span style="color:#6b7280;">({{ $frequencia['presente'] }} presenças · {{ $frequencia['justificada'] }} justificadas · {{ $frequencia['falta'] }} faltas)</span>
                    @else
                        —
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="grid">
        @foreach($disciplinas->chunk(2) as $par)
            <tr>
                @foreach($par as $disciplina)
                    @php
                        $cor = \App\Services\TurmaGradeService::corDaDisciplina($disciplina->id);
                        $avals = $avaliacoes->get($disciplina->id, collect());
                        $media = $medias[$disciplina->id] ?? null;
                    @endphp
                    <td class="celula {{ $loop->first ? 'e' : 'd' }}">
                        <div class="card avoid">
                            <div class="card-head" style="background:{{ $cor['bg'] }}; border-left:3px solid {{ $cor['tx'] }};">
                                <table>
                                    <tr>
                                        <td class="disc-nome" style="color:{{ $cor['tx'] }}">{{ $disciplina->nome }}</td>
                                        <td style="text-align:right;">
                                            @if($media !== null)
                                                <span class="media" style="background:{{ $media >= $disciplina->mediaAprovacaoNaTurma() ? '#15803d' : '#b91c1c' }}; color:#fff">Média {{ number_format($media, 1, ',', '.') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <table class="notas">
                                <thead>
                                    <tr>
                                        <th>Avaliação</th>
                                        <th style="width:34px;">Tipo</th>
                                        <th style="width:38px;">Data</th>
                                        <th class="num" style="width:42px;">Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($avals as $av)
                                        @php $nota = $av->notas->first()?->nota; @endphp
                                        <tr class="{{ $loop->even ? 'alt' : '' }}">
                                            <td>{{ $av->nome }}</td>
                                            <td>{{ \App\Models\Avaliacao::TIPOS[$av->tipo] ?? $av->tipo }}</td>
                                            <td>{{ $av->data?->format('d/m/Y') ?? '—' }}</td>
                                            <td class="num nota-v">
                                                {{ $nota !== null ? number_format($nota, 1, ',', '.').' / '.number_format($av->nota_maxima, 1, ',', '.') : '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="vazio">Nenhuma avaliação registrada.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </td>
                @endforeach
                @if($par->count() === 1)
                    <td class="celula d"></td>
                @endif
            </tr>
        @endforeach
    </table>

    <div class="sec-titulo">
        Ocorrências disciplinares
        <span>
            @foreach(\App\Models\Ocorrencia::TIPOS as $tipo => $label)
                @if(($ocorrencias_resumo[$tipo] ?? 0) > 0) {{ $label }}: {{ $ocorrencias_resumo[$tipo] }} · @endif
            @endforeach
            @if($ocorrencias->isEmpty()) Nenhuma no período @endif
        </span>
    </div>
    <table class="notas">
        <thead>
            <tr>
                <th style="width:42px;">Data</th>
                <th style="width:60px;">Tipo</th>
                <th style="width:48px;">Gravidade</th>
                <th>Descrição</th>
                <th style="width:120px;">Medida adotada</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ocorrencias as $oc)
                <tr class="{{ $loop->even ? 'alt' : '' }}">
                    <td>{{ $oc->data?->format('d/m/Y') }}</td>
                    <td>{{ $oc->tipoLabel() }}</td>
                    <td>{{ $oc->gravidadeLabel() }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($oc->descricao, 100) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($oc->medida_adotada, 60) ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="vazio">Nenhuma ocorrência registrada no período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ $turma->escola->nome ?? '' }}
        <span class="r">Médias calculadas a partir das avaliações lançadas · Emitido em {{ now()->format('d/m/Y H:i') }}</span>
    </div>
</body>
</html>
