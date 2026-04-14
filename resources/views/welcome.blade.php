<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="DeCars — Sistema completo para gestão de locadoras de veículos. Reservas, frota, financeiro, check-in digital, multas e muito mais.">
    <title>DeCars — Sistema para Locadoras de Veículos</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicons/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

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
        --bg: #0a1628;          /* navy escuro — accent principal */
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

    /* Reveal genérico */
    .reveal {
        opacity: 0; transform: translateY(36px);
        transition: opacity .75s cubic-bezier(.16,1,.3,1), transform .75s cubic-bezier(.16,1,.3,1);
    }
    .reveal.visible { opacity: 1; transform: none; }
    .reveal-left { opacity: 0; transform: translateX(-40px); transition: opacity .75s cubic-bezier(.16,1,.3,1), transform .75s cubic-bezier(.16,1,.3,1); }
    .reveal-left.visible { opacity: 1; transform: none; }
    .reveal-right { opacity: 0; transform: translateX(40px); transition: opacity .75s cubic-bezier(.16,1,.3,1), transform .75s cubic-bezier(.16,1,.3,1); }
    .reveal-right.visible { opacity: 1; transform: none; }
    .reveal-scale { opacity: 0; transform: scale(.88); transition: opacity .7s cubic-bezier(.16,1,.3,1), transform .7s cubic-bezier(.16,1,.3,1); }
    .reveal-scale.visible { opacity: 1; transform: scale(1); }
    .delay-1 { transition-delay: .1s; }
    .delay-2 { transition-delay: .2s; }
    .delay-3 { transition-delay: .32s; }
    .delay-4 { transition-delay: .44s; }
    .delay-5 { transition-delay: .56s; }

    /* Floating blob */
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
    @keyframes ticker { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    @keyframes shimmer {
        0%   { background-position: -600px 0; }
        100% { background-position: 600px 0; }
    }
    @keyframes borderGlow {
        0%,100% { box-shadow: 0 0 0 0 rgba(10,22,40,0); }
        50%      { box-shadow: 0 0 0 4px rgba(10,22,40,.12); }
    }
    @keyframes countUp { from { opacity: 0; } to { opacity: 1; } }

    /* Typewriter cursor */
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
    .nav-logo:hover .nav-logo-icon { transform: rotate(-8deg) scale(1.08); }
    .nav-logo em { color: var(--cyan); font-style: normal; }
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
    /* Blobs decorativos */
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
    /* Grade pontilhada de fundo */
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
        align-items: center; gap: 4rem; padding: rem 0;
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
    .hero-differential {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .38rem .9rem;
        margin-bottom: 1.2rem;
        border-radius: 999px;
        background: rgba(14,165,233,.08);
        border: 1px solid rgba(14,165,233,.2);
        color: var(--bg);
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .hero-differential i { color: var(--cyan); font-size: .72rem; }
    .hero-credit {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .4rem .85rem;
        margin-top: 1rem;
        border-radius: 999px;
        background: var(--bg);
        border: 1px solid rgba(10,22,40,.12);
        color: white;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .hero-credit a {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        color: inherit;
        text-decoration: none;
    }
    .hero-credit img {
        width: 10em;
                /* height: 20px; */
        object-fit: contain;
        flex-shrink: 0;
    }

    /* Imagem hero com animação */
    .hero-visual {
        position: relative;
        animation: floatY 5s ease-in-out infinite;
    }
    .hero-carousel-wrap {
        position: relative;
    }
    .hero-carousel {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: var(--shadow-lg), 0 0 0 1px var(--border);
        position: relative;
        aspect-ratio: 16 / 10;
        background: var(--surface);
    }
    .hero-carousel::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, var(--bg), var(--cyan), var(--bg));
        z-index: 1; background-size: 200% 100%;
        animation: shimmer 3s linear infinite;
    }
    .hero-carousel-slide {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transform: scale(1.02);
        transition: opacity .75s ease, transform .75s ease;
        pointer-events: none;
    }
    .hero-carousel-slide.is-active {
        opacity: 1;
        transform: scale(1);
        pointer-events: auto;
    }
    .hero-carousel-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .hero-carousel-slide::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 45%, rgba(10,22,40,.18) 100%);
        pointer-events: none;
    }
    .hero-carousel-caption {
        position: absolute;
        left: 16px;
        right: 16px;
        bottom: 16px;
        z-index: 2;
        padding: .85rem 1rem;
        border-radius: 14px;
        background: rgba(10,22,40,.84);
        color: #fff;
        backdrop-filter: blur(12px);
        box-shadow: 0 10px 28px rgba(0,0,0,.18);
    }
    .hero-carousel-caption strong {
        display: block;
        margin-bottom: .15rem;
        font-size: .92rem;
        font-weight: 800;
        letter-spacing: -.01em;
    }
    .hero-carousel-caption span {
        display: block;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--cyan);
        margin-bottom: .22rem;
    }
    .hero-carousel-caption small {
        display: block;
        font-size: .76rem;
        line-height: 1.45;
        color: rgba(255,255,255,.74);
    }
    /* Badges flutuantes sobre a imagem */
    .hero-badge-float {
        position: absolute; display: flex; align-items: center; gap: .6rem;
        background: #fff; border: 1px solid var(--border);
        box-shadow: var(--shadow); border-radius: 12px;
        padding: .65rem 1rem; font-size: .8rem; font-weight: 700; color: var(--bg);
        white-space: nowrap;
    }
    .hero-badge-float i { font-size: .9rem; }
    .hero-badge-float.pos-tl { top: -18px; left: -20px; animation: floatY 4s ease-in-out infinite; }
    .hero-badge-float.pos-br { bottom: -18px; right: -20px; animation: floatY 4.5s ease-in-out infinite .5s; }
    .hero-badge-float .dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; animation: pulse 1.5s ease-in-out infinite; }

    /* ══ SALES DIFFERENTIAL ══ */
    .sales-diff {
        padding: 5rem 0;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
    }
    .sales-diff-inner {
        display: grid;
        grid-template-columns: 1fr 1.05fr;
        gap: 2.5rem;
        align-items: center;
    }
    .sales-diff-tag {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .33rem .8rem;
        margin-bottom: 1rem;
        border-radius: 999px;
        background: rgba(14,165,233,.08);
        color: var(--bg);
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .sales-diff-title {
        font-family: 'Raleway', sans-serif;
        font-size: clamp(1.8rem, 3.4vw, 2.8rem);
        font-weight: 900;
        line-height: 1.1;
        letter-spacing: -.03em;
        color: var(--bg);
        margin-bottom: 1rem;
    }
    .sales-diff-copy {
        font-size: 1rem;
        line-height: 1.8;
        color: var(--muted);
        max-width: 560px;
        margin-bottom: 1.5rem;
    }
    .sales-diff-points {
        display: grid;
        gap: .9rem;
        margin-bottom: 1.8rem;
    }
    .sales-diff-point {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        font-size: .92rem;
        line-height: 1.6;
        color: var(--text);
    }
    .sales-diff-point i {
        color: #22c55e;
        margin-top: .2rem;
        font-size: .88rem;
        flex-shrink: 0;
    }
    .sales-diff-actions {
        display: flex;
        align-items: center;
        gap: .9rem;
        flex-wrap: wrap;
    }
    .sales-diff-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .9rem 1.2rem;
        border-radius: var(--radius);
        background: var(--bg);
        color: #fff;
        font-size: .9rem;
        font-weight: 700;
        transition: transform .2s, background .2s, box-shadow .2s;
    }
    .sales-diff-link:hover {
        background: var(--bg-hover);
        transform: translateY(-1px);
        box-shadow: 0 10px 26px rgba(10,22,40,.18);
    }
    .sales-diff-note {
        font-size: .84rem;
        color: var(--muted);
    }
    .sales-diff-visual {
        position: relative;
        padding: 1rem;
        border-radius: 22px;
        background: #fff;
        border: 1.5px solid var(--border);
        box-shadow: var(--shadow-lg);
    }
    .sales-diff-visual img {
        width: 100%;
        border-radius: 16px;
        display: block;
        box-shadow: 0 18px 32px rgba(15,23,42,.12);
    }
    .sales-diff-badge {
        position: absolute;
        top: -14px;
        left: 18px;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .52rem .8rem;
        border-radius: 999px;
        background: #0f172a;
        color: #fff;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        box-shadow: 0 10px 24px rgba(15,23,42,.2);
    }
    .sales-diff-badge i { color: var(--cyan); }

    /* ══ CLIENTS TICKER ══ */
    .clients {
        padding: 3.5rem 0;
        background: var(--surface);
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        overflow: hidden;
    }
    .clients-label {
        text-align: center; font-size: .72rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .1em;
        color: var(--muted2); margin-bottom: 2rem;
    }
    .clients-track {
        display: flex; align-items: center; gap: 2.5rem;
        width: max-content;
        animation: ticker 30s linear infinite;
    }
    .clients-track:hover { animation-play-state: paused; }
    .client-chip {
        font-family: 'Raleway', sans-serif; font-weight: 800; font-size: 1rem;
        letter-spacing: -.01em; color: var(--muted2); white-space: nowrap;
        padding: .5rem 1.5rem; border: 1.5px solid var(--border);
        border-radius: 8px; opacity: .6;
        transition: opacity .3s, color .3s, border-color .3s, transform .3s;
    }
    .client-chip:hover { opacity: 1; color: var(--bg); border-color: var(--bg); transform: translateY(-2px); }

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
    .swiper-feature .swiper-slide {
        transition: opacity .5s, transform .5s;
    }
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
    .feature-item {
        display: flex; align-items: center; gap: .65rem;
        font-size: .85rem; font-weight: 500; color: var(--text);
    }
    .feature-item i { color: var(--bg); font-size: .78rem; flex-shrink: 0; }
    .feature-slide-image {
        height: 100%; min-height: 440px;
        background: var(--surface);
        display: flex; align-items: center; justify-content: center;
        border-left: 1.5px solid var(--border); overflow: hidden;
    }
    .feature-slide-image img { width: 100%; height: 100%; object-fit: scale-down; }
    /* .feature-slide-image img { width: 100%; height: 100%; object-fit: cover; } */
    .feature-placeholder {
        display: flex; flex-direction: column; align-items: center;
        justify-content: center; gap: .75rem; color: var(--muted2); text-align: center; padding: 2rem;
    }
    .feature-placeholder i { font-size: 3.5rem; opacity: .25; color: var(--bg); }
    .feature-placeholder span { font-size: .78rem; }
    .site-sales-preview {
        width: 100%;
        height: 100%;
        padding: 1.25rem;
        background:
            radial-gradient(circle at top left, rgba(14,165,233,.12), transparent 38%),
            linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 1rem;
    }
    .site-sales-preview__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }
    .site-sales-preview__header strong {
        font-family: 'Raleway', sans-serif;
        font-size: 1rem;
        font-weight: 900;
        color: var(--bg);
        letter-spacing: -.02em;
    }
    .site-sales-preview__header span {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--cyan);
        background: rgba(14,165,233,.08);
        padding: .28rem .6rem;
        border-radius: 999px;
    }
    .site-sales-models {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .75rem;
    }
    .site-sales-model {
        background: rgba(255,255,255,.88);
        border: 1px solid rgba(10,22,40,.1);
        border-radius: 16px;
        padding: .9rem;
        box-shadow: 0 12px 26px rgba(15,23,42,.06);
    }
    .site-sales-model__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .65rem;
    }
    .site-sales-model__name {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--bg);
    }
    .site-sales-model__dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    .site-sales-model__screen {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(10,22,40,.08);
        background: #fff;
        min-height: 132px;
        display: flex;
        flex-direction: column;
    }
    .site-sales-model__hero {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: .9rem .75rem;
        color: #fff;
        text-align: center;
        font-family: 'Raleway', sans-serif;
        font-weight: 900;
        font-size: .9rem;
        line-height: 1.2;
    }
    .site-sales-model__bar {
        display: flex;
        gap: .35rem;
        padding: .55rem .65rem;
        background: #fff;
    }
    .site-sales-model__bar span {
        flex: 1;
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
    }
    .site-sales-model__footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        margin-top: .65rem;
        font-size: .72rem;
        color: var(--muted);
        line-height: 1.4;
    }
    .site-sales-model__footer strong {
        display: block;
        color: var(--bg);
        font-size: .74rem;
    }
    .site-sales-model__chips {
        display: flex;
        gap: .35rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .site-sales-model__chips span {
        padding: .22rem .45rem;
        border-radius: 999px;
        background: rgba(10,22,40,.05);
        color: var(--bg);
        font-size: .6rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    /* Controles swiper */
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
    /* Grade decorativa no fundo escuro */
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

    /* ══ STATS ══ */
    .stats { padding: 6rem 0; background: #fff; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; }
    .stat-item {
        text-align: center; padding: 2.5rem 1.5rem;
        border: 1.5px solid var(--border); border-radius: 16px;
        position: relative; overflow: hidden;
        transition: border-color .3s, transform .35s cubic-bezier(.16,1,.3,1), box-shadow .3s;
    }
    .stat-item:hover { border-color: var(--bg); transform: translateY(-4px); box-shadow: var(--shadow); }
    .stat-item::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
        background: var(--bg); transform: scaleX(0); transform-origin: center;
        transition: transform .4s cubic-bezier(.16,1,.3,1);
    }
    .stat-item:hover::after { transform: scaleX(1); }
    .stat-icon { font-size: 1.5rem; color: var(--bg); margin-bottom: 1rem; opacity: .7; }
    .stat-number {
        font-family: 'Raleway', sans-serif;
        font-size: clamp(2rem, 3vw, 2.8rem);
        font-weight: 900; color: var(--bg); line-height: 1; margin-bottom: .4rem;
    }
    .stat-label { font-size: .85rem; color: var(--muted); font-weight: 500; }

    /* ══ TESTIMONIALS ══ */
    .testimonials { padding: 7rem 0; background: var(--surface); }
    .testimonials-inner { display: grid; grid-template-columns: 280px 1fr; gap: 5rem; align-items: start; }
    .swiper-testimonial .swiper-slide { height: auto; }
    .testi-card {
        padding: 2rem; background: #fff;
        border: 1.5px solid var(--border); border-radius: 16px; height: 100%;
        box-shadow: var(--shadow-sm);
        transition: transform .35s cubic-bezier(.16,1,.3,1), box-shadow .35s;
    }
    .testi-card:hover { transform: translateY(-4px); box-shadow: var(--shadow); }
    .testi-stars { display: flex; gap: 3px; margin-bottom: 1.2rem; }
    .testi-stars i { color: #f59e0b; font-size: .8rem; }
    .testi-text { font-size: .92rem; color: var(--text); line-height: 1.8; margin-bottom: 1.6rem; font-style: italic; }
    .testi-author { display: flex; align-items: center; gap: .9rem; }
    .testi-avatar {
        width: 42px; height: 42px; border-radius: 50%;
        background: var(--bg); display: flex; align-items: center;
        justify-content: center; font-family: 'Raleway', sans-serif;
        font-weight: 800; font-size: .85rem; color: #fff; flex-shrink: 0;
    }
    .testi-name { font-weight: 700; font-size: .88rem; color: var(--bg); }
    .testi-role { font-size: .78rem; color: var(--muted); margin-top: .1rem; }
    .testi-nav { display: flex; gap: .75rem; margin-top: 2rem; }
    .testi-btn {
        width: 44px; height: 44px; border-radius: 50%;
        border: 1.5px solid var(--border); background: #fff;
        color: var(--bg); font-size: .8rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all .25s; box-shadow: var(--shadow-sm);
    }
    .testi-btn:hover { border-color: var(--bg); background: var(--bg); color: #fff; }

    /* ══ PRICING ══ */
    .pricing { padding: 7rem 0; background: #fff; }
    .pricing-toggle { display: flex; align-items: center; justify-content: center; gap: 1rem; margin: 2rem 0 3rem; }
    .pricing-toggle span { font-size: .875rem; font-weight: 600; color: var(--muted); transition: color .3s; }
    .pricing-toggle span.active { color: var(--bg); }
    .pricing-highlight {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 1rem 1.25rem;
        align-items: center;
        padding: 1.25rem 1.4rem;
        margin: 2rem auto 0;
        border: 1.5px solid rgba(10,22,40,.12);
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(10,22,40,.04), rgba(10,22,40,.015));
        box-shadow: var(--shadow-sm);
        max-width: 980px;
    }
    .pricing-highlight-icon {
        width: 52px; height: 52px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        background: var(--bg); color: #fff; font-size: 1.1rem;
        box-shadow: 0 10px 24px rgba(10,22,40,.16);
        flex-shrink: 0;
    }
    .pricing-highlight h3 {
        font-family: 'Raleway', sans-serif;
        font-weight: 900;
        font-size: 1.05rem;
        color: var(--bg);
        margin-bottom: .25rem;
    }
    .pricing-highlight p {
        font-size: .9rem;
        line-height: 1.7;
        color: var(--muted);
        max-width: 680px;
    }
    .pricing-highlight-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .8rem 1rem;
        border-radius: var(--radius);
        border: 1.5px solid var(--border);
        color: var(--bg);
        font-size: .85rem;
        font-weight: 700;
        transition: background .2s, border-color .2s, transform .2s;
        white-space: nowrap;
    }
    .pricing-highlight-link:hover {
        background: rgba(10,22,40,.04);
        border-color: var(--bg);
        transform: translateY(-1px);
    }
    .toggle-switch {
        width: 48px; height: 26px; border-radius: 13px;
        background: var(--bg); position: relative; cursor: pointer;
        transition: background .3s;
    }
    .toggle-switch::after {
        content: ''; position: absolute; top: 3px; left: 3px;
        width: 20px; height: 20px; border-radius: 50%; background: #fff;
        transition: transform .3s cubic-bezier(.16,1,.3,1);
        box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    .toggle-switch.annual::after { transform: translateX(22px); }
    .badge-save {
        font-size: .65rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
        color: #fff; background: #22c55e; border-radius: 999px; padding: .2rem .55rem; margin-left: .3rem;
    }
    .pricing-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; align-items: start; }
    .plan-card {
        padding: 2.2rem; background: #fff;
        border: 1.5px solid var(--border); border-radius: 16px;
        transition: border-color .3s, transform .35s cubic-bezier(.16,1,.3,1), box-shadow .3s;
        position: relative; overflow: hidden;
    }
    .plan-card:hover { transform: translateY(-4px); box-shadow: var(--shadow); }
    .plan-card.popular { border-color: var(--bg); box-shadow: 0 0 0 4px rgba(10,22,40,.06); }
    .plan-badge {
        display: inline-block; font-size: .68rem; font-weight: 700; letter-spacing: .08em;
        text-transform: uppercase; color: #fff; background: var(--bg);
        border-radius: 999px; padding: .25rem .75rem; margin-bottom: 1.1rem;
    }
    .plan-name { font-family: 'Raleway', sans-serif; font-weight: 900; font-size: 1.2rem; color: var(--bg); margin-bottom: 1.4rem; }
    .plan-price { margin-bottom: 1.5rem; }
    .plan-price .currency { font-size: 1.1rem; color: var(--muted); vertical-align: super; margin-right: 2px; }
    .plan-price .amount { font-family: 'Raleway', sans-serif; font-size: 2.8rem; font-weight: 900; color: var(--bg); transition: all .4s; }
    .plan-price .period { font-size: .8rem; color: var(--muted); }
    .plan-price .annual-note { font-size: .75rem; color: #22c55e; display: block; margin-top: .3rem; }
    .plan-desc { font-size: .85rem; color: var(--muted); line-height: 1.65; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border); }
    .plan-features { display: flex; flex-direction: column; gap: .7rem; margin-bottom: 2rem; }
    .plan-feature { display: flex; align-items: flex-start; gap: .6rem; font-size: .85rem; color: var(--text); }
    .plan-feature i { color: var(--bg); font-size: .78rem; margin-top: .18rem; flex-shrink: 0; }
    .btn-plan {
        display: block; width: 100%; padding: .85rem;
        border-radius: var(--radius); font-weight: 700; font-size: .9rem;
        text-align: center; transition: all .25s;
    }
    .btn-plan-primary { background: var(--bg); color: #fff; }
    .btn-plan-primary:hover { background: var(--bg-hover); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(10,22,40,.2); }
    .btn-plan-ghost { border: 1.5px solid var(--border); color: var(--bg); background: transparent; }
    .btn-plan-ghost:hover { border-color: var(--bg); background: rgba(10,22,40,.04); }

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
    /* Linhas decorativas */
    .cta-lines {
        position: absolute; inset: 0; overflow: hidden; pointer-events: none;
    }
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
    .btn-whatsapp {
        display: inline-flex; align-items: center; gap: .6rem;
        padding: .9rem 2rem; background: #25d366; color: #fff;
        border-radius: var(--radius); font-weight: 700; font-size: .95rem;
        transition: all .25s;
    }
    .btn-whatsapp:hover { background: #1da851; transform: translateY(-2px); }

    /* ══ FOOTER ══ */
    .footer { background: var(--surface); border-top: 1px solid var(--border); padding: 4.5rem 0 2.5rem; }
    .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem; padding-bottom: 3rem; border-bottom: 1px solid var(--border); }
    .footer-logo {
        display: flex; align-items: center; gap: .7rem;
        font-family: 'Raleway', sans-serif; font-weight: 900; font-size: 1.25rem;
        color: var(--bg); margin-bottom: 1rem; letter-spacing: -.02em;
    }
    .footer-logo-icon { width: 30px; height: 30px; border-radius: 7px;   display: flex; align-items: center; justify-content: center; font-size: .75rem; color: #fff; }
    .footer-logo em { color: var(--cyan); font-style: normal; }
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
    .footer-brand-credit {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .55rem .8rem;
        border-radius: 999px;
        border: 1px solid var(--border);
        color: rgba(255,255,255,.75);
        background: var(--bg);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .02em;
        text-decoration: none;
        transition: transform .2s, border-color .2s, background .2s, box-shadow .2s;
    }
    .footer-brand-credit:hover {
        transform: translateY(-1px);
        border-color: rgba(10,22,40,.2);
        background: #1c2723;
        box-shadow: var(--shadow-sm);
    }
    .footer-brand-credit strong {
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: none;
        color: var(--bg);
    }
    .footer-brand-credit img {
        width: 88px;
        height: auto;
        object-fit: contain;
        display: block;
    }
    .footer-socials { display: flex; gap: .6rem; }
    .social-btn {
        width: 34px; height: 34px; border-radius: 8px;
        border: 1.5px solid var(--border); color: var(--muted);
        display: flex; align-items: center; justify-content: center; font-size: .8rem;
        transition: all .25s;
    }
    .social-btn:hover { border-color: var(--bg); color: var(--bg); background: rgba(10,22,40,.06); transform: translateY(-2px); }

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
        .features-header { grid-template-columns: 1fr; gap: 1.5rem; }
        .feature-slide { grid-template-columns: 1fr; }
        .feature-slide-image { min-height: 260px; border-left: none; border-top: 1.5px solid var(--border); }
        .site-sales-models { grid-template-columns: 1fr; }
        .sales-diff-inner { grid-template-columns: 1fr; gap: 1.75rem; }
        .testimonials-inner { grid-template-columns: 1fr; gap: 2rem; }
        .modules-grid { grid-template-columns: repeat(3, 1fr); }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .footer-grid { grid-template-columns: 1fr 1fr; }
        .hero-carousel-caption { left: 14px; right: 14px; bottom: 14px; }
        .pricing-grid { grid-template-columns: repeat(2, 1fr); }
        .pricing-highlight { grid-template-columns: auto 1fr; }
        .pricing-highlight-link { grid-column: 2; justify-self: start; }
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
        .pricing-grid { grid-template-columns: 1fr; }
        .modules-grid { grid-template-columns: repeat(2, 1fr); }
        .footer-grid { grid-template-columns: 1fr; }
        .hero-badge-float { display: none; }
        .hero-carousel-caption { left: 12px; right: 12px; bottom: 12px; padding: .72rem .85rem; }
        .site-sales-preview { padding: 1rem; }
        .site-sales-model__screen { min-height: 116px; }
        .sales-diff { padding: 4rem 0; }
        .sales-diff-visual { padding: .8rem; }
        .pricing-grid { grid-template-columns: 1fr; }
        .pricing-highlight { grid-template-columns: 1fr; }
        .pricing-highlight-link { grid-column: auto; justify-self: stretch; width: 100%; }
    }
    @media (max-width: 480px) {
        .hero-ctas { flex-direction: column; width: 100%; }
        .btn-hero, .btn-hero-ghost { width: 100%; justify-content: center; text-align: center; }
        .modules-grid { grid-template-columns: 1fr 1fr; }
        .stats-grid { grid-template-columns: 1fr 1fr; }
    }
    </style>
</head>
<body>

<!-- Mouse glow -->
<div class="mouse-glow" id="mouseGlow"></div>

<!-- ══ NAVBAR ══ -->
<nav class="navbar" id="navbar">
    <div class="nav-inner">
        <a href="/" class="nav-logo">
            <div class="nav-logo-icon">
                <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="">
            </div>
            DECARS
        </a>
        <ul class="nav-links" id="navLinks">
            <li><a href="#funcionalidades">Funcionalidades</a></li>
            <li><a href="#modulos">Módulos</a></li>
            <li><a href="#planos">Planos</a></li>
            <li><a href="#depoimentos">Clientes</a></li>
        </ul>
        <div class="nav-actions" id="navActions">
            <a href="{{ route('login') }}" class="btn-ghost-nav">Entrar</a>
            {{-- <a href="#planos" class="btn-nav">Solicitar Demo</a> --}}
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
                <div class="hero-differential reveal">
                    <i class="fas fa-sparkles"></i>
                    Sistema + site de reservas para vender online
                </div>
                <h1 class="hero-title reveal delay-1">
                    Gestão completa<br>da sua locadora<br>em <span class="line-accent" id="typewriterTarget"></span><span class="cursor" id="typewriterCursor"></span>
                </h1>
                <p class="hero-text reveal delay-2">
                    Do check-in digital ao financeiro, do controle de multas às reservas online, tudo em um sistema robusto, pensado para quem vive o dia a dia da locação.
                </p>
                <div class="hero-ctas reveal delay-2">
                    <a href="#planos" class="btn-hero">Ver planos e preços</a>
                    <a href="#funcionalidades" class="btn-hero-ghost">
                        <i class="fas fa-play-circle"></i>
                        Ver como funciona
                    </a>
                </div>
                <div class="hero-trust reveal delay-3">
                    <div class="hero-trust-item"><i class="fas fa-check-circle"></i> Sem taxa de implantação</div>
                    <div class="hero-trust-item"><i class="fas fa-check-circle"></i> Suporte incluso</div>
                    <div class="hero-trust-item"><i class="fas fa-check-circle"></i> Dados na nuvem</div>
                </div>
                <div class="hero-credit reveal delay-4">
                    <a href="https://www.sonyd.com.br" target="_blank" rel="noopener noreferrer" aria-label="Visitar o site da Sonyd Tech">
                        Software da
                        <img src="{{ asset('assets/imgs/logo-sonydtech.webp') }}" alt="Logo Sonyd Tech">
                    </a>
                </div>
            </div>
            <div class="hero-visual reveal-right delay-2">
                {{-- <div class="hero-badge-float pos-tl">
                    <div class="dot"></div> Sistema Online
                </div> --}}
                <div class="hero-carousel-wrap">
                    <div class="hero-carousel" id="heroCarousel">
                        <figure class="hero-carousel-slide is-active">
                            <img src="{{ asset('assets/welcome/site-aluguel.png') }}" alt="Site de reservas online do DeCars">
                            <figcaption class="hero-carousel-caption">
                                <span>Site de vendas</span>
                                <strong>O cliente reserva online com a sua marca</strong>
                                <small>Uma vitrine pronta para vender veículos direto no site.</small>
                            </figcaption>
                        </figure>
                        <figure class="hero-carousel-slide">
                            <img src="{{ asset('assets/welcome/home.png') }}" alt="Painel principal do DeCars">
                            <figcaption class="hero-carousel-caption">
                                <span>Dashboard</span>
                                <strong>Visão executiva da operação</strong>
                                <small>Reservas, faturamento e frota em um só lugar.</small>
                            </figcaption>
                        </figure>
                        <figure class="hero-carousel-slide">
                            <img src="{{ asset('assets/welcome/reservas.png') }}" alt="Lista de reservas do DeCars">
                            <figcaption class="hero-carousel-caption">
                                <span>Reservas online</span>
                                <strong>Receba e organize as locações</strong>
                                <small>O cliente inicia a jornada e a equipe acompanha tudo com clareza.</small>
                            </figcaption>
                        </figure>
                        <figure class="hero-carousel-slide">
                            <img src="{{ asset('assets/welcome/disponibilidade.png') }}" alt="Grade de disponibilidade de veículos">
                            <figcaption class="hero-carousel-caption">
                                <span>Disponibilidade</span>
                                <strong>Confirmação em tempo real</strong>
                                <small>Menos conflito de agenda e mais conversão no portal.</small>
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

<!-- ══ CLIENTS TICKER ══ -->
{{-- <section class="clients">
    <div class="clients-label">Locadoras que confiam na plataforma</div>
    <div style="overflow:hidden">
        <div class="clients-track">
            <div class="client-chip">Locadora Alpha</div>
            <div class="client-chip">Frota Premium</div>
            <div class="client-chip">Auto Rent SP</div>
            <div class="client-chip">Mobilidade RJ</div>
            <div class="client-chip">Veículos & CIA</div>
            <div class="client-chip">CarFlex Natal</div>
            <div class="client-chip">TopCars Goiânia</div>
            <div class="client-chip">Locadora Sul</div>
            <!-- duplicadas para loop infinito -->
            <div class="client-chip">Locadora Alpha</div>
            <div class="client-chip">Frota Premium</div>
            <div class="client-chip">Auto Rent SP</div>
            <div class="client-chip">Mobilidade RJ</div>
            <div class="client-chip">Veículos & CIA</div>
            <div class="client-chip">CarFlex Natal</div>
            <div class="client-chip">TopCars Goiânia</div>
            <div class="client-chip">Locadora Sul</div>
        </div>
    </div>
</section> --}}

<!-- ══ SALES DIFFERENTIAL ══ -->
<section class="sales-diff">
    <div class="container">
        <div class="sales-diff-inner">
            <div class="reveal-left">
                <div class="sales-diff-tag"><i class="fas fa-store"></i> Diferencial competitivo</div>
                <h2 class="sales-diff-title">Além de gerenciar, sua locadora também vende.</h2>
                <p class="sales-diff-copy">
                    O DeCars não entrega apenas controle operacional. Ele também oferece um site de vendas para a locadora escalar os alugueis, gerar mais reservas e transformar a marca em um canal de aquisição.
                </p>
                <div class="sales-diff-points">
                    <div class="sales-diff-point"><i class="fas fa-check-circle"></i> Site público integrado ao sistema, com reserva online e identidade visual da empresa.</div>
                    <div class="sales-diff-point"><i class="fas fa-check-circle"></i> Modelos de apresentação para customizar cores, textos e imagens sem depender de desenvolvimento.</div>
                    <div class="sales-diff-point"><i class="fas fa-check-circle"></i> Uma vitrine que ajuda a converter visitantes em locatários e aumenta a escala da operação.</div>
                </div>
                <div class="sales-diff-actions">
                    <a href="https://locrickson.shop" class="sales-diff-link">Ver um dos modelos de site de vendas</a>
                    <span class="sales-diff-note">Esse é o diferencial que faz o DeCars se destacar entre os sistemas do mercado.</span>
                </div>
            </div>
            <div class="reveal-right delay-1">
                <div class="sales-diff-visual">
                    <div class="sales-diff-badge"><i class="fas fa-sparkles"></i> Site de vendas</div>
                    <img src="{{ asset('assets/welcome/site-aluguel.png') }}" alt="Site de vendas do DeCars">
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
            <h2 class="section-title reveal delay-1" style="margin:0 auto .9rem">Sua locadora ainda usa<br>planilha ou sistema ultrapassado?</h2>
            <p class="section-sub reveal delay-2" style="margin:0 auto">Locadoras perdem dinheiro todo dia por falta de controle integrado. O DeCars resolve na raiz.</p>
        </div>
        <div class="problems-grid">
            <div class="problem-card reveal delay-1">
                <div class="problem-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h3>Reservas duplicadas</h3>
                <p>Controles manuais geram conflito de agenda e veículos saindo sem documentação, expondo a locadora a prejuízos.</p>
            </div>
            <div class="problem-card reveal delay-2">
                <div class="problem-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <h3>Financeiro no escuro</h3>
                <p>Sem relatórios em tempo real, é impossível saber o faturamento real, inadimplências e custo por veículo.</p>
            </div>
            <div class="problem-card reveal delay-3">
                <div class="problem-icon"><i class="fas fa-car-crash"></i></div>
                <h3>Multas e sinistros perdidos</h3>
                <p>Multas chegam meses depois. Sem rastreamento automático, a locadora absorve os custos que deveriam ser do cliente.</p>
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
                <h2 class="section-title">Uma plataforma.<br>Operação completa e site de vendas.</h2>
            </div>
            <p class="section-sub reveal-right delay-1">
                Cada módulo foi desenhado a partir de demandas reais de locadoras, incluindo o site público que vende, personaliza e converte reservas.
            </p>
        </div>

        <div class="swiper swiper-feature reveal" id="swiperFeature">
            <div class="swiper-wrapper">

                <!-- SLIDE 1 — Reservas -->
                <div class="swiper-slide">
                    <div class="feature-slide">
                        <div class="feature-slide-content">
                            <div class="feature-slide-tag"><i class="fas fa-calendar-check"></i> Reservas</div>
                            <h3>Controle total da agenda de locação</h3>
                            <p>Receba reservas, valide disponibilidade em tempo real e gerencie contratos, tudo no mesmo lugar, sem conflito.</p>
                            <div class="feature-items">
                                <div class="feature-item"><i class="fas fa-check"></i> Calendário visual por veículo</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Reserva pelo próprio cliente (portal)</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Geração automática de contrato</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Alertas de entrega e devolução</div>
                            </div>
                        </div>
                        <div class="feature-slide-image">
                            {{-- <img src="{{ asset('img/screen-reservas.png') }}" alt="Reservas"> --}}
                            {{-- <div class="feature-placeholder">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Adicione print da tela de Reservas</span>
                            </div> --}}
                            <img src="{{ asset('assets/welcome/reservas.png') }}" alt="Tela de Reservas">
                        </div>
                    </div>
                </div>

                <!-- SLIDE 2 — Check-in -->
                <div class="swiper-slide">
                    <div class="feature-slide">
                        <div class="feature-slide-content">
                            <div class="feature-slide-tag"><i class="fas fa-clipboard-check"></i> Check-in Digital</div>
                            <h3>Vistoria com fotos direto no celular</h3>
                            <p>Registre o estado do veículo na saída e na devolução com fotos geolocalizadas. Sem papel, sem discussão.</p>
                            <div class="feature-items">
                                <div class="feature-item"><i class="fas fa-check"></i> Checklist configurável por categoria</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Fotos vinculadas ao contrato</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Assinatura digital do cliente</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Histórico de vistorias por veículo</div>
                            </div>
                        </div>
                        <div class="feature-slide-image">
                            {{-- <div class="feature-placeholder">
                                <i class="fas fa-mobile-alt"></i>
                                <span>Adicione print da tela de Check-in</span>
                            </div> --}}
                            <img src="{{ asset('assets/welcome/check-in.png') }}" alt="Tela de Check-in Digital">
                        </div>
                    </div>
                </div>

                <!-- SLIDE 3 — Frota -->
                <div class="swiper-slide">
                    <div class="feature-slide">
                        <div class="feature-slide-content">
                            <div class="feature-slide-tag"><i class="fas fa-car"></i> Gestão de Frota</div>
                            <h3>Visão 360° de cada veículo da frota</h3>
                            <p>Saiba em tempo real quais veículos estão disponíveis, em locação, em manutenção ou com pendências.</p>
                            <div class="feature-items">
                                <div class="feature-item"><i class="fas fa-check"></i> Categorias e grupos de veículos</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Histórico de manutenções</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Alertas de revisão e IPVA</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Rentabilidade por veículo</div>
                            </div>
                        </div>
                        <div class="feature-slide-image">
                            {{-- <div class="feature-placeholder">
                                <i class="fas fa-car-side"></i>
                                <span>Adicione print da tela de Frota</span>
                            </div> --}}
                            <img src="{{ asset('assets/welcome/frota.png') }}" alt="Tela de Frota">
                        </div>
                    </div>
                </div>

                <!-- SLIDE 4 — Financeiro -->
                <div class="swiper-slide">
                    <div class="feature-slide">
                        <div class="feature-slide-content">
                            <div class="feature-slide-tag"><i class="fas fa-chart-line"></i> Financeiro</div>
                            <h3>Do recebimento ao fechamento mensal</h3>
                            <p>Controle recebimentos via PIX, boleto e cartão, acompanhe inadimplências e exporte relatórios com um clique.</p>
                            <div class="feature-items">
                                <div class="feature-item"><i class="fas fa-check"></i> Integração com Stripe / PIX</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Contas a receber e a pagar</div>
                                <div class="feature-item"><i class="fas fa-check"></i> DRE e fluxo de caixa</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Emissão de NFS-e automática</div>
                            </div>
                        </div>
                        <div class="feature-slide-image">
                            {{-- <div class="feature-placeholder">
                                <i class="fas fa-chart-bar"></i>
                                <span>Adicione print da tela Financeiro</span>
                            </div> --}}
                            <img src="{{ asset('assets/welcome/financeiro.png') }}" alt="Tela de Financeiro">
                        </div>
                    </div>
                </div>

                <!-- SLIDE 5 — Site de Vendas -->
                <div class="swiper-slide">
                    <div class="feature-slide">
                        <div class="feature-slide-content">
                            <div class="feature-slide-tag"><i class="fas fa-store"></i> Site de Vendas</div>
                            <h3>Escolha um modelo e personalize sua vitrine online</h3>
                            <p>Sua locadora ganha um site de reservas com modelos visuais diferentes, onde a empresa pode ajustar cores, textos e imagens para vender mais e reforçar a própria marca.</p>
                            <div class="feature-items">
                                <div class="feature-item"><i class="fas fa-check"></i> Vários modelos de apresentação</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Cores, textos e imagens editáveis</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Site público para reservas online</div>
                                <div class="feature-item"><i class="fas fa-check"></i> Identidade visual da própria locadora</div>
                            </div>
                        </div>
                        <div class="feature-slide-image">
                            <img src="{{ asset('assets/welcome/site-aluguel.png') }}" alt="Site de reservas personalizado da locadora">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="swiper-nav">
            <button class="swiper-btn" id="featPrev"><i class="fas fa-arrow-left"></i></button>
            <div class="swiper-progress"><div class="swiper-progress-bar" id="featProgress"></div></div>
            <div class="swiper-count" id="featCount">1 / 4</div>
            <button class="swiper-btn" id="featNext"><i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
</section>

<!-- ══ MODULES ══ -->
<section class="modules" id="modulos">
    <div class="container" style="position:relative;z-index:1">
        <div class="section-header" style="text-align:center">
            <div class="section-tag reveal" style="justify-content:center">Módulos</div>
            <h2 class="section-title reveal delay-1" style="margin:0 auto .9rem">Tudo que sua locadora precisa</h2>
            <p class="section-sub reveal delay-2" style="margin:0 auto;color:rgba(255,255,255,.55)">9 módulos integrados, sem necessidade de sistemas terceiros para a operação.</p>
        </div>
        <div class="modules-grid">
            <div class="module-card reveal delay-1">
                <div class="mod-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fas fa-calendar-check"></i></div>
                <h4>Reservas</h4>
                <p>Agenda inteligente, sem conflito, com confirmação automática.</p>
            </div>
            <div class="module-card reveal delay-2">
                <div class="mod-icon" style="background:rgba(52,211,153,.12);color:#34d399"><i class="fas fa-car-side"></i></div>
                <h4>Frota</h4>
                <p>Cadastro completo, categorias, documentação e status operacional.</p>
            </div>
            <div class="module-card reveal delay-3">
                <div class="mod-icon" style="background:rgba(251,191,36,.12);color:#fbbf24"><i class="fas fa-clipboard-list"></i></div>
                <h4>Check-in / Check-out</h4>
                <p>Vistoria digital com fotos e assinatura vinculadas ao contrato.</p>
            </div>
            <div class="module-card reveal delay-4">
                <div class="mod-icon" style="background:rgba(248,113,113,.12);color:#f87171"><i class="fas fa-chart-line"></i></div>
                <h4>Financeiro</h4>
                <p>Recebimentos, cobranças, DRE e fluxo de caixa em tempo real.</p>
            </div>
            <div class="module-card reveal delay-1">
                <div class="mod-icon" style="background:rgba(192,132,252,.12);color:#c084fc"><i class="fas fa-file-invoice"></i></div>
                <h4>NFS-e</h4>
                <p>Emissão automática de nota fiscal integrada ao financeiro.</p>
            </div>
            <div class="module-card reveal delay-2">
                <div class="mod-icon" style="background:rgba(251,146,60,.12);color:#fb923c"><i class="fas fa-tools"></i></div>
                <h4>Manutenção</h4>
                <p>Ordens de serviço, agendamento preventivo e histórico por veículo.</p>
            </div>
            <div class="module-card reveal delay-3">
                <div class="mod-icon" style="background:rgba(244,114,182,.12);color:#f472b6"><i class="fas fa-traffic-light"></i></div>
                <h4>Multas</h4>
                <p>Rastreamento e imputação automática ao locatário responsável.</p>
            </div>
            <div class="module-card reveal delay-4">
                <div class="mod-icon" style="background:rgba(56,189,248,.12);color:#38bdf8"><i class="fas fa-users"></i></div>
                <h4>Usuários e Perfis</h4>
                <p>Controle de acesso por perfil: gerente, atendente, financeiro e mais.</p>
            </div>
            <div class="module-card reveal delay-1">
                <div class="mod-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9"><i class="fas fa-tachometer-alt"></i></div>
                <h4>Dashboard</h4>
                <p>Visão executiva: reservas, faturamento, frota e alertas em um painel.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══ STATS ══ -->
{{-- <section class="stats" id="numeros">
    <div class="container">
        <div class="section-header" style="text-align:center;margin-bottom:3rem">
            <div class="section-tag reveal" style="justify-content:center">Números</div>
            <h2 class="section-title reveal delay-1" style="margin:0 auto">DeCars em números</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-item reveal delay-1">
                <div class="stat-icon"><i class="fas fa-building"></i></div>
                <div class="stat-number" data-target="200">0<span>+</span></div>
                <div class="stat-label">Locadoras ativas</div>
            </div>
            <div class="stat-item reveal delay-2">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-number" data-target="40000">0<span>+</span></div>
                <div class="stat-label">Reservas processadas</div>
            </div>
            <div class="stat-item reveal delay-3">
                <div class="stat-icon"><i class="fas fa-car"></i></div>
                <div class="stat-number" data-target="18000">0<span>+</span></div>
                <div class="stat-label">Veículos gerenciados</div>
            </div>
            <div class="stat-item reveal delay-4">
                <div class="stat-icon"><i class="fas fa-server"></i></div>
                <div class="stat-number" data-target="99">0<span>%</span></div>
                <div class="stat-label">Uptime garantido</div>
            </div>
        </div>
    </div>
</section> --}}

<!-- ══ TESTIMONIALS ══ -->
{{-- <section class="testimonials" id="depoimentos">
    <div class="container">
        <div class="testimonials-inner">
            <div class="reveal-left">
                <div class="section-tag">Depoimentos</div>
                <h2 class="section-title">O que dizem nossos clientes</h2>
                <p class="section-sub">Quem usa o DeCars não volta a controlar locação em planilha.</p>
                <div class="testi-nav">
                    <button class="testi-btn" id="testiPrev"><i class="fas fa-arrow-left"></i></button>
                    <button class="testi-btn" id="testiNext"><i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
            <div class="reveal-right delay-1">
                <div class="swiper swiper-testimonial" id="swiperTesti">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="testi-card">
                                <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                <p class="testi-text">"Antes do DeCars a gente perdia em média 3 reservas por semana por conflito de agenda. Hoje isso não acontece mais. O sistema é simples para a equipe e completo para o gestor."</p>
                                <div class="testi-author">
                                    <div class="testi-avatar">MA</div>
                                    <div><div class="testi-name">Marcos Alves</div><div class="testi-role">Diretor — Auto Premium Recife</div></div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="testi-card">
                                <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                <p class="testi-text">"O módulo de check-in digital acabou com as discussões na devolução. O cliente assina na hora, as fotos ficam salvas e não tem como alguém negar um arranhão que já estava lá."</p>
                                <div class="testi-author">
                                    <div class="testi-avatar">JF</div>
                                    <div><div class="testi-name">Juliana Ferreira</div><div class="testi-role">Proprietária — FlexRent Brasília</div></div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="testi-card">
                                <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                <p class="testi-text">"Fechei o mês passado em 15 minutos. Antes eu levava uma semana para fechar o financeiro. O DRE sai automático e o fluxo de caixa é atualizado em tempo real."</p>
                                <div class="testi-author">
                                    <div class="testi-avatar">RO</div>
                                    <div><div class="testi-name">Roberto Oliveira</div><div class="testi-role">Sócio-Gerente — Locamax SP</div></div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="testi-card">
                                <div class="testi-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                <p class="testi-text">"A migração foi tranquila, a equipe de suporte acompanhou tudo. Em dois dias minha equipe já estava operando normalmente. Faturamos 18% a mais no trimestre seguinte."</p>
                                <div class="testi-author">
                                    <div class="testi-avatar">CL</div>
                                    <div><div class="testi-name">Cláudia Lemos</div><div class="testi-role">CEO — Rent Solutions Manaus</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> --}}

<!-- ══ PRICING ══ -->
<section class="pricing" id="planos">
    <div class="container">
        <div class="section-header" style="text-align:center">
            <div class="section-tag reveal" style="justify-content:center">Planos</div>
            <h2 class="section-title reveal delay-1" style="margin:0 auto .9rem">Investimento justo para cada tamanho de locadora</h2>
            <p class="section-sub reveal delay-2" style="margin:0 auto">Além do sistema administrativo, sua locadora também ganha um site de reservas para vender online e facilitar o aluguel dos veículos.</p>
        </div>
        <div class="pricing-highlight reveal delay-1">
            <div class="pricing-highlight-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div>
                <h3>Seu sistema também vira vitrine de vendas</h3>
                <p>Com o DeCars, a locadora não fica só no controle interno. Você pode oferecer um site público para clientes pesquisarem veículos, simularem aluguel e fazerem reservas pela internet.</p>
            </div>
            <a href="#funcionalidades" class="pricing-highlight-link">Ver como funciona</a>
        </div>
        <div class="pricing-toggle reveal delay-1">
            <span id="lblMonthly" class="active">Mensal</span>
            <button class="toggle-switch" id="pricingToggle" aria-label="Alternar período"></button>
            <span id="lblAnnual">Anual <span class="badge-save">-20%</span></span>
        </div>
        <div class="pricing-grid">
            <div class="plan-card reveal delay-1">
                <div class="plan-name">Gestão</div>
                <div class="plan-price">
                    <span class="currency">R$</span>
                    <span class="amount" data-monthly="247" data-annual="197">247</span>
                    <span class="period">/mês</span>
                    <span class="annual-note" style="display:none">Cobrado anualmente, economia de R$600/ano</span>
                </div>
                <p class="plan-desc">Para quem quer começar com o sistema administrativo e deixar o site de vendas para uma próxima etapa.</p>
                <ul class="plan-features">
                    <li class="plan-feature"><i class="fas fa-check"></i> Gestão da frota</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Reservas e contratos</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Check-in digital</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Financeiro básico</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Sem site de vendas</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Suporte via chat</li>
                </ul>
                <a href="https://wa.me/558192554172?text={{ urlencode('Olá! Gostei do plano Gestão e queria conhecer melhor o sistema.') }}" class="btn-plan btn-plan-ghost" target="_blank" rel="noopener">Começar sem site</a>
            </div>
            <div class="plan-card reveal delay-1">
                <div class="plan-name">Essencial</div>
                <div class="plan-price">
                    <span class="currency">R$</span>
                    <span class="amount" data-monthly="397" data-annual="317">397</span>
                    <span class="period">/mês</span>
                    <span class="annual-note" style="display:none">Cobrado anualmente, economia de R$960/ano</span>
                </div>
                <p class="plan-desc">Para locadoras que querem começar com presença digital profissional e reservas online no mesmo pacote.</p>
                <ul class="plan-features">
                    <li class="plan-feature"><i class="fas fa-check"></i> Até 20 veículos</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Reservas e contratos</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Check-in digital</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Financeiro básico</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Site de vendas com 1 modelo</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Personalização de cores e textos</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Suporte via chat</li>
                </ul>
                <a href="https://wa.me/558192554172?text={{ urlencode('Olá! Gostei do plano Essencial e queria conhecer melhor o sistema.') }}" class="btn-plan btn-plan-ghost" target="_blank" rel="noopener">Começar agora</a>
            </div>
            <div class="plan-card popular reveal delay-2">
                <div class="plan-badge">Mais escolhido</div>
                <div class="plan-name">Crescimento</div>
                <div class="plan-price">
                    <span class="currency">R$</span>
                    <span class="amount" data-monthly="697" data-annual="557">697</span>
                    <span class="period">/mês</span>
                    <span class="annual-note" style="display:none">Cobrado anualmente, economia de R$1.680/ano</span>
                </div>
                <p class="plan-desc">Para locadoras que querem vender mais, acelerar reservas e ter um site com cara de marca forte.</p>
                <ul class="plan-features">
                    <li class="plan-feature"><i class="fas fa-check"></i> Até 80 veículos</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Tudo do Essencial</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Módulo de multas</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> NFS-e automática</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Portal de reservas para clientes</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Até 3 modelos de site</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Customização completa de branding</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Multi-estação / filiais</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Relatórios avançados</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Suporte prioritário</li>
                </ul>
                <a href="https://wa.me/558192554172?text={{ urlencode('Olá! Gostei do plano Crescimento e queria conhecer melhor o sistema.') }}" class="btn-plan btn-plan-primary" target="_blank" rel="noopener">Começar agora</a>
            </div>
            <div class="plan-card reveal delay-3">
                <div class="plan-name">Escala</div>
                <div class="plan-price">
                    <span class="amount" data-monthly="1297" data-annual="1037" style="font-size:2rem;color:var(--muted)">1297</span>
                    <span class="period">/mês</span>
                    <span class="annual-note" style="display:none">Cobrado anualmente, economia de R$3.120/ano</span>
                </div>
                <p class="plan-desc">Para operações que precisam de uma vitrine white-label, integrações e atendimento dedicado.</p>
                <ul class="plan-features">
                    <li class="plan-feature"><i class="fas fa-check"></i> Frota ilimitada</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Tudo do Crescimento</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> API de integração</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Site white-label de reservas</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Customizações</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> Gerente de conta dedicado</li>
                    <li class="plan-feature"><i class="fas fa-check"></i> SLA 99,9% garantido</li>
                </ul>
                <a href="#" class="btn-plan btn-plan-ghost">Falar com consultor</a>
            </div>
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
        <h2 class="section-title" style="margin:0 auto 1rem">Transforme a operação da<br>sua locadora hoje</h2>
        <p class="section-sub" style="text-align:center;margin:0 auto 2.5rem">Implantação em até 48 horas. Sem taxa de setup. Suporte incluso desde o primeiro dia.</p>
        <div class="cta-actions">
            <a href="#planos" class="btn-cta-white">Ver planos</a>
            <a href="https://wa.me/558192554172" class="btn-whatsapp" target="_blank" rel="noopener">
                <i class="fab fa-whatsapp"></i> Falar com especialista
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
                       <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="">
                    </div>
                   DECARS
                </div>
                <p class="footer-tagline">Software completo para gestão de locadoras de veículos. Simples para a equipe, poderoso para o gestor.</p>
            </div>
            <div class="footer-col">
                <h5>Produto</h5>
                <ul>
                    <li><a href="#funcionalidades">Funcionalidades</a></li>
                    <li><a href="#modulos">Módulos</a></li>
                    <li><a href="#planos">Planos</a></li>
                    {{-- <li><a href="#numeros">Números</a></li> --}}
                </ul>
            </div>
            {{-- <div class="footer-col">
                <h5>Empresa</h5>
                <ul>
                    <li><a href="#">Sobre nós</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Cases</a></li>
                    <li><a href="#">Contato</a></li>
                </ul>
            </div> --}}
            <div class="footer-col">
                <h5>Suporte</h5>
                <ul>
                    {{-- <li><a href="#">Central de ajuda</a></li>
                    <li><a href="#">Documentação</a></li>
                    <li><a href="#">Status do sistema</a></li> --}}
                    <li><a href="{{ route('login') }}">Entrar no sistema</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} DeCars. Todos os direitos reservados.</p>
            <a class="footer-brand-credit" href="https://www.sonyd.com.br" target="_blank" rel="noopener noreferrer" aria-label="Visitar o site da Sonyd Tech">
                <span>Software oficial da</span>
                <img src="{{ asset('assets/imgs/logo-sonydtech.webp') }}" alt="Logo Sonyd Tech">
            </a>
            {{-- <div class="footer-socials">
                <a href="#" class="social-btn" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-btn" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="social-btn" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div> --}}
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
    const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); }
        });
    }, { threshold: 0.1 });
    revealEls.forEach(el => io.observe(el));

    /* ── Hero Carousel ── */
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
            }, 1800);
        };

        const stopHeroTimer = () => {
            if (heroTimer) {
                clearInterval(heroTimer);
                heroTimer = null;
            }
        };

        heroCarousel.addEventListener('mouseenter', stopHeroTimer);
        heroCarousel.addEventListener('mouseleave', startHeroTimer);
        startHeroTimer();
    }

    /* ── Typewriter ── */
    const target = document.getElementById('typewriterTarget');
    const cursor = document.getElementById('typewriterCursor');
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

    /* ── Counter animation ── */
    function formatNum(n, target) {
        if (target >= 10000) return (n / 1000).toFixed(0) + 'k';
        return n.toLocaleString('pt-BR');
    }
    const counters = document.querySelectorAll('.stat-number[data-target]');
    const counterIO = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const el = e.target;
            const tgt = +el.dataset.target;
            const suffix = el.querySelector('span') ? el.querySelector('span').outerHTML : '';
            const duration = 1800;
            const step = 16;
            const steps = duration / step;
            let current = 0;
            const inc = tgt / steps;
            el.innerHTML = '0' + suffix;
            const timer = setInterval(() => {
                current = Math.min(current + inc, tgt);
                el.innerHTML = formatNum(Math.floor(current), tgt) + suffix;
                if (current >= tgt) clearInterval(timer);
            }, step);
            counterIO.unobserve(el);
        });
    }, { threshold: 0.5 });
    counters.forEach(el => counterIO.observe(el));

    /* ── Features Swiper ── */
    const featSwiper = new Swiper('#swiperFeature', {
        slidesPerView: 1,
        spaceBetween: 0,
        speed: 650,
        grabCursor: true,
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

    /* ── Testimonials Swiper ── */
    new Swiper('#swiperTesti', {
        slidesPerView: 1,
        spaceBetween: 24,
        speed: 600,
        grabCursor: true,
        autoplay: { delay: 5000, disableOnInteraction: true },
        navigation: { prevEl: '#testiPrev', nextEl: '#testiNext' },
        breakpoints: { 768: { slidesPerView: 1.3 }, 1024: { slidesPerView: 1.5 } }
    });

    /* ── Pricing toggle ── */
    let annual = false;
    document.getElementById('pricingToggle').addEventListener('click', function () {
        annual = !annual;
        this.classList.toggle('annual', annual);
        document.getElementById('lblMonthly').classList.toggle('active', !annual);
        document.getElementById('lblAnnual').classList.toggle('active', annual);
        document.querySelectorAll('.plan-price .amount').forEach(el => {
            const v = annual ? el.dataset.annual : el.dataset.monthly;
            if (v) el.textContent = v;
        });
        document.querySelectorAll('.annual-note').forEach(el => {
            el.style.display = annual ? 'block' : 'none';
        });
    });

    /* ── Card tilt hover ── */
    document.querySelectorAll('.problem-card, .stat-item, .testi-card').forEach(card => {
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
