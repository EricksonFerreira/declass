<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Suas credenciais de acesso — DECLOCK</title>
<style>
  body { margin:0; padding:0; background:#f4f6f9; font-family: 'Segoe UI', Arial, sans-serif; color:#333; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
  .header { background:#0a1628; padding:32px 40px; text-align:center; }
  .header h1 { color:#fff; font-size:1.4rem; margin:0; font-weight:700; letter-spacing:.01em; }
  .body { padding:36px 40px; }
  .greeting { font-size:1.05rem; font-weight:700; color:#0a1628; margin-bottom:12px; }
  p { font-size:.92rem; line-height:1.7; color:#444; margin:0 0 16px; }
  .cred-box { background:#fff8e1; border:1px solid #fde68a; border-radius:10px; padding:20px 24px; margin:24px 0; }
  .cred-box .label { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#92400e; margin-bottom:4px; }
  .cred-box .value { font-family:monospace; font-size:1rem; font-weight:700; color:#78350f; margin-bottom:14px; background:#fffbeb; padding:6px 10px; border-radius:6px; display:inline-block; }
  .cred-box .value:last-child { margin-bottom:0; }
  .alert { background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:12px 16px; font-size:.83rem; color:#b91c1c; margin:16px 0; line-height:1.6; }
  .btn { display:inline-block; background:#0a1628; color:#fff !important; text-decoration:none; padding:13px 32px; border-radius:8px; font-weight:700; font-size:.9rem; margin-top:8px; }
  .footer { background:#f8fafc; border-top:1px solid #e5e7eb; padding:20px 40px; text-align:center; font-size:.78rem; color:#9ca3af; line-height:1.6; }
  .divider { border:none; border-top:1px solid #f0f0f0; margin:24px 0; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>🔑 Suas credenciais de acesso</h1>
  </div>

  <div class="body">
    <div class="greeting">Olá, <?php echo e($admin->nome); ?>!</div>

    <p>
      Sua conta de administrador foi criada na plataforma <strong>DECLOCK</strong>
      para a empresa <strong><?php echo e($empresa->nome); ?></strong>.
      Use as credenciais abaixo para fazer seu primeiro acesso.
    </p>

    <div class="cred-box">
      <div class="label">URL de acesso</div>
      <div class="value"><?php echo e(config('app.url')); ?>/login</div>

      <div class="label">Slug da empresa</div>
      <div class="value"><?php echo e($empresa->slug); ?></div>

      <div class="label">E-mail</div>
      <div class="value"><?php echo e($admin->email); ?></div>

      <div class="label">Senha temporária</div>
      <div class="value"><?php echo e($senhaTemporaria); ?></div>
    </div>

    <div class="alert">
      ⚠️ <strong>Troque sua senha no primeiro acesso.</strong>
      Esta senha é temporária e foi gerada automaticamente.
      Não compartilhe estas credenciais com ninguém.
    </div>

    <p style="text-align:center;">
      <a href="<?php echo e(config('app.url')); ?>/login" class="btn">Fazer primeiro acesso</a>
    </p>

    <hr class="divider">

    <p style="font-size:.82rem; color:#9ca3af;">
      Se você não solicitou este cadastro, ignore este e-mail ou entre em contato
      com o suporte respondendo esta mensagem.
    </p>
  </div>

  <div class="footer">
    DECLOCK — Sistema de Gestão de Tempo e Projetos<br>
    Este é um e-mail automático, por favor não responda diretamente.
  </div>
</div>
</body>
</html>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\emails\cadastro\boas-vindas-admin.blade.php ENDPATH**/ ?>