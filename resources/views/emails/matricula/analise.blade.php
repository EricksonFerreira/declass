<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pré-matrícula {{ $solicitacao->codigo }}</title>
<style>
  body { margin:0; padding:0; background:#f4f6f9; font-family:'Segoe UI', Arial, sans-serif; color:#333; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
  .header { background:#0a1628; padding:32px 40px; text-align:center; }
  .header h1 { color:#fff; font-size:1.4rem; margin:0; font-weight:700; letter-spacing:.01em; }
  .body { padding:36px 40px; }
  .greeting { font-size:1.05rem; font-weight:700; color:#0a1628; margin-bottom:12px; }
  p { font-size:.92rem; line-height:1.7; color:#444; margin:0 0 16px; }
  .info-box { background:#f0f4ff; border:1px solid #c7d7f8; border-radius:10px; padding:20px 24px; margin:24px 0; }
  .info-box .label { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:4px; }
  .info-box .value { font-size:.95rem; font-weight:700; color:#0a1628; margin-bottom:12px; }
  .info-box .value:last-child { margin-bottom:0; }
  .status-aprovada { color:#15803d; }
  .status-recusada { color:#b91c1c; }
  .footer { background:#f8fafc; border-top:1px solid #e5e7eb; padding:20px 40px; text-align:center; font-size:.78rem; color:#9ca3af; line-height:1.6; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>{{ $solicitacao->status === 'aprovada' ? 'Pré-matrícula aprovada' : 'Pré-matrícula não aprovada' }}</h1>
  </div>

  <div class="body">
    <div class="greeting">Olá, {{ $solicitacao->nome_responsavel ?? 'responsável' }}!</div>

    @if ($solicitacao->status === 'aprovada')
      <p>
        Temos uma ótima notícia: a pré-matrícula de
        <strong>{{ $solicitacao->nome_aluno }}</strong> foi
        <strong class="status-aprovada">aprovada</strong>{{ $solicitacao->escola ? ' em '.$solicitacao->escola->nome : '' }}.
      </p>

      <div class="info-box">
        <div class="label">Protocolo</div>
        <div class="value">#{{ $solicitacao->codigo }}</div>

        <div class="label">Aluno(a)</div>
        <div class="value">{{ $solicitacao->nome_aluno }}</div>

        @if ($solicitacao->escola)
          <div class="label">Escola</div>
          <div class="value">{{ $solicitacao->escola->nome }}</div>
        @endif

        @if ($solicitacao->turma)
          <div class="label">Turma</div>
          <div class="value">{{ $solicitacao->turma->nome }}</div>
        @endif
      </div>

      <p>
        A matrícula já está registrada. A escola entrará em contato para
        os próximos passos (documentação e início das aulas).
      </p>
    @else
      <p>
        Informamos que a pré-matrícula de
        <strong>{{ $solicitacao->nome_aluno }}</strong> foi
        <strong class="status-recusada">não aprovada</strong> neste momento.
      </p>

      <div class="info-box">
        <div class="label">Protocolo</div>
        <div class="value">#{{ $solicitacao->codigo }}</div>

        <div class="label">Aluno(a)</div>
        <div class="value">{{ $solicitacao->nome_aluno }}</div>

        @if ($solicitacao->escola)
          <div class="label">Escola</div>
          <div class="value">{{ $solicitacao->escola->nome }}</div>
        @endif

        @if ($solicitacao->motivo_recusa)
          <div class="label">Motivo</div>
          <div class="value">{{ $solicitacao->motivo_recusa }}</div>
        @endif
      </div>

      <p>
        Em caso de dúvidas, entre em contato com a secretaria da escola
        informando o protocolo acima.
      </p>
    @endif
  </div>

  <div class="footer">
    Este é um e-mail automático da plataforma DECLASS. Não responda.
  </div>
</div>
</body>
</html>
