<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }} | {{ $empresa->nome }}</title>
    <meta name="description" content="{{ $descricao }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,500;0,700;0,800;0,900;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <style>
        /* ══════════════════════════════════════════
           TOKENS
        ══════════════════════════════════════════ */
        :root {
            --brand-primary:          {{ $corPrimaria }};
            --brand-secondary:        {{ $corSecundaria }};
            --brand-accent:           {{ $corTerciaria }};
            --brand-primary-text:     {{ $corTextoPrimario }};
            --topbar-bg:              {{ $corTopbarFundo }};
            --topbar-text:            {{ $corTopbarTexto }};
            --topbar-hover:           {{ $corTopbarLinkHover }};
            --hero-bg:                {{ $corHeroFundo }};
            --hero-text:              {{ $corHeroTexto }};
            --form-bg:                {{ $corFormFundo }};
            --form-text:              {{ $corFormTexto }};
            --frota-bg:               {{ $corFrotaFundo }};
            --vantagens-bg:           {{ $corVantagensFundo }};
            --etapas-bg:              {{ $corEtapasFundo }};
            --contato-bg:             {{ $corContatoFundo }};
            --footer-bg:              {{ $corFooterFundo }};
            --footer-text:            {{ $corFooterTexto }};
            --btn-p-bg:               {{ $corBotaoPrimarioFundo }};
            --btn-p-text:             {{ $corBotaoPrimarioTexto }};
            --btn-s-bg:               {{ $corBotaoSecundarioFundo }};
            --btn-s-text:             {{ $corBotaoSecundarioTexto }};

            --ink:   #0b1220;
            --ink-2: #2d3a4f;
            --ink-3: #5e6e85;
            --line:  #dde4ef;
            --white: #ffffff;
            --off:   #f5f7fb;

            --r: 16px;
            --r-lg: 24px;

            --sh-sm:  0 2px 10px rgba(11,18,32,.06);
            --sh-md:  0 8px 28px rgba(11,18,32,.10);
            --sh-lg:  0 20px 56px rgba(11,18,32,.14);
            --sh-xl:  0 32px 80px rgba(11,18,32,.18);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: var(--off);
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: inherit; }
        img { display: block; }

        .wrap {
            width: min(1160px, calc(100% - 40px));
            margin-inline: auto;
        }

        /* ══════════════════════════════════════════
           TOPBAR
        ══════════════════════════════════════════ */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--topbar-bg);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 2px 20px rgba(11,18,32,.12);
        }

        .topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            height: 72px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            flex-shrink: 0;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            object-fit: contain;
            padding: 4px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.18);
        }

        .brand-logo-fallback {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--topbar-text);
            letter-spacing: .02em;
            flex-shrink: 0;
        }

        .brand-name {
            font-weight: 700;
            font-size: 1rem;
            color: var(--topbar-text);
            letter-spacing: -.01em;
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav a {
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--topbar-text);
            opacity: 0.82;
            padding: 7px 12px;
            border-radius: 8px;
            transition: opacity .15s, background .15s;
        }

        .nav a:hover {
            opacity: 1;
            color: var(--topbar-hover);
            background: rgba(255,255,255,0.06);
        }

        .nav-cta {
            margin-left: 8px;
            padding: 9px 18px !important;
            border-radius: 999px !important;
            background: var(--btn-p-bg) !important;
            color: var(--btn-p-text) !important;
            opacity: 1 !important;
            font-size: 0.84rem !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 14px rgba(0,0,0,.18);
            transition: transform .16s, box-shadow .16s !important;
        }

        .nav-cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 22px rgba(0,0,0,.24) !important;
            background: var(--btn-p-bg) !important;
        }

        /* ══════════════════════════════════════════
           HAMBURGER BUTTON
        ══════════════════════════════════════════ */
        .nav-toggle {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.16);
            cursor: pointer;
            flex-shrink: 0;
            transition: background .18s;
            padding: 0;
        }

        .nav-toggle:hover {
            background: rgba(255,255,255,0.18);
        }

        .nav-toggle-bar {
            display: block;
            width: 20px;
            height: 2px;
            background: var(--topbar-text);
            border-radius: 2px;
            transition: transform .25s ease, opacity .2s ease, width .2s ease;
            transform-origin: center;
        }

        /* X state */
        .nav-toggle.is-open .nav-toggle-bar:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .nav-toggle.is-open .nav-toggle-bar:nth-child(2) { opacity: 0; width: 0; }
        .nav-toggle.is-open .nav-toggle-bar:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* ══════════════════════════════════════════
           SIDE DRAWER
        ══════════════════════════════════════════ */
        .drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(11,18,32,0.52);
            z-index: 200;
            opacity: 0;
            pointer-events: none;
            transition: opacity .28s ease;
            backdrop-filter: blur(3px);
        }

        .drawer-overlay.is-open {
            opacity: 1;
            pointer-events: all;
        }

        .drawer {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 300px;
            background: var(--topbar-bg);
            z-index: 201;
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform .3s cubic-bezier(.22,.68,0,1.2);
            box-shadow: -20px 0 60px rgba(11,18,32,.28);
            overflow-y: auto;
        }

        .drawer.is-open {
            transform: translateX(0);
        }

        .drawer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 22px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }

        .drawer-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .drawer-brand-logo {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            object-fit: contain;
            padding: 3px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.16);
        }

        .drawer-brand-logo-fallback {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.16);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            font-weight: 800;
            color: var(--topbar-text);
        }

        .drawer-brand-name {
            font-size: .88rem;
            font-weight: 700;
            color: var(--topbar-text);
            max-width: 160px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .drawer-close {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            color: var(--topbar-text);
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .18s;
            flex-shrink: 0;
            line-height: 1;
        }

        .drawer-close:hover { background: rgba(239,68,68,0.25); }

        .drawer-nav {
            display: flex;
            flex-direction: column;
            padding: 16px 14px;
            gap: 4px;
            flex: 1;
        }

        .drawer-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            color: var(--topbar-text);
            opacity: .8;
            transition: background .16s, opacity .16s;
        }

        .drawer-nav a:hover {
            background: rgba(255,255,255,0.08);
            opacity: 1;
        }

        .drawer-nav .drawer-nav-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            flex-shrink: 0;
            color: var(--topbar-hover);
        }

        .drawer-nav-divider {
            height: 1px;
            background: rgba(255,255,255,0.07);
            margin: 8px 0;
        }

        .drawer-cta {
            padding: 16px 14px 20px;
            flex-shrink: 0;
        }

        .drawer-cta a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 13px;
            border-radius: 12px;
            background: var(--btn-p-bg);
            color: var(--btn-p-text);
            font-size: .9rem;
            font-weight: 800;
            box-shadow: 0 6px 20px rgba(0,0,0,.2);
            transition: transform .16s, box-shadow .16s;
        }

        .drawer-cta a:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 26px rgba(0,0,0,.26);
        }

        /* ══════════════════════════════════════════
           HERO — full bleed
        ══════════════════════════════════════════ */
        .hero {
            background: var(--hero-bg);
            padding: 0;
            position: relative;
            overflow: hidden;
        }

        /* Noise texture layer */
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(ellipse 80% 60% at 60% 120%, rgba(255,255,255,0.04) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 10% -10%, rgba(255,255,255,0.06) 0%, transparent 60%);
            pointer-events: none;
        }

        /* Decorative accent stripe */
        .hero::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--brand-accent), var(--brand-primary), var(--brand-secondary));
        }

        .hero-inner {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 0;
            min-height: 560px;
            align-items: stretch;
            position: relative;
            z-index: 1;
        }

        /* LEFT — text side */
        .hero-content {
            padding: 64px 60px 64px 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: var(--hero-text);
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--brand-accent);
            margin-bottom: 18px;
        }

        .hero-tag::before {
            content: '';
            display: block;
            width: 28px;
            height: 2px;
            background: var(--brand-accent);
            border-radius: 2px;
        }

        .hero-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: clamp(2.4rem, 3.8vw + 1rem, 4.4rem);
            font-weight: 900;
            line-height: .97;
            letter-spacing: -.01em;
            text-transform: uppercase;
            color: var(--hero-text);
            margin-bottom: 20px;
        }

        .hero-title em {
            font-style: italic;
            color: var(--brand-accent);
        }

        .hero-subtitle {
            font-size: clamp(.95rem, 1vw + .5rem, 1.1rem);
            line-height: 1.7;
            color: rgba(255,255,255,0.78);
            max-width: 46ch;
            margin-bottom: 14px;
        }

        .hero-description {
            font-size: .88rem;
            line-height: 1.75;
            color: rgba(255,255,255,0.58);
            max-width: 52ch;
            margin-bottom: 32px;
        }

        .hero-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 36px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: none;
            border-radius: 999px;
            font-family: inherit;
            font-weight: 700;
            font-size: .88rem;
            padding: 13px 22px;
            cursor: pointer;
            transition: transform .16s, box-shadow .16s, filter .16s;
        }

        .btn:hover { transform: translateY(-2px); }

        .btn-primary {
            background: var(--btn-p-bg);
            color: var(--btn-p-text);
            box-shadow: 0 6px 20px rgba(0,0,0,.22);
        }

        .btn-primary:hover { box-shadow: 0 12px 28px rgba(0,0,0,.28); }

        .btn-ghost {
            background: rgba(255,255,255,0.10);
            color: var(--hero-text);
            border: 1px solid rgba(255,255,255,0.22);
        }

        .btn-ghost:hover { background: rgba(255,255,255,0.16); }

        .hero-trust {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
        }

        .trust-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .73rem;
            font-weight: 600;
            color: rgba(255,255,255,0.64);
        }

        .trust-chip::before {
            content: '✓';
            display: inline-flex;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            align-items: center;
            justify-content: center;
            font-size: .68rem;
            font-weight: 800;
            color: var(--brand-accent);
        }

        .hero-image-strip {
            margin-top: 28px;
        }

        .hero-image-strip img {
            width: 100%;
            max-height: 220px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: var(--sh-lg);
        }

        /* RIGHT — booking form */
        .hero-form-col {
            background: var(--form-bg);
            border-left: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: stretch;
        }

        .booking-form {
            padding: 40px 32px;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .booking-form-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .02em;
            color: var(--form-text);
            margin-bottom: 4px;
            line-height: 1.05;
        }

        .booking-form-desc {
            font-size: .83rem;
            color: var(--ink-3);
            line-height: 1.55;
            margin-bottom: 22px;
        }

        .field-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .field-full { grid-column: 1 / -1; }

        .field label {
            font-size: .72rem;
            font-weight: 700;
            color: var(--ink-2);
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .field input,
        .field select {
            border: 1.5px solid var(--line);
            border-radius: 10px;
            min-height: 44px;
            padding: 10px 13px;
            font-family: inherit;
            font-size: .88rem;
            color: var(--ink);
            background: #fff;
            transition: border-color .18s, box-shadow .18s;
        }

        .field input:focus,
        .field select:focus {
            outline: none;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(11,18,32,.07);
        }

        .field .choices__inner {
            border-radius: 10px;
            border: 1.5px solid var(--line);
            min-height: 44px;
            background: #fff;
            font-size: .88rem;
            padding: 5px 10px;
            transition: border-color .18s;
        }

        .field .choices__input { background: transparent; font-size: .88rem; }
        .field .choices.is-focused .choices__inner { border-color: var(--brand-primary); }

        .toggle-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .78rem;
            font-weight: 600;
            color: var(--ink-2);
        }

        .toggle-row input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: var(--brand-primary);
            border-radius: 4px;
        }

        .btn-book {
            width: 100%;
            min-height: 48px;
            margin-top: 14px;
            border-radius: 12px;
            border: 0;
            font-size: .92rem;
            font-weight: 800;
            letter-spacing: .02em;
            background: var(--btn-p-bg);
            color: var(--btn-p-text);
            cursor: pointer;
            font-family: inherit;
            box-shadow: 0 6px 20px rgba(0,0,0,.2);
            transition: transform .16s, box-shadow .16s;
        }

        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0,0,0,.26);
        }

        .booking-note {
            margin-top: 12px;
            font-size: .72rem;
            color: var(--ink-3);
            line-height: 1.55;
            text-align: center;
        }

        /* ══════════════════════════════════════════
           SECTION BASE
        ══════════════════════════════════════════ */
        .section {
            padding: 80px 0;
        }

        .section--alt { background: #fff; }
        .section--frota   { background: var(--frota-bg); }
        .section--vant    { background: var(--vantagens-bg); }
        .section--etapas  { background: var(--etapas-bg); }
        .section--contato { background: var(--contato-bg); }

        .section-head {
            margin-bottom: 48px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--brand-secondary);
            margin-bottom: 12px;
        }

        .eyebrow::before {
            content: '';
            display: block;
            width: 22px;
            height: 2px;
            border-radius: 2px;
            background: var(--brand-accent);
        }

        .section-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: clamp(2rem, 2.4vw + .8rem, 3.2rem);
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -.01em;
            line-height: 1;
            color: var(--ink);
            margin-bottom: 14px;
        }

        .section-desc {
            font-size: .94rem;
            line-height: 1.75;
            color: var(--ink-3);
            max-width: 64ch;
        }

        /* ══════════════════════════════════════════
           FROTA
        ══════════════════════════════════════════ */
        .fleet-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .fleet-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            overflow: hidden;
            box-shadow: var(--sh-sm);
            transition: transform .22s, box-shadow .22s;
            position: relative;
        }

        .fleet-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--sh-lg);
        }

        .fleet-card-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
            background: linear-gradient(135deg, var(--off), #e8edf5);
        }

        .fleet-card-img-placeholder {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, #f0f4fa, #e3eaf5);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fleet-card-img-placeholder svg {
            width: 64px;
            opacity: .18;
        }

        .fleet-card-body {
            padding: 20px 22px 24px;
        }

        .fleet-card-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--brand-primary);
            color: var(--brand-primary-text);
            font-size: .76rem;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .fleet-card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
            letter-spacing: -.01em;
        }

        .fleet-card-desc {
            font-size: .86rem;
            line-height: 1.65;
            color: var(--ink-3);
        }

        /* Top accent line on card */
        .fleet-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-accent));
            opacity: 0;
            transition: opacity .22s;
        }

        .fleet-card:hover::before { opacity: 1; }

        /* ══════════════════════════════════════════
           VANTAGENS
        ══════════════════════════════════════════ */
        .vant-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: center;
        }

        .vant-image-wrap {
            border-radius: var(--r-lg);
            overflow: hidden;
            box-shadow: var(--sh-xl);
            position: relative;
        }

        .vant-image-wrap img {
            width: 100%;
            height: 420px;
            object-fit: cover;
            display: block;
        }

        /* Decorative corner accent */
        .vant-image-wrap::after {
            content: '';
            position: absolute;
            bottom: -16px;
            right: -16px;
            width: 120px;
            height: 120px;
            border-radius: var(--r);
            background: var(--brand-accent);
            z-index: -1;
            opacity: .55;
        }

        .vant-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .vant-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px 18px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: var(--sh-sm);
            transition: box-shadow .2s, transform .2s;
        }

        .vant-item:hover {
            box-shadow: var(--sh-md);
            transform: translateX(4px);
        }

        .vant-item-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Checkmark icon via CSS */
        .vant-item-icon::after {
            content: '✓';
            color: var(--brand-primary-text);
            font-size: 1rem;
            font-weight: 800;
        }

        .vant-item-text {
            font-size: .92rem;
            font-weight: 600;
            color: var(--ink-2);
            line-height: 1.5;
            padding-top: 9px;
        }

        /* Fallback: grid when no image */
        .vant-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .vant-grid .vant-item { margin: 0; }

        /* ══════════════════════════════════════════
           ETAPAS — timeline
        ══════════════════════════════════════════ */
        .steps-track {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0;
            position: relative;
        }

        /* Connecting line */
        .steps-track::before {
            content: '';
            position: absolute;
            top: 36px;
            left: calc(100% / 6);
            right: calc(100% / 6);
            height: 2px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-accent));
            z-index: 0;
        }

        .step-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            padding: 28px 24px 26px;
            margin: 0 10px;
            box-shadow: var(--sh-sm);
            position: relative;
            z-index: 1;
            transition: transform .2s, box-shadow .2s;
        }

        .step-card:first-child { margin-left: 0; }
        .step-card:last-child  { margin-right: 0; }

        .step-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--sh-md);
        }

        .step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--brand-primary);
            color: var(--brand-primary-text);
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.5rem;
            font-weight: 900;
            margin-bottom: 18px;
            box-shadow: 0 6px 18px rgba(0,0,0,.18);
            position: relative;
        }

        /* Outer ring */
        .step-num::before {
            content: '';
            position: absolute;
            inset: -5px;
            border-radius: 18px;
            border: 1.5px dashed rgba(0,0,0,.10);
        }

        .step-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
            letter-spacing: -.01em;
        }

        .step-card-desc {
            font-size: .86rem;
            line-height: 1.68;
            color: var(--ink-3);
        }

        /* ══════════════════════════════════════════
           CONTATO — CTA banner
        ══════════════════════════════════════════ */
        .contato-banner {
            background: var(--brand-primary);
            border-radius: var(--r-lg);
            padding: 52px 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            position: relative;
            overflow: hidden;
        }

        .contato-banner::before {
            content: '';
            position: absolute;
            right: -80px;
            top: -80px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.10) 0%, transparent 65%);
            pointer-events: none;
        }

        .contato-banner::after {
            content: '';
            position: absolute;
            left: -40px;
            bottom: -60px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0,0,0,0.08) 0%, transparent 65%);
            pointer-events: none;
        }

        .contato-text { position: relative; z-index: 1; }

        .contato-eyebrow {
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--brand-accent);
            margin-bottom: 10px;
        }

        .contato-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: clamp(1.8rem, 2.4vw + .6rem, 2.8rem);
            font-weight: 900;
            text-transform: uppercase;
            color: var(--brand-primary-text);
            line-height: 1;
            margin-bottom: 12px;
        }

        .contato-info {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 24px;
        }

        .contato-info-item {
            font-size: .88rem;
            color: rgba(255,255,255,0.78);
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .contato-info-item::before {
            content: '';
            display: block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--brand-accent);
        }

        .contato-cta { position: relative; z-index: 1; flex-shrink: 0; }

        .btn-contato {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 16px 30px;
            border-radius: 999px;
            background: var(--btn-s-bg);
            color: var(--btn-s-text);
            font-size: .92rem;
            font-weight: 800;
            letter-spacing: .01em;
            border: 1.5px solid rgba(255,255,255,0.3);
            box-shadow: 0 8px 24px rgba(0,0,0,.22);
            transition: transform .16s, box-shadow .16s;
        }

        .btn-contato:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(0,0,0,.28);
        }

        .contato-image-wrap {
            margin-top: 32px;
            border-radius: var(--r-lg);
            overflow: hidden;
            box-shadow: var(--sh-lg);
            max-height: 320px;
        }

        .contato-image-wrap img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: block;
        }

        /* ══════════════════════════════════════════
           FOOTER
        ══════════════════════════════════════════ */
        .footer {
            background: var(--footer-bg);
            color: var(--footer-text);
            padding: 52px 0 28px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        .footer-top {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 48px;
            padding-bottom: 36px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 24px;
        }

        .footer-brand {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .footer-brand-name {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -.01em;
        }

        .footer-brand-desc {
            font-size: .84rem;
            line-height: 1.65;
            opacity: .62;
            max-width: 42ch;
        }

        .footer-cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .footer-col-title {
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            opacity: .55;
            margin-bottom: 14px;
        }

        .footer-col-item {
            font-size: .86rem;
            font-weight: 600;
            opacity: .75;
            margin-bottom: 8px;
            line-height: 1.45;
            word-break: break-word;
        }

        .footer-col-item:last-child { margin-bottom: 0; }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            font-size: .78rem;
            opacity: .55;
        }

        .footer-bottom a {
            opacity: 1;
            color: inherit;
            transition: opacity .16s;
        }

        .footer-bottom a:hover { opacity: .85; }

        /* ══════════════════════════════════════════
           SCROLL REVEAL
        ══════════════════════════════════════════ */
        .reveal {
            opacity: 0;
            transform: translateY(20px);
            animation: appear .6s ease forwards;
        }

        .d1 { animation-delay: .06s; }
        .d2 { animation-delay: .14s; }
        .d3 { animation-delay: .22s; }
        .d4 { animation-delay: .30s; }

        @keyframes appear {
            to { opacity: 1; transform: none; }
        }

        /* ══════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════ */
        @media (max-width: 1060px) {
            .hero-inner {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .hero-content {
                padding: 52px 0 0;
            }

            .hero-form-col {
                border-left: none;
                border-top: 1px solid rgba(255,255,255,0.08);
            }

            .nav { display: none; }

            .fleet-grid { grid-template-columns: 1fr 1fr; }

            .steps-track { grid-template-columns: 1fr; }
            .steps-track::before { display: none; }
            .step-card { margin: 0; }
            .vant-layout { grid-template-columns: 1fr; }
            .vant-image-wrap img { height: 280px; }

            .contato-banner {
                flex-direction: column;
                padding: 36px 28px;
                text-align: center;
            }

            .contato-info { justify-content: center; }

            .footer-top { grid-template-columns: 1fr; gap: 32px; }
        }

        @media (max-width: 700px) {
            .section { padding: 56px 0; }

            .hero-content { padding: 40px 0 0; }

            .booking-form { padding: 28px 22px; }

            .fleet-grid,
            .vant-grid { grid-template-columns: 1fr; }

            .footer-cols { grid-template-columns: 1fr; }

            .contato-banner { padding: 28px 20px; }
        }
    </style>
</head>
<body>

    <!-- ══ DRAWER OVERLAY ══ -->
    <div class="drawer-overlay" id="drawerOverlay"></div>

    <!-- ══ DRAWER ══ -->
    <aside class="drawer" id="siteDrawer" aria-label="Menu lateral">
        <div class="drawer-header">
            <div class="drawer-brand">
                @if(!empty($empresa->logo_url))
                    <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}" class="drawer-brand-logo">
                @else
                    <div class="drawer-brand-logo-fallback">{{ mb_strtoupper(mb_substr($empresa->nome, 0, 1)) }}</div>
                @endif
                <span class="drawer-brand-name">{{ $empresa->nome }}</span>
            </div>
            <button class="drawer-close" id="drawerClose" aria-label="Fechar menu">&times;</button>
        </div>

        <nav class="drawer-nav">
            <a href="#frota" class="drawer-link">
                <span class="drawer-nav-icon">&#9632;</span>
                {{ $menuItemFrota }}
            </a>
            <a href="#vantagens" class="drawer-link">
                <span class="drawer-nav-icon">&#9650;</span>
                {{ $menuItemVantagens }}
            </a>
            <a href="#como-funciona" class="drawer-link">
                <span class="drawer-nav-icon">&#9679;</span>
                {{ $menuItemEtapas }}
            </a>
            <a href="#contato" class="drawer-link">
                <span class="drawer-nav-icon">&#9993;</span>
                {{ $menuItemContato }}
            </a>
            <div class="drawer-nav-divider"></div>
            <a href="{{ $botaoLoginUrl }}" class="drawer-link">
                <span class="drawer-nav-icon">&#128274;</span>
                {{ $botaoAdminTexto }}
            </a>
        </nav>

        <div class="drawer-cta">
            <a href="{{ $botaoUrl }}">{{ $botaoTexto }}</a>
        </div>
    </aside>

    <!-- ══ TOPBAR ══ -->
    <header class="topbar">
        <div class="wrap topbar-inner">
            <div class="brand">
                @if(!empty($empresa->logo_url))
                    <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}" class="brand-logo">
                @else
                    <div class="brand-logo-fallback">{{ mb_strtoupper(mb_substr($empresa->nome, 0, 1)) }}</div>
                @endif
                <span class="brand-name">{{ $empresa->nome }}</span>
            </div>

            <nav class="nav">
                <a href="#frota">{{ $menuItemFrota }}</a>
                <a href="#vantagens">{{ $menuItemVantagens }}</a>
                <a href="#como-funciona">{{ $menuItemEtapas }}</a>
                <a href="#contato">{{ $menuItemContato }}</a>
                <a class="nav-cta" href="{{ $botaoUrl }}">{{ $botaoTexto }}</a>
            </nav>

            <button class="nav-toggle" id="navToggle" aria-label="Abrir menu" aria-expanded="false">
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
                <span class="nav-toggle-bar"></span>
            </button>
        </div>
    </header>

    <!-- ══ HERO ══ -->
    <section class="hero">
        <div class="wrap">
            <div class="hero-inner">

                <div class="hero-content reveal d1">
                    @if(!empty($heroTag))
                        <span class="hero-tag">{{ $heroTag }}</span>
                    @endif

                    <h1 class="hero-title">{{ $titulo }}</h1>

                    @if(!empty($subtitulo))
                        <p class="hero-subtitle">{{ $subtitulo }}</p>
                    @endif

                    @if(!empty($descricao))
                        <p class="hero-description">{{ $descricao }}</p>
                    @endif

                    <div class="hero-btns">
                        <a class="btn btn-primary" href="{{ $botaoUrl }}">{{ $botaoTexto }}</a>
                        <a class="btn btn-ghost" href="{{ $botaoLoginUrl }}">{{ $botaoAdminTexto }}</a>
                    </div>

                    <div class="hero-trust">
                        @if(!empty($contatoTelefone))
                            <span class="trust-chip">{{ $contatoTelefone }}</span>
                        @endif
                        <span class="trust-chip">Reserva sem burocracia</span>
                        <span class="trust-chip">Pagamento seguro</span>
                    </div>

                    @if(!empty($imagemHero))
                        <div class="hero-image-strip">
                            <img src="{{ $imagemHero }}" alt="Destaque {{ $empresa->nome }}">
                        </div>
                    @endif
                </div>

                <div class="hero-form-col">
                    <div class="booking-form reveal d2">
                        <h2 class="booking-form-title">{{ $heroFormTitulo }}</h2>
                        @if(!empty($heroFormDescricao))
                            <p class="booking-form-desc">{{ $heroFormDescricao }}</p>
                        @endif

                        <form method="GET" action="{{ $botaoUrl }}">
                            <div class="field-group">
                                <div class="field field-full">
                                    <label for="retirada">{{ $heroFormLabelRetirada }}</label>
                                    <select id="retirada" name="retirada">
                                        <option value="">Selecione uma estacao</option>
                                        @foreach($estacoesSelect as $estacao)
                                            <option value="{{ $estacao['id'] }}">{{ $estacao['nome'] }}{{ $estacao['cidade'] !== '' ? ' — ' . $estacao['cidade'] : '' }}</option>
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

                                <div class="field field-full">
                                    <div class="toggle-row">
                                        <input type="checkbox" id="mesmo_local" name="mesmo_local" checked>
                                        <label for="mesmo_local">Devolver no mesmo local de retirada</label>
                                    </div>
                                </div>

                                <div class="field field-full" id="devolucaoField">
                                    <label for="devolucao">{{ $heroFormLabelDevolucao ?? 'Local de devolucao' }}</label>
                                    <select id="devolucao" name="devolucao">
                                        <option value="">Selecione uma estacao</option>
                                        @foreach($estacoesSelect as $estacao)
                                            <option value="{{ $estacao['id'] }}">{{ $estacao['nome'] }}{{ $estacao['cidade'] !== '' ? ' — ' . $estacao['cidade'] : '' }}</option>
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
                            </div>

                            <button type="submit" class="btn-book">{{ $heroFormTextoBotao }}</button>
                        </form>

                        @if(!empty($heroFormNota))
                            <p class="booking-note">{{ $heroFormNota }}</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══ FROTA ══ -->
    <section id="frota" class="section section--frota">
        <div class="wrap">
            <div class="section-head reveal d1">
                @if(!empty($frotaLabelSuperior))
                    <div class="eyebrow">{{ $frotaLabelSuperior }}</div>
                @endif
                <h2 class="section-title">{{ $frotaTitulo }}</h2>
                @if(!empty($frotaDescricao))
                    <p class="section-desc">{{ $frotaDescricao }}</p>
                @endif
            </div>

            <div class="fleet-grid">
                @foreach($frotaDestaques as $i => $frota)
                    <article class="fleet-card reveal d{{ $i + 2 }}">
                        @if(!empty($frota['imagem']))
                            <img src="{{ $frota['imagem'] }}" alt="{{ $frota['titulo'] }}" class="fleet-card-img">
                        @else
                            <div class="fleet-card-img-placeholder">
                                <svg viewBox="0 0 64 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="4" y="10" width="56" height="16" rx="4" fill="currentColor"/>
                                    <circle cx="14" cy="26" r="5" fill="currentColor"/>
                                    <circle cx="50" cy="26" r="5" fill="currentColor"/>
                                    <path d="M10 10 L18 4 H46 L56 10" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </div>
                        @endif
                        <div class="fleet-card-body">
                            <div class="fleet-card-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                            <h3 class="fleet-card-title">{{ $frota['titulo'] }}</h3>
                            <p class="fleet-card-desc">{{ $frota['descricao'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ══ VANTAGENS ══ -->
    <section id="vantagens" class="section section--alt section--vant">
        <div class="wrap">
            <div class="section-head reveal d1">
                @if(!empty($vantagensLabelSuperior))
                    <div class="eyebrow">{{ $vantagensLabelSuperior }}</div>
                @endif
                <h2 class="section-title">{{ $vantagensTitulo }}</h2>
                @if(!empty($vantagensDescricao))
                    <p class="section-desc">{{ $vantagensDescricao }}</p>
                @endif
            </div>

            @if(!empty($imagemVantagens))
                <div class="vant-layout reveal d2">
                    <div class="vant-image-wrap">
                        <img src="{{ $imagemVantagens }}" alt="Diferenciais {{ $empresa->nome }}">
                    </div>
                    <div class="vant-list">
                        @foreach($beneficios as $beneficio)
                            @if(!empty($beneficio))
                                <div class="vant-item">
                                    <div class="vant-item-icon"></div>
                                    <span class="vant-item-text">{{ $beneficio }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <div class="vant-grid reveal d2">
                    @foreach($beneficios as $beneficio)
                        @if(!empty($beneficio))
                            <div class="vant-item">
                                <div class="vant-item-icon"></div>
                                <span class="vant-item-text">{{ $beneficio }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- ══ COMO FUNCIONA ══ -->
    <section id="como-funciona" class="section section--etapas">
        <div class="wrap">
            <div class="section-head reveal d1">
                @if(!empty($etapasLabelSuperior))
                    <div class="eyebrow">{{ $etapasLabelSuperior }}</div>
                @endif
                <h2 class="section-title">{{ $etapasTitulo }}</h2>
            </div>

            <div class="steps-track">
                @foreach($etapasReserva as $index => $etapa)
                    <article class="step-card reveal d{{ $index + 2 }}">
                        <div class="step-num">{{ $index + 1 }}</div>
                        <h4 class="step-card-title">{{ $etapa['titulo'] }}</h4>
                        <p class="step-card-desc">{{ $etapa['descricao'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ══ CONTATO ══ -->
    <section id="contato" class="section section--contato">
        <div class="wrap">
            <div class="contato-banner reveal d1">
                <div class="contato-text">
                    @if(!empty($contatoLabelSuperior))
                        <div class="contato-eyebrow">{{ $contatoLabelSuperior }}</div>
                    @endif
                    <div class="contato-title">{{ $contatoTitulo }}</div>
                    <div class="contato-info">
                        @if(!empty($contatoTelefone))
                            <span class="contato-info-item">{{ $contatoTelefone }}</span>
                        @endif
                        @if(!empty($contatoEmail))
                            <span class="contato-info-item">{{ $contatoEmail }}</span>
                        @endif
                        @if(!empty($contatoEndereco))
                            <span class="contato-info-item">{{ $contatoEndereco }}</span>
                        @endif
                    </div>
                </div>
                <div class="contato-cta">
                    <a class="btn-contato" href="{{ $botaoUrl }}">{{ $botaoTexto }}</a>
                </div>
            </div>

            @if(!empty($imagemContato))
                <div class="contato-image-wrap reveal d2">
                    <img src="{{ $imagemContato }}" alt="Contato {{ $empresa->nome }}">
                </div>
            @endif
        </div>
    </section>

    <!-- ══ FOOTER ══ -->
    <footer class="footer">
        <div class="wrap">
            <div class="footer-top">
                <div class="footer-brand">
                    <div class="footer-brand-name">{{ $empresa->nome }}</div>
                    @if(!empty($footerTexto))
                        <div class="footer-brand-desc">{{ $footerTexto }}</div>
                    @endif
                </div>
                <div class="footer-cols">
                    <div>
                        <div class="footer-col-title">Contato</div>
                        @if(!empty($contatoTelefone))
                            <div class="footer-col-item">{{ $contatoTelefone }}</div>
                        @endif
                        @if(!empty($contatoEmail))
                            <div class="footer-col-item">{{ $contatoEmail }}</div>
                        @endif
                        @if(!empty($contatoEndereco))
                            <div class="footer-col-item">{{ $contatoEndereco }}</div>
                        @endif
                    </div>
                    <div>
                        <div class="footer-col-title">Navegacao</div>
                        <div class="footer-col-item"><a href="#frota">{{ $menuItemFrota }}</a></div>
                        <div class="footer-col-item"><a href="#vantagens">{{ $menuItemVantagens }}</a></div>
                        <div class="footer-col-item"><a href="#como-funciona">{{ $menuItemEtapas }}</a></div>
                        <div class="footer-col-item"><a href="#contato">{{ $menuItemContato }}</a></div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <span>&copy; {{ $anoAtual }} {{ $empresa->nome }}. Todos os direitos reservados.</span>
                <a href="{{ $botaoLoginUrl }}">{{ $botaoAdminTexto }}</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        /* ── DRAWER ── */
        (function () {
            const toggle  = document.getElementById('navToggle');
            const drawer  = document.getElementById('siteDrawer');
            const overlay = document.getElementById('drawerOverlay');
            const close   = document.getElementById('drawerClose');
            const links   = drawer.querySelectorAll('.drawer-link');

            function openDrawer() {
                drawer.classList.add('is-open');
                overlay.classList.add('is-open');
                toggle.classList.add('is-open');
                toggle.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            function closeDrawer() {
                drawer.classList.remove('is-open');
                overlay.classList.remove('is-open');
                toggle.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            toggle.addEventListener('click', () => {
                drawer.classList.contains('is-open') ? closeDrawer() : openDrawer();
            });

            close.addEventListener('click', closeDrawer);
            overlay.addEventListener('click', closeDrawer);

            links.forEach(link => {
                link.addEventListener('click', closeDrawer);
            });

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeDrawer();
            });
        })();
    </script>
    <script>
        (function () {
            const retiradaSelect  = document.getElementById('retirada');
            const devolucaoSelect = document.getElementById('devolucao');
            const devolucaoField  = document.getElementById('devolucaoField');
            const mesmoLocal      = document.getElementById('mesmo_local');
            const dataRetirada    = document.getElementById('data_retirada');
            const horaRetirada    = document.getElementById('hora_retirada');
            const dataDevolucao   = document.getElementById('data_devolucao');
            const form            = dataRetirada?.closest('form');

            function toLocalDateInput(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }

            function toLocalTimeInput(date) {
                const h = String(date.getHours()).padStart(2, '0');
                const mi = String(date.getMinutes()).padStart(2, '0');
                return `${h}:${mi}`;
            }

            function addDays(date, days) {
                const copy = new Date(date.getTime());
                copy.setDate(copy.getDate() + days);
                return copy;
            }

            function normalizeMinDates() {
                if (!dataRetirada || !dataDevolucao) return;
                const now = new Date();
                dataRetirada.min = toLocalDateInput(now);
                const retiradaDate  = dataRetirada.value ? new Date(`${dataRetirada.value}T00:00:00`) : now;
                const minDevolucao  = addDays(retiradaDate, 1);
                dataDevolucao.min   = toLocalDateInput(minDevolucao);
                if (dataDevolucao.value && new Date(`${dataDevolucao.value}T00:00:00`) < minDevolucao) {
                    dataDevolucao.value = toLocalDateInput(minDevolucao);
                }
            }

            function normalizeMinTime() {
                if (!dataRetirada || !horaRetirada) return;
                const now   = new Date();
                const today = toLocalDateInput(now);
                if (dataRetirada.value === today) {
                    horaRetirada.min = toLocalTimeInput(now);
                } else {
                    horaRetirada.removeAttribute('min');
                }
            }

            function validateDates() {
                if (!dataRetirada || !horaRetirada || !dataDevolucao) return true;
                const now          = new Date();
                const today        = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                const retiradaDate = dataRetirada.value ? new Date(`${dataRetirada.value}T00:00:00`) : null;
                const devolDate    = dataDevolucao.value ? new Date(`${dataDevolucao.value}T00:00:00`) : null;

                if (retiradaDate && retiradaDate < today) {
                    dataRetirada.setCustomValidity('A retirada nao pode ser antes de hoje.');
                    dataRetirada.reportValidity();
                    return false;
                }
                dataRetirada.setCustomValidity('');

                if (retiradaDate && retiradaDate.getTime() === today.getTime() && horaRetirada.value) {
                    const dt = new Date(`${dataRetirada.value}T${horaRetirada.value}`);
                    if (dt < now) {
                        horaRetirada.setCustomValidity('A hora de retirada nao pode ser no passado.');
                        horaRetirada.reportValidity();
                        return false;
                    }
                }
                horaRetirada.setCustomValidity('');

                if (dataRetirada.value && dataDevolucao.value) {
                    const minDevol = addDays(new Date(`${dataRetirada.value}T00:00:00`), 1);
                    if (devolDate < minDevol) {
                        dataDevolucao.setCustomValidity('A devolucao deve ser ao menos 1 dia apos a retirada.');
                        dataDevolucao.reportValidity();
                        return false;
                    }
                }
                dataDevolucao.setCustomValidity('');
                return true;
            }

            function syncDevolucao() {
                if (!devolucaoField || !devolucaoSelect || !mesmoLocal) return;
                const hide = mesmoLocal.checked;
                devolucaoField.style.display = hide ? 'none' : 'block';
                devolucaoSelect.disabled     = hide;
                if (hide && retiradaSelect?.value) devolucaoSelect.value = retiradaSelect.value;
            }

            mesmoLocal?.addEventListener('change', syncDevolucao);
            retiradaSelect?.addEventListener('change', () => {
                if (mesmoLocal?.checked && devolucaoSelect) devolucaoSelect.value = retiradaSelect.value;
            });

            if (typeof window.Choices !== 'undefined') {
                [retiradaSelect, devolucaoSelect].forEach(sel => {
                    if (!sel) return;
                    new window.Choices(sel, {
                        searchEnabled: true,
                        searchPlaceholderValue: 'Buscar estacao...',
                        itemSelectText: '',
                        shouldSort: false,
                        noResultsText: 'Nenhuma estacao encontrada',
                        noChoicesText: 'Nenhuma estacao disponivel'
                    });
                });
            }

            dataRetirada?.addEventListener('change', () => { normalizeMinTime(); normalizeMinDates(); validateDates(); });
            horaRetirada?.addEventListener('change', normalizeMinTime);
            dataDevolucao?.addEventListener('change', () => { normalizeMinDates(); validateDates(); });

            form?.addEventListener('submit', e => {
                normalizeMinTime();
                normalizeMinDates();
                syncDevolucao();
                if (!validateDates()) e.preventDefault();
            });

            normalizeMinDates();
            normalizeMinTime();
            syncDevolucao();
        })();
    </script>
</body>
</html>
