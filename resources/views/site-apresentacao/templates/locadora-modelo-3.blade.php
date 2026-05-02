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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
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
            --ink:#111;--ink2:#333;--ink3:#666;--line:#e5e5e5;--white:#fff;--off:#f5f5f5;
            --r:0;--r-lg:0;
            --sh:0 0 0 1px rgba(0,0,0,.04);--sh-md:0 4px 20px rgba(0,0,0,.06);--sh-lg:0 12px 48px rgba(0,0,0,.08);
        }
        body{font-family:Inter,sans-serif;color:var(--ink);background:var(--white);-webkit-font-smoothing:antialiased}
        a{text-decoration:none;color:inherit}
        img{display:block;max-width:100%}

        .hdr{position:sticky;top:0;z-index:100;background:var(--tb);border-bottom:1px solid var(--line)}
        .hdr-inner{display:flex;align-items:center;justify-content:space-between;height:56px;padding:0 32px;max-width:1400px;margin-inline:auto;gap:16px}
        .hdr-brand{font-family:Outfit,sans-serif;font-weight:700;font-size:1rem;color:var(--tt);display:flex;align-items:center;gap:8px;flex-shrink:0}
        .hdr-brand img{height:30px;width:auto}
        .hdr-nav{display:flex;gap:4px}
        .hdr-nav a{padding:6px 14px;font-size:.82rem;font-weight:500;color:var(--tt);transition:all .15s;border-radius:20px}
        .hdr-nav a:hover{background:var(--th)}
        .hdr-cta{padding:7px 20px;font-size:.8rem;font-weight:600;border-radius:20px;background:var(--b1-b);color:var(--b1-t);transition:all .15s;flex-shrink:0}
        .hdr-cta:hover{opacity:.85}

        .hero{background:var(--hb);color:var(--ht);min-height:90vh;display:flex;align-items:center;padding:80px 32px;position:relative}
        .hero-inner{max-width:1400px;margin-inline:auto;width:100%;display:grid;grid-template-columns:1.2fr .8fr;gap:64px;align-items:center}
        .hero-tag{font-size:.7rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--a);margin-bottom:20px;display:inline-block;padding:6px 16px;border:1px solid;border-radius:20px}
        .hero-title{font-family:Outfit,sans-serif;font-size:clamp(2.4rem,4vw+1rem,4.8rem);font-weight:800;line-height:.95;margin-bottom:18px;letter-spacing:-.03em}
        .hero-sub{font-size:1.05rem;line-height:1.5;opacity:.7;margin-bottom:6px}
        .hero-desc{font-size:.92rem;line-height:1.7;opacity:.5;margin-bottom:36px;max-width:44ch}
        .hero-actions{display:flex;gap:12px;flex-wrap:wrap}
        .hero-btn{display:inline-flex;align-items:center;gap:8px;padding:14px 32px;font-weight:600;font-size:.88rem;border:none;cursor:pointer;transition:all .2s}
        .hero-btn-p{background:var(--b1-b);color:var(--b1-t)}
        .hero-btn-s{color:var(--ht);border:1px solid;opacity:.5;background:transparent}
        .hero-btn-s:hover{opacity:1}
        .hero-btn:hover{transform:translateY(-3px);box-shadow:var(--sh-lg)}

        .hero-form{background:var(--fb);color:var(--ft);padding:32px}
        .hero-form h2{font-family:Outfit,sans-serif;font-size:1.2rem;font-weight:700;margin-bottom:4px}
        .hero-form .desc{font-size:.84rem;opacity:.6;margin-bottom:20px}
        .hero-form form{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .hero-form .full{grid-column:1/-1}
        .hero-form label{font-size:.75rem;font-weight:600;display:block;margin-bottom:3px;opacity:.7}
        .hero-form select,.hero-form input{padding:10px 12px;border:1px solid var(--line);font-size:.84rem;width:100%;background:var(--white);color:var(--ink)}
        .hero-form .btn-book{padding:12px 24px;border:none;background:var(--b1-b);color:var(--b1-t);font-weight:600;font-size:.88rem;cursor:pointer;grid-column:1/-1;justify-self:start}
        .hero-form .btn-book:hover{opacity:.9}
        .hero-form small{font-size:.7rem;opacity:.5;display:block;margin-top:2px}
        .hero-form .note{font-size:.75rem;opacity:.5;margin-top:10px;grid-column:1/-1}

        .sec{padding:96px 32px}
        .sec--alt{background:var(--off)}
        .sec--frota{background:var(--frota-b)}
        .sec--vant{background:var(--vant-b)}
        .sec--etapas{background:var(--etap-b)}
        .sec-wrap{max-width:1400px;margin-inline:auto}
        .sec-head{margin-bottom:56px}
        .sec-head .eyebrow{font-size:.65rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--a);margin-bottom:10px}
        .sec--frota .eyebrow{color:var(--frota-e)}.sec--vant .eyebrow{color:var(--vant-e)}.sec--etapas .eyebrow{color:var(--etap-e)}
        .sec-head h2{font-family:Outfit,sans-serif;font-size:clamp(1.8rem,3vw+.6rem,3.2rem);font-weight:800;letter-spacing:-.03em;line-height:1;margin-bottom:10px}
        .sec--frota .sec-head h2{color:var(--frota-t)}.sec--vant .sec-head h2{color:var(--vant-t)}.sec--etapas .sec-head h2{color:var(--etap-t)}
        .sec-head p{font-size:.92rem;line-height:1.7;color:var(--ink3);max-width:48ch}
        .sec--frota .sec-head p{color:var(--frota-d)}.sec--vant .sec-head p{color:var(--vant-d)}.sec--etapas .sec-head p{color:var(--etap-d)}

        .fleet{display:grid;grid-template-columns:1fr 1fr;gap:2px;background:var(--line)}
        .fleet-card{background:var(--frota-cb);padding:32px;display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:center;position:relative}
        .fleet-card:nth-child(even){direction:rtl;text-align:left}
        .fleet-card:nth-child(even) .fleet-card-body{direction:ltr}
        .fleet-card-img{width:100%;height:200px;object-fit:cover;filter:grayscale(1);transition:filter .4s}
        .fleet-card:hover .fleet-card-img{filter:grayscale(0)}
        .fleet-card-img-placeholder{height:200px;background:var(--off);display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:var(--ink3);opacity:.2}
        .fleet-card-num{font-family:Outfit,sans-serif;font-size:.7rem;font-weight:700;color:var(--a);margin-bottom:4px;letter-spacing:.05em}
        .fleet-card-title{font-family:Outfit,sans-serif;font-size:1.1rem;font-weight:700;color:var(--frota-ct);margin-bottom:4px;letter-spacing:-.02em}
        .fleet-card-desc{font-size:.86rem;line-height:1.6;color:var(--frota-cd)}

        .vant{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--line)}
        .vant-item{padding:40px 28px;background:var(--vant-b);text-align:center}
        .vant-item-icon{font-size:2rem;margin-bottom:12px;display:block}
        .vant-item-text{font-size:.88rem;font-weight:500;color:var(--vant-i);line-height:1.6;display:block}

        .etapas{display:grid;grid-template-columns:repeat(3,1fr);gap:2px;background:var(--line)}
        .etapa-card{background:var(--etap-pb);padding:40px 32px;text-align:center}
        .etapa-num{font-family:Outfit,sans-serif;font-size:3rem;font-weight:800;color:var(--a);line-height:1;margin-bottom:12px;letter-spacing:-.04em}
        .etapa-title{font-family:Outfit,sans-serif;font-size:1.05rem;font-weight:700;color:var(--etap-pt);margin-bottom:6px;letter-spacing:-.02em}
        .etapa-desc{font-size:.86rem;line-height:1.6;color:var(--etap-pd);max-width:32ch;margin-inline:auto}

        .contato{padding:96px 32px;background:var(--cont-b)}
        .contato-wrap{max-width:1400px;margin-inline:auto}
        .contato-grid{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
        .contato-eyebrow{font-size:.65rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--cont-e);margin-bottom:10px}
        .contato-title{font-family:Outfit,sans-serif;font-size:clamp(1.6rem,2.4vw+.6rem,2.6rem);font-weight:800;letter-spacing:-.03em;line-height:1;color:var(--cont-t);margin-bottom:20px}
        .contato-info{display:flex;flex-direction:column;gap:4px}
        .contato-info-item{font-size:.9rem;color:var(--cont-i)}
        .contato-cta{display:inline-flex;align-items:center;gap:8px;margin-top:24px;padding:14px 32px;font-weight:600;font-size:.88rem;background:var(--b1-b);color:var(--b1-t);border:none;cursor:pointer;transition:all .2s}
        .contato-cta:hover{transform:translateY(-2px);box-shadow:var(--sh-lg)}
        .contato-image{overflow:hidden}
        .contato-image img{width:100%;height:360px;object-fit:cover}
        .contato-image-placeholder{height:360px;background:var(--line);display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--ink3);opacity:.15}

        .footer{background:var(--foot-b);color:var(--foot-t);padding:32px;border-top:1px solid var(--line)}
        .footer-inner{max-width:1400px;margin-inline:auto;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;font-size:.82rem}
        .footer-text{opacity:.4}
        .footer-nav{display:flex;gap:24px}
        .footer-nav a{opacity:.35;transition:opacity .2s}
        .footer-nav a:hover{opacity:.7}

        @media(max-width:768px){
            .hero-inner{grid-template-columns:1fr;gap:40px}
            .hero{min-height:auto;padding:48px 24px}
            .hero-form form{grid-template-columns:1fr}
            .hdr-nav{display:none}
            .fleet{grid-template-columns:1fr}
            .fleet-card,.fleet-card:nth-child(even){grid-template-columns:1fr;direction:ltr;text-align:left}
            .vant{grid-template-columns:1fr 1fr}
            .etapas{grid-template-columns:1fr}
            .contato-grid{grid-template-columns:1fr;gap:32px}
            .sec{padding:64px 24px}
        }
    </style>
