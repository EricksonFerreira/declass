<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Agenda de Avaliações — {{ $mesCarbon->locale('pt_BR')->translatedFormat('F/Y') }}</title>
<style>
    @page { margin: 34px 36px 40px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #1f2937; line-height: 1.4; }

    .head { border-bottom: 2px solid #4f46e5; padding-bottom: 6px; margin-bottom: 10px; }
    .head table { width: 100%; border-collapse: collapse; }
    .head td { border: none; padding: 0; vertical-align: bottom; }
    h1 { font-size: 15px; margin: 0; color: #111827; }
    .sub { color: #6b7280; font-size: 8px; margin-top: 2px; }
    .head-r { text-align: right; font-size: 7.5px; color: #6b7280; }

    .dia-head { background: #eef2ff; border-left: 3px solid #4f46e5; padding: 4px 8px; margin: 9px 0 0; font-weight: bold; font-size: 9px; color: #1e293b; }
    .dia-head .sem { color: #64748b; font-weight: normal; text-transform: capitalize; }
    .dia-head.hoje { background: #dbeafe; border-left-color: #2563eb; }

    table.itens { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    .itens td { border: none; border-bottom: 1px solid #f1f5f9; padding: 4px 8px 4px 12px; vertical-align: top; }
    .itens .nome { font-weight: bold; font-size: 9px; }
    .itens .meta { color: #6b7280; font-size: 7.5px; margin-top: 1px; }
    .tipo { font-size: 6.5px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em; border-radius: 3px; padding: 1px 5px; white-space: nowrap; }

    .vazio { border: 1px dashed #d1d5db; border-radius: 6px; text-align: center; color: #9ca3af; padding: 22px 10px; margin-top: 8px; font-size: 9px; }

    .legenda { margin-top: 10px; border-top: 1px solid #e5e7eb; padding-top: 6px; font-size: 7px; color: #9ca3af; }
    .legenda .tipo { margin-right: 2px; }
    .footer { margin-top: 10px; font-size: 7px; color: #9ca3af; }
    .footer .r { float: right; }
</style>
</head>
<body>
    @php
        $tipoCores = [
            'prova' => ['#dc2626', '#fee2e2'],
            'trabalho' => ['#0284c7', '#e0f2fe'],
            'participacao' => ['#16a34a', '#dcfce7'],
            'simulado' => ['#d97706', '#fef3c7'],
            'projeto' => ['#2563eb', '#dbeafe'],
            'recuperacao' => ['#7c3aed', '#ede9fe'],
            'outro' => ['#475569', '#f1f5f9'],
        ];
        $porDia = $avaliacoes->groupBy(fn ($a) => $a->data->format('Y-m-d'));
        $tiposUsados = $avaliacoes->pluck('tipo')->unique()->filter()->values();
    @endphp

    <div class="head">
        <table>
            <tr>
                <td style="width:80px;vertical-align:middle;">
                    @include('pdf._logo', ['escola' => $escola, 'style' => 'display:block;max-height:46px;max-width:74px;'])
                </td>
                <td>
                    <h1>Agenda de Avaliações — {{ ucfirst($mesCarbon->locale('pt_BR')->translatedFormat('F')) }} de {{ $mesCarbon->year }}</h1>
                    <div class="sub">
                        {{ $escola->nome ?? 'Todas as escolas' }}
                        @if($turma) · Turma {{ $turma->nome }}@endif
                        · {{ $avaliacoes->count() }} avaliação(ões) no mês
                    </div>
                </td>
                <td class="head-r">Emitido em<br>{{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    @forelse($porDia as $dia => $doDia)
        @php $dataDia = \Carbon\Carbon::parse($dia); @endphp
        <div class="dia-head {{ $dataDia->isToday() ? 'hoje' : '' }}">
            {{ $dataDia->format('d/m') }}
            <span class="sem">· {{ $dataDia->locale('pt_BR')->translatedFormat('l') }}</span>
            @if($dataDia->isToday()) · hoje @endif
        </div>
        <table class="itens">
            @foreach($doDia as $av)
                @php $cor = $tipoCores[$av->tipo] ?? $tipoCores['outro']; @endphp
                <tr>
                    <td style="width:70px;">
                        <span class="tipo" style="background:{{ $cor[1] }}; color:{{ $cor[0] }};">{{ $tipoLabel[$av->tipo] ?? $av->tipo }}</span>
                    </td>
                    <td>
                        <div class="nome">{{ $av->nome }}</div>
                        <div class="meta">
                            @if(! $turma && $av->turma){{ $av->turma->nome }} · @endif
                            {{ $av->disciplina->nome ?? '—' }}
                            @if($av->periodo) · {{ $av->periodo->nome }}@endif
                            @if($av->nota_maxima !== null) · máx. {{ $av->nota_maxima }}@endif
                        </div>
                        @if($av->descricao)
                            <div class="meta">{{ \Illuminate\Support\Str::limit($av->descricao, 160) }}</div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @empty
        <div class="vazio">Nenhuma avaliação agendada neste mês.</div>
    @endforelse

    @if($tiposUsados->isNotEmpty())
        <div class="legenda">
            <b>Legenda:</b>
            @foreach($tiposUsados as $t)
                @php $cor = $tipoCores[$t] ?? $tipoCores['outro']; @endphp
                <span class="tipo" style="background:{{ $cor[1] }}; color:{{ $cor[0] }};">{{ $tipoLabel[$t] ?? $t }}</span>
            @endforeach
        </div>
    @endif

    <div class="footer">
        {{ config('app.name') }} — agenda gerada pelo sistema escolar
        <span class="r">Página 1</span>
    </div>
</body>
</html>
