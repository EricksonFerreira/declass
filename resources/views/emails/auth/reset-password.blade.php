<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Redefinição de senha</title>
<style>
  body { margin:0; padding:0; background:#f4f6f9; font-family: Arial, Helvetica, sans-serif; color:#1f2937; }
  .wrapper { max-width:640px; margin:28px auto; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 10px 32px rgba(15,23,42,.08); }
  .header { background:linear-gradient(135deg, #0f172a, #1e293b); padding:28px 36px; text-align:center; color:#fff; }
  .header img { max-width:140px; max-height:64px; object-fit:contain; display:block; margin:0 auto 14px; }
  .header h1 { margin:0; font-size:1.35rem; line-height:1.25; }
  .header p { margin:8px 0 0; font-size:.92rem; color:rgba(255,255,255,.82); }
  .body { padding:34px 36px 26px; }
  .greeting { font-size:1.02rem; font-weight:700; margin-bottom:12px; color:#0f172a; }
  p { font-size:.94rem; line-height:1.7; margin:0 0 14px; color:#334155; }
  .box { background:#f8fbff; border:1px solid #dbeafe; border-radius:12px; padding:18px 18px 8px; margin:22px 0; }
  .box__title { font-size:.75rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#64748b; margin-bottom:10px; }
  .detail { margin-bottom:10px; }
  .detail__label { display:block; font-size:.72rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:3px; }
  .detail__value { font-size:.94rem; color:#0f172a; }
  .btn { display:inline-block; background:#2563eb; color:#fff !important; text-decoration:none; padding:13px 24px; border-radius:10px; font-weight:700; font-size:.92rem; margin:12px 0 4px; }
  .alert { background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:14px 16px; font-size:.86rem; color:#9a3412; line-height:1.6; margin:18px 0; }
  .footer { background:#f8fafc; border-top:1px solid #e5e7eb; padding:18px 36px; text-align:center; font-size:.78rem; color:#64748b; line-height:1.6; }
  .muted { color:#64748b; }
  .company { margin-top:4px; font-size:.86rem; color:#475569; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    @if(!empty($empresa?->logo_url))
      <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}">
    @else
      <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="Logo do sistema">
    @endif
    <h1>Redefinição de senha</h1>
    <p>Recebemos um pedido para alterar a senha da sua conta.</p>
  </div>

  <div class="body">
    <div class="greeting">Olá, {{ $user->nome ?? $user->name ?? 'usuário' }}!</div>

    <p>
      Para criar uma nova senha de acesso, clique no botão abaixo.
      Se você não solicitou essa alteração, pode ignorar este e-mail com segurança.
    </p>

    <p style="text-align:center;">
      <a href="{{ $url }}" class="btn">Redefinir senha</a>
    </p>

    <div class="box">
      <div class="box__title">Dados da empresa</div>

      <div class="detail">
        <span class="detail__label">Empresa</span>
        <div class="detail__value">{{ $empresa?->nome ?? config('adminlte.title', 'Sistema') }}</div>
      </div>

      @if(!empty($empresa?->dominio))
        <div class="detail">
          <span class="detail__label">Domínio</span>
          <div class="detail__value">{{ $empresa->dominio }}</div>
        </div>
      @endif

      @if(!empty($empresa?->email))
        <div class="detail">
          <span class="detail__label">E-mail</span>
          <div class="detail__value">{{ $empresa->email }}</div>
        </div>
      @endif

      @if(!empty($empresa?->telefone))
        <div class="detail">
          <span class="detail__label">Telefone</span>
          <div class="detail__value">{{ $empresa->telefone }}</div>
        </div>
      @endif
    </div>

    <div class="alert">
      <strong>Segurança:</strong> esse link expira em segurança conforme a política do sistema.
      Se alguém solicitou isso por engano, nenhuma ação adicional é necessária.
    </div>

    <p class="muted" style="font-size:.84rem;">
      Caso o botão não funcione, copie e cole este link no navegador:
      <br>
      <span style="word-break:break-all;">{{ $url }}</span>
    </p>
  </div>

  <div class="footer">
    {{ $empresa?->nome ?? config('adminlte.title', 'Sistema') }}<br>
    E-mail automático enviado pelo sistema. Não responda esta mensagem.
  </div>
</div>
</body>
</html>
