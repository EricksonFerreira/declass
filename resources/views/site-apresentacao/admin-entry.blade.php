<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso administrativo | {{ $empresa->nome }}</title>
    <link rel="manifest" href="{{ route('site.manifest') }}">
    <meta name="theme-color" content="{{ $empresa->cor_primaria ?: '#0a1628' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $empresa->nome }}">
    <link rel="apple-touch-icon" href="{{ $empresa->logo_url ?: asset('favicons/favicon.ico') }}">
    <script>
        if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('{{ asset('sw.js') }}', { scope: '/' }).catch(function (error) {
                    console.warn('Service worker registration failed:', error);
                });
            });
        }
    </script>
    <style>
        :root {
            --admin-bg: {{ $corFundo }};
            --admin-text: {{ $corTexto }};
            --card-bg: {{ $corCard }};
            --card-text: {{ $corTextoCard }};
            --btn-primary-bg: {{ $corBotaoFundo }};
            --btn-primary-text: {{ $corBotaoTexto }};
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at 85% 10%, rgba(255, 255, 255, 0.22), transparent 34%), var(--admin-bg);
            color: var(--admin-text);
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .card {
            width: min(560px, 100%);
            background: var(--card-bg);
            color: var(--card-text);
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.28);
            padding: 24px;
            box-shadow: 0 20px 36px rgba(2, 6, 23, .25);
            text-align: center;
        }

        .logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, .18);
            background: var(--admin-bg);
            padding: 6px;
            margin: 0 auto 12px;
            display: block;
        }

        h1 { margin: 0 0 10px; font-size: 1.55rem; }
        p { margin: 0 0 16px; color: var(--card-text); opacity: .85; }

        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            border: 0;
            border-radius: 999px;
            padding: 10px 14px;
            font-size: .86rem;
            font-weight: 700;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--btn-primary-bg);
            color: var(--btn-primary-text);
        }

        .btn-secondary {
            border: 1px solid rgba(71, 85, 105, .35);
            color: var(--card-text);
        }
    </style>
</head>
<body>
    <section class="card">
        @if(!empty($empresa->logo_url))
            <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}" class="logo">
        @endif

        <h1>Painel administrativo da empresa</h1>
        <p>Este acesso e especifico para a operacao da <strong>{{ $empresa->nome }}</strong>.</p>

        <div class="actions">
            <a class="btn btn-primary" href="{{ $loginUrl }}">Entrar na area administrativa</a>
            <a class="btn btn-secondary" href="{{ route('site.apresentacao') }}">Voltar ao site</a>
        </div>
    </section>
</body>
</html>
