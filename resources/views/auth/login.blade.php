<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $loginEmpresa = null;
        $loginTitle = config('adminlte.title', 'Sistema');
        $loginBrandColor = '#1e4fd8';
        $loginLogoUrl = null;
        $loginPoweredLogoUrl = asset('assets/imgs/logo.png');

        $loginEmpresa = \App\Support\EmpresaDominio::porHost(request()->getHost());

        if ($loginEmpresa) {
            $loginTitle = (string) ($loginEmpresa->nome ?: $loginTitle);
            $loginBrandColor = (string) ($loginEmpresa->cor_primaria ?: $loginBrandColor);
            $loginLogoUrl = $loginEmpresa->logo_url ?? null;
        }

        $hex = ltrim(trim((string) $loginBrandColor), '#');
        $loginBrandContrast = '#FFFFFF';
        if (preg_match('/^[A-Fa-f0-9]{6}$/', $hex)) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
            $loginBrandContrast = $brightness >= 155 ? '#0F172A' : '#FFFFFF';
        }
    @endphp
    <title>{{ $loginTitle }} — Entrar</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root{
            --brand: {{ $loginBrandColor }};
            --brand-contrast: {{ $loginBrandContrast }};
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'Inter', system-ui, sans-serif;
            background: color-mix(in srgb, var(--brand) 6%, #f0f2f6 94%);
            color: #111827;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Layout ── */
        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .wrap {
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        /* ── Logo ── */
        .logo {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .logo__mark {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .logo__name {
            font-size: 1.15rem;
            font-weight: 600;
            color: #111827;
            letter-spacing: -.02em;
        }

        .logo__img{
            width: 28px;
            height: 28px;
            object-fit: contain;
            display: block;
            filter: drop-shadow(0 1px 0 rgba(0,0,0,.06));
        }

        /* ── Card ── */
        .card {
            background: #ffffff;
            border-radius: 18px;
            width: 100%;
            box-shadow:
                0 0 0 1px rgba(0,0,0,.05),
                0 4px 6px rgba(0,0,0,.04),
                0 16px 48px rgba(0,0,0,.09);
            overflow: hidden;
        }

        .card__body {
            padding: 2.25rem 2.375rem 2rem;
        }

        .card__footer {
            border-top: 1px solid #f3f4f6;
            padding: 1.1rem 2.375rem;
            text-align: center;
            font-size: .8rem;
            color: #9ca3af;
        }

        .card__footer a {
            color: var(--brand);
            font-weight: 600;
            text-decoration: none;
        }

        .card__footer a:hover { text-decoration: underline; }

        /* ── Form heading ── */
        .fhead { margin-bottom: 1.75rem; }
        .fhead__title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #111827;
            letter-spacing: -.025em;
            line-height: 1.3;
            margin-bottom: .3rem;
        }
        .fhead__sub {
            font-size: .8rem;
            color: #9ca3af;
            line-height: 1.5;
        }

        /* ── Flash ── */
        .flash {
            display: flex;
            align-items: center;
            gap: .45rem;
            padding: .7rem .9rem;
            border-radius: 9px;
            font-size: .8rem;
            margin-bottom: 1.25rem;
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        /* ── Field ── */
        .field { display: flex; flex-direction: column; gap: .35rem; }
        .field + .field { margin-top: 1rem; }

        .field__label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .field__label {
            font-size: .775rem;
            font-weight: 500;
            color: #374151;
            letter-spacing: .005em;
        }

        .field__link {
            font-size: .775rem;
            color: var(--brand);
            text-decoration: none;
            transition: color .15s;
        }
        .field__link:hover { color: color-mix(in srgb, var(--brand) 80%, #000 20%); text-decoration: underline; }

        /* ── Input ── */
        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap__icon {
            position: absolute;
            left: .85rem;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            pointer-events: none;
            transition: color .15s;
        }

        .input-wrap:focus-within .input-wrap__icon { color: var(--brand); }

        .input {
            width: 100%;
            height: 43px;
            padding: 0 2.6rem 0 2.55rem;
            font-size: .855rem;
            font-family: 'Inter', sans-serif;
            color: #111827;
            background: #f8f9fc;
            border: 1.5px solid #e9eaee;
            border-radius: 10px;
            outline: none;
            transition: border-color .15s, background .15s, box-shadow .15s;
            -webkit-appearance: none;
        }

        .input::placeholder { color: #c5ccd7; }
        .input:hover { border-color: #d1d5db; background: #f3f4f8; }
        .input:focus {
            border-color: var(--brand);
            background: #fff;
            box-shadow: 0 0 0 3.5px color-mix(in srgb, var(--brand) 12%, transparent 88%);
        }

        .field--error .input { border-color: #f05252; background: #fef7f7; }
        .field--error .input:focus { box-shadow: 0 0 0 3.5px rgba(240,82,82,.1); }
        .field--error .input-wrap__icon { color: #f05252; }

        .field__error {
            font-size: .75rem;
            color: #e53e3e;
            line-height: 1.4;
        }

        /* ── Eye toggle ── */
        .eye-btn {
            position: absolute;
            right: .7rem;
            background: none;
            border: none;
            padding: 5px;
            cursor: pointer;
            color: #c5ccd7;
            display: flex;
            align-items: center;
            border-radius: 6px;
            transition: color .15s, background .15s;
        }
        .eye-btn:hover { color: #6b7280; background: #f3f4f6; }

        /* ── Checkbox ── */
        .check {
            display: flex;
            align-items: center;
            gap: .5rem;
            cursor: pointer;
            user-select: none;
            margin-top: .9rem;
        }

        .check input { display: none; }

        .check__box {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
            border: 1.5px solid #d1d5db;
            border-radius: 4px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s, border-color .15s;
        }

        .check input:checked + .check__box {
            background: var(--brand);
            border-color: var(--brand);
        }

        .check input:checked + .check__box::after {
            content: '';
            width: 7px;
            height: 4.5px;
            border-left: 1.5px solid #fff;
            border-bottom: 1.5px solid #fff;
            transform: rotate(-45deg) translateY(-1px);
            display: block;
        }

        .check__label {
            font-size: .775rem;
            color: #6b7280;
        }

        /* ── Button ── */
        .btn {
            width: 100%;
            height: 44px;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            background: var(--brand);
            color: #fff;
            font-size: .875rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            letter-spacing: .01em;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background .15s, transform .1s, box-shadow .15s;
            box-shadow:
                0 1px 3px color-mix(in srgb, var(--brand) 30%, transparent 70%),
                0 4px 14px color-mix(in srgb, var(--brand) 20%, transparent 80%);
        }

        .btn:hover {
            background: color-mix(in srgb, var(--brand) 82%, #000 18%);
            transform: translateY(-1px);
            box-shadow:
                0 2px 6px color-mix(in srgb, var(--brand) 35%, transparent 65%),
                0 8px 22px color-mix(in srgb, var(--brand) 22%, transparent 78%);
        }

        .btn:active { transform: translateY(0); background: color-mix(in srgb, var(--brand) 72%, #000 28%); }
        .btn:disabled,
        .btn.is-loading {
            cursor: not-allowed;
            pointer-events: none;
            background: color-mix(in srgb, var(--brand) 52%, #94a3b8 48%);
            color: rgba(255,255,255,.96);
            transform: none;
            box-shadow: none;
            opacity: .92;
        }
        .btn__spinner {
            width: 14px;
            height: 14px;
            border-radius: 999px;
            border: 2px solid rgba(255,255,255,.35);
            border-top-color: #fff;
            animation: login-spin .7s linear infinite;
            display: none;
            flex-shrink: 0;
        }
        .btn.is-loading .btn__spinner { display: inline-flex; }
        .btn.is-loading .btn__icon { display: none; }
        @keyframes login-spin {
            to { transform: rotate(360deg); }
        }

        .powered{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:.5rem;
            margin-top:.75rem;
            font-size:.75rem;
            color:#9ca3af;
        }
        .powered img{ height:2em; width:auto; display:block; opacity:.95; }
    </style>
</head>
<body>
<div class="page">
    <div class="wrap">

        {{-- Logo --}}
        <a href="{{ route('site.apresentacao') }}" class="logo" title="Ir para a pagina inicial" style="text-decoration:none">
            <div class="">
                @if(!empty($loginLogoUrl))
                <img src="{{ $loginLogoUrl }}" alt="Logo {{ $loginTitle }}" style="width:9em">
                @else
                <img src="{{asset('assets/imgs/logo.png')}}" alt="Logo {{ $loginTitle }}" style="width:9em">
                @endif
            </div>
            {{-- @if(!empty($loginLogoUrl))
            <span class="logo__name">{{ $loginTitle }}</span>
            @endif --}}
        </a>

        {{-- Card --}}
        <div class="card">
            <div class="card__body">

                {{-- Flash status --}}
                @if (session('status'))
                    <div class="flash">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                            <circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.2"/>
                            <path d="M4 6.5l2 2 3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Heading --}}
                <div class="fhead">
                    <h1 class="fhead__title">Bem-vindo de volta</h1>
                    <p class="fhead__sub">
                        @if($loginEmpresa)
                            Entre com seu e-mail ou usuário e senha para acessar {{ $loginTitle }}
                        @else
                            Informe o slug da empresa, seu e-mail ou usuário e sua senha para acessar o sistema
                        @endif
                    </p>
                </div>

                <form method="POST" action="{{ route('login') }}" novalidate id="loginForm">
                    @csrf

                    @if($loginEmpresa)
                        <input type="hidden" name="slug" value="{{ old('slug', $loginEmpresa->slug) }}">
                    @else
                    <div class="field {{ $errors->has('slug') ? 'field--error' : '' }}">
                        <label class="field__label" for="slug">Slug da empresa</label>
                        <div class="input-wrap">
                            <span class="input-wrap__icon">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <path d="M2 3.5A1.5 1.5 0 013.5 2h7A1.5 1.5 0 0112 3.5v7a1.5 1.5 0 01-1.5 1.5h-7A1.5 1.5 0 012 10.5v-7z" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M4.2 7h5.6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <input
                                id="slug"
                                class="input"
                                type="text"
                                name="slug"
                                value="{{ old('slug') }}"
                                placeholder="ex.: minha-empresa"
                                required autofocus autocomplete="organization"
                            >
                        </div>
                        @error('slug')
                            <p class="field__error">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    {{-- E-mail --}}
                    <div class="field {{ $errors->has('email') ? 'field--error' : '' }}">
                        <label class="field__label" for="email">E-mail ou usuário</label>
                        <div class="input-wrap">
                            <span class="input-wrap__icon">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="1" y="3" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M1 5l6 4 6-4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <input
                                id="email"
                                class="input"
                                type="text"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="seu@email.com ou usuario"
                                required {{ $loginEmpresa ? 'autofocus' : '' }} autocomplete="username"
                            >
                        </div>
                        @error('email')
                            <p class="field__error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Senha --}}
                    <div class="field {{ $errors->has('password') ? 'field--error' : '' }}">
                        <div class="field__label-row">
                            <label class="field__label" for="password">Senha</label>
                            @if (Route::has('password.request'))
                                <a class="field__link" href="{{ route('password.request') }}">Esqueceu a senha?</a>
                            @endif
                        </div>
                        <div class="input-wrap">
                            <span class="input-wrap__icon">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <rect x="2" y="6" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M4.5 6V4.5a2.5 2.5 0 015 0V6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                    <circle cx="7" cy="9.5" r=".85" fill="currentColor"/>
                                </svg>
                            </span>
                            <input
                                id="password"
                                class="input"
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                required autocomplete="current-password"
                            >
                            <button type="button" class="eye-btn" id="eyeBtn" tabindex="-1">
                                <svg id="eyeShow" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                    <path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"/>
                                    <circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"/>
                                </svg>
                                <svg id="eyeHide" width="14" height="14" viewBox="0 0 14 14" fill="none" style="display:none">
                                    <path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"/>
                                    <circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"/>
                                    <line x1="2" y1="2" x2="12" y2="12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="field__error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Lembrar-me --}}
                    <label class="check">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="check__box"></span>
                        <span class="check__label">Relembre-me neste dispositivo</span>
                    </label>

                    {{-- Submit --}}
                    <button type="submit" class="btn" id="loginSubmitBtn">
                        <span class="btn__spinner" aria-hidden="true"></span>
                        <span class="btn__label" data-default-label="Entrar">Entrar</span>
                        <svg class="btn__icon" width="13" height="13" viewBox="0 0 13 13" fill="none">
                            <path d="M1 6.5h11M7 2l4.5 4.5L7 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </div>

            {{-- Footer --}}
            <div class="card__footer">
                @if (Route::has('register'))
                    Não possui uma conta?
                    <a href="{{ route('register') }}">Criar conta</a>
                @endif
                <div class="powered">
                    <span>Powered by</span>
                    <img src="{{ $loginPoweredLogoUrl }}" alt="XpandCars">
                </div>
            </div>
        </div>

    </div>
</div>

<script>
(function () {
    var btn  = document.getElementById('eyeBtn');
    var pwd  = document.getElementById('password');
    var show = document.getElementById('eyeShow');
    var hide = document.getElementById('eyeHide');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var visible = pwd.type === 'text';
        pwd.type       = visible ? 'password' : 'text';
        show.style.display = visible ? '' : 'none';
        hide.style.display = visible ? 'none' : '';
    });
}());

(function () {
    var form = document.getElementById('loginForm');
    var submitBtn = document.getElementById('loginSubmitBtn');
    if (!form || !submitBtn) return;

    form.addEventListener('submit', function () {
        if (submitBtn.disabled) return;

        submitBtn.disabled = true;
        submitBtn.classList.add('is-loading');
        submitBtn.setAttribute('aria-busy', 'true');

        var label = submitBtn.querySelector('.btn__label');
        if (label) {
            label.textContent = 'Processando...';
        }
    });
}());
</script>
</body>
</html>
