<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bem-vindo ao DECars</title>
<style>
  body { margin:0; padding:0; background:#f4f6f9; font-family: 'Segoe UI', Arial, sans-serif; color:#333; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
  .header { background:#0a1628; padding:32px 40px; text-align:center; }
  .header img { height:40px; }
  .header h1 { color:#fff; font-size:1.4rem; margin:16px 0 0; font-weight:700; letter-spacing:.01em; }
  .body { padding:36px 40px; }
  .greeting { font-size:1.05rem; font-weight:700; color:#0a1628; margin-bottom:12px; }
  p { font-size:.92rem; line-height:1.7; color:#444; margin:0 0 16px; }
  .info-box { background:#f0f4ff; border:1px solid #c7d7f8; border-radius:10px; padding:20px 24px; margin:24px 0; }
  .info-box .label { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:4px; }
  .info-box .value { font-size:.95rem; font-weight:700; color:#0a1628; margin-bottom:12px; }
  .info-box .value:last-child { margin-bottom:0; }
  .btn { display:inline-block; background:#0a1628; color:#fff !important; text-decoration:none; padding:13px 32px; border-radius:8px; font-weight:700; font-size:.9rem; margin-top:8px; }
  .footer { background:#f8fafc; border-top:1px solid #e5e7eb; padding:20px 40px; text-align:center; font-size:.78rem; color:#9ca3af; line-height:1.6; }
  .divider { border:none; border-top:1px solid #f0f0f0; margin:24px 0; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>🎉 Bem-vindo ao DECars!</h1>
  </div>

  <div class="body">
    <div class="greeting">Olá, {{ $empresa->nome }}!</div>

    <p>
      Sua empresa foi cadastrada com sucesso na plataforma <strong>DECars</strong>.
      Agora é só seguir os próximos passos para começar a gerenciar sua frota com agilidade.
    </p>

    <div class="info-box">
      <div class="label">Empresa</div>
      <div class="value">{{ $empresa->nome }}</div>

      @if ($empresa->cnpj)
      <div class="label">CNPJ</div>
      <div class="value">{{ $empresa->cnpj }}</div>
      @endif

      <div class="label">Responsável</div>
      <div class="value">{{ $admin->nome }}</div>

      <div class="label">E-mail de acesso</div>
      <div class="value">{{ $admin->email }}</div>
    </div>

    <p>
      O acesso ao sistema está disponível agora. Recomendamos que o administrador
      troque a senha no primeiro acesso.
    </p>

    <p style="text-align:center;">
      <a href="{{ config('app.url') }}/login" class="btn">Acessar o DECars</a>
    </p>

    <hr class="divider">

    <p style="font-size:.82rem; color:#9ca3af;">
      Se você não solicitou este cadastro, entre em contato com nosso suporte
      imediatamente respondendo este e-mail.
    </p>
  </div>

  <div class="footer">
    DECars — Sistema de Gestão de Frotas e Locadoras<br>
    Este é um e-mail automático, por favor não responda diretamente.
  </div>
</div>
</body>
</html>
