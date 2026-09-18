<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acesso criado</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background: #f0f2f6;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.25rem 1rem;
        }
        .wrap { width: 100%; max-width: 520px; }
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 0 0 1px rgba(0,0,0,.05), 0 4px 6px rgba(0,0,0,.04), 0 16px 48px rgba(0,0,0,.09);
            overflow: hidden;
        }
        .head { padding: 1.6rem 1.8rem 1.25rem; border-bottom: 1px solid #eef2f7; text-align: center; }
        .title { font-size: 1.15rem; font-weight: 900; letter-spacing: -.02em; margin-bottom: .35rem; }
        .sub { font-size: .85rem; color: #64748b; line-height: 1.5; }
        .body { padding: 1.6rem 1.8rem 1.8rem; }
        .pill {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 12px 14px;
            background: #f8fafc;
            margin-bottom: 14px;
            font-size: .86rem;
        }
        /* .pill code { font-weight: 800; } */
        .btn {
            height: 44px;
            border-radius: 12px;
            padding: 0 1rem;
            font-weight: 900;
            font-size: .86rem;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: .15s;
            width: 100%;
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            border-color: #2563eb;
            color: #fff;
            box-shadow: 0 12px 24px rgba(37,99,235,.18);
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 16px 30px rgba(37,99,235,.22); }
        .warn {
            margin-top: 12px;
            font-size: .82rem;
            color: #475569;
            line-height: 1.55;
        }
        .warn strong { color: #0f172a; }
    </style>
</head>

<body>
<div class="wrap">
    <div class="card">
        <div class="head">
            <div class="title">Acesso criado</div>
            <div class="sub">Use as credenciais abaixo para entrar no painel.</div>
        </div>

        <div class="body">
            <?php if(empty($email) || empty($senha)): ?>
                <div class="pill">
                    Não foi possivel recuperar as credenciais desta sessão. Tente cadastrar novamente ou fale com o suporte.
                </div>
            <?php else: ?>
                <div class="pill">
                    <?php if($slug): ?>
                        <div><strong>Slug da empresa:</strong> <code><?php echo e($slug); ?></code></div>
                    <?php endif; ?>
                    <div style="margin-bottom:6px;"><strong>E-mail:</strong> <code><?php echo e($email); ?></code></div>
                    <div style="margin-bottom:6px;"><strong>Senha:</strong> <code><?php echo e($senha); ?></code></div>
                </div>
            <?php endif; ?>

            <a class="btn" href="<?php echo e(route('login')); ?>">Ir para login</a>

            <div class="warn">
                <strong>Primeiro acesso:</strong> ao entrar, o sistema vai obrigar a definir uma nova senha.
                <br>
                <strong>Aviso:</strong> os modulos do sistema ficam bloqueados ate o pagamento do plano ser confirmado.
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\auth\cadastro-empresa-success.blade.php ENDPATH**/ ?>