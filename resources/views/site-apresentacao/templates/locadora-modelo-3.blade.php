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
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            --ink:#0c0c0c;--ink2:#2a2a2a;--ink3:#888;--line:#eaeaea;--white:#fff;--off:#f8f8f8;
            --w:1200px;--g:24px
        }
        body{font-family:Inter,sans-serif;color:var(--ink);background:var(--white);-webkit-font-smoothing:antialiased;font-size:15px;line-height:1.6}
        a{text-decoration:none;color:inherit}
        img{display:block;max-width:100%}

        .mast{position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:100;display:flex;align-items:center;justify-content:space-between;background:var(--tb);padding:8px 20px 8px 24px;border:1px solid var(--line);box-shadow:var(--sh);width:calc(min(var(--w),calc(100% - 48px)));border-radius:40px}
        .mast-brand{font-family:'DM Serif Display',serif;font-size:1.1rem;color:var(--tt)}
        .mast-brand img{height:28px;width:auto}
        .mast-nav{display:flex;gap:2px}
        .mast-nav a{padding:6px 14px;font-size:.78rem;font-weight:500;color:var(--tt);transition:all .15s;border-radius:20px}
        .mast-nav a:hover{background:var(--th)}
        .mast-cta{padding:6px 18px;font-size:.78rem;font-weight:600;border-radius:20px;background:var(--b1-b);color:var(--b1-t);transition:opacity .15s}
        .mast-cta:hover{opacity:.85}

        .hero{padding:140px 24px 80px;background:var(--hb);color:var(--ht);min-height:90vh;display:flex;align-items:center}
        .hero-inner{width:min(var(--w),100%);margin-inline:auto;display:grid;grid-template-columns:1.3fr .7fr;gap:64px;align-items:center}
        .hero-tag{font-size:.7rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--a);margin-bottom:24px;display:inline-block;border-bottom:2px solid;padding-bottom:4px}
        .hero-title{font-family:'DM Serif Display',serif;font-size:clamp(2.4rem,4vw+1rem,4.6rem);line-height:1;margin-bottom:20px;font-weight:400;letter-spacing:-.02em}
        .hero-sub{font-size:1.1rem;line-height:1.4;opacity:.65;font-weight:400;margin-bottom:4px}
        .hero-desc{font-size:.9rem;line-height:1.7;opacity:.45;margin-bottom:36px;max-width:40ch}
        .hero-actions{display:flex;gap:12px;flex-wrap:wrap}
        .hero-btn{padding:14px 36px;font-weight:600;font-size:.85rem;border:2px solid;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:8px}
        .hero-btn-p{background:var(--b1-b);border-color:var(--b1-b);color:var(--b1-t)}
        .hero-btn-s{background:transparent;border-color:currentColor;color:var(--ht);opacity:.5}
        .hero-btn-s:hover{opacity:1}
        .hero-btn:hover{transform:translateY(-3px)}

        .hero-form{background:var(--fb);color:var(--ft);padding:32px}
        .hero-form h2{font-family:'DM Serif Display',serif;font-size:1.3rem;font-weight:400;margin-bottom:4px}
        .hero-form .desc{font-size:.82rem;opacity:.55;margin-bottom:20px}
        .hero-form form{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .hero-form .full{grid-column:1/-1}
        .hero-form label{font-size:.72rem;font-weight:600;display:block;margin-bottom:3px;opacity:.6;text-transform:uppercase;letter-spacing:.04em}
        .hero-form select,.hero-form input{padding:10px 12px;border:2px solid var(--line);font-size:.84rem;width:100%;background:var(--white);color:var(--ink)}
        .hero-form .btn-book{padding:12px 28px;border:none;background:var(--b1-b);color:var(--b1-t);font-weight:600;font-size:.85rem;cursor:pointer;grid-column:1/-1;justify-self:start}
        .hero-form .btn-book:hover{opacity:.85}
        .hero-form small{font-size:.68rem;opacity:.45;display:block;margin-top:2px}
        .hero-form .note{font-size:.72rem;opacity:.45;margin-top:10px;grid-column:1/-1;font-style:italic}

        .sec{padding:100px 24px}
        .sec--alt{background:var(--off)}
        .sec--frota{background:var(--frota-b)}
        .sec--vant{background:var(--vant-b)}
        .sec--etapas{background:var(--etap-b)}
        .sec-inner{width:min(var(--w),100%);margin-inline:auto}
        .sec-tag{font-size:.65rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--a);margin-bottom:12px}
        .sec--frota .sec-tag{color:var(--frota-e)}.sec--vant .sec-tag{color:var(--vant-e)}.sec--etapas .sec-tag{color:var(--etap-e)}
        .sec-h{font-family:'DM Serif Display',serif;font-size:clamp(1.6rem,2.8vw+.6rem,2.8rem);font-weight:400;line-height:1.05;margin-bottom:12px;letter-spacing:-.02em}
        .sec--frota .sec-h{color:var(--frota-t)}.sec--vant .sec-h{color:var(--vant-t)}.sec--etapas .sec-h{color:var(--etap-t)}
        .sec-p{font-size:.9rem;color:var(--ink3);max-width:48ch}
        .sec--frota .sec-p{color:var(--frota-d)}.sec--vant .sec-p{color:var(--vant-d)}.sec--etapas .sec-p{color:var(--etap-d)}

        .frota-scroll{display:flex;gap:24px;overflow-x:auto;padding:8px 0 20px;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch}
        .frota-scroll::-webkit-scrollbar{height:4px}
        .frota-scroll::-webkit-scrollbar-thumb{background:var(--line);border-radius:2px}
        .frota-scroll .f-card{min-width:340px;max-width:380px;scroll-snap-align:start;flex-shrink:0;position:relative}
        .f-card-img{width:100%;height:260px;object-fit:cover}
        .f-card-img-placeholder{height:260px;background:var(--off);display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--ink3);opacity:.15}
        .f-card-body{padding:16px 0 0}
        .f-card-num{font-size:.65rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--a);margin-bottom:4px}
        .f-card-title{font-family:'DM Serif Display',serif;font-size:1.2rem;font-weight:400;color:var(--frota-ct);margin-bottom:4px;letter-spacing:-.01em}
        .f-card-desc{font-size:.84rem;line-height:1.6;color:var(--frota-cd)}

        .vant-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:2px;background:var(--line)}
        .v-cell{padding:36px 24px;background:var(--vant-b)}
        .v-num{font-family:'DM Serif Display',serif;font-size:2.4rem;color:var(--a);line-height:1;margin-bottom:8px}
        .v-txt{font-size:.84rem;color:var(--vant-i);line-height:1.5;display:block}

        .etapas-row{display:grid;grid-template-columns:repeat(3,1fr);gap:32px;position:relative;padding-top:20px}
        .etapas-row::before{content:'';position:absolute;top:40px;left:12%;right:12%;height:1px;background:var(--line)}
        .e-card{text-align:center}
        .e-dot{width:40px;height:40px;border-radius:50%;background:var(--etap-pb);border:2px solid var(--a);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:.85rem;font-weight:700;color:var(--a);position:relative;z-index:1}
        .e-title{font-family:'DM Serif Display',serif;font-size:1.1rem;font-weight:400;color:var(--etap-pt);margin-bottom:6px;letter-spacing:-.01em}
        .e-desc{font-size:.84rem;line-height:1.6;color:var(--etap-pd);max-width:28ch;margin-inline:auto}

        .contato{padding:100px 24px;background:var(--cont-b)}
        .contato-inner{width:min(var(--w),100%);margin-inline:auto;display:grid;grid-template-columns:1fr 1fr;gap:56px;align-items:center}
        .contato-tag{font-size:.65rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--cont-e);margin-bottom:12px}
        .contato-h{font-family:'DM Serif Display',serif;font-size:clamp(1.6rem,2.4vw+.6rem,2.6rem);font-weight:400;color:var(--cont-t);line-height:1.05;margin-bottom:20px;letter-spacing:-.02em}
        .contato-info{display:flex;flex-direction:column;gap:2px}
        .contato-info span{font-size:.9rem;color:var(--cont-i)}
        .contato-btn{display:inline-flex;align-items:center;gap:8px;margin-top:24px;padding:14px 36px;font-weight:600;font-size:.85rem;border:2px solid;background:var(--b1-b);border-color:var(--b1-b);color:var(--b1-t);cursor:pointer;transition:all .2s}
        .contato-btn:hover{transform:translateY(-3px)}
        .contato-img{overflow:hidden}
        .contato-img img{width:100%;height:360px;object-fit:cover}
        .contato-img-placeholder{height:360px;background:var(--off);display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--ink3);opacity:.12}

        .footer{background:var(--foot-b);color:var(--foot-t);padding:32px 24px;border-top:1px solid var(--line)}
        .footer-inner{width:min(var(--w),100%);margin-inline:auto;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;font-size:.82rem}
        .footer-copy{opacity:.35}
        .footer-links{display:flex;gap:20px}
        .footer-links a{opacity:.3;transition:opacity .2s}
        .footer-links a:hover{opacity:.6}

        @media(max-width:768px){
            .hero-inner{grid-template-columns:1fr;gap:32px}
            .hero{min-height:auto;padding:100px 20px 48px}
            .mast{padding:6px 12px 6px 16px;top:12px;width:calc(100% - 24px)}
            .mast-nav{display:none}
            .hero-form form{grid-template-columns:1fr}
            .vant-grid{grid-template-columns:1fr 1fr}
            .etapas-row{grid-template-columns:1fr;gap:24px}
            .etapas-row::before{display:none}
            .contato-inner{grid-template-columns:1fr;gap:32px}
            .frota-scroll .f-card{min-width:280px;max-width:320px}
            .sec{padding:64px 20px}
        }
    </style>
</head>
<body>

    <header class="mast">
        <a href="#" class="mast-brand">
            @if(!empty($empresa->logo_url))
                <img src="{{ $empresa->logo_url }}" alt="{{ $empresa->nome }}">
            @else
                {{ $empresa->nome }}
            @endif
        </a>
        <nav class="mast-nav">
            <a href="#vantagens">{{ $menuItemVantagens }}</a>
            <a href="#frota">{{ $menuItemFrota }}</a>
            <a href="#etapas">{{ $menuItemEtapas }}</a>
            <a href="#contato">{{ $menuItemContato }}</a>
        </nav>
        <a href="{{ $botaoUrl }}" class="mast-cta">{{ $botaoTexto }}</a>
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
        <div class="sec-inner">
            <div style="margin-bottom:48px">
                @if(!empty($vantagensLabelSuperior))<div class="sec-tag">{{ $vantagensLabelSuperior }}</div>@endif
                <h2 class="sec-h">{{ $vantagensTitulo }}</h2>
                @if(!empty($vantagensDescricao))<p class="sec-p">{{ $vantagensDescricao }}</p>@endif
            </div>
            <div class="vant-grid">
                @php $vn = 1; @endphp
                @foreach($beneficios as $beneficio)
                    @if(!empty($beneficio))
                        <div class="v-cell">
                            <div class="v-num">{{ str_pad($vn++,2,'0',STR_PAD_LEFT) }}</div>
                            <span class="v-txt">{{ $beneficio }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <section id="frota" class="sec sec--frota">
        <div class="sec-inner">
            <div style="margin-bottom:40px">
                @if(!empty($frotaLabelSuperior))<div class="sec-tag">{{ $frotaLabelSuperior }}</div>@endif
                <h2 class="sec-h">{{ $frotaTitulo }}</h2>
                @if(!empty($frotaDescricao))<p class="sec-p">{{ $frotaDescricao }}</p>@endif
            </div>
            <div class="frota-scroll">
                @for($fi = 1; $fi <= 10; $fi++)
                    @php $fCard = $frotaDestaques[$fi - 1] ?? null; $fv = $fCard && !empty($fCard['titulo']); @endphp
                    <div class="f-card" data-frota="{{ $fi }}" style="{{ $fv ? '' : 'display:none' }}">
                        @if($fCard && !empty($fCard['imagem']))
                            <img src="{{ $fCard['imagem'] }}" alt="{{ $fCard['titulo'] }}" class="f-card-img" data-frota-img="{{ $fi }}">
                        @else
                            <div class="f-card-img-placeholder">∎</div>
                        @endif
                        <div class="f-card-body">
                            <div class="f-card-num">{{ str_pad($fi,2,'0',STR_PAD_LEFT) }}</div>
                            <h3 class="f-card-title" data-frota-titulo="{{ $fi }}">{{ $fCard['titulo'] ?? '' }}</h3>
                            <p class="f-card-desc" data-frota-desc="{{ $fi }}">{{ $fCard['descricao'] ?? '' }}</p>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    <section id="como-funciona" class="sec sec--etapas">
        <div class="sec-inner">
            <div style="margin-bottom:48px;text-align:center">
                @if(!empty($etapasLabelSuperior))<div class="sec-tag">{{ $etapasLabelSuperior }}</div>@endif
                <h2 class="sec-h">{{ $etapasTitulo }}</h2>
            </div>
            <div class="etapas-row">
                @foreach($etapasReserva as $idx => $etapa)
                    @if(!empty($etapa['titulo']))
                    <div class="e-card">
                        <div class="e-dot">{{ $idx + 1 }}</div>
                        <h4 class="e-title">{{ $etapa['titulo'] }}</h4>
                        <p class="e-desc">{{ $etapa['descricao'] ?? '' }}</p>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <section id="contato" class="contato">
        <div class="contato-inner">
            <div>
                @if(!empty($contatoLabelSuperior))<div class="contato-tag">{{ $contatoLabelSuperior }}</div>@endif
                <div class="contato-h">{{ $contatoTitulo }}</div>
                <div class="contato-info">
                    @if(!empty($contatoTelefone))<span>{{ $contatoTelefone }}</span>@endif
                    @if(!empty($contatoEmail))<span>{{ $contatoEmail }}</span>@endif
                </div>
                <a href="{{ $botaoUrl }}" class="contato-btn">{{ $botaoTexto }}</a>
            </div>
            <div class="contato-img">
                @if(!empty($imagemContato))
                    <img src="{{ $imagemContato }}" alt="Contato {{ $empresa->nome }}">
                @else
                    <div class="contato-img-placeholder">∎</div>
                @endif
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-copy">{{ $footerTexto }}</div>
            <div class="footer-links">
                <a href="#vantagens">{{ $menuItemVantagens }}</a>
                <a href="#frota">{{ $menuItemFrota }}</a>
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
                    var c=document.querySelector('.f-card[data-frota="'+fi+'"]');if(!c)continue;
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
