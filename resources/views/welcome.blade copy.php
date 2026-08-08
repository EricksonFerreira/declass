<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="DECLOCK — Locadora de veículos premium. Reserve agora e dirija com conforto, segurança e estilo.">
    <title>DECLOCK — Locadora de Veículos Premium</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">

    <style>
        /* ─── Reset ─── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; font-size: 16px; }
        body { font-family: 'Inter', sans-serif; background: #0a0a0f; color: #f1f5f9; overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; display: block; }
        ul { list-style: none; }

        /* ─── CSS Variables ─── */
        :root {
            --red:      #e63946;
            --red-dark: #b91c2a;
            --red-glow: rgba(230,57,70,.25);
            --dark:     #0a0a0f;
            --dark2:    #111118;
            --dark3:    #16161f;
            --dark4:    #1e1e2a;
            --border:   rgba(255,255,255,.07);
            --muted:    #94a3b8;
            --white:    #f1f5f9;
        }

        /* ─── Navbar ─── */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 5%; height: 72px;
            background: rgba(10,10,15,.82);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--border);
            transition: background .3s;
        }
        .navbar.scrolled { background: rgba(10,10,15,.97); }

        .nav-logo {
            display: flex; align-items: center; gap: 10px;
            font-family: 'Syne', sans-serif; font-size: 1.55rem; font-weight: 800;
        }
        .nav-logo .logo-de { color: var(--white); }
        .nav-logo .logo-cars { color: var(--red); }
        .nav-logo-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: var(--red); display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: #fff; flex-shrink: 0;
        }

        .nav-links { display: flex; align-items: center; gap: 2rem; }
        .nav-links a {
            font-size: .88rem; font-weight: 500; color: var(--muted);
            transition: color .2s;
        }
        .nav-links a:hover { color: var(--white); }

        .nav-cta {
            display: flex; align-items: center; gap: .75rem;
        }
        .btn-outline {
            padding: .5rem 1.2rem; border: 1px solid rgba(255,255,255,.18);
            border-radius: 8px; font-size: .84rem; font-weight: 600; color: var(--white);
            transition: all .2s;
        }
        .btn-outline:hover { border-color: var(--red); color: var(--red); }
        .btn-red {
            padding: .5rem 1.4rem; background: var(--red); color: #fff;
            border-radius: 8px; font-size: .84rem; font-weight: 700;
            transition: all .2s; border: none; cursor: pointer;
        }
        .btn-red:hover { background: var(--red-dark); transform: translateY(-1px); box-shadow: 0 6px 20px var(--red-glow); }

        .nav-hamburger { display: none; background: none; border: none; color: var(--white); font-size: 1.4rem; cursor: pointer; }

        /* ─── Hero ─── */
        .hero {
            min-height: 100vh;
            background:
                radial-gradient(ellipse 80% 60% at 55% 40%, rgba(230,57,70,.12) 0%, transparent 65%),
                radial-gradient(ellipse 60% 50% at 10% 80%, rgba(59,130,246,.07) 0%, transparent 60%),
                var(--dark);
            display: flex; flex-direction: column;
            padding-top: 72px;
            position: relative; overflow: hidden;
        }

        /* Animated grid lines */
        .hero-grid {
            position: absolute; inset: 0; pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 72px 72px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 20%, black 30%, transparent 100%);
        }

        .hero-inner {
            flex: 1; display: flex; align-items: center;
            max-width: 1280px; width: 100%; margin: 0 auto; padding: 5rem 5% 3rem;
            gap: 3rem;
        }

        .hero-text { flex: 1; min-width: 0; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(230,57,70,.12); border: 1px solid rgba(230,57,70,.28);
            color: #f87171; font-size: .78rem; font-weight: 700;
            padding: .35rem .9rem; border-radius: 999px; margin-bottom: 1.5rem;
            letter-spacing: .06em; text-transform: uppercase;
        }
        .hero-badge i { font-size: .7rem; }

        .hero-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2.6rem, 5.5vw, 4.2rem);
            font-weight: 800; line-height: 1.1;
            color: var(--white);
            margin-bottom: 1.25rem;
        }
        .hero-title .accent { color: var(--red); }
        .hero-title .gradient-text {
            background: linear-gradient(135deg, #f87171, #fbbf24);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .hero-sub {
            font-size: 1.05rem; color: var(--muted); line-height: 1.7;
            max-width: 540px; margin-bottom: 2.5rem;
        }

        .hero-btns { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 3rem; }
        .btn-hero-primary {
            display: inline-flex; align-items: center; gap: .6rem;
            background: var(--red); color: #fff;
            padding: .85rem 1.8rem; border-radius: 12px;
            font-size: .95rem; font-weight: 700;
            transition: all .25s; box-shadow: 0 4px 20px var(--red-glow);
        }
        .btn-hero-primary:hover { background: var(--red-dark); transform: translateY(-2px); box-shadow: 0 8px 30px var(--red-glow); }
        .btn-hero-secondary {
            display: inline-flex; align-items: center; gap: .6rem;
            background: rgba(255,255,255,.07); border: 1px solid var(--border);
            color: var(--white); padding: .85rem 1.8rem; border-radius: 12px;
            font-size: .95rem; font-weight: 600; transition: all .2s;
        }
        .btn-hero-secondary:hover { background: rgba(255,255,255,.12); }

        .hero-trust {
            display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;
        }
        .trust-item { display: flex; align-items: center; gap: .45rem; font-size: .82rem; color: var(--muted); }
        .trust-item i { color: var(--red); font-size: .9rem; }

        /* ─── Hero visual ─── */
        .hero-visual {
            flex: 1; min-width: 0; position: relative;
            display: flex; align-items: center; justify-content: center;
        }
        .hero-car-wrap {
            position: relative; width: 100%; max-width: 600px;
        }
        .hero-car-glow {
            position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%);
            width: 80%; height: 60px;
            background: radial-gradient(ellipse, rgba(230,57,70,.45), transparent 70%);
            filter: blur(18px); pointer-events: none;
        }
        .hero-car-svg {
            width: 100%;
            filter: drop-shadow(0 20px 60px rgba(230,57,70,.2));
            animation: float 4s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-14px); }
        }

        /* Floating chips */
        .hero-chip {
            position: absolute;
            background: rgba(30,30,42,.9); border: 1px solid var(--border);
            border-radius: 14px; padding: .65rem 1rem;
            display: flex; align-items: center; gap: .6rem;
            backdrop-filter: blur(12px);
            animation: chipFloat 5s ease-in-out infinite;
        }
        .hero-chip:nth-child(2) { top: 8%; right: 0; animation-delay: 0s; }
        .hero-chip:nth-child(3) { bottom: 20%; left: -5%;  animation-delay: 1.5s; }
        .hero-chip:nth-child(4) { top: 42%; right: -2%; animation-delay: .8s; }

        @keyframes chipFloat {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }
        .chip-icon {
            width: 34px; height: 34px; border-radius: 9px;
            background: rgba(230,57,70,.15); display: flex; align-items: center; justify-content: center;
            font-size: .9rem; color: var(--red); flex-shrink: 0;
        }
        .chip-label { font-size: .72rem; color: var(--muted); }
        .chip-val { font-size: .9rem; font-weight: 700; color: var(--white); }

        /* ─── Search bar ─── */
        .search-section {
            background: var(--dark);
            padding: 0 5% 0;
        }
        .search-bar {
            max-width: 1280px; margin: 0 auto;
            background: var(--dark3);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 1.5rem 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 1rem;
            transform: translateY(-36px);
            box-shadow: 0 20px 60px rgba(0,0,0,.5);
        }
        .search-field { display: flex; flex-direction: column; gap: .3rem; }
        .search-field label { font-size: .73rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); }
        .search-field input, .search-field select {
            background: var(--dark4); border: 1px solid var(--border);
            color: var(--white); border-radius: 10px; padding: .65rem .9rem;
            font-size: .9rem; font-family: inherit; outline: none;
            transition: border-color .2s;
        }
        .search-field input:focus, .search-field select:focus { border-color: var(--red); }
        .search-field select option { background: var(--dark3); }
        .search-submit {
            display: flex; align-items: flex-end;
        }
        .search-submit button {
            width: 100%; padding: .68rem 1.6rem;
            background: var(--red); color: #fff; border: none; border-radius: 10px;
            font-size: .9rem; font-weight: 700; cursor: pointer; font-family: inherit;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            transition: all .2s; white-space: nowrap;
        }
        .search-submit button:hover { background: var(--red-dark); }

        /* ─── Sections common ─── */
        section { padding: 6rem 5%; }
        .section-inner { max-width: 1280px; margin: 0 auto; }

        .section-eyebrow {
            display: inline-flex; align-items: center; gap: .5rem;
            font-size: .75rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
            color: var(--red); margin-bottom: .75rem;
        }
        .section-eyebrow::before {
            content: ''; display: block; width: 22px; height: 2px; background: var(--red); border-radius: 2px;
        }
        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(1.9rem, 3.5vw, 2.75rem);
            font-weight: 800; color: var(--white); line-height: 1.2;
            margin-bottom: .9rem;
        }
        .section-sub { font-size: 1rem; color: var(--muted); line-height: 1.7; max-width: 560px; }

        /* ─── Stats ─── */
        .stats-section {
            padding: 3rem 5% 6rem;
            background: var(--dark);
        }
        .stats-grid {
            max-width: 1280px; margin: 0 auto;
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 1px; background: var(--border); border-radius: 20px; overflow: hidden;
            border: 1px solid var(--border);
        }
        .stat-item {
            background: var(--dark3); padding: 2.5rem 2rem;
            text-align: center;
            transition: background .2s;
        }
        .stat-item:hover { background: var(--dark4); }
        .stat-num {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2.4rem, 4vw, 3.2rem); font-weight: 800;
            color: var(--white); line-height: 1; margin-bottom: .4rem;
        }
        .stat-num span { color: var(--red); }
        .stat-label { font-size: .85rem; color: var(--muted); font-weight: 500; }

        /* ─── How it works ─── */
        .hiw-section { background: var(--dark2); }
        .hiw-header { text-align: center; margin-bottom: 4rem; }
        .hiw-header .section-sub { margin: 0 auto; }
        .hiw-steps {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem; position: relative;
        }
        .hiw-connector {
            position: absolute; top: 36px; left: calc(16.66% + 1rem); right: calc(16.66% + 1rem);
            height: 1px; background: linear-gradient(90deg, var(--red) 0%, rgba(230,57,70,.15) 100%);
            pointer-events: none;
        }
        .hiw-step {
            background: var(--dark3); border: 1px solid var(--border);
            border-radius: 20px; padding: 2rem 1.75rem;
            position: relative; transition: all .2s;
        }
        .hiw-step:hover { border-color: rgba(230,57,70,.35); transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,.3); }
        .hiw-num {
            position: absolute; top: -18px; left: 1.75rem;
            background: var(--red); color: #fff; width: 36px; height: 36px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: .82rem; font-weight: 800;
        }
        .hiw-icon {
            width: 54px; height: 54px; border-radius: 14px;
            background: rgba(230,57,70,.1); display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: var(--red); margin-bottom: 1.2rem; margin-top: .5rem;
        }
        .hiw-step h3 { font-size: 1.05rem; font-weight: 700; color: var(--white); margin-bottom: .6rem; }
        .hiw-step p { font-size: .86rem; color: var(--muted); line-height: 1.65; }

        /* ─── Fleet ─── */
        .fleet-section { background: var(--dark); }
        .fleet-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1rem; }
        .fleet-tabs {
            display: flex; gap: .5rem; flex-wrap: wrap;
        }
        .fleet-tab {
            padding: .45rem 1.1rem; border-radius: 999px;
            border: 1px solid var(--border); background: transparent;
            color: var(--muted); font-size: .82rem; font-weight: 600;
            cursor: pointer; font-family: inherit; transition: all .2s;
        }
        .fleet-tab.active, .fleet-tab:hover { background: var(--red); color: #fff; border-color: var(--red); }

        .fleet-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.25rem;
        }
        .fleet-card {
            background: var(--dark3); border: 1px solid var(--border);
            border-radius: 20px; overflow: hidden;
            transition: all .25s; cursor: pointer;
        }
        .fleet-card:hover { border-color: rgba(230,57,70,.35); transform: translateY(-5px); box-shadow: 0 16px 48px rgba(0,0,0,.4); }
        .fleet-card-img {
            height: 160px; background: var(--dark4);
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        .fleet-card-img svg { width: 80%; max-height: 130px; }
        .fleet-category-badge {
            position: absolute; top: 12px; left: 12px;
            background: rgba(0,0,0,.55); backdrop-filter: blur(6px);
            color: var(--muted); font-size: .7rem; font-weight: 700;
            padding: .25rem .6rem; border-radius: 6px; text-transform: uppercase; letter-spacing: .06em;
        }
        .fleet-card-body { padding: 1.25rem 1.4rem; }
        .fleet-car-name { font-size: 1rem; font-weight: 700; color: var(--white); margin-bottom: .25rem; }
        .fleet-car-sub { font-size: .78rem; color: var(--muted); margin-bottom: 1rem; }
        .fleet-specs {
            display: flex; gap: 1rem; margin-bottom: 1.1rem; flex-wrap: wrap;
        }
        .fleet-spec { font-size: .76rem; color: var(--muted); display: flex; align-items: center; gap: .3rem; }
        .fleet-spec i { color: var(--red); font-size: .72rem; }
        .fleet-price-row { display: flex; align-items: center; justify-content: space-between; }
        .fleet-price { font-size: 1.2rem; font-weight: 800; color: var(--white); }
        .fleet-price small { font-size: .72rem; color: var(--muted); font-weight: 400; }
        .fleet-btn {
            padding: .45rem 1rem; background: var(--red); color: #fff;
            border: none; border-radius: 8px; font-size: .8rem; font-weight: 700;
            cursor: pointer; font-family: inherit; transition: all .2s;
        }
        .fleet-btn:hover { background: var(--red-dark); }

        /* ─── Features ─── */
        .features-section { background: var(--dark2); }
        .features-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; align-items: center; }
        .features-visual {
            position: relative;
        }
        .features-img-wrap {
            border-radius: 24px; overflow: hidden;
            background: var(--dark3); border: 1px solid var(--border);
            padding: 2rem; aspect-ratio: 1 / 1; display: flex; align-items: center; justify-content: center;
        }
        .features-big-icon {
            font-size: 10rem; color: rgba(230,57,70,.15);
            position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
        }
        .features-badge-float {
            position: absolute; background: var(--dark3); border: 1px solid var(--border);
            border-radius: 14px; padding: 1rem 1.2rem;
            display: flex; align-items: center; gap: .7rem;
            backdrop-filter: blur(12px); box-shadow: 0 8px 30px rgba(0,0,0,.4);
        }
        .fbf-top { top: -12px; right: -20px; }
        .fbf-bottom { bottom: -12px; left: -20px; }
        .features-badge-float .ficon {
            width: 40px; height: 40px; border-radius: 10px;
            background: var(--red); display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1rem; flex-shrink: 0;
        }
        .features-badge-float .flabel { font-size: .72rem; color: var(--muted); }
        .features-badge-float .fval { font-size: .95rem; font-weight: 700; color: var(--white); }

        .features-list { display: flex; flex-direction: column; gap: 1.25rem; margin-top: 2.5rem; }
        .feature-item {
            display: flex; align-items: flex-start; gap: 1rem;
            background: var(--dark3); border: 1px solid var(--border);
            border-radius: 14px; padding: 1.1rem 1.25rem;
            transition: all .2s;
        }
        .feature-item:hover { border-color: rgba(230,57,70,.3); }
        .feature-icon {
            width: 42px; height: 42px; border-radius: 10px;
            background: rgba(230,57,70,.12); display: flex; align-items: center; justify-content: center;
            font-size: .95rem; color: var(--red); flex-shrink: 0;
        }
        .feature-item h4 { font-size: .92rem; font-weight: 700; color: var(--white); margin-bottom: .25rem; }
        .feature-item p { font-size: .82rem; color: var(--muted); line-height: 1.6; }

        /* ─── Testimonials ─── */
        .testimonials-section { background: var(--dark); }
        .testimonials-header { text-align: center; margin-bottom: 3rem; }
        .testimonials-header .section-sub { margin: 0 auto; }
        .testimonials-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
        .testi-card {
            background: var(--dark3); border: 1px solid var(--border);
            border-radius: 20px; padding: 1.75rem;
            display: flex; flex-direction: column; gap: 1rem;
            transition: all .2s;
        }
        .testi-card:hover { border-color: rgba(230,57,70,.25); transform: translateY(-3px); }
        .testi-stars { color: #fbbf24; font-size: .85rem; display: flex; gap: 2px; }
        .testi-text { font-size: .88rem; color: var(--muted); line-height: 1.7; font-style: italic; flex: 1; }
        .testi-text::before { content: '"'; font-size: 1.4rem; color: var(--red); line-height: 0; vertical-align: -.35em; margin-right: .2rem; }
        .testi-author { display: flex; align-items: center; gap: .75rem; margin-top: auto; }
        .testi-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: linear-gradient(135deg, var(--red), #f59e0b);
            display: flex; align-items: center; justify-content: center;
            font-size: .9rem; font-weight: 800; color: #fff; flex-shrink: 0;
        }
        .testi-name { font-size: .85rem; font-weight: 700; color: var(--white); }
        .testi-role { font-size: .74rem; color: var(--muted); }

        /* ─── CTA ─── */
        .cta-section {
            padding: 6rem 5%;
            background:
                radial-gradient(ellipse 70% 80% at 50% 100%, rgba(230,57,70,.14), transparent 70%),
                var(--dark2);
        }
        .cta-inner {
            max-width: 760px; margin: 0 auto; text-align: center;
        }
        .cta-inner .section-eyebrow { justify-content: center; }
        .cta-inner .section-eyebrow::before { display: none; }
        .cta-inner .section-title { font-size: clamp(1.8rem, 3.5vw, 3rem); }
        .cta-inner .section-sub { margin: 0 auto 2.5rem; }
        .cta-btns { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }

        /* ─── Footer ─── */
        footer {
            background: var(--dark); border-top: 1px solid var(--border);
            padding: 4rem 5% 2rem;
        }
        .footer-inner { max-width: 1280px; margin: 0 auto; }
        .footer-top {
            display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem; padding-bottom: 3rem; border-bottom: 1px solid var(--border);
        }
        .footer-brand p { font-size: .85rem; color: var(--muted); line-height: 1.7; margin-top: .9rem; max-width: 280px; }
        .footer-socials { display: flex; gap: .6rem; margin-top: 1.25rem; }
        .social-btn {
            width: 36px; height: 36px; border-radius: 9px;
            background: var(--dark3); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            color: var(--muted); font-size: .82rem; transition: all .2s;
        }
        .social-btn:hover { background: var(--red); border-color: var(--red); color: #fff; }
        .footer-col h4 { font-size: .85rem; font-weight: 700; color: var(--white); margin-bottom: 1.1rem; }
        .footer-col ul { display: flex; flex-direction: column; gap: .55rem; }
        .footer-col ul a { font-size: .82rem; color: var(--muted); transition: color .2s; }
        .footer-col ul a:hover { color: var(--white); }
        .footer-bottom {
            display: flex; justify-content: space-between; align-items: center;
            padding-top: 2rem; flex-wrap: wrap; gap: 1rem;
        }
        .footer-bottom p { font-size: .78rem; color: var(--muted); }
        .footer-bottom .footer-legal { display: flex; gap: 1.5rem; }
        .footer-bottom .footer-legal a { font-size: .78rem; color: var(--muted); transition: color .2s; }
        .footer-bottom .footer-legal a:hover { color: var(--white); }

        /* ─── Reveal animation ─── */
        .reveal { opacity: 0; transform: translateY(30px); transition: opacity .65s ease, transform .65s ease; }
        .reveal.visible { opacity: 1; transform: none; }

        /* ─── Responsive ─── */
        @media (max-width: 1024px) {
            .hero-inner { flex-direction: column; text-align: center; padding-top: 4rem; }
            .hero-sub { max-width: 100%; }
            .hero-btns { justify-content: center; }
            .hero-trust { justify-content: center; }
            .hero-visual { max-width: 480px; width: 100%; }
            .features-layout { grid-template-columns: 1fr; }
            .features-visual { display: none; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-top { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .nav-hamburger { display: block; }
            .search-bar { grid-template-columns: 1fr; }
            .hiw-steps { grid-template-columns: 1fr; }
            .hiw-connector { display: none; }
            .testimonials-grid { grid-template-columns: 1fr; }
            .footer-top { grid-template-columns: 1fr; }
            section { padding: 4rem 5%; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .hero-chip { display: none; }
            .fleet-grid { grid-template-columns: 1fr; }
        }

        /* ─── Mobile menu ─── */
        .mobile-menu {
            display: none; position: fixed; top: 72px; left: 0; right: 0; bottom: 0;
            background: rgba(10,10,15,.97); z-index: 999;
            flex-direction: column; align-items: center; justify-content: center;
            gap: 2rem;
        }
        .mobile-menu.open { display: flex; }
        .mobile-menu a { font-size: 1.4rem; font-weight: 600; color: var(--white); }
        .mobile-menu a:hover { color: var(--red); }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════ NAVBAR ═══ -->
<nav class="navbar" id="navbar">
    <a href="#" class="nav-logo">
        <span class="nav-logo-icon"><i class="fas fa-car"></i></span>
        <span class="logo-de">De</span><span class="logo-cars">Cars</span>
    </a>

    <ul class="nav-links">
        <li><a href="#frota">Nossa Frota</a></li>
        <li><a href="#como-funciona">Como Funciona</a></li>
        <li><a href="#diferenciais">Diferenciais</a></li>
        <li><a href="#depoimentos">Depoimentos</a></li>
        <li><a href="#contato">Contato</a></li>
    </ul>

    <div class="nav-cta">
        @if(Route::has('login'))
            @auth
                <a href="{{ url('/home') }}" class="btn-outline">Painel</a>
            @else
                <a href="{{ route('login') }}" class="btn-outline">Entrar</a>
                <a href="#reservar" class="btn-red">Reservar agora</a>
            @endauth
        @endif
    </div>

    <button class="nav-hamburger" id="hamburger" aria-label="Menu">
        <i class="fas fa-bars"></i>
    </button>
</nav>

<!-- Mobile menu -->
<div class="mobile-menu" id="mobileMenu">
    <a href="#frota" onclick="closeMobile()">Nossa Frota</a>
    <a href="#como-funciona" onclick="closeMobile()">Como Funciona</a>
    <a href="#diferenciais" onclick="closeMobile()">Diferenciais</a>
    <a href="#depoimentos" onclick="closeMobile()">Depoimentos</a>
    @if(Route::has('login'))
        @auth
            <a href="{{ url('/home') }}">Painel</a>
        @else
            <a href="{{ route('login') }}">Entrar</a>
        @endauth
    @endif
</div>

<!-- ═══════════════════════════════════════════ HERO ════ -->
<section class="hero" id="inicio">
    <div class="hero-grid"></div>

    <div class="hero-inner">
        <!-- Text -->
        <div class="hero-text reveal">
            <div class="hero-badge">
                <i class="fas fa-star"></i> Locadora #1 em experiência
            </div>

            <h1 class="hero-title">
                Dirija o que você<br>
                <span class="gradient-text">merece de verdade</span>
            </h1>

            <p class="hero-sub">
                Frota premium, tecnologia de ponta e atendimento excepcional. Reserve em minutos e pegue as chaves quando quiser — sem burocracia, sem surpresas.
            </p>

            <div class="hero-btns">
                <a href="#reservar" class="btn-hero-primary">
                    <i class="fas fa-calendar-check"></i> Fazer reserva
                </a>
                <a href="#frota" class="btn-hero-secondary">
                    <i class="fas fa-th-large"></i> Ver frota
                </a>
            </div>

            <div class="hero-trust">
                <div class="trust-item"><i class="fas fa-check-circle"></i> Sem taxa de cancelamento</div>
                <div class="trust-item"><i class="fas fa-check-circle"></i> Seguro incluso</div>
                <div class="trust-item"><i class="fas fa-check-circle"></i> Km livre</div>
            </div>
        </div>

        <!-- Visual -->
        <div class="hero-visual">
            <!-- Floating chips -->
            <div class="hero-chip">
                <div class="chip-icon"><i class="fas fa-shield-alt"></i></div>
                <div>
                    <div class="chip-label">Proteção completa</div>
                    <div class="chip-val">Seguro incluso</div>
                </div>
            </div>

            <div class="hero-chip">
                <div class="chip-icon"><i class="fas fa-star"></i></div>
                <div>
                    <div class="chip-label">Avaliação média</div>
                    <div class="chip-val">4.9 / 5.0</div>
                </div>
            </div>

            <div class="hero-chip">
                <div class="chip-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                    <div class="chip-label">Estações</div>
                    <div class="chip-val">Retirada fácil</div>
                </div>
            </div>

            <!-- SVG Car -->
            <div class="hero-car-wrap">
                <div class="hero-car-glow"></div>
                <svg class="hero-car-svg" viewBox="0 0 900 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Car body -->
                    <path d="M120 280 Q110 240 130 220 L230 165 Q280 145 340 140 L480 135 Q560 135 620 150 L730 175 Q790 200 810 230 L820 280 Z"
                          fill="#1e1e2e" stroke="rgba(230,57,70,.5)" stroke-width="2"/>
                    <!-- Roof -->
                    <path d="M250 175 Q310 120 400 108 Q510 95 590 115 L700 160"
                          fill="#16162a" stroke="rgba(230,57,70,.4)" stroke-width="1.5"/>
                    <!-- Side body -->
                    <path d="M120 280 L820 280 Q840 280 840 265 L840 250 Q820 230 810 230 L820 280"
                          fill="#1a1a28" stroke="rgba(230,57,70,.3)" stroke-width="1"/>
                    <!-- Windows -->
                    <path d="M262 175 Q290 130 350 120 L430 118 L420 168 Z" fill="rgba(59,130,246,.15)" stroke="rgba(255,255,255,.1)" stroke-width="1"/>
                    <path d="M430 118 L500 116 Q560 118 585 130 L590 165 L420 168 Z" fill="rgba(59,130,246,.15)" stroke="rgba(255,255,255,.1)" stroke-width="1"/>
                    <path d="M590 165 L595 132 L680 155 L690 175 Z" fill="rgba(59,130,246,.15)" stroke="rgba(255,255,255,.1)" stroke-width="1"/>
                    <!-- Door lines -->
                    <line x1="425" y1="170" x2="418" y2="275" stroke="rgba(255,255,255,.06)" stroke-width="1.5"/>
                    <line x1="590" y1="168" x2="588" y2="275" stroke="rgba(255,255,255,.06)" stroke-width="1.5"/>
                    <!-- Headlight left -->
                    <path d="M135 240 Q125 245 122 258 L135 260 Z" fill="rgba(251,191,36,.7)" filter="url(#glow)"/>
                    <!-- Taillight -->
                    <rect x="805" y="240" width="18" height="22" rx="4" fill="rgba(230,57,70,.9)"/>
                    <!-- Wheels -->
                    <circle cx="245" cy="285" r="58" fill="#0d0d18" stroke="rgba(255,255,255,.1)" stroke-width="3"/>
                    <circle cx="245" cy="285" r="38" fill="#111120" stroke="rgba(230,57,70,.4)" stroke-width="2"/>
                    <circle cx="245" cy="285" r="14" fill="rgba(230,57,70,.5)"/>
                    <circle cx="685" cy="285" r="58" fill="#0d0d18" stroke="rgba(255,255,255,.1)" stroke-width="3"/>
                    <circle cx="685" cy="285" r="38" fill="#111120" stroke="rgba(230,57,70,.4)" stroke-width="2"/>
                    <circle cx="685" cy="285" r="14" fill="rgba(230,57,70,.5)"/>
                    <!-- Rim spokes -->
                    <g stroke="rgba(230,57,70,.35)" stroke-width="2">
                        <line x1="245" y1="247" x2="245" y2="285"/><line x1="245" y1="323" x2="245" y2="285"/>
                        <line x1="207" y1="285" x2="245" y2="285"/><line x1="283" y1="285" x2="245" y2="285"/>
                        <line x1="219" y1="259" x2="245" y2="285"/><line x1="271" y1="311" x2="245" y2="285"/>
                        <line x1="271" y1="259" x2="245" y2="285"/><line x1="219" y1="311" x2="245" y2="285"/>
                        <line x1="685" y1="247" x2="685" y2="285"/><line x1="685" y1="323" x2="685" y2="285"/>
                        <line x1="647" y1="285" x2="685" y2="285"/><line x1="723" y1="285" x2="685" y2="285"/>
                        <line x1="659" y1="259" x2="685" y2="285"/><line x1="711" y1="311" x2="685" y2="285"/>
                        <line x1="711" y1="259" x2="685" y2="285"/><line x1="659" y1="311" x2="685" y2="285"/>
                    </g>
                    <!-- DECLOCK decal -->
                    <text x="450" y="215" text-anchor="middle" font-family="'Syne',sans-serif" font-size="13" font-weight="800" fill="rgba(230,57,70,.6)" letter-spacing="4">DECLOCK</text>
                    <!-- Ground shadow -->
                    <ellipse cx="465" cy="342" rx="320" ry="18" fill="rgba(0,0,0,.5)"/>
                    <!-- Red accent stripe -->
                    <path d="M310 275 L605 275" stroke="var(--red)" stroke-width="2.5" stroke-linecap="round" opacity=".6"/>
                    <defs>
                        <filter id="glow"><feGaussianBlur stdDeviation="3" result="blur"/><feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
                    </defs>
                </svg>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════ SEARCH ═══ -->
<div class="search-section" id="reservar">
    <div class="search-bar reveal">
        <div class="search-field">
            <label><i class="fas fa-map-marker-alt" style="color:var(--red)"></i> Local de Retirada</label>
            <select>
                <option value="">Selecione a estação...</option>
                <option>Aeroporto Internacional</option>
                <option>Centro — Av. Paulista</option>
                <option>Shopping Morumbi</option>
                <option>Bairro Pinheiros</option>
            </select>
        </div>
        <div class="search-field">
            <label><i class="fas fa-calendar" style="color:var(--red)"></i> Data de Retirada</label>
            <input type="datetime-local">
        </div>
        <div class="search-field">
            <label><i class="fas fa-calendar-check" style="color:var(--red)"></i> Data de Devolução</label>
            <input type="datetime-local">
        </div>
        <div class="search-submit">
            <button type="button" onclick="window.location='{{ Route::has('login') ? route('login') : '#' }}'">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════ STATS ════ -->
<div class="stats-section">
    <div class="stats-grid reveal">
        <div class="stat-item">
            <div class="stat-num">2<span>k</span><span style="color:var(--red)">+</span></div>
            <div class="stat-label">Veículos na frota</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">48<span style="color:var(--red)">+</span></div>
            <div class="stat-label">Estações em todo o país</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">150<span>k</span><span style="color:var(--red)">+</span></div>
            <div class="stat-label">Clientes satisfeitos</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">4<span style="font-size:1.8rem">.9</span><span style="color:#fbbf24; font-size:1.8rem">★</span></div>
            <div class="stat-label">Nota média de avaliação</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════ HOW IT WORKS ═══ -->
<section class="hiw-section" id="como-funciona">
    <div class="section-inner">
        <div class="hiw-header reveal">
            <div class="section-eyebrow">Como funciona</div>
            <h2 class="section-title">Reserve em 3 passos simples</h2>
            <p class="section-sub">Sem fila, sem papelada. Do celular à chave do carro em menos de 5 minutos.</p>
        </div>

        <div class="hiw-steps">
            <div class="hiw-connector"></div>

            <div class="hiw-step reveal">
                <div class="hiw-num">01</div>
                <div class="hiw-icon"><i class="fas fa-search"></i></div>
                <h3>Escolha seu veículo</h3>
                <p>Filtre por categoria, período e localização. Compare modelos com especificações completas, fotos e preços transparentes — sem taxas escondidas.</p>
            </div>

            <div class="hiw-step reveal" style="transition-delay:.12s">
                <div class="hiw-num">02</div>
                <div class="hiw-icon"><i class="fas fa-credit-card"></i></div>
                <h3>Confirme e pague</h3>
                <p>Pagamento seguro via cartão, Pix ou link de cobrança. Receba sua confirmação instantaneamente com todos os detalhes da reserva no e-mail.</p>
            </div>

            <div class="hiw-step reveal" style="transition-delay:.24s">
                <div class="hiw-num">03</div>
                <div class="hiw-icon"><i class="fas fa-car-side"></i></div>
                <h3>Pegue as chaves</h3>
                <p>Apresente o QR Code na estação e saia dirigindo. Check-in digital em 2 minutos — sem espera, sem burocracia, direto para a estrada.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════ FLEET ═══ -->
<section class="fleet-section" id="frota">
    <div class="section-inner">
        <div class="fleet-header">
            <div class="reveal">
                <div class="section-eyebrow">Nossa frota</div>
                <h2 class="section-title">Veículos para cada momento</h2>
            </div>
            <div class="fleet-tabs reveal">
                <button class="fleet-tab active" data-cat="todos">Todos</button>
                <button class="fleet-tab" data-cat="economico">Econômico</button>
                <button class="fleet-tab" data-cat="suv">SUV</button>
                <button class="fleet-tab" data-cat="premium">Premium</button>
                <button class="fleet-tab" data-cat="eletrico">Elétrico</button>
            </div>
        </div>

        <div class="fleet-grid" id="fleetGrid">

            <!-- Card 1 -->
            <div class="fleet-card reveal" data-cat="economico">
                <div class="fleet-card-img">
                    <span class="fleet-category-badge">Econômico</span>
                    <svg viewBox="0 0 300 140" fill="none">
                        <path d="M40 100 Q38 82 48 74 L88 55 Q105 50 130 48 L175 46 Q205 46 225 54 L262 66 Q278 74 282 84 L285 100 Z" fill="#1e1e2e" stroke="rgba(230,57,70,.4)" stroke-width="1.5"/>
                        <path d="M90 57 Q108 38 145 34 Q190 30 220 42 L255 58" fill="#16162a" stroke="rgba(230,57,70,.3)" stroke-width="1"/>
                        <circle cx="88" cy="105" r="22" fill="#0d0d18" stroke="rgba(230,57,70,.4)" stroke-width="1.5"/>
                        <circle cx="88" cy="105" r="12" fill="#111120"/>
                        <circle cx="230" cy="105" r="22" fill="#0d0d18" stroke="rgba(230,57,70,.4)" stroke-width="1.5"/>
                        <circle cx="230" cy="105" r="12" fill="#111120"/>
                        <ellipse cx="162" cy="123" rx="108" ry="7" fill="rgba(0,0,0,.5)"/>
                    </svg>
                </div>
                <div class="fleet-card-body">
                    <div class="fleet-car-name">Volkswagen Polo</div>
                    <div class="fleet-car-sub">Hatch · Manual · 2024</div>
                    <div class="fleet-specs">
                        <span class="fleet-spec"><i class="fas fa-users"></i> 5 lugares</span>
                        <span class="fleet-spec"><i class="fas fa-snowflake"></i> Ar-cond.</span>
                        <span class="fleet-spec"><i class="fas fa-road"></i> Km livre</span>
                    </div>
                    <div class="fleet-price-row">
                        <div class="fleet-price">R$ 89<small>/dia</small></div>
                        <button class="fleet-btn">Reservar</button>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="fleet-card reveal" data-cat="suv" style="transition-delay:.08s">
                <div class="fleet-card-img" style="background:linear-gradient(135deg,#12121e,#1a1a2e)">
                    <span class="fleet-category-badge">SUV</span>
                    <svg viewBox="0 0 300 140" fill="none">
                        <path d="M30 100 Q28 78 40 68 L85 50 Q108 42 140 40 L185 38 Q220 38 248 50 L272 65 Q285 76 288 90 L290 100 Z" fill="#1e1e2e" stroke="rgba(59,130,246,.4)" stroke-width="1.5"/>
                        <path d="M85 52 Q110 26 155 22 Q200 18 240 36 L272 56" fill="#16162a" stroke="rgba(59,130,246,.3)" stroke-width="1"/>
                        <circle cx="82" cy="105" r="25" fill="#0d0d18" stroke="rgba(59,130,246,.4)" stroke-width="1.5"/>
                        <circle cx="82" cy="105" r="14" fill="#111120"/>
                        <circle cx="235" cy="105" r="25" fill="#0d0d18" stroke="rgba(59,130,246,.4)" stroke-width="1.5"/>
                        <circle cx="235" cy="105" r="14" fill="#111120"/>
                        <ellipse cx="160" cy="125" rx="115" ry="7" fill="rgba(0,0,0,.5)"/>
                        <rect x="268" y="80" width="8" height="14" rx="3" fill="rgba(230,57,70,.8)"/>
                    </svg>
                </div>
                <div class="fleet-card-body">
                    <div class="fleet-car-name">Toyota Corolla Cross</div>
                    <div class="fleet-car-sub">SUV · Automático · 2024</div>
                    <div class="fleet-specs">
                        <span class="fleet-spec"><i class="fas fa-users"></i> 5 lugares</span>
                        <span class="fleet-spec"><i class="fas fa-wifi"></i> Wi-Fi</span>
                        <span class="fleet-spec"><i class="fas fa-road"></i> Km livre</span>
                    </div>
                    <div class="fleet-price-row">
                        <div class="fleet-price">R$ 159<small>/dia</small></div>
                        <button class="fleet-btn">Reservar</button>
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="fleet-card reveal" data-cat="premium" style="transition-delay:.16s">
                <div class="fleet-card-img" style="background:linear-gradient(135deg,#12121e,#1e1220)">
                    <span class="fleet-category-badge">Premium</span>
                    <svg viewBox="0 0 300 140" fill="none">
                        <path d="M42 100 Q40 80 52 70 L92 56 Q118 48 152 46 L195 44 Q228 44 252 56 L272 68 Q283 78 285 92 L288 100 Z" fill="#1e1e2e" stroke="rgba(230,57,70,.6)" stroke-width="2"/>
                        <path d="M95 58 Q118 30 162 26 Q206 22 248 44 L272 62" fill="#16162a" stroke="rgba(230,57,70,.4)" stroke-width="1.5"/>
                        <circle cx="90" cy="106" r="26" fill="#0d0d18" stroke="rgba(230,57,70,.5)" stroke-width="2"/>
                        <circle cx="90" cy="106" r="15" fill="#111120" stroke="rgba(230,57,70,.3)" stroke-width="1"/>
                        <circle cx="235" cy="106" r="26" fill="#0d0d18" stroke="rgba(230,57,70,.5)" stroke-width="2"/>
                        <circle cx="235" cy="106" r="15" fill="#111120" stroke="rgba(230,57,70,.3)" stroke-width="1"/>
                        <ellipse cx="162" cy="126" rx="112" ry="8" fill="rgba(0,0,0,.6)"/>
                        <rect x="274" y="78" width="10" height="16" rx="3" fill="rgba(230,57,70,.9)"/>
                        <path d="M162 92 L155 86 L162 80 L169 86 Z" fill="rgba(230,57,70,.5)"/>
                    </svg>
                </div>
                <div class="fleet-card-body">
                    <div class="fleet-car-name">BMW Série 3</div>
                    <div class="fleet-car-sub">Sedan Premium · Automático · 2024</div>
                    <div class="fleet-specs">
                        <span class="fleet-spec"><i class="fas fa-users"></i> 5 lugares</span>
                        <span class="fleet-spec"><i class="fas fa-music"></i> Harman Kardon</span>
                        <span class="fleet-spec"><i class="fas fa-road"></i> Km livre</span>
                    </div>
                    <div class="fleet-price-row">
                        <div class="fleet-price">R$ 349<small>/dia</small></div>
                        <button class="fleet-btn">Reservar</button>
                    </div>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="fleet-card reveal" data-cat="eletrico" style="transition-delay:.24s">
                <div class="fleet-card-img" style="background:linear-gradient(135deg,#0f1a0f,#12201c)">
                    <span class="fleet-category-badge">Elétrico</span>
                    <svg viewBox="0 0 300 140" fill="none">
                        <path d="M38 100 Q36 80 48 70 L90 54 Q115 46 150 44 L192 42 Q226 42 250 54 L272 66 Q284 78 287 92 L290 100 Z" fill="#1e1e2e" stroke="rgba(34,197,94,.5)" stroke-width="1.5"/>
                        <circle cx="88" cy="104" r="24" fill="#0d0d18" stroke="rgba(34,197,94,.4)" stroke-width="1.5"/>
                        <circle cx="88" cy="104" r="14" fill="#111120"/>
                        <circle cx="232" cy="104" r="24" fill="#0d0d18" stroke="rgba(34,197,94,.4)" stroke-width="1.5"/>
                        <circle cx="232" cy="104" r="14" fill="#111120"/>
                        <ellipse cx="160" cy="123" rx="108" ry="7" fill="rgba(0,0,0,.5)"/>
                        <!-- Lightning bolt -->
                        <path d="M158 54 L148 75 L160 72 L152 92 L170 68 L157 72 Z" fill="rgba(34,197,94,.7)"/>
                    </svg>
                </div>
                <div class="fleet-card-body">
                    <div class="fleet-car-name">BYD Atto 3</div>
                    <div class="fleet-car-sub">SUV Elétrico · Automático · 2024</div>
                    <div class="fleet-specs">
                        <span class="fleet-spec"><i class="fas fa-bolt"></i> 480km autonomia</span>
                        <span class="fleet-spec"><i class="fas fa-leaf"></i> Zero emissão</span>
                        <span class="fleet-spec"><i class="fas fa-road"></i> Km livre</span>
                    </div>
                    <div class="fleet-price-row">
                        <div class="fleet-price">R$ 219<small>/dia</small></div>
                        <button class="fleet-btn">Reservar</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════ FEATURES ═══ -->
<section class="features-section" id="diferenciais">
    <div class="section-inner">
        <div class="features-layout">
            <div class="features-visual reveal">
                <div class="features-img-wrap">
                    <i class="fas fa-shield-alt features-big-icon"></i>
                    <svg viewBox="0 0 400 400" style="max-width:320px; opacity:.85">
                        <!-- Shield main -->
                        <path d="M200 30 L340 80 L340 200 Q340 310 200 370 Q60 310 60 200 L60 80 Z"
                              fill="none" stroke="rgba(230,57,70,.25)" stroke-width="2"/>
                        <path d="M200 55 L320 98 L320 200 Q320 298 200 348 Q80 298 80 200 L80 98 Z"
                              fill="rgba(230,57,70,.04)" stroke="rgba(230,57,70,.15)" stroke-width="1.5"/>
                        <!-- Car silhouette inside shield -->
                        <path d="M120 220 Q118 205 125 198 L155 185 Q168 180 185 179 L215 178 Q232 178 242 183 L265 192 Q272 198 274 208 L275 220 Z"
                              fill="rgba(230,57,70,.3)" stroke="rgba(230,57,70,.5)" stroke-width="1"/>
                        <circle cx="150" cy="224" r="13" fill="rgba(230,57,70,.4)"/>
                        <circle cx="248" cy="224" r="13" fill="rgba(230,57,70,.4)"/>
                        <!-- Check mark -->
                        <path d="M160 200 L190 230 L260 160" stroke="rgba(230,57,70,.8)" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <!-- Stars rating -->
                        <text x="200" y="290" text-anchor="middle" fill="#fbbf24" font-size="22">★★★★★</text>
                        <text x="200" y="310" text-anchor="middle" fill="rgba(255,255,255,.4)" font-size="11" font-family="Inter">4.9 de 5.0 — 12.842 avaliações</text>
                        <!-- Circles decoration -->
                        <circle cx="200" cy="200" r="170" fill="none" stroke="rgba(230,57,70,.07)" stroke-width="1" stroke-dasharray="6,8"/>
                        <circle cx="200" cy="200" r="140" fill="none" stroke="rgba(230,57,70,.05)" stroke-width="1"/>
                    </svg>
                </div>
                <!-- Floating badges -->
                <div class="features-badge-float fbf-top">
                    <div class="ficon"><i class="fas fa-check"></i></div>
                    <div>
                        <div class="flabel">Proteção total</div>
                        <div class="fval">Seguro incluso</div>
                    </div>
                </div>
                <div class="features-badge-float fbf-bottom">
                    <div class="ficon"><i class="fas fa-headset"></i></div>
                    <div>
                        <div class="flabel">Suporte</div>
                        <div class="fval">24h / 7 dias</div>
                    </div>
                </div>
            </div>

            <div class="reveal" style="transition-delay:.15s">
                <div class="section-eyebrow">Por que a DECLOCK?</div>
                <h2 class="section-title">Experiência além do volante</h2>
                <p class="section-sub">
                    Combinamos tecnologia, frota premium e atendimento humanizado para que cada jornada seja memorável.
                </p>

                <div class="features-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                        <div>
                            <h4>Check-in em 2 minutos</h4>
                            <p>Tudo digital — sem fila no balcão. Apresente o QR Code e pegue as chaves imediatamente.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <div>
                            <h4>Proteção completa inclusa</h4>
                            <p>Seguro contra colisão, furto e terceiros já incluído em todos os planos. Sem surpresas.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-road"></i></div>
                        <div>
                            <h4>Quilometragem livre</h4>
                            <p>Dirija sem preocupação. Todos os veículos têm km ilimitado — sem cobrança extra no retorno.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-headset"></i></div>
                        <div>
                            <h4>Suporte 24h em tempo real</h4>
                            <p>Equipe disponível via app, chat ou telefone em qualquer horário, para qualquer situação na estrada.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════ TESTIMONIALS ═══ -->
<section class="testimonials-section" id="depoimentos">
    <div class="section-inner">
        <div class="testimonials-header reveal">
            <div class="section-eyebrow">Depoimentos</div>
            <h2 class="section-title">O que nossos clientes dizem</h2>
            <p class="section-sub">Mais de 150.000 clientes satisfeitos. Veja por que escolhem a DECLOCK em cada viagem.</p>
        </div>

        <div class="testimonials-grid">
            <div class="testi-card reveal">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">Experiência incrível do início ao fim. O check-in foi realmente em menos de 2 minutos, o carro estava impecável e o suporte no caminho foi excepcional. Já marquei a próxima locação!</p>
                <div class="testi-author">
                    <div class="testi-avatar">RC</div>
                    <div>
                        <div class="testi-name">Ricardo Carvalho</div>
                        <div class="testi-role">Empresário · São Paulo, SP</div>
                    </div>
                </div>
            </div>

            <div class="testi-card reveal" style="transition-delay:.1s">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">Aluguel de viagem a trabalho toda semana e a DECLOCK se tornou minha primeira opção. O app é intuitivo, a frota está sempre atualizada e nunca tive problemas com cobrança.</p>
                <div class="testi-author">
                    <div class="testi-avatar" style="background:linear-gradient(135deg,#3b82f6,#8b5cf6)">AM</div>
                    <div>
                        <div class="testi-name">Ana Mendes</div>
                        <div class="testi-role">Executiva de Vendas · Rio de Janeiro, RJ</div>
                    </div>
                </div>
            </div>

            <div class="testi-card reveal" style="transition-delay:.2s">
                <div class="testi-stars">★★★★★</div>
                <p class="testi-text">Reservei um SUV para a viagem de férias em família. O carro era zero bala, o seguro estava incluso e não paguei nada a mais. Voltei em 10 minutos na devolução. Perfeito!</p>
                <div class="testi-author">
                    <div class="testi-avatar" style="background:linear-gradient(135deg,#10b981,#059669)">FS</div>
                    <div>
                        <div class="testi-name">Felipe Santos</div>
                        <div class="testi-role">Médico · Curitiba, PR</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════ CTA ════ -->
<section class="cta-section">
    <div class="cta-inner reveal">
        <div class="section-eyebrow">Pronto para partir?</div>
        <h2 class="section-title">Reserve agora e ganhe<br><span style="color:var(--red)">10% na primeira locação</span></h2>
        <p class="section-sub">Use o código <strong style="color:var(--white)">DECLOCK10</strong> ao finalizar a reserva. Válido para qualquer veículo da frota na primeira locação.</p>
        <div class="cta-btns">
            <a href="{{ Route::has('login') ? route('login') : '#' }}" class="btn-hero-primary">
                <i class="fas fa-calendar-check"></i> Fazer minha reserva
            </a>
            <a href="#frota" class="btn-hero-secondary">
                <i class="fas fa-car"></i> Ver a frota
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════ FOOTER ════ -->
<footer id="contato">
    <div class="footer-inner">
        <div class="footer-top">
            <div class="footer-brand">
                <div class="nav-logo" style="margin-bottom:.75rem">
                    <span class="nav-logo-icon"><i class="fas fa-car"></i></span>
                    <span class="logo-de">De</span><span class="logo-cars">Cars</span>
                </div>
                <p>Locadora de veículos premium comprometida com a melhor experiência de mobilidade do Brasil. Frota moderna, tecnologia e transparência em cada reserva.</p>
                <div class="footer-socials">
                    <a href="#" class="social-btn" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-btn" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-btn" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-btn" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Empresa</h4>
                <ul>
                    <li><a href="#">Sobre a DECLOCK</a></li>
                    <li><a href="#">Nossa frota</a></li>
                    <li><a href="#">Estações</a></li>
                    <li><a href="#">Trabalhe conosco</a></li>
                    <li><a href="#">Blog</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Serviços</h4>
                <ul>
                    <li><a href="#">Aluguel diário</a></li>
                    <li><a href="#">Assinatura mensal</a></li>
                    <li><a href="#">Frota corporativa</a></li>
                    <li><a href="#">Transfer aeroporto</a></li>
                    <li><a href="#">Eventos especiais</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Contato</h4>
                <ul>
                    <li><a href="tel:08001234567"><i class="fas fa-phone mr-1"></i> 0800 123 4567</a></li>
                    <li><a href="mailto:contato@DECLOCK.com.br"><i class="fas fa-envelope mr-1"></i> contato@DECLOCK.com.br</a></li>
                    <li><a href="#"><i class="fas fa-comment mr-1"></i> Chat ao vivo 24h</a></li>
                    <li><a href="#"><i class="fab fa-whatsapp mr-1"></i> WhatsApp</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© {{ date('Y') }} DECLOCK Locadora de Veículos. Todos os direitos reservados.</p>
            <div class="footer-legal">
                <a href="#">Privacidade</a>
                <a href="#">Termos de Uso</a>
                <a href="#">Cookies</a>
            </div>
        </div>
    </div>
</footer>

<script>
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 40);
    });

    // Mobile menu
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    hamburger.addEventListener('click', () => {
        mobileMenu.classList.toggle('open');
        hamburger.innerHTML = mobileMenu.classList.contains('open')
            ? '<i class="fas fa-times"></i>'
            : '<i class="fas fa-bars"></i>';
    });
    function closeMobile() {
        mobileMenu.classList.remove('open');
        hamburger.innerHTML = '<i class="fas fa-bars"></i>';
    }

    // Reveal on scroll
    const reveals = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.12 });
    reveals.forEach(el => observer.observe(el));

    // Fleet filter tabs
    const tabs = document.querySelectorAll('.fleet-tab');
    const cards = document.querySelectorAll('.fleet-card');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const cat = tab.dataset.cat;
            cards.forEach(card => {
                const show = cat === 'todos' || card.dataset.cat === cat;
                card.style.display = show ? '' : 'none';
            });
        });
    });

    // Search date defaults
    const now = new Date();
    const pad = n => String(n).padStart(2,'0');
    const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:00`;
    const ret = new Date(now); ret.setDate(ret.getDate() + 1);
    const dev = new Date(now); dev.setDate(dev.getDate() + 4);
    const inputs = document.querySelectorAll('.search-field input[type="datetime-local"]');
    if (inputs[0]) inputs[0].value = fmt(ret);
    if (inputs[1]) inputs[1].value = fmt(dev);

    // Stats counter animation
    const statNums = document.querySelectorAll('.stat-num');
    const statObserver = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const el = e.target;
            statObserver.unobserve(el);
            el.style.opacity = '0';
            setTimeout(() => {
                el.style.transition = 'opacity .5s';
                el.style.opacity = '1';
            }, 100);
        });
    }, { threshold: .5 });
    statNums.forEach(el => statObserver.observe(el));
</script>
</body>
</html>
