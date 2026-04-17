@extends('adminlte::page')

@section('title', $titulo)

@section('content')
    <style>
        .force-password-page {
            min-height: calc(100vh - 120px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 12px;
        }

        .force-password-card {
            width: min(100%, 560px);
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .12);
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }

        .force-password-card__head {
            padding: 1.6rem 1.6rem 1.1rem;
            border-bottom: 1px solid #e5eef7;
        }

        .force-password-card__title {
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f2740;
            margin: 0 0 .35rem;
            letter-spacing: -.02em;
        }

        .force-password-card__body {
            padding: 1.5rem 1.6rem 1.6rem;
        }

        .force-password-help {
            margin-top: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid #dbe6f3;
            background: #fff;
        }

        .force-password-help__title {
            font-size: .82rem;
            font-weight: 700;
            color: #0f2740;
            margin-bottom: 8px;
        }

        .force-password-help__list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 6px;
        }

        .force-password-help__item {
            font-size: .82rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .force-password-help__item i {
            width: 12px;
            color: #94a3b8;
        }

        .force-password-help__item.is-ok {
            color: #166534;
        }

        .force-password-help__item.is-ok i {
            color: #16a34a;
        }

        .force-password-actions {
            margin-top: 1.35rem;
            display: flex;
            gap: 10px;
        }

        .force-password-actions .btn {
            min-height: 44px;
            border-radius: 12px;
            font-weight: 700;
        }

        .force-password-actions .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            border: 1px solid #2563eb;
            box-shadow: 0 12px 24px rgba(37, 99, 235, .18);
        }

        .force-password-actions .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(37, 99, 235, .22);
        }

        @media (max-width: 575.98px) {
            .force-password-page {
                padding: 14px;
            }

            .force-password-card__head,
            .force-password-card__body {
                padding-left: 1rem;
                padding-right: 1rem;
            }

        }
    </style>

    <div class="force-password-page">
        <div class="force-password-card">
            <div class="force-password-card__head">
                <h1 class="force-password-card__title">Definir nova senha</h1>
            </div>

            <div class="force-password-card__body">
                <x-form method="PUT" action="{{ route('profile.force-password.update') }}" rowClass="row" id="forcePasswordForm" novalidate>
                    <div class="col-md-6">
                        <div class="form-group">
                            <x-form-input
                                type="password"
                                name="password"
                                label="Nova senha"
                                required
                                hint="Use ao menos 8 caracteres, com maiúscula, minúscula, número e símbolo."
                                autocomplete="new-password"
                                id="force_password"
                            />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <x-form-input
                                type="password"
                                name="password_confirmation"
                                label="Confirmar nova senha"
                                required
                                hint="Repita exatamente a nova senha."
                                autocomplete="new-password"
                                id="force_password_confirmation"
                            />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="force-password-help" id="passwordRulesBox" aria-live="polite">
                            <div class="force-password-help__title">Requisitos da senha</div>
                            <ul class="force-password-help__list">
                                <li class="force-password-help__item" data-rule="length"><i class="fas fa-circle"></i> Pelo menos 8 caracteres</li>
                                <li class="force-password-help__item" data-rule="uppercase"><i class="fas fa-circle"></i> Uma letra maiúscula</li>
                                <li class="force-password-help__item" data-rule="lowercase"><i class="fas fa-circle"></i> Uma letra minúscula</li>
                                <li class="force-password-help__item" data-rule="number"><i class="fas fa-circle"></i> Um número</li>
                                <li class="force-password-help__item" data-rule="special"><i class="fas fa-circle"></i> Um caractere especial</li>
                                <li class="force-password-help__item" data-rule="match"><i class="fas fa-circle"></i> As duas senhas devem ser iguais</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="force-password-actions">
                            <button type="submit" class="btn btn-primary btn-block" id="btnForcePassword">
                                <i class="fas fa-check mr-1"></i> Salvar senha
                            </button>
                        </div>
                    </div>
                </x-form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        (function () {
            const form = document.getElementById('forcePasswordForm');
            if (!form) {
                return;
            }

            const password = document.getElementById('force_password');
            const confirmation = document.getElementById('force_password_confirmation');
            const submitBtn = document.getElementById('btnForcePassword');
            const ruleItems = {
                length: form.querySelector('[data-rule="length"]'),
                uppercase: form.querySelector('[data-rule="uppercase"]'),
                lowercase: form.querySelector('[data-rule="lowercase"]'),
                number: form.querySelector('[data-rule="number"]'),
                special: form.querySelector('[data-rule="special"]'),
                match: form.querySelector('[data-rule="match"]'),
            };

            function setRuleState(name, ok) {
                const el = ruleItems[name];
                if (!el) {
                    return;
                }

                el.classList.toggle('is-ok', ok);
                const icon = el.querySelector('i');
                if (icon) {
                    icon.className = ok ? 'fas fa-check-circle' : 'fas fa-circle';
                }
            }

            function validatePassword() {
                const value = String(password?.value || '');
                const confirm = String(confirmation?.value || '');

                const checks = {
                    length: value.length >= 8,
                    uppercase: /[A-Z]/.test(value),
                    lowercase: /[a-z]/.test(value),
                    number: /[0-9]/.test(value),
                    special: /[!@#$%^&*(),.?":{}|<>]/.test(value),
                    match: value.length > 0 && value === confirm,
                };

                Object.entries(checks).forEach(([key, ok]) => setRuleState(key, ok));

                password.setCustomValidity(checks.length && checks.uppercase && checks.lowercase && checks.number && checks.special ? '' : 'A senha não atende aos requisitos mínimos.');
                confirmation.setCustomValidity(checks.match ? '' : 'As senhas devem ser iguais.');

                if (submitBtn) {
                    submitBtn.disabled = !Object.values(checks).every(Boolean);
                }

                return Object.values(checks).every(Boolean);
            }

            password?.addEventListener('input', validatePassword);
            confirmation?.addEventListener('input', validatePassword);
            form.addEventListener('submit', function (event) {
                if (!validatePassword()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });

            validatePassword();
        })();
    </script>
@stop
