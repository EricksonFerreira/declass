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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,500;9..40,700;9..40,800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
            --ink:#0b1220;--ink2:#1e293b;--ink3:#475569;--line:#e2e8f0;--white:#fff;--off:#f1f5f9;
            --r:8px;--r-lg:16px;
            --sh:0 1px 2px rgba(0,0,0,.05);--sh-md:0 4px 16px rgba(0,0,0,.08);--sh-lg:0 12px 48px rgba(0,0,0,.12);
        }
        body{font-family:Inter,sans-serif;color:var(--ink);background:var(--white);-webkit-font-smoothing:antialiased}
        a{text-decoration:none;color:inherit}
        img{display:block;max-width:100%}
        .wrap{width:min(1160px,calc(100% - 40px));margin-inline:auto}

        .topbar{position:sticky;top:0;z-index:100;background:var(--tb);border-bottom:1px solid var(--line)}
        .topbar-inner{display:flex;align-items:center;justify-content:space-between;height:64px;gap:16px}
        .brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:1.05rem;color:var(--tt);flex-shrink:0}
        .brand img{height:36px;width:auto}
        .topbar-nav{display:flex;align-items:center;gap:4px}
        .topbar-nav a{padding:6px 14px;border-radius:6px;font-size:.82rem;font-weight:500;color:var(--tt);transition:all .15s}
        .topbar-nav a:hover{background:var(--th);color:var(--tt)}
        .topbar-cta{padding:8px 22px;border-radius:6px;font-size:.84rem;font-weight:600;background:var(--b1-b);color:var(--b1-t);transition:all .2s;flex-shrink:0}
        .topbar-cta:hover{transform:translateY(-1px);box-shadow:var(--sh-md)}

        .hero{background:var(--hb);color:var(--ht);padding:80px 0;position:relative;overflow:hidden}
        .hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 30% 50%,rgba(255,255,255,.04) 0%,transparent 70%);pointer-events:none}
        .hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center;position:relative;z-index:1}
        .hero-tag{display:inline-flex;align-items:center;gap:6px;font-size:.65rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--a);margin-bottom:14px;padding:4px 12px;border:1px solid rgba(255,255,255,.12);border-radius:20px}
        .hero-title{font-family:'DM Sans',sans-serif;font-size:clamp(2rem,3.2vw+1rem,3.8rem);font-weight:800;line-height:1.05;margin-bottom:12px;letter-spacing:-.02em}
        .hero-sub{font-size:1.1rem;font-weight:500;opacity:.8;margin-bottom:6px}
        .hero-desc{font-size:.9rem;line-height:1.7;opacity:.6;margin-bottom:32px;max-width:48ch}
        .hero-actions{display:flex;gap:12px;flex-wrap:wrap}
        .hero-btn{padding:14px 32px;border-radius:8px;font-weight:600;font-size:.9rem;border:none;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:8px}
        .hero-btn-p{background:var(--b1-b);color:var(--b1-t)}
        .hero-btn-s{background:var(--b2-b);color:var(--b2-t);border:1px solid rgba(255,255,255,.15)}
        .hero-btn:hover{transform:translateY(-2px);box-shadow:var(--sh-lg)}
        .hero-image{border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh-lg)}
        .hero-image img{width:100%;height:460px;object-fit:cover}
        .hero-image-placeholder{height:460px;background:linear-gradient(135deg,var(--s),var(--p));border-radius:var(--r-lg);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.2);font-size:5rem}

        .hero-form-card{background:var(--fb);color:var(--ft);border-radius:12px;padding:24px;box-shadow:var(--sh-lg);margin-top:24px}
        .hero-form-card form{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .hero-form-card .full{grid-column:1/-1}
        .hero-form-card label{font-size:.76rem;font-weight:600;display:block;margin-bottom:4px;opacity:.8}
        .hero-form-card select,.hero-form-card input{padding:10px 12px;border:1px solid var(--line);border-radius:6px;font-size:.84rem;width:100%;background:var(--white);color:var(--ink)}
        .hero-form-card .btn-book{padding:12px;border:none;border-radius:6px;background:var(--b1-b);color:var(--b1-t);font-weight:600;font-size:.88rem;cursor:pointer;transition:opacity .15s;grid-column:1/-1}
        .hero-form-card .btn-book:hover{opacity:.9}

        .section{padding:80px 0}
        .section--alt{background:var(--off)}
        .section--frota{background:var(--frota-b)}
        .section--vant{background:var(--vant-b)}
        .section--etapas{background:var(--etap-b)}
        .section--contato{background:var(--cont-b)}
        .section-head{text-align:center;margin-bottom:48px}
        .section-head .eyebrow{font-size:.65rem;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--a);margin-bottom:10px}
        .section--frota .eyebrow{color:var(--frota-e)}
        .section--vant .eyebrow{color:var(--vant-e)}
        .section--etapas .eyebrow{color:var(--etap-e)}
        .section-title{font-family:'DM Sans',sans-serif;font-size:clamp(1.8rem,2.4vw+.8rem,2.8rem);font-weight:800;line-height:1.1;margin-bottom:10px;letter-spacing:-.02em}
        .section--frota .section-title{color:var(--frota-t)}
        .section--vant .section-title{color:var(--vant-t)}
        .section--etapas .section-title{color:var(--etap-t)}
        .section-desc{font-size:.9rem;line-height:1.7;color:var(--ink3);max-width:56ch;margin-inline:auto}
        .section--frota .section-desc{color:var(--frota-d)}
        .section--vant .section-desc{color:var(--vant-d)}
        .section--etapas .section-desc{color:var(--etap-d)}

        .fleet-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px}
        .fleet-card{position:relative;border-radius:12px;overflow:hidden;background:var(--frota-cb);box-shadow:var(--sh);transition:all .3s;cursor:pointer}
        .fleet-card:hover{transform:translateY(-6px);box-shadow:var(--sh-lg)}
        .fleet-card-img{width:100%;height:200px;object-fit:cover}
        .fleet-card-img-placeholder{height:200px;background:linear-gradient(135deg,var(--off),var(--line));display:flex;align-items:center;justify-content:center;color:var(--ink3);font-size:3rem;opacity:.3}
        .fleet-card-body{padding:20px 22px 24px;position:relative}
        .fleet-card-num{position:absolute;top:-14px;right:16px;width:32px;height:32px;border-radius:50%;background:var(--p);color:var(--pt);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;box-shadow:0 2px 8px rgba(0,0,0,.15)}
        .fleet-card-title{font-family:'DM Sans',sans-serif;font-size:1.1rem;font-weight:700;color:var(--frota-ct);margin-bottom:6px}
        .fleet-card-desc{font-size:.85rem;line-height:1.6;color:var(--frota-cd)}

        .vant-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
        .vant-item{text-align:center;padding:32px 20px;background:var(--white);border:1px solid var(--line);border-radius:12px;box-shadow:var(--sh);transition:all .25s}
        .vant-item:hover{transform:translateY(-4px);box-shadow:var(--sh-md);border-color:var(--a)}
        .vant-item-icon{width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,var(--p),var(--s));display:flex;align-items:center;justify-content:center;margin:0 auto 14px;color:var(--pt);font-size:1.3rem;font-weight:800}
        .vant-item-text{font-size:.9rem;font-weight:500;color:var(--vant-i);line-height:1.5}

        .steps-flow{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;position:relative}
        .steps-flow::before{content:'';position:absolute;top:32px;left:calc(16.66%);right:calc(16.66%);height:2px;background:linear-gradient(90deg,var(--p),var(--a));z-index:0}
        .step-card{background:var(--etap-pb);border:1px solid var(--line);border-radius:12px;padding:28px 22px 24px;text-align:center;box-shadow:var(--sh);position:relative;z-index:1;transition:all .25s}
        .step-card:hover{transform:translateY(-4px);box-shadow:var(--sh-md)}
        .step-num{width:48px;height:48px;border-radius:50%;background:var(--p);color:var(--pt);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-family:'DM Sans',sans-serif;font-size:1.2rem;font-weight:800;box-shadow:0 4px 12px rgba(0,0,0,.15)}
        .step-card-title{font-size:1rem;font-weight:700;color:var(--etap-pt);margin-bottom:8px}
        .step-card-desc{font-size:.85rem;line-height:1.6;color:var(--etap-pd);max-width:28ch;margin-inline:auto}

        .contato-banner{background:linear-gradient(135deg,var(--p),var(--s));border-radius:12px;padding:48px 52px;display:flex;align-items:center;justify-content:space-between;gap:32px;position:relative;overflow:hidden}
        .contato-banner::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 70% 50%,rgba(255,255,255,.06) 0%,transparent 60%);pointer-events:none}
        .contato-text{position:relative;z-index:1}
        .contato-eyebrow{font-size:.65rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--a);margin-bottom:10px;opacity:.9}
        .contato-title{font-family:'DM Sans',sans-serif;font-size:clamp(1.4rem,2vw+.6rem,2.2rem);font-weight:800;color:var(--cont-t);line-height:1.1;margin-bottom:14px}
        .contato-info{display:flex;flex-wrap:wrap;gap:6px 20px}
        .contato-info-item{font-size:.88rem;color:var(--cont-i);display:flex;align-items:center;gap:6px}
        .contato-info-item::before{content:'◆';font-size:.5rem;color:var(--a)}
        .contato-cta{flex-shrink:0;position:relative;z-index:1}
        .btn-contato{padding:14px 32px;border-radius:8px;background:var(--b2-b);color:var(--b2-t);font-weight:600;font-size:.9rem;border:1px solid rgba(255,255,255,.15);display:inline-flex;align-items:center;gap:8px;cursor:pointer;transition:all .2s}
        .btn-contato:hover{transform:translateY(-2px);box-shadow:var(--sh-lg)}

        .contato-image-wrap{border-radius:12px;overflow:hidden;box-shadow:var(--sh-lg);margin-top:28px}
        .contato-image-wrap img{width:100%;height:300px;object-fit:cover}

        .footer{background:var(--foot-b);color:var(--foot-t);padding:36px 0;border-top:1px solid var(--line)}
        .footer-inner{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .footer-text{font-size:.84rem;opacity:.65}
        .footer-nav{display:flex;gap:16px;font-size:.84rem}
        .footer-nav a{opacity:.5;transition:opacity .2s}
        .footer-nav a:hover{opacity:1}
        .footer-social{display:flex;gap:10px}
        .footer-social a{width:36px;height:36px;border-radius:50%;border:1px solid var(--line);display:flex;align-items:center;justify-content:center;font-size:.8rem;transition:all .2s;opacity:.5}
        .footer-social a:hover{opacity:1;background:var(--p);color:var(--pt);border-color:var(--p)}

        @media(max-width:768px){
            .hero-grid{grid-template-columns:1fr;gap:32px}
            .hero-image img,.hero-image-placeholder{height:260px}
            .topbar-nav{display:none}
            .vant-grid{grid-template-columns:1fr}
            .steps-flow{grid-template-columns:1fr;gap:16px}
            .steps-flow::before{display:none}
            .contato-banner{flex-direction:column;padding:32px 24px;text-align:center}
            .contato-info{justify-content:center}
            .hero-form-card form{grid-template-columns:1fr}
            .fleet-grid{grid-template-columns:1fr}
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
            <nav class="topbar-nav">
                <a href="#frota">{{ $menuItemFrota }}</a>
                <a href="#vantagens">{{ $menuItemVantagens }}</a>
                <a href="#etapas">{{ $menuItemEtapas }}</a>
                <a href="#contato">{{ $menuItemContato }}</a>
            </nav>
            <a href="{{ $botaoUrl }}" class="topbar-cta">{{ $botaoTexto }}</a>
        </div></div>
    </header>

    <section class="hero">
        <div class="wrap"><div class="hero-grid">
            <div>
                @if(!empty($heroTag))<div class="hero-tag">{{ $heroTag }}</div>@endif
                <h1 class="hero-title">{{ $titulo }}</h1>
                @if(!empty($subtitulo))<div class="hero-sub">{{ $subtitulo }}</div>@endif
                @if(!empty($descricao))<p class="hero-desc">{{ $descricao }}</p>@endif
                <div class="hero-actions">
                    <a href="{{ $botaoUrl }}" class="hero-btn hero-btn-p">{{ $botaoTexto }}</a>
                    <a href="{{ $botaoLoginUrl }}" class="hero-btn hero-btn-s">{{ $botaoAdminTexto }}</a>
                </div>
                @if(!empty($heroFormTitulo))
                <div class="hero-form-card">
                    <h2 style="font-size:.95rem;font-weight:700;margin-bottom:6px;font-family:'DM Sans',sans-serif">{{ $heroFormTitulo }}</h2>
                    @if(!empty($heroFormDescricao))<p style="font-size:.82rem;opacity:.7;margin-bottom:14px">{{ $heroFormDescricao }}</p>@endif
                    <form method="GET" action="{{ $botaoUrl }}">
                        <div class="full">
                            <label for="retirada">{{ $heroFormLabelRetirada }}</label>
                            <select id="retirada" name="retirada">
                                <option value="">Selecione uma estação</option>
                                @foreach($estacoesRetirada as $estacao_id => $nome)
                                    <option value="{{ $estacao_id }}">{{ $nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="data_retirada">{{ $heroFormLabelDataRetirada }}</label>
                            <input id="data_retirada" name="data_retirada" type="text" readonly placeholder="Selecione a data">
                            <small id="data_retirada_feedback" style="font-size:.7rem;opacity:.6;display:block;margin-top:2px">Selecione uma estação de retirada para ver os dias disponíveis.</small>
                        </div>
                        <div>
                            <label for="hora_retirada">{{ $heroFormLabelHoraRetirada }}</label>
                            <select id="hora_retirada" name="hora_retirada" disabled>
                                <option value="">Selecione a data</option>
                            </select>
                            <small id="hora_retirada_feedback" style="font-size:.7rem;opacity:.6;display:block;margin-top:2px">Escolha uma data para ver os horários disponíveis.</small>
                        </div>
                        <div class="full" id="devolucaoField">
                            <label for="devolucao">{{ $heroFormLabelDevolucao ?? 'Local de devolução' }}</label>
                            <select id="devolucao" name="devolucao">
                                <option value="">Selecione uma estação</option>
                                @foreach($estacoesDevolucao as $estacao_id => $nome)
                                    <option value="{{ $estacao_id }}">{{ $nome }}</option>
                                @endforeach
                            </select>
                            <small id="devolucao_feedback" style="font-size:.7rem;opacity:.6;display:block;margin-top:2px">Selecione uma estação de devolução.</small>
                        </div>
                        <div>
                            <label for="data_devolucao">{{ $heroFormLabelDataDevolucao }}</label>
                            <input id="data_devolucao" name="data_devolucao" type="text" readonly placeholder="Selecione a data">
                            <small id="data_devolucao_feedback" style="font-size:.7rem;opacity:.6;display:block;margin-top:2px">Selecione uma estação de devolução para ver os dias disponíveis.</small>
                        </div>
                        <div>
                            <label for="hora_devolucao">{{ $heroFormLabelHoraDevolucao }}</label>
                            <select id="hora_devolucao" name="hora_devolucao" disabled>
                                <option value="">Selecione a data</option>
                            </select>
                            <small id="hora_devolucao_feedback" style="font-size:.7rem;opacity:.6;display:block;margin-top:2px">Escolha uma data para ver os horários disponíveis.</small>
                        </div>
                        <button type="submit" class="btn-book">{{ $heroFormTextoBotao }}</button>
                        @if(!empty($heroFormNota))<p style="font-size:.75rem;opacity:.5;margin-top:10px;grid-column:1/-1">{{ $heroFormNota }}</p>@endif
                    </form>
                </div>
                @endif
            </div>
            <div class="hero-image">
                @if(!empty($imagemHero))
                    <img src="{{ $imagemHero }}" alt="Hero">
                @else
                    <div class="hero-image-placeholder">✦</div>
                @endif
            </div>
        </div></div>
    </section>

    <section id="frota" class="section section--frota">
        <div class="wrap">
            <div class="section-head">
                @if(!empty($frotaLabelSuperior))<div class="eyebrow">{{ $frotaLabelSuperior }}</div>@endif
                <h2 class="section-title">{{ $frotaTitulo }}</h2>
                @if(!empty($frotaDescricao))<p class="section-desc">{{ $frotaDescricao }}</p>@endif
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

    <section id="vantagens" class="section section--vant">
        <div class="wrap">
            <div class="section-head">
                @if(!empty($vantagensLabelSuperior))<div class="eyebrow">{{ $vantagensLabelSuperior }}</div>@endif
                <h2 class="section-title">{{ $vantagensTitulo }}</h2>
                @if(!empty($vantagensDescricao))<p class="section-desc">{{ $vantagensDescricao }}</p>@endif
            </div>
            <div class="vant-grid">
                @foreach($beneficios as $beneficio)
                    @if(!empty($beneficio))
                        <div class="vant-item">
                            <div class="vant-item-icon">✦</div>
                            <span class="vant-item-text">{{ $beneficio }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <section id="como-funciona" class="section section--etapas">
        <div class="wrap">
            <div class="section-head">
                @if(!empty($etapasLabelSuperior))<div class="eyebrow">{{ $etapasLabelSuperior }}</div>@endif
                <h2 class="section-title">{{ $etapasTitulo }}</h2>
            </div>
            <div class="steps-flow">
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

    <section id="contato" class="section section--contato">
        <div class="wrap">
            <div class="contato-banner">
                <div class="contato-text">
                    @if(!empty($contatoLabelSuperior))<div class="contato-eyebrow">{{ $contatoLabelSuperior }}</div>@endif
                    <div class="contato-title">{{ $contatoTitulo }}</div>
                    <div class="contato-info">
                        @if(!empty($contatoTelefone))<span class="contato-info-item">{{ $contatoTelefone }}</span>@endif
                        @if(!empty($contatoEmail))<span class="contato-info-item">{{ $contatoEmail }}</span>@endif
                    </div>
                </div>
                <div class="contato-cta">
                    <a href="{{ $botaoUrl }}" class="btn-contato">{{ $botaoTexto }}</a>
                </div>
            </div>
            @if(!empty($imagemContato))
                <div class="contato-image-wrap">
                    <img src="{{ $imagemContato }}" alt="Contato {{ $empresa->nome }}">
                </div>
            @endif
        </div>
    </section>

    <footer class="footer">
        <div class="wrap"><div class="footer-inner">
            <div class="footer-text">{{ $footerTexto }}</div>
            <div class="footer-nav">
                <a href="#frota">{{ $menuItemFrota }}</a>
                <a href="#vantagens">{{ $menuItemVantagens }}</a>
                <a href="#etapas">{{ $menuItemEtapas }}</a>
                <a href="#contato">{{ $menuItemContato }}</a>
            </div>
        </div></div>
    </footer>

    <script>
        (function(){
            if(!window.parent||window.parent===window)return;
            document.addEventListener('click',function(e){
                var l=e.target.closest('a');if(l&&l.getAttribute('href')&&l.getAttribute('href')!=='#')e.preventDefault();
            },true);
            document.querySelectorAll('form').forEach(function(f){f.addEventListener('submit',function(e){e.preventDefault()})});

            var cssMap={
                cor_primaria:'--p',cor_secundaria:'--s',cor_terciaria:'--a',
                cor_topbar_fundo:'--tb',cor_topbar_texto:'--tt',cor_topbar_link_hover:'--th',
                cor_hero_fundo:'--hb',cor_hero_texto:'--ht',
                cor_form_fundo:'--fb',cor_form_texto:'--ft',
                cor_frota_fundo:'--frota-b',cor_frota_etiqueta_texto:'--frota-e',cor_frota_titulo_texto:'--frota-t',cor_frota_descricao_texto:'--frota-d',
                cor_frota_card_titulo_texto:'--frota-ct',cor_frota_card_descricao_texto:'--frota-cd',cor_frota_card_fundo:'--frota-cb',
                cor_vantagens_fundo:'--vant-b',cor_vantagens_etiqueta_texto:'--vant-e',cor_vantagens_titulo_texto:'--vant-t',
                cor_vantagens_descricao_texto:'--vant-d',cor_vantagens_item_texto:'--vant-i',
                cor_etapas_fundo:'--etap-b',cor_etapas_etiqueta_texto:'--etap-e',cor_etapas_titulo_texto:'--etap-t',
                cor_etapas_descricao_texto:'--etap-d',cor_etapas_passo_texto:'--etap-pt',cor_etapas_passo_descricao_texto:'--etap-pd',cor_etapas_passo_fundo:'--etap-pb',
                cor_contato_fundo:'--cont-b',cor_contato_etiqueta_texto:'--cont-e',cor_contato_titulo_texto:'--cont-t',cor_contato_info_texto:'--cont-i',
                cor_footer_fundo:'--foot-b',cor_footer_texto:'--foot-t',
                cor_botao_primario_fundo:'--b1-b',cor_botao_primario_texto:'--b1-t',
                cor_botao_secundario_fundo:'--b2-b',cor_botao_secundario_texto:'--b2-t',
            };

            window.addEventListener('message',function(e){
                if(!e.data||e.data.type!=='se-carregar'&&e.data.type!=='se-atualizar')return;
                var d=e.data.dados||{},du=e.data.dataUrls||{},rt=document.documentElement;
                for(var k in cssMap){if(d[k])rt.style.setProperty(cssMap[k],d[k])}
                if(d.cor_primaria){var b=(function(h){h=h.replace('#','');return(parseInt(h.substr(0,2),16)*299+parseInt(h.substr(2,2),16)*587+parseInt(h.substr(4,2),16)*114)/1000})(d.cor_primaria);rt.style.setProperty('--pt',b>=155?'#0F172A':'#FFFFFF')}
                for(var fi=1;fi<=10;fi++){
                    var c=document.querySelector('.fleet-card[data-frota="'+fi+'"]');if(!c)continue;
                    var tk='frota_card_'+fi+'_titulo',dk='frota_card_'+fi+'_descricao',t=d[tk],de=d[dk];
                    var te=c.querySelector('[data-frota-titulo="'+fi+'"]'),de2=c.querySelector('[data-frota-desc="'+fi+'"]');
                    if(t&&t.trim()){c.style.display='';if(te)te.textContent=t;if(de2)de2.textContent=de||''}
                    else c.style.display='none'
                }
                var imgMap={imagem_hero:'.hero-image img'};
                for(var ik in imgMap){if(!du[ik])continue;var ie=document.querySelector(imgMap[ik]);if(ie)ie.src=du[ik]}
            });
            window.parent.postMessage({type:'se-pronto'},'*');
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script>
        (function () {
            const retiradaSelect  = document.getElementById('retirada');
            const devolucaoSelect = document.getElementById('devolucao');
            const devolucaoField  = document.getElementById('devolucaoField');
            const dataRetirada    = document.getElementById('data_retirada');
            const horaRetirada    = document.getElementById('hora_retirada');
            const dataDevolucao   = document.getElementById('data_devolucao');
            const horaDevolucao   = document.getElementById('hora_devolucao');
            const form            = dataRetirada?.closest('form');
            const diasDisponiveisUrl = @json(route('reserva.diasSemDisponibilidade'));
            const agendaUrl = @json(route('reserva.agendaDisponivelPorEstacao'));
            const dataRetiradaFeedback = document.getElementById('data_retirada_feedback');
            const horaRetiradaFeedback = document.getElementById('hora_retirada_feedback');
            const dataDevolucaoFeedback = document.getElementById('data_devolucao_feedback');
            const horaDevolucaoFeedback = document.getElementById('hora_devolucao_feedback');
            const devolucaoFeedback = document.getElementById('devolucao_feedback');
            const eventoChange = new Event('change', { bubbles: true });
            let pickerRetirada = null;
            let pickerDevolucao = null;

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
            function addDays(date, days) { const c = new Date(date.getTime()); c.setDate(c.getDate() + days); return c; }
            function normalizeMinDates() {
                if (!dataRetirada || !dataDevolucao) return;
                const now = new Date();
                dataRetirada.min = toLocalDateInput(now);
                const rd  = dataRetirada.value ? new Date(dataRetirada.value+'T00:00:00') : now;
                const md  = addDays(rd, 1);
                dataDevolucao.min = toLocalDateInput(md);
                if (dataDevolucao.value && new Date(dataDevolucao.value+'T00:00:00') < md) dataDevolucao.value = toLocalDateInput(md);
            }
            function normalizeMinTime() {
                if (!dataRetirada || !horaRetirada) return;
                const n=new Date(), t=toLocalDateInput(n);
                dataRetirada.value===t? horaRetirada.min=toLocalTimeInput(n) : horaRetirada.removeAttribute('min');
            }
            function validateDates() {
                if (!dataRetirada||!horaRetirada||!dataDevolucao) return true;
                const n=new Date(),td=new Date(n.getFullYear(),n.getMonth(),n.getDate());
                const rd=dataRetirada.value?new Date(dataRetirada.value+'T00:00:00'):null,dd=dataDevolucao.value?new Date(dataDevolucao.value+'T00:00:00'):null;
                if(rd&&rd<td){dataRetirada.setCustomValidity('A retirada não pode ser antes de hoje.');dataRetirada.reportValidity();return false}
                dataRetirada.setCustomValidity('');
                if(rd&&rd.getTime()===td.getTime()&&horaRetirada.value){const dt=new Date(dataRetirada.value+'T'+horaRetirada.value);if(dt<n){horaRetirada.setCustomValidity('A hora de retirada não pode ser no passado.');horaRetirada.reportValidity();return false}}
                horaRetirada.setCustomValidity('');
                if(dataRetirada.value&&dataDevolucao.value){const md=addDays(new Date(dataRetirada.value+'T00:00:00'),1);if(dd<md){dataDevolucao.setCustomValidity('A devolução deve ser ao menos 1 dia após a retirada.');dataDevolucao.reportValidity();return false}}
                dataDevolucao.setCustomValidity('');
                return true;
            }
            function estacaoPermiteCheckout(id) { return !!devolucaoSelect?.querySelector(`option[value="${id}"]`); }
            function syncDevolucao(t=false) {
                if(!devolucaoField||!devolucaoSelect)return;
                const rid=retiradaSelect?.value||'',ok=estacaoPermiteCheckout(rid),rt=retiradaSelect?.selectedOptions?.[0]?.textContent?.trim()||'';
                devolucaoField.style.display='block';devolucaoSelect.disabled=false;
                if(ok&&rid){const va=devolucaoSelect.value;devolucaoSelect.value=rid;if(t&&va!==rid)devolucaoSelect.dispatchEvent(eventoChange);if(devolucaoFeedback)devolucaoFeedback.textContent=rt?'Estação de devolução definida automaticamente: '+rt+'.':'Estação de devolução definida automaticamente.'}
                else if(!ok&&devolucaoSelect.value===rid){devolucaoSelect.value='';if(devolucaoFeedback)devolucaoFeedback.textContent='Selecione uma estação de devolução.'}
                else if(devolucaoFeedback)devolucaoFeedback.textContent=devolucaoSelect.value?'Estação de devolução selecionada: '+(devolucaoSelect.selectedOptions?.[0]?.textContent?.trim()||'')+'.':'Selecione uma estação de devolução.';
            }
            retiradaSelect?.addEventListener('change',()=>{syncDevolucao(true)});
            const ptLocale=window.flatpickr?.l10ns?.pt||{};
            function toIsoDate(d){return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0')}
            function normalizarSlotHorario(s){
                if(s&&typeof s==='object'){const v=String(s.value||s.hora||s.time||'').trim(),l=String(s.label||s.text||v).trim();if(!v)return null;return{value:v,label:l||v,taxa_extra:Boolean(s.taxa_extra||s.status==='taxa_extra')}}
                const v=String(s||'').trim();return v?{value:v,label:v,taxa_extra:false}:null;
            }
            function preencherHorarios(se,fe,sl,hp){
                const l=Array.isArray(sl)?sl.map(normalizarSlotHorario).filter(Boolean):[];se.innerHTML='';
                if(!l.length){se.appendChild(new Option('Sem horários disponíveis',''));se.disabled=true;if(fe)fe.textContent='Sem horários disponíveis para a data selecionada.';return[]}
                se.appendChild(new Option('Selecione a hora',''));
                l.forEach(function(s){const v=s.value.includes('T')?s.value.split('T')[1].slice(0,5):s.value.slice(0,5);const o=new Option(s.taxa_extra?s.label+' • Taxa extra':s.label,v);o.dataset.taxaExtra=s.taxa_extra?'1':'0';se.appendChild(o)});
                se.disabled=false;const p=hp?String(hp).slice(0,5):(l.length===1?l[0].value.slice(0,5):'');se.value=p&&[...se.options].some(o=>o.value===p)?p:'';if(fe)fe.textContent='Escolha a hora disponível.';return l;
            }
            function carregarHorariosAgenda(tipo,db,hp){
                const eid=tipo==='devolucao'?devolucaoSelect?.value:retiradaSelect?.value;if(!eid||!db)return Promise.resolve([]);
                if(tipo==='devolucao'){horaDevolucao.innerHTML='<option>Carregando...</option>';horaDevolucao.disabled=true}else{horaRetirada.innerHTML='<option>Carregando...</option>';horaRetirada.disabled=true}
                return fetch(agendaUrl+'?estacao_id='+eid+'&tipo='+tipo+'&data_base='+db+'&limite=1',{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(r){const a=Array.isArray(r.agenda)?r.agenda:[],dia=a.find(i=>String(i.date)===String(db))||a[0]||null,sl=Array.isArray(dia?.slots)?dia.slots:[];return tipo==='devolucao'?preencherHorarios(horaDevolucao,horaDevolucaoFeedback,sl,hp):preencherHorarios(horaRetirada,horaRetiradaFeedback,sl,hp)}).catch(function(){tipo==='devolucao'?preencherHorarios(horaDevolucao,horaDevolucaoFeedback,[],null):preencherHorarios(horaRetirada,horaRetiradaFeedback,[],null);return[]});
            }
            function buscarDiasDisponiveis(eid,tipo){
                if(!eid)return Promise.resolve([]);
                return fetch(diasDisponiveisUrl+'?estacao_id='+eid+'&dias=90&tipo='+tipo,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(r){return Array.isArray(r.dias_disponiveis)?r.dias_disponiveis:[]}).catch(function(){return[]});
            }
            function criarPickerDate(el,dias){
                if(!el||!window.flatpickr)return null;
                const ok=new Set((dias||[]).map(function(i){return String(i.data||i).slice(0,10)}));
                return window.flatpickr(el,{locale:ptLocale,altInput:true,altFormat:'d/m/Y',dateFormat:'Y-m-d',disableMobile:true,minDate:'today',disable:[function(d){return!ok.has(toIsoDate(d))}],onChange:function(){el.dispatchEvent(new Event('change',{bubbles:true}))}});
            }
            function atualizarMinDateDevolucao(){
                const rd=dataRetirada.value?new Date(dataRetirada.value+'T00:00:00'):null;if(!rd)return;
                const md=new Date(rd.getTime()+86400000);
                if(pickerDevolucao){pickerDevolucao.set('minDate',md);const s=pickerDevolucao.selectedDates[0]||null;if(s&&s.getTime()<md.getTime()){pickerDevolucao.clear();dataDevolucao.value='';horaDevolucao.value='';horaDevolucao.disabled=true;if(dataDevolucaoFeedback)dataDevolucaoFeedback.textContent='Escolha uma data de devolução igual ou posterior ao dia seguinte.'}}
                else if(window.flatpickr)pickerDevolucao=window.flatpickr(dataDevolucao,{locale:ptLocale,altInput:true,altFormat:'d/m/Y',dateFormat:'Y-m-d',disableMobile:true,minDate:md,onChange:function(){dataDevolucao.dispatchEvent(new Event('change',{bubbles:true}))}});
            }
            function atualizarRetirada(){const d=dataRetirada.value||'',h=horaRetirada.value||'';if(!d){horaRetirada.value='';horaRetirada.disabled=true;dataRetiradaFeedback&&(dataRetiradaFeedback.textContent='Selecione uma estação de retirada para ver os dias disponíveis.');return}dataRetiradaFeedback&&(dataRetiradaFeedback.textContent='Selecione a hora de retirada.');atualizarMinDateDevolucao();carregarHorariosAgenda('retirada',d,h)}
            function atualizarDevolucao(){const d=dataDevolucao.value||'',h=horaDevolucao.value||'';if(!d){horaDevolucao.value='';horaDevolucao.disabled=true;dataDevolucaoFeedback&&(dataDevolucaoFeedback.textContent='Selecione uma estação de devolução para ver os dias disponíveis.');return}dataDevolucaoFeedback&&(dataDevolucaoFeedback.textContent='Selecione a hora de devolução.');carregarHorariosAgenda('devolucao',d,h)}
            function recarregarPickersPorEstacao(){
                const er=retiradaSelect?.value||'',ed=devolucaoSelect?.value||'';
                if(pickerRetirada){pickerRetirada.destroy();pickerRetirada=null}if(pickerDevolucao){pickerDevolucao.destroy();pickerDevolucao=null}
                dataRetirada.value='';dataDevolucao.value='';horaRetirada.value='';horaDevolucao.value='';horaRetirada.disabled=true;horaDevolucao.disabled=true;
                if(er){dataRetiradaFeedback&&(dataRetiradaFeedback.textContent='Carregando...');buscarDiasDisponiveis(er,'retirada').then(function(d){dataRetiradaFeedback&&(dataRetiradaFeedback.textContent=d.length?'Selecione a data de retirada.':'Não há dias disponíveis para retirada nesta estação.');pickerRetirada=criarPickerDate(dataRetirada,d);atualizarMinDateDevolucao()})}
                if(ed){dataDevolucaoFeedback&&(dataDevolucaoFeedback.textContent='Carregando...');buscarDiasDisponiveis(ed,'devolucao').then(function(d){dataDevolucaoFeedback&&(dataDevolucaoFeedback.textContent=d.length?'Selecione a data de devolução.':'Não há dias disponíveis para devolução nesta estação.');pickerDevolucao=criarPickerDate(dataDevolucao,d);atualizarMinDateDevolucao()})}
            }
            dataRetirada.addEventListener('change',atualizarRetirada);
            horaRetirada.addEventListener('change',atualizarRetirada);
            dataDevolucao.addEventListener('change',atualizarDevolucao);
            horaDevolucao.addEventListener('change',atualizarDevolucao);
            retiradaSelect?.addEventListener('change',function(){syncDevolucao(true);recarregarPickersPorEstacao()});
            devolucaoSelect?.addEventListener('change',function(){syncDevolucao(false);recarregarPickersPorEstacao()});
            if(typeof window.Choices!=='undefined'){[retiradaSelect,devolucaoSelect].forEach(function(s){if(!s)return;new window.Choices(s,{searchEnabled:true,searchPlaceholderValue:'Buscar estação...',itemSelectText:'',shouldSort:false,noResultsText:'Nenhuma estação encontrada',noChoicesText:'Nenhuma estação disponível'})})}
            dataRetirada?.addEventListener('change',function(){normalizeMinTime();normalizeMinDates();validateDates()});
            horaRetirada?.addEventListener('change',normalizeMinTime);
            dataDevolucao?.addEventListener('change',function(){normalizeMinDates();validateDates()});
            form?.addEventListener('submit',function(e){normalizeMinTime();normalizeMinDates();syncDevolucao();if(!validateDates())e.preventDefault()});
            syncDevolucao();recarregarPickersPorEstacao();normalizeMinDates();normalizeMinTime();
        })();
    </script>
</body>
</html>
