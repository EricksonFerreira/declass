<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — @yield('title') | DECLOCK</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0b3d91 0%, #1565c0 55%, #1e88e5 100%);
            padding: 24px;
            color: #1e293b;
        }
        .error-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 24px 60px rgba(2, 6, 23, .35);
            max-width: 480px;
            width: 100%;
            padding: 44px 36px 36px;
            text-align: center;
        }
        .error-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }
        .error-logo img { height: 34px; }
        .error-logo span { font-weight: 800; font-size: 1.15rem; color: #0b3d91; letter-spacing: .04em; }
        .error-code {
            font-size: 4.5rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #0b3d91, #1e88e5);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .error-icon { font-size: 2rem; margin: 10px 0 4px; }
        .error-title { font-size: 1.3rem; font-weight: 700; margin: 8px 0 10px; }
        .error-message { color: #64748b; font-size: .95rem; line-height: 1.55; margin-bottom: 26px; }
        .error-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .error-btn {
            display: inline-block;
            padding: 11px 22px;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            text-decoration: none;
            transition: transform .12s ease, box-shadow .12s ease;
            cursor: pointer;
            border: none;
        }
        .error-btn:hover { transform: translateY(-1px); }
        .error-btn-primary {
            background: #0b3d91;
            color: #fff;
            box-shadow: 0 6px 16px rgba(11, 61, 145, .3);
        }
        .error-btn-outline {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #e2e8f0;
        }
        .error-footer {
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid #eef2f7;
            color: #94a3b8;
            font-size: .75rem;
        }
        @media (max-width: 480px) {
            .error-card { padding: 34px 22px 28px; }
            .error-code { font-size: 3.6rem; }
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-logo">
            <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="DECLOCK">
            <span>DECLOCK</span>
        </div>

        <div class="error-code">@yield('code')</div>
        <div class="error-icon">@yield('icon')</div>
        <h1 class="error-title">@yield('title')</h1>
        <p class="error-message">@yield('message')</p>

        <div class="error-actions">
            @yield('actions')
            <a href="{{ url('/') }}" class="error-btn error-btn-primary">Ir para o início</a>
        </div>

        <div class="error-footer">
            Se o problema persistir, fale com o suporte.
        </div>
    </div>
</body>
</html>
