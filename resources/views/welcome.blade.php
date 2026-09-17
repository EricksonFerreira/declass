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
    <meta name="theme-color" content="#2563eb">
    <title>DECLOCK | Gestao de tempo e produtividade para equipes</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicons/favicon.ico') }}">
    <link rel="manifest" href="{{ route('site.manifest') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">

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
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-soft: #eff6ff;
            --navy: #0f172a;
            --text: #334155;
            --muted: #64748b;
            --surface: #f4f6f9;
            --border: #e2e8f0;
            --radius: 16px;
            --shadow-sm: 0 1px 3px rgba(15,23,42,.07);
            --shadow: 0 4px 18px rgba(15,23,42,.08);
            --shadow-lg: 0 24px 60px rgba(15,23,42,.14);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #fff;
            color: var(--text);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; color: inherit; }
        .container { max-width: 1160px; margin: 0 auto; padding: 0 5%; }

        /* ---------- Nav ---------- */
        .nav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(226,232,240,.8);
        }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; min-height: 68px; gap: 1rem; }
        .brand { display: inline-flex; align-items: center; gap: .65rem; font-weight: 800; font-size: 1.15rem; color: var(--navy); letter-spacing: -.02em; }
        .brand img { width: 34px; height: 34px; object-fit: contain; }
        .nav-links { display: flex; align-items: center; gap: 1.5rem; }
        .nav-links a { font-size: .88rem; color: var(--muted); font-weight: 600; }
        .nav-links a:hover { color: var(--navy); }
        .nav-actions { display: flex; align-items: center; gap: .7rem; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            padding: .8rem 1.35rem; border-radius: 10px;
            font-weight: 700; font-size: .92rem;
            transition: transform .18s, box-shadow .18s, background .18s, border-color .18s;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 6px 16px rgba(37,99,235,.28); }
        .btn-primary:hover { background: var(--primary-dark); box-shadow: 0 10px 24px rgba(37,99,235,.34); }
        .btn-ghost { border: 1.5px solid var(--border); color: var(--navy); background: #fff; }
        .btn-ghost:hover { border-color: var(--navy); background: #f8fafc; }
        .btn-white { background: #fff; color: var(--navy); }
        .btn-white:hover { box-shadow: 0 10px 24px rgba(0,0,0,.22); }
        .btn-outline-light { border: 1.5px solid rgba(255,255,255,.35); color: #fff; }
        .btn-outline-light:hover { background: rgba(255,255,255,.1); }
        .btn-sm { padding: .55rem 1rem; font-size: .85rem; }

        /* ---------- Hero ---------- */
        .hero {
            position: relative;
            padding: 4.5rem 0 5rem;
            background:
                radial-gradient(ellipse 60% 45% at 80% 0%, rgba(37,99,235,.09), transparent 60%),
                radial-gradient(ellipse 45% 40% at 5% 90%, rgba(14,165,233,.07), transparent 55%),
                var(--surface);
            overflow: hidden;
        }
        .hero-grid { display: grid; grid-template-columns: 1.02fr .98fr; gap: 3.5rem; align-items: center; }
        .eyebrow {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .35rem .9rem; border-radius: 999px;
            background: var(--primary-soft); border: 1px solid rgba(37,99,235,.18);
            color: var(--primary); font-size: .74rem; font-weight: 800;
            letter-spacing: .08em; text-transform: uppercase; margin-bottom: 1.2rem;
        }
        .hero h1 {
            font-size: clamp(2.2rem, 4.6vw, 3.6rem);
            line-height: 1.06; letter-spacing: -.035em;
            color: var(--navy); font-weight: 800; margin-bottom: 1.15rem;
        }
        .hero h1 em { font-style: normal; color: var(--primary); }
        .hero p.sub { font-size: 1.05rem; line-height: 1.8; color: var(--muted); max-width: 560px; margin-bottom: 1.7rem; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: .85rem; margin-bottom: 1.6rem; }
        .hero-points { display: flex; flex-wrap: wrap; gap: .7rem 1.4rem; color: var(--muted); font-size: .85rem; font-weight: 600; }
        .hero-points span { display: inline-flex; align-items: center; gap: .45rem; }
        .hero-points i { color: #16a34a; }

        /* ---------- Hero mockup (product preview) ---------- */
        .mock-window {
            background: #fff; border-radius: 18px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
            overflow: hidden;
            transform: perspective(1400px) rotateY(-4deg) rotateX(1deg);
        }
        .mock-chrome {
            display: flex; align-items: center; gap: .5rem;
            padding: .65rem .9rem; background: #f8fafc; border-bottom: 1px solid var(--border);
        }
        .mock-dot { width: 10px; height: 10px; border-radius: 50%; }
        .mock-url {
            flex: 1; margin-left: .6rem; background: #fff; border: 1px solid var(--border);
            border-radius: 7px; font-size: .68rem; color: var(--muted);
            padding: .28rem .7rem; display: flex; align-items: center; gap: .4rem;
        }
        .mock-body { display: grid; grid-template-columns: 52px 1fr; min-height: 330px; }
        .mock-side { background: var(--navy); padding: .8rem .55rem; display: flex; flex-direction: column; gap: .75rem; align-items: center; }
        .mock-side i { color: rgba(255,255,255,.45); font-size: .8rem; }
        .mock-side i.on { color: #fff; }
        .mock-main { padding: .9rem 1rem; background: var(--surface); }
        .mock-title { font-size: .72rem; font-weight: 800; color: var(--navy); margin-bottom: .7rem; display: flex; align-items: center; gap: .45rem; }
        .mock-title i { color: var(--primary); }
        .mock-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: .55rem; margin-bottom: .8rem; }
        .mock-kpi { background: #fff; border-radius: 10px; padding: .55rem .65rem; box-shadow: var(--shadow-sm); }
        .mock-kpi small { display: block; font-size: .55rem; color: var(--muted); text-transform: uppercase; font-weight: 700; letter-spacing: .05em; }
        .mock-kpi b { font-size: .92rem; color: var(--navy); font-weight: 800; }
        .mock-chart { background: #fff; border-radius: 10px; padding: .7rem .8rem; box-shadow: var(--shadow-sm); margin-bottom: .8rem; }
        .mock-chart-head { display: flex; justify-content: space-between; font-size: .6rem; font-weight: 700; color: var(--navy); margin-bottom: .55rem; }
        .mock-chart-head span { color: var(--muted); font-weight: 600; }
        .mock-bars { display: flex; align-items: flex-end; gap: 6px; height: 74px; }
        .mock-bars div { flex: 1; border-radius: 4px 4px 0 0; background: linear-gradient(180deg, #60a5fa, var(--primary)); }
        .mock-tracker {
            display: flex; align-items: center; gap: .6rem;
            background: #fff; border-radius: 10px; padding: .55rem .7rem; box-shadow: var(--shadow-sm);
        }
        .mock-play {
            width: 26px; height: 26px; border-radius: 50%;
            background: #dcfce7; color: #16a34a;
            display: inline-flex; align-items: center; justify-content: center; font-size: .6rem;
        }
        .mock-tracker-info { flex: 1; }
        .mock-tracker-info b { display: block; font-size: .62rem; color: var(--navy); }
        .mock-tracker-info small { font-size: .55rem; color: var(--muted); }
        .mock-time { font-size: .8rem; font-weight: 800; color: var(--navy); font-variant-numeric: tabular-nums; }
        .mock-badge { font-size: .52rem; font-weight: 800; padding: .15rem .45rem; border-radius: 999px; background: var(--primary-soft); color: var(--primary); text-transform: uppercase; }

        /* ---------- Sections ---------- */
        .section { padding: 4.8rem 0; }
        .section.alt { background: var(--surface); }
        .section-head { max-width: 720px; margin-bottom: 2.6rem; }
        .tag {
            display: inline-flex; align-items: center; gap: .45rem;
            padding: .32rem .85rem; border-radius: 999px;
            background: var(--primary-soft); color: var(--primary);
            font-size: .72rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase;
            margin-bottom: 1rem;
        }
        .section h2 { font-size: clamp(1.7rem, 3vw, 2.5rem); line-height: 1.15; letter-spacing: -.03em; color: var(--navy); font-weight: 800; margin-bottom: .8rem; }
        .section .lead { color: var(--muted); line-height: 1.8; font-size: .98rem; }

        .cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.1rem; }
        .card {
            background: #fff; border: 1px solid var(--border); border-radius: var(--radius);
            padding: 1.5rem; box-shadow: var(--shadow-sm);
            transition: transform .22s, box-shadow .22s, border-color .22s;
        }
        .card:hover { transform: translateY(-4px); box-shadow: var(--shadow); border-color: rgba(37,99,235,.35); }
        .card .ic {
            width: 44px; height: 44px; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.05rem; margin-bottom: 1rem;
        }
        .ic-blue { background: var(--primary-soft); color: var(--primary); }
        .ic-green { background: #f0fdf4; color: #16a34a; }
        .ic-amber { background: #fffbeb; color: #d97706; }
        .ic-violet { background: #f5f3ff; color: #7c3aed; }
        .ic-cyan { background: #ecfeff; color: #0891b2; }
        .ic-rose { background: #fff1f2; color: #e11d48; }
        .card h3 { font-size: 1rem; color: var(--navy); font-weight: 800; margin-bottom: .5rem; }
        .card p { color: var(--muted); line-height: 1.7; font-size: .87rem; }

        /* ---------- Steps ---------- */
        .steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.1rem; }
        .step { background: #fff; border: 1px solid var(--border); border-radius: var(--radius); padding: 1.6rem; box-shadow: var(--shadow-sm); }
        .step-num {
            display: inline-flex; align-items: center; justify-content: center;
            width: 38px; height: 38px; border-radius: 999px;
            background: var(--primary); color: #fff; font-weight: 800; margin-bottom: 1rem;
        }
        .step h3 { color: var(--navy); font-weight: 800; margin-bottom: .55rem; font-size: 1.02rem; }
        .step p { color: var(--muted); line-height: 1.7; font-size: .9rem; }

        /* ---------- Split highlight ---------- */
        .split { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; }
        .check-list { display: grid; gap: .9rem; margin-top: 1.4rem; }
        .check-list li { display: flex; gap: .7rem; align-items: flex-start; line-height: 1.65; font-size: .94rem; list-style: none; }
        .check-list i { color: #16a34a; margin-top: .25rem; }
        .panel {
            background: #fff; border: 1px solid var(--border); border-radius: 20px;
            padding: 1.6rem; box-shadow: var(--shadow);
        }
        .panel-row { display: flex; align-items: center; gap: .8rem; padding: .75rem .4rem; border-bottom: 1px solid #f1f5f9; }
        .panel-row:last-child { border-bottom: none; }
        .panel-row .avatar {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: .8rem; color: #fff;
        }
        .panel-row b { display: block; font-size: .85rem; color: var(--navy); }
        .panel-row small { font-size: .75rem; color: var(--muted); }
        .panel-row .hrs { margin-left: auto; font-weight: 800; color: var(--navy); font-size: .9rem; font-variant-numeric: tabular-nums; }
        .bar { height: 6px; border-radius: 999px; background: #f1f5f9; overflow: hidden; width: 90px; margin-left: .6rem; }
        .bar i { display: block; height: 100%; border-radius: 999px; background: linear-gradient(90deg, #60a5fa, var(--primary)); }

        /* ---------- CTA ---------- */
        .cta {
            background: linear-gradient(135deg, var(--navy), #1e3a8a);
            color: #fff; border-radius: 24px; padding: 3rem;
            display: grid; grid-template-columns: 1fr auto; gap: 2rem; align-items: center;
            position: relative; overflow: hidden;
        }
        .cta::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 85% 15%, rgba(96,165,250,.25), transparent 45%);
            pointer-events: none;
        }
        .cta > * { position: relative; z-index: 1; }
        .cta h2 { color: #fff; margin-bottom: .6rem; }
        .cta p { color: rgba(255,255,255,.75); max-width: 560px; line-height: 1.7; }
        .cta-actions { display: flex; flex-direction: column; gap: .7rem; }

        /* ---------- Footer ---------- */
        .footer { padding: 2.4rem 0; border-top: 1px solid var(--border); }
        .footer-inner { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .footer-links { display: flex; gap: 1.4rem; }
        .footer-links a { font-size: .84rem; color: var(--muted); font-weight: 600; }
        .footer-links a:hover { color: var(--navy); }
        .footer small { color: var(--muted); }

        @media (max-width: 1024px) {
            .hero-grid, .split, .cta { grid-template-columns: 1fr; }
            .cards { grid-template-columns: repeat(2, 1fr); }
            .steps { grid-template-columns: 1fr; }
            .mock-window { transform: none; }
            .cta-actions { flex-direction: row; flex-wrap: wrap; }
        }
        @media (max-width: 640px) {
            .nav-links { display: none; }
            .hero { padding: 3rem 0 3.5rem; }
            .cards { grid-template-columns: 1fr; }
            .cta { padding: 1.8rem; }
            .mock-kpis { grid-template-columns: repeat(3, 1fr); }
        }
    </style>
</head>
<body>
    <header class="nav">
        <div class="container nav-inner">
            <a class="brand" href="{{ route('site.apresentacao') }}">
                <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="Logo DECLOCK">
                <span>DECLOCK</span>
            </a>
            <nav class="nav-links" aria-label="Navegacao principal">
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="#como-funciona">Como funciona</a>
                <a href="#planos">Planos</a>
                <a href="{{ route('site.sobre') }}">Sobre</a>
            </nav>
            <div class="nav-actions">
                @auth
                    <a class="btn btn-primary btn-sm" href="{{ route('home') }}"><i class="fas fa-gauge-high"></i> Acessar painel</a>
                @else
                    <a class="btn btn-ghost btn-sm" href="{{ route('login') }}">Entrar</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('cadastro.empresa.create') }}">Comecar gratis</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        {{-- ===================== HERO ===================== --}}
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <div class="eyebrow"><i class="fas fa-clock"></i> Gestao de tempo para equipes</div>
                    <h1>Saiba exatamente onde o tempo da sua equipe <em>esta sendo investido</em></h1>
                    <p class="sub">
                        O DECLOCK reune rastreador de horas, timesheet, calendario de alocacoes e relatorios de produtividade em uma plataforma simples — para equipes que trabalham por projetos e precisam de clareza, nao de planilhas.
                    </p>
                    <div class="hero-actions">
                        @auth
                            <a class="btn btn-primary" href="{{ route('home') }}"><i class="fas fa-gauge-high"></i> Acessar painel</a>
                        @else
                            <a class="btn btn-primary" href="{{ route('cadastro.empresa.create') }}"><i class="fas fa-rocket"></i> Comecar agora — e gratis</a>
                            <a class="btn btn-ghost" href="{{ route('login') }}"><i class="fas fa-right-to-bracket"></i> Ja tenho conta</a>
                        @endauth
                    </div>
                    <div class="hero-points">
                        <span><i class="fas fa-check-circle"></i> Sem instalacao — roda no navegador</span>
                        <span><i class="fas fa-check-circle"></i> Multiempresa com permissoes</span>
                        <span><i class="fas fa-check-circle"></i> Relatorios exportaveis</span>
                    </div>
                </div>

                {{-- Product preview (CSS mockup do dashboard real) --}}
                <div class="mock-window" aria-hidden="true">
                    <div class="mock-chrome">
                        <span class="mock-dot" style="background:#f87171"></span>
                        <span class="mock-dot" style="background:#fbbf24"></span>
                        <span class="mock-dot" style="background:#34d399"></span>
                        <span class="mock-url"><i class="fas fa-lock" style="font-size:.55rem"></i> declock.ericksondev.online/admin/home</span>
                    </div>
                    <div class="mock-body">
                        <div class="mock-side">
                            <i class="fas fa-gauge-high on"></i>
                            <i class="fas fa-stopwatch"></i>
                            <i class="fas fa-calendar-days"></i>
                            <i class="fas fa-diagram-project"></i>
                            <i class="fas fa-chart-column"></i>
                        </div>
                        <div class="mock-main">
                            <div class="mock-title"><i class="fas fa-gauge-high"></i> Visao geral da equipe</div>
                            <div class="mock-kpis">
                                <div class="mock-kpi"><small>Horas hoje</small><b>126h</b></div>
                                <div class="mock-kpi"><small>Rastreadores</small><b>8 ativos</b></div>
                                <div class="mock-kpi"><small>Projetos</small><b>12</b></div>
                            </div>
                            <div class="mock-chart">
                                <div class="mock-chart-head">Horas por dia no periodo <span>ultimos 7 dias</span></div>
                                <div class="mock-bars">
                                    <div style="height:45%"></div>
                                    <div style="height:70%"></div>
                                    <div style="height:55%"></div>
                                    <div style="height:85%"></div>
                                    <div style="height:62%"></div>
                                    <div style="height:95%"></div>
                                    <div style="height:40%"></div>
                                </div>
                            </div>
                            <div class="mock-tracker">
                                <span class="mock-play"><i class="fas fa-stop"></i></span>
                                <div class="mock-tracker-info">
                                    <b>Desenvolvimento — Portal do cliente</b>
                                    <small>Ana Souza · Projeto DECLOCK Web</small>
                                </div>
                                <span class="mock-badge">Rodando</span>
                                <span class="mock-time">02:47:13</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===================== FEATURES ===================== --}}
        <section class="section" id="funcionalidades">
            <div class="container">
                <div class="section-head">
                    <div class="tag"><i class="fas fa-layer-group"></i> Funcionalidades</div>
                    <h2>Tudo o que a sua equipe precisa para controlar o tempo</h2>
                    <p class="lead">Do registro da hora ao relatorio gerencial: um fluxo completo para times que vendem projetos, horas tecnicas ou precisam prestar contas da operacao.</p>
                </div>
                <div class="cards">
                    <article class="card">
                        <span class="ic ic-blue"><i class="fas fa-stopwatch"></i></span>
                        <h3>Rastreador de tempo</h3>
                        <p>Inicie e pare o cronometro com um clique, vinculando o trabalho a projetos e clientes. Veja quem esta trabalhando em que, em tempo real.</p>
                    </article>
                    <article class="card">
                        <span class="ic ic-green"><i class="fas fa-table-list"></i></span>
                        <h3>Timesheet</h3>
                        <p>Visao semanal das horas lancadas por cada colaborador — ideal para conferencia, fechamento de periodo e prestacao de contas.</p>
                    </article>
                    <article class="card">
                        <span class="ic ic-violet"><i class="fas fa-calendar-days"></i></span>
                        <h3>Calendario de alocacoes</h3>
                        <p>Planeje a agenda da equipe arrastando e soltando alocacoes, com visoes por dia, semana e mes — otimizado tambem no celular.</p>
                    </article>
                    <article class="card">
                        <span class="ic ic-cyan"><i class="fas fa-diagram-project"></i></span>
                        <h3>Projetos e clientes</h3>
                        <p>Organize projetos por cliente, defina etapas com cronograma, valor hora e acompanhe a entrega de cada fase.</p>
                    </article>
                    <article class="card">
                        <span class="ic ic-amber"><i class="fas fa-users-gear"></i></span>
                        <h3>Planejamento de equipe</h3>
                        <p>Distribua membros entre projetos e etapas, definindo horas por dia e periodo — com visao clara de quem esta alocado onde.</p>
                    </article>
                    <article class="card">
                        <span class="ic ic-rose"><i class="fas fa-chart-line"></i></span>
                        <h3>Produtividade</h3>
                        <p>Dashboards com horas por dia, ranking de colaboradores e indicadores para entender a capacidade real do time.</p>
                    </article>
                    <article class="card">
                        <span class="ic ic-green"><i class="fas fa-file-export"></i></span>
                        <h3>Relatorios</h3>
                        <p>Relatorios de horas trabalhadas por projeto, cliente e colaborador — com filtros e exportacao para analise externa.</p>
                    </article>
                    <article class="card">
                        <span class="ic ic-blue"><i class="fas fa-umbrella-beach"></i></span>
                        <h3>Folgas e ferias</h3>
                        <p>Solicitacoes de time off com fluxo de aprovacao, saldo visivel para o gestor e registro integrado ao calendario.</p>
                    </article>
                </div>
            </div>
        </section>

        {{-- ===================== COMO FUNCIONA ===================== --}}
        <section class="section alt" id="como-funciona">
            <div class="container">
                <div class="section-head">
                    <div class="tag"><i class="fas fa-route"></i> Como funciona</div>
                    <h2>Da conta ao primeiro relatorio em minutos</h2>
                </div>
                <div class="steps">
                    <div class="step">
                        <span class="step-num">1</span>
                        <h3>Crie sua empresa</h3>
                        <p>Cadastre a organizacao e o responsavel em menos de um minuto. O ambiente e criado na hora, com acesso imediato ao painel.</p>
                    </div>
                    <div class="step">
                        <span class="step-num">2</span>
                        <h3>Monte projetos e equipes</h3>
                        <p>Cadastre clientes, projetos com etapas e convide os colaboradores. Cada pessoa ve apenas o que precisa, com permissoes por perfil.</p>
                    </div>
                    <div class="step">
                        <span class="step-num">3</span>
                        <h3>Registre e acompanhe</h3>
                        <p>A equipe registra horas pelo rastreador ou timesheet, e voce acompanha tudo nos dashboards, calendario e relatorios.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===================== HIGHLIGHT SPLIT ===================== --}}
        <section class="section">
            <div class="container split">
                <div>
                    <div class="tag"><i class="fas fa-eye"></i> Visibilidade total</div>
                    <h2>Gestao que enxerga a operacao inteira</h2>
                    <ul class="check-list">
                        <li><i class="fas fa-check-circle"></i> <span><strong>Quem esta trabalhando agora</strong> — rastreadores ativos em tempo real no dashboard.</span></li>
                        <li><i class="fas fa-check-circle"></i> <span><strong>Onde as horas estao indo</strong> — por projeto, cliente, etapa e colaborador.</span></li>
                        <li><i class="fas fa-check-circle"></i> <span><strong>Capacidade do time</strong> — alocacoes e folgas visiveis no mesmo calendario.</span></li>
                        <li><i class="fas fa-check-circle"></i> <span><strong>Multiempresa</strong> — dados isolados por empresa, com perfis e permissoes.</span></li>
                    </ul>
                </div>
                <div class="panel">
                    <div class="mock-title" style="margin-bottom:.4rem"><i class="fas fa-chart-column"></i> Produtividade da equipe</div>
                    <div class="panel-row">
                        <span class="avatar" style="background:#2563eb">AS</span>
                        <div><b>Ana Souza</b><small>Portal do cliente</small></div>
                        <div class="bar"><i style="width:92%"></i></div>
                        <span class="hrs">7,4h</span>
                    </div>
                    <div class="panel-row">
                        <span class="avatar" style="background:#16a34a">BR</span>
                        <div><b>Bruno Rocha</b><small>App mobile</small></div>
                        <div class="bar"><i style="width:80%"></i></div>
                        <span class="hrs">6,4h</span>
                    </div>
                    <div class="panel-row">
                        <span class="avatar" style="background:#d97706">CM</span>
                        <div><b>Carla Mendes</b><small>Design system</small></div>
                        <div class="bar"><i style="width:64%"></i></div>
                        <span class="hrs">5,1h</span>
                    </div>
                    <div class="panel-row">
                        <span class="avatar" style="background:#7c3aed">DL</span>
                        <div><b>Diego Lima</b><small>API e integracoes</small></div>
                        <div class="bar"><i style="width:48%"></i></div>
                        <span class="hrs">3,8h</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===================== CTA / PLANOS ===================== --}}
        <section class="section alt" id="planos">
            <div class="container">
                <div class="cta">
                    <div>
                        <h2>Pronto para colocar o tempo da equipe sob controle?</h2>
                        <p>Crie a conta da sua empresa agora e comece a registrar horas em minutos. Planos flexiveis para equipes de todos os tamanhos, com pagamento via PIX ou cartao.</p>
                    </div>
                    <div class="cta-actions">
                        <a class="btn btn-white" href="{{ route('cadastro.empresa.create') }}"><i class="fas fa-rocket"></i> Criar conta gratis</a>
                        <a class="btn btn-outline-light" href="{{ route('site.sobre') }}"><i class="fas fa-circle-info"></i> Conhecer a DECLOCK</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container footer-inner">
            <a class="brand" href="{{ route('site.apresentacao') }}" style="font-size:1rem">
                <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="Logo DECLOCK">
                <span>DECLOCK</span>
            </a>
            <nav class="footer-links">
                <a href="#funcionalidades">Funcionalidades</a>
                <a href="{{ route('site.sobre') }}">Sobre</a>
                <a href="{{ route('login') }}">Entrar</a>
            </nav>
            <small>&copy; {{ date('Y') }} DECLOCK · Sonyd Tech. Todos os direitos reservados.</small>
        </div>
    </footer>
</body>
</html>
