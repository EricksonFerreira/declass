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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        *,:after,:before{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        :root{
            --brand-p:{{$corPrimaria}};--brand-s:{{$corSecundaria}};--brand-a:{{$corTerciaria}};--brand-pt:{{$corTextoPrimario}};
            --top-bg:{{$corTopbarFundo}};--top-txt:{{$corTopbarTexto}};--top-hv:{{$corTopbarLinkHover}};
            --hero-bg:{{$corHeroFundo}};--hero-txt:{{$corHeroTexto}};
            --form-bg:{{$corFormFundo}};--form-txt:{{$corFormTexto}};
            --frota-bg:{{$corFrotaFundo}};--frota-et:{{$corFrotaEtiquetaTexto}};--frota-tt:{{$corFrotaTituloTexto}};--frota-dt:{{$corFrotaDescricaoTexto}};--frota-ct:{{$corFrotaCardTituloTexto}};--frota-cd:{{$corFrotaCardDescricaoTexto}};--frota-cb:{{$corFrotaCardFundo}};
            --vant-bg:{{$corVantagensFundo}};--vant-et:{{$corVantagensEtiquetaTexto}};--vant-tt:{{$corVantagensTituloTexto}};--vant-dt:{{$corVantagensDescricaoTexto}};--vant-it:{{$corVantagensItemTexto}};
            --etap-bg:{{$corEtapasFundo}};--etap-et:{{$corEtapasEtiquetaTexto}};--etap-tt:{{$corEtapasTituloTexto}};--etap-dt:{{$corEtapasDescricaoTexto}};--etap-pt:{{$corEtapasPassoTexto}};--etap-pd:{{$corEtapasPassoDescricaoTexto}};--etap-pb:{{$corEtapasPassoFundo}};
            --cont-bg:{{$corContatoFundo}};--cont-et:{{$corContatoEtiquetaTexto}};--cont-tt:{{$corContatoTituloTexto}};--cont-it:{{$corContatoInfoTexto}};
            --foot-bg:{{$corFooterFundo}};--foot-txt:{{$corFooterTexto}};
            --btn1-bg:{{$corBotaoPrimarioFundo}};--btn1-txt:{{$corBotaoPrimarioTexto}};--btn2-bg:{{$corBotaoSecundarioFundo}};--btn2-txt:{{$corBotaoSecundarioTexto}};
            --ink:#0f172a;--ink2:#334155;--ink3:#64748b;--line:#e2e8f0;--white:#fff;--off:#f8fafc;
            --r:12px;--r-lg:20px;
            --sh:0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);--sh-md:0 4px 12px rgba(0,0,0,.08);--sh-lg:0 12px 40px rgba(0,0,0,.10);
        }
        body{font-family:Inter,sans-serif;color:var(--ink);background:var(--off);-webkit-font-smoothing:antialiased}
        a{text-decoration:none;color:inherit}
        img{display:block;max-width:100%}
        .wrap{width:min(1160px,calc(100% - 40px));margin-inline:auto}

        .topbar{position:sticky;top:0;z-index:100;background:var(--top-bg);border-bottom:1px solid var(--line);backdrop-filter:blur(12px)}
        .topbar-inner{display:flex;align-items:center;justify-content:space-between;height:68px;gap:16px}
        .brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:1.1rem;color:var(--top-txt);flex-shrink:0}
        .brand img{height:38px;width:auto}
        .topbar-nav{display:flex;align-items:center;gap:6px}
        .topbar-nav a{padding:8px 16px;border-radius:8px;font-size:.84rem;font-weight:500;color:var(--top-txt);transition:background .15s}
        .topbar-nav a:hover{background:var(--top-hv);color:var(--top-txt)}
        .topbar-cta{padding:8px 20px;border-radius:8px;font-size:.84rem;font-weight:600;background:var(--btn1-bg);color:var(--btn1-txt);transition:opacity .15s;flex-shrink:0}
        .topbar-cta:hover{opacity:.9}

        .hero{background:var(--hero-bg);color:var(--hero-txt);padding:60px 0;min-height:70vh;display:flex;align-items:center}
        .hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
        .hero-tag{font-size:.7rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--brand-a);margin-bottom:16px}
        .hero-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(2rem,3vw+1rem,3.6rem);font-weight:800;line-height:1.05;margin-bottom:14px}
        .hero-sub{font-size:1.05rem;font-weight:500;opacity:.85;margin-bottom:8px}
        .hero-desc{font-size:.9rem;line-height:1.7;opacity:.7;margin-bottom:28px;max-width:48ch}
        .hero-actions{display:flex;gap:12px;flex-wrap:wrap}
        .hero-btn{padding:14px 32px;border-radius:12px;font-weight:600;font-size:.92rem;border:none;cursor:pointer;transition:transform .15s,box-shadow .15s;display:inline-flex;align-items:center;gap:8px}
        .hero-btn-prim{background:var(--btn1-bg);color:var(--btn1-txt)}
        .hero-btn-sec{background:var(--btn2-bg);color:var(--btn2-txt);border:1px solid var(--line)}
        .hero-btn:hover{transform:translateY(-2px);box-shadow:var(--sh-md)}
        .hero-image{border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh-lg);position:relative}
        .hero-image img{width:100%;height:480px;object-fit:cover}
        .hero-image-placeholder{width:100%;height:480px;background:linear-gradient(135deg,var(--brand-s),var(--brand-p));border-radius:var(--r-lg);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.3);font-size:4rem}

        .field-small{display:block;margin-top:4px;font-size:9px;color:var(--ink3)}

        .hero-form-card{background:var(--form-bg);color:var(--form-txt);border-radius:var(--r-lg);padding:28px;box-shadow:var(--sh-lg);margin-top:24px}
        .hero-form-card form{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .hero-form-card .full{grid-column:1/-1}
        .hero-form-card label{font-size:.78rem;font-weight:600;display:block;margin-bottom:4px}
        .hero-form-card select,.hero-form-card input{padding:10px 14px;border:1px solid var(--line);border-radius:8px;font-size:.85rem;width:100%;background:var(--white);color:var(--ink)}
        .hero-form-card .btn-book{padding:12px;border:none;border-radius:8px;background:var(--btn1-bg);color:var(--btn1-txt);font-weight:600;font-size:.9rem;cursor:pointer;transition:opacity .15s;grid-column:1/-1}
        .hero-form-card .btn-book:hover{opacity:.9}
        .hero-form-card .booking-note{font-size:.75rem;opacity:.6;margin-top:8px;grid-column:1/-1}

        .section{padding:72px 0}
        .section--alt{background:var(--white)}
        .section--frota{background:var(--frota-bg)}
        .section--vant{background:var(--vant-bg)}
        .section--etapas{background:var(--etap-bg)}
        .section--contato{background:var(--cont-bg)}
        .section-head{text-align:center;margin-bottom:48px}
        .section-head .eyebrow{font-size:.68rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--brand-a);margin-bottom:10px}
        .section--frota .eyebrow{color:var(--frota-et)}
        .section--vant .eyebrow{color:var(--vant-et)}
        .section--etapas .eyebrow{color:var(--etap-et)}
        .section-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(1.6rem,2vw+.8rem,2.6rem);font-weight:800;line-height:1.1;margin-bottom:10px}
        .section--frota .section-title{color:var(--frota-tt)}
        .section--vant .section-title{color:var(--vant-tt)}
        .section--etapas .section-title{color:var(--etap-tt)}
        .section-desc{font-size:.9rem;line-height:1.7;color:var(--ink3);max-width:56ch;margin-inline:auto}
        .section--frota .section-desc{color:var(--frota-dt)}
        .section--vant .section-desc{color:var(--vant-dt)}
        .section--etapas .section-desc{color:var(--etap-dt)}

        .fleet-scroll{display:flex;gap:16px;overflow-x:auto;padding:8px 0 16px;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch}
        .fleet-scroll::-webkit-scrollbar{height:6px}
        .fleet-scroll::-webkit-scrollbar-track{background:transparent}
        .fleet-scroll::-webkit-scrollbar-thumb{background:var(--line);border-radius:3px}
        .fleet-scroll .fleet-card{min-width:280px;max-width:320px;scroll-snap-align:start;background:var(--frota-cb);border:1px solid var(--line);border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh);transition:transform .2s,box-shadow .2s;flex-shrink:0}
        .fleet-scroll .fleet-card:hover{transform:translateY(-4px);box-shadow:var(--sh-md)}
        .fleet-card-img{width:100%;height:170px;object-fit:cover}
        .fleet-card-img-wrap{position:relative}
        .fleet-card-img-placeholder{height:170px;background:linear-gradient(135deg,var(--off),var(--line));display:flex;align-items:center;justify-content:center;color:var(--ink3);font-size:2.5rem;opacity:.4}
        .fleet-card-body{padding:18px 20px 22px}
        .fleet-card-num{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:8px;background:var(--brand-p);color:var(--brand-pt);font-size:.7rem;font-weight:800;margin-bottom:10px}
        .fleet-card-title{font-size:1rem;font-weight:700;color:var(--frota-ct);margin-bottom:6px}
        .fleet-card-desc{font-size:.84rem;line-height:1.6;color:var(--frota-cd)}

        .vant-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .vant-item{display:flex;align-items:flex-start;gap:14px;padding:20px;background:var(--white);border:1px solid var(--line);border-radius:var(--r-lg);box-shadow:var(--sh);transition:transform .2s,box-shadow .2s}
        .vant-item:hover{transform:translateX(4px);box-shadow:var(--sh-md)}
        .vant-item-icon{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,var(--brand-p),var(--brand-s));display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--brand-pt);font-size:1.2rem;font-weight:800}
        .vant-item-text{font-size:.9rem;font-weight:500;color:var(--vant-it);line-height:1.5;padding-top:11px}

        .steps-track{display:flex;flex-direction:column;gap:0;max-width:640px;margin-inline:auto;position:relative}
        .steps-track::before{content:'';position:absolute;left:28px;top:20px;bottom:20px;width:2px;background:linear-gradient(180deg,var(--brand-p),var(--brand-a))}
        .step-card{display:flex;gap:20px;padding:20px 0;position:relative}
        .step-num{width:56px;height:56px;border-radius:50%;background:var(--brand-p);color:var(--brand-pt);display:flex;align-items:center;justify-content:center;font-family:'Plus Jakarta Sans',sans-serif;font-size:1.3rem;font-weight:800;flex-shrink:0;position:relative;z-index:1;box-shadow:0 0 0 4px var(--etap-pb)}
        .step-card-body{flex:1;background:var(--etap-pb);border:1px solid var(--line);border-radius:var(--r-lg);padding:20px 24px;box-shadow:var(--sh)}
        .step-card-title{font-size:1rem;font-weight:700;color:var(--etap-pt);margin-bottom:6px}
        .step-card-desc{font-size:.86rem;line-height:1.65;color:var(--etap-pd)}

        .contato-banner{background:linear-gradient(135deg,var(--brand-p),var(--brand-s));border-radius:var(--r-lg);padding:48px 52px;display:flex;align-items:center;justify-content:space-between;gap:32px;position:relative;overflow:hidden}
        .contato-text{position:relative;z-index:1}
        .contato-eyebrow{font-size:.68rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--brand-a);margin-bottom:10px}
        .contato-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(1.4rem,2vw+.6rem,2.2rem);font-weight:800;color:var(--cont-tt);line-height:1.1;margin-bottom:16px}
        .contato-info{display:flex;flex-wrap:wrap;gap:8px 24px}
        .contato-info-item{font-size:.88rem;color:var(--cont-it)}
        .contato-info-item::before{content:'›';margin-right:6px;font-weight:700;color:var(--brand-a)}
        .contato-cta{flex-shrink:0;position:relative;z-index:1}
        .btn-contato{padding:14px 32px;border-radius:12px;background:var(--btn2-bg);color:var(--btn2-txt);font-weight:600;font-size:.9rem;border:1px solid var(--line);display:inline-flex;align-items:center;gap:8px;cursor:pointer;transition:transform .15s}
        .btn-contato:hover{transform:translateY(-2px)}

        .contato-image-wrap{border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh-lg);margin-top:32px}
        .contato-image-wrap img{width:100%;height:320px;object-fit:cover}

        .footer{background:var(--foot-bg);color:var(--foot-txt);padding:40px 0;border-top:1px solid var(--line)}
        .footer-inner{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .footer-text{font-size:.84rem;opacity:.7}
        .footer-nav{display:flex;gap:20px;font-size:.84rem}
        .footer-nav a{opacity:.6;transition:opacity .15s}
        .footer-nav a:hover{opacity:1}

        @media(max-width:768px){
            .hero-grid{grid-template-columns:1fr;gap:32px}
            .hero-image img,.hero-image-placeholder{height:280px}
            .topbar-nav{display:none}
            .vant-grid{grid-template-columns:1fr}
            .contato-banner{flex-direction:column;padding:32px 24px;text-align:center}
            .contato-info{justify-content:center}
            .hero-form-card form{grid-template-columns:1fr}
            .steps-track::before{left:20px}
            .step-num{width:40px;height:40px;font-size:1rem}
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
            <div class="hero-text">
                @if(!empty($heroTag))<div class="hero-tag">{{ $heroTag }}</div>@endif
                <h1 class="hero-title">{{ $titulo }}</h1>
                @if(!empty($subtitulo))<div class="hero-sub">{{ $subtitulo }}</div>@endif
                @if(!empty($descricao))<p class="hero-desc">{{ $descricao }}</p>@endif
                <div class="hero-actions">
                    <a href="{{ $botaoUrl }}" class="hero-btn hero-btn-prim">{{ $botaoTexto }}</a>
                    <a href="{{ $botaoLoginUrl }}" class="hero-btn hero-btn-sec">{{ $botaoAdminTexto }}</a>
                </div>
                @if(!empty($heroFormTitulo))
                <div class="hero-form-card">
                    <h2 class="booking-form-title">{{ $heroFormTitulo }}</h2>
                    @if(!empty($heroFormDescricao))<p class="booking-form-desc">{{ $heroFormDescricao }}</p>@endif
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
                            <small class="field-small" id="data_retirada_feedback">Selecione uma estação de retirada para ver os dias disponíveis.</small>
                        </div>
                        <div>
                            <label for="hora_retirada">{{ $heroFormLabelHoraRetirada }}</label>
                            <select id="hora_retirada" name="hora_retirada" disabled>
                                <option value="">Selecione a data</option>
                            </select>
                            <small class="field-small" id="hora_retirada_feedback">Escolha uma data para ver os horários disponíveis.</small>
                        </div>
                        <div class="full" id="devolucaoField">
                            <label for="devolucao">{{ $heroFormLabelDevolucao ?? 'Local de devolução' }}</label>
                            <select id="devolucao" name="devolucao">
                                <option value="">Selecione uma estação</option>
                                @foreach($estacoesDevolucao as $estacao_id => $nome)
                                    <option value="{{ $estacao_id }}">{{ $nome }}</option>
                                @endforeach
                            </select>
                            <small class="field-small" id="devolucao_feedback">Selecione uma estação de devolução.</small>
                        </div>
                        <div>
                            <label for="data_devolucao">{{ $heroFormLabelDataDevolucao }}</label>
                            <input id="data_devolucao" name="data_devolucao" type="text" readonly placeholder="Selecione a data">
                            <small class="field-small" id="data_devolucao_feedback">Selecione uma estação de devolução para ver os dias disponíveis.</small>
                        </div>
                        <div>
                            <label for="hora_devolucao">{{ $heroFormLabelHoraDevolucao }}</label>
                            <select id="hora_devolucao" name="hora_devolucao" disabled>
                                <option value="">Selecione a data</option>
                            </select>
                            <small class="field-small" id="hora_devolucao_feedback">Escolha uma data para ver os horários disponíveis.</small>
                        </div>
                        <button type="submit" class="btn-book">{{ $heroFormTextoBotao }}</button>
                        @if(!empty($heroFormNota))<div class="booking-note">{{ $heroFormNota }}</div>@endif
                    </form>
                </div>
                @endif
            </div>
            <div class="hero-image" style="{{ empty($imagemHero) ? 'display:none' : '' }}">
                <img src="{{ $imagemHero ?? '' }}" alt="Hero {{ $empresa->nome }}" style="{{ empty($imagemHero) ? 'display:none' : '' }}">
                <div class="hero-image-placeholder" style="{{ empty($imagemHero) ? '' : 'display:none' }}"><i class="f-icon">🚗</i></div>
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
            <div class="fleet-scroll">
                @for($fi = 1; $fi <= 10; $fi++)
                    @php $fCard = $frotaDestaques[$fi - 1] ?? null; $fv = $fCard && !empty($fCard['titulo']); @endphp
                    <article class="fleet-card" data-frota="{{ $fi }}" style="{{ $fv ? '' : 'display:none' }}">
                        <div class="fleet-card-img-wrap">
                            <img src="{{ $fCard['imagem'] ?? '' }}" alt="{{ $fCard['titulo'] ?? '' }}" class="fleet-card-img" data-frota-img="{{ $fi }}" style="{{ ($fCard && !empty($fCard['imagem'])) ? '' : 'display:none' }}">
                            <div class="fleet-card-img-placeholder" style="{{ ($fCard && !empty($fCard['imagem'])) ? 'display:none' : '' }}">🚗</div>
                        </div>
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
                            <div class="vant-item-icon">✓</div>
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
            <div class="steps-track">
                @foreach($etapasReserva as $idx => $etapa)
                    @if(!empty($etapa['titulo']))
                    <div class="step-card">
                        <div class="step-num">{{ $idx + 1 }}</div>
                        <div class="step-card-body">
                            <h4 class="step-card-title">{{ $etapa['titulo'] }}</h4>
                            <p class="step-card-desc">{{ $etapa['descricao'] ?? '' }}</p>
                        </div>
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
            <div class="contato-image-wrap" style="{{ empty($imagemContato) ? 'display:none' : '' }}">
                <img src="{{ $imagemContato ?? '' }}" alt="Contato {{ $empresa->nome }}" style="{{ empty($imagemContato) ? 'display:none' : '' }}">
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

            var st=document.createElement('style');
            st.textContent='.se-hover-hint{outline:2px dashed rgba(14,165,233,0.4)!important;outline-offset:2px;cursor:pointer!important}.se-bg-hint{position:relative}.se-bg-hint::after{content:"✎ editar fundo";position:absolute;top:8px;right:12px;font:11px/1 sans-serif;background:rgba(14,165,233,0.9);color:#fff;padding:3px 10px;border-radius:6px;opacity:0;transition:opacity .2s;pointer-events:none;z-index:99}.se-bg-hint:hover::after{opacity:1}.se-img-hint{position:relative}.se-img-hint::after{content:"✎ trocar imagem";position:absolute;bottom:8px;left:50%;transform:translateX(-50%);font:11px/1 sans-serif;background:rgba(0,0,0,0.65);color:#fff;padding:4px 12px;border-radius:6px;opacity:0;transition:opacity .2s;pointer-events:none;white-space:nowrap;z-index:99}.se-img-hint:hover::after{opacity:1}';
            document.head.appendChild(st);
            var sa=function(a,s,c){window.parent.postMessage({type:'se-clicar',alvo:a,secao:s,campo:c},'*')};

            var cm=[
                {sel:'.hero .hero-tag',a:'texto',s:'hero',c:'hero_tag'},
                {sel:'.hero .hero-title',a:'texto',s:'hero',c:'hero_titulo'},
                {sel:'.hero .hero-sub',a:'texto',s:'hero',c:'hero_subtitulo'},
                {sel:'.hero .hero-desc',a:'texto',s:'hero',c:'hero_descricao'},
                {sel:'.hero-form h2',a:'texto',s:'hero',c:'hero_form_titulo'},
                {sel:'#frota .eyebrow',a:'texto',s:'frota',c:'frota_label_superior'},
                {sel:'#frota .section-title',a:'texto',s:'frota',c:'frota_titulo'},
                {sel:'#frota .section-desc',a:'texto',s:'frota',c:'frota_descricao'},
                {sel:'.fleet-card[data-frota="1"] .fleet-card-title',a:'texto',s:'frota',c:'frota_card_1_titulo'},
                {sel:'.fleet-card[data-frota="1"] .fleet-card-desc',a:'texto',s:'frota',c:'frota_card_1_descricao'},
                {sel:'.fleet-card[data-frota="2"] .fleet-card-title',a:'texto',s:'frota',c:'frota_card_2_titulo'},
                {sel:'.fleet-card[data-frota="2"] .fleet-card-desc',a:'texto',s:'frota',c:'frota_card_2_descricao'},
                @for($fi = 3; $fi <= 10; $fi++)
                {sel:'.fleet-card[data-frota="{{$fi}}"] .fleet-card-title',a:'texto',s:'frota',c:'frota_card_{{$fi}}_titulo'},
                {sel:'.fleet-card[data-frota="{{$fi}}"] .fleet-card-desc',a:'texto',s:'frota',c:'frota_card_{{$fi}}_descricao'},
                @endfor
                {sel:'#vantagens .eyebrow',a:'texto',s:'vantagens',c:'vantagens_label_superior'},
                {sel:'#vantagens .section-title',a:'texto',s:'vantagens',c:'vantagens_titulo'},
                {sel:'#vantagens .section-desc',a:'texto',s:'vantagens',c:'vantagens_descricao'},
                {sel:'.vant-item-text',a:'texto',s:'vantagens',c:'beneficio_1'},
                {sel:'#como-funciona .eyebrow',a:'texto',s:'etapas',c:'etapas_label_superior'},
                {sel:'#como-funciona .section-title',a:'texto',s:'etapas',c:'etapas_titulo'},
                {sel:'.step-card:nth-child(1) .step-card-title',a:'texto',s:'etapas',c:'etapa_1_titulo'},
                {sel:'.step-card:nth-child(1) .step-card-desc',a:'texto',s:'etapas',c:'etapa_1_descricao'},
                {sel:'.step-card:nth-child(2) .step-card-title',a:'texto',s:'etapas',c:'etapa_2_titulo'},
                {sel:'.step-card:nth-child(2) .step-card-desc',a:'texto',s:'etapas',c:'etapa_2_descricao'},
                {sel:'.step-card:nth-child(3) .step-card-title',a:'texto',s:'etapas',c:'etapa_3_titulo'},
                {sel:'.step-card:nth-child(3) .step-card-desc',a:'texto',s:'etapas',c:'etapa_3_descricao'},
                {sel:'#contato .contato-eyebrow',a:'texto',s:'contato',c:'contato_label_superior'},
                {sel:'#contato .contato-title',a:'texto',s:'contato',c:'contato_titulo'},
            ];
            cm.forEach(function(i){var e=document.querySelector(i.sel);if(!e)return;e.classList.add('se-hover-hint');e.addEventListener('click',function(ev){ev.stopPropagation();ev.preventDefault();sa(i.a,i.s,i.c)})});

            var bg=[{sel:'.hero',s:'hero'},{sel:'#frota',s:'frota'},{sel:'#vantagens',s:'vantagens'},{sel:'#como-funciona',s:'etapas'},{sel:'#contato',s:'contato'}];
            bg.forEach(function(i){var e=document.querySelector(i.sel);if(!e)return;e.classList.add('se-bg-hint');e.addEventListener('click',function(ev){if(ev.target===this||!ev.target.closest('.se-hover-hint'))sa('fundo',i.s,null)})});

            var im=[{sel:'.hero-image img',s:'hero',c:'imagem_hero'},{sel:'#frota .fleet-card[data-frota="1"] img',s:'frota',c:'imagem_frota_1'},{sel:'#frota .fleet-card[data-frota="2"] img',s:'frota',c:'imagem_frota_2'},{sel:'#frota .fleet-card[data-frota="3"] img',s:'frota',c:'imagem_frota_3'}];
            @for($fi = 4; $fi <= 10; $fi++)
            im.push({sel:'#frota .fleet-card[data-frota="{{$fi}}"] img',s:'frota',c:'imagem_frota_{{$fi}}'});
            @endfor
            im.forEach(function(i){var e=document.querySelector(i.sel);if(!e)return;e.classList.add('se-img-hint');e.style.cursor='pointer';e.addEventListener('click',function(ev){ev.stopPropagation();ev.preventDefault();sa('imagem',i.s,i.c)})});

            var cssMap={
                cor_primaria:'--brand-p',cor_secundaria:'--brand-s',cor_terciaria:'--brand-a',
                cor_topbar_fundo:'--top-bg',cor_topbar_texto:'--top-txt',cor_topbar_link_hover:'--top-hv',
                cor_hero_fundo:'--hero-bg',cor_hero_texto:'--hero-txt',
                cor_form_fundo:'--form-bg',cor_form_texto:'--form-txt',
                cor_frota_fundo:'--frota-bg',cor_frota_etiqueta_texto:'--frota-et',cor_frota_titulo_texto:'--frota-tt',cor_frota_descricao_texto:'--frota-dt',
                cor_frota_card_titulo_texto:'--frota-ct',cor_frota_card_descricao_texto:'--frota-cd',cor_frota_card_fundo:'--frota-cb',
                cor_vantagens_fundo:'--vant-bg',cor_vantagens_etiqueta_texto:'--vant-et',cor_vantagens_titulo_texto:'--vant-tt',
                cor_vantagens_descricao_texto:'--vant-dt',cor_vantagens_item_texto:'--vant-it',
                cor_etapas_fundo:'--etap-bg',cor_etapas_etiqueta_texto:'--etap-et',cor_etapas_titulo_texto:'--etap-tt',
                cor_etapas_descricao_texto:'--etap-dt',cor_etapas_passo_texto:'--etap-pt',cor_etapas_passo_descricao_texto:'--etap-pd',cor_etapas_passo_fundo:'--etap-pb',
                cor_contato_fundo:'--cont-bg',cor_contato_etiqueta_texto:'--cont-et',cor_contato_titulo_texto:'--cont-tt',cor_contato_info_texto:'--cont-it',
                cor_footer_fundo:'--foot-bg',cor_footer_texto:'--foot-txt',
                cor_botao_primario_fundo:'--btn1-bg',cor_botao_primario_texto:'--btn1-txt',
                cor_botao_secundario_fundo:'--btn2-bg',cor_botao_secundario_texto:'--btn2-txt',
            };

            window.addEventListener('message',function(e){
                if(!e.data||e.data.type!=='se-carregar'&&e.data.type!=='se-atualizar')return;
                var d=e.data.dados||{},du=e.data.dataUrls||{},rt=document.documentElement;
                for(var k in cssMap){if(d[k])rt.style.setProperty(cssMap[k],d[k])}
                if(d.cor_primaria){var b=(function(h){h=h.replace('#','');return(parseInt(h.substr(0,2),16)*299+parseInt(h.substr(2,2),16)*587+parseInt(h.substr(4,2),16)*114)/1000})(d.cor_primaria);rt.style.setProperty('--brand-pt',b>=155?'#0F172A':'#FFFFFF')}
                for(var fi=1;fi<=10;fi++){
                    var c=document.querySelector('.fleet-card[data-frota="'+fi+'"]');if(!c)continue;
                    var tk='frota_card_'+fi+'_titulo',dk='frota_card_'+fi+'_descricao',t=d[tk],de=d[dk];
                    var te=c.querySelector('[data-frota-titulo="'+fi+'"]'),de2=c.querySelector('[data-frota-desc="'+fi+'"]');
                    if(t&&t.trim()){c.style.display='';if(te)te.textContent=t;if(de2)de2.textContent=de||''}
                    else c.style.display='none'
                }
                var imgVisM2={hero:['.hero-image','.hero-image img','.hero-image-placeholder'],contato:['.contato-image-wrap']};
                for(var vkm in imgVisM2){var uk='usar_imagem_'+vkm;if(d[uk]===undefined)continue;var c=document.querySelector(imgVisM2[vkm][0]);if(!c)continue;c.style.display=d[uk]==='1'?'':'none';if(imgVisM2[vkm][1]){var ig=document.querySelector(imgVisM2[vkm][1]);if(ig)ig.style.display=d[uk]==='1'?'':'none'}if(imgVisM2[vkm][2]){var ph=document.querySelector(imgVisM2[vkm][2]);if(ph)ph.style.display=d[uk]==='1'?'none':''}}
                for(var fi=1;fi<=10;fi++){var uk='usar_imagem_frota_'+fi;if(d[uk]===undefined)continue;var cel=document.querySelector('.fleet-card[data-frota="'+fi+'"]');if(!cel)continue;var ig=cel.querySelector('.fleet-card-img'),pl=cel.querySelector('.fleet-card-img-placeholder');if(ig)ig.style.display=d[uk]==='1'?'':'none';if(pl)pl.style.display=d[uk]==='1'?'none':''}
                var imgMap={imagem_hero:'.hero-image img'};for(var ik in imgMap){if(!du[ik])continue;var ie=document.querySelector(imgMap[ik]);if(ie)ie.src=du[ik]}
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
                if (retiradaDate && retiradaDate < today) { dataRetirada.setCustomValidity('A retirada não pode ser antes de hoje.'); dataRetirada.reportValidity(); return false; }
                dataRetirada.setCustomValidity('');
                if (retiradaDate && retiradaDate.getTime() === today.getTime() && horaRetirada.value) {
                    const dt = new Date(`${dataRetirada.value}T${horaRetirada.value}`);
                    if (dt < now) { horaRetirada.setCustomValidity('A hora de retirada não pode ser no passado.'); horaRetirada.reportValidity(); return false; }
                }
                horaRetirada.setCustomValidity('');
                if (dataRetirada.value && dataDevolucao.value) {
                    const minDevol = addDays(new Date(`${dataRetirada.value}T00:00:00`), 1);
                    if (devolDate < minDevol) { dataDevolucao.setCustomValidity('A devolução deve ser ao menos 1 dia após a retirada.'); dataDevolucao.reportValidity(); return false; }
                }
                dataDevolucao.setCustomValidity('');
                return true;
            }
            function estacaoPermiteCheckout(estacaoId) {
                if (!devolucaoSelect || !estacaoId) return false;
                return !!devolucaoSelect.querySelector(`option[value="${estacaoId}"]`);
            }
            function syncDevolucao(triggeredFromRetirada = false) {
                if (!devolucaoField || !devolucaoSelect) return;
                const retiradaId = retiradaSelect?.value || '';
                const podeMesmoLocal = estacaoPermiteCheckout(retiradaId);
                const retiradaTexto = retiradaSelect?.selectedOptions?.[0]?.textContent?.trim() || '';
                devolucaoField.style.display = 'block';
                devolucaoSelect.disabled = false;
                if (podeMesmoLocal && retiradaId) {
                    const valorAnterior = devolucaoSelect.value;
                    devolucaoSelect.value = retiradaId;
                    if (triggeredFromRetirada && valorAnterior !== retiradaId) devolucaoSelect.dispatchEvent(eventoChange);
                    if (devolucaoFeedback) devolucaoFeedback.textContent = retiradaTexto ? `Estação de devolução definida automaticamente: ${retiradaTexto}.` : 'Estação de devolução definida automaticamente.';
                } else if (!podeMesmoLocal && devolucaoSelect.value === retiradaId) {
                    devolucaoSelect.value = '';
                    if (devolucaoFeedback) devolucaoFeedback.textContent = 'Selecione uma estação de devolução.';
                } else if (devolucaoFeedback) {
                    devolucaoFeedback.textContent = devolucaoSelect.value ? `Estação de devolução selecionada: ${devolucaoSelect.selectedOptions?.[0]?.textContent?.trim() || ''}.` : 'Selecione uma estação de devolução.';
                }
            }
            retiradaSelect?.addEventListener('change', () => { syncDevolucao(true); });
            const ptLocale = window.flatpickr?.l10ns?.pt || {};
            function toIsoDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
            function normalizarSlotHorario(slot) {
                if (slot && typeof slot === 'object') {
                    const value = String(slot.value || slot.hora || slot.time || '').trim();
                    const label = String(slot.label || slot.text || value).trim();
                    if (!value) return null;
                    return { value, label: label || value, taxa_extra: Boolean(slot.taxa_extra || slot.status === 'taxa_extra') };
                }
                const value = String(slot || '').trim();
                if (!value) return null;
                return { value, label: value, taxa_extra: false };
            }
            function mostrarCarregandoHorarios(selectEl, feedbackEl) {
                if (!selectEl) return;
                selectEl.innerHTML = '';
                selectEl.appendChild(new Option('Carregando horários...', ''));
                selectEl.disabled = true;
                if (feedbackEl) feedbackEl.textContent = 'Carregando horários disponíveis...';
            }
            function mostrarCarregandoDatas(feedbackEl, mensagem) {
                if (!feedbackEl) return;
                feedbackEl.textContent = mensagem || 'Carregando datas disponíveis...';
            }
            function preencherHorarios(selectEl, feedbackEl, slots, horaPreferida = null) {
                const lista = Array.isArray(slots) ? slots.map(normalizarSlotHorario).filter(Boolean) : [];
                selectEl.innerHTML = '';
                if (!lista.length) {
                    selectEl.appendChild(new Option('Sem horários disponíveis', ''));
                    selectEl.disabled = true;
                    if (feedbackEl) feedbackEl.textContent = 'Sem horários disponíveis para a data selecionada.';
                    return [];
                }
                selectEl.appendChild(new Option('Selecione a hora', ''));
                lista.forEach(function(slot) {
                    const valor = String(slot.value).includes('T') ? String(slot.value).split('T')[1].slice(0, 5) : String(slot.value).slice(0, 5);
                    const texto = slot.taxa_extra ? `${slot.label} • Taxa extra` : slot.label;
                    const option = new Option(texto, valor);
                    option.dataset.taxaExtra = slot.taxa_extra ? '1' : '0';
                    selectEl.appendChild(option);
                });
                selectEl.disabled = false;
                const preferida = horaPreferida ? String(horaPreferida).slice(0, 5) : (lista.length === 1 ? String(lista[0].value).slice(0, 5) : '');
                if (preferida && Array.from(selectEl.options).some((option) => option.value === preferida)) selectEl.value = preferida;
                else selectEl.value = '';
                if (feedbackEl) feedbackEl.textContent = 'Escolha a hora disponível.';
                return lista;
            }
            function carregarHorariosAgenda(tipo, dataBase, horaPreferida = null) {
                const estacaoId = tipo === 'devolucao' ? devolucaoSelect?.value : retiradaSelect?.value;
                if (!estacaoId || !dataBase) return Promise.resolve([]);
                if (tipo === 'devolucao') mostrarCarregandoHorarios(horaDevolucao, horaDevolucaoFeedback);
                else mostrarCarregandoHorarios(horaRetirada, horaRetiradaFeedback);
                const params = new URLSearchParams({ estacao_id: estacaoId, tipo: tipo || 'retirada', data_base: dataBase, limite: 1 });
                return fetch(`${agendaUrl}?${params.toString()}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(function(response) {
                    const agenda = Array.isArray(response.agenda) ? response.agenda : [];
                    const dia = agenda.find(item => String(item.date) === String(dataBase)) || agenda[0] || null;
                    const slots = Array.isArray(dia?.slots) ? dia.slots : [];
                    if (tipo === 'devolucao') return preencherHorarios(horaDevolucao, horaDevolucaoFeedback, slots, horaPreferida);
                    return preencherHorarios(horaRetirada, horaRetiradaFeedback, slots, horaPreferida);
                })
                .catch(function() {
                    if (tipo === 'devolucao') preencherHorarios(horaDevolucao, horaDevolucaoFeedback, [], null);
                    else preencherHorarios(horaRetirada, horaRetiradaFeedback, [], null);
                    return [];
                });
            }
            function buscarDiasDisponiveis(estacaoId, tipo) {
                if (!estacaoId) return Promise.resolve([]);
                const params = new URLSearchParams({ estacao_id: estacaoId, dias: 90, tipo: tipo || 'retirada' });
                return fetch(`${diasDisponiveisUrl}?${params.toString()}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(function(response) { return Array.isArray(response.dias_disponiveis) ? response.dias_disponiveis : []; })
                .catch(function() { return []; });
            }
            function criarPickerDate(inputEl, diasDisponiveis) {
                if (!inputEl || !window.flatpickr) return null;
                const allowed = new Set((diasDisponiveis || []).map(function(item) { return String(item.data || item).slice(0, 10); }));
                return window.flatpickr(inputEl, {
                    locale: ptLocale, altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', disableMobile: true, minDate: 'today',
                    disable: [function(date) { return !allowed.has(toIsoDate(date)); }],
                    onChange: function(selectedDates) { const date = selectedDates[0] || null; inputEl.dispatchEvent(new Event('change', { bubbles: true })); return date; }
                });
            }
            function getDataRetiradaSelecionada() {
                if (!dataRetirada?.value) return null;
                const data = new Date(`${dataRetirada.value}T00:00:00`);
                return Number.isNaN(data.getTime()) ? null : data;
            }
            function atualizarMinDateDevolucao() {
                const retirada = getDataRetiradaSelecionada();
                if (!retirada) return;
                const minDevolucao = new Date(retirada.getTime() + 86400000);
                if (pickerDevolucao) {
                    pickerDevolucao.set('minDate', minDevolucao);
                    const selecionada = pickerDevolucao.selectedDates[0] || null;
                    if (selecionada && selecionada.getTime() < minDevolucao.getTime()) {
                        pickerDevolucao.clear(); dataDevolucao.value = ''; horaDevolucao.value = ''; horaDevolucao.disabled = true;
                        if (dataDevolucaoFeedback) dataDevolucaoFeedback.textContent = 'Escolha uma data de devolução igual ou posterior ao dia seguinte.';
                    }
                } else if (window.flatpickr) {
                    pickerDevolucao = window.flatpickr(dataDevolucao, {
                        locale: ptLocale, altInput: true, altFormat: 'd/m/Y', dateFormat: 'Y-m-d', disableMobile: true,
                        minDate: minDevolucao,
                        onChange: function() { dataDevolucao.dispatchEvent(new Event('change', { bubbles: true })); }
                    });
                }
            }
            function atualizarRetirada() {
                const data = dataRetirada.value || '';
                const hora = horaRetirada.value || '';
                if (!data) { horaRetirada.value = ''; horaRetirada.disabled = true; dataRetiradaFeedback && (dataRetiradaFeedback.textContent = 'Selecione uma estação de retirada para ver os dias disponíveis.'); return; }
                dataRetiradaFeedback && (dataRetiradaFeedback.textContent = 'Selecione a hora de retirada.');
                atualizarMinDateDevolucao();
                carregarHorariosAgenda('retirada', data, hora);
            }
            function atualizarDevolucao() {
                const data = dataDevolucao.value || '';
                const hora = horaDevolucao.value || '';
                if (!data) { horaDevolucao.value = ''; horaDevolucao.disabled = true; dataDevolucaoFeedback && (dataDevolucaoFeedback.textContent = 'Selecione uma estação de devolução para ver os dias disponíveis.'); return; }
                dataDevolucaoFeedback && (dataDevolucaoFeedback.textContent = 'Selecione a hora de devolução.');
                carregarHorariosAgenda('devolucao', data, hora);
            }
            function recarregarPickersPorEstacao() {
                const estRet = retiradaSelect?.value || '';
                const estDev = devolucaoSelect?.value || '';
                if (pickerRetirada) { pickerRetirada.destroy(); pickerRetirada = null; }
                if (pickerDevolucao) { pickerDevolucao.destroy(); pickerDevolucao = null; }
                dataRetirada.value = ''; dataDevolucao.value = ''; horaRetirada.value = ''; horaDevolucao.value = '';
                horaRetirada.disabled = true; horaDevolucao.disabled = true;
                if (estRet) {
                    mostrarCarregandoDatas(dataRetiradaFeedback, 'Carregando datas de retirada...');
                    buscarDiasDisponiveis(estRet, 'retirada').then(function(dias) {
                        if (dataRetiradaFeedback) dataRetiradaFeedback.textContent = dias.length ? 'Selecione a data de retirada.' : 'Não há dias disponíveis para retirada nesta estação.';
                        pickerRetirada = criarPickerDate(dataRetirada, dias);
                        atualizarMinDateDevolucao();
                    });
                }
                if (estDev) {
                    mostrarCarregandoDatas(dataDevolucaoFeedback, 'Carregando datas de devolução...');
                    buscarDiasDisponiveis(estDev, 'devolucao').then(function(dias) {
                        if (dataDevolucaoFeedback) dataDevolucaoFeedback.textContent = dias.length ? 'Selecione a data de devolução.' : 'Não há dias disponíveis para devolução nesta estação.';
                        pickerDevolucao = criarPickerDate(dataDevolucao, dias);
                        atualizarMinDateDevolucao();
                    });
                }
            }
            dataRetirada.addEventListener('change', atualizarRetirada);
            horaRetirada.addEventListener('change', atualizarRetirada);
            dataDevolucao.addEventListener('change', atualizarDevolucao);
            horaDevolucao.addEventListener('change', atualizarDevolucao);
            retiradaSelect?.addEventListener('change', function() { syncDevolucao(true); recarregarPickersPorEstacao(); });
            devolucaoSelect?.addEventListener('change', function() { syncDevolucao(false); recarregarPickersPorEstacao(); });
            if (typeof window.Choices !== 'undefined') {
                [retiradaSelect, devolucaoSelect].forEach(sel => {
                    if (!sel) return;
                    new window.Choices(sel, { searchEnabled: true, searchPlaceholderValue: 'Buscar estação...', itemSelectText: '', shouldSort: false, noResultsText: 'Nenhuma estação encontrada', noChoicesText: 'Nenhuma estação disponível' });
                });
            }
            dataRetirada?.addEventListener('change', () => { normalizeMinTime(); normalizeMinDates(); validateDates(); });
            horaRetirada?.addEventListener('change', normalizeMinTime);
            dataDevolucao?.addEventListener('change', () => { normalizeMinDates(); validateDates(); });
            form?.addEventListener('submit', e => { normalizeMinTime(); normalizeMinDates(); syncDevolucao(); if (!validateDates()) e.preventDefault(); });
            syncDevolucao();
            recarregarPickersPorEstacao();
            normalizeMinDates();
            normalizeMinTime();
        })();
    </script>
</body>
</html>