</head>
<body>
    <header class="hdr">
        <div class="hdr-inner">
            <a href="#" class="hdr-brand">
                @if(!empty($empresa->logo_url))
                    <img src="{{ $empresa->logo_url }}" alt="{{ $empresa->nome }}">
                @else
                    {{ $empresa->nome }}
                @endif
            </a>
            <nav class="hdr-nav">
                <a href="#frota">{{ $menuItemFrota }}</a>
                <a href="#vantagens">{{ $menuItemVantagens }}</a>
                <a href="#etapas">{{ $menuItemEtapas }}</a>
                <a href="#contato">{{ $menuItemContato }}</a>
            </nav>
            <a href="{{ $botaoUrl }}" class="hdr-cta">{{ $botaoTexto }}</a>
        </div>
    </header>

    <section class="hero">
        <div class="hero-inner">
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

    <section id="vantagens" class="sec sec--vant">
        <div class="sec-wrap">
            <div class="sec-head">
                @if(!empty($vantagensLabelSuperior))<div class="eyebrow">{{ $vantagensLabelSuperior }}</div>@endif
                <h2>{{ $vantagensTitulo }}</h2>
                @if(!empty($vantagensDescricao))<p>{{ $vantagensDescricao }}</p>@endif
            </div>
            <div class="vant">
                @foreach($beneficios as $beneficio)
                    @if(!empty($beneficio))
                        <div class="vant-item">
                            <span class="vant-item-icon">◆</span>
                            <span class="vant-item-text">{{ $beneficio }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <section id="frota" class="sec sec--frota">
        <div class="sec-wrap">
            <div class="sec-head">
                @if(!empty($frotaLabelSuperior))<div class="eyebrow">{{ $frotaLabelSuperior }}</div>@endif
                <h2>{{ $frotaTitulo }}</h2>
                @if(!empty($frotaDescricao))<p>{{ $frotaDescricao }}</p>@endif
            </div>
            <div class="fleet">
                @for($fi = 1; $fi <= 10; $fi++)
                    @php $fCard = $frotaDestaques[$fi - 1] ?? null; $fv = $fCard && !empty($fCard['titulo']); @endphp
                    <article class="fleet-card" data-frota="{{ $fi }}" style="{{ $fv ? '' : 'display:none' }}">
                        @if($fCard && !empty($fCard['imagem']))
                            <img src="{{ $fCard['imagem'] }}" alt="{{ $fCard['titulo'] }}" class="fleet-card-img" data-frota-img="{{ $fi }}">
                        @else
                            <div class="fleet-card-img-placeholder">◆</div>
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

    <section id="como-funciona" class="sec sec--etapas">
        <div class="sec-wrap">
            <div class="sec-head">
                @if(!empty($etapasLabelSuperior))<div class="eyebrow">{{ $etapasLabelSuperior }}</div>@endif
                <h2>{{ $etapasTitulo }}</h2>
            </div>
            <div class="etapas">
                @foreach($etapasReserva as $idx => $etapa)
                    @if(!empty($etapa['titulo']))
                    <div class="etapa-card">
                        <div class="etapa-num">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</div>
                        <h4 class="etapa-title">{{ $etapa['titulo'] }}</h4>
                        <p class="etapa-desc">{{ $etapa['descricao'] ?? '' }}</p>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <section id="contato" class="contato">
        <div class="contato-wrap"><div class="contato-grid">
            <div>
                @if(!empty($contatoLabelSuperior))<div class="contato-eyebrow">{{ $contatoLabelSuperior }}</div>@endif
                <div class="contato-title">{{ $contatoTitulo }}</div>
                <div class="contato-info">
                    @if(!empty($contatoTelefone))<span class="contato-info-item">{{ $contatoTelefone }}</span>@endif
                    @if(!empty($contatoEmail))<span class="contato-info-item">{{ $contatoEmail }}</span>@endif
                </div>
                <a href="{{ $botaoUrl }}" class="contato-cta">{{ $botaoTexto }}</a>
            </div>
            <div class="contato-image">
                @if(!empty($imagemContato))
                    <img src="{{ $imagemContato }}" alt="Contato {{ $empresa->nome }}">
                @else
                    <div class="contato-image-placeholder">◆</div>
                @endif
            </div>
        </div></div>
    </section>

    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-text">{{ $footerTexto }}</div>
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
                cor_botao_primario_fundo:'--b1-b',cor_botao_primario_texto:'--b1-t',cor_botao_secundario_fundo:'--b2-b',cor_botao_secundario_texto:'--b2-t',
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
                var imgMap={imagem_hero:'img'};
                for(var ik in imgMap){if(!du[ik])continue;var ie=document.querySelector(imgMap[ik]);if(ie)ie.src=du[ik]}
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
        function valD(){if(!DR||!HR||!DD)return true;const n=new Date(),td=new Date(n.getFullYear(),n.getMonth(),n.getDate()),rd=DR.value?new Date(DR.value+'T00:00:00'):null,dd=DD.value?new Date(DD.value+'T00:00:00'):null;if(rd&&rd<td){DR.setCustomValidity('Inválido');DR.reportValidity();return false}DR.setCustomValidity('');if(rd&&rd.getTime()===td.getTime()&&HR.value&&new Date(DR.value+'T'+HR.value)<n){HR.setCustomValidity('Inválido');HR.reportValidity();return false}HR.setCustomValidity('');if(DR.value&&DD.value){const md=addD(new Date(DR.value+'T00:00:00'),1);if(dd<md){DD.setCustomValidity('Inválido');DD.reportValidity();return false}}DD.setCustomValidity('');return true}
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
