<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }} | {{ $empresa->nome }}</title>
    <meta name="description" content="{{ $descricao }}">
    <link rel="icon" href="{{ $empresa->logo_url ?: asset('assets/imgs/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        *,:after,:before{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        :root{
            --p:{{$corPrimaria}};--s:{{$corSecundaria}};--a:{{$corTerciaria}};--pt:{{$corTextoPrimario}};
            --tb:{{$corTopbarFundo}};--tt:{{$corTopbarTexto}};--th:{{$corTopbarLinkHover}};
            --hb:{{$corHeroFundo}};--ht:{{$corHeroTexto}};
            --fb:{{$corFormFundo}};--ft:{{$corFormTexto}};
            --frota-b:{{$corFrotaFundo}};--frota-e:{{$corFrotaEtiquetaTexto}};--frota-t:{{$corFrotaTituloTexto}};--frota-d:{{$corFrotaDescricaoTexto}};--frota-ct:{{$corFrotaCardTituloTexto}};--frota-cd:{{$corFrotaCardDescricaoTexto}};--frota-cb:{{$corFrotaCardFundo}};
            --vant-b:{{$corVantagensFundo}};--vant-e:{{$corVantagensEtiquetaTexto}};--vant-t:{{$corVantagensTituloTexto}};--vant-d:{{$corVantagensDescricaoTexto}};--vant-i:{{$corVantagensItemTexto}};
            --etap-b:{{$corEtapasFundo}};--etap-e:{{$corEtapasEtiquetaTexto}};--etap-t:{{$corEtapasTituloTexto}};--etap-d:{{$corEtapasDescricaoTexto}};--etap-pt:{{$corEtapasPassoTexto}};--etap-pd:{{$corEtapasPassoDescricaoTexto}};--etap-pb:{{$corEtapasPassoFundo}};
            --cont-b:{{$corContatoFundo}};--cont-e:{{$corContatoEtiquetaTexto}};--cont-t:{{$corContatoTituloTexto}};--cont-i:{{$corContatoInfoTexto}};
            --foot-b:{{$corFooterFundo}};--foot-t:{{$corFooterTexto}};
            --b1-b:{{$corBotaoPrimarioFundo}};--b1-t:{{$corBotaoPrimarioTexto}};--b2-b:{{$corBotaoSecundarioFundo}};--b2-t:{{$corBotaoSecundarioTexto}};
            --ink:#0f172a;--ink2:#334155;--ink3:#64748b;--line:#e2e8f0;--white:#fff;--off:#f8fafc;
            --r:16px;--r-lg:24px;
            --sh:0 4px 24px rgba(0,0,0,.06);--sh-md:0 8px 40px rgba(0,0,0,.08);--sh-lg:0 20px 60px rgba(0,0,0,.12);
            --w:1200px
        }
        body{font-family:Inter,sans-serif;color:var(--ink);background:var(--white);-webkit-font-smoothing:antialiased}
        a{text-decoration:none;color:inherit}
        img{display:block;max-width:100%}
        .wrap{width:min(var(--w),calc(100% - 48px));margin-inline:auto}

        .topbar{position:sticky;top:0;z-index:100;background:var(--tb);border-bottom:1px solid transparent;transition:all .3s}
        .topbar-inner{display:flex;align-items:center;justify-content:space-between;height:72px;gap:20px}
        .brand{display:flex;align-items:center;gap:10px;font-family:Outfit,sans-serif;font-weight:700;font-size:1.15rem;color:var(--tt);flex-shrink:0}
        .brand img{height:34px;width:auto}
        .nav{display:flex;align-items:center;gap:4px}
        .nav a{padding:8px 18px;border-radius:var(--r);font-size:.84rem;font-weight:500;color:var(--tt);transition:all .2s}
        .nav a:hover{background:var(--th)}
        .nav-cta{padding:10px 24px;border-radius:var(--r);font-size:.85rem;font-weight:600;background:var(--b1-b);color:var(--b1-t);transition:all .2s;flex-shrink:0}
        .nav-cta:hover{transform:translateY(-2px);box-shadow:var(--sh-md)}

        .hero{background:var(--hb);color:var(--ht);padding:100px 24px;position:relative;overflow:hidden}
        .hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 80% 40%,rgba(255,255,255,.06) 0%,transparent 60%);pointer-events:none}
        .hero .wrap{position:relative;z-index:1;display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center}
        .hero-tag{font-family:Outfit,sans-serif;font-size:.7rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--a);margin-bottom:20px;display:inline-flex;align-items:center;gap:8px}
        .hero-tag::before{content:'';display:block;width:24px;height:2px;background:var(--a)}
        .hero-title{font-family:Outfit,sans-serif;font-size:clamp(2.2rem,3.6vw+1rem,4rem);font-weight:800;line-height:1;margin-bottom:16px;letter-spacing:-.03em}
        .hero-sub{font-size:1.05rem;line-height:1.5;opacity:.75;font-weight:500;margin-bottom:6px}
        .hero-desc{font-size:.92rem;line-height:1.7;opacity:.5;margin-bottom:36px;max-width:44ch}
        .hero-actions{display:flex;gap:12px;flex-wrap:wrap}
        .hero-btn{padding:16px 36px;border-radius:var(--r);font-weight:600;font-size:.9rem;border:none;cursor:pointer;transition:all .25s;display:inline-flex;align-items:center;gap:8px}
        .hero-btn-p{background:var(--b1-b);color:var(--b1-t);box-shadow:0 4px 14px color-mix(in srgb,var(--b1-b) 35%,transparent)}
        .hero-btn-s{background:var(--b2-b);color:var(--b2-t);border:1px solid rgba(255,255,255,.15)}
        .hero-btn:hover{transform:translateY(-3px);box-shadow:var(--sh-lg)}

        .hero-form{background:var(--fb);color:var(--ft);border-radius:var(--r-lg);padding:36px;box-shadow:var(--sh-lg)}
        .hero-form h2{font-family:Outfit,sans-serif;font-size:1.2rem;font-weight:700;margin-bottom:4px}
        .hero-form .desc{font-size:.84rem;opacity:.55;margin-bottom:24px}
        .hero-form form{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .hero-form .full{grid-column:1/-1}
        .hero-form label{font-size:.76rem;font-weight:600;display:block;margin-bottom:4px;opacity:.7}
        .hero-form select,.hero-form input{padding:12px 14px;border:2px solid var(--line);border-radius:12px;font-size:.85rem;width:100%;background:var(--white);color:var(--ink);outline:none;transition:border-color .2s}
        .hero-form select:focus,.hero-form input:focus{border-color:var(--p)}
        .hero-form .btn-book{padding:14px 28px;border:none;border-radius:12px;background:var(--b1-b);color:var(--b1-t);font-weight:600;font-size:.88rem;cursor:pointer;transition:all .2s;grid-column:1/-1;justify-self:start;box-shadow:0 4px 14px color-mix(in srgb,var(--b1-b) 35%,transparent)}
        .hero-form .btn-book:hover{transform:translateY(-2px);box-shadow:0 8px 24px color-mix(in srgb,var(--b1-b) 40%,transparent)}
        .hero-form small{font-size:.7rem;opacity:.5;display:block;margin-top:3px}
        .hero-form .note{font-size:.75rem;opacity:.5;margin-top:12px;grid-column:1/-1;text-align:center}

        .sec{padding:100px 24px}
        .sec--alt{background:var(--off)}
        .sec--frota{background:var(--frota-b)}
        .sec--vant{background:var(--vant-b);position:relative}
        .sec--vant::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--line),transparent)}
        .sec--etapas{background:var(--etap-b)}
        .sec-head{text-align:center;margin-bottom:56px;max-width:600px;margin-inline:auto}
        .sec-head .eyebrow{font-family:Outfit,sans-serif;font-size:.65rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--a);margin-bottom:12px}
        .sec--frota .eyebrow{color:var(--frota-e)}.sec--vant .eyebrow{color:var(--vant-e)}.sec--etapas .eyebrow{color:var(--etap-e)}
        .sec-head h2{font-family:Outfit,sans-serif;font-size:clamp(1.8rem,2.8vw+.6rem,3rem);font-weight:800;letter-spacing:-.03em;line-height:1.05;margin-bottom:12px}
        .sec--frota .sec-head h2{color:var(--frota-t)}.sec--vant .sec-head h2{color:var(--vant-t)}.sec--etapas .sec-head h2{color:var(--etap-t)}
        .sec-head p{font-size:.92rem;line-height:1.7;color:var(--ink3)}
        .sec--frota .sec-head p{color:var(--frota-d)}.sec--vant .sec-head p{color:var(--vant-d)}.sec--etapas .sec-head p{color:var(--etap-d)}

        .fleet-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
        .fleet-card{background:var(--frota-cb);border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh);transition:all .35s;position:relative}
        .fleet-card:hover{transform:translateY(-8px);box-shadow:var(--sh-lg)}
        .fleet-card::after{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--p),var(--a));opacity:0;transition:opacity .35s}
        .fleet-card:hover::after{opacity:1}
        .fleet-card-img{width:100%;height:200px;object-fit:cover}
        .fleet-card-img-placeholder{height:200px;background:linear-gradient(135deg,var(--off),var(--line));display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--ink3);opacity:.25}
        .fleet-card-body{padding:24px 24px 28px}
        .fleet-card-num{font-family:Outfit,sans-serif;font-size:.7rem;font-weight:700;color:var(--a);margin-bottom:6px;letter-spacing:.04em}
        .fleet-card-title{font-family:Outfit,sans-serif;font-size:1.1rem;font-weight:700;color:var(--frota-ct);margin-bottom:6px;letter-spacing:-.02em}
        .fleet-card-desc{font-size:.86rem;line-height:1.65;color:var(--frota-cd)}

        .vant-strip{display:flex;gap:16px;overflow-x:auto;padding:8px 0 16px;scroll-snap-type:x mandatory;flex-wrap: wrap}
        .vant-strip::-webkit-scrollbar{height:6px}
        .vant-strip::-webkit-scrollbar-thumb{background:var(--line);border-radius:3px}
        .vant-chip{scroll-snap-align:start;flex-shrink:0;padding:20px 32px;background:var(--white);border-radius:var(--r-lg);box-shadow:var(--sh);display:flex;align-items:center;gap:14px;transition:all .25s;min-width:280px;border:1px solid var(--line)}
        .vant-chip:hover{transform:translateY(-4px);box-shadow:var(--sh-md);border-color:var(--a)}
        .vant-chip-icon{width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;color:var(--pt);font-size:1.1rem;font-weight:700;flex-shrink:0}
        .vant-chip-text{font-size:.9rem;font-weight:500;color:var(--vant-i);line-height:1.4;white-space:nowrap}

        .steps-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;position:relative}
        .steps-grid::before{content:'';position:absolute;top:32px;left:16%;right:16%;height:2px;background:linear-gradient(90deg,var(--p),var(--a),var(--p));border-radius:1px;z-index:0}
        .step-card{background:var(--etap-pb);border-radius:var(--r-lg);padding:32px 24px 28px;text-align:center;box-shadow:var(--sh);position:relative;z-index:1;transition:all .3s;border:1px solid var(--line)}
        .step-card:hover{transform:translateY(-6px);box-shadow:var(--sh-md)}
        .step-num{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--s));color:var(--pt);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-family:Outfit,sans-serif;font-size:1.2rem;font-weight:800;box-shadow:0 6px 20px color-mix(in srgb,var(--p) 30%,transparent)}
        .step-card-title{font-family:Outfit,sans-serif;font-size:1.05rem;font-weight:700;color:var(--etap-pt);margin-bottom:8px;letter-spacing:-.02em}
        .step-card-desc{font-size:.86rem;line-height:1.65;color:var(--etap-pd);max-width:28ch;margin-inline:auto}

        .contato{background:var(--cont-b);padding:100px 24px}
        .contato .wrap{display:grid;grid-template-columns:1.2fr .8fr;gap:56px;align-items:center}
        .contato-eyebrow{font-family:Outfit,sans-serif;font-size:.65rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--cont-e);margin-bottom:12px}
        .contato-title{font-family:Outfit,sans-serif;font-size:clamp(1.6rem,2.4vw+.6rem,2.6rem);font-weight:800;color:var(--cont-t);line-height:1.05;margin-bottom:20px;letter-spacing:-.03em}
        .contato-info{display:flex;flex-wrap:wrap;gap:8px 24px}
        .contato-info span{font-size:.92rem;color:var(--cont-i);display:flex;align-items:center;gap:8px}
        .contato-info span::before{content:'◆';font-size:.5rem;color:var(--a)}
        .contato-btn{display:inline-flex;align-items:center;gap:10px;margin-top:28px;padding:16px 36px;border-radius:var(--r);font-weight:600;font-size:.9rem;background:var(--b1-b);color:var(--b1-t);border:none;cursor:pointer;transition:all .25s;box-shadow:0 4px 14px color-mix(in srgb,var(--b1-b) 35%,transparent)}
        .contato-btn:hover{transform:translateY(-3px);box-shadow:0 8px 28px color-mix(in srgb,var(--b1-b) 40%,transparent)}
        .contato-image{border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh-lg)}
        .contato-image img{width:100%;height:380px;object-fit:cover}
        .contato-image-placeholder{height:380px;background:linear-gradient(135deg,var(--off),var(--line));display:flex;align-items:center;justify-content:center;font-size:4rem;color:var(--ink3);opacity:.15}

        .footer{background:var(--foot-b);color:var(--foot-t);padding:40px 24px;border-top:1px solid var(--line)}
        .footer .wrap{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;font-size:.84rem}
        .footer-copy{opacity:.5}
        .footer-nav{display:flex;gap:24px}
        .footer-nav a{opacity:.4;transition:opacity .2s}
        .footer-nav a:hover{opacity:.8}

        @media(max-width:768px){
            .hero .wrap{grid-template-columns:1fr;gap:40px}
            .hero{padding:60px 20px 48px}
            .nav{display:none}
            .fleet-grid{grid-template-columns:1fr}
            .steps-grid{grid-template-columns:1fr}
            .steps-grid::before{display:none}
            .contato .wrap{grid-template-columns:1fr;gap:32px}
            .hero-form form{grid-template-columns:1fr}
            .hero-btn{padding:14px 28px}
            .hero-form{padding:24px}
            .sec{padding:64px 20px}
            .vant-chip{min-width:240px}
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="wrap"><div class="topbar-inner">
            <a href="#" class="brand">
                @if(!empty($empresa->logo_url))
                    <img src="{{ $empresa->logo_url }}" alt="{{ $empresa->nome }}">
                @else
                    {{ $empresa->nome }}
                @endif
            </a>
            <nav class="nav">
                <a href="#vantagens">{{ $menuItemVantagens }}</a>
                <a href="#frota">{{ $menuItemFrota }}</a>
                <a href="#etapas">{{ $menuItemEtapas }}</a>
                <a href="#contato">{{ $menuItemContato }}</a>
            </nav>
            <a href="{{ $botaoUrl }}" class="nav-cta">{{ $botaoTexto }}</a>
        </div></div>
    </header>

    <section class="hero">
        <div class="wrap">
            <div>
                @if(!empty($heroTag))<div class="hero-tag">{{ $heroTag }}</div>@endif
                <h1 class="hero-title">{{ $titulo }}</h1>
                @if(!empty($subtitulo))<div class="hero-sub">{{ $subtitulo }}</div>@endif
                @if(!empty($descricao))<p class="hero-desc">{{ $descricao }}</p>@endif
                <div class="hero-actions">
                    <a href="{{ $botaoUrl }}" class="hero-btn hero-btn-p">{{ $botaoTexto }}</a>
                    <a href="{{ $botaoLoginUrl }}" class="hero-btn hero-btn-s">{{ $botaoAdminTexto }}</a>
                </div>
            </div>
            @if(!empty($heroFormTitulo))
            <div class="hero-form">
                <h2>{{ $heroFormTitulo }}</h2>
                @if(!empty($heroFormDescricao))<p class="desc">{{ $heroFormDescricao }}</p>@endif
                <form method="GET" action="{{ $botaoUrl }}">
                    <div class="full">
                        <label for="retirada">{{ $heroFormLabelRetirada }}</label>
                        <select id="retirada" name="retirada">
                            <option value="">Selecione</option>
                            @foreach($estacoesRetirada as $id => $nome)
                                <option value="{{ $id }}">{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="data_retirada">{{ $heroFormLabelDataRetirada }}</label>
                        <input id="data_retirada" name="data_retirada" type="text" readonly placeholder="Data">
                        <small id="data_retirada_feedback">Selecione a estação</small>
                    </div>
                    <div>
                        <label for="hora_retirada">{{ $heroFormLabelHoraRetirada }}</label>
                        <select id="hora_retirada" name="hora_retirada" disabled>
                            <option value="">Data</option>
                        </select>
                        <small id="hora_retirada_feedback">Escolha a data</small>
                    </div>
                    <div class="full" id="devolucaoField">
                        <label for="devolucao">{{ $heroFormLabelDevolucao ?? 'Devolução' }}</label>
                        <select id="devolucao" name="devolucao">
                            <option value="">Selecione</option>
                            @foreach($estacoesDevolucao as $id => $nome)
                                <option value="{{ $id }}">{{ $nome }}</option>
                            @endforeach
                        </select>
                        <small id="devolucao_feedback">Selecione a estação</small>
                    </div>
                    <div>
                        <label for="data_devolucao">{{ $heroFormLabelDataDevolucao }}</label>
                        <input id="data_devolucao" name="data_devolucao" type="text" readonly placeholder="Data">
                        <small id="data_devolucao_feedback">Selecione a estação</small>
                    </div>
                    <div>
                        <label for="hora_devolucao">{{ $heroFormLabelHoraDevolucao }}</label>
                        <select id="hora_devolucao" name="hora_devolucao" disabled>
                            <option value="">Data</option>
                        </select>
                        <small id="hora_devolucao_feedback">Escolha a data</small>
                    </div>
                    <button type="submit" class="btn-book">{{ $heroFormTextoBotao }}</button>
                    @if(!empty($heroFormNota))<div class="note">{{ $heroFormNota }}</div>@endif
                </form>
            </div>
            @endif
        </div>
    </section>

    

    <section id="frota" class="sec sec--frota">
        <div class="wrap">
            <div class="sec-head">
                @if(!empty($frotaLabelSuperior))<div class="eyebrow">{{ $frotaLabelSuperior }}</div>@endif
                <h2>{{ $frotaTitulo }}</h2>
                @if(!empty($frotaDescricao))<p>{{ $frotaDescricao }}</p>@endif
            </div>
            <div class="fleet-grid">
                @for($fi = 1; $fi <= 10; $fi++)
                    @php $fCard = $frotaDestaques[$fi - 1] ?? null; $fv = $fCard && !empty($fCard['titulo']); @endphp
                    <article class="fleet-card" data-frota="{{ $fi }}" style="{{ $fv ? '' : 'display:none' }}">
                        @if($fCard && !empty($fCard['imagem']))
                            <img src="{{ $fCard['imagem'] }}" alt="{{ $fCard['titulo'] }}" class="fleet-card-img" data-frota-img="{{ $fi }}">
                        @else
                            <div class="fleet-card-img-placeholder">✦</div>
                        @endif
                        <div class="fleet-card-body">
                            <div class="fleet-card-num">{{ str_pad($fi,2,'0',STR_PAD_LEFT) }}</div>
                            <h3 class="fleet-card-title" data-frota-titulo="{{ $fi }}">{{ $fCard['titulo'] ?? '' }}</h3>
                            <p class="fleet-card-desc" data-frota-desc="{{ $fi }}">{{ $fCard['descricao'] ?? '' }}</p>
                        </div>
                    </article>
                @endfor
            </div>
        </div>
    </section>

    <section id="vantagens" class="sec sec--vant">
        <div class="wrap">
            <div class="sec-head">
                @if(!empty($vantagensLabelSuperior))<div class="eyebrow">{{ $vantagensLabelSuperior }}</div>@endif
                <h2>{{ $vantagensTitulo }}</h2>
                @if(!empty($vantagensDescricao))<p>{{ $vantagensDescricao }}</p>@endif
            </div>
            <div class="vant-strip">
                @foreach($beneficios as $beneficio)
                    @if(!empty($beneficio))
                        <div class="vant-chip">
                            <span class="vant-chip-icon">✦</span>
                            <span class="vant-chip-text">{{ $beneficio }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <section id="como-funciona" class="sec sec--etapas">
        <div class="wrap">
            <div class="sec-head">
                @if(!empty($etapasLabelSuperior))<div class="eyebrow">{{ $etapasLabelSuperior }}</div>@endif
                <h2>{{ $etapasTitulo }}</h2>
            </div>
            <div class="steps-grid">
                @foreach($etapasReserva as $idx => $etapa)
                    @if(!empty($etapa['titulo']))
                    <div class="step-card">
                        <div class="step-num">{{ $idx + 1 }}</div>
                        <h4 class="step-card-title">{{ $etapa['titulo'] }}</h4>
                        <p class="step-card-desc">{{ $etapa['descricao'] ?? '' }}</p>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <section id="contato" class="contato">
        <div class="wrap">
            <div>
                @if(!empty($contatoLabelSuperior))<div class="contato-eyebrow">{{ $contatoLabelSuperior }}</div>@endif
                <div class="contato-title">{{ $contatoTitulo }}</div>
                <div class="contato-info">
                    @if(!empty($contatoTelefone))<span>{{ $contatoTelefone }}</span>@endif
                    @if(!empty($contatoEmail))<span>{{ $contatoEmail }}</span>@endif
                </div>
                <a href="{{ $botaoUrl }}" class="contato-btn">{{ $botaoTexto }}</a>
            </div>
            <div class="contato-image">
                @if(!empty($imagemContato))
                    <img src="{{ $imagemContato }}" alt="Contato {{ $empresa->nome }}">
                @else
                    <div class="contato-image-placeholder">✦</div>
                @endif
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="wrap">
            <div class="footer-copy">{{ $footerTexto }}</div>
            <div class="footer-nav">
                <a href="#frota">{{ $menuItemFrota }}</a>
                <a href="#vantagens">{{ $menuItemVantagens }}</a>
                <a href="#etapas">{{ $menuItemEtapas }}</a>
                <a href="#contato">{{ $menuItemContato }}</a>
            </div>
        </div>
    </footer>

    <script>
        (function(){
            if(!window.parent||window.parent===window)return;
            document.addEventListener('click',function(e){
                var l=e.target.closest('a');if(l&&l.getAttribute('href')&&l.getAttribute('href')!=='#')e.preventDefault();
            },true);
            document.querySelectorAll('form').forEach(function(f){f.addEventListener('submit',function(e){e.preventDefault()})});

            var m={
                cor_primaria:'--p',cor_secundaria:'--s',cor_terciaria:'--a',
                cor_topbar_fundo:'--tb',cor_topbar_texto:'--tt',cor_topbar_link_hover:'--th',
                cor_hero_fundo:'--hb',cor_hero_texto:'--ht',cor_form_fundo:'--fb',cor_form_texto:'--ft',
                cor_frota_fundo:'--frota-b',cor_frota_etiqueta_texto:'--frota-e',cor_frota_titulo_texto:'--frota-t',cor_frota_descricao_texto:'--frota-d',
                cor_frota_card_titulo_texto:'--frota-ct',cor_frota_card_descricao_texto:'--frota-cd',cor_frota_card_fundo:'--frota-cb',
                cor_vantagens_fundo:'--vant-b',cor_vantagens_etiqueta_texto:'--vant-e',cor_vantagens_titulo_texto:'--vant-t',
                cor_vantagens_descricao_texto:'--vant-d',cor_vantagens_item_texto:'--vant-i',
                cor_etapas_fundo:'--etap-b',cor_etapas_etiqueta_texto:'--etap-e',cor_etapas_titulo_texto:'--etap-t',
                cor_etapas_descricao_texto:'--etap-d',cor_etapas_passo_texto:'--etap-pt',cor_etapas_passo_descricao_texto:'--etap-pd',cor_etapas_passo_fundo:'--etap-pb',
                cor_contato_fundo:'--cont-b',cor_contato_etiqueta_texto:'--cont-e',cor_contato_titulo_texto:'--cont-t',cor_contato_info_texto:'--cont-i',
                cor_footer_fundo:'--foot-b',cor_footer_texto:'--foot-t',
                cor_botao_primario_fundo:'--b1-b',cor_botao_primario_texto:'--b1-t',cor_botao_secundario_fundo:'--b2-b',cor_botao_secundario_texto:'--b2-t',
            };

            window.addEventListener('message',function(e){
                if(!e.data||e.data.type!=='se-carregar'&&e.data.type!=='se-atualizar')return;
                var d=e.data.dados||{},du=e.data.dataUrls||{},rt=document.documentElement;
                for(var k in m){if(d[k])rt.style.setProperty(m[k],d[k])}
                if(d.cor_primaria){var b=(function(h){h=h.replace('#','');return(parseInt(h.substr(0,2),16)*299+parseInt(h.substr(2,2),16)*587+parseInt(h.substr(4,2),16)*114)/1000})(d.cor_primaria);rt.style.setProperty('--pt',b>=155?'#0F172A':'#FFFFFF')}
                for(var fi=1;fi<=10;fi++){
                    var c=document.querySelector('.fleet-card[data-frota="'+fi+'"]');if(!c)continue;
                    var tk='frota_card_'+fi+'_titulo',dk='frota_card_'+fi+'_descricao',t=d[tk],de=d[dk];
                    var te=c.querySelector('[data-frota-titulo="'+fi+'"]'),de2=c.querySelector('[data-frota-desc="'+fi+'"]');
                    if(t&&t.trim()){c.style.display='';if(te)te.textContent=t;if(de2)de2.textContent=de||''}
                    else c.style.display='none'
                }
                var im={imagem_hero:'img'};for(var ik in im){if(!du[ik])continue;var ie=document.querySelector(im[ik]);if(ie)ie.src=du[ik]}
            });
            window.parent.postMessage({type:'se-pronto'},'*');
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script>
        (function(){const R=document.getElementById('retirada'),D=document.getElementById('devolucao'),DF=document.getElementById('devolucaoField'),DR=document.getElementById('data_retirada'),HR=document.getElementById('hora_retirada'),DD=document.getElementById('data_devolucao'),HD=document.getElementById('hora_devolucao'),F=DR?.closest('form'),DU=@json(route('reserva.diasSemDisponibilidade')),AU=@json(route('reserva.agendaDisponivelPorEstacao')),FR=document.getElementById('data_retirada_feedback'),FH=document.getElementById('hora_retirada_feedback'),FD=document.getElementById('data_devolucao_feedback'),FHD=document.getElementById('hora_devolucao_feedback'),FDB=document.getElementById('devolucao_feedback'),EV=new Event('change',{bubbles:true});let PR=null,PD=null;
        function tld(d){return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0')}
        function tlt(d){return String(d.getHours()).padStart(2,'0')+':'+String(d.getMinutes()).padStart(2,'0')}
        function addD(d,n){const c=new Date(d);c.setDate(c.getDate()+n);return c}
        function normD(){if(!DR||!DD)return;const n=new Date();DR.min=tld(n);const rd=DR.value?new Date(DR.value+'T00:00:00'):n,md=addD(rd,1);DD.min=tld(md);if(DD.value&&new Date(DD.value+'T00:00:00')<md)DD.value=tld(md)}
        function normT(){if(!DR||!HR)return;const n=new Date(),t=tld(n);DR.value===t?HR.min=tlt(n):HR.removeAttribute('min')}
        function valD(){if(!DR||!HR||!DD)return true;const n=new Date(),td=new Date(n.getFullYear(),n.getMonth(),n.getDate()),rd=DR.value?new Date(DR.value+'T00:00:00'):null,dd=DD.value?new Date(DD.value+'T00:00:00'):null;if(rd&&rd<td){DR.setCustomValidity('Inválido');DR.reportValidity();return false}DR.setCustomValidity('');if(rd&&rd.getTime()===td.getTime()&&HR.value&&new Date(DR.value+'T'+HR.value)<n){HR.setCustomValidity('Inválido');HR.reportValidity();return false}HR.setCustomValidity('');if(DR.value&&DD.value&&dd<addD(new Date(DR.value+'T00:00:00'),1)){DD.setCustomValidity('Inválido');DD.reportValidity();return false}DD.setCustomValidity('');return true}
        function eok(id){return!!D?.querySelector('option[value="'+id+'"]')}
        function sD(t){if(!DF||!D)return;const rid=R?.value||'',ok=eok(rid),rt=R?.selectedOptions?.[0]?.textContent?.trim()||'';DF.style.display='block';D.disabled=false;if(ok&&rid){const va=D.value;D.value=rid;if(t&&va!==rid)D.dispatchEvent(EV);if(FDB)FDB.textContent=rt?'Auto: '+rt:'.'}else if(!ok&&D.value===rid){D.value='';if(FDB)FDB.textContent='Selecione'}else if(FDB)FDB.textContent=D.value?'OK':'Selecione'}
        R?.addEventListener('change',function(){sD(true)});
        const pt=window.flatpickr?.l10ns?.pt||{};
        function toI(d){return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0')}
        function nrm(s){if(s&&typeof s==='object'){const v=String(s.value||s.hora||s.time||'').trim(),l=String(s.label||s.text||v).trim();return v?{value:v,label:l||v,taxa_extra:Boolean(s.taxa_extra||s.status==='taxa_extra')}:null}const v=String(s||'').trim();return v?{value:v,label:v,taxa_extra:false}:null}
        function preenche(se,fe,sl,hp){const l=Array.isArray(sl)?sl.map(nrm).filter(Boolean):[];se.innerHTML='';if(!l.length){se.appendChild(new Option('Indisponível',''));se.disabled=true;fe&&(fe.textContent='Sem horários');return[]}se.appendChild(new Option('Hora',''));l.forEach(function(s){const v=s.value.includes('T')?s.value.split('T')[1].slice(0,5):s.value.slice(0,5);const o=new Option(s.taxa_extra?s.label+' • Extra':s.label,v);se.appendChild(o)});se.disabled=false;const p=hp?String(hp).slice(0,5):(l.length===1?l[0].value.slice(0,5):'');se.value=p&&[...se.options].some(o=>o.value===p)?p:'';fe&&(fe.textContent='Hora');return l}
        function carrega(tipo,db,hp){const eid=tipo==='devolucao'?D?.value:R?.value;if(!eid||!db)return Promise.resolve([]);const sel=tipo==='devolucao'?HD:HR,fee=tipo==='devolucao'?FHD:FH;sel.innerHTML='<option>Carregando...</option>';sel.disabled=true;return fetch(AU+'?estacao_id='+eid+'&tipo='+tipo+'&data_base='+db+'&limite=1',{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(r){const a=Array.isArray(r.agenda)?r.agenda:[],dia=a.find(i=>String(i.date)===String(db))||a[0]||null,sl=Array.isArray(dia?.slots)?dia.slots:[];return preenche(sel,fee,sl,hp)}).catch(function(){preenche(sel,fee,[],null);return[]})}
        function buscaDias(eid,tipo){if(!eid)return Promise.resolve([]);return fetch(DU+'?estacao_id='+eid+'&dias=90&tipo='+tipo,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(r){return Array.isArray(r.dias_disponiveis)?r.dias_disponiveis:[]}).catch(function(){return[]})}
        function pickerDate(el,dias){if(!el||!window.flatpickr)return null;const ok=new Set((dias||[]).map(function(i){return String(i.data||i).slice(0,10)}));return window.flatpickr(el,{locale:pt,altInput:true,altFormat:'d/m/Y',dateFormat:'Y-m-d',disableMobile:true,minDate:'today',disable:[function(d){return!ok.has(toI(d))}],onChange:function(){el.dispatchEvent(new Event('change',{bubbles:true}))}})}
        function attMD(){const rd=DR.value?new Date(DR.value+'T00:00:00'):null;if(!rd)return;const md=new Date(rd.getTime()+86400000);if(PD){PD.set('minDate',md);const s=PD.selectedDates[0]||null;if(s&&s.getTime()<md.getTime()){PD.clear();DD.value='';HD.value='';HD.disabled=true;FD&&(FD.textContent='Inválida')}}else if(window.flatpickr)PD=window.flatpickr(DD,{locale:pt,altInput:true,altFormat:'d/m/Y',dateFormat:'Y-m-d',disableMobile:true,minDate:md,onChange:function(){DD.dispatchEvent(new Event('change',{bubbles:true}))}})}
        function attR(){const d=DR.value||'';if(!d){HR.value='';HR.disabled=true;FR&&(FR.textContent='Estação');return}FR&&(FR.textContent='Hora:');attMD();carrega('retirada',d,HR.value)}
        function attD(){const d=DD.value||'';if(!d){HD.value='';HD.disabled=true;FD&&(FD.textContent='Estação');return}FD&&(FD.textContent='Hora:');carrega('devolucao',d,HD.value)}
        function recarregar(){const er=R?.value||'',ed=D?.value||'';if(PR){PR.destroy();PR=null}if(PD){PD.destroy();PD=null}DR.value='';DD.value='';HR.value='';HD.value='';HR.disabled=true;HD.disabled=true;if(er){FR&&(FR.textContent='Carregando...');buscaDias(er,'retirada').then(function(d){FR&&(FR.textContent=d.length?'Data:':'Indisponível');PR=pickerDate(DR,d);attMD()})}if(ed){FD&&(FD.textContent='Carregando...');buscaDias(ed,'devolucao').then(function(d){FD&&(FD.textContent=d.length?'Data:':'Indisponível');PD=pickerDate(DD,d);attMD()})}}
        DR.addEventListener('change',attR);HR.addEventListener('change',attR);DD.addEventListener('change',attD);HD.addEventListener('change',attD);
        R?.addEventListener('change',function(){sD(true);recarregar()});D?.addEventListener('change',function(){sD(false);recarregar()});
        if(typeof window.Choices!=='undefined'){[R,D].forEach(function(s){if(!s)return;new window.Choices(s,{searchEnabled:true,searchPlaceholderValue:'Buscar...',itemSelectText:'',shouldSort:false,noResultsText:'Nada',noChoicesText:'Indisponível'})})}
        DR?.addEventListener('change',function(){normT();normD();valD()});HR?.addEventListener('change',normT);DD?.addEventListener('change',function(){normD();valD()});
        F?.addEventListener('submit',function(e){normT();normD();sD();if(!valD())e.preventDefault()});
        sD();recarregar();normD();normT()})();
    </script>
</body>
</html>
