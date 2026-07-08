<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $registerEmpresa = null;
        $registerTitle = config('adminlte.title', 'Sistema');
        $registerBrandColor = '#1e4fd8';
        $registerLogoUrl = null;
        $registerPoweredLogoUrl = asset('assets/imgs/logo.png');

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('empresas')) {
                $host = \App\Support\DomainNormalizer::normalize(request()->getHost());
                if ($host) {
                    $registerEmpresa = \App\Models\Empresa::query()
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
            $registerEmpresa = null;
        }

        if ($registerEmpresa) {
            $registerTitle = (string) ($registerEmpresa->nome ?: $registerTitle);
            $registerBrandColor = (string) ($registerEmpresa->cor_primaria ?: $registerBrandColor);
            $registerLogoUrl = $registerEmpresa->logo_url ?? null;
        }

        $hex = ltrim(trim((string) $registerBrandColor), '#');
        $registerBrandContrast = '#FFFFFF';
        if (preg_match('/^[A-Fa-f0-9]{6}$/', $hex)) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
            $registerBrandContrast = $brightness >= 155 ? '#0F172A' : '#FFFFFF';
        }
    @endphp
    <title>{{ $registerTitle }} - Criar conta</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand: {{ $registerBrandColor }};
            --brand-contrast: {{ $registerBrandContrast }};
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            min-height: 100%;
            font-family: 'Inter', system-ui, sans-serif;
            background: color-mix(in srgb, var(--brand) 6%, #f0f2f6 94%);
            color: #111827;
            -webkit-font-smoothing: antialiased;
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .wrap {
            width: 100%;
            max-width: 560px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        .logo__img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            display: block;
            border-radius: 11px;
            filter: drop-shadow(0 1px 0 rgba(0, 0, 0, .06));
        }

        .logo__name {
            font-size: 1.05rem;
            font-weight: 600;
            color: #111827;
            letter-spacing: -.02em;
        }

        .card {
            background: #ffffff;
            border-radius: 18px;
            width: 100%;
            box-shadow:
                0 0 0 1px rgba(0, 0, 0, .05),
                0 4px 6px rgba(0, 0, 0, .04),
                0 16px 48px rgba(0, 0, 0, .09);
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

        .card__footer a:hover {
            text-decoration: underline;
        }

        .fhead {
            margin-bottom: 1.5rem;
        }

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

        .flash {
            display: flex;
            align-items: center;
            gap: .45rem;
            padding: .7rem .9rem;
            border-radius: 9px;
            font-size: .8rem;
            margin-bottom: 1rem;
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .section-label {
            font-size: .7rem;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin: 1.35rem 0 .875rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f3f4f6;
        }

        .row {
            display: grid;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .row--1 {
            grid-template-columns: 1fr;
        }

        .row--2 {
            grid-template-columns: 1fr 1fr;
        }

        .row--3 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        @media (max-width: 520px) {
            .row--2,
            .row--3 {
                grid-template-columns: 1fr;
            }

            .card__body {
                padding: 1.75rem 1.375rem 1.5rem;
            }
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }

        .field__label {
            font-size: .775rem;
            font-weight: 500;
            color: #374151;
            letter-spacing: .005em;
        }

        .field__label .req {
            color: #e53e3e;
            margin-left: 1px;
        }

        .field__label small {
            color: #6b7280;
            font-size: .72rem;
        }

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
            flex-shrink: 0;
        }

        .input-wrap:focus-within .input-wrap__icon {
            color: var(--brand);
        }

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

        .input--no-icon {
            padding-left: .875rem;
        }

        .input::placeholder {
            color: #c5ccd7;
        }

        .input:hover {
            border-color: #d1d5db;
            background: #f3f4f8;
        }

        .input:focus {
            border-color: var(--brand);
            background: #fff;
            box-shadow: 0 0 0 3.5px color-mix(in srgb, var(--brand) 12%, transparent 88%);
        }

        .field--error .input {
            border-color: #f05252;
            background: #fef7f7;
        }

        .field--error .input:focus {
            box-shadow: 0 0 0 3.5px rgba(240, 82, 82, .1);
        }

        .field--error .input-wrap__icon {
            color: #f05252;
        }

        .field__error {
            font-size: .75rem;
            color: #e53e3e;
            line-height: 1.4;
        }

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

        .eye-btn:hover {
            color: #6b7280;
            background: #f3f4f6;
        }

        .postal-spinner {
            position: absolute;
            right: .75rem;
            width: 14px;
            height: 14px;
            border: 2px solid #e9eaee;
            border-top-color: var(--brand);
            border-radius: 50%;
            animation: spin .6s linear infinite;
            display: none;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

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

        .btn:active {
            transform: translateY(0);
            background: color-mix(in srgb, var(--brand) 72%, #000 28%);
        }
    </style>
</head>
<body>
<div class="page">
    <div class="wrap">
        <div class="logo">
            @if(!empty($registerLogoUrl))
                <img class="logo__img" src="{{ $registerLogoUrl }}" alt="Logo {{ $registerTitle }}">
            @else
                <img class="logo__img" src="{{ $registerPoweredLogoUrl }}" alt="Logo {{ $registerTitle }}">
            @endif
            <span class="logo__name">{{ $registerTitle }}</span>
        </div>

        <div class="card">
            <div class="card__body">
                @if (session('status'))
                    <div class="flash">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                            <circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.2"></circle>
                            <path d="M4 6.5l2 2 3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        {{ session('status') }}
                    </div>
                @endif

                <div class="fhead">
                    <h1 class="fhead__title">Criar nova conta</h1>
                    <p class="fhead__sub">Preencha os dados abaixo para se registrar.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" novalidate>
                    @csrf

                    <div class="section-label">Dados pessoais</div>

                    <div class="row row--1">
                        <div class="field {{ $errors->has('name') ? 'field--error' : '' }}">
                            <label class="field__label" for="name">Nome <span class="req">*</span></label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <circle cx="7" cy="4.5" r="2.5" stroke="currentColor" stroke-width="1.2"></circle>
                                        <path d="M1.5 12.5c0-2.485 2.462-4.5 5.5-4.5s5.5 2.015 5.5 4.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"></path>
                                    </svg>
                                </span>
                                <input id="name" class="input" type="text" name="name" value="{{ old('name') }}" placeholder="Seu nome completo" required autofocus autocomplete="name">
                            </div>
                            @error('name')<p class="field__error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="row row--2">
                        <div class="field {{ $errors->has('email') ? 'field--error' : '' }}">
                            <label class="field__label" for="email">E-mail <span class="req">*</span></label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <rect x="1" y="3" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.2"></rect>
                                        <path d="M1 5l6 4 6-4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"></path>
                                    </svg>
                                </span>
                                <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" placeholder="seu@email.com" required autocomplete="username">
                            </div>
                            @error('email')<p class="field__error">{{ $message }}</p>@enderror
                        </div>

                        <div class="field {{ $errors->has('sexo') ? 'field--error' : '' }}">
                            <label class="field__label" for="sexo">Sexo <span class="req">*</span></label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <path d="M7 1a3 3 0 100 6 3 3 0 000-6zM7 7.5c-2.5 0-4.5 2-4.5 4.5v.5h9v-.5c0-2.5-2-4.5-4.5-4.5z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                                <select id="sexo" class="input" name="sexo" required>
                                    <option value="" disabled {{ old('sexo') ? '' : 'selected' }}>Selecione</option>
                                    <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Feminino</option>
                                    <option value="NI" {{ old('sexo') == 'NI' ? 'selected' : '' }}>Nao informado</option>
                                </select>
                            </div>
                            @error('sexo')<p class="field__error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="row row--2">
                        <div class="field {{ $errors->has('data_nascimento') ? 'field--error' : '' }}">
                            <label class="field__label" for="data_nascimento">Data de nascimento <span class="req">*</span> <small>minimo 16 anos</small></label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <rect x="1" y="2.5" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.2"></rect>
                                        <path d="M1 6h12M4.5 1v3M9.5 1v3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"></path>
                                    </svg>
                                </span>
                                <input id="data_nascimento" class="input" type="date" name="data_nascimento" value="{{ old('data_nascimento') }}" required autocomplete="bday">
                            </div>
                            @error('data_nascimento')<p class="field__error">{{ $message }}</p>@enderror
                        </div>

                        <div class="field {{ $errors->has('celular') ? 'field--error' : '' }}">
                            <label class="field__label" for="celular">Celular <span class="req">*</span></label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <path d="M4.5 1.5h-2A1 1 0 001.5 2.5C1.5 8.575 6.425 13.5 12.5 13.5a1 1 0 001-1v-2a1 1 0 00-1-1c-.69 0-1.367-.112-2-.32a1 1 0 00-1.02.242l-1.24 1.24a9.057 9.057 0 01-3.9-3.9l1.24-1.24A1 1 0 005.82 4.5C5.612 3.867 5.5 3.19 5.5 2.5a1 1 0 00-1-1z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                                <input id="celular" class="input" type="tel" name="celular" value="{{ old('celular') }}" placeholder="(00) 00000-0000" required autocomplete="tel">
                            </div>
                            @error('celular')<p class="field__error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="section-label">Endereco</div>

                    <div class="row row--3">
                        <div class="field {{ $errors->has('codigo_postal') ? 'field--error' : '' }}">
                            <label class="field__label" for="codigo_postal">CEP</label>
                            <div class="input-wrap">
                                <input id="codigo_postal" class="input input--no-icon" type="text" name="codigo_postal" value="{{ old('codigo_postal') }}" placeholder="00000-000" maxlength="10" autocomplete="postal-code">
                                <span class="postal-spinner" id="postalSpinner"></span>
                            </div>
                            @error('codigo_postal')<p class="field__error">{{ $message }}</p>@enderror
                        </div>

                        <div class="field {{ $errors->has('cidade') ? 'field--error' : '' }}">
                            <label class="field__label" for="cidade">Cidade</label>
                            <div class="input-wrap">
                                <input id="cidade" class="input input--no-icon" type="text" name="cidade" value="{{ old('cidade') }}" placeholder="Cidade" autocomplete="address-level2">
                            </div>
                            @error('cidade')<p class="field__error">{{ $message }}</p>@enderror
                        </div>

                        <div class="field {{ $errors->has('pais') ? 'field--error' : '' }}">
                            <label class="field__label" for="pais_nome">Pais</label>
                            <div class="input-wrap">
                                <input id="pais_nome" class="input input--no-icon" type="text" value="{{ old('pais') == 'BR' ? 'Brasil' : old('pais') }}" placeholder="Pais" readonly>
                                <input type="hidden" name="pais" id="pais" value="{{ old('pais', 'BR') }}">
                            </div>
                            @error('pais')<p class="field__error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="row row--1">
                        <div class="field {{ $errors->has('endereco1') ? 'field--error' : '' }}">
                            <label class="field__label" for="endereco1">Endereco</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <path d="M7 1C4.791 1 3 2.791 3 5c0 3.25 4 8 4 8s4-4.75 4-8c0-2.209-1.791-4-4-4z" stroke="currentColor" stroke-width="1.2"></path>
                                        <circle cx="7" cy="5" r="1.25" stroke="currentColor" stroke-width="1.2"></circle>
                                    </svg>
                                </span>
                                <input id="endereco1" class="input" type="text" name="endereco1" value="{{ old('endereco1') }}" placeholder="Rua, numero" autocomplete="address-line1">
                            </div>
                            @error('endereco1')<p class="field__error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="row row--1">
                        <div class="field {{ $errors->has('endereco2') ? 'field--error' : '' }}">
                            <label class="field__label" for="endereco2">Complemento</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <path d="M7 1C4.791 1 3 2.791 3 5c0 3.25 4 8 4 8s4-4.75 4-8c0-2.209-1.791-4-4-4z" stroke="currentColor" stroke-width="1.2"></path>
                                        <circle cx="7" cy="5" r="1.25" stroke="currentColor" stroke-width="1.2"></circle>
                                    </svg>
                                </span>
                                <input id="endereco2" class="input" type="text" name="endereco2" value="{{ old('endereco2') }}" placeholder="Apto, bloco, andar (opcional)" autocomplete="address-line2">
                            </div>
                            @error('endereco2')<p class="field__error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="section-label">Senha</div>

                    <div class="row row--2">
                        <div class="field {{ $errors->has('password') ? 'field--error' : '' }}">
                            <label class="field__label" for="password">Senha <span class="req">*</span></label>
                            <small>Use pelo menos 8 caracteres, com maiuscula, minuscula e numero.</small>
                            <div class="input-wrap">
                                <span class="input-wrap__icon">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <rect x="2" y="6" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.2"></rect>
                                        <path d="M4.5 6V4.5a2.5 2.5 0 015 0V6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"></path>
                                        <circle cx="7" cy="9.5" r=".85" fill="currentColor"></circle>
                                    </svg>
                                </span>
                                <input id="password" class="input" type="password" name="password" placeholder="Minimo 8 caracteres" required autocomplete="new-password">
                                <button type="button" class="eye-btn" data-target="password" tabindex="-1">
                                    <svg class="eye-show" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"></path>
                                        <circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"></circle>
                                    </svg>
                                    <svg class="eye-hide" width="14" height="14" viewBox="0 0 14 14" fill="none" style="display:none">
                                        <path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"></path>
                                        <circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"></circle>
                                        <line x1="2" y1="2" x2="12" y2="12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"></line>
                                    </svg>
                                </button>
                            </div>
                            @error('password')<p class="field__error">{{ $message }}</p>@enderror
                        </div>

                        <div class="field {{ $errors->has('password_confirmation') ? 'field--error' : '' }}">
                            <label class="field__label" for="password_confirmation">Confirmar senha <span class="req">*</span></label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <rect x="2" y="6" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.2"></rect>
                                        <path d="M4.5 6V4.5a2.5 2.5 0 015 0V6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"></path>
                                        <circle cx="7" cy="9.5" r=".85" fill="currentColor"></circle>
                                    </svg>
                                </span>
                                <input id="password_confirmation" class="input" type="password" name="password_confirmation" placeholder="Repita a senha" required autocomplete="new-password">
                                <button type="button" class="eye-btn" data-target="password_confirmation" tabindex="-1">
                                    <svg class="eye-show" width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"></path>
                                        <circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"></circle>
                                    </svg>
                                    <svg class="eye-hide" width="14" height="14" viewBox="0 0 14 14" fill="none" style="display:none">
                                        <path d="M1 7s2.2-4 6-4 6 4 6 4-2.2 4-6 4-6-4-6-4z" stroke="currentColor" stroke-width="1.2"></path>
                                        <circle cx="7" cy="7" r="1.75" stroke="currentColor" stroke-width="1.2"></circle>
                                        <line x1="2" y1="2" x2="12" y2="12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"></line>
                                    </svg>
                                </button>
                            </div>
                            @error('password_confirmation')<p class="field__error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn">Criar conta</button>
                </form>
            </div>

            <div class="card__footer">
                Ja possui uma conta? <a href="{{ route('login') }}">Entrar</a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('.eye-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var inp = document.getElementById(this.dataset.target);
            var show = this.querySelector('.eye-show');
            var hide = this.querySelector('.eye-hide');
            var vis = inp.type === 'text';
            inp.type = vis ? 'password' : 'text';
            if (show) show.style.display = vis ? '' : 'none';
            if (hide) hide.style.display = vis ? 'none' : '';
        });
    });

    var celular = document.getElementById('celular');
    if (celular) {
        celular.addEventListener('input', function () {
            var v = this.value.replace(/\D/g, '').substring(0, 11);
            if (v.length <= 10) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else {
                v = v.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            }
            this.value = v;
        });
    }

    var cepInput = document.getElementById('codigo_postal');
    var enderecoInput = document.getElementById('endereco1');
    var cidadeInput = document.getElementById('cidade');
    var paisNomeInput = document.getElementById('pais_nome');
    var paisInput = document.getElementById('pais');
    var spinner = document.getElementById('postalSpinner');
    var debounceTimer = null;

    function setReadonlyFields(on) {
        [cidadeInput, paisNomeInput].forEach(function (el) {
            if (!el) return;
            if (on) {
                el.setAttribute('readonly', true);
                el.style.background = '#f3f4f6';
                el.style.color = '#6b7280';
                el.style.cursor = 'not-allowed';
            } else {
                el.removeAttribute('readonly');
                el.style.background = '';
                el.style.color = '';
                el.style.cursor = '';
            }
        });
    }

    function clearLocationFields() {
        if (enderecoInput) enderecoInput.value = '';
        if (cidadeInput) cidadeInput.value = '';
        if (paisNomeInput) paisNomeInput.value = '';
        if (paisInput) paisInput.value = '';
        setReadonlyFields(false);
    }

    if (cepInput) {
        cepInput.addEventListener('input', function () {
            var v = this.value.replace(/\D/g, '').substring(0, 8);
            if (v.length > 5) {
                v = v.replace(/^(\d{5})(\d{0,3})/, '$1-$2');
            }
            this.value = v;

            clearTimeout(debounceTimer);
            var raw = this.value.replace(/\D/g, '');

            if (raw.length < 8) {
                if (spinner) spinner.style.display = 'none';
                clearLocationFields();
                return;
            }

            if (spinner) spinner.style.display = 'block';

            debounceTimer = setTimeout(function () {
                fetch('https://viacep.com.br/ws/' + raw + '/json/')
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Erro ao consultar CEP');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        if (spinner) spinner.style.display = 'none';
                        if (data.erro) {
                            clearLocationFields();
                            return;
                        }

                        if (enderecoInput) {
                            enderecoInput.value = data.logradouro || '';
                        }
                        if (cidadeInput) {
                            cidadeInput.value = data.localidade || '';
                        }
                        if (paisNomeInput) {
                            paisNomeInput.value = 'Brasil';
                        }
                        if (paisInput) {
                            paisInput.value = 'BR';
                        }
                        setReadonlyFields(true);
                    })
                    .catch(function () {
                        if (spinner) spinner.style.display = 'none';
                        clearLocationFields();
                    });
            }, 500);
        });
    }
}());
</script>
</body>
</html>
