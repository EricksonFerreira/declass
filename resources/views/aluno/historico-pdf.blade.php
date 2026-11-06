<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Histórico Escolar — {{ $aluno->nome }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
    .cab { text-align: center; border-bottom: 3px double #1d4ed8; padding-bottom: 8px; margin-bottom: 16px; }
    .cab h1 { font-size: 15px; margin: 0; color: #1d4ed8; text-transform: uppercase; }
    .cab h2 { font-size: 12px; margin: 3px 0 0; font-weight: normal; color: #444; }
    .sec { font-size: 11px; font-weight: bold; color: #1d4ed8; text-transform: uppercase; border-bottom: 1px solid #cbd5e1; margin: 16px 0 6px; padding-bottom: 2px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    th, td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; }
    th { background: #f1f5f9; font-size: 9px; text-transform: uppercase; }
    table.dados td.lbl { background: #f8fafc; font-weight: bold; width: 22%; font-size: 9px; }
    .badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8px; font-weight: bold; }
    .b-success { background: #dcfce7; color: #166534; }
    .b-warning { background: #fef9c3; color: #854d0e; }
    .b-danger { background: #fee2e2; color: #991b1b; }
    .b-info { background: #dbeafe; color: #1e40af; }
    .b-secondary { background: #e2e8f0; color: #475569; }
    .muted { color: #64748b; }
    .rodape { margin-top: 20px; font-size: 8px; color: #94a3b8; text-align: center; }
</style>
</head>
<body>
    @php
        $p = $aluno->pessoa;
        $statusMatricula = \App\Models\Matricula::STATUS;
        $statusBadge = ['ativa' => 'b-success', 'trancada' => 'b-warning', 'cancelada' => 'b-danger', 'concluida' => 'b-info'];
        $ocorrencias = $eventos->where('tipo', 'Ocorrência')->take(15);
    @endphp

    <div class="cab">
        <h1>{{ $aluno->escola->nome ?? 'Escola' }}</h1>
        <h2>Histórico Escolar do Aluno</h2>
    </div>

    <table class="dados">
        <tr>
            <td class="lbl">Aluno(a)</td><td>{{ $p->nome ?? $aluno->nome }}</td>
            <td class="lbl">RA</td><td>{{ $aluno->registro_academico ?? '—' }}</td>
            <td class="lbl">Nascimento</td><td>{{ optional($p->data_nascimento)->format('d/m/Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Escola</td><td>{{ $aluno->escola->nome ?? '—' }}</td>
            <td class="lbl">Ingresso</td><td>{{ optional($aluno->data_ingresso)->format('d/m/Y') ?? '—' }}</td>
            <td class="lbl">Emitido em</td><td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <div class="sec">Histórico por ano letivo</div>
    <table>
        <thead>
            <tr>
                <th>Ano letivo</th><th>Turma</th><th>Etapa</th><th>Situação</th>
                <th>Chamada</th><th>Ingresso</th><th>Frequência</th><th>Média</th>
            </tr>
        </thead>
        <tbody>
            @forelse($matriculas as $m)
                @php
                    $freq = $frequencias[$m->id] ?? null;
                    $pres = $freq ? (int) ($freq['presente'] ?? 0) : 0;
                    $freqTotal = $freq ? (int) $freq->sum() : 0;
                    $freqPct = $freqTotal > 0 ? round($pres / $freqTotal * 100) : null;
                    $media = $medias[$m->id] ?? null;
                @endphp
                <tr>
                    <td><strong>{{ $m->turma->anoLetivo->ano ?? '—' }}</strong></td>
                    <td>{{ $m->turma->nome ?? '—' }}</td>
                    <td>{{ $m->turma->etapa->nome ?? '—' }}</td>
                    <td><span class="badge {{ $statusBadge[$m->status] ?? 'b-secondary' }}">{{ $statusMatricula[$m->status] ?? $m->status }}</span></td>
                    <td>{{ $m->numero_chamada ?? '—' }}</td>
                    <td>{{ optional($m->data_matricula)->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $freqPct !== null ? $freqPct.'%' : '—' }}</td>
                    <td>{{ $media ? $media['media'].'%' : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted" style="text-align:center;">Nenhuma matrícula registrada.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="dados">
        <tr>
            <td class="lbl">Mensalidades</td>
            <td>
                {{ $financeiro['pagas'] }} paga(s) · {{ $financeiro['pendentes'] }} pendente(s)
                @if($financeiro['atrasadas'] > 0) · <strong style="color:#991b1b;">{{ $financeiro['atrasadas'] }} atrasada(s)</strong>@endif
            </td>
            <td class="lbl">Em aberto</td>
            <td><strong>R$ {{ number_format($financeiro['valor_em_aberto'], 2, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="lbl">Anos na escola</td><td>{{ $anosNaEscola }}</td>
            <td class="lbl">Frequência geral</td>
            <td>{{ $frequenciaGeral !== null ? $frequenciaGeral.'%' : '—' }}</td>
        </tr>
    </table>

    @if($saude && ($saude->tipo_sanguineo || $saude->alergias || $saude->condicoes_cronicas || $saude->medicamentos))
        <div class="sec">Informações de saúde</div>
        <table class="dados">
            @if($saude->tipo_sanguineo)<tr><td class="lbl">Tipo sanguíneo</td><td>{{ \App\Models\SaudeAluno::TIPOS_SANGUINEOS[$saude->tipo_sanguineo] ?? $saude->tipo_sanguineo }}</td></tr>@endif
            @if($saude->alergias)<tr><td class="lbl">Alergias</td><td>{{ $saude->alergias }}</td></tr>@endif
            @if($saude->medicamentos)<tr><td class="lbl">Medicamentos</td><td>{{ $saude->medicamentos }}</td></tr>@endif
            @if($saude->condicoes_cronicas)<tr><td class="lbl">Condições crônicas</td><td>{{ $saude->condicoes_cronicas }}</td></tr>@endif
            @if($saude->contato_emergencia_nome)<tr><td class="lbl">Contato de emergência</td><td>{{ $saude->contato_emergencia_nome }} — {{ $saude->contato_emergencia_telefone ?? '—' }}</td></tr>@endif
        </table>
    @endif

    @if($ocorrencias->isNotEmpty())
        <div class="sec">Ocorrências e eventos ({{ $ocorrencias->count() }})</div>
        <table>
            <thead><tr><th style="width:70px;">Data</th><th style="width:110px;">Tipo</th><th>Descrição</th></tr></thead>
            <tbody>
                @foreach($ocorrencias as $evento)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($evento['data'])->format('d/m/Y') }}</td>
                        <td><span class="badge b-warning">{{ $evento['titulo'] }}</span></td>
                        <td>{{ $evento['detalhe'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="rodape">
        Documento gerado pelo sistema Declass em {{ now()->format('d/m/Y \à\s H:i') }} — registro cumulativo do aluno.
    </div>
</body>
</html>
