@php
    $corPrimaria = $empresa->cor_primaria ?: '#1F2937';
    $corSecundaria = $empresa->cor_secundaria ?: '#334155';
    $corTerciaria = $empresa->cor_terciaria ?: '#64748B';
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
    <meta name="description" content="{{ $descricao }}">
    <link rel="manifest" href="{{ route('site.manifest') }}">
    <meta name="theme-color" content="#0a1628">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="DeCars">
    <link rel="apple-touch-icon" href="{{ $empresa->logo_url ?: asset('favicons/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
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
            --c1: {{ $corPrimaria }};
            --c2: {{ $corSecundaria }};
            --c3: {{ $corTerciaria }};
            --white: #ffffff;
            --ink: #0f172a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Manrope', sans-serif;
            background: radial-gradient(circle at 15% 20%, rgba(255, 255, 255, .22), transparent 45%),
                        radial-gradient(circle at 90% 0%, rgba(255, 255, 255, .16), transparent 35%),
                        linear-gradient(135deg, var(--c1) 0%, var(--c2) 55%, var(--c3) 100%);
            color: var(--white);
        }

        .wrap {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .hero {
            width: 100%;
            max-width: 860px;
            background: rgba(7, 12, 22, .34);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 22px;
            padding: 28px;
            backdrop-filter: blur(6px);
            box-shadow: 0 18px 42px rgba(2, 6, 23, .3);
            animation: rise .5s ease-out both;
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .top {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }

        .logo {
            width: 62px;
            height: 62px;
            border-radius: 14px;
            background: var(--c1);
            border: 1px solid rgba(255, 255, 255, .34);
            object-fit: contain;
            padding: 6px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255, 255, 255, .88);
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 7px rgba(34, 197, 94, .18);
        }

        h1 {
            margin: 8px 0 10px;
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 2vw + 1rem, 2.8rem);
            line-height: 1.2;
        }

        .subtitle {
            margin: 0 0 14px;
            font-size: clamp(1rem, 1.4vw, 1.2rem);
            color: rgba(255, 255, 255, .92);
        }

        .description {
            margin: 0;
            color: rgba(255, 255, 255, .82);
            line-height: 1.65;
            max-width: 64ch;
        }

        .actions {
            margin-top: 22px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            text-decoration: none;
            border-radius: 999px;
            padding: 12px 18px;
            font-weight: 700;
            font-size: .93rem;
            transition: transform .15s ease, filter .2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
        }

        .btn-primary {
            background: #ffffff;
            color: var(--ink);
        }

        .btn-secondary {
            border: 1px solid rgba(255, 255, 255, .45);
            color: #ffffff;
        }

        .footer {
            margin-top: 16px;
            font-size: 12px;
            color: rgba(255, 255, 255, .75);
        }

        @media (max-width: 640px) {
            .hero {
                padding: 22px;
                border-radius: 16px;
            }

            .top {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="hero">
            <div class="top">
                @if(!empty($empresa->logo_url))
                    <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}" class="logo">
                @endif
                <div>
                    <div class="badge">
                        <span class="dot"></span>
                        site oficial
                    </div>
                    <strong>{{ $empresa->nome }}</strong>
                </div>
            </div>

            <h1>{{ $titulo }}</h1>
            <p class="subtitle">{{ $subtitulo }}</p>
            <p class="description">{{ $descricao }}</p>

            <div class="actions">
                <a class="btn btn-primary" href="{{ $botaoUrl }}">
                    {{ $botaoTexto }}
                </a>
                @if(Route::has('login'))
                    <a class="btn btn-secondary" href="{{ route('login') }}">
                        Acesso administrativo
                    </a>
                @endif
            </div>

            <div class="footer">
                Dominio vinculado: {{ $empresa->dominio }}
            </div>
        </section>
    </main>
</body>
</html>
