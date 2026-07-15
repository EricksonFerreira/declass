<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $resetEmpresa = null;
        $resetTitle = config('adminlte.title', 'Sistema');
        $resetBrandColor = '#1e4fd8';
        $resetLogoUrl = asset('favicons/favicon.ico');

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('empresas')) {
                $host = \App\Support\DomainNormalizer::normalize(request()->getHost());
                if ($host) {
                    $resetEmpresa = \App\Models\Empresa::query()
                        ->withoutGlobalScopes()
                        ->where('ativo', 'S')
                        ->whereNotNull('dominio')
                        ->get()
                        ->first(function (\App\Models\Empresa $empresa) use ($host) {
                            return \App\Support\DomainNormalizer::normalize($empresa->dominio) === $host;
                        });
                }
            }
        } catch (\Throwable $e) {
            $resetEmpresa = null;
        }

        if ($resetEmpresa) {
            $resetTitle = (string) ($resetEmpresa->nome ?: $resetTitle);
            $resetBrandColor = (string) ($resetEmpresa->cor_primaria ?: $resetBrandColor);
            $resetLogoUrl = $resetEmpresa->logo_url ?: $resetLogoUrl;
        }
    @endphp
    <title>{{ $resetTitle }} - Redefinir senha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{min-height:100%;font-family:'Inter',system-ui,sans-serif;background:#f0f2f6;color:#111827;-webkit-font-smoothing:antialiased}
        .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
        .wrap{width:100%;max-width:400px;display:flex;flex-direction:column;align-items:center;gap:1.5rem}
        .logo{display:flex;align-items:center;gap:.6rem}
        .logo__mark{width:38px;height:38px;border-radius:11px;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(30,79,216,.18);overflow:hidden;border:1px solid rgba(30,79,216,.12)}
        .logo__mark img{width:100%;height:100%;object-fit:contain;display:block}
        .logo__name{font-size:1.15rem;font-weight:600;color:#111827;letter-spacing:-.02em}
        .card{background:#fff;border-radius:18px;width:100%;box-shadow:0 0 0 1px rgba(0,0,0,.05),0 4px 6px rgba(0,0,0,.04),0 16px 48px rgba(0,0,0,.09);overflow:hidden}
        .card__body{padding:2.25rem 2.375rem 2rem}
        .card__footer{border-top:1px solid #f3f4f6;padding:1.1rem 2.375rem;text-align:center;font-size:.8rem;color:#9ca3af}
        .card__footer a{color:{{ $resetBrandColor }};font-weight:600;text-decoration:none}
        .card__footer a:hover{text-decoration:underline}
        .fhead{margin-bottom:1.75rem}
        .fhead__title{font-size:1.2rem;font-weight:600;color:#111827;letter-spacing:-.025em;line-height:1.3;margin-bottom:.3rem}
        .fhead__sub{font-size:.8rem;color:#9ca3af;line-height:1.5}
        .field{display:flex;flex-direction:column;gap:.35rem}
        .field+.field{margin-top:1rem}
        .field__label{font-size:.775rem;font-weight:500;color:#374151;letter-spacing:.005em}
        .field__error{font-size:.74rem;color:#e53e3e;line-height:1.4}
        .input-wrap{position:relative;display:flex;align-items:center}
        .input-wrap__icon{position:absolute;left:.85rem;color:#cbd5e1;display:flex;align-items:center;pointer-events:none;transition:color .15s}
        .input-wrap:focus-within .input-wrap__icon{color:{{ $resetBrandColor }}}
        .input{width:100%;height:43px;padding:0 2.6rem 0 2.55rem;font-size:.855rem;font-family:'Inter',sans-serif;color:#111827;background:#f8f9fc;border:1.5px solid #e9eaee;border-radius:10px;outline:none;transition:border-color .15s,background .15s,box-shadow .15s;-webkit-appearance:none}
        .input::placeholder{color:#c5ccd7}
        .input:hover{border-color:#d1d5db;background:#f3f4f8}
        .input:focus{border-color:{{ $resetBrandColor }};background:#fff;box-shadow:0 0 0 3.5px color-mix(in srgb, {{ $resetBrandColor }} 14%, transparent)}
        .input[readonly]{background:#eef2f7;color:#4b5563;cursor:not-allowed}
        .input[readonly]:hover{border-color:#e9eaee;background:#eef2f7}
        .input[readonly]:focus{border-color:#e9eaee;background:#eef2f7;box-shadow:none}
        .field--error .input{border-color:#f05252;background:#fef7f7}
        .field--error .input:focus{box-shadow:0 0 0 3.5px rgba(240,82,82,.1)}
        .field--error .input-wrap__icon{color:#f05252}
        .eye-btn{position:absolute;right:.7rem;background:none;border:none;padding:5px;cursor:pointer;color:#c5ccd7;display:flex;align-items:center;border-radius:6px;transition:color .15s,background .15s}
        .eye-btn:hover{color:#6b7280;background:#f3f4f6}
        .btn{width:100%;height:44px;margin-top:1.5rem;display:flex;align-items:center;justify-content:center;gap:.45rem;background:{{ $resetBrandColor }};color:#fff;font-size:.875rem;font-weight:600;font-family:'Inter',sans-serif;letter-spacing:.01em;border:none;border-radius:10px;cursor:pointer;transition:background .15s,transform .1s,box-shadow .15s;box-shadow:0 1px 3px color-mix(in srgb, {{ $resetBrandColor }} 30%, transparent),0 4px 14px color-mix(in srgb, {{ $resetBrandColor }} 20%, transparent)}
        .btn:hover{background:color-mix(in srgb, {{ $resetBrandColor }} 88%, #000 12%);transform:translateY(-1px);box-shadow:0 2px 6px color-mix(in srgb, {{ $resetBrandColor }} 35%, transparent),0 8px 22px color-mix(in srgb, {{ $resetBrandColor }} 22%, transparent)}
        .btn:active{transform:translateY(0);background:color-mix(in srgb, {{ $resetBrandColor }} 82%, #000 18%)}
    </style>
</head>
<body>
<div class="page">
    <div class="wrap">

        <div class="logo">
            <div class="logo__mark">
                <img src="{{ $resetLogoUrl }}" alt="Logo {{ $resetTitle }}">
            </div>
            <span class="logo__name">{{ $resetTitle }}</span>
        </div>

        <div class="card">
            <div class="card__body">

                <div class="fhead">
                    <h1 class="fhead__title">Redefinir senha</h1>
                    <p class="fhead__sub">Escolha uma nova senha para sua conta</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

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
                                value="{{ old('email', $request->email) }}"
                                placeholder="seu@email.com"
                                required autofocus autocomplete="username" readonly>
                        </div>
                        @error('email')
                            <p class="field__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field {{ $errors->has('password') ? 'field--error' : '' }}">
                        <label class="field__label" for="password">Nova senha</label>
                        <div class="input-wrap">
                            <span class="input-wrap__icon">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="2" y="6" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M4.5 6V4.5a2.5 2.5 0 015 0V6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                    <circle cx="7" cy="9.5" r=".85" fill="currentColor"/>
                                </svg>
                            </span>
                            <input id="password" class="input" type="password" name="password"
                                placeholder="Minimo 8 caracteres"
                                required autocomplete="new-password">
                            <button type="button" class="eye-btn" data-target="password" tabindex="-1">
                                <svg class="eye-show" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"/><circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"/></svg>
                                <svg class="eye-hide" width="14" height="14" viewBox="0 0 14 14" fill="none" style="display:none"><path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"/><circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"/><line x1="2" y1="2" x2="12" y2="12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="field__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field {{ $errors->has('password_confirmation') ? 'field--error' : '' }}">
                        <label class="field__label" for="password_confirmation">Confirmar nova senha</label>
                        <div class="input-wrap">
                            <span class="input-wrap__icon">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="2" y="6" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M4.5 6V4.5a2.5 2.5 0 015 0V6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                    <circle cx="7" cy="9.5" r=".85" fill="currentColor"/>
                                </svg>
                            </span>
                            <input id="password_confirmation" class="input" type="password" name="password_confirmation"
                                placeholder="Repita a nova senha"
                                required autocomplete="new-password">
                            <button type="button" class="eye-btn" data-target="password_confirmation" tabindex="-1">
                                <svg class="eye-show" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"/><circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"/></svg>
                                <svg class="eye-hide" width="14" height="14" viewBox="0 0 14 14" fill="none" style="display:none"><path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"/><circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"/><line x1="2" y1="2" x2="12" y2="12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="field__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn">
                        Redefinir senha
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                            <path d="M1 6.5h11M7 2l4.5 4.5L7 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="card__footer">
                Lembrou a senha?
                <a href="{{ route('login') }}">Entrar</a>
            </div>
        </div>

    </div>
</div>
<script>
document.querySelectorAll('.eye-btn').forEach(function(btn){
    btn.addEventListener('click',function(){
        var inp=document.getElementById(this.dataset.target);
        var s=this.querySelector('.eye-show'),h=this.querySelector('.eye-hide'),v=inp.type==='text';
        inp.type=v?'password':'text';s.style.display=v?'':'none';h.style.display=v?'none':'';
    });
});
</script>
</body>
</html>
