<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirme seu e-mail</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: #f0f2f6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wrap {
            width: 100%;
            max-width: 460px;
            padding: 1rem;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow:
                0 0 0 1px rgba(0,0,0,.05),
                0 4px 6px rgba(0,0,0,.04),
                0 16px 48px rgba(0,0,0,.08);
        }

        .icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: rgba(30,79,216,.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: .5rem;
            color: #111827;
        }

        .text {
            font-size: .85rem;
            color: #6b7280;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            padding: 0 1.25rem;
            background: #1e4fd8;
            color: #fff;
            font-size: .85rem;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            transition: .15s;
        }

        .btn:hover {
            background: #1338b0;
        }
    </style>
</head>

<body>

<div class="wrap">
    <div class="card">

        <div class="icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                <path d="M4 4h16v16H4z" stroke="#1e4fd8" stroke-width="1.5"/>
                <path d="M4 6l8 6 8-6" stroke="#1e4fd8" stroke-width="1.5"/>
            </svg>
        </div>

        <div class="title">
            Confirme seu e-mail
        </div>

        <div class="text">
            Seu cadastro foi realizado com sucesso.<br>
            Enviamos um link de confirmação para o seu e-mail.
        </div>

        <div class="text" style="margin-bottom: 2rem;">
            Verifique sua caixa de entrada e clique no link para ativar sua conta.
        </div>

        <a href="<?php echo e(route('login')); ?>" class="btn">
            Ir para login →
        </a>

    </div>
</div>

</body>
</html><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\auth\register-success.blade.php ENDPATH**/ ?>