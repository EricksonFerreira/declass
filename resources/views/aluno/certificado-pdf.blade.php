<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Certificado de Conclusão — {{ $aluno->nome }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
    .borda { border: 6px double #1d4ed8; padding: 40px 50px; text-align: center; }
    .cab h1 { font-size: 18px; margin: 0; color: #1d4ed8; text-transform: uppercase; letter-spacing: 1px; }
    .cab .sub { font-size: 10px; color: #666; margin-top: 6px; }
    .titulo { font-size: 20px; font-weight: bold; letter-spacing: 3px; color: #1e3a8a; margin: 26px 0 24px; text-decoration: underline; }
    .texto { line-height: 1.8; text-align: justify; margin: 0 auto 22px; max-width: 560px; }
    .destaque { font-weight: bold; color: #1d4ed8; }
    .curso { font-size: 14px; font-weight: bold; margin: 10px 0; }
    table.dados { margin: 18px auto 8px; border-collapse: collapse; }
    table.dados td { border: 1px solid #bbb; padding: 5px 12px; font-size: 11px; }
    table.dados td.lbl { background: #eff6ff; font-weight: bold; text-transform: uppercase; font-size: 9px; }
    .assinatura { margin-top: 80px; }
    .assinatura .linha { border-top: 1px solid #333; width: 340px; margin: 0 auto; padding-top: 6px; font-size: 10px; color: #555; }
    .local-data { margin-top: 36px; font-size: 11px; }
    .rodape { margin-top: 40px; font-size: 9px; color: #888; }
</style>
</head>
<body>
    @php
        $turma = $matricula?->turma;
        $escolaDoc = $turma?->escola ?? $aluno->escola;
        $anoLetivo = $turma?->anoLetivo;
        $etapa = $turma?->etapa;
    @endphp

    <div class="borda">
        <div class="cab">
            @include('pdf._logo', ['escola' => $escolaDoc])
            <h1>{{ $escolaDoc->nome ?? 'Escola' }}</h1>
            @if($escolaDoc->endereco)<div class="sub">{{ $escolaDoc->endereco }}</div>@endif
            @if($escolaDoc->telefone)<div class="sub">Tel: {{ $escolaDoc->telefone }}</div>@endif
        </div>

        <div class="titulo">CERTIFICADO DE CONCLUSÃO</div>

        <div class="texto">
            Certificamos que <span class="destaque">{{ $aluno->pessoa->nome ?? $aluno->nome }}</span>,
            @if($aluno->pessoa?->data_nascimento)
                nascido(a) em <span class="destaque">{{ $aluno->pessoa->data_nascimento->format('d/m/Y') }}</span>,
            @endif
            concluiu com êxito
            @if($etapa) a etapa <span class="destaque">{{ $etapa->nome }}</span>@endif
            @if($turma) na turma <span class="destaque">{{ $turma->nome }}</span>@endif
            @if($anoLetivo), no ano letivo de <span class="destaque">{{ $anoLetivo->ano }}</span>@endif,
            conforme consta nos registros acadêmicos desta instituição de ensino.
        </div>

        <table class="dados">
            <tr>
                <td class="lbl">Aluno(a)</td>
                <td>{{ $aluno->pessoa->nome ?? $aluno->nome }}</td>
            </tr>
            @if($aluno->pessoa?->data_nascimento)
            <tr>
                <td class="lbl">Nascimento</td>
                <td>{{ $aluno->pessoa->data_nascimento->format('d/m/Y') }}</td>
            </tr>
            @endif
            <tr>
                <td class="lbl">Registro acadêmico</td>
                <td>{{ $aluno->registro_academico ?? '—' }}</td>
            </tr>
            @if($etapa)
            <tr>
                <td class="lbl">Etapa concluída</td>
                <td>{{ $etapa->nome }}</td>
            </tr>
            @endif
            @if($turma)
            <tr>
                <td class="lbl">Turma</td>
                <td>{{ $turma->nome }}</td>
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
            <div class="linha">{{ $escolaDoc->nome ?? 'Direção' }}<br>Direção</div>
        </div>

        <div class="rodape">
            Documento emitido eletronicamente em {{ now()->format('d/m/Y \à\s H:i') }}.
        </div>
    </div>
</body>
</html>
