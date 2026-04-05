<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }} | {{ $empresa->nome }}</title>
    <meta name="description" content="{{ $descricao }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <style>
        :root {
            --brand-primary: {{ $corPrimaria }};
            --brand-secondary: {{ $corSecundaria }};
            --brand-accent: {{ $corTerciaria }};
            --brand-primary-text: {{ $corTextoPrimario }};
            --topbar-bg: {{ $corTopbarFundo }};
            --topbar-text: {{ $corTopbarTexto }};
            --topbar-hover: {{ $corTopbarLinkHover }};
            --hero-bg: {{ $corHeroFundo }};
            --hero-text: {{ $corHeroTexto }};
            --hero-form-bg: {{ $corFormFundo }};
            --hero-form-text: {{ $corFormTexto }};
            --section-frota-bg: {{ $corFrotaFundo }};
            --section-vantagens-bg: {{ $corVantagensFundo }};
            --section-etapas-bg: {{ $corEtapasFundo }};
            --section-contato-bg: {{ $corContatoFundo }};
            --footer-bg: {{ $corFooterFundo }};
            --footer-text: {{ $corFooterTexto }};
            --btn-primary-bg: {{ $corBotaoPrimarioFundo }};
            --btn-primary-text: {{ $corBotaoPrimarioTexto }};
            --btn-secondary-bg: {{ $corBotaoSecundarioFundo }};
            --btn-secondary-text: {{ $corBotaoSecundarioTexto }};
            --ink-900: #0f172a;
            --ink-700: #334155;
            --ink-500: #64748b;
            --surface: #f7fafc;
            --white: #ffffff;
            --line: #dbe4ee;
            --radius-lg: 22px;
            --radius-md: 14px;
            --radius-sm: 10px;
            --shadow-soft: 0 18px 42px rgba(15, 23, 42, 0.08);
            --shadow-card: 0 12px 24px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: 'Sora', sans-serif;
            color: var(--ink-900);
            background:
                radial-gradient(circle at 88% 2%, rgba(255, 255, 255, 0.75) 0%, rgba(255, 255, 255, 0) 38%),
                radial-gradient(circle at 3% 26%, rgba(255, 255, 255, 0.45) 0%, rgba(255, 255, 255, 0) 30%),
                linear-gradient(145deg, rgba(31, 58, 92, 0.08), rgba(231, 185, 58, 0.07)),
                var(--surface);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .container {
            width: min(1120px, calc(100% - 36px));
            margin: 0 auto;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(8px);
            background: color-mix(in srgb, var(--topbar-bg) 82%, transparent 18%);
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }

        .topbar-inner {
            height: 74px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .brand-logo {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: var(--white);
            object-fit: contain;
            padding: 4px;
            flex-shrink: 0;
        }

        .brand-logo-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: var(--ink-900);
            font-size: 1rem;
            text-transform: uppercase;
        }

        .brand-title {
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: 0.02em;
            color: var(--topbar-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 280px;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 18px;
            font-size: 0.88rem;
            color: var(--topbar-text);
        }

        .menu a:hover {
            color: var(--topbar-hover);
        }

        .menu-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 999px;
            background: var(--btn-primary-bg);
            color: var(--btn-primary-text);
            font-size: 0.84rem;
            font-weight: 700;
            border: 1px solid transparent;
            transition: transform .18s ease, filter .2s ease;
        }

        .menu-cta:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
        }

        .hero {
            padding: 42px 0 24px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 24px;
            align-items: stretch;
        }

        .hero-left {
            border-radius: var(--radius-lg);
            padding: 34px;
            color: var(--hero-text);
            background:var(--brand-secondary) ;
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
        }

        .hero-left::after {
            content: "";
            position: absolute;
            right: -50px;
            bottom: -62px;
            width: 230px;
            height: 230px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.36), transparent 68%);
            pointer-events: none;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 999px;
            padding: 7px 12px;
        }

        .hero-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: clamp(2rem, 3vw + 0.9rem, 3.5rem);
            line-height: 1.04;
            margin: 15px 0 14px;
            letter-spacing: 0.01em;
            text-transform: uppercase;
        }

        .hero-subtitle {
            margin: 0 0 12px;
            font-size: clamp(1rem, 1.2vw, 1.15rem);
            color: rgba(255, 255, 255, 0.95);
            max-width: 48ch;
        }

        .hero-description {
            margin: 0;
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.7;
            max-width: 52ch;
            font-size: 0.94rem;
        }

        .hero-custom-image {
            margin-top: 14px;
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.28);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.22);
        }

        .hero-actions {
            margin-top: 22px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.86rem;
            padding: 12px 18px;
            transition: transform .15s ease, filter .2s ease, background .2s ease, color .2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
        }

        .btn-primary {
            background: var(--btn-primary-bg);
            color: var(--btn-primary-text);
            border: 1px solid rgba(17, 24, 39, 0.15);
        }

        .btn-secondary {
            background: var(--btn-secondary-bg);
            color: var(--btn-secondary-text);
            border: 1px solid rgba(255, 255, 255, 0.45);
        }

        .hero-meta {
            margin-top: 17px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .meta-chip {
            font-size: 0.73rem;
            border-radius: 999px;
            padding: 7px 11px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: rgba(255, 255, 255, 0.84);
            background: rgba(255, 255, 255, 0.08);
        }

        .hero-form {
            border-radius: var(--radius-lg);
            background: var(--hero-form-bg);
            border: 1px solid var(--line);
            box-shadow: var(--shadow-soft);
            padding: 24px;
            color: var(--hero-form-text);
        }

        .hero-form h3 {
            margin: 0;
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.6rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--hero-form-text);
        }

        .hero-form p {
            margin: 8px 0 16px;
            color: var(--ink-500);
            font-size: 0.87rem;
            line-height: 1.55;
        }

        .booking-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field label {
            font-size: 0.76rem;
            color: var(--ink-700);
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .field input,
        .field select {
            border: 1px solid var(--line);
            border-radius: var(--radius-sm);
            min-height: 42px;
            padding: 10px 12px;
            font-family: inherit;
            font-size: 0.88rem;
            color: var(--ink-900);
            background: #f8fbff;
        }

        .toggle-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            color: var(--ink-700);
            font-weight: 600;
        }

        .toggle-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--brand-primary);
        }

        .field .choices {
            margin-bottom: 0;
        }

        .field .choices__inner {
            border-radius: var(--radius-sm);
            border-color: var(--line);
            min-height: 42px;
            background: #f8fbff;
            font-size: 0.88rem;
            padding: 4px 10px;
        }

        .field .choices__input {
            background: transparent;
        }

        .field input:focus,
        .field select:focus {
            outline: none;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.07);
            background: #ffffff;
        }

        .booking-full {
            grid-column: 1 / -1;
        }

        .btn-submit {
            width: 100%;
            border: 0;
            min-height: 45px;
            margin-top: 6px;
            background: var(--btn-primary-bg);
            color: var(--btn-primary-text);
            cursor: pointer;
            font-family: inherit;
        }

        .booking-note {
            margin: 12px 0 0;
            font-size: 0.74rem;
            line-height: 1.5;
            color: var(--ink-500);
        }

        .section {
            padding: 28px 0 16px;
        }

        #frota.section {
            background: var(--section-frota-bg);
        }

        #vantagens.section {
            background: var(--section-vantagens-bg);
        }

        #como-funciona.section {
            background: var(--section-etapas-bg);
        }

        #contato.section {
            background: var(--section-contato-bg);
        }

        .section-head {
            margin-bottom: 14px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--brand-secondary);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .eyebrow::before {
            content: "";
            width: 18px;
            height: 2px;
            border-radius: 999px;
            background: var(--brand-accent);
        }

        .section-title {
            margin: 7px 0 5px;
            font-family: 'Barlow Condensed', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-size: clamp(1.55rem, 1.4vw + 1rem, 2.2rem);
            color: var(--ink-900);
        }

        .section-description {
            margin: 0;
            color: var(--ink-500);
            max-width: 68ch;
            line-height: 1.7;
            font-size: 0.9rem;
        }

        .fleet-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .fleet-card {
            border-radius: var(--radius-md);
            border: 1px solid var(--line);
            background: #ffffff;
            box-shadow: var(--shadow-card);
            padding: 18px;
            position: relative;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .fleet-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px rgba(15, 23, 42, 0.1);
        }

        .fleet-card::before {
            content: "";
            position: absolute;
            inset: -1px auto auto -1px;
            width: 120px;
            height: 120px;
            border-radius: 0 0 100% 0;
            background: linear-gradient(140deg, rgba(15, 23, 42, 0.05), rgba(15, 23, 42, 0));
            pointer-events: none;
        }

        .fleet-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            margin-bottom: 12px;
            position: relative;
        }

        .fleet-icon::before {
            content: "";
            position: absolute;
            left: 10px;
            top: 11px;
            width: 16px;
            height: 10px;
            border-radius: 3px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: -6px 10px 0 -4px rgba(255, 255, 255, 0.92), 8px 10px 0 -4px rgba(255, 255, 255, 0.92);
        }

        .fleet-card h3 {
            margin: 0 0 7px;
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink-900);
        }

        .fleet-card p {
            margin: 0;
            color: var(--ink-500);
            line-height: 1.64;
            font-size: 0.86rem;
        }

        .fleet-image {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--line);
            margin-bottom: 12px;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .vantagens-image-wrap {
            margin-top: 12px;
            margin-bottom: 14px;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid var(--line);
            box-shadow: var(--shadow-card);
            max-height: 260px;
        }

        .vantagens-image-wrap img {
            width: 100%;
            height: 100%;
            max-height: 260px;
            object-fit: cover;
            display: block;
        }

        .benefit-item {
            border-radius: 12px;
            border: 1px dashed rgba(100, 116, 139, 0.4);
            background: #ffffff;
            padding: 14px;
            font-size: 0.83rem;
            line-height: 1.55;
            color: var(--ink-700);
            font-weight: 600;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .step-card {
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid var(--line);
            padding: 17px 16px;
            box-shadow: var(--shadow-card);
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 0.84rem;
            font-weight: 800;
            background: var(--brand-primary);
            color: var(--brand-primary-text);
        }

        .step-card h4 {
            margin: 0 0 6px;
            font-size: 0.97rem;
            color: var(--ink-900);
        }

        .step-card p {
            margin: 0;
            color: var(--ink-500);
            line-height: 1.62;
            font-size: 0.84rem;
        }

        .contact-panel {
            border-radius: var(--radius-lg);
            border: 1px solid var(--line);
            background: #ffffff;
            box-shadow: var(--shadow-soft);
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .contact-item {
            border-radius: 12px;
            background: #f8fbff;
            border: 1px solid var(--line);
            padding: 12px;
            min-height: 88px;
        }

        .contact-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ink-500);
            margin-bottom: 7px;
            font-weight: 700;
        }

        .contact-value {
            font-size: 0.88rem;
            color: var(--ink-900);
            line-height: 1.5;
            font-weight: 600;
            word-break: break-word;
        }

        .contact-image {
            width: 100%;
            height: 100%;
            min-height: 88px;
            object-fit: cover;
            border-radius: 10px;
        }

        .footer {
            margin-top: 26px;
            border-top: 1px solid rgba(148, 163, 184, 0.25);
            padding: 18px 0 30px;
            background: var(--footer-bg);
            color: var(--footer-text);
            font-size: 0.78rem;
        }

        .footer-top {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 18px;
        }

        .footer-block {
            background: rgba(15, 23, 42, 0.06);
            border: 1px solid rgba(148, 163, 184, 0.2);
            padding: 14px 16px;
            border-radius: 14px;
        }

        .footer-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            margin-bottom: 10px;
            color: inherit;
        }

        .footer-item {
            font-size: 0.84rem;
            font-weight: 600;
            line-height: 1.5;
            margin-bottom: 6px;
            word-break: break-word;
        }

        .footer-item:last-child {
            margin-bottom: 0;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .reveal {
            opacity: 0;
            transform: translateY(16px);
            animation: reveal .55s ease forwards;
        }

        .delay-1 { animation-delay: .05s; }
        .delay-2 { animation-delay: .12s; }
        .delay-3 { animation-delay: .2s; }
        .delay-4 { animation-delay: .28s; }

        @keyframes reveal {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1030px) {
            .menu {
                display: none;
            }

            .hero-grid {
                grid-template-columns: 1fr;
            }

            .fleet-grid,
            .steps-grid {
                grid-template-columns: 1fr 1fr;
            }

            .benefits-grid {
                grid-template-columns: 1fr 1fr;
            }

            .contact-panel {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 700px) {
            .container {
                width: min(1120px, calc(100% - 24px));
            }

            .hero {
                padding-top: 18px;
            }

            .hero-left,
            .hero-form {
                padding: 18px;
                border-radius: 16px;
            }

            .booking-grid,
            .fleet-grid,
            .steps-grid,
            .benefits-grid,
            .contact-panel {
                grid-template-columns: 1fr;
            }

            .topbar-inner {
                height: 66px;
            }

            .brand-title {
                max-width: 180px;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <div class="brand">
                @if(!empty($empresa->logo_url))
                    <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}" class="brand-logo">
                @else
                    <div class="brand-logo brand-logo-fallback">{{ mb_substr($empresa->nome, 0, 1) }}</div>
                @endif
                <div class="brand-title">{{ $empresa->nome }}</div>
            </div>

            <nav class="menu">
                <a href="#frota">{{ $menuItemFrota }}</a>
                <a href="#vantagens">{{ $menuItemVantagens }}</a>
                <a href="#como-funciona">{{ $menuItemEtapas }}</a>
                <a href="#contato">{{ $menuItemContato }}</a>
                <a class="menu-cta" href="{{ $botaoUrl }}">{{ $botaoTexto }}</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <article class="hero-left reveal delay-1">
                    <span class="hero-tag">{{ $heroTag }}</span>
                    <h1 class="hero-title">{{ $titulo }}</h1>
                    <p class="hero-subtitle">{{ $subtitulo }}</p>
                    <p class="hero-description">{{ $descricao }}</p>
                    @if(!empty($imagemHero))
                        <img src="{{ $imagemHero }}" alt="Imagem de destaque {{ $empresa->nome }}" class="hero-custom-image">
                    @endif

                    <div class="hero-actions">
                        <a class="btn btn-primary" href="{{ $botaoUrl }}">{{ $botaoTexto }}</a>
                        <a class="btn btn-secondary" href="{{ $botaoLoginUrl }}">{{ $botaoAdminTexto }}</a>
                    </div>

                    <div class="hero-meta">
                        @if(!empty($contatoTelefone))
                            <span class="meta-chip">Atendimento: {{ $contatoTelefone }}</span>
                        @endif
                        @if(!empty($empresa->dominio))
                            <span class="meta-chip">Dominio: {{ $empresa->dominio }}</span>
                        @endif
                    </div>
                </article>

                <aside class="hero-form reveal delay-2">
                    <h3>{{ $heroFormTitulo }}</h3>
                    <p>{{ $heroFormDescricao }}</p>

                    <form method="GET" action="{{ $botaoUrl }}">
                        <div class="booking-grid">
                            <div class="field booking-full">
                                <label for="retirada">{{ $heroFormLabelRetirada }}</label>
                                <select id="retirada" name="retirada">
                                    <option value="">Selecione uma estacao</option>
                                    @foreach($estacoesSelect as $estacao)
                                        <option value="{{ $estacao['id'] }}">
                                            {{ $estacao['nome'] }}{{ $estacao['cidade'] !== '' ? ' - ' . $estacao['cidade'] : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label for="data_retirada">{{ $heroFormLabelDataRetirada }}</label>
                                <input id="data_retirada" name="data_retirada" type="date">
                            </div>
                            <div class="field">
                                <label for="hora_retirada">{{ $heroFormLabelHoraRetirada }}</label>
                                <input id="hora_retirada" name="hora_retirada" type="time">
                            </div>
                            <div class="field booking-full">
                                <div class="toggle-row">
                                    <input type="checkbox" id="mesmo_local" name="mesmo_local" checked>
                                    <label for="mesmo_local">Devolucao no mesmo local de retirada</label>
                                </div>
                            </div>
                            <div class="field booking-full" id="devolucaoField">
                                <label for="devolucao">{{ $heroFormLabelDevolucao ?? 'Local de devolucao' }}</label>
                                <select id="devolucao" name="devolucao">
                                    <option value="">Selecione uma estacao</option>
                                    @foreach($estacoesSelect as $estacao)
                                        <option value="{{ $estacao['id'] }}">
                                            {{ $estacao['nome'] }}{{ $estacao['cidade'] !== '' ? ' - ' . $estacao['cidade'] : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label for="data_devolucao">{{ $heroFormLabelDataDevolucao }}</label>
                                <input id="data_devolucao" name="data_devolucao" type="date">
                            </div>
                            <div class="field">
                                <label for="hora_devolucao">{{ $heroFormLabelHoraDevolucao }}</label>
                                <input id="hora_devolucao" name="hora_devolucao" type="time">
                            </div>
                            <div class="field booking-full">
                                <button class="btn btn-submit" type="submit">{{ $heroFormTextoBotao }}</button>
                            </div>
                        </div>
                    </form>

                    <p class="booking-note">{{ $heroFormNota }}</p>
                </aside>
            </div>
        </section>

        <section id="frota" class="section">
            <div class="container">
                <div class="section-head reveal delay-1">
                    <span class="eyebrow">{{ $frotaLabelSuperior }}</span>
                    <h2 class="section-title">{{ $frotaTitulo }}</h2>
                    <p class="section-description">{{ $frotaDescricao }}</p>
                </div>

                <div class="fleet-grid">
                    @foreach($frotaDestaques as $frota)
                        <article class="fleet-card reveal delay-2">
                            @if(!empty($frota['imagem']))
                                <img src="{{ $frota['imagem'] }}" alt="{{ $frota['titulo'] }}" class="fleet-image">
                            @endif
                            <div class="fleet-icon"></div>
                            <h3>{{ $frota['titulo'] }}</h3>
                            <p>{{ $frota['descricao'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="vantagens" class="section">
            <div class="container">
                <div class="section-head reveal delay-1">
                    <span class="eyebrow">{{ $vantagensLabelSuperior }}</span>
                    <h2 class="section-title">{{ $vantagensTitulo }}</h2>
                    <p class="section-description">{{ $vantagensDescricao }}</p>
                </div>

                @if(!empty($imagemVantagens))
                    <div class="vantagens-image-wrap reveal delay-2">
                        <img src="{{ $imagemVantagens }}" alt="Diferenciais {{ $empresa->nome }}">
                    </div>
                @endif

                <div class="benefits-grid">
                    @foreach($beneficios as $beneficio)
                        <div class="benefit-item reveal delay-2">{{ $beneficio }}</div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="como-funciona" class="section">
            <div class="container">
                <div class="section-head reveal delay-1">
                    <span class="eyebrow">{{ $etapasLabelSuperior }}</span>
                    <h2 class="section-title">{{ $etapasTitulo }}</h2>
                </div>

                <div class="steps-grid">
                    @foreach($etapasReserva as $index => $etapa)
                        <article class="step-card reveal delay-3">
                            <span class="step-number">{{ $index + 1 }}</span>
                            <h4>{{ $etapa['titulo'] }}</h4>
                            <p>{{ $etapa['descricao'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

    </main>

    <footer id="contato" class="footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-block">
                    <div class="footer-title">Contato direto</div>
                    <div class="footer-item">{{ $contatoTelefone ?: 'Telefone nao informado' }}</div>
                    <div class="footer-item">{{ $contatoEmail ?: 'E-mail nao informado' }}</div>
                </div>
                <div class="footer-block">
                    <div class="footer-title">Informacoes da locadora</div>
                    <div class="footer-item">{{ $empresa->nome }}</div>
                    @if(!empty($empresa->dominio))
                        <div class="footer-item">{{ $empresa->dominio }}</div>
                    @endif
                </div>
            </div>

            <div class="footer-inner">
            <span>&copy; {{ $anoAtual }} {{ $empresa->nome }}. {{ $footerTexto }}</span>
            <a href="{{ $botaoLoginUrl }}">{{ $botaoAdminTexto }}</a>
        </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        (function () {
            const retiradaSelect = document.getElementById('retirada');
            const devolucaoSelect = document.getElementById('devolucao');
            const devolucaoField = document.getElementById('devolucaoField');
            const mesmoLocal = document.getElementById('mesmo_local');
            const dataRetirada = document.getElementById('data_retirada');
            const horaRetirada = document.getElementById('hora_retirada');
            const dataDevolucao = document.getElementById('data_devolucao');
            const form = dataRetirada?.closest('form');

            function toLocalDateInput(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function toLocalTimeInput(date) {
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                return `${hours}:${minutes}`;
            }

            function addDays(date, days) {
                const copy = new Date(date.getTime());
                copy.setDate(copy.getDate() + days);
                return copy;
            }

            function normalizeMinDates() {
                if (!dataRetirada || !dataDevolucao) {
                    return;
                }

                const now = new Date();
                const today = toLocalDateInput(now);
                dataRetirada.min = today;

                const retiradaDate = dataRetirada.value ? new Date(`${dataRetirada.value}T00:00:00`) : now;
                const minDevolucao = addDays(retiradaDate, 1);
                dataDevolucao.min = toLocalDateInput(minDevolucao);
                if (dataDevolucao.value && new Date(`${dataDevolucao.value}T00:00:00`) < minDevolucao) {
                    dataDevolucao.value = toLocalDateInput(minDevolucao);
                }
            }

            function normalizeMinTime() {
                if (!dataRetirada || !horaRetirada) {
                    return;
                }

                const now = new Date();
                const today = toLocalDateInput(now);
                if (dataRetirada.value === today) {
                    horaRetirada.min = toLocalTimeInput(now);
                } else {
                    horaRetirada.removeAttribute('min');
                }
            }

            function validateDates() {
                if (!dataRetirada || !horaRetirada || !dataDevolucao) {
                    return true;
                }

                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                const retiradaDateOnly = dataRetirada.value ? new Date(`${dataRetirada.value}T00:00:00`) : null;
                const devolucaoDate = dataDevolucao.value ? new Date(`${dataDevolucao.value}T00:00:00`) : null;

                if (retiradaDateOnly && retiradaDateOnly < today) {
                    dataRetirada.setCustomValidity('A retirada nao pode ser antes de hoje.');
                    dataRetirada.reportValidity();
                    return false;
                }

                dataRetirada.setCustomValidity('');

                if (retiradaDateOnly && retiradaDateOnly.getTime() === today.getTime() && horaRetirada.value) {
                    const retiradaDateTime = new Date(`${dataRetirada.value}T${horaRetirada.value}`);
                    if (retiradaDateTime < now) {
                        horaRetirada.setCustomValidity('A hora de retirada nao pode ser menor do que agora.');
                        horaRetirada.reportValidity();
                        return false;
                    }
                }

                horaRetirada.setCustomValidity('');

                if (dataRetirada.value && dataDevolucao.value) {
                    const minDevolucao = addDays(new Date(`${dataRetirada.value}T00:00:00`), 1);
                    if (devolucaoDate < minDevolucao) {
                        dataDevolucao.setCustomValidity('A devolucao deve ser ao menos um dia apos a retirada.');
                        dataDevolucao.reportValidity();
                        return false;
                    }
                }

                dataDevolucao.setCustomValidity('');
                return true;
            }

            function syncDevolucaoVisibility() {
                if (!devolucaoField || !devolucaoSelect || !mesmoLocal) {
                    return;
                }

                const hide = mesmoLocal.checked;
                devolucaoField.style.display = hide ? 'none' : 'block';
                devolucaoSelect.disabled = hide;

                if (hide && retiradaSelect && retiradaSelect.value) {
                    devolucaoSelect.value = retiradaSelect.value;
                }
            }

            if (mesmoLocal) {
                mesmoLocal.addEventListener('change', syncDevolucaoVisibility);
            }

            if (retiradaSelect) {
                retiradaSelect.addEventListener('change', function () {
                    if (mesmoLocal && mesmoLocal.checked && devolucaoSelect) {
                        devolucaoSelect.value = retiradaSelect.value;
                    }
                });
            }
            if (typeof window.Choices === 'undefined') {
                return;
            }

            [retiradaSelect, devolucaoSelect].forEach((select) => {
                if (!select) {
                    return;
                }

                new window.Choices(select, {
                    searchEnabled: true,
                    searchPlaceholderValue: 'Digite para buscar estacao',
                    itemSelectText: '',
                    shouldSort: false,
                    noResultsText: 'Nenhuma estacao encontrada',
                    noChoicesText: 'Nenhuma estacao disponivel'
                });
            });

            if (dataRetirada) {
                dataRetirada.addEventListener('change', function () {
                    normalizeMinTime();
                    normalizeMinDates();
                    validateDates();
                });
            }

            if (horaRetirada) {
                horaRetirada.addEventListener('change', normalizeMinTime);
            }

            if (dataDevolucao) {
                dataDevolucao.addEventListener('change', function () {
                    normalizeMinDates();
                    validateDates();
                });
            }

            if (form) {
                form.addEventListener('submit', function (event) {
                    normalizeMinTime();
                    normalizeMinDates();
                    syncDevolucaoVisibility();
                    if (!validateDates()) {
                        event.preventDefault();
                    }
                });
            }

            normalizeMinDates();
            normalizeMinTime();
            syncDevolucaoVisibility();
        })();
    </script>
</body>
</html>
