<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Histórico Escolar Completo — {{ $aluno->nome }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
    .cab { text-align: center; border-bottom: 3px double #1d4ed8; padding-bottom: 8px; margin-bottom: 14px; }
    .cab h1 { font-size: 15px; margin: 0; color: #1d4ed8; text-transform: uppercase; }
    .cab h2 { font-size: 11px; margin: 3px 0 0; font-weight: normal; color: #444; }
    .sec { font-size: 10px; font-weight: bold; color: #1d4ed8; text-transform: uppercase; border-bottom: 1px solid #cbd5e1; margin: 14px 0 5px; padding-bottom: 2px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    th, td { border: 1px solid #cbd5e1; padding: 3px 5px; text-align: left; }
    th { background: #f1f5f9; font-size: 8px; text-transform: uppercase; }
    th.num, td.num { text-align: center; }
    table.dados td.lbl { background: #f8fafc; font-weight: bold; width: 18%; font-size: 8px; }
    .ano { page-break-after: always; }
    .ano:last-child { page-break-after: auto; }
    .ano-head { background: #eff6ff; border: 1px solid #bfdbfe; padding: 6px 8px; margin-bottom: 6px; }
    .ano-head strong { font-size: 12px; color: #1e40af; }
    .ano-head .meta { color: #475569; font-size: 8.5px; }
    .media-ok { color: #166534; font-weight: bold; }
    .media-mid { color: #854d0e; font-weight: bold; }
    .media-low { color: #991b1b; font-weight: bold; }
    .muted { color: #64748b; }
    .assinatura { margin-top: 40px; }
    .assinatura td { border: none; text-align: center; }
    .assinatura .linha { display: inline-block; border-top: 1px solid #334155; padding-top: 5px; margin-top: 26px; width: 88%; font-size: 8.5px; color: #475569; }
    .rodape { margin-top: 18px; font-size: 7.5px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
    @php
        $p = $aluno->pessoa;
        $statusMatricula = \App\Models\Matricula::STATUS;
        $fmtPct = fn ($v) => $v === null ? '—' : number_format($v, 1, ',', '.').'%';
        $mediaClass = fn ($v) => $v === null ? '' : ($v >= 75 ? 'media-ok' : ($v >= 50 ? 'media-mid' : 'media-low'));
        $primeiraEscola = $anos->first()['escola'] ?? $aluno->escola ?? null;
    @endphp

    <div class="cab">
        @include('pdf._logo', ['escola' => $primeiraEscola ?? $aluno->escola])
        <h1>{{ $primeiraEscola->nome ?? $aluno->escola->nome ?? 'Escola' }}</h1>
        @if($primeiraEscola)
            <h2>{{ collect([$primeiraEscola->endereco, $primeiraEscola->telefone, $primeiraEscola->email])->filter()->implode(' · ') }}</h2>
        @endif
        <h2>Histórico Escolar — Registro Acumulado Completo</h2>
    </div>

    <table class="dados">
        <tr>
            <td class="lbl">Aluno(a)</td><td><strong>{{ $p->nome ?? $aluno->nome }}</strong></td>
            <td class="lbl">RA</td><td>{{ $aluno->registro_academico ?? '—' }}</td>
            <td class="lbl">Nascimento</td><td>{{ optional($p->data_nascimento)->format('d/m/Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Escola</td><td>{{ $aluno->escola->nome ?? '—' }}</td>
            <td class="lbl">Ingresso</td><td>{{ optional($aluno->data_ingresso)->format('d/m/Y') ?? '—' }}</td>
            <td class="lbl">Emitido em</td><td>{{ now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    @forelse($anos as $ano)
        @php
            $mat = $ano['matricula'];
            $tur = $ano['turma'];
            $freq = $ano['frequencia'];
        @endphp
        <div class="ano">
            <div class="ano-head">
                <strong>Ano letivo {{ $ano['ano_letivo']->ano ?? '—' }}</strong>
                <span class="meta">
                    — {{ $tur->nome ?? 'Turma' }}
                    @if($ano['etapa']) · {{ $ano['etapa']->nome }}@endif
                    @if($tur->turno) · {{ \App\Models\Turma::TURNOS[$tur->turno] ?? $tur->turno }}@endif
                    · Matrícula {{ optional($mat->data_matricula)->format('d/m/Y') ?? '—' }}
                    · Situação: {{ $statusMatricula[$mat->status] ?? $mat->status }}
                    @if($mat->numero_chamada) · Chamada {{ $mat->numero_chamada }}@endif
                </span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width:34%;">Disciplina</th>
                        <th class="num" style="width:7%;">CH/sem</th>
                        @foreach($ano['periodos'] as $periodo)
                            <th class="num">{{ $periodo->nome }}</th>
                        @endforeach
                        @if($ano['tem_sem_periodo'])
                            <th class="num muted">Sem período</th>
                        @endif
                        <th class="num" style="width:7%;">Mín.</th>
                        <th class="num" style="width:10%;">Média final</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ano['linhas'] as $linha)
                        <tr>
                            <td>{{ $linha['disciplina']->nome }}</td>
                            <td class="num">{{ $linha['carga_horaria'] ?? '—' }}</td>
                            @foreach($ano['periodos'] as $periodo)
                                <td class="num {{ $mediaClass($linha['medias'][$periodo->id] ?? null) }}">{{ $fmtPct($linha['medias'][$periodo->id] ?? null) }}</td>
                            @endforeach
                            @if($ano['tem_sem_periodo'])
                                <td class="num {{ $mediaClass($linha['medias']['__sem_periodo__'] ?? null) }}">{{ $fmtPct($linha['medias']['__sem_periodo__'] ?? null) }}</td>
                            @endif
                            <td class="num muted">{{ number_format($linha['corte'], 1, ',', '.') }}</td>
                            <td class="num {{ $mediaClass($linha['media_geral']) }}"><strong>{{ $fmtPct($linha['media_geral']) }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 4 + $ano['periodos']->count() + ($ano['tem_sem_periodo'] ? 1 : 0) }}" class="muted" style="text-align:center;">Nenhuma disciplina ofertada nesta turma.</td></tr>
                    @endforelse
                    <tr>
                        <td colspan="{{ 3 + $ano['periodos']->count() + ($ano['tem_sem_periodo'] ? 1 : 0) }}" class="muted">
                            Frequência no ano: {{ $freq['total'] > 0 ? $freq['pct'].'% ('.($freq['presente'] + $freq['justificada']).' presenças, '.$freq['falta'].' faltas)' : 'sem registros' }}
                            · <span title="Nota mínima (0–10) para aprovação em cada disciplina">Min. = média de aprovação</span>
                        </td>
                        <td class="num" style="background:#f8fafc;"><strong>Total de disciplinas: {{ $ano['linhas']->count() }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <p class="muted" style="text-align:center;">Nenhuma matrícula registrada para este aluno.</p>
    @endforelse

    <table class="assinatura">
        <tr>
            <td style="width:42%;"><span class="linha">{{ $primeiraEscola->nome ?? 'Escola' }}<br>Direção / Secretaria</span></td>
            <td style="width:16%;"></td>
            <td style="width:42%;"><span class="linha">Responsável legal<br>{{ $aluno->responsaveis->first()?->pessoa?->nome ?? '____________________' }}</span></td>
        </tr>
    </table>

    <div class="rodape">
        Histórico escolar completo emitido pelo sistema Declass em {{ now()->format('d/m/Y \à\s H:i') }} — registro cumulativo de todas as matrículas do aluno.
    </div>
</body>
</html>
