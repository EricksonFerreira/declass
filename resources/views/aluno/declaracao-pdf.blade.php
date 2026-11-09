<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Declaração de Matrícula — {{ $aluno->nome }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    .cab { text-align: center; border-bottom: 3px double #1d4ed8; padding-bottom: 10px; margin-bottom: 24px; }
    .cab h1 { font-size: 17px; margin: 0; color: #1d4ed8; text-transform: uppercase; }
    .cab h2 { font-size: 13px; margin: 4px 0 0; font-weight: normal; color: #444; }
    .cab .end { font-size: 10px; color: #666; margin-top: 4px; }
    .titulo { text-align: center; font-size: 14px; font-weight: bold; letter-spacing: 1px; margin: 26px 0 20px; text-decoration: underline; }
    .texto { line-height: 1.7; text-align: justify; margin-bottom: 18px; }
    .destaque { font-weight: bold; }
    table.dados { width: 100%; border-collapse: collapse; margin: 14px 0 20px; }
    table.dados td { border: 1px solid #bbb; padding: 6px 10px; width: 50%; }
    table.dados td.lbl { background: #f1f5f9; font-weight: bold; width: 30%; font-size: 10px; text-transform: uppercase; }
    .assinatura { margin-top: 70px; text-align: center; }
    .assinatura .linha { border-top: 1px solid #333; width: 320px; margin: 0 auto; padding-top: 6px; font-size: 10px; color: #555; }
    .local-data { margin-top: 30px; font-size: 11px; }
    .rodape { margin-top: 30px; font-size: 9px; color: #888; text-align: center; }
</style>
</head>
<body>
    @php
        $turma = $matricula?->turma;
        $escolaDoc = $turma?->escola ?? $aluno->escola;
        $anoLetivo = $turma?->anoLetivo;
    @endphp

    <div class="cab">
        @include('pdf._logo', ['escola' => $escolaDoc])
        <h1>{{ $escolaDoc->nome ?? 'Escola' }}</h1>
        @if($escolaDoc->endereco)<div class="end">{{ $escolaDoc->endereco }}</div>@endif
        @if($escolaDoc->telefone)<div class="end">Tel: {{ $escolaDoc->telefone }}</div>@endif
        <h2>Declaração de Matrícula</h2>
    </div>

    <div class="titulo">DECLARAÇÃO</div>

    <div class="texto">
        Declaramos, para os devidos fins, que <span class="destaque">{{ $aluno->pessoa->nome ?? $aluno->nome }}</span>,
        @if($aluno->pessoa?->data_nascimento)
            nascido(a) em <span class="destaque">{{ $aluno->pessoa->data_nascimento->format('d/m/Y') }}</span>,
        @endif
        encontra-se regularmente matriculado(a) nesta instituição de ensino,
        @if($turma) na turma <span class="destaque">{{ $turma->nome }}</span>@endif
        @if($turma?->etapa), etapa <span class="destaque">{{ $turma->etapa->nome }}</span>@endif
        @if($anoLetivo), no ano letivo de <span class="destaque">{{ $anoLetivo->ano }}</span>@endif,
        conforme registros acadêmicos desta escola.
    </div>

    <table class="dados">
        <tr>
            <td class="lbl">Aluno(a)</td>
            <td>{{ $aluno->pessoa->nome ?? $aluno->nome }}</td>
        </tr>
        @if($aluno->pessoa?->data_nascimento)
        <tr>
            <td class="lbl">Data de nascimento</td>
            <td>{{ $aluno->pessoa->data_nascimento->format('d/m/Y') }}</td>
        </tr>
        @endif
        <tr>
            <td class="lbl">Registro acadêmico</td>
            <td>{{ $aluno->registro_academico ?? '—' }}</td>
        </tr>
        @if($matricula?->numero_chamada !== null)
        <tr>
            <td class="lbl">Nº de chamada</td>
            <td>{{ $matricula->numero_chamada }}</td>
        </tr>
        @endif
        <tr>
            <td class="lbl">Escola</td>
            <td>{{ $escolaDoc->nome ?? '—' }}</td>
        </tr>
        @if($turma)
        <tr>
            <td class="lbl">Turma</td>
            <td>{{ $turma->nome }}@if($turma->etapa) — {{ $turma->etapa->nome }}@endif</td>
        </tr>
        @endif
        @if($anoLetivo)
        <tr>
            <td class="lbl">Ano letivo</td>
            <td>{{ $anoLetivo->ano }}</td>
        </tr>
        @endif
        @if($responsavel?->pessoa)
        <tr>
            <td class="lbl">Responsável legal</td>
            <td>{{ $responsavel->pessoa->nome }}</td>
        </tr>
        @endif
    </table>

    <div class="local-data">
        {{ $escolaDoc->cidade ?? '' }}@if($escolaDoc->cidade && $escolaDoc->estado), @endif{{ $escolaDoc->estado ?? '' }},
        {{ now()->format('d \d\e F \d\e Y') }}.
    </div>

    <div class="assinatura">
        <div class="linha">{{ $escolaDoc->nome ?? 'Direção' }}<br>Direção / Secretaria</div>
    </div>

    <div class="rodape">
        Documento emitido eletronicamente em {{ now()->format('d/m/Y \à\s H:i') }}.
    </div>
</body>
</html>
