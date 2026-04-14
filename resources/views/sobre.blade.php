<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Conheca a DeCars, software da Sonyd Tech para locadoras de veiculos com gestao completa, check-in digital, financeiro e site de reservas online para vender mais.">
    <meta name="keywords" content="sobre DeCars, software para locadora de veiculos, sistema para locadoras, site de reservas online, Sonyd Tech, gestao de frota, check-in digital, financeiro, multas">
    <meta name="author" content="Sonyd Tech">
    <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
    <link rel="canonical" href="{{ url('/sobre') }}">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DeCars">
    <meta property="og:title" content="Sobre a DeCars | Software da Sonyd Tech para Locadoras">
    <meta property="og:description" content="Gestao de locadoras com site de reservas online, personalizacao e foco em conversao.">
    <meta property="og:url" content="{{ url('/sobre') }}">
    <meta property="og:image" content="{{ asset('assets/og/decars-share.svg') }}">
    <meta property="og:image:type" content="image/svg+xml">
    <meta property="og:image:alt" content="Banner da DeCars com software para locadoras e site de vendas">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Sobre a DeCars | Software da Sonyd Tech para Locadoras">
    <meta name="twitter:description" content="Software para locadoras com site de reservas online e foco em vendas.">
    <meta name="twitter:image" content="{{ asset('assets/og/decars-share.svg') }}">
    <meta name="theme-color" content="#0a1628">
    <title>Sobre a DeCars | Software da Sonyd Tech para Locadoras</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicons/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "AboutPage",
        "name": "Sobre a DeCars",
        "url": "{{ url('/sobre') }}",
        "about": {
            "@type": "SoftwareApplication",
            "name": "DeCars",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web",
            "publisher": {
                "@type": "Organization",
                "name": "Sonyd Tech",
                "url": "https://www.sonydtech.com"
            }
        }
    }
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(14,165,233,.14), transparent 30%),
                radial-gradient(circle at left bottom, rgba(14,165,233,.1), transparent 28%),
                #fff;
            color: #334155;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; display: block; }
        button { font-family: inherit; cursor: pointer; border: none; background: none; }
        :root {
            --bg: #0a1628;
            --bg-hover: #162035;
            --cyan: #0ea5e9;
            --surface: #f8fafc;
            --border: #e2e8f0;
            --text: #334155;
            --muted: #64748b;
            --radius: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
            --shadow: 0 4px 16px rgba(0,0,0,.1), 0 2px 6px rgba(0,0,0,.06);
            --shadow-lg: 0 20px 60px rgba(0,0,0,.12), 0 8px 24px rgba(0,0,0,.08);
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 5%; }
        .nav {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255,255,255,.9);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(226,232,240,.75);
        }
        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 72px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
            font-family: 'Raleway', sans-serif;
            font-weight: 900;
            color: var(--bg);
            letter-spacing: -.02em;
        }
        .brand img { width: 34px; height: 34px; object-fit: contain; }
        .nav-links { display: flex; align-items: center; gap: 1.35rem; flex-wrap: wrap; }
        .nav-links a {
            font-size: .9rem;
            color: var(--muted);
            font-weight: 600;
        }
        .nav-links a:hover { color: var(--bg); }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            padding: .85rem 1.2rem;
            border-radius: 999px;
            font-weight: 800;
            transition: transform .2s, background .2s, border-color .2s, box-shadow .2s;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: var(--bg); color: #fff; }
        .btn-primary:hover { background: var(--bg-hover); box-shadow: 0 8px 24px rgba(10,22,40,.2); }
        .btn-ghost { border: 1.5px solid var(--border); color: var(--bg); background: #fff; }
        .btn-ghost:hover { border-color: var(--bg); background: rgba(10,22,40,.03); }

        .hero {
            position: relative;
            padding: 5.5rem 0 4rem;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 70% 20%, rgba(14,165,233,.12), transparent 26%);
            pointer-events: none;
        }
        .hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 3rem;
            align-items: center;
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .35rem .85rem;
            border-radius: 999px;
            border: 1px solid rgba(14,165,233,.18);
            background: rgba(14,165,233,.06);
            color: var(--bg);
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 1.1rem;
        }
        .hero h1 {
            font-family: 'Raleway', sans-serif;
            font-size: clamp(2.3rem, 4.8vw, 4.2rem);
            line-height: 1.02;
            letter-spacing: -.04em;
            color: var(--bg);
            margin-bottom: 1rem;
        }
        .hero p {
            font-size: 1.03rem;
            line-height: 1.85;
            color: var(--muted);
            max-width: 620px;
            margin-bottom: 1.5rem;
        }
        .hero-actions { display: flex; flex-wrap: wrap; gap: .85rem; margin-bottom: 1.4rem; }
        .hero-points {
            display: flex;
            flex-wrap: wrap;
            gap: .8rem 1.2rem;
            color: var(--muted);
            font-size: .86rem;
        }
        .hero-points span { display: inline-flex; align-items: center; gap: .45rem; }
        .hero-points i { color: var(--bg); }
        .hero-card {
            background: #fff;
            border: 1.5px solid var(--border);
            border-radius: 24px;
            padding: 1rem;
            box-shadow: var(--shadow-lg);
        }
        .hero-card img {
            width: 100%;
            border-radius: 18px;
            display: block;
        }
        .section {
            padding: 4.5rem 0;
        }
        .section-head {
            display: flex;
            flex-direction: column;
            gap: .7rem;
            margin-bottom: 2rem;
        }
        .tag {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            width: fit-content;
            padding: .32rem .8rem;
            border-radius: 999px;
            border: 1px solid rgba(10,22,40,.14);
            color: var(--bg);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
        }
        .section h2 {
            font-family: 'Raleway', sans-serif;
            font-size: clamp(1.8rem, 3vw, 2.7rem);
            line-height: 1.12;
            letter-spacing: -.03em;
            color: var(--bg);
        }
        .section p.lead {
            max-width: 760px;
            color: var(--muted);
            line-height: 1.8;
            font-size: .98rem;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }
        .card {
            background: #fff;
            border: 1.5px solid var(--border);
            border-radius: 18px;
            padding: 1.35rem;
            box-shadow: var(--shadow-sm);
            transition: transform .25s, border-color .25s, box-shadow .25s;
        }
        .card:hover {
            transform: translateY(-4px);
            border-color: var(--bg);
            box-shadow: var(--shadow);
        }
        .card i {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(10,22,40,.06);
            color: var(--bg);
            margin-bottom: .95rem;
        }
        .card h3 {
            font-family: 'Raleway', sans-serif;
            font-size: 1rem;
            color: var(--bg);
            margin-bottom: .55rem;
        }
        .card p {
            color: var(--muted);
            line-height: 1.75;
            font-size: .88rem;
        }
        .split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: start;
        }
        .panel {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 20px;
            padding: 1.5rem;
        }
        .panel ul { display: grid; gap: .85rem; margin-top: 1rem; }
        .panel li {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            line-height: 1.65;
            color: var(--text);
            font-size: .94rem;
        }
        .panel li i { color: var(--bg); margin-top: .2rem; }
        .timeline {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }
        .step {
            background: linear-gradient(180deg, #fff, #f8fafc);
            border: 1.5px solid var(--border);
            border-radius: 18px;
            padding: 1.4rem;
        }
        .step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            background: var(--bg);
            color: #fff;
            font-weight: 900;
            margin-bottom: .85rem;
        }
        .step h3 {
            font-family: 'Raleway', sans-serif;
            color: var(--bg);
            margin-bottom: .55rem;
        }
        .step p {
            color: var(--muted);
            line-height: 1.7;
            font-size: .9rem;
        }
        .cta {
            background: var(--bg);
            color: #fff;
            border-radius: 28px;
            padding: 2.5rem;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1.5rem;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        .cta::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(14,165,233,.14), transparent 34%);
            pointer-events: none;
        }
        .cta > * { position: relative; z-index: 1; }
        .cta h2 {
            color: #fff;
            margin-bottom: .65rem;
        }
        .cta p { color: rgba(255,255,255,.72); max-width: 720px; }
        .footer {
            padding: 2rem 0 3rem;
            border-top: 1px solid var(--border);
            background: #fff;
        }
        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .footer-brand {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            color: var(--bg);
            font-weight: 800;
        }
        .footer-brand img { width: 32px; height: 32px; object-fit: contain; }
        .footer small { color: var(--muted); }

        @media (max-width: 1024px) {
            .hero-grid, .split, .cta { grid-template-columns: 1fr; }
            .cards { grid-template-columns: repeat(2, 1fr); }
            .timeline { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hero { padding-top: 3.5rem; }
            .cards { grid-template-columns: 1fr; }
            .cta { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <header class="nav">
        <div class="container nav-inner">
            <a class="brand" href="{{ route('site.apresentacao') }}">
                <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="Logo DeCars">
                <span>DeCars</span>
            </a>
            <nav class="nav-links" aria-label="Navegacao principal">
                <a href="{{ route('site.apresentacao') }}">Home</a>
                <a href="{{ route('site.sobre') }}">Sobre</a>
                <a href="{{ route('site.apresentacao') }}#planos">Planos</a>
                <a href="{{ route('site.apresentacao') }}#funcionalidades">Funcionalidades</a>
            </nav>
            <div class="nav-links">
                <a class="btn btn-ghost" href="{{ route('login') }}">Entrar</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <div class="eyebrow">
                        <i class="fas fa-sparkles"></i>
                        Software da Sonyd Tech
                    </div>
                    <h1>Software para locadoras que também vende online</h1>
                    <p>
                        A DeCars foi criada para locadoras que querem sair da planilha, organizar a operação e, ao mesmo tempo, ter um site de reservas online para transformar visitantes em clientes.
                        Aqui você encontra gestão completa, check-in digital, financeiro, multas, NFS-e e uma presença digital pensada para crescer junto com o seu negócio.
                    </p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="{{ route('site.apresentacao') }}#planos">
                            Ver planos
                        </a>
                        <a class="btn btn-ghost" href="https://wa.me/558192554172?text={{ urlencode('Olá! Quero conhecer melhor a DeCars e entender como o site de reservas pode ajudar minha locadora.') }}" target="_blank" rel="noopener">
                            Falar no WhatsApp
                        </a>
                    </div>
                    <div class="hero-points">
                        <span><i class="fas fa-check-circle"></i> Site de reservas online</span>
                        <span><i class="fas fa-check-circle"></i> Sistema administrativo completo</span>
                        <span><i class="fas fa-check-circle"></i> Personalização da marca</span>
                    </div>
                </div>
                <div class="hero-card">
                    <img src="{{ asset('assets/og/decars-share.svg') }}" alt="Banner da DeCars com software e site de reservas online">
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section-head">
                    <div class="tag">O diferencial</div>
                    <h2>Mais do que um sistema. Uma plataforma para vender mais.</h2>
                    <p class="lead">
                        A maioria dos sistemas para locadoras ajuda na operação interna. A DeCars vai além: entrega um site de reservas online e um painel completo para o dia a dia da empresa.
                        Isso faz com que a locadora ganhe eficiência operacional e também uma vitrine para captar reservas pela internet.
                    </p>
                </div>
                <div class="cards">
                    <article class="card">
                        <i class="fas fa-car-side"></i>
                        <h3>Gestao completa</h3>
                        <p>Reservas, frota, check-in, financeiro, multas e acompanhamento operacional em uma unica plataforma.</p>
                    </article>
                    <article class="card">
                        <i class="fas fa-store"></i>
                        <h3>Site de vendas</h3>
                        <p>Um site de reservas para a locadora vender online com visual profissional, pronto para converter acessos em aluguel.</p>
                    </article>
                    <article class="card">
                        <i class="fas fa-palette"></i>
                        <h3>Marca da empresa</h3>
                        <p>Personalize cores, textos e imagens para deixar o site com a identidade visual da locadora.</p>
                    </article>
                    <article class="card">
                        <i class="fas fa-chart-line"></i>
                        <h3>Escala de vendas</h3>
                        <p>Mais visibilidade, mais conveniencia para o cliente e mais chances de aumentar o volume de reservas.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container split">
                <div>
                    <div class="section-head">
                        <div class="tag">Para quem foi feito</div>
                        <h2>Feito para locadoras que querem crescer com previsibilidade</h2>
                        <p class="lead">
                            A DeCars atende desde operações menores que precisam de controle e agilidade ate locadoras que querem um produto mais completo, com canal de vendas próprio e potencial de escala.
                        </p>
                    </div>
                    <div class="panel">
                        <ul>
                            <li><i class="fas fa-check"></i> Para quem quer parar de depender de planilhas e processos soltos.</li>
                            <li><i class="fas fa-check"></i> Para quem precisa de um sistema que organize a operação e reduza erros.</li>
                            <li><i class="fas fa-check"></i> Para quem quer oferecer reservas online com mais profissionalismo.</li>
                            <li><i class="fas fa-check"></i> Para quem deseja vender mais sem abrir mao do controle administrativo.</li>
                        </ul>
                    </div>
                </div>
                <div>
                    <div class="section-head">
                        <div class="tag">Como funciona</div>
                        <h2>Uma jornada simples, da escolha ao aluguel</h2>
                        <p class="lead">
                            A experiência foi pensada para ser objetiva no site e eficiente no admin, para que a locadora tenha mais conversao e menos atrito.
                        </p>
                    </div>
                    <div class="timeline">
                        <article class="step">
                            <div class="step-num">1</div>
                            <h3>O cliente encontra o carro</h3>
                            <p>O site de reservas apresenta a frota e facilita a busca pelo veiculo ideal.</p>
                        </article>
                        <article class="step">
                            <div class="step-num">2</div>
                            <h3>A locadora organiza a operacao</h3>
                            <p>O sistema ajuda a controlar reservas, entrega, devolucao, financeiro e pendencias.</p>
                        </article>
                        <article class="step">
                            <div class="step-num">3</div>
                            <h3>O negócio ganha escala</h3>
                            <p>Com site proprio, a locadora aumenta a chance de captar novos alugueis pela internet.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="cta">
                    <div>
                        <div class="tag" style="border-color: rgba(255,255,255,.18); color: #fff; background: rgba(255,255,255,.08);">Pronto para começar?</div>
                        <h2>Conheca a DeCars e veja como o site de vendas pode ajudar sua locadora</h2>
                        <p>
                            Se voce quer um software com foco em operacao e tambem em vendas, a DeCars foi pensada para esse modelo. Entre em contato, conheca os planos e veja como a sua locadora pode ganhar mais presenca digital.
                        </p>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:.75rem; min-width:260px;">
                        <a class="btn btn-primary" href="{{ route('site.apresentacao') }}" style="background:#fff; color:var(--bg);">Voltar para a home</a>
                        <a class="btn btn-ghost" href="{{ route('site.apresentacao') }}#planos" style="border-color: rgba(255,255,255,.2); color:#fff; background:transparent;">Ver planos</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container footer-inner">
            <a class="footer-brand" href="https://www.sonydtech.com" target="_blank" rel="noopener noreferrer">
                <img src="{{ asset('assets/imgs/logo-sonydtech.webp') }}" alt="Logo Sonyd Tech">
                <span>Software da Sonyd Tech</span>
            </a>
            <small>© {{ $anoAtual }} DeCars. Todos os direitos reservados.</small>
        </div>
    </footer>
</body>
</html>
