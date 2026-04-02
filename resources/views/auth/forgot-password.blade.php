<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('adminlte.title', 'Sistema') }} — Recuperar senha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{min-height:100%;font-family:'Inter',system-ui,sans-serif;background:#f0f2f6;color:#111827;-webkit-font-smoothing:antialiased}
        .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
        .wrap{width:100%;max-width:400px;display:flex;flex-direction:column;align-items:center;gap:1.5rem}
        .logo{display:flex;align-items:center;gap:.6rem}
        .logo__mark{width:38px;height:38px;border-radius:11px;background:#1e4fd8;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(30,79,216,.35)}
        .logo__name{font-size:1.15rem;font-weight:600;color:#111827;letter-spacing:-.02em}
        .card{background:#fff;border-radius:18px;width:100%;box-shadow:0 0 0 1px rgba(0,0,0,.05),0 4px 6px rgba(0,0,0,.04),0 16px 48px rgba(0,0,0,.09);overflow:hidden}
        .card__body{padding:2.25rem 2.375rem 2rem}
        .card__footer{border-top:1px solid #f3f4f6;padding:1.1rem 2.375rem;text-align:center;font-size:.8rem;color:#9ca3af}
        .card__footer a{color:#1e4fd8;font-weight:600;text-decoration:none}
        .card__footer a:hover{text-decoration:underline}
        .fhead{margin-bottom:1.5rem}
        .fhead__icon{width:44px;height:44px;border-radius:12px;background:#eff4ff;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;color:#1e4fd8}
        .fhead__title{font-size:1.2rem;font-weight:600;color:#111827;letter-spacing:-.025em;line-height:1.3;margin-bottom:.3rem}
        .fhead__sub{font-size:.815rem;color:#6b7280;line-height:1.6}
        .flash{display:flex;align-items:flex-start;gap:.6rem;padding:.85rem 1rem;border-radius:10px;font-size:.815rem;margin-bottom:1.25rem;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}
        .field{display:flex;flex-direction:column;gap:.35rem}
        .field__label{font-size:.775rem;font-weight:500;color:#374151;letter-spacing:.005em}
        .field__error{font-size:.74rem;color:#e53e3e;line-height:1.4}
        .input-wrap{position:relative;display:flex;align-items:center}
        .input-wrap__icon{position:absolute;left:.85rem;color:#cbd5e1;display:flex;align-items:center;pointer-events:none;transition:color .15s}
        .input-wrap:focus-within .input-wrap__icon{color:#1e4fd8}
        .input{width:100%;height:43px;padding:0 .875rem 0 2.55rem;font-size:.855rem;font-family:'Inter',sans-serif;color:#111827;background:#f8f9fc;border:1.5px solid #e9eaee;border-radius:10px;outline:none;transition:border-color .15s,background .15s,box-shadow .15s;-webkit-appearance:none}
        .input::placeholder{color:#c5ccd7}
        .input:hover{border-color:#d1d5db;background:#f3f4f8}
        .input:focus{border-color:#1e4fd8;background:#fff;box-shadow:0 0 0 3.5px rgba(30,79,216,.1)}
        .field--error .input{border-color:#f05252;background:#fef7f7}
        .field--error .input:focus{box-shadow:0 0 0 3.5px rgba(240,82,82,.1)}
        .field--error .input-wrap__icon{color:#f05252}
        .btn{width:100%;height:44px;margin-top:1.5rem;display:flex;align-items:center;justify-content:center;gap:.45rem;background:#1e4fd8;color:#fff;font-size:.875rem;font-weight:600;font-family:'Inter',sans-serif;letter-spacing:.01em;border:none;border-radius:10px;cursor:pointer;transition:background .15s,transform .1s,box-shadow .15s;box-shadow:0 1px 3px rgba(30,79,216,.3),0 4px 14px rgba(30,79,216,.2)}
        .btn:hover{background:#1338b0;transform:translateY(-1px);box-shadow:0 2px 6px rgba(30,79,216,.35),0 8px 22px rgba(30,79,216,.22)}
        .btn:active{transform:translateY(0);background:#1030a0}
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

                <div class="fhead">
                    <div class="fhead__icon">
                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                            <rect x="3" y="9" width="16" height="11" rx="2" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M7 9V6a4 4 0 018 0v3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <circle cx="11" cy="14.5" r="1.25" fill="currentColor"/>
                            <path d="M11 15.75v1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h1 class="fhead__title">Recuperar senha</h1>
                    <p class="fhead__sub">Informe seu e-mail e enviaremos um link para você criar uma nova senha.</p>
                </div>

                {{-- Flash de sucesso --}}
                @if (session('status'))
                    <div class="flash">
                        <svg width="15" height="15" viewBox="0 0 15 15" fill="none" style="flex-shrink:0;margin-top:1px">
                            <circle cx="7.5" cy="7.5" r="6.5" stroke="currentColor" stroke-width="1.2"/>
                            <path d="M5 7.5l2 2 3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" novalidate>
                    @csrf

                    <div class="field {{ $errors->has('email') ? 'field--error' : '' }}">
                        <label class="field__label" for="email">E-mail</label>
                        <div class="input-wrap">
                            <span class="input-wrap__icon">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="1" y="3" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M1 5l6 4 6-4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <input id="email" class="input" type="email" name="email"
                                value="{{ old('email') }}"
                                placeholder="seu@email.com"
                                required autofocus autocomplete="username">
                        </div>
                        @error('email')
                            <p class="field__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn">
                        Enviar link de recuperação
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                            <path d="M1 6.5h11M7 2l4.5 4.5L7 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="card__footer">
                Lembrou a senha?
                <a href="{{ route('login') }}">Voltar ao login</a>
            </div>
        </div>

    </div>
</div>
</body>
</html>