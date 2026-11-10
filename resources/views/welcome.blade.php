<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DECLASS, Gestão Escolar Completa para Escolas e Redes de Ensino</title>
    <meta name="description" content="O Declass reúne matrículas, diário de classe, avaliações, boletim, financeiro, biblioteca, transporte e portal da família em uma única plataforma SaaS multi-tenant.">
    <link rel="icon" type="image/png" href="{{ asset('assets/imgs/logo-sem-texto.png') }}">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root{
            --navy:#0b1530; --navy-2:#101d42; --ink:#0f172a; --muted:#64748b;
            --accent:#f59e0b; --accent-dark:#d97706; --blue:#1e3a8a; --indigo:#312e81;
            --bg:#f8fafc; --card:#fff; --line:#e2e8f0; --radius:16px;
        }
        *{margin:0;padding:0;box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{font-family:'Figtree',sans-serif;background:var(--bg);color:var(--ink);line-height:1.6}
        img{max-width:100%}
        a{text-decoration:none;color:inherit}
        .wrap{max-width:1180px;margin:0 auto;padding:0 1.25rem}
        section{padding:5rem 0}
        .eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-size:.8rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--accent-dark);background:#fef3c7;padding:.4rem .9rem;border-radius:999px;margin-bottom:1rem}
        h2{font-size:clamp(1.7rem,3.5vw,2.6rem);font-weight:800;line-height:1.15;letter-spacing:-.02em}
        h2 .hl{color:var(--blue)}
        .lead{color:var(--muted);font-size:1.08rem;max-width:640px;margin-top:1rem}
        .center{text-align:center}
        .center .lead{margin-left:auto;margin-right:auto}

        /* ---------- NAV ---------- */
        header{position:sticky;top:0;z-index:50;background:rgba(11,21,48,.92);backdrop-filter:blur(12px);border-bottom:1px solid rgba(255,255,255,.08)}
        .nav{display:flex;align-items:center;justify-content:space-between;height:68px}
        .brand{display:flex;align-items:center;gap:.65rem;font-weight:800;font-size:1.15rem;color:#fff;letter-spacing:-.01em}
        .brand img{height:34px;width:auto}
        .nav-links{display:flex;align-items:center;gap:1.6rem}
        .nav-links a.nl{color:#cbd5e1;font-size:.92rem;font-weight:500;transition:color .15s}
        .nav-links a.nl:hover{color:#fff}
        .nav-cta{display:flex;align-items:center;gap:.7rem}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.72rem 1.5rem;border-radius:11px;font-weight:700;font-size:.92rem;transition:transform .15s,box-shadow .15s,background .15s;border:0;cursor:pointer}
        .btn:hover{transform:translateY(-1px)}
        .btn-amber{background:var(--accent);color:#1e293b;box-shadow:0 8px 20px rgba(245,158,11,.35)}
        .btn-amber:hover{background:var(--accent-dark);color:#fff}
        .btn-ghost-light{border:1.5px solid rgba(255,255,255,.35);color:#fff}
        .btn-ghost-light:hover{border-color:#fff;background:rgba(255,255,255,.08)}
        .btn-blue{background:var(--blue);color:#fff}
        .btn-blue:hover{background:var(--indigo)}
        .btn-outline{border:1.5px solid var(--line);color:var(--ink);background:#fff}
        .btn-outline:hover{border-color:var(--blue);color:var(--blue)}
        .btn-lg{padding:.95rem 2rem;font-size:1rem;border-radius:13px}
        .btn-sm{padding:.55rem 1.1rem;font-size:.85rem;border-radius:9px}
        .nav-toggle{display:none;background:none;border:0;color:#fff;font-size:1.3rem;cursor:pointer}

        /* ---------- HERO ---------- */
        .hero{background:radial-gradient(1200px 600px at 80% -10%,rgba(49,46,129,.55),transparent 60%),radial-gradient(900px 500px at 10% 110%,rgba(30,58,138,.5),transparent 60%),var(--navy);color:#fff;padding:5.5rem 0 0;overflow:hidden}
        .hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:3.5rem;align-items:center}
        .hero h1{font-size:clamp(2.2rem,4.6vw,3.4rem);font-weight:800;line-height:1.08;letter-spacing:-.03em}
        .hero h1 .hl{color:var(--accent)}
        .hero .sub{color:#b6c2dd;font-size:1.12rem;max-width:520px;margin:1.4rem 0 2rem}
        .hero-ctas{display:flex;gap:.8rem;flex-wrap:wrap}
        .trust{display:flex;gap:1.8rem;flex-wrap:wrap;margin-top:2.2rem}
        .trust span{display:inline-flex;align-items:center;gap:.5rem;font-size:.85rem;color:#94a3b8}
        .trust i{color:var(--accent)}

        /* mockup dashboard */
        .mock{position:relative}
        .mock::before{content:'';position:absolute;inset:-30px;background:radial-gradient(closest-side,rgba(245,158,11,.16),transparent);z-index:0}
        .browser{position:relative;z-index:1;background:#0d1830;border:1px solid rgba(255,255,255,.12);border-radius:16px;overflow:hidden;box-shadow:0 40px 80px rgba(0,0,0,.45)}
        .browser-bar{display:flex;align-items:center;gap:.5rem;padding:.7rem 1rem;background:#111f42;border-bottom:1px solid rgba(255,255,255,.08)}
        .dot{width:10px;height:10px;border-radius:50%}
        .dot.r{background:#f87171}.dot.y{background:#fbbf24}.dot.g{background:#34d399}
        .browser-bar .url{margin-left:.6rem;background:rgba(255,255,255,.07);border-radius:7px;padding:.25rem .8rem;font-size:.72rem;color:#94a3b8;flex:1}
        .dash{display:grid;grid-template-columns:44px 1fr;background:#f1f5f9;min-height:340px}
        .dash-side{background:var(--navy-2);padding:.8rem .6rem;display:flex;flex-direction:column;gap:.55rem;align-items:center}
        .dash-side i{color:#64748b;font-size:.8rem}
        .dash-side i.on{color:var(--accent)}
        .dash-main{padding:.9rem;display:flex;flex-direction:column;gap:.7rem}
        .dash-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:.55rem}
        .dcard{background:#fff;border-radius:9px;padding:.55rem .7rem;border:1px solid #e2e8f0}
        .dcard .t{font-size:.58rem;color:#94a3b8;text-transform:uppercase;font-weight:700}
        .dcard .v{font-size:.95rem;font-weight:800;color:var(--ink)}
        .dcard .d{font-size:.6rem;font-weight:700}
        .dcard .d.up{color:#059669}.dcard .d.down{color:#dc2626}
        .dash-chart{flex:1;background:#fff;border:1px solid #e2e8f0;border-radius:9px;padding:.6rem .8rem;display:flex;align-items:flex-end;gap:.45rem;min-height:110px}
        .bcol{flex:1;background:linear-gradient(180deg,#3b82f6,#1e3a8a);border-radius:4px 4px 0 0;opacity:.9}
        .bcol.alt{background:linear-gradient(180deg,#f59e0b,#d97706)}
        .dash-table{background:#fff;border:1px solid #e2e8f0;border-radius:9px;overflow:hidden}
        .dash-table div{display:flex;justify-content:space-between;padding:.42rem .7rem;font-size:.68rem;color:#475569;border-top:1px solid #f1f5f9}
        .dash-table div:first-child{border-top:0;font-weight:700;color:#94a3b8;text-transform:uppercase;font-size:.58rem}

        /* carrossel de telas reais */
        .car{position:relative;overflow:hidden;aspect-ratio:16/10;background:#0d1830}
        .car-slide{position:absolute;inset:0;opacity:0;transform:scale(1.04);transition:opacity .6s ease,transform .6s ease}
        .car-slide.on{opacity:1;transform:scale(1);z-index:1}
        .car-slide img{width:100%;height:100%;object-fit:cover;object-position:top;display:block}
        .car-cap{position:absolute;left:0;right:0;bottom:0;padding:.55rem .9rem;font-size:.72rem;color:#e2e8f0;background:linear-gradient(0deg,rgba(2,6,23,.85),transparent);z-index:2}
        .car-arrow{position:absolute;top:50%;transform:translateY(-50%);z-index:3;width:30px;height:30px;border-radius:50%;border:1px solid rgba(255,255,255,.25);background:rgba(13,24,48,.75);color:#e2e8f0;font-size:.75rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .2s}
        .car-arrow:hover{background:var(--accent);border-color:var(--accent);color:#0f172a}
        .car-prev{left:.6rem}.car-next{right:.6rem}
        .car-dots{position:absolute;bottom:.55rem;right:.9rem;z-index:3;display:flex;gap:.35rem}
        .car-dot{width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,.35);cursor:pointer;transition:background .2s,transform .2s}
        .car-dot.on{background:var(--accent);transform:scale(1.25)}

        /* screenshots reais */
        .shots{background:#0b1530;color:#fff}
        .shots .lead{color:#b6c2dd}
        .shots-grid{display:grid;grid-template-columns:1.7fr .7fr;gap:2.2rem;align-items:center;margin-top:2.8rem}
        .shot-browser{background:#0d1830;border:1px solid rgba(255,255,255,.12);border-radius:14px;overflow:hidden;box-shadow:0 30px 60px rgba(0,0,0,.5)}
        .shot-browser img{display:block;width:100%}
        .shot-caption{display:flex;align-items:center;gap:.5rem;padding:.7rem 1rem;font-size:.75rem;color:#94a3b8;border-top:1px solid rgba(255,255,255,.08)}
        .shot-caption i{color:var(--accent)}
        .shot-phone{background:#0f172a;border:7px solid #1e293b;border-radius:26px;overflow:hidden;box-shadow:0 30px 60px rgba(0,0,0,.5);position:relative}
        .shot-phone::before{content:'';position:absolute;top:8px;left:50%;transform:translateX(-50%);width:70px;height:14px;background:#1e293b;border-radius:8px;z-index:2}
        .shot-phone img{display:block;width:100%;padding-top:18px}
        .shots-row{display:grid;grid-template-columns:repeat(3,1fr);gap:1.2rem;margin-top:1.8rem}
        .shots-row .shot-caption{font-size:.68rem}
        @media(max-width:900px){.shots-grid{grid-template-columns:1fr}.shots-row{grid-template-columns:1fr}.shot-phone{max-width:280px;margin:0 auto}}
        .shot-browser img,.shot-phone img{cursor:zoom-in}
        .lightbox{position:fixed;inset:0;z-index:9999;background:rgba(2,6,23,.92);display:flex;align-items:center;justify-content:center;padding:2.5rem 1.5rem;cursor:zoom-out;opacity:0;pointer-events:none;transition:opacity .25s}
        .lightbox.on{opacity:1;pointer-events:auto}
        .lightbox img{max-width:100%;max-height:100%;border-radius:10px;box-shadow:0 30px 80px rgba(0,0,0,.6)}
        .lightbox .lb-close{position:absolute;top:1rem;right:1.4rem;color:#e2e8f0;font-size:2rem;background:none;border:0;cursor:pointer}
        .lightbox .lb-cap{position:absolute;bottom:1.2rem;left:50%;transform:translateX(-50%);color:#cbd5e1;font-size:.82rem}

        /* stats strip */
        .stats{background:#fff;border-bottom:1px solid var(--line)}
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;padding:2.2rem 1.25rem;text-align:center}
        .stat b{display:block;font-size:1.9rem;font-weight:800;color:var(--blue);letter-spacing:-.02em}
        .stat span{font-size:.85rem;color:var(--muted)}

        /* problema */
        .problem{background:var(--navy);color:#fff}
        .problem .lead{color:#b6c2dd}
        .pain-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.4rem;margin-top:2.8rem}
        .pain{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius);padding:1.8rem;transition:border-color .2s,transform .2s}
        .pain:hover{border-color:rgba(245,158,11,.5);transform:translateY(-3px)}
        .pain .ico{width:46px;height:46px;border-radius:12px;background:rgba(245,158,11,.14);color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:1.1rem}
        .pain h3{font-size:1.08rem;margin-bottom:.5rem}
        .pain p{font-size:.88rem;color:#a8b6d4}

        /* funcionalidades */
        .feat{background:#fff}
        .feat-row{display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;margin-top:3.2rem}
        .feat-row.flip .feat-txt{order:2}
        .feat-row.flip .feat-mock{order:1}
        .feat-txt h3{font-size:1.45rem;font-weight:800;letter-spacing:-.02em;margin-bottom:.8rem}
        .feat-txt p{color:var(--muted);margin-bottom:1.2rem}
        .feat-list{list-style:none;display:grid;gap:.65rem}
        .feat-list li{display:flex;gap:.7rem;align-items:flex-start;font-size:.92rem;color:#334155}
        .feat-list li i{color:#059669;margin-top:.2rem;font-size:.85rem}
        .mini-mock{background:#fff;border:1px solid var(--line);border-radius:14px;box-shadow:0 24px 50px rgba(15,23,42,.1);overflow:hidden}
        .mini-mock .mm-bar{display:flex;gap:.4rem;padding:.6rem .9rem;border-bottom:1px solid var(--line);background:#f8fafc}
        .mini-mock .mm-bar span{width:8px;height:8px;border-radius:50%;background:#cbd5e1}
        .mini-mock .mm-body{padding:1rem;display:flex;flex-direction:column;gap:.6rem}
        .mini-mock .mm-img{display:block;width:100%;aspect-ratio:16/9;object-fit:cover;object-position:top}
        .mm-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem}
        .mm-card{border:1px solid var(--line);border-radius:9px;padding:.6rem}
        .mm-card .l{font-size:.58rem;color:#94a3b8;font-weight:700;text-transform:uppercase}
        .mm-card .v{font-size:1rem;font-weight:800}
        .mm-lines{display:flex;flex-direction:column;gap:.45rem}
        .mm-line{height:10px;border-radius:5px;background:#eef2f7}
        .mm-line.short{width:55%}
        .mm-line.mid{width:75%}
        .mm-line.accent{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
        .mm-line.blue{background:linear-gradient(90deg,#1e3a8a,#3b82f6)}
        .mm-grid2{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
        .mm-badge{display:inline-block;background:#ecfdf5;color:#047857;font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:999px}
        .mm-badge.warn{background:#fef3c7;color:#b45309}
        .mm-badge.info{background:#eff6ff;color:#1d4ed8}

        /* módulos */
        .modules{background:#fff}
        .mod-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-top:2.8rem}
        .mod{border:1px solid var(--line);border-radius:14px;padding:1.4rem;transition:transform .2s,box-shadow .2s,border-color .2s;background:#fff}
        .mod:hover{transform:translateY(-4px);box-shadow:0 18px 40px rgba(15,23,42,.1);border-color:#c7d2fe}
        .mod .ico{width:42px;height:42px;border-radius:11px;background:linear-gradient(135deg,#eff6ff,#e0e7ff);color:var(--blue);display:flex;align-items:center;justify-content:center;font-size:1rem;margin-bottom:.9rem}
        .mod h3{font-size:.98rem;font-weight:700;margin-bottom:.35rem}
        .mod p{font-size:.82rem;color:var(--muted)}

        /* planos */
        .plans{background:var(--navy);color:#fff}
        .plans .lead{color:#b6c2dd}
        .plan-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.4rem;margin-top:3rem;align-items:stretch;max-width:900px;margin-left:auto;margin-right:auto}
        .plan-tabela{margin:1rem 0 .4rem;border:1px solid rgba(255,255,255,.14);border-radius:10px;overflow:hidden;font-size:.82rem}
        .plan-tabela .pt-row{display:flex;justify-content:space-between;padding:.5rem .9rem;border-top:1px solid rgba(255,255,255,.08);color:#cbd5e1}
        .plan-tabela .pt-row:first-child{border-top:0;background:rgba(255,255,255,.05);font-weight:700;color:#94a3b8;text-transform:uppercase;font-size:.68rem;letter-spacing:.04em}
        .plan-tabela .pt-row b{color:#fff;font-weight:700}
        .plan-tabela .pt-row .consulta{color:#fbbf24;font-weight:700}
        .plan{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:2rem;display:flex;flex-direction:column;transition:border-color .2s,transform .2s}
        .plan:hover{border-color:rgba(255,255,255,.3);transform:translateY(-4px)}
        .plan.hot{background:linear-gradient(160deg,rgba(245,158,11,.14),rgba(255,255,255,.04));border-color:rgba(245,158,11,.55);position:relative}
        .plan .tag{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--accent);color:#1e293b;font-size:.68rem;font-weight:800;padding:.3rem .9rem;border-radius:999px;white-space:nowrap}
        .plan h3{font-size:1.15rem}
        .plan .price{margin:1rem 0 .3rem}
        .plan .price b{font-size:1.9rem;font-weight:800}
        .plan .price span{color:#94a3b8;font-size:.85rem}
        .plan ul{list-style:none;margin:1.2rem 0 1.6rem;display:grid;gap:.6rem;flex:1}
        .plan ul li{display:flex;gap:.6rem;font-size:.87rem;color:#cbd5e1;align-items:flex-start}
        .plan ul li i{color:var(--accent);margin-top:.2rem;font-size:.75rem}
        .plan .btn{width:100%}
        .plan-note{text-align:center;color:#94a3b8;font-size:.82rem;margin-top:1.6rem}
        .plan-extra{display:flex;flex-wrap:wrap;justify-content:center;gap:1rem 2.4rem;margin-top:2.2rem;padding:1.1rem 1.5rem;border:1px solid rgba(255,255,255,.14);border-radius:14px;background:rgba(255,255,255,.04);font-size:.85rem;color:#cbd5e1}
        .plan-extra i{color:var(--accent);margin-right:.45rem}
        .plan-extra em{color:#e2e8f0;font-style:normal;font-weight:600}
        .plan-demo-link{display:inline-flex;align-items:center;gap:.3rem;margin-left:.4rem;font-size:.72rem;font-weight:700;color:#fbbf24;background:rgba(245,158,11,.14);border:1px solid rgba(245,158,11,.4);border-radius:999px;padding:.15rem .6rem;text-decoration:none;transition:background .2s}
        .plan-demo-link:hover{background:rgba(245,158,11,.28);color:#fde68a}
        .plan-demo-link i{font-size:.6rem}

        /* FAQ */
        .faq details{border:1px solid var(--line);border-radius:12px;background:#fff;margin-bottom:.8rem;overflow:hidden;transition:border-color .2s}
        .faq details[open]{border-color:#c7d2fe}
        .faq summary{cursor:pointer;list-style:none;padding:1.15rem 1.4rem;font-weight:700;font-size:.98rem;display:flex;justify-content:space-between;align-items:center;gap:1rem}
        .faq summary::-webkit-details-marker{display:none}
        .faq summary::after{content:'\f078';font-family:'Font Awesome 6 Free';font-weight:900;font-size:.75rem;color:var(--muted);transition:transform .25s}
        .faq details[open] summary::after{transform:rotate(180deg)}
        .faq .ans{padding:0 1.4rem 1.2rem;color:var(--muted);font-size:.92rem}
        .faq .ans a{color:var(--blue);font-weight:600}

        /* CTA final */
        .final{background:linear-gradient(135deg,var(--blue),var(--indigo));color:#fff;text-align:center;position:relative;overflow:hidden}
        .final::before{content:'';position:absolute;width:600px;height:600px;border-radius:50%;background:rgba(245,158,11,.12);top:-300px;right:-150px}
        .final h2{position:relative}
        .final p{position:relative;color:#c7d2fe;margin:1.2rem auto 2rem;max-width:520px}
        .final .hero-ctas{justify-content:center;position:relative}

        /* footer */
        footer{background:#070d1f;color:#94a3b8;padding:4rem 0 2rem;font-size:.88rem}
        .foot-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:2.5rem;padding-bottom:2.5rem;border-bottom:1px solid rgba(255,255,255,.08)}
        .foot-grid .brand{color:#fff;margin-bottom:.9rem}
        .foot-grid p{font-size:.85rem;max-width:300px}
        footer h4{color:#fff;font-size:.8rem;text-transform:uppercase;letter-spacing:.08em;margin-bottom:1rem}
        footer ul{list-style:none;display:grid;gap:.55rem}
        footer ul a:hover{color:#fff}
        .foot-bottom{display:flex;justify-content:space-between;align-items:center;padding-top:1.8rem;font-size:.78rem;flex-wrap:wrap;gap:.8rem}

        /* responsivo */
        @media(max-width:980px){
            .hero-grid{grid-template-columns:1fr;gap:3rem}
            .feat-row{grid-template-columns:1fr;gap:2rem}
            .feat-row.flip .feat-txt{order:1}.feat-row.flip .feat-mock{order:2}
            .pain-grid,.mod-grid,.plan-grid{grid-template-columns:repeat(2,1fr)}
            .stats-grid{grid-template-columns:repeat(2,1fr)}
            .foot-grid{grid-template-columns:1fr 1fr}
        }
        @media(max-width:720px){
            .nav-links{display:none}
            .nav-toggle{display:block}
            .nav-links.open{display:flex;position:absolute;top:68px;left:0;right:0;background:var(--navy-2);flex-direction:column;padding:1.2rem;gap:1rem;border-bottom:1px solid rgba(255,255,255,.1)}
            .pain-grid,.mod-grid,.plan-grid,.stats-grid,.foot-grid{grid-template-columns:1fr}
            section{padding:3.5rem 0}
            .hero{padding-top:3.5rem}
        }
    </style>
</head>
<body>

{{-- ================= NAV ================= --}}
<header>
    <div class="wrap nav">
        <a href="/" class="brand">
            <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="DECLASS">
            DECLASS
        </a>
        <nav class="nav-links" id="navLinks">
            <a class="nl" href="#funcionalidades">Funcionalidades</a>
            <a class="nl" href="#modulos">Módulos</a>
            <a class="nl" href="#planos">Planos</a>
            <a class="nl" href="#sobre">Sobre</a>
            <a class="nl" href="#perguntas">Perguntas</a>
        </nav>
        <div class="nav-cta">
            <a href="{{ route('login') }}" class="btn btn-ghost-light btn-sm">Entrar</a>
            <a href="{{ route('cadastro.empresa.create') }}" class="btn btn-amber btn-sm">Começar agora</a>
            <button class="nav-toggle" onclick="document.getElementById('navLinks').classList.toggle('open')" aria-label="Menu">☰</button>
        </div>
    </div>
</header>

{{-- ================= HERO ================= --}}
<section class="hero">
    <div class="wrap hero-grid">
        <div>
            <span class="eyebrow" style="background:rgba(245,158,11,.15);color:#fbbf24"><i class="fas fa-graduation-cap"></i> Plataforma SaaS para escolas</span>
            <h1>Gestão completa da sua escola em <span class="hl">um só lugar</span></h1>
            <p class="sub">O Declass reúne matrículas, diário de classe, avaliações, boletim, financeiro, biblioteca, transporte e portal da família, do primeiro contato à emissão de certificados.</p>
            <div class="hero-ctas">
                <a href="{{ route('cadastro.empresa.create') }}" class="btn btn-amber btn-lg">Começar agora <i class="fas fa-arrow-right"></i></a>
                <a href="#modulos" class="btn btn-ghost-light btn-lg">Ver módulos</a>
            </div>
            <div class="trust">
                <span><i class="fas fa-check-circle"></i> Sem taxa de implantação</span>
                <span><i class="fas fa-check-circle"></i> Suporte incluso</span>
                <span><i class="fas fa-check-circle"></i> Dados na nuvem</span>
            </div>
        </div>

        <div class="mock">
            <div class="browser">
                <div class="browser-bar">
                    <span class="dot r"></span><span class="dot y"></span><span class="dot g"></span>
                    
                </div>
                <div class="car" id="heroCar">
                    @foreach([
                        ['desktop-secretaria-home.png', 'Painel da secretaria, fila de atendimento e resumo da escola'],
                        ['desktop-secretaria-turmas.png', 'Gestão de turmas e alunos'],
                        ['desktop-secretaria-avaliacao.png', 'Avaliações e lançamento de notas'],
                        ['desktop-secretaria-alunos.png', 'Cadastro e ficha completa do aluno'],
                        ['desktop-portal-home.png', 'Portal da família, notas, frequência e avisos'],
                    ] as $idx => [$img, $cap])
                        <div class="car-slide {{ $idx === 0 ? 'on' : '' }}">
                            <img src="{{ asset('img/mockups/'.$img.'?v=3') }}" alt="{{ $cap }}" loading="{{ $idx === 0 ? 'eager' : 'lazy' }}">
                            <div class="car-cap">{{ $cap }}</div>
                        </div>
                    @endforeach
                    <button type="button" class="car-arrow car-prev" aria-label="Anterior"><i class="fas fa-chevron-left"></i></button>
                    <button type="button" class="car-arrow car-next" aria-label="Próximo"><i class="fas fa-chevron-right"></i></button>
                    <div class="car-dots"></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ================= STATS ================= --}}
<div class="stats">
    <div class="wrap stats-grid">
        <div class="stat"><b>18+</b><span>módulos integrados</span></div>
        <div class="stat"><b>19</b><span>perfis de acesso prontos</span></div>
        <div class="stat"><b>1</b><span>login para toda a escola e família</span></div>
        <div class="stat"><b>100%</b><span>web, funciona em qualquer dispositivo</span></div>
    </div>
</div>

{{-- ================= PROBLEMA ================= --}}
<section class="problem" id="sobre">
    <div class="wrap center">
        <span class="eyebrow"><i class="fas fa-exclamation-triangle"></i> O problema</span>
        <h2>Sua escola ainda gerencia tudo em <span style="color:var(--accent)">planilha e grupo de WhatsApp?</span></h2>
        <p class="lead">Escolas perdem tempo e dinheiro todo dia com processos manuais. O Declass resolve na raiz.</p>
        <div class="pain-grid">
            <div class="pain">
                <div class="ico"><i class="fas fa-file-pen"></i></div>
                <h3>Matrícula e rematrícula no papel</h3>
                <p>Chamada duplicada, documentos perdidos e famílias sem retorno. Cada ano letivo vira uma correria de planilhas.</p>
            </div>
            <div class="pain">
                <div class="ico"><i class="fas fa-coins"></i></div>
                <p style="display:none"></p>
                <h3>Financeiro no escuro</h3>
                <p>Sem fluxo de caixa nem DRE, é impossível saber o faturamento real, a inadimplência e o custo por turma.</p>
            </div>
            <div class="pain">
                <div class="ico"><i class="fas fa-comments"></i></div>
                <h3>Família desinformada</h3>
                <p>Notas, faltas e avisos trocados em grupos que se perdem. Responsáveis ligam na secretaria para saber tudo.</p>
            </div>
        </div>
    </div>
</section>

{{-- ================= FUNCIONALIDADES ================= --}}
<section class="feat" id="funcionalidades">
    <div class="wrap">
        <div class="center">
            <span class="eyebrow"><i class="fas fa-th-large"></i> Funcionalidades</span>
            <h2>Uma plataforma. <span class="hl">Operação escolar completa.</span></h2>
            <p class="lead">Cada módulo foi desenhado a partir de demandas reais de escolas, da secretaria à sala de aula.</p>
        </div>

        <div class="feat-row">
            <div class="feat-txt">
                <h3>Matrículas sem papelada</h3>
                <p>Da solicitação à rematrícula em lote, todo o ciclo de matrícula digital.</p>
                <ul class="feat-list">
                    <li><i class="fas fa-check"></i> Formulário público de matrícula com fila de análise</li>
                    <li><i class="fas fa-check"></i> Aprovação gera aluno, matrícula e responsável automaticamente</li>
                    <li><i class="fas fa-check"></i> Rematrícula anual em lote com escolha de turma</li>
                    <li><i class="fas fa-check"></i> Número de chamada, plano de mensalidade e bolsa de estudo</li>
                </ul>
            </div>
            <div class="feat-mock">
                <div class="mini-mock">
                    <div class="mm-bar"><span></span><span></span><span></span></div>
                    <img src="{{ asset('img/mockups/desktop-secretaria-matricula.png?v=3') }}" alt="Solicitações de matrícula" loading="lazy" class="mm-img">
                </div>
            </div>
        </div>

        <div class="feat-row flip">
            <div class="feat-txt">
                <h3>Financeiro completo, do recebimento ao DRE</h3>
                <p>Mensalidades, bolsas, contas a pagar, fluxo de caixa e demonstrativo de resultados.</p>
                <ul class="feat-list">
                    <li><i class="fas fa-check"></i> Geração de mensalidades por competência com desconto de bolsa</li>
                    <li><i class="fas fa-check"></i> Contas a pagar com baixa e cancelamento</li>
                    <li><i class="fas fa-check"></i> Fluxo de caixa previsto × realizado por mês</li>
                    <li><i class="fas fa-check"></i> DRE por categoria, recibos em PDF e lembretes automáticos</li>
                </ul>
            </div>
            <div class="feat-mock">
                <div class="mini-mock">
                    <div class="mm-bar"><span></span><span></span><span></span></div>
                    <img src="{{ asset('img/mockups/desktop-secretaria-mensalidade.png?v=3') }}" alt="Gestão de mensalidades" loading="lazy" class="mm-img">
                </div>
            </div>
        </div>

        <div class="feat-row">
            <div class="feat-txt">
                <h3>Portal da família 24 horas</h3>
                <p>Responsáveis e alunos acompanham tudo pelo próprio login, sem ligar para a secretaria.</p>
                <ul class="feat-list">
                    <li><i class="fas fa-check"></i> Notas, frequência e boletim em PDF</li>
                    <li><i class="fas fa-check"></i> Mensalidades com recibo de pagamento</li>
                    <li><i class="fas fa-check"></i> Saúde, biblioteca, tarefas e pesquisas de satisfação</li>
                    <li><i class="fas fa-check"></i> Avisos e agenda da escola</li>
                </ul>
            </div>
            <div class="feat-mock">
                <div class="mini-mock">
                    <div class="mm-bar"><span></span><span></span><span></span></div>
                    <img src="{{ asset('img/mockups/desktop-portal-home.png?v=3') }}" alt="Portal da família" loading="lazy" class="mm-img">
                </div>
            </div>
        </div>

        <div class="feat-row flip">
            <div class="feat-txt">
                <h3>Cada pessoa vê só o que precisa</h3>
                <p>Secretaria, professores, famílias e gestão: cada um entra e vê sua rotina, sem complicação.</p>
                <ul class="feat-list">
                    <li><i class="fas fa-check"></i> Professor vê só as turmas que leciona</li>
                    <li><i class="fas fa-check"></i> Secretaria com fila de atendimento e ações rápidas</li>
                    <li><i class="fas fa-check"></i> Família acompanha notas, frequência e financeiro pelo portal</li>
                    <li><i class="fas fa-check"></i> Gestão enxerga tudo e controla permissões de cada pessoa</li>
                </ul>
            </div>
            <div class="feat-mock">
                <div class="mini-mock">
                    <div class="mm-bar"><span></span><span></span><span></span></div>
                    <img src="{{ asset('img/mockups/desktop-secretaria-home.png?v=3') }}" alt="Painel de cada perfil" loading="lazy" class="mm-img">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ================= MÓDULOS ================= --}}
<section class="modules" id="modulos">
    <div class="wrap center">
        <span class="eyebrow"><i class="fas fa-cubes"></i> Módulos</span>
        <h2>Tudo que sua escola precisa</h2>
        <p class="lead">Módulos integrados, sem necessidade de sistemas terceiros para a operação.</p>
        <div class="mod-grid">
            <div class="mod"><div class="ico"><i class="fas fa-id-card"></i></div><h3>Matrículas</h3><p>Fila de análise, aprovação automática, rematrícula em lote e bolsa de estudo.</p></div>
            <div class="mod"><div class="ico"><i class="fas fa-clipboard-list"></i></div><h3>Diário e Avaliações</h3><p>Chamada, plano de aula, avaliações por período letivo e agenda centralizada.</p></div>
            <div class="mod"><div class="ico"><i class="fas fa-file-alt"></i></div><h3>Boletim e Certificados</h3><p>Boletim em PDF, declaração de matrícula e certificado de conclusão.</p></div>
            <div class="mod"><div class="ico"><i class="fas fa-hand-holding-usd"></i></div><h3>Financeiro</h3><p>Mensalidades, contas a pagar, fluxo de caixa, DRE e recibos.</p></div>
            <div class="mod"><div class="ico"><i class="fas fa-book-open"></i></div><h3>Biblioteca</h3><p>Acervo, empréstimos e devoluções com controle de prazo.</p></div>
            <div class="mod"><div class="ico"><i class="fas fa-bus"></i></div><h3>Transporte</h3><p>Rotas, veículos, passageiros e relatório de ocupação.</p></div>
            <div class="mod"><div class="ico"><i class="fas fa-utensils"></i></div><h3>Alimentação</h3><p>Cardápios semanais e controle do serviço de alimentação.</p></div>
            <div class="mod"><div class="ico"><i class="fas fa-boxes"></i></div><h3>Estoque</h3><p>Materiais, movimentações, saldo e alerta de estoque mínimo.</p></div>
            <div class="mod"><div class="ico"><i class="fas fa-notes-medical"></i></div><h3>Saúde e Inclusão</h3><p>Ficha de saúde, PEI/PDI e planos de atendimento especializado.</p></div>
            <div class="mod"><div class="ico"><i class="fas fa-home"></i></div><h3>Portal da Família</h3><p>Notas, mensalidades, saúde, biblioteca e pesquisas em um só lugar.</p></div>
        </div>
    </div>
</section>

{{-- ================= PLANOS ================= --}}
<section class="shots" id="telas">
    <div class="wrap">
        <div class="center">
            <div class="eyebrow"><i class="fas fa-desktop"></i> Telas reais do sistema</div>
            <h2>O sistema <span class="hl">por dentro</span>, no PC e no celular</h2>
            <p class="lead">Capturas reais do Declass em produção, a secretaria no controle da rotina e a família acompanhando tudo pelo portal, em qualquer dispositivo.</p>
        </div>

        <div class="shots-grid">
            <div class="shot-browser">
                <div class="browser-bar"><span class="dot r"></span><span class="dot y"></span><span class="dot g"></span></div>
                <img src="{{ asset('img/mockups/desktop-secretaria-home.png?v=3') }}" alt="Painel da secretaria no computador" loading="lazy">
                <div class="shot-caption"><i class="fas fa-desktop"></i> Painel da secretaria, fila de atendimento, resumo da escola e ações rápidas</div>
            </div>
            <div class="shot-phone">
                <img src="{{ asset('img/mockups/mobile-portal-home.png?v=3') }}" alt="Portal da família no celular" loading="lazy">
                <div class="shot-caption"><i class="fas fa-mobile-alt"></i> Portal da família, notas, frequência e avisos no bolso</div>
            </div>
        </div>

        <div class="shots-row">
            <div class="shot-browser">
                <div class="browser-bar"><span class="dot r"></span><span class="dot y"></span><span class="dot g"></span></div>
                <img src="{{ asset('img/mockups/desktop-secretaria-turmas.png?v=3') }}" alt="Gestão de turmas" loading="lazy">
                <div class="shot-caption"><i class="fas fa-users"></i> Gestão de turmas</div>
            </div>
            <div class="shot-browser">
                <div class="browser-bar"><span class="dot r"></span><span class="dot y"></span><span class="dot g"></span></div>
                <img src="{{ asset('img/mockups/desktop-secretaria-alunos.png?v=3') }}" alt="Gestão de alunos" loading="lazy">
                <div class="shot-caption"><i class="fas fa-user-graduate"></i> Cadastro de alunos</div>
            </div>
            <div class="shot-browser">
                <div class="browser-bar"><span class="dot r"></span><span class="dot y"></span><span class="dot g"></span></div>
                <img src="{{ asset('img/mockups/desktop-secretaria-avaliacao.png?v=3') }}" alt="Avaliações e lançamento de notas" loading="lazy">
                <div class="shot-caption"><i class="fas fa-clipboard-check"></i> Avaliações e lançamento de notas</div>
            </div>
        </div>
    </div>
</section>

<section class="plans" id="planos">
    <div class="wrap center">
        <span class="eyebrow" style="background:rgba(245,158,11,.15);color:#fbbf24"><i class="fas fa-tag"></i> Planos</span>
        <h2>Escolha pelo que sua escola precisa</h2>
        <p class="lead">Preço por unidade, conforme o número de alunos. Todos os planos incluem suporte e atualizações, sem taxa de implantação.</p>
        <div class="plan-grid">
            <div class="plan">
                <h3>Essencial</h3>
                <div class="price"><b>R$ 129</b> <span>/mês por unidade, a partir de</span></div>
                <div class="plan-tabela">
                    <div class="pt-row"><span>Alunos</span><span>Preço/mês</span></div>
                    <div class="pt-row"><span>Até 100</span><b>R$ 129</b></div>
                    <div class="pt-row"><span>101 a 200</span><b>R$ 179</b></div>
                    <div class="pt-row"><span>201 a 300</span><b>R$ 229</b></div>
                    <div class="pt-row"><span>301 a 500</span><b>R$ 329</b></div>
                    <div class="pt-row"><span>501 a 1.000</span><b>R$ 479</b></div>
                    <div class="pt-row"><span>Acima de 1.000</span><span class="consulta">Sob consulta</span></div>
                </div>
                <ul>
                    <li><i class="fas fa-check"></i> Matrículas, rematrícula e fila de análise</li>
                    <li><i class="fas fa-check"></i> Alunos, responsáveis e ficha completa</li>
                    <li><i class="fas fa-check"></i> Diário de classe, avaliações e boletim</li>
                    <li><i class="fas fa-check"></i> Frequência, atestados e ocorrências</li>
                    <li><i class="fas fa-check"></i> Mensalidades e planos de pagamento</li>
                    <li><i class="fas fa-check"></i> Portal da família 24 horas</li>
                    <li><i class="fas fa-check"></i> Avisos, agenda e comunicação</li>
                    <li><i class="fas fa-check"></i> Documentos e declarações em PDF</li>
                </ul>
                <a href="{{ route('cadastro.empresa.create') }}" class="btn btn-outline">Começar agora</a>
            </div>
            <div class="plan hot">
                <span class="tag">Mais escolhido</span>
                <h3>Completo</h3>
                <div class="price"><b>R$ 199</b> <span>/mês por unidade, a partir de</span></div>
                <div class="plan-tabela">
                    <div class="pt-row"><span>Alunos</span><span>Preço/mês</span></div>
                    <div class="pt-row"><span>Até 100</span><b>R$ 199</b></div>
                    <div class="pt-row"><span>101 a 200</span><b>R$ 279</b></div>
                    <div class="pt-row"><span>201 a 300</span><b>R$ 349</b></div>
                    <div class="pt-row"><span>301 a 500</span><b>R$ 499</b></div>
                    <div class="pt-row"><span>501 a 1.000</span><b>R$ 749</b></div>
                    <div class="pt-row"><span>Acima de 1.000</span><span class="consulta">Sob consulta</span></div>
                </div>
                <ul>
                    <li><i class="fas fa-check"></i> Tudo do Essencial</li>
                    <li><i class="fas fa-check"></i> <strong>Site de apresentação da escola</strong> (página pública com sua identidade)
                        <a href="https://vilanova.ericksondev.online/" target="_blank" rel="noopener" class="plan-demo-link">ver exemplo ao vivo <i class="fas fa-external-link-alt"></i></a>
                    </li>
                    <li><i class="fas fa-check"></i> Financeiro completo (DRE, fluxo de caixa, contas a pagar)</li>
                    <li><i class="fas fa-check"></i> Biblioteca, transporte, alimentação e estoque</li>
                    <li><i class="fas fa-check"></i> Saúde, inclusão, PEI/PDI e planos de atendimento</li>
                    <li><i class="fas fa-check"></i> Pesquisas de satisfação e relatórios gerenciais</li>
                    <li><i class="fas fa-check"></i> Tarefas online, fórum e solicitações das famílias</li>
                </ul>
                <a href="{{ route('cadastro.empresa.create') }}" class="btn btn-amber">Começar agora</a>
            </div>
        </div>
        <div class="plan-extra">
            <span><i class="fas fa-globe"></i> <strong>Subdomínio próprio incluído</strong> — sua escola acessa em <em>suaescola.declass.com.br</em></span>
            <span><i class="fas fa-link"></i> Tem domínio próprio? Use o seu — apontamos <em>portal.suaescola.com.br</em> para o sistema</span>
            <span><i class="fas fa-headset"></i> Suporte e atualizações em todos os planos, sem taxa de implantação</span>
        </div>
        <p class="plan-note">Cobrança por unidade e faixa de alunos. Acima de 1.000 alunos, fale com a gente para uma proposta personalizada.</p>
    </div>
</section>

{{-- ================= FAQ ================= --}}
<section class="faq" id="perguntas">
    <div class="wrap" style="max-width:820px">
        <div class="center" style="margin-bottom:2.5rem">
            <span class="eyebrow"><i class="fas fa-circle-question"></i> Perguntas frequentes</span>
            <h2>Tudo o que você precisa saber antes de contratar</h2>
        </div>
        <details>
            <summary>O Declass é só um sistema administrativo?</summary>
            <div class="ans">Não. Além da gestão completa para a equipe, o Declass inclui o portal da família (notas, mensalidades, saúde, biblioteca) e um formulário público de matrícula que alimenta a fila de análise da secretaria.</div>
        </details>
        <details>
            <summary>Serve para uma escola só ou para uma rede de ensino?</summary>
            <div class="ans">Para os dois. Cada empresa pode ter várias escolas/unidades; os dados são isolados por tenant e o seletor de escola filtra toda a operação por unidade, com relatórios consolidados e permissões por perfil.</div>
        </details>
        <details>
            <summary>Meus dados ficam seguros e isolados?</summary>
            <div class="ans">Sim. Todo registro é vinculado à empresa (tenant) e as consultas são sempre filtradas por ela. O controle de acesso é granular, cada perfil só vê e faz o que lhe é permitido, e as ações sensíveis ficam registradas em auditoria.</div>
        </details>
        <details>
            <summary>Como funciona a cobrança?</summary>
            <div class="ans">A cobrança é por unidade e faixa de alunos: o Essencial começa em R$ 129/mês e o Completo em R$ 199/mês (veja a tabela na seção de planos). Não há taxa de implantação e todos os planos incluem suporte e atualizações.</div>
        </details>
        <details>
            <summary>Posso começar a usar agora?</summary>
            <div class="ans">Sim. O cadastro cria a estrutura da sua escola em minutos, você já pode cadastrar unidades, usuários e alunos. Para redes maiores, oferecemos implantação assistida.</div>
        </details>
    </div>
</section>

{{-- ================= CTA FINAL ================= --}}
<section class="final">
    <div class="wrap">
        <h2>Pronto para transformar a gestão da sua escola?</h2>
        <p>Implantação rápida. Sem taxa de setup. Suporte incluso desde o primeiro dia.</p>
        <div class="hero-ctas">
            <a href="{{ route('cadastro.empresa.create') }}" class="btn btn-amber btn-lg">Começar agora <i class="fas fa-arrow-right"></i></a>
            <a href="{{ route('login') }}" class="btn btn-ghost-light btn-lg">Já tenho acesso</a>
        </div>
    </div>
</section>

{{-- ================= FOOTER ================= --}}
<footer>
    <div class="wrap">
        <div class="foot-grid">
            <div>
                <a href="/" class="brand"><img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="" style="height:30px"> DECLASS</a>
                <p>Plataforma SaaS de gestão escolar multi-tenant para escolas e redes de ensino com múltiplas unidades.</p>
            </div>
            <div>
                <h4>Produto</h4>
                <ul>
                    <li><a href="#funcionalidades">Funcionalidades</a></li>
                    <li><a href="#modulos">Módulos</a></li>
                    <li><a href="#planos">Planos</a></li>
                    <li><a href="#perguntas">Perguntas frequentes</a></li>
                </ul>
            </div>
            <div>
                <h4>Empresa</h4>
                <ul>
                    <li><a href="#sobre">Sobre o Declass</a></li>
                    <li><a href="{{ route('cadastro.empresa.create') }}">Criar minha escola</a></li>
                    <li><a href="{{ route('login') }}">Acessar o sistema</a></li>
                </ul>
            </div>
            <div>
                <h4>Suporte</h4>
                <ul>
                    <li><a href="{{ route('login') }}">Entrar no sistema</a></li>
                    <li><a href="{{ route('cadastro.empresa.create') }}">Começar agora</a></li>
                </ul>
            </div>
        </div>
        <div class="foot-bottom">
            <span>© {{ date('Y') }} DECLASS. Todos os direitos reservados.</span>
            <span>Gestão escolar simples para a equipe, poderosa para o gestor.</span>
        </div>
    </div>
</footer>

<script>
(function () {
    var car = document.getElementById('heroCar');
    if (!car) return;
    var slides = car.querySelectorAll('.car-slide');
    var dotsWrap = car.querySelector('.car-dots');
    var i = 0, timer;

    slides.forEach(function (_, n) {
        var d = document.createElement('span');
        d.className = 'car-dot' + (n === 0 ? ' on' : '');
        d.addEventListener('click', function () { go(n); reset(); });
        dotsWrap.appendChild(d);
    });
    var dots = dotsWrap.querySelectorAll('.car-dot');

    function go(n) {
        slides[i].classList.remove('on');
        dots[i].classList.remove('on');
        i = (n + slides.length) % slides.length;
        slides[i].classList.add('on');
        dots[i].classList.add('on');
    }
    function reset() { clearInterval(timer); timer = setInterval(function () { go(i + 1); }, 4000); }

    car.querySelector('.car-prev').addEventListener('click', function () { go(i - 1); reset(); });
    car.querySelector('.car-next').addEventListener('click', function () { go(i + 1); reset(); });
    car.addEventListener('mouseenter', function () { clearInterval(timer); });
    car.addEventListener('mouseleave', reset);
    reset();
})();
</script>
<div class="lightbox" id="imgLb">
    <button type="button" class="lb-close" aria-label="Fechar">&times;</button>
    <img src="" alt="">
    <div class="lb-cap"></div>
</div>
<script>
(function () {
    var lb = document.getElementById('imgLb');
    var img = lb.querySelector('img');
    var cap = lb.querySelector('.lb-cap');
    document.querySelectorAll('.shot-browser img, .shot-phone img, .car-slide img').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.stopPropagation();
            img.src = el.src;
            cap.textContent = el.alt || '';
            lb.classList.add('on');
            document.body.style.overflow = 'hidden';
        });
    });
    function close() { lb.classList.remove('on'); document.body.style.overflow = ''; img.src = ''; }
    lb.addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
</body>
</html>
