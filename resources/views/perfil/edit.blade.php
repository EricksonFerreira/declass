@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @php
        $roleLabel = $user->isAdministrador()
            ? 'Superusuário'
            : (optional($user->tipoUsuario)->name ?? 'Sem perfil');

        $heroHighlights = [
            [
                'label' => 'Empresa',
                'value' => optional($user->empresa)->nome ?? 'Não informada',
                'icon' => 'fas fa-building',
            ],
            [
                'label' => 'Cadastro',
                'value' => $user->created_at ? $user->created_at->format('d/m/Y') : 'Não informado',
                'icon' => 'fas fa-calendar-check',
            ],
            [
                'label' => 'Perfil',
                'value' => $roleLabel,
                'icon' => 'fas fa-user-shield',
            ],
        ];

        $profileCards = [
            [
                'label' => 'Empresa',
                'value' => optional($user->empresa)->nome ?? 'Não informada',
                'icon' => 'fas fa-building',
            ],
            [
                'label' => 'Telefone',
                'value' => $user->telefone ?? 'Não informado',
                'icon' => 'fas fa-phone-alt',
            ],
            [
                'label' => 'Celular',
                'value' => $user->celular ?? 'Não informado',
                'icon' => 'fas fa-mobile-alt',
            ],
            [
                'label' => 'Cidade',
                'value' => $user->cidade ?? 'Não informada',
                'icon' => 'fas fa-map-marker-alt',
            ],
        ];
    @endphp

    <div class="profile-page">
        <div class="profile-shell">
            <section class="profile-hero card">
                <span class="profile-hero__decor profile-hero__decor--one"></span>
                <span class="profile-hero__decor profile-hero__decor--two"></span>
                <span class="profile-hero__accent"></span>

                <div class="profile-hero__content">
                    <div class="profile-hero__main">
                        <div class="profile-avatar-wrap">
                            <div class="profile-avatar" aria-hidden="true">
                                <img src="{{ $user->foto_perfil_url }}" alt="Foto de perfil de {{ $user->nome }}">
                            </div>
                            <div class="profile-id-badge">ID #{{ $user->id }}</div>
                        </div>

                        <div class="profile-identity">
                            <span class="profile-kicker">Meu perfil</span>
                            <h1 class="profile-name">{{ $user->nome }}</h1>
                            <p class="profile-email">{{ $user->email }}</p>

                            <div class="profile-meta">
                                <span class="profile-badge profile-badge--primary">
                                    <i class="fas fa-user-shield mr-1"></i>{{ $roleLabel }}
                                </span>
                                <span class="profile-badge profile-badge--soft">
                                    <i class="fas fa-circle-info mr-1"></i>Visualização apenas
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="profile-hero__aside">
                        @foreach ($heroHighlights as $item)
                            <div class="profile-quick-card">
                                <div class="profile-quick-card__icon">
                                    <i class="{{ $item['icon'] }}"></i>
                                </div>
                                <div class="profile-quick-card__body">
                                    <span class="profile-quick-card__label">{{ $item['label'] }}</span>
                                    <span class="profile-quick-card__value">{{ $item['value'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="profile-grid">
                @foreach ($profileCards as $info)
                    <div class="profile-info-card card">
                        <div class="profile-info-card__icon">
                            <i class="{{ $info['icon'] }}"></i>
                        </div>
                        <span class="profile-info-card__label">{{ $info['label'] }}</span>
                        <span class="profile-info-card__value">{{ $info['value'] }}</span>
                    </div>
                @endforeach
            </section>

            <section class="profile-action card">
                <div class="profile-action__content">
                    <div class="profile-action__text">
                        <span class="profile-action__eyebrow">Segurança da conta</span>
                        <h2 class="profile-action__title">Quer trocar sua senha?</h2>
                        <p class="profile-action__description">
                            Use uma senha forte e mantenha seu acesso protegido. O sistema abrirá a tela de atualização com validação reforçada.
                        </p>
                    </div>

                    <div class="profile-action__buttons">
                        <a href="{{ route('profile.password.edit') }}" class="btn profile-btn profile-btn--primary">
                            <i class="fas fa-key mr-1"></i> Alterar senha
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
@stop

@section('css')
    <style>
        .profile-page {
            position: relative;
            --profile-primary: var(--empresa-cor-primaria, #1F2937);
            --profile-secondary: var(--empresa-cor-secundaria, #334155);
            --profile-tertiary: var(--empresa-cor-terciaria, #E7B93A);
            --profile-surface: #ffffff;
            --profile-surface-soft: #f8fbff;
            padding-bottom: 1.5rem;
        }

        .profile-page::before,
        .profile-page::after {
            content: '';
            position: fixed;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            filter: blur(8px);
        }

        .profile-page::before {
            top: 90px;
            right: -90px;
            background: radial-gradient(circle, color-mix(in srgb, var(--profile-primary) 22%, transparent 78%) 0%, transparent 72%);
        }

        .profile-page::after {
            left: -110px;
            bottom: 40px;
            background: radial-gradient(circle, color-mix(in srgb, var(--profile-tertiary) 24%, transparent 76%) 0%, transparent 72%);
        }

        .profile-page > * {
            position: relative;
            z-index: 1;
        }

        .profile-shell {
            max-width: 1180px;
        }

        .profile-hero {
            position: relative;
            overflow: hidden;
            border: 1px solid #e5edf7;
            border-radius: 28px;
            background: linear-gradient(180deg, var(--profile-surface) 0%, var(--profile-surface-soft) 100%);
            box-shadow: 0 22px 48px rgba(15, 23, 42, .08);
            margin-bottom: 1rem;
        }

        .profile-hero__accent {
            position: absolute;
            inset: auto 0 0 0;
            height: 6px;
            background: linear-gradient(90deg, var(--profile-primary) 0%, var(--profile-secondary) 54%, var(--profile-tertiary) 100%);
        }

        .profile-hero__decor {
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            opacity: .85;
        }

        .profile-hero__decor--one {
            top: -60px;
            right: 120px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, color-mix(in srgb, var(--profile-primary) 20%, transparent 80%) 0%, transparent 72%);
        }

        .profile-hero__decor--two {
            bottom: -100px;
            left: 40%;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, color-mix(in srgb, var(--profile-tertiary) 18%, transparent 82%) 0%, transparent 70%);
        }

        .profile-hero__content {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(260px, .95fr);
            gap: 1.25rem;
            padding: 1.5rem;
        }

        .profile-hero__main {
            display: flex;
            gap: 1.25rem;
            align-items: flex-start;
        }

        .profile-avatar-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .65rem;
            flex: 0 0 auto;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            padding: 5px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffffff 0%, color-mix(in srgb, var(--profile-primary) 10%, #ffffff 90%) 100%);
            box-shadow: 0 18px 38px rgba(15, 23, 42, .16);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            background: #dbeafe;
        }

        .profile-id-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .38rem .8rem;
            border-radius: 999px;
            background: var(--profile-primary);
            color: #fff;
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .04em;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .20);
        }

        .profile-identity {
            min-width: 0;
            padding-top: .25rem;
        }

        .profile-kicker {
            display: inline-flex;
            align-items: center;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--profile-primary) 12%, #ffffff 88%);
            color: var(--profile-primary);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .profile-name {
            margin: .7rem 0 .25rem;
            font-size: 1.85rem;
            line-height: 1.1;
            font-weight: 800;
            color: #0f172a;
            word-break: break-word;
        }

        .profile-email {
            margin: 0;
            color: var(--profile-secondary);
            font-size: 1rem;
            line-height: 1.45;
            word-break: break-word;
        }

        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            margin-top: 1rem;
        }

        .profile-badge {
            display: inline-flex;
            align-items: center;
            padding: .42rem .8rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .profile-badge--primary {
            background: var(--profile-primary);
            color: var(--empresa-cor-primaria-contraste, #ffffff);
            border-color: var(--profile-primary);
        }

        .profile-badge--soft {
            background: var(--profile-secondary);
            color: var(--empresa-cor-secundaria-contraste, #ffffff);
            border-color: var(--profile-secondary);
        }

        .profile-hero__aside {
            display: grid;
            gap: .8rem;
            align-content: start;
        }

        .profile-quick-card {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .95rem 1rem;
            background: #ffffff;
            border: 1px solid color-mix(in srgb, var(--profile-primary) 12%, #dfe9f7 88%);
            border-radius: 18px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
        }

        .profile-quick-card__icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--profile-primary);
            color: var(--empresa-cor-primaria-contraste, #ffffff);
            font-size: 1rem;
            flex: 0 0 auto;
        }

        .profile-quick-card__body {
            min-width: 0;
        }

        .profile-quick-card__label {
            display: block;
            color: var(--profile-secondary);
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: .22rem;
        }

        .profile-quick-card__value {
            display: block;
            color: #0f172a;
            font-weight: 700;
            font-size: .96rem;
            line-height: 1.4;
            word-break: break-word;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .9rem;
            margin-bottom: 1rem;
        }

        .profile-info-card {
            position: relative;
            overflow: hidden;
            border: 1px solid color-mix(in srgb, var(--profile-primary) 10%, #e5edf7 90%);
            border-radius: 20px;
            padding: 1.1rem 1rem 1rem;
            background: linear-gradient(180deg, #ffffff 0%, color-mix(in srgb, var(--profile-primary) 2%, #fbfdff 98%) 100%);
            box-shadow: 0 12px 28px rgba(15, 23, 42, .05);
            min-height: 142px;
        }

        .profile-info-card::before {
            content: '';
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--profile-primary) 0%, var(--profile-secondary) 54%, var(--profile-tertiary) 100%);
        }

        .profile-info-card__icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--profile-secondary);
            color: var(--empresa-cor-secundaria-contraste, #ffffff);
            margin-bottom: .9rem;
            font-size: 1rem;
        }

        .profile-info-card__label {
            display: block;
            font-size: .72rem;
            font-weight: 800;
            color: var(--profile-secondary);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: .3rem;
        }

        .profile-info-card__value {
            display: block;
            color: #0f172a;
            font-weight: 700;
            font-size: .98rem;
            line-height: 1.45;
            word-break: break-word;
        }

        .profile-action {
            border: 1px solid color-mix(in srgb, var(--profile-primary) 10%, #e5edf7 90%);
            border-radius: 24px;
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, color-mix(in srgb, var(--profile-primary) 3%, #f8fbff 97%) 100%);
            box-shadow: 0 18px 34px rgba(15, 23, 42, .06);
        }

        .profile-action__content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            padding: 1.35rem 1.4rem;
        }

        .profile-action__text {
            min-width: 0;
        }

        .profile-action__eyebrow {
            display: inline-flex;
            align-items: center;
            padding: .32rem .7rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--profile-primary) 12%, #ffffff 88%);
            color: var(--profile-primary);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: .7rem;
        }

        .profile-action__title {
            margin: 0 0 .45rem;
            color: #0f172a;
            font-size: 1.08rem;
            font-weight: 800;
        }

        .profile-action__description {
            margin: 0;
            color: var(--profile-secondary);
            font-size: .94rem;
            line-height: 1.5;
            max-width: 760px;
        }

        .profile-btn {
            min-width: 200px;
            min-height: 48px;
            border-radius: 14px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }

        .profile-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(15, 23, 42, .12);
        }

        .profile-btn--primary {
            color: #111827 !important;
            background: linear-gradient(135deg, var(--profile-tertiary) 0%, color-mix(in srgb, var(--profile-tertiary) 78%, #ffffff 22%) 100%);
            border: 1px solid color-mix(in srgb, var(--profile-tertiary) 70%, #d4a72b 30%);
        }

        @media (max-width: 1199.98px) {
            .profile-hero__content {
                grid-template-columns: 1fr;
            }

            .profile-hero__aside {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .profile-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .profile-hero__main {
                flex-direction: column;
            }

            .profile-hero__aside {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .profile-hero__aside {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .profile-page {
                padding-bottom: .5rem;
            }

            .profile-hero__content,
            .profile-action__content {
                padding: 1rem;
            }

            .profile-avatar {
                width: 96px;
                height: 96px;
            }

            .profile-name {
                font-size: 1.45rem;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .profile-action__content {
                flex-direction: column;
                align-items: stretch;
            }

            .profile-btn {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
@stop
