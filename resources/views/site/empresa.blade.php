<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $empresa->nome }}</title>
    <meta name="description" content="{{ $empresa->site_descricao ? \Illuminate\Support\Str::limit(strip_tags($empresa->site_descricao), 150) : $empresa->nome.' — rede de escolas' }}">
    @if($empresa->logo_url)
        <link rel="icon" href="{{ $empresa->logo_url }}">
    @endif
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @php
        $cor1 = $empresa->cor_primaria ?: '#1e3a8a';
        $cor2 = $empresa->cor_secundaria ?: '#f59e0b';
        $whats = preg_replace('/\D/', '', (string) $empresa->site_whatsapp);
        $insta = ltrim((string) $empresa->site_instagram, '@/ ');
        $mostrarUnidades = $empresa->site_unidades === 'S' && $escolas->isNotEmpty();
        $mostrarMatricula = $empresa->site_matricula === 'S';
        $mostrarContato = $empresa->site_contato === 'S'
            && ($empresa->telefone || $empresa->email || $whats || $insta || $empresa->endereco);
    @endphp
    <style>
        :root{
            --c1:{{ $cor1 }}; --c2:{{ $cor2 }};
            --ink:#0f172a; --muted:#64748b; --bg:#f8fafc; --card:#fff; --line:#e2e8f0;
        }
        *{margin:0;padding:0;box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{font-family:'Figtree',sans-serif;background:var(--bg);color:var(--ink);line-height:1.6}
        a{text-decoration:none;color:inherit}
        .wrap{max-width:1100px;margin:0 auto;padding:0 1.25rem}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.7rem 1.4rem;border-radius:11px;font-weight:700;font-size:.92rem;transition:transform .15s,box-shadow .15s;border:0}
        .btn:hover{transform:translateY(-1px)}
        .btn-p{background:var(--c2);color:#fff;box-shadow:0 6px 18px color-mix(in srgb,var(--c2) 35%,transparent)}
        .btn-g{background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.3)}

        header{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.9);backdrop-filter:blur(10px);border-bottom:1px solid var(--line)}
        .nav{display:flex;align-items:center;justify-content:space-between;height:66px}
        .brand{display:flex;align-items:center;gap:.6rem;font-weight:800;font-size:1.1rem;color:var(--ink)}
        .brand img{height:38px;width:auto;object-fit:contain}
        .nav-cta{display:flex;align-items:center;gap:.6rem}
        .nav-cta .btn{padding:.55rem 1.15rem}
        .btn-o{background:var(--c1);color:#fff}

        .hero{background:linear-gradient(135deg,var(--c1) 0%,color-mix(in srgb,var(--c1) 72%,#312e81) 100%);color:#fff;padding:4.5rem 0}
        .hero-in{display:flex;align-items:center;gap:2.5rem;flex-wrap:wrap}
        .hero-txt{flex:1;min-width:280px}
        .hero-tag{display:inline-block;font-size:.78rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;background:rgba(255,255,255,.15);padding:.35rem .85rem;border-radius:999px;margin-bottom:1rem}
        .hero h1{font-size:clamp(1.9rem,4.5vw,3rem);font-weight:800;line-height:1.12;letter-spacing:-.02em}
        .hero .lead{margin-top:1rem;font-size:1.05rem;opacity:.9;max-width:560px}
        .hero-ctas{display:flex;gap:.7rem;margin-top:1.6rem;flex-wrap:wrap}
        .hero-logo{width:120px;height:120px;border-radius:1.4rem;background:#fff;object-fit:contain;padding:.8rem;box-shadow:0 16px 40px rgba(0,0,0,.25);flex-shrink:0}

        section{padding:3.5rem 0}
        .sec-head{text-align:center;margin-bottom:2.2rem}
        .sec-head h2{font-size:clamp(1.5rem,3vw,2.1rem);font-weight:800;letter-spacing:-.02em}
        .sec-head p{color:var(--muted);margin-top:.5rem}

        .grid-unidades{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.1rem}
        .unidade{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:1.3rem;display:flex;flex-direction:column;gap:.45rem;transition:transform .15s,box-shadow .15s}
        .unidade:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(15,23,42,.09)}
        .unidade h3{font-size:1.05rem;font-weight:800;color:var(--c1)}
        .unidade .meta{font-size:.85rem;color:var(--muted);display:flex;gap:.45rem;align-items:flex-start}
        .unidade .meta i{color:var(--c2);margin-top:.2rem}
        .unidade .btn{margin-top:auto;align-self:flex-start;padding:.5rem 1rem;font-size:.82rem}

        .sobre{max-width:760px;margin:0 auto;text-align:center;color:#334155;font-size:1.03rem}

        .contato-cards{display:flex;justify-content:center;gap:1rem;flex-wrap:wrap}
        .contato-card{display:flex;align-items:center;gap:.8rem;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:.9rem 1.2rem;transition:transform .15s}
        .contato-card:hover{transform:translateY(-2px)}
        .contato-card i{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;background:color-mix(in srgb,var(--c1) 10%,#fff);color:var(--c1);flex-shrink:0}
        .contato-card .lbl{font-size:.72rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em}
        .contato-card .val{font-size:.92rem;font-weight:700;color:var(--ink)}

        footer{background:#0b1530;color:#94a3b8;padding:1.6rem 0;text-align:center;font-size:.82rem}
        footer .declass{color:#cbd5e1;font-weight:700}

        @media (max-width:640px){
            .hero{padding:3rem 0}
            .hero-logo{width:84px;height:84px}
            section{padding:2.5rem 0}
        }
    </style>
</head>
<body>

<header>
    <div class="wrap nav">
        <span class="brand">
            @if($empresa->logo_url)<img src="{{ $empresa->logo_url }}" alt="{{ $empresa->nome }}">@endif
            {{ $empresa->nome }}
        </span>
        <div class="nav-cta">
            <a href="{{ route('login') }}" class="btn btn-o"><i class="fas fa-right-to-bracket"></i> Entrar</a>
        </div>
    </div>
</header>

<div class="hero">
    <div class="wrap hero-in">
        <div class="hero-txt">
            <span class="hero-tag">Rede de escolas</span>
            <h1>{{ $empresa->site_titulo ?: $empresa->nome }}</h1>
            @if($empresa->site_descricao)
                <p class="lead">{{ \Illuminate\Support\Str::limit($empresa->site_descricao, 220) }}</p>
            @endif
            <div class="hero-ctas">
                @if($mostrarUnidades)
                    <a href="#unidades" class="btn btn-p"><i class="fas fa-school"></i> Nossas unidades</a>
                @endif
                @if($mostrarContato)
                    <a href="#contato" class="btn btn-g"><i class="fas fa-phone"></i> Fale conosco</a>
                @endif
            </div>
        </div>
        @if($empresa->logo_url)
            <img class="hero-logo" src="{{ $empresa->logo_url }}" alt="{{ $empresa->nome }}">
        @endif
    </div>
</div>

@if($mostrarUnidades)
<section id="unidades">
    <div class="wrap">
        <div class="sec-head">
            <h2>Nossas unidades</h2>
            <p>{{ $escolas->count() }} {{ \Illuminate\Support\Str::plural('unidade', $escolas->count()) }} atendendo sua família</p>
        </div>
        <div class="grid-unidades">
            @foreach($escolas as $escola)
                <div class="unidade">
                    <h3><i class="fas fa-school" style="color:var(--c2);margin-right:.35rem;"></i>{{ $escola->nome }}</h3>
                    @if($escola->cidade || $escola->estado)
                        <div class="meta"><i class="fas fa-location-dot"></i><span>{{ $escola->cidade }}{{ $escola->cidade && $escola->estado ? ' — ' : '' }}{{ $escola->estado }}</span></div>
                    @endif
                    @if($escola->endereco)
                        <div class="meta"><i class="fas fa-map"></i><span>{{ $escola->endereco }}</span></div>
                    @endif
                    @if($escola->telefone)
                        <div class="meta"><i class="fas fa-phone"></i><span>{{ $escola->telefone }}</span></div>
                    @endif
                    @if($mostrarMatricula)
                        <a href="{{ route('matricula_publica.create', [$empresa->slug, $escola->slug]) }}" class="btn btn-p"><i class="fas fa-user-plus"></i> Matrícula</a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($empresa->site_descricao && $empresa->site_titulo)
<section>
    <div class="wrap sobre">
        <div class="sec-head"><h2>Sobre nós</h2></div>
        <p>{{ $empresa->site_descricao }}</p>
    </div>
</section>
@endif

@if($mostrarContato)
<section id="contato" style="background:#fff;border-top:1px solid var(--line)">
    <div class="wrap">
        <div class="sec-head">
            <h2>Fale conosco</h2>
            <p>Estamos prontos para atender você</p>
        </div>
        <div class="contato-cards">
            @if($whats)
                <a class="contato-card" href="https://wa.me/55{{ $whats }}" target="_blank" rel="noopener">
                    <i class="fab fa-whatsapp"></i>
                    <span><span class="lbl">WhatsApp</span><br><span class="val">{{ $empresa->site_whatsapp }}</span></span>
                </a>
            @endif
            @if($empresa->telefone)
                <a class="contato-card" href="tel:{{ preg_replace('/\D/', '', $empresa->telefone) }}">
                    <i class="fas fa-phone"></i>
                    <span><span class="lbl">Telefone</span><br><span class="val">{{ $empresa->telefone }}</span></span>
                </a>
            @endif
            @if($empresa->email)
                <a class="contato-card" href="mailto:{{ $empresa->email }}">
                    <i class="fas fa-envelope"></i>
                    <span><span class="lbl">E-mail</span><br><span class="val">{{ $empresa->email }}</span></span>
                </a>
            @endif
            @if($insta)
                <a class="contato-card" href="https://instagram.com/{{ $insta }}" target="_blank" rel="noopener">
                    <i class="fab fa-instagram"></i>
                    <span><span class="lbl">Instagram</span><br><span class="val">{{ '@'.$insta }}</span></span>
                </a>
            @endif
            @if($empresa->endereco)
                <div class="contato-card">
                    <i class="fas fa-location-dot"></i>
                    <span><span class="lbl">Endereço</span><br><span class="val">{{ $empresa->endereco }}</span></span>
                </div>
            @endif
        </div>
    </div>
</section>
@endif

<footer>
    <div class="wrap">
        © {{ date('Y') }} {{ $empresa->nome }} · Plataforma <span class="declass">DeClass</span> — Gestão Escolar
    </div>
</footer>

</body>
</html>
