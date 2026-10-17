<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('adminlte.title', 'Sistema') }} — Verificar e-mail</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{min-height:100%;font-family:'Inter',system-ui,sans-serif;background:#f0f2f6;color:#111827;-webkit-font-smoothing:antialiased}
        .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
        .wrap{width:100%;max-width:420px;display:flex;flex-direction:column;align-items:center;gap:1.5rem}
        .logo{display:flex;align-items:center;gap:.6rem}
        .logo__mark{width:38px;height:38px;border-radius:11px;background:#1e4fd8;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(30,79,216,.35)}
        .logo__name{font-size:1.15rem;font-weight:600;color:#111827;letter-spacing:-.02em}
        .card{background:#fff;border-radius:18px;width:100%;box-shadow:0 0 0 1px rgba(0,0,0,.05),0 4px 6px rgba(0,0,0,.04),0 16px 48px rgba(0,0,0,.09);overflow:hidden}
        .card__body{padding:2.25rem 2.375rem 2rem}

        /* Ilustração central */
        .email-icon{
            width:68px;height:68px;border-radius:18px;
            background:#eff4ff;
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 1.5rem;
            color:#1e4fd8;
        }
        .fhead__title{font-size:1.2rem;font-weight:600;color:#111827;letter-spacing:-.025em;line-height:1.3;margin-bottom:.5rem;text-align:center}
        .fhead__sub{font-size:.815rem;color:#6b7280;line-height:1.65;text-align:center;margin-bottom:1.5rem}

        /* Flash de sucesso */
        .flash{display:flex;align-items:flex-start;gap:.6rem;padding:.85rem 1rem;border-radius:10px;font-size:.815rem;margin-bottom:1.25rem;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}

        /* Divider */
        .divider{display:flex;align-items:center;gap:.75rem;margin:1.25rem 0}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:#f3f4f6}
        .divider__text{font-size:.74rem;color:#c3cad4;white-space:nowrap}

        /* Botão primário */
        .btn{width:100%;height:44px;display:flex;align-items:center;justify-content:center;gap:.45rem;background:#1e4fd8;color:#fff;font-size:.875rem;font-weight:600;font-family:'Inter',sans-serif;letter-spacing:.01em;border:none;border-radius:10px;cursor:pointer;transition:background .15s,transform .1s,box-shadow .15s;box-shadow:0 1px 3px rgba(30,79,216,.3),0 4px 14px rgba(30,79,216,.2)}
        .btn:hover{background:#1338b0;transform:translateY(-1px);box-shadow:0 2px 6px rgba(30,79,216,.35),0 8px 22px rgba(30,79,216,.22)}
        .btn:active{transform:translateY(0);background:#1030a0}

        /* Botão secundário (sair) */
        .btn-ghost{width:100%;height:40px;display:flex;align-items:center;justify-content:center;gap:.4rem;background:none;color:#6b7280;font-size:.825rem;font-weight:500;font-family:'Inter',sans-serif;border:1.5px solid #e9eaee;border-radius:10px;cursor:pointer;transition:border-color .15s,color .15s,background .15s}
        .btn-ghost:hover{border-color:#d1d5db;color:#374151;background:#f8f9fc}
    </style>
</head>
<body>
<div class="page">
    <div class="wrap">

        <div class="logo">
            <div class="logo__mark">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 1L18.5 6v8L10 19 1.5 14V6L10 1z" fill="white" fill-opacity=".95"/>
                    <path d="M10 5.5L15 8.5v6L10 17 5 14.5v-6L10 5.5z" fill="white" fill-opacity=".28"/>
                </svg>
            </div>
            <span class="logo__name">{{ config('adminlte.title', 'Sistema') }}</span>
        </div>

        <div class="card">
            <div class="card__body">

                {{-- Ícone --}}
                <div class="email-icon">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                        <rect x="2" y="7" width="28" height="20" rx="3" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M2 11l14 9 14-9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        <circle cx="24" cy="10" r="5" fill="#1e4fd8"/>
                        <path d="M21.5 10l2 2 3-3" stroke="white" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <h1 class="fhead__title">Verifique seu e-mail</h1>
                <p class="fhead__sub">
                    Obrigado por se registrar! Antes de continuar, verifique sua caixa de entrada e clique no link de confirmação que enviamos para você.
                </p>

                {{-- Flash: link reenviado --}}
                @if (session('status') == 'verification-link-sent')
                    <div class="flash">
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none" style="flex-shrink:0;margin-top:1px">
                            <circle cx="7.5" cy="7.5" r="6.5" stroke="currentColor" stroke-width="1.2"/>
                            <path d="M5 7.5l2 2 3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Um novo link de verificação foi enviado para o endereço de e-mail cadastrado.
                    </div>
                @endif

                {{-- Reenviar link --}}
                !DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('adminlte.title', 'Sistema') }} — Verificar e-mail</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{min-height:100%;font-family:'Inter',system-ui,sans-serif;background:#f0f2f6;color:#111827;-webkit-font-smoothing:antialiased}
        .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
        .wrap{width:100%;max-width:420px;display:flex;flex-direction:column;align-items:center;gap:1.5rem}
        .logo{display:flex;align-items:center;gap:.6rem}
        .logo__mark{width:38px;height:38px;border-radius:11px;background:#1e4fd8;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(30,79,216,.35)}
        .logo__name{font-size:1.15rem;font-weight:600;color:#111827;letter-spacing:-.02em}
        .card{background:#fff;border-radius:18px;width:100%;box-shadow:0 0 0 1px rgba(0,0,0,.05),0 4px 6px rgba(0,0,0,.04),0 16px 48px rgba(0,0,0,.09);overflow:hidden}
        .card__body{padding:2.25rem 2.375rem 2rem}

        /* Ilustração central */
        .email-icon{
            width:68px;height:68px;border-radius:18px;
            background:#eff4ff;
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 1.5rem;
            color:#1e4fd8;
        }
        .fhead__title{font-size:1.2rem;font-weight:600;color:#111827;letter-spacing:-.025em;line-height:1.3;margin-bottom:.5rem;text-align:center}
        .fhead__sub{font-size:.815rem;color:#6b7280;line-height:1.65;text-align:center;margin-bottom:1.5rem}

        /* Flash de sucesso */
        .flash{display:flex;align-items:flex-start;gap:.6rem;padding:.85rem 1rem;border-radius:10px;font-size:.815rem;margin-bottom:1.25rem;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}

        /* Divider */
        .divider{display:flex;align-items:center;gap:.75rem;margin:1.25rem 0}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:#f3f4f6}
        .divider__text{font-size:.74rem;color:#c3cad4;white-space:nowrap}

        /* Botão primário */
        .btn{width:100%;height:44px;display:flex;align-items:center;justify-content:center;gap:.45rem;background:#1e4fd8;color:#fff;font-size:.875rem;font-weight:600;font-family:'Inter',sans-serif;letter-spacing:.01em;border:none;border-radius:10px;cursor:pointer;transition:background .15s,transform .1s,box-shadow .15s;box-shadow:0 1px 3px rgba(30,79,216,.3),0 4px 14px rgba(30,79,216,.2)}
        .btn:hover{background:#1338b0;transform:translateY(-1px);box-shadow:0 2px 6px rgba(30,79,216,.35),0 8px 22px rgba(30,79,216,.22)}
        .btn:active{transform:translateY(0);background:#1030a0}

        /* Botão secundário (sair) */
        .btn-ghost{width:100%;height:40px;display:flex;align-items:center;justify-content:center;gap:.4rem;background:none;color:#6b7280;font-size:.825rem;font-weight:500;font-family:'Inter',sans-serif;border:1.5px solid #e9eaee;border-radius:10px;cursor:pointer;transition:border-color .15s,color .15s,background .15s}
        .btn-ghost:hover{border-color:#d1d5db;color:#374151;background:#f8f9fc}
    </style>
</head>
<body>
<div class="page">
    <div class="wrap">

        <div class="logo">
            <div class="logo__mark">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 1L18.5 6v8L10 19 1.5 14V6L10 1z" fill="white" fill-opacity=".95"/>
                    <path d="M10 5.5L15 8.5v6L10 17 5 14.5v-6L10 5.5z" fill="white" fill-opacity=".28"/>
                </svg>
            </div>
            <span class="logo__name">{{ config('adminlte.title', 'Sistema') }}</span>
        </div>

        <div class="card">
            <div class="card__body">

                {{-- Ícone --}}
                <div class="email-icon">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                        <rect x="2" y="7" width="28" height="20" rx="3" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M2 11l14 9 14-9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        <circle cx="24" cy="10" r="5" fill="#1e4fd8"/>
                        <path d="M21.5 10l2 2 3-3" stroke="white" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <h1 class="fhead__title">Verifique seu e-mail</h1>
                <p class="fhead__sub">
                    Obrigado por se registrar! Antes de continuar, verifique sua caixa de entrada e clique no link de confirmação que enviamos para você.
                </p>

                {{-- Flash: link reenviado --}}
                @if (session('status') == 'verification-link-sent')
                    <div class="flash">
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none" style="flex-shrink:0;margin-top:1px">
                            <circle cx="7.5" cy="7.5" r="6.5" stroke="currentColor" stroke-width="1.2"/>
                            <path d="M5 7.5l2 2 3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Um novo link de verificação foi enviado para o endereço de e-mail cadastrado.
                    </div>
                @endif

                {{-- Reenviar link --}}
                @include('partials.form-errors-banner')
<form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M1 7s2.5-5 6-5 6 5 6 5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                            <path d="M9.5 4.5L13 7l-3.5 2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Reenviar e-mail de verificação
                    </button>
                </form>

                <div class="divider">
                    <span class="divider__text">ou</span>
                </div>

                {{-- Sair --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                            <path d="M5 11.5H2.5A1.5 1.5 0 011 10V3A1.5 1.5 0 012.5 1.5H5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                            <path d="M8.5 9.5L12 6.5l-3.5-3M12 6.5H4.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Sair da conta
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>
<script src="{{ asset('js/form-ux.js') }}"></script>
</body>
</html>