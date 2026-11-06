<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Recibo — Mensalidade #{{ $mensalidade->id }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    .cab { text-align: center; border-bottom: 3px double #1d4ed8; padding-bottom: 10px; margin-bottom: 20px; }
    .cab h1 { font-size: 17px; margin: 0; color: #1d4ed8; text-transform: uppercase; }
    .cab h2 { font-size: 13px; margin: 4px 0 0; font-weight: normal; color: #444; }
    .cab .end { font-size: 10px; color: #666; margin-top: 4px; }
    .titulo { text-align: center; font-size: 15px; font-weight: bold; letter-spacing: 2px; margin: 18px 0 16px; }
    table.dados { width: 100%; border-collapse: collapse; margin: 10px 0 16px; }
    table.dados td { border: 1px solid #bbb; padding: 6px 10px; }
    table.dados td.lbl { background: #f1f5f9; font-weight: bold; width: 32%; font-size: 10px; text-transform: uppercase; }
    .valor-box { border: 2px solid #1d4ed8; border-radius: 8px; padding: 14px; text-align: center; margin: 14px 0; }
    .valor-box .num { font-size: 22px; font-weight: bold; color: #1d4ed8; }
    .valor-box .ext { font-size: 10px; color: #555; font-style: italic; margin-top: 4px; }
    .recibo-n { font-size: 10px; color: #888; text-align: right; margin-bottom: 6px; }
    .texto { line-height: 1.6; text-align: justify; margin-bottom: 14px; }
    .assinatura { margin-top: 64px; text-align: center; }
    .assinatura .linha { border-top: 1px solid #333; width: 300px; margin: 0 auto; padding-top: 6px; font-size: 10px; color: #555; }
    .rodape { margin-top: 26px; font-size: 9px; color: #888; text-align: center; }
</style>
</head>
<body>
    @php
        $matricula = $mensalidade->matricula;
        $aluno = $matricula?->aluno;
        $turma = $matricula?->turma;
        $escolaDoc = $turma?->escola;
        $competencia = \Carbon\Carbon::createFromFormat('Y-m', $mensalidade->competencia)->format('m/Y');
    @endphp

    <div class="recibo-n">Recibo nº {{ $mensalidade->id }}</div>

    <div class="cab">
        <h1>{{ $escolaDoc->nome ?? 'Escola' }}</h1>
        @if($escolaDoc?->endereco)<div class="end">{{ $escolaDoc->endereco }}</div>@endif
        @if($escolaDoc?->telefone)<div class="end">Tel: {{ $escolaDoc->telefone }}</div>@endif
        <h2>Recibo de Pagamento</h2>
    </div>

    <div class="titulo">RECIBO</div>

    <table class="dados">
        <tr>
            <td class="lbl">Recebemos de</td>
            <td>{{ $aluno?->pessoa?->nome ?? '—' }} (aluno(a))</td>
        </tr>
        <tr>
            <td class="lbl">Referente a</td>
            <td>Mensalidade — competência {{ $competencia }}@if($turma) · Turma {{ $turma->nome }}@endif</td>
        </tr>
        @if($mensalidade->planoMensalidade)
        <tr>
            <td class="lbl">Plano</td>
            <td>{{ $mensalidade->planoMensalidade->nome }}</td>
        </tr>
        @endif
        <tr>
            <td class="lbl">Forma de pagamento</td>
            <td>{{ \App\Models\Mensalidade::FORMAS_PAGAMENTO[$mensalidade->forma_pagamento] ?? $mensalidade->forma_pagamento ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Data do pagamento</td>
            <td>{{ $mensalidade->data_pagamento?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Vencimento original</td>
            <td>{{ $mensalidade->vencimento?->format('d/m/Y') }}</td>
        </tr>
    </table>

    <div class="valor-box">
        <div class="num">R$ {{ number_format($mensalidade->valor, 2, ',', '.') }}</div>
        <div class="ext">{{ \App\Support\ValorExtenso::converter((float) $mensalidade->valor) }}</div>
    </div>

    <div class="texto">
        Declaramos para os devidos fins que recebemos a importância acima discriminada,
        referente à mensalidade escolar indicada, deixando quitada a obrigação correspondente
        à competência informada.
    </div>

    <div class="assinatura">
        <div class="linha">{{ $escolaDoc?->nome ?? 'Direção' }}<br>Direção / Secretaria</div>
    </div>

    <div class="rodape">
        Recibo emitido eletronicamente em {{ now()->format('d/m/Y \à\s H:i') }}.
    </div>
</body>
</html>
