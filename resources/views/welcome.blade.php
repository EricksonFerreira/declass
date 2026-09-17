<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="DECLOCK e o sistema de gestao de tempo para equipes: rastreador de horas, timesheet, calendario de alocacoes, projetos, produtividade e relatorios em um so lugar.">
    <meta name="keywords" content="controle de horas, timesheet, rastreador de tempo, gestao de equipe, produtividade, projetos, DECLOCK">
    <meta name="author" content="Sonyd Tech">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DECLOCK">
    <meta property="og:title" content="DECLOCK | Gestao de tempo e produtividade para equipes">
    <meta property="og:description" content="Rastreador de horas, timesheet, calendario e relatorios de produtividade para a sua equipe em uma unica plataforma.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta name="theme-color" content="#0a1628">
    <title>DECLOCK | Gestao de tempo e produtividade para equipes</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicons/favicon.ico') }}">
    <link rel="manifest" href="{{ route('site.manifest') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "DECLOCK",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Sistema de gestao de tempo para equipes: rastreador de horas, timesheet, calendario, projetos e relatorios de produtividade.",
        "publisher": {
            "@type": "Organization",
            "name": "Sonyd Tech"
        }
    }
    </script>

    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
        font-family: 'Inter', sans-serif;
        background: #fff;
        color: #334155;
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
    }
    a { text-decoration: none; color: inherit; }
    img { max-width: 100%; display: block; }
    ul { list-style: none; }
    button { font-family: inherit; cursor: pointer; border: none; background: none; }

    /* ── Variables ── */
    :root {
        --bg: #0a1628;
        --bg-hover: #162035;
        --cyan: #0ea5e9;
        --white: #ffffff;
        --surface: #f8fafc;
        --surface2: #f1f5f9;
        --border: #e2e8f0;
        --border2: rgba(10,22,40,.15);
        --text: #334155;
        --muted: #64748b;
        --muted2: #94a3b8;
        --radius: 10px;
        --shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
        --shadow: 0 4px 16px rgba(0,0,0,.1), 0 2px 6px rgba(0,0,0,.06);
        --shadow-lg: 0 20px 60px rgba(0,0,0,.12), 0 8px 24px rgba(0,0,0,.08);
    }

    .container { max-width: 1200px; margin: 0 auto; padding: 0 5%; }

    /* ══ ANIMAÇÕES ══ */
    .reveal {
        opacity: 0; transform: translateY(36px);
        transition: opacity .75s cubic-bezier(.16,1,.3,1), transform .75s cubic-bezier(.16,1,.3,1);
    }
    .reveal.visible { opacity: 1; transform: none; }
    .reveal-left { opacity: 0; transform: translateX(-40px); transition: opacity .75s cubic-bezier(.16,1,.3,1), transform .75s cubic-bezier(.16,1,.3,1); }
    .reveal-left.visible { opacity: 1; transform: none; }
    .reveal-right { opacity: 0; transform: translateX(40px); transition: opacity .75s cubic-bezier(.16,1,.3,1), transform .75s cubic-bezier(.16,1,.3,1); }
    .reveal-right.visible { opacity: 1; transform: none; }
    .delay-1 { transition-delay: .1s; }
    .delay-2 { transition-delay: .2s; }
    .delay-3 { transition-delay: .32s; }
    .delay-4 { transition-delay: .44s; }

    @keyframes blobFloat {
        0%,100% { transform: translate(0,0) scale(1); }
        33%      { transform: translate(30px,-20px) scale(1.05); }
        66%      { transform: translate(-15px,25px) scale(.97); }
    }
    @keyframes floatY {
        0%,100% { transform: translateY(0); }
        50%      { transform: translateY(-12px); }
    }
    @keyframes spinSlow { to { transform: rotate(360deg); } }
    @keyframes pulse { 0%,100% { opacity: .6; } 50% { opacity: 1; } }
    @keyframes shimmer {
        0%   { background-position: -600px 0; }
        100% { background-position: 600px 0; }
    }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }
    .cursor { display: inline-block; width: 3px; height: 1em; background: var(--bg); vertical-align: text-bottom; margin-left: 2px; animation: blink .85s step-end infinite; }

    /* ══ NAVBAR ══ */
    .navbar {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        height: 70px; display: flex; align-items: center;
        background: rgba(255,255,255,0);
        transition: background .4s, box-shadow .4s;
    }
    .navbar.scrolled {
        background: rgba(255,255,255,.95);
        backdrop-filter: blur(16px);
        box-shadow: 0 1px 0 var(--border), var(--shadow-sm);
    }
    .nav-inner {
        display: flex; align-items: center; justify-content: space-between;
        width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 5%;
    }
    .nav-logo {
        display: flex; align-items: center; gap: 9px;
        font-family: 'Raleway', sans-serif; font-size: 1.4rem; font-weight: 900;
        color: var(--bg); letter-spacing: -.02em;
    }
    .nav-logo-icon {
        width: 36px; height: 36px; border-radius: 8px;
        display: flex; align-items: center;
        justify-content: center; font-size: .85rem; color: #fff; flex-shrink: 0;
        transition: transform .3s;
    }
    .nav-logo-icon img { width: 100%; height: 100%; object-fit: contain; }
    .nav-logo:hover .nav-logo-icon { transform: rotate(-8deg) scale(1.08); }
    .nav-links { display: flex; align-items: center; gap: 2rem; }
    .nav-links a {
        font-size: .875rem; font-weight: 500; color: var(--muted);
        transition: color .2s; position: relative; padding-bottom: 2px;
    }
    .nav-links a::after {
        content: ''; position: absolute; bottom: 0; left: 0; width: 0; height: 2px;
        background: var(--bg); border-radius: 1px; transition: width .25s;
    }
    .nav-links a:hover { color: var(--bg); }
    .nav-links a:hover::after { width: 100%; }
    .nav-actions { display: flex; align-items: center; gap: .75rem; }
    .btn-ghost-nav {
        padding: .45rem 1rem; border: 1.5px solid var(--border2);
        border-radius: var(--radius); font-size: .83rem; font-weight: 600;
        color: var(--bg); transition: all .2s;
    }
    .btn-ghost-nav:hover { border-color: var(--bg); background: rgba(10,22,40,.04); }
    .btn-nav {
        padding: .45rem 1.2rem; background: var(--bg); color: #fff;
        border-radius: var(--radius); font-size: .83rem; font-weight: 700;
        transition: background .2s, transform .15s, box-shadow .2s;
    }
    .btn-nav:hover { background: var(--bg-hover); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(10,22,40,.2); }
    .nav-toggle { display: none; flex-direction: column; gap: 5px; padding: 6px; }
    .nav-toggle span { width: 24px; height: 2px; background: var(--bg); border-radius: 2px; transition: all .3s; }

    /* ══ HERO ══ */
    .hero {
        min-height: 100vh;
        padding-top: 70px;
        background: #fff;
        display: flex; align-items: center;
        position: relative; overflow: hidden;
    }
    .hero-blob {
        position: absolute; border-radius: 50%; filter: blur(70px); pointer-events: none;
    }
    .hero-blob-1 {
        width: 520px; height: 520px;
        background: rgba(14,165,233,.08);
        top: -120px; right: -80px;
        animation: blobFloat 14s ease-in-out infinite;
    }
    .hero-blob-2 {
        width: 360px; height: 360px;
        background: rgba(10,22,40,.05);
        bottom: -80px; left: -60px;
        animation: blobFloat 18s ease-in-out infinite reverse;
    }
    .hero-dots {
        position: absolute; inset: 0; pointer-events: none;
        background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
        background-size: 32px 32px;
        opacity: .35;
        -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 50%, black 30%, transparent 100%);
        mask-image: radial-gradient(ellipse 80% 60% at 50% 50%, black 30%, transparent 100%);
    }
    .hero-inner {
        position: relative; z-index: 1;
        display: grid; grid-template-columns: 1fr 1fr;
        align-items: center; gap: 4rem; padding: 5rem 0;
        width: 100%;
    }
    .hero-eyebrow {
        display: inline-flex; align-items: center; gap: .5rem;
        font-size: .72rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
        color: var(--bg); margin-bottom: 1.4rem;
        border: 1.5px solid rgba(10,22,40,.15);
        padding: .35rem .9rem; border-radius: 999px;
        background: rgba(10,22,40,.04);
        animation: pulse 3s ease-in-out infinite;
    }
    .hero-eyebrow i { font-size: .65rem; }
    .hero-title {
        font-family: 'Raleway', sans-serif;
        font-size: clamp(2.2rem, 4vw, 3.4rem);
        font-weight: 900; line-height: 1.1; letter-spacing: -.025em;
        color: var(--bg); margin-bottom: 1.4rem;
    }
    .hero-title .line-accent { color: var(--cyan); }
    .hero-text {
        font-size: 1.05rem; line-height: 1.85; color: var(--muted);
        max-width: 460px; margin-bottom: 2.2rem;
    }
    .hero-ctas { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 2.5rem; }
    .btn-hero {
        padding: .9rem 2rem; background: var(--bg); color: #fff;
        border-radius: var(--radius); font-size: .95rem; font-weight: 700;
        transition: background .2s, transform .2s, box-shadow .2s; display: inline-block;
    }
    .btn-hero:hover { background: var(--bg-hover); transform: translateY(-2px); box-shadow: 0 10px 30px rgba(10,22,40,.25); }
    .btn-hero-ghost {
        padding: .9rem 2rem; border: 1.5px solid var(--border);
        color: var(--bg); border-radius: var(--radius); font-size: .95rem; font-weight: 600;
        transition: border-color .2s, background .2s; display: inline-flex; align-items: center; gap: .6rem;
    }
    .btn-hero-ghost:hover { border-color: var(--bg); background: rgba(10,22,40,.03); }
    .hero-trust { display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; }
    .hero-trust-item { display: flex; align-items: center; gap: .45rem; font-size: .82rem; font-weight: 500; color: var(--muted); }
    .hero-trust-item i { color: var(--bg); font-size: .75rem; }

    /* Imagem hero com animação */
    .hero-visual {
        position: relative;
        animation: floatY 5s ease-in-out infinite;
    }
    .hero-carousel {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow-lg), 0 0 0 1px var(--border);
        position: relative;
        aspect-ratio: 16 / 10;
        background: var(--surface);
        transition: transform .3s cubic-bezier(.16,1,.3,1), box-shadow .3s ease;
    }
    .hero-carousel-wrap:hover .hero-carousel { transform: scale(1.02); }
    .hero-carousel::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, var(--bg), var(--cyan), var(--bg));
        z-index: 3; background-size: 200% 100%;
        animation: shimmer 3s linear infinite;
    }
    .hero-carousel-slide {
        position: absolute; inset: 0; width: 100%; height: 100%;
        opacity: 0; transform: scale(1.02);
        transition: opacity .75s ease, transform .75s ease;
        pointer-events: none;
    }
    .hero-carousel-slide.is-active { opacity: 1; transform: scale(1); pointer-events: auto; }
    .hero-carousel-slide::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(180deg, transparent 55%, rgba(10,22,40,.14) 100%);
        pointer-events: none;
    }
    .hero-carousel-caption {
        position: absolute; left: 16px; right: 16px; bottom: 16px; z-index: 2;
        padding: .85rem 1rem; border-radius: 14px;
        background: rgba(10,22,40,.84); color: #fff;
        backdrop-filter: blur(12px);
        box-shadow: 0 10px 28px rgba(0,0,0,.18);
    }
    .hero-carousel-caption strong { display: block; margin-bottom: .15rem; font-size: .92rem; font-weight: 800; letter-spacing: -.01em; }
    .hero-carousel-caption span {
        display: block; font-size: .68rem; font-weight: 700;
        letter-spacing: .1em; text-transform: uppercase;
        color: var(--cyan); margin-bottom: .22rem;
    }
    .hero-carousel-caption small { display: block; font-size: .76rem; line-height: 1.45; color: rgba(255,255,255,.74); }
    .hero-badge-float {
        position: absolute; display: flex; align-items: center; gap: .6rem;
        background: #fff; border: 1px solid var(--border);
        box-shadow: var(--shadow); border-radius: 12px;
        padding: .65rem 1rem; font-size: .8rem; font-weight: 700; color: var(--bg);
        white-space: nowrap; z-index: 4;
    }
    .hero-badge-float i { font-size: .9rem; }
    .hero-badge-float.pos-tl { top: -18px; left: -20px; animation: floatY 4s ease-in-out infinite; }
    .hero-badge-float.pos-br { bottom: -18px; right: -20px; animation: floatY 4.5s ease-in-out infinite .5s; }
    .hero-badge-float .dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; animation: pulse 1.5s ease-in-out infinite; }

    /* ══ MINI PRODUCT SHOTS (CSS mockups) ══ */
    .hero-shot {
        position: absolute; inset: 0;
        background: var(--surface);
        padding: 1rem 1.1rem 5.4rem;
        display: flex; flex-direction: column; gap: .6rem;
    }
    .feature-shot {
        width: 82%; max-width: 380px;
        background: #fff; border: 1.5px solid var(--border);
        border-radius: 16px; box-shadow: var(--shadow-lg);
        padding: 1rem; display: flex; flex-direction: column; gap: .6rem;
    }
    .shot-head { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
    .shot-title { display: inline-flex; align-items: center; gap: .45rem; font-size: .68rem; font-weight: 800; color: var(--bg); }
    .shot-title i { color: var(--cyan); font-size: .62rem; }
    .shot-badge {
        font-size: .55rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
        padding: .18rem .5rem; border-radius: 999px;
        background: rgba(14,165,233,.1); color: var(--cyan);
    }
    .shot-badge.green { background: #dcfce7; color: #16a34a; }
    .shot-badge.gray { background: var(--surface2); color: var(--muted); }
    .shot-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
    .shot-kpi { background: #fff; border-radius: 10px; padding: .5rem .6rem; box-shadow: var(--shadow-sm); border: 1px solid var(--border); }
    .shot-kpi small { display: block; font-size: .52rem; color: var(--muted); text-transform: uppercase; font-weight: 700; letter-spacing: .05em; }
    .shot-kpi b { font-size: .88rem; color: var(--bg); font-weight: 800; }
    .shot-card { background: #fff; border-radius: 10px; padding: .6rem .7rem; box-shadow: var(--shadow-sm); border: 1px solid var(--border); }
    .shot-card-head { display: flex; justify-content: space-between; font-size: .56rem; font-weight: 700; color: var(--bg); margin-bottom: .5rem; }
    .shot-card-head span { color: var(--muted); font-weight: 600; }
    .shot-bars { display: flex; align-items: flex-end; gap: 6px; height: 64px; }
    .shot-bars i { flex: 1; border-radius: 4px 4px 0 0; background: linear-gradient(180deg, #38bdf8, var(--bg)); }
    .shot-track {
        display: flex; align-items: center; gap: .55rem;
        background: #fff; border-radius: 10px; padding: .5rem .6rem;
        box-shadow: var(--shadow-sm); border: 1px solid var(--border);
    }
    .shot-play {
        width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center; font-size: .55rem;
        background: var(--surface2); color: var(--muted);
    }
    .shot-play.rec { background: #dcfce7; color: #16a34a; }
    .shot-track-info { flex: 1; min-width: 0; }
    .shot-track-info b { display: block; font-size: .58rem; color: var(--bg); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .shot-track-info small { font-size: .52rem; color: var(--muted); }
    .shot-time { font-size: .72rem; font-weight: 800; color: var(--bg); font-variant-numeric: tabular-nums; }
    .shot-avatar {
        width: 24px; height: 24px; border-radius: 8px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: .5rem; color: #fff;
    }
    /* Timesheet grid */
    .ts-grid { display: flex; flex-direction: column; gap: .35rem; }
    .ts-row { display: grid; grid-template-columns: 64px repeat(5, 1fr); gap: .3rem; align-items: center; }
    .ts-row.head .ts-cell { background: transparent; height: auto; }
    .ts-row.head .ts-cell span { font-size: .48rem; font-weight: 800; color: var(--muted); letter-spacing: .04em; }
    .ts-name { display: flex; align-items: center; gap: .35rem; font-size: .52rem; font-weight: 700; color: var(--bg); }
    .ts-cell { background: #fff; border: 1px solid var(--border); border-radius: 5px; height: 20px; position: relative; overflow: hidden; }
    .ts-cell i { position: absolute; left: 0; top: 0; bottom: 0; background: linear-gradient(90deg, rgba(56,189,248,.55), rgba(14,165,233,.7)); border-radius: 5px 0 0 5px; }
    /* Calendar grid */
    .cal-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: .4rem; flex: 1; }
    .cal-col { display: flex; flex-direction: column; gap: .35rem; }
    .cal-day { font-size: .5rem; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; text-align: center; }
    .cal-chip { border-radius: 6px; padding: .3rem .35rem; font-size: .48rem; font-weight: 700; color: #fff; line-height: 1.3; }
    .cal-chip small { display: block; font-weight: 600; opacity: .8; font-size: .44rem; }

    /* ══ SECTION COMMONS ══ */
    .section-tag {
        display: inline-flex; align-items: center; gap: .45rem;
        font-size: .7rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
        color: var(--bg); border: 1.5px solid rgba(10,22,40,.15);
        padding: .3rem .85rem; border-radius: 999px; margin-bottom: 1rem;
    }
    .section-title {
        font-family: 'Raleway', sans-serif;
        font-size: clamp(1.8rem, 3vw, 2.6rem);
        font-weight: 900; color: var(--bg); line-height: 1.15; letter-spacing: -.025em;
        margin-bottom: .9rem;
    }
    .section-sub { font-size: .97rem; color: var(--muted); line-height: 1.75; max-width: 520px; }
    .section-header { margin-bottom: 3rem; }

    /* ══ SALES DIFFERENTIAL ══ */
    .sales-diff {
        padding: 5rem 0;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
    }
    .sales-diff-inner { display: grid; grid-template-columns: 1fr 1.05fr; gap: 2.5rem; align-items: center; }
    .sales-diff-tag {
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .33rem .8rem; margin-bottom: 1rem; border-radius: 999px;
        background: rgba(14,165,233,.08); color: var(--bg);
        font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
    }
    .sales-diff-tag i { color: var(--cyan); }
    .sales-diff-title {
        font-family: 'Raleway', sans-serif;
        font-size: clamp(1.8rem, 3.4vw, 2.8rem);
        font-weight: 900; line-height: 1.1; letter-spacing: -.03em;
        color: var(--bg); margin-bottom: 1rem;
    }
    .sales-diff-copy { font-size: 1rem; line-height: 1.8; color: var(--muted); max-width: 560px; margin-bottom: 1.5rem; }
    .sales-diff-points { display: grid; gap: .9rem; margin-bottom: 1.8rem; }
    .sales-diff-point { display: flex; align-items: flex-start; gap: .7rem; font-size: .92rem; line-height: 1.6; color: var(--text); }
    .sales-diff-point i { color: #22c55e; margin-top: .2rem; font-size: .88rem; flex-shrink: 0; }
    .sales-diff-actions { display: flex; align-items: center; gap: .9rem; flex-wrap: wrap; }
    .sales-diff-link {
        display: inline-flex; align-items: center; justify-content: center;
        padding: .9rem 1.2rem; border-radius: var(--radius);
        background: var(--bg); color: #fff; font-size: .9rem; font-weight: 700;
        transition: transform .2s, background .2s, box-shadow .2s;
    }
    .sales-diff-link:hover { background: var(--bg-hover); transform: translateY(-1px); box-shadow: 0 10px 26px rgba(10,22,40,.18); }
    .sales-diff-note { font-size: .84rem; color: var(--muted); }
    .sales-diff-visual {
        position: relative; padding: 1rem; border-radius: 22px;
        background: #fff; border: 1.5px solid var(--border); box-shadow: var(--shadow-lg);
    }
    .sales-diff-panel { background: var(--surface); border-radius: 16px; padding: 1rem; display: flex; flex-direction: column; gap: .55rem; }
    .sales-diff-badge {
        position: absolute; top: -14px; left: 18px; z-index: 2;
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .52rem .8rem; border-radius: 999px;
        background: #0f172a; color: #fff;
        font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
        box-shadow: 0 10px 24px rgba(15,23,42,.2);
    }
    .sales-diff-badge i { color: var(--cyan); }

    /* ══ PROBLEMS ══ */
    .problems { padding: 7rem 0; background: #fff; }
    .problems-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 3.5rem; }
    .problem-card {
        padding: 2rem 1.8rem; background: var(--surface);
        border: 1.5px solid var(--border); border-radius: 14px;
        position: relative; overflow: hidden;
        transition: transform .35s cubic-bezier(.16,1,.3,1), border-color .3s, box-shadow .35s;
        cursor: default;
    }
    .problem-card::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
        background: var(--bg); transform: scaleX(0); transform-origin: left;
        transition: transform .4s cubic-bezier(.16,1,.3,1);
    }
    .problem-card:hover { transform: translateY(-6px); border-color: var(--bg); box-shadow: var(--shadow); }
    .problem-card:hover::after { transform: scaleX(1); }
    .problem-icon {
        width: 46px; height: 46px; border-radius: 12px;
        background: var(--bg); display: flex; align-items: center;
        justify-content: center; font-size: 1.05rem; color: #fff; margin-bottom: 1.3rem;
        transition: transform .3s;
    }
    .problem-card:hover .problem-icon { transform: rotate(-8deg) scale(1.1); }
    .problem-card h3 { font-family: 'Raleway', sans-serif; font-weight: 800; font-size: 1.05rem; color: var(--bg); margin-bottom: .6rem; }
    .problem-card p { font-size: .875rem; color: var(--muted); line-height: 1.7; }

    /* ══ FEATURES SWIPER ══ */
    .features { padding: 7rem 0; background: var(--surface); }
    .features-header { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: end; margin-bottom: 3.5rem; }
    .feature-slide {
        display: grid; grid-template-columns: 1fr 1fr;
        align-items: center; background: #fff;
        border: 1.5px solid var(--border); border-radius: 20px;
        overflow: hidden; min-height: 440px;
        box-shadow: var(--shadow);
        transition: box-shadow .3s;
    }
    .feature-slide:hover { box-shadow: var(--shadow-lg); }
    .feature-slide-content { padding: 3rem; }
    .feature-slide-tag {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .7rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase;
        color: var(--bg); background: rgba(10,22,40,.06);
        border: 1.5px solid rgba(10,22,40,.12); padding: .28rem .75rem;
        border-radius: 999px; margin-bottom: 1.3rem;
    }
    .feature-slide h3 {
        font-family: 'Raleway', sans-serif; font-weight: 900;
        font-size: 1.6rem; color: var(--bg); line-height: 1.2; margin-bottom: 1rem;
    }
    .feature-slide p { font-size: .9rem; color: var(--muted); line-height: 1.75; margin-bottom: 1.6rem; }
    .feature-items { display: flex; flex-direction: column; gap: .7rem; }
    .feature-item { display: flex; align-items: center; gap: .65rem; font-size: .85rem; font-weight: 500; color: var(--text); }
    .feature-item i { color: var(--bg); font-size: .78rem; flex-shrink: 0; }
    .feature-slide-image {
        height: 100%; min-height: 440px;
        background: var(--surface);
        display: flex; align-items: center; justify-content: center;
        border-left: 1.5px solid var(--border); overflow: hidden;
        padding: 2rem;
    }
    .swiper-nav { display: flex; align-items: center; gap: 1rem; margin-top: 2rem; }
    .swiper-btn {
        width: 48px; height: 48px; border-radius: 50%;
        border: 1.5px solid var(--border); background: #fff;
        display: flex; align-items: center; justify-content: center;
        color: var(--bg); font-size: .85rem; cursor: pointer;
        transition: all .25s; box-shadow: var(--shadow-sm);
    }
    .swiper-btn:hover:not(:disabled) { border-color: var(--bg); background: var(--bg); color: #fff; transform: scale(1.08); }
    .swiper-btn:disabled { opacity: .3; pointer-events: none; }
    .swiper-progress { flex: 1; height: 2px; background: var(--border); border-radius: 1px; overflow: hidden; }
    .swiper-progress-bar { height: 100%; background: var(--bg); border-radius: 1px; transition: width .4s ease; }
    .swiper-count { font-size: .8rem; color: var(--muted); font-weight: 600; }

    /* ══ MODULES ══ */
    .modules { padding: 7rem 0; background: var(--bg); position: relative; overflow: hidden; }
    .modules::before {
        content: ''; position: absolute; inset: 0; pointer-events: none;
        background-image: radial-gradient(circle, rgba(255,255,255,.05) 1px, transparent 1px);
        background-size: 36px 36px;
    }
    .modules .section-tag { color: var(--cyan); border-color: rgba(14,165,233,.3); }
    .modules .section-title { color: #fff; }
    .modules .section-sub { color: rgba(255,255,255,.6); }
    .modules-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-top: 3rem; }
    .module-card {
        padding: 1.75rem 1.5rem;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.08); border-radius: 14px;
        position: relative; overflow: hidden;
        transition: background .3s, border-color .3s, transform .35s cubic-bezier(.16,1,.3,1);
        cursor: default;
    }
    .module-card::before {
        content: ''; position: absolute; inset: 0; border-radius: 14px;
        background: linear-gradient(135deg, rgba(255,255,255,.06), transparent);
        opacity: 0; transition: opacity .35s;
    }
    .module-card:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18); transform: translateY(-5px); }
    .module-card:hover::before { opacity: 1; }
    .mod-icon {
        width: 44px; height: 44px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: .95rem; margin-bottom: 1.1rem;
        transition: transform .3s;
    }
    .module-card:hover .mod-icon { transform: rotate(-8deg) scale(1.12); }
    .module-card h4 { font-family: 'Raleway', sans-serif; font-weight: 800; font-size: .95rem; color: #fff; margin-bottom: .45rem; }
    .module-card p { font-size: .8rem; color: rgba(255,255,255,.5); line-height: 1.6; }

    /* ══ FAQ ══ */
    .faq { padding: 7rem 0; background: var(--surface); }
    .faq-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; margin-top: 3rem; }
    .faq-card {
        background: #fff; border: 1.5px solid var(--border); border-radius: 16px;
        padding: 1.8rem;
        transition: transform .3s, border-color .3s, box-shadow .3s;
    }
    .faq-card:hover { transform: translateY(-4px); border-color: var(--bg); box-shadow: var(--shadow); }
    .faq-q { display: flex; gap: .75rem; align-items: flex-start; margin-bottom: .9rem; }
    .faq-q i {
        width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
        background: rgba(10,22,40,.06); color: var(--bg);
        display: flex; align-items: center; justify-content: center; font-size: .8rem;
    }
    .faq-card h3 { font-family: 'Raleway', sans-serif; font-size: 1rem; line-height: 1.35; font-weight: 800; color: var(--bg); padding-top: .35rem; }
    .faq-card p { font-size: .9rem; line-height: 1.75; color: var(--muted); }

    /* ══ CTA FINAL ══ */
    .cta-section {
        padding: 8rem 0; background: var(--bg);
        text-align: center; position: relative; overflow: hidden;
    }
    .cta-section::before {
        content: ''; position: absolute; top: -30%; left: 50%; transform: translateX(-50%);
        width: 800px; height: 800px; border-radius: 50%;
        background: radial-gradient(circle, rgba(14,165,233,.12) 0%, transparent 70%);
        pointer-events: none; animation: blobFloat 12s ease-in-out infinite;
    }
    .cta-lines { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
    .cta-line {
        position: absolute; top: 50%; left: 50%;
        border: 1px solid rgba(255,255,255,.04);
        border-radius: 50%; transform: translate(-50%, -50%);
        animation: spinSlow linear infinite;
    }
    .cta-line:nth-child(1) { width: 400px; height: 400px; animation-duration: 40s; }
    .cta-line:nth-child(2) { width: 600px; height: 600px; animation-duration: 55s; animation-direction: reverse; }
    .cta-line:nth-child(3) { width: 800px; height: 800px; animation-duration: 70s; }
    .cta-inner { position: relative; z-index: 1; }
    .cta-section .section-tag { color: var(--cyan); border-color: rgba(14,165,233,.3); }
    .cta-section .section-title { color: #fff; font-size: clamp(2rem, 3.5vw, 3rem); margin: 0 auto 1rem; }
    .cta-section .section-sub { color: rgba(255,255,255,.6); margin: 0 auto 2.5rem; text-align: center; }
    .cta-actions { display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap; }
    .btn-cta-white {
        padding: .9rem 2rem; background: #fff; color: var(--bg);
        border-radius: var(--radius); font-size: .95rem; font-weight: 800;
        transition: all .25s; display: inline-block;
    }
    .btn-cta-white:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,.25); }
    .btn-cta-ghost {
        display: inline-flex; align-items: center; gap: .6rem;
        padding: .9rem 2rem; border: 1.5px solid rgba(255,255,255,.35); color: #fff;
        border-radius: var(--radius); font-weight: 700; font-size: .95rem;
        transition: all .25s;
    }
    .btn-cta-ghost:hover { background: rgba(255,255,255,.1); transform: translateY(-2px); }

    /* ══ FOOTER ══ */
    .footer { background: var(--surface); border-top: 1px solid var(--border); padding: 4.5rem 0 2.5rem; }
    .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem; padding-bottom: 3rem; border-bottom: 1px solid var(--border); }
    .footer-logo {
        display: flex; align-items: center; gap: .7rem;
        font-family: 'Raleway', sans-serif; font-weight: 900; font-size: 1.25rem;
        color: var(--bg); margin-bottom: 1rem; letter-spacing: -.02em;
    }
    .footer-logo-icon { width: 30px; height: 30px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: .75rem; color: #fff; }
    .footer-logo-icon img { width: 100%; height: 100%; object-fit: contain; }
    .footer-tagline { font-size: .85rem; color: var(--muted); line-height: 1.7; max-width: 240px; }
    .footer-col h5 {
        font-family: 'Raleway', sans-serif; font-weight: 800; font-size: .78rem;
        text-transform: uppercase; letter-spacing: .09em; color: var(--bg); margin-bottom: 1.2rem;
    }
    .footer-col ul { display: flex; flex-direction: column; gap: .65rem; }
    .footer-col ul li a { font-size: .85rem; color: var(--muted); transition: color .2s, padding-left .2s; display: inline-block; }
    .footer-col ul li a:hover { color: var(--bg); padding-left: 4px; }
    .footer-bottom { display: flex; align-items: center; justify-content: space-between; padding-top: 2rem; flex-wrap: wrap; gap: 1rem; }
    .footer-bottom p { font-size: .8rem; color: var(--muted2); }
    .footer-bottom a { font-weight: 600; color: var(--muted); }
    .footer-bottom a:hover { color: var(--bg); }

    /* Mouse glow */
    .mouse-glow {
        position: fixed; pointer-events: none; z-index: 9999;
        width: 400px; height: 400px; border-radius: 50%;
        background: radial-gradient(circle, rgba(10,22,40,.06) 0%, transparent 65%);
        transform: translate(-50%, -50%);
        transition: opacity .5s;
        opacity: 0;
    }

    /* ══ RESPONSIVE ══ */
    @media (max-width: 1024px) {
        .hero-inner { grid-template-columns: 1fr; gap: 3rem; }
        .sales-diff-inner { grid-template-columns: 1fr; }
        .features-header { grid-template-columns: 1fr; gap: 1.5rem; }
        .feature-slide { grid-template-columns: 1fr; }
        .feature-slide-image { min-height: 300px; border-left: none; border-top: 1.5px solid var(--border); }
        .modules-grid { grid-template-columns: repeat(3, 1fr); }
        .footer-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 768px) {
        .nav-links, .nav-actions { display: none; }
        .nav-links.open, .nav-actions.open {
            display: flex; flex-direction: column; position: fixed;
            top: 70px; left: 0; right: 0; background: rgba(255,255,255,.98);
            backdrop-filter: blur(16px); padding: 1.5rem;
            gap: 1rem; border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
        .nav-toggle { display: flex; }
        .problems-grid { grid-template-columns: 1fr; }
        .modules-grid { grid-template-columns: repeat(2, 1fr); }
        .faq-grid { grid-template-columns: 1fr; }
        .footer-grid { grid-template-columns: 1fr; }
        .hero-badge-float { display: none; }
        .hero-carousel-caption { left: 12px; right: 12px; bottom: 12px; padding: .72rem .85rem; }
    }
    @media (max-width: 480px) {
        .hero-ctas { flex-direction: column; width: 100%; }
        .btn-hero, .btn-hero-ghost { width: 100%; justify-content: center; text-align: center; }
        .modules-grid { grid-template-columns: 1fr 1fr; }
    }
    </style>
</head>
<body>

<!-- Mouse glow -->
<div class="mouse-glow" id="mouseGlow"></div>

<!-- ══ NAVBAR ══ -->
<nav class="navbar" id="navbar">
    <div class="nav-inner">
        <a href="{{ route('site.apresentacao') }}" class="nav-logo">
            <div class="nav-logo-icon">
                <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="Logo DECLOCK">
            </div>
            DECLOCK
        </a>
        <ul class="nav-links" id="navLinks">
            <li><a href="#funcionalidades">Funcionalidades</a></li>
            <li><a href="#modulos">Módulos</a></li>
            <li><a href="{{ route('site.sobre') }}">Sobre</a></li>
            <li><a href="#perguntas">Perguntas</a></li>
        </ul>
        <div class="nav-actions" id="navActions">
            @auth
                <a href="{{ route('home') }}" class="btn-nav">Acessar painel</a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost-nav">Entrar</a>
                <a href="{{ route('cadastro.empresa.create') }}" class="btn-nav">Começar agora</a>
            @endauth
        </div>
        <button class="nav-toggle" id="navToggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- ══ HERO ══ -->
<section class="hero" id="hero">
    <div class="hero-blob hero-blob-1"></div>
    <div class="hero-blob hero-blob-2"></div>
    <div class="hero-dots"></div>
    <div class="container">
        <div class="hero-inner">
            <div>
                <div class="hero-eyebrow reveal">
                    <i class="fas fa-clock"></i>
                    Gestão de tempo para equipes
                </div>
                <h1 class="hero-title reveal delay-1">
                    Gestão completa<br>do tempo da equipe<br>em <span class="line-accent" id="typewriterTarget"></span><span class="cursor" id="typewriterCursor"></span>
                </h1>
                <p class="hero-text reveal delay-2">
                    O DECLOCK reúne rastreador de horas, timesheet, calendário de alocações e relatórios de produtividade em uma plataforma simples — para equipes que precisam de clareza, não de planilhas.
                </p>
                <div class="hero-ctas reveal delay-2">
                    @auth
                        <a href="{{ route('home') }}" class="btn-hero">Acessar painel</a>
                    @else
                        <a href="{{ route('cadastro.empresa.create') }}" class="btn-hero">Começar agora</a>
                    @endauth
                    <a href="#funcionalidades" class="btn-hero-ghost">
                        <i class="fas fa-play-circle"></i>
                        Ver como funciona
                    </a>
                </div>
                <div class="hero-trust reveal delay-3">
                    <div class="hero-trust-item"><i class="fas fa-check-circle"></i> Sem instalação — roda no navegador</div>
                    <div class="hero-trust-item"><i class="fas fa-check-circle"></i> Multiempresa com permissões</div>
                    <div class="hero-trust-item"><i class="fas fa-check-circle"></i> Dados na nuvem</div>
                </div>
            </div>
            <div class="hero-visual reveal-right delay-2">
                <div class="hero-badge-float pos-tl">
                    <div class="dot"></div> 8 rastreadores ativos
                </div>
                <div class="hero-carousel-wrap">
                    <div class="hero-carousel" id="heroCarousel">

                        <figure class="hero-carousel-slide is-active">
                            <div class="hero-shot">
                                <div class="shot-head">
                                    <span class="shot-title"><i class="fas fa-gauge-high"></i> Visão geral da equipe</span>
                                    <span class="shot-badge">Hoje</span>
                                </div>
                                <div class="shot-kpis">
                                    <div class="shot-kpi"><small>Horas hoje</small><b>126h</b></div>
                                    <div class="shot-kpi"><small>Rastreadores</small><b>8 ativos</b></div>
                                    <div class="shot-kpi"><small>Projetos</small><b>12</b></div>
                                </div>
                                <div class="shot-card">
                                    <div class="shot-card-head">Horas por dia no período <span>últimos 7 dias</span></div>
                                    <div class="shot-bars">
                                        <i style="height:45%"></i><i style="height:70%"></i><i style="height:55%"></i><i style="height:85%"></i><i style="height:62%"></i><i style="height:95%"></i><i style="height:40%"></i>
                                    </div>
                                </div>
                                <div class="shot-track">
                                    <span class="shot-play rec"><i class="fas fa-stop"></i></span>
                                    <div class="shot-track-info">
                                        <b>Desenvolvimento — Portal do cliente</b>
                                        <small>Ana Souza · Projeto DECLOCK Web</small>
                                    </div>
                                    <span class="shot-badge green">Rodando</span>
                                    <span class="shot-time">02:47</span>
                                </div>
                            </div>
                            <figcaption class="hero-carousel-caption">
                                <span>Dashboard</span>
                                <strong>Visão executiva da operação</strong>
                                <small>Horas, rastreadores ativos e projetos em um só lugar.</small>
                            </figcaption>
                        </figure>

                        <figure class="hero-carousel-slide">
                            <div class="hero-shot">
                                <div class="shot-head">
                                    <span class="shot-title"><i class="fas fa-stopwatch"></i> Rastreador de tempo</span>
                                    <span class="shot-badge green">Ao vivo</span>
                                </div>
                                <div class="shot-track">
                                    <span class="shot-play rec"><i class="fas fa-stop"></i></span>
                                    <div class="shot-track-info">
                                        <b>Design system</b>
                                        <small>Carla Mendes · DECLOCK Web</small>
                                    </div>
                                    <span class="shot-time">01:12</span>
                                </div>
                                <div class="shot-track">
                                    <span class="shot-play"><i class="fas fa-play"></i></span>
                                    <div class="shot-track-info">
                                        <b>API e integrações</b>
                                        <small>Diego Lima · Backend</small>
                                    </div>
                                    <span class="shot-time">00:00</span>
                                </div>
                                <div class="shot-track">
                                    <span class="shot-play"><i class="fas fa-play"></i></span>
                                    <div class="shot-track-info">
                                        <b>App mobile</b>
                                        <small>Bruno Rocha · Frontend</small>
                                    </div>
                                    <span class="shot-time">00:00</span>
                                </div>
                                <div class="shot-track">
                                    <span class="shot-play"><i class="fas fa-play"></i></span>
                                    <div class="shot-track-info">
                                        <b>Portal do cliente</b>
                                        <small>Ana Souza · DECLOCK Web</small>
                                    </div>
                                    <span class="shot-time">00:00</span>
                                </div>
                            </div>
                            <figcaption class="hero-carousel-caption">
                                <span>Rastreador</span>
                                <strong>Um clique para registrar</strong>
                                <small>A equipe inicia o cronômetro e o gestor acompanha em tempo real.</small>
                            </figcaption>
                        </figure>

                        <figure class="hero-carousel-slide">
                            <div class="hero-shot">
                                <div class="shot-head">
                                    <span class="shot-title"><i class="fas fa-table-list"></i> Timesheet da semana</span>
                                    <span class="shot-badge">38h</span>
                                </div>
                                <div class="ts-grid">
                                    <div class="ts-row head">
                                        <span></span>
                                        <div class="ts-cell"><span>SEG</span></div>
                                        <div class="ts-cell"><span>TER</span></div>
                                        <div class="ts-cell"><span>QUA</span></div>
                                        <div class="ts-cell"><span>QUI</span></div>
                                        <div class="ts-cell"><span>SEX</span></div>
                                    </div>
                                    <div class="ts-row">
                                        <span class="ts-name"><span class="shot-avatar" style="background:#0ea5e9">AS</span> Ana</span>
                                        <div class="ts-cell"><i style="width:90%"></i></div>
                                        <div class="ts-cell"><i style="width:75%"></i></div>
                                        <div class="ts-cell"><i style="width:100%"></i></div>
                                        <div class="ts-cell"><i style="width:60%"></i></div>
                                        <div class="ts-cell"><i style="width:80%"></i></div>
                                    </div>
                                    <div class="ts-row">
                                        <span class="ts-name"><span class="shot-avatar" style="background:#34d399">BR</span> Bruno</span>
                                        <div class="ts-cell"><i style="width:70%"></i></div>
                                        <div class="ts-cell"><i style="width:85%"></i></div>
                                        <div class="ts-cell"><i style="width:50%"></i></div>
                                        <div class="ts-cell"><i style="width:90%"></i></div>
                                        <div class="ts-cell"><i style="width:65%"></i></div>
                                    </div>
                                    <div class="ts-row">
                                        <span class="ts-name"><span class="shot-avatar" style="background:#fbbf24">CM</span> Carla</span>
                                        <div class="ts-cell"><i style="width:55%"></i></div>
                                        <div class="ts-cell"><i style="width:95%"></i></div>
                                        <div class="ts-cell"><i style="width:70%"></i></div>
                                        <div class="ts-cell"><i style="width:80%"></i></div>
                                        <div class="ts-cell"><i style="width:45%"></i></div>
                                    </div>
                                </div>
                            </div>
                            <figcaption class="hero-carousel-caption">
                                <span>Timesheet</span>
                                <strong>A semana inteira em uma tela</strong>
                                <small>Lançamentos, totais por dia e conferência do período.</small>
                            </figcaption>
                        </figure>

                        <figure class="hero-carousel-slide">
                            <div class="hero-shot">
                                <div class="shot-head">
                                    <span class="shot-title"><i class="fas fa-calendar-days"></i> Calendário de alocações</span>
                                    <span class="shot-badge gray">Semana</span>
                                </div>
                                <div class="cal-grid">
                                    <div class="cal-col">
                                        <div class="cal-day">Seg</div>
                                        <div class="cal-chip" style="background:#0ea5e9;flex:2">Portal<small>Ana</small></div>
                                        <div class="cal-chip" style="background:#34d399;flex:1">App<small>Bruno</small></div>
                                    </div>
                                    <div class="cal-col">
                                        <div class="cal-day">Ter</div>
                                        <div class="cal-chip" style="background:#fbbf24;flex:1">Design<small>Carla</small></div>
                                        <div class="cal-chip" style="background:#c084fc;flex:2">API<small>Diego</small></div>
                                    </div>
                                    <div class="cal-col">
                                        <div class="cal-day">Qua</div>
                                        <div class="cal-chip" style="background:#0ea5e9;flex:2">Portal<small>Ana</small></div>
                                    </div>
                                    <div class="cal-col">
                                        <div class="cal-day">Qui</div>
                                        <div class="cal-chip" style="background:#f472b6;flex:1">Folga<small>Carla</small></div>
                                        <div class="cal-chip" style="background:#34d399;flex:2">App<small>Bruno</small></div>
                                    </div>
                                    <div class="cal-col">
                                        <div class="cal-day">Sex</div>
                                        <div class="cal-chip" style="background:#c084fc;flex:1">API<small>Diego</small></div>
                                        <div class="cal-chip" style="background:#0ea5e9;flex:1">Portal<small>Ana</small></div>
                                    </div>
                                </div>
                            </div>
                            <figcaption class="hero-carousel-caption">
                                <span>Calendário</span>
                                <strong>Alocações sem conflito</strong>
                                <small>Quem está em qual projeto, em qualquer dia da semana.</small>
                            </figcaption>
                        </figure>

                    </div>
                </div>
                <div class="hero-badge-float pos-br">
                    <i class="fas fa-chart-line" style="color:#22c55e"></i> +37% de produtividade
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ SALES DIFFERENTIAL ══ -->
<section class="sales-diff">
    <div class="container">
        <div class="sales-diff-inner">
            <div class="reveal-left">
                <div class="sales-diff-tag"><i class="fas fa-eye"></i> Diferencial competitivo</div>
                <h2 class="sales-diff-title">Além de registrar, sua equipe entende onde o tempo vai.</h2>
                <p class="sales-diff-copy">
                    O DECLOCK não entrega apenas um cronômetro. Ele transforma as horas lançadas em visão de operação: capacidade, alocações e produtividade em tempo real para o gestor decidir com clareza.
                </p>
                <div class="sales-diff-points">
                    <div class="sales-diff-point"><i class="fas fa-check-circle"></i> Dashboards com horas por dia, ranking de colaboradores e rastreadores ativos.</div>
                    <div class="sales-diff-point"><i class="fas fa-check-circle"></i> Relatórios por projeto, cliente e pessoa — com filtros e exportação em um clique.</div>
                    <div class="sales-diff-point"><i class="fas fa-check-circle"></i> Alocações e folgas no mesmo calendário, sem conflito de agenda.</div>
                </div>
                <div class="sales-diff-actions">
                    <a href="#funcionalidades" class="sales-diff-link">Ver funcionalidades</a>
                    <span class="sales-diff-note">Esse é o diferencial que faz o DECLOCK se destacar entre as ferramentas do mercado.</span>
                </div>
            </div>
            <div class="sales-diff-visual reveal-right delay-1">
                <div class="sales-diff-badge"><i class="fas fa-chart-line"></i> Visão executiva</div>
                <div class="sales-diff-panel">
                    <div class="shot-head" style="margin-bottom:.2rem">
                        <span class="shot-title"><i class="fas fa-chart-column"></i> Produtividade da equipe</span>
                        <span class="shot-badge">Esta semana</span>
                    </div>
                    <div class="shot-track">
                        <span class="shot-avatar" style="background:#0ea5e9">AS</span>
                        <div class="shot-track-info"><b>Ana Souza</b><small>Portal do cliente</small></div>
                        <span class="shot-time">7,4h</span>
                    </div>
                    <div class="shot-track">
                        <span class="shot-avatar" style="background:#34d399">BR</span>
                        <div class="shot-track-info"><b>Bruno Rocha</b><small>App mobile</small></div>
                        <span class="shot-time">6,4h</span>
                    </div>
                    <div class="shot-track">
                        <span class="shot-avatar" style="background:#fbbf24">CM</span>
                        <div class="shot-track-info"><b>Carla Mendes</b><small>Design system</small></div>
                        <span class="shot-time">5,1h</span>
                    </div>
                    <div class="shot-track">
                        <span class="shot-avatar" style="background:#c084fc">DL</span>
                        <div class="shot-track-info"><b>Diego Lima</b><small>API e integrações</small></div>
                        <span class="shot-time">3,8h</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ PROBLEMS ══ -->
<section class="problems" id="problemas">
    <div class="container">
        <div class="section-header" style="text-align:center;margin-bottom:0">
            <div class="section-tag reveal" style="justify-content:center">O Problema</div>
            <h2 class="section-title reveal delay-1" style="margin:0 auto .9rem">Sua equipe ainda controla horas<br>em planilha ou no papel?</h2>
            <p class="section-sub reveal delay-2" style="margin:0 auto">Times perdem horas de produtividade toda semana por falta de controle integrado. O DECLOCK resolve na raiz.</p>
        </div>
        <div class="problems-grid">
            <div class="problem-card reveal delay-1">
                <div class="problem-icon"><i class="fas fa-brain"></i></div>
                <h3>Horas lançadas de memória</h3>
                <p>Timesheets preenchidos no fim do mês geram dados imprecisos, esquecimentos e retrabalho na conferência.</p>
            </div>
            <div class="problem-card reveal delay-2">
                <div class="problem-icon"><i class="fas fa-users-slash"></i></div>
                <h3>Capacidade no escuro</h3>
                <p>Sem visão de alocações, ninguém sabe quem está sobrecarregado, quem está ocioso ou quem entra de folga.</p>
            </div>
            <div class="problem-card reveal delay-3">
                <div class="problem-icon"><i class="fas fa-file-excel"></i></div>
                <h3>Relatórios manuais</h3>
                <p>Fechar o período vira dias consolidando planilhas soltas, prints de conversa e anotações dispersas.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══ FEATURES SWIPER ══ -->
<section class="features" id="funcionalidades">
    <div class="container">
        <div class="features-header">
            <div class="reveal-left">
                <div class="section-tag">Funcionalidades</div>
                <h2 class="section-title">Uma plataforma.<br>Operação completa.</h2>
            </div>
            <p class="section-sub reveal-right delay-1">
                Cada módulo foi desenhado a partir de demandas reais de equipes que trabalham por projetos, não de suposições de mercado.
            </p>
        </div>

        <div class="swiper swiper-feature reveal" id="swiperFeature">
            <div class="swiper-wrapper">

                <!-- SLIDE 1 — Rastreador -->
                <div class="swiper-slide">
                    <div class="feature-slide">
                        <div class="feature-slide-content">
                            <div class="feature-slide-tag"><i class="fas fa-stopwatch"></i> Rastreador</div>
                            <h3>Um clique para começar a contar</h3>
                            <p>Inicie e pare o cronômetro vinculando o trabalho a projetos e clientes. Veja quem está trabalhando em quê, em tempo real.</p>
                            <div class="feature-items">
                                <div class="feature-item"><i class="fas fa-check"></i> Start e stop por projeto e cliente</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Rastreadores ativos no dashboard</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Lançamento manual quando esquecer</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Sincroniza direto com o timesheet</div>
                            </div>
                        </div>
                        <div class="feature-slide-image">
                            <div class="feature-shot">
                                <div class="shot-head">
                                    <span class="shot-title"><i class="fas fa-stopwatch"></i> Rastreador</span>
                                    <span class="shot-badge green">Rodando</span>
                                </div>
                                <div class="shot-track">
                                    <span class="shot-play rec"><i class="fas fa-stop"></i></span>
                                    <div class="shot-track-info"><b>Portal do cliente</b><small>Projeto DECLOCK Web</small></div>
                                    <span class="shot-time">02:47:13</span>
                                </div>
                                <div class="shot-track">
                                    <span class="shot-play"><i class="fas fa-play"></i></span>
                                    <div class="shot-track-info"><b>App mobile</b><small>Projeto DECLOCK App</small></div>
                                    <span class="shot-time">00:00:00</span>
                                </div>
                                <div class="shot-track">
                                    <span class="shot-play"><i class="fas fa-play"></i></span>
                                    <div class="shot-track-info"><b>API e integrações</b><small>Backend</small></div>
                                    <span class="shot-time">00:00:00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 2 — Timesheet -->
                <div class="swiper-slide">
                    <div class="feature-slide">
                        <div class="feature-slide-content">
                            <div class="feature-slide-tag"><i class="fas fa-table-list"></i> Timesheet</div>
                            <h3>A semana inteira em uma tela</h3>
                            <p>Visão semanal das horas lançadas por cada colaborador — ideal para conferência, fechamento de período e prestação de contas.</p>
                            <div class="feature-items">
                                <div class="feature-item"><i class="fas fa-check"></i> Lançamento rápido por dia e projeto</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Totais por dia, semana e pessoa</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Conferência do período em minutos</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Exportação para análise externa</div>
                            </div>
                        </div>
                        <div class="feature-slide-image">
                            <div class="feature-shot">
                                <div class="shot-head">
                                    <span class="shot-title"><i class="fas fa-table-list"></i> Timesheet</span>
                                    <span class="shot-badge">38h na semana</span>
                                </div>
                                <div class="ts-grid">
                                    <div class="ts-row head">
                                        <span></span>
                                        <div class="ts-cell"><span>SEG</span></div>
                                        <div class="ts-cell"><span>TER</span></div>
                                        <div class="ts-cell"><span>QUA</span></div>
                                        <div class="ts-cell"><span>QUI</span></div>
                                        <div class="ts-cell"><span>SEX</span></div>
                                    </div>
                                    <div class="ts-row">
                                        <span class="ts-name"><span class="shot-avatar" style="background:#0ea5e9">AS</span> Ana</span>
                                        <div class="ts-cell"><i style="width:90%"></i></div>
                                        <div class="ts-cell"><i style="width:75%"></i></div>
                                        <div class="ts-cell"><i style="width:100%"></i></div>
                                        <div class="ts-cell"><i style="width:60%"></i></div>
                                        <div class="ts-cell"><i style="width:80%"></i></div>
                                    </div>
                                    <div class="ts-row">
                                        <span class="ts-name"><span class="shot-avatar" style="background:#34d399">BR</span> Bruno</span>
                                        <div class="ts-cell"><i style="width:70%"></i></div>
                                        <div class="ts-cell"><i style="width:85%"></i></div>
                                        <div class="ts-cell"><i style="width:50%"></i></div>
                                        <div class="ts-cell"><i style="width:90%"></i></div>
                                        <div class="ts-cell"><i style="width:65%"></i></div>
                                    </div>
                                    <div class="ts-row">
                                        <span class="ts-name"><span class="shot-avatar" style="background:#fbbf24">CM</span> Carla</span>
                                        <div class="ts-cell"><i style="width:55%"></i></div>
                                        <div class="ts-cell"><i style="width:95%"></i></div>
                                        <div class="ts-cell"><i style="width:70%"></i></div>
                                        <div class="ts-cell"><i style="width:80%"></i></div>
                                        <div class="ts-cell"><i style="width:45%"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 3 — Calendário -->
                <div class="swiper-slide">
                    <div class="feature-slide">
                        <div class="feature-slide-content">
                            <div class="feature-slide-tag"><i class="fas fa-calendar-days"></i> Calendário</div>
                            <h3>Alocações visuais, sem conflito</h3>
                            <p>Planeje a agenda da equipe arrastando e soltando alocações, com visões por dia, semana e mês — otimizado também no celular.</p>
                            <div class="feature-items">
                                <div class="feature-item"><i class="fas fa-check"></i> Arraste e solte para alocar pessoas</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Visões por dia, semana e mês</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Folgas e férias no mesmo calendário</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Alertas de conflito de agenda</div>
                            </div>
                        </div>
                        <div class="feature-slide-image">
                            <div class="feature-shot">
                                <div class="shot-head">
                                    <span class="shot-title"><i class="fas fa-calendar-days"></i> Calendário</span>
                                    <span class="shot-badge gray">Semana</span>
                                </div>
                                <div class="cal-grid" style="min-height:150px">
                                    <div class="cal-col">
                                        <div class="cal-day">Seg</div>
                                        <div class="cal-chip" style="background:#0ea5e9;flex:2">Portal<small>Ana</small></div>
                                        <div class="cal-chip" style="background:#34d399;flex:1">App<small>Bruno</small></div>
                                    </div>
                                    <div class="cal-col">
                                        <div class="cal-day">Ter</div>
                                        <div class="cal-chip" style="background:#fbbf24;flex:1">Design<small>Carla</small></div>
                                        <div class="cal-chip" style="background:#c084fc;flex:2">API<small>Diego</small></div>
                                    </div>
                                    <div class="cal-col">
                                        <div class="cal-day">Qua</div>
                                        <div class="cal-chip" style="background:#0ea5e9;flex:2">Portal<small>Ana</small></div>
                                    </div>
                                    <div class="cal-col">
                                        <div class="cal-day">Qui</div>
                                        <div class="cal-chip" style="background:#f472b6;flex:1">Folga<small>Carla</small></div>
                                        <div class="cal-chip" style="background:#34d399;flex:2">App<small>Bruno</small></div>
                                    </div>
                                    <div class="cal-col">
                                        <div class="cal-day">Sex</div>
                                        <div class="cal-chip" style="background:#c084fc;flex:1">API<small>Diego</small></div>
                                        <div class="cal-chip" style="background:#0ea5e9;flex:1">Portal<small>Ana</small></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 4 — Relatórios -->
                <div class="swiper-slide">
                    <div class="feature-slide">
                        <div class="feature-slide-content">
                            <div class="feature-slide-tag"><i class="fas fa-chart-column"></i> Relatórios</div>
                            <h3>Do registro à decisão</h3>
                            <p>Relatórios de horas por projeto, cliente e colaborador — com filtros por período e exportação para análise externa.</p>
                            <div class="feature-items">
                                <div class="feature-item"><i class="fas fa-check"></i> Horas por projeto, cliente e pessoa</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Filtros por período e equipe</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Ranking de produtividade</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Exportação com um clique</div>
                            </div>
                        </div>
                        <div class="feature-slide-image">
                            <div class="feature-shot">
                                <div class="shot-head">
                                    <span class="shot-title"><i class="fas fa-chart-column"></i> Relatório de horas</span>
                                    <span class="shot-badge">30 dias</span>
                                </div>
                                <div class="shot-card">
                                    <div class="shot-card-head">Horas por dia <span>últimos 7 dias</span></div>
                                    <div class="shot-bars">
                                        <i style="height:50%"></i><i style="height:75%"></i><i style="height:60%"></i><i style="height:90%"></i><i style="height:68%"></i><i style="height:100%"></i><i style="height:45%"></i>
                                    </div>
                                </div>
                                <div class="shot-track">
                                    <span class="shot-avatar" style="background:#0ea5e9">AS</span>
                                    <div class="shot-track-info"><b>Portal do cliente</b><small>Projeto DECLOCK Web</small></div>
                                    <span class="shot-time">32,5h</span>
                                </div>
                                <div class="shot-track">
                                    <span class="shot-avatar" style="background:#34d399">BR</span>
                                    <div class="shot-track-info"><b>App mobile</b><small>Projeto DECLOCK App</small></div>
                                    <span class="shot-time">27,0h</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="swiper-nav">
            <button class="swiper-btn" id="featPrev" aria-label="Anterior"><i class="fas fa-arrow-left"></i></button>
            <div class="swiper-progress"><div class="swiper-progress-bar" id="featProgress"></div></div>
            <div class="swiper-count" id="featCount">1 / 4</div>
            <button class="swiper-btn" id="featNext" aria-label="Próximo"><i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
</section>

<!-- ══ MODULES ══ -->
<section class="modules" id="modulos">
    <div class="container" style="position:relative;z-index:1">
        <div class="section-header" style="text-align:center">
            <div class="section-tag reveal" style="justify-content:center">Módulos</div>
            <h2 class="section-title reveal delay-1" style="margin:0 auto .9rem">Tudo que sua equipe precisa</h2>
            <p class="section-sub reveal delay-2" style="margin:0 auto;color:rgba(255,255,255,.55)">9 módulos integrados, sem necessidade de ferramentas terceiras para a operação.</p>
        </div>
        <div class="modules-grid">
            <div class="module-card reveal delay-1">
                <div class="mod-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fas fa-stopwatch"></i></div>
                <h4>Rastreador</h4>
                <p>Cronômetro por projeto e cliente, com visão em tempo real do time.</p>
            </div>
            <div class="module-card reveal delay-2">
                <div class="mod-icon" style="background:rgba(52,211,153,.12);color:#34d399"><i class="fas fa-table-list"></i></div>
                <h4>Timesheet</h4>
                <p>Visão semanal de lançamentos, totais e conferência do período.</p>
            </div>
            <div class="module-card reveal delay-3">
                <div class="mod-icon" style="background:rgba(251,191,36,.12);color:#fbbf24"><i class="fas fa-calendar-days"></i></div>
                <h4>Calendário</h4>
                <p>Alocações por dia, semana e mês, com arrastar e soltar.</p>
            </div>
            <div class="module-card reveal delay-4">
                <div class="mod-icon" style="background:rgba(248,113,113,.12);color:#f87171"><i class="fas fa-diagram-project"></i></div>
                <h4>Projetos</h4>
                <p>Etapas com cronograma, planejamento de equipe e acompanhamento.</p>
            </div>
            <div class="module-card reveal delay-1">
                <div class="mod-icon" style="background:rgba(192,132,252,.12);color:#c084fc"><i class="fas fa-building"></i></div>
                <h4>Clientes</h4>
                <p>Projetos organizados por cliente, com visão consolidada de horas.</p>
            </div>
            <div class="module-card reveal delay-2">
                <div class="mod-icon" style="background:rgba(251,146,60,.12);color:#fb923c"><i class="fas fa-tags"></i></div>
                <h4>Tags</h4>
                <p>Classifique lançamentos por tipo de atividade e contexto.</p>
            </div>
            <div class="module-card reveal delay-3">
                <div class="mod-icon" style="background:rgba(244,114,182,.12);color:#f472b6"><i class="fas fa-umbrella-beach"></i></div>
                <h4>Folgas e férias</h4>
                <p>Solicitações com aprovação e registro integrado ao calendário.</p>
            </div>
            <div class="module-card reveal delay-4">
                <div class="mod-icon" style="background:rgba(56,189,248,.12);color:#38bdf8"><i class="fas fa-users-gear"></i></div>
                <h4>Usuários e perfis</h4>
                <p>Controle de acesso por perfil e multiempresa com dados isolados.</p>
            </div>
            <div class="module-card reveal delay-1">
                <div class="mod-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9"><i class="fas fa-gauge-high"></i></div>
                <h4>Dashboard</h4>
                <p>Visão executiva: horas, rastreadores e alertas em um painel.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══ FAQ ══ -->
<section class="faq" id="perguntas">
    <div class="container">
        <div class="section-header" style="text-align:center">
            <div class="section-tag reveal" style="justify-content:center">Perguntas frequentes</div>
            <h2 class="section-title reveal delay-1" style="margin:0 auto .9rem">Tudo o que você precisa saber antes de começar</h2>
            <p class="section-sub reveal delay-2" style="margin:0 auto">Respostas rápidas sobre como o DECLOCK organiza o tempo da sua equipe.</p>
        </div>
        <div class="faq-grid">
            <article class="faq-card reveal delay-1">
                <div class="faq-q">
                    <i class="fas fa-question"></i>
                    <h3>O DECLOCK é só um timesheet?</h3>
                </div>
                <p>Não. O DECLOCK combina rastreador de tempo em tempo real, timesheet semanal, calendário de alocações, projetos e relatórios em uma única plataforma.</p>
            </article>
            <article class="faq-card reveal delay-2">
                <div class="faq-q">
                    <i class="fas fa-mobile-screen"></i>
                    <h3>Precisa instalar alguma coisa?</h3>
                </div>
                <p>Não. O sistema roda direto no navegador e funciona bem no celular — a equipe registra horas de onde estiver.</p>
            </article>
            <article class="faq-card reveal delay-3">
                <div class="faq-q">
                    <i class="fas fa-users-gear"></i>
                    <h3>Consigo separar acessos por perfil?</h3>
                </div>
                <p>Sim. O DECLOCK é multiempresa, com dados isolados e permissões por perfil — cada pessoa vê apenas o que precisa.</p>
            </article>
            <article class="faq-card reveal delay-4">
                <div class="faq-q">
                    <i class="fas fa-file-export"></i>
                    <h3>Dá para exportar as horas lançadas?</h3>
                </div>
                <p>Sim. Os relatórios filtram por período, projeto, cliente e colaborador, e podem ser exportados para análise externa.</p>
            </article>
        </div>
    </div>
</section>

<!-- ══ CTA FINAL ══ -->
<section class="cta-section">
    <div class="cta-lines">
        <div class="cta-line"></div>
        <div class="cta-line"></div>
        <div class="cta-line"></div>
    </div>
    <div class="container cta-inner reveal">
        <div class="section-tag" style="justify-content:center">Pronto para começar?</div>
        <h2 class="section-title" style="margin:0 auto 1rem">Coloque o tempo da sua<br>equipe sob controle hoje</h2>
        <p class="section-sub" style="text-align:center;margin:0 auto 2.5rem">Cadastro em menos de um minuto. A equipe começa a registrar horas no mesmo dia.</p>
        <div class="cta-actions">
            @auth
                <a href="{{ route('home') }}" class="btn-cta-white">Acessar painel</a>
            @else
                <a href="{{ route('cadastro.empresa.create') }}" class="btn-cta-white">Começar agora</a>
            @endauth
            <a href="{{ route('site.sobre') }}" class="btn-cta-ghost">
                <i class="fas fa-circle-info"></i> Conhecer a DECLOCK
            </a>
        </div>
    </div>
</section>

<!-- ══ FOOTER ══ -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-logo">
                    <div class="footer-logo-icon">
                        <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="Logo DECLOCK">
                    </div>
                    DECLOCK
                </div>
                <p class="footer-tagline">Sistema de gestão de tempo para equipes. Simples para o time, poderoso para o gestor.</p>
            </div>
            <div class="footer-col">
                <h5>Produto</h5>
                <ul>
                    <li><a href="#funcionalidades">Funcionalidades</a></li>
                    <li><a href="#modulos">Módulos</a></li>
                    <li><a href="#perguntas">Perguntas</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Empresa</h5>
                <ul>
                    <li><a href="{{ route('site.sobre') }}">Sobre a DECLOCK</a></li>
                    <li><a href="https://www.sonyd.com.br" target="_blank" rel="noopener noreferrer">Sonyd Tech</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>Suporte</h5>
                <ul>
                    <li><a href="{{ route('login') }}">Entrar no sistema</a></li>
                    <li><a href="{{ route('cadastro.empresa.create') }}">Criar conta</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} DECLOCK. Todos os direitos reservados.</p>
            <p>
                Criado por
                <a href="https://www.sonyd.com.br" target="_blank" rel="noopener noreferrer">Sonyd Tech</a>
            </p>
        </div>
    </div>
</footer>

<!-- ══ SCRIPTS ══ -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Navbar ── */
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 30);
    }, { passive: true });

    /* ── Mobile menu ── */
    const toggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    const navActions = document.getElementById('navActions');
    toggle.addEventListener('click', () => {
        const open = navLinks.classList.toggle('open');
        navActions.classList.toggle('open', open);
        const spans = toggle.querySelectorAll('span');
        if (open) {
            spans[0].style.cssText = 'transform:rotate(45deg) translate(5px,5px)';
            spans[1].style.cssText = 'opacity:0';
            spans[2].style.cssText = 'transform:rotate(-45deg) translate(5px,-5px)';
        } else {
            spans.forEach(s => s.style.cssText = '');
        }
    });
    document.querySelectorAll('#navLinks a').forEach(a => a.addEventListener('click', () => {
        navLinks.classList.remove('open'); navActions.classList.remove('open');
        toggle.querySelectorAll('span').forEach(s => s.style.cssText = '');
    }));

    /* ── Mouse glow ── */
    const glow = document.getElementById('mouseGlow');
    let glowVisible = false;
    document.addEventListener('mousemove', e => {
        glow.style.left = e.clientX + 'px';
        glow.style.top = e.clientY + 'px';
        if (!glowVisible) { glow.style.opacity = '1'; glowVisible = true; }
    }, { passive: true });
    document.addEventListener('mouseleave', () => { glow.style.opacity = '0'; glowVisible = false; });

    /* ── Reveal on scroll ── */
    const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); }
        });
    }, { threshold: 0.1 });
    revealEls.forEach(el => io.observe(el));

    /* ── Hero carousel ── */
    const heroCarousel = document.getElementById('heroCarousel');
    const heroSlides = heroCarousel ? Array.from(heroCarousel.querySelectorAll('.hero-carousel-slide')) : [];
    if (heroCarousel && heroSlides.length > 1) {
        let heroIndex = 0;
        let heroTimer = null;

        const showHeroSlide = (nextIndex) => {
            heroSlides[heroIndex].classList.remove('is-active');
            heroIndex = nextIndex;
            heroSlides[heroIndex].classList.add('is-active');
        };

        const startHeroTimer = () => {
            heroTimer = setInterval(() => {
                showHeroSlide((heroIndex + 1) % heroSlides.length);
            }, 3200);
        };

        const stopHeroTimer = () => {
            if (heroTimer) { clearInterval(heroTimer); heroTimer = null; }
        };

        heroCarousel.addEventListener('mouseenter', stopHeroTimer);
        heroCarousel.addEventListener('mouseleave', startHeroTimer);
        startHeroTimer();
    }

    /* ── Typewriter ── */
    const target = document.getElementById('typewriterTarget');
    const words = ['um clique', 'tempo real', 'uma tela'];
    let wi = 0, ci = 0, deleting = false;
    function typeStep() {
        const word = words[wi];
        if (!deleting) {
            target.textContent = word.slice(0, ci + 1);
            ci++;
            if (ci === word.length) { deleting = true; setTimeout(typeStep, 1600); return; }
        } else {
            target.textContent = word.slice(0, ci - 1);
            ci--;
            if (ci === 0) { deleting = false; wi = (wi + 1) % words.length; }
        }
        setTimeout(typeStep, deleting ? 60 : 90);
    }
    setTimeout(typeStep, 1200);

    /* ── Features Swiper ── */
    const featSwiper = new Swiper('#swiperFeature', {
        slidesPerView: 1,
        spaceBetween: 0,
        speed: 650,
        grabCursor: true,
        autoHeight: true,
        navigation: { prevEl: '#featPrev', nextEl: '#featNext' },
        on: {
            slideChange(sw) { updateFeatProgress(sw); },
            init(sw) { updateFeatProgress(sw); }
        }
    });
    function updateFeatProgress(sw) {
        const total = sw.slides.length;
        const cur = sw.activeIndex + 1;
        document.getElementById('featCount').textContent = cur + ' / ' + total;
        document.getElementById('featProgress').style.width = (cur / total * 100) + '%';
        document.getElementById('featPrev').disabled = sw.isBeginning;
        document.getElementById('featNext').disabled = sw.isEnd;
    }

    /* ── Card tilt hover ── */
    document.querySelectorAll('.problem-card, .faq-card').forEach(card => {
        card.addEventListener('mousemove', function (e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            const rx = -(y / rect.height) * 5;
            const ry = (x / rect.width) * 5;
            this.style.transform = `translateY(-6px) perspective(600px) rotateX(${rx}deg) rotateY(${ry}deg)`;
        });
        card.addEventListener('mouseleave', function () {
            this.style.transform = '';
        });
    });

});
</script>

</body>
</html>
