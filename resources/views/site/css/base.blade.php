/* ===== Tokens (cada modelo sobrescreve) ===== */
:root{
    --f-titulo:system-ui,sans-serif;--f-texto:system-ui,sans-serif;
    --peso-titulo:700;--tracking-titulo:-.015em;
    --fundo:#ffffff;--superficie:#f6f6f3;--tinta:#16181d;--texto:#3b4049;--suave:#5f6571;--linha:#e4e4de;
    --raio:4px;--raio-img:4px;--largura:1200px;--sec:clamp(4rem,8vw,6.5rem);
    --btn-raio:4px;--btn-peso:600;
}

/* ===== Reset ===== */
*,*::before,*::after{box-sizing:border-box}
*{margin:0;padding:0}
html{-webkit-text-size-adjust:100%;text-size-adjust:100%}
body{font-family:var(--f-texto);font-size:1.0625rem;line-height:1.65;color:var(--texto);background:var(--fundo);-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility;overflow-x:hidden}
img{display:block;max-width:100%;height:auto}
a{color:inherit;text-decoration-thickness:1px;text-underline-offset:.18em}
ul,ol{list-style:none}
h1,h2,h3,h4{font-family:var(--f-titulo);font-weight:var(--peso-titulo);letter-spacing:var(--tracking-titulo);color:var(--tinta);line-height:1.15;text-wrap:balance}
p{text-wrap:pretty}
address{font-style:normal}
.sr{position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.wrap{width:100%;max-width:var(--largura);margin-inline:auto;padding-inline:clamp(1.25rem,4vw,2.5rem)}
.sec{padding-block:var(--sec)}
body.menu-aberto{overflow:hidden}

/* ===== Acessibilidade ===== */
.pular{position:absolute;left:1rem;top:-4rem;z-index:1000;background:var(--tinta);color:#fff;padding:.75rem 1.1rem;border-radius:var(--raio);font-weight:600;text-decoration:none}
.pular:focus{top:1rem}
:focus-visible{outline:3px solid var(--c2-texto);outline-offset:3px;border-radius:2px}
@media (prefers-reduced-motion:no-preference){
    html{scroll-behavior:smooth}
    .btn,.menu a,.seg,.uni,.gal-item img,.com,.ct-item a{transition:background-color .2s ease,color .2s ease,border-color .2s ease,transform .25s ease,box-shadow .25s ease,opacity .2s ease}
}
[id]{scroll-margin-top:96px}

/* ===== Botoes ===== */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:.55rem;min-height:48px;padding:.8rem 1.5rem;border-radius:var(--btn-raio);font-family:var(--f-texto);font-weight:var(--btn-peso);font-size:.95rem;line-height:1.2;text-decoration:none;border:1.5px solid transparent;cursor:pointer;white-space:nowrap}
.btn-sm{min-height:40px;padding:.55rem 1.05rem;font-size:.87rem}
.btn-pri{background:var(--c1);color:var(--c1-sobre);border-color:var(--c1)}
.btn-pri:hover{background:var(--c1-escura);border-color:var(--c1-escura)}
.btn-acento{background:var(--c2);color:var(--c2-sobre);border-color:var(--c2)}
.btn-acento:hover{filter:brightness(.93)}
.btn-contorno{background:transparent;color:var(--c1-texto);border-color:currentColor}
.btn-contorno:hover{background:var(--c1-suave)}
.btn-claro{background:#fff;color:var(--c1-texto);border-color:#fff}
.btn-claro:hover{background:transparent;color:#fff}
.btn-contorno-claro{background:transparent;color:#fff;border-color:rgba(255,255,255,.7)}
.btn-contorno-claro:hover{background:#fff;color:var(--c1-texto)}

/* ===== Cabecalho de secao ===== */
.s-head{margin-bottom:clamp(2rem,4vw,3rem);max-width:720px}
.s-kicker{font-size:.78rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--c2-texto);margin-bottom:.75rem}
.s-titulo{font-size:clamp(1.85rem,3.6vw,2.75rem)}
.s-intro{margin-top:.9rem;color:var(--suave);font-size:1.05rem}

/* ===== Topo + menu (estrutura; cada modelo estiliza) ===== */
.topo{position:relative;z-index:60;background:var(--fundo)}
.topo-in{display:flex;align-items:center;justify-content:space-between;gap:1.5rem;min-height:80px}
.marca{display:flex;align-items:center;gap:.8rem;text-decoration:none;min-width:0}
.marca img{height:48px;width:auto;max-width:150px;object-fit:contain}
.marca-nome{font-family:var(--f-titulo);font-weight:700;font-size:1.15rem;color:var(--tinta);line-height:1.15}
.marca-nome small{display:block;font-family:var(--f-texto);font-size:.74rem;font-weight:500;color:var(--suave);letter-spacing:.02em;margin-top:.15rem}
.menu{display:flex;align-items:center;gap:2rem}
.menu-itens{display:flex;align-items:center;gap:.25rem}
.menu-itens a{display:inline-flex;align-items:center;min-height:44px;padding:.4rem .8rem;font-size:.94rem;font-weight:500;color:var(--tinta);text-decoration:none;border-radius:var(--raio)}
.menu-itens a:hover{color:var(--c1-texto)}
.menu-acoes{display:flex;align-items:center;gap:1rem}
.menu-portal{display:inline-flex;align-items:center;gap:.45rem;min-height:44px;font-size:.9rem;font-weight:600;color:var(--tinta);text-decoration:none}
.menu-portal:hover{color:var(--c1-texto)}
.menu-botao{display:none;align-items:center;justify-content:center;width:48px;height:48px;border:1px solid var(--linha);background:var(--fundo);border-radius:var(--raio);cursor:pointer;color:var(--tinta)}
.menu-botao-icone,.menu-botao-icone::before,.menu-botao-icone::after{display:block;width:20px;height:2px;background:currentColor;border-radius:2px;position:relative}
.menu-botao-icone::before,.menu-botao-icone::after{content:"";position:absolute;left:0}
.menu-botao-icone::before{top:-6px}.menu-botao-icone::after{top:6px}
.menu-botao[aria-expanded="true"] .menu-botao-icone{background:transparent}
.menu-botao[aria-expanded="true"] .menu-botao-icone::before{top:0;transform:rotate(45deg)}
.menu-botao[aria-expanded="true"] .menu-botao-icone::after{top:0;transform:rotate(-45deg)}
@media (max-width:1023px){
    .menu-botao{display:inline-flex}
    .menu{display:none;position:fixed;inset:80px 0 0 0;z-index:59;background:var(--fundo);flex-direction:column;align-items:stretch;gap:1.5rem;padding:1.5rem clamp(1.25rem,4vw,2.5rem) 2rem;overflow-y:auto;border-top:1px solid var(--linha)}
    .menu.aberto{display:flex}
    .menu-itens{flex-direction:column;align-items:stretch;gap:0}
    .menu-itens a{width:100%;min-height:56px;font-size:1.15rem;border-bottom:1px solid var(--linha);border-radius:0;padding-inline:0}
    .menu-acoes{flex-direction:column;align-items:stretch}
    .menu-acoes .btn{width:100%}
}

/* ===== Faixa de aviso ===== */
.s-aviso{background:var(--c2);color:var(--c2-sobre)}
.s-aviso--encerrada{background:var(--fundo);color:var(--tinta);border-block:1px solid var(--linha)}
.s-aviso--encerrada .s-aviso-link{color:var(--c1-texto)}
.s-aviso-in{display:flex;align-items:center;justify-content:center;gap:.4rem 1.25rem;flex-wrap:wrap;min-height:48px;padding-block:.6rem;text-align:center;font-size:.93rem;font-weight:600}
.s-aviso-link{display:inline-flex;align-items:center;gap:.4rem;min-height:36px;color:inherit;text-decoration:underline;font-weight:700}
@media (max-width:599px){
    .s-aviso-in{flex-direction:column;gap:0;font-size:.86rem;padding-block:.55rem}
    .s-aviso-link{min-height:32px}
    .h-acoes .btn{width:100%}
}

/* ===== Segmentos ===== */
.seg-lista{display:grid;gap:1rem}
.seg{position:relative;display:flex;flex-direction:column;gap:.5rem}
.seg-num{font-family:var(--f-titulo);font-size:.85rem;font-weight:700;color:var(--c2-texto);letter-spacing:.04em}
.seg-rotulo{font-size:.74rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--suave)}
.seg-nome{font-size:1.3rem}
.seg-desc{color:var(--suave);font-size:.97rem}
.seg-link{display:inline-flex;align-items:center;gap:.45rem;min-height:44px;margin-top:auto;font-weight:600;font-size:.92rem;color:var(--c1-texto);text-decoration:none}
.seg-link:hover{text-decoration:underline}
.s-segmentos--cards .seg-lista{grid-template-columns:repeat(auto-fit,minmax(240px,1fr))}
.s-segmentos--cards .seg{background:var(--fundo);border:1px solid var(--linha);border-radius:var(--raio);padding:1.75rem 1.6rem 1.25rem}
.s-segmentos--lista .seg-lista{gap:0;border-top:1px solid var(--linha)}
.s-segmentos--lista .seg{display:grid;grid-template-columns:64px 1fr auto;align-items:center;gap:1.5rem;padding-block:1.6rem;border-bottom:1px solid var(--linha)}
.s-segmentos--lista .seg-link{margin-top:0}
.s-segmentos--blocos .seg-lista{grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:0}
.s-segmentos--blocos .seg{min-height:300px;padding:2rem 1.75rem;background:var(--c1);color:var(--c1-sobre)}
.s-segmentos--blocos .seg:nth-child(even){background:var(--c1-escura);color:#fff}
.s-segmentos--blocos .seg *{color:inherit}
.s-segmentos--blocos .seg-num{font-size:3rem;line-height:1;opacity:.55}
.s-segmentos--blocos .seg-nome{font-size:1.6rem;margin-top:auto}
.s-segmentos--blocos .seg-desc,.s-segmentos--blocos .seg-rotulo{opacity:.85}
.s-segmentos--blocos .seg-link{margin-top:.75rem;text-decoration:underline}
.s-segmentos--editorial .seg-lista{gap:0}
.s-segmentos--editorial .seg{display:grid;grid-template-columns:minmax(120px,1fr) 2.4fr auto;gap:2rem;align-items:baseline;padding-block:2.25rem;border-top:1px solid var(--linha)}
.s-segmentos--editorial .seg-nome{font-size:clamp(1.6rem,3vw,2.3rem)}
.s-segmentos--editorial .seg-link{margin-top:0}
@media (max-width:719px){
    .s-segmentos--lista .seg,.s-segmentos--editorial .seg{grid-template-columns:1fr;gap:.4rem}
    .s-segmentos--blocos .seg{min-height:220px}
}

/* ===== Unidades ===== */
.uni-lista{display:grid;gap:1.25rem;grid-template-columns:repeat(auto-fit,minmax(280px,1fr))}
.uni{display:flex;flex-direction:column;gap:1rem;background:var(--fundo);border:1px solid var(--linha);border-radius:var(--raio);padding:1.6rem}
.uni-cidade{font-size:.76rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--c2-texto);margin-bottom:.35rem}
.uni-nome{font-size:1.3rem}
.uni-dados{display:grid;gap:.55rem;font-size:.94rem;color:var(--suave)}
.uni-dados div{display:grid;grid-template-columns:20px 1fr;gap:.6rem}
.uni-dados dt{color:var(--c1-texto);padding-top:.1rem}
.uni-dados a{text-decoration:none}.uni-dados a:hover{text-decoration:underline;color:var(--c1-texto)}
.uni-acoes{display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;margin-top:auto;padding-top:.5rem}
.uni-mapa{display:inline-flex;align-items:center;min-height:40px;font-size:.9rem;font-weight:600;color:var(--c1-texto)}
.s-unidades--lista .uni-lista{grid-template-columns:1fr;gap:0;border-top:1px solid var(--linha)}
.s-unidades--lista .uni{display:grid;grid-template-columns:1.2fr 1.6fr auto;align-items:center;gap:2rem;border:0;border-bottom:1px solid var(--linha);border-radius:0;padding:1.75rem 0;background:transparent}
.s-unidades--lista .uni-acoes{margin-top:0;padding-top:0}
@media (max-width:859px){.s-unidades--lista .uni{grid-template-columns:1fr;gap:.9rem}}

/* ===== Galeria ===== */
.gal-lista{display:grid;gap:.75rem;grid-template-columns:repeat(4,1fr)}
.gal-item a{display:block;height:100%;overflow:hidden;border-radius:var(--raio-img);background:var(--superficie)}
.gal-item img{width:100%;height:100%;aspect-ratio:4/3;object-fit:cover}
.gal-item a:hover img{transform:scale(1.03)}
.s-galeria--mosaico .gal-lista{grid-template-columns:repeat(4,1fr);grid-auto-rows:clamp(140px,16vw,220px);grid-auto-flow:dense}
.s-galeria--mosaico .gal-item img{aspect-ratio:auto}
.s-galeria--mosaico .gal-item:first-child{grid-column:span 2;grid-row:span 2}
.s-galeria--mosaico .gal-item:nth-child(6n+4){grid-column:span 2}
.s-galeria--faixa .gal-lista{display:flex;overflow-x:auto;scroll-snap-type:x mandatory;gap:1rem;padding-bottom:.75rem;scrollbar-width:thin}
.s-galeria--faixa .gal-item{flex:0 0 min(78vw,380px);scroll-snap-align:start}
.s-galeria--faixa .gal-item img{aspect-ratio:4/5}

/* Lightbox da galeria: miniatura com crop uniforme na grade,
   clique abre a foto inteira (padrao de sites de escola). */
.lightbox{position:fixed;inset:0;z-index:120;display:none;align-items:center;justify-content:center;background:rgba(12,12,16,.92);padding:1rem}
.lightbox.aberto{display:flex}
.lightbox img{max-width:min(92vw,1200px);max-height:84vh;width:auto;height:auto;object-fit:contain;border-radius:8px;box-shadow:0 24px 60px rgba(0,0,0,.5)}
.lightbox button{position:absolute;display:flex;align-items:center;justify-content:center;width:46px;height:46px;border:0;border-radius:50%;background:rgba(255,255,255,.14);color:#fff;font-size:1.15rem;cursor:pointer;transition:background .2s ease}
.lightbox button:hover{background:rgba(255,255,255,.3)}
.lightbox button:focus-visible{outline:3px solid #fff;outline-offset:2px}
.lb-fechar{top:1rem;right:1rem}
.lb-ant{left:1rem;top:50%;transform:translateY(-50%)}
.lb-prox{right:1rem;top:50%;transform:translateY(-50%)}
.lb-contador{position:absolute;bottom:1rem;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.75);font-size:.85rem;letter-spacing:.04em}
@media (max-width:640px){.lb-ant{left:.4rem}.lb-prox{right:.4rem}.lightbox button{width:40px;height:40px}}
@media (max-width:859px){
    .gal-lista,.s-galeria--mosaico .gal-lista{grid-template-columns:repeat(2,1fr)}
    .s-galeria--mosaico .gal-item:nth-child(6n+4){grid-column:span 1}
}

/* ===== Agenda + comunicados ===== */
.ac-grade{display:grid;gap:clamp(2rem,4vw,3.5rem)}
.s-acontece--ambos.s-acontece--colunas .ac-grade{grid-template-columns:1fr 1.15fr}
.ac-subtitulo{font-size:1.1rem;margin-bottom:1.1rem;padding-bottom:.75rem;border-bottom:2px solid var(--tinta)}
.ag-lista{display:grid}
.ag{display:grid;grid-template-columns:64px 1fr;gap:1.1rem;align-items:start;padding-block:1.1rem;border-bottom:1px solid var(--linha)}
.ag-data{display:flex;flex-direction:column;align-items:center;justify-content:center;width:64px;height:68px;border:1px solid var(--linha);border-radius:var(--raio);background:var(--fundo)}
.ag-dia{font-family:var(--f-titulo);font-size:1.55rem;font-weight:700;line-height:1;color:var(--c1-texto)}
.ag-mes{font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--suave);margin-top:.2rem}
.ag-tipo{font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--c2-texto)}
.ag-titulo{font-size:1.08rem;margin-top:.15rem}
.ag-det{display:flex;flex-wrap:wrap;gap:.2rem .9rem;font-size:.88rem;color:var(--suave);margin-top:.3rem}
.ag-det span+span::before{content:"·";margin-right:.9rem;color:var(--linha)}
.com-lista{display:grid}
.com{padding-block:1.2rem;border-bottom:1px solid var(--linha)}
.com-meta{display:flex;align-items:center;gap:.75rem;font-size:.84rem;color:var(--suave)}
.com-urgente{font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9f1239;background:#ffe4e6;padding:.15rem .5rem;border-radius:3px}
.com-titulo{font-size:1.12rem;margin-top:.35rem}
.com-resumo{font-size:.95rem;color:var(--suave);margin-top:.35rem}
.s-acontece--cards .com-lista{grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.25rem}
.s-acontece--cards .com{border:1px solid var(--linha);border-radius:var(--raio);padding:1.5rem;background:var(--fundo)}
.s-acontece--cards .ag-lista{grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.25rem}
.s-acontece--cards .ag{border:1px solid var(--linha);border-radius:var(--raio);padding:1.25rem;background:var(--fundo)}
@media (max-width:859px){.s-acontece--ambos.s-acontece--colunas .ac-grade{grid-template-columns:1fr}}

/* ===== Depoimentos ===== */
.dep-lista{display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(280px,1fr))}
.dep figure{height:100%;display:flex;flex-direction:column;gap:1.25rem}
.dep blockquote p{font-size:1.06rem;line-height:1.7;color:var(--tinta)}
.dep blockquote p::before{content:"\201C"}.dep blockquote p::after{content:"\201D"}
.dep figcaption{margin-top:auto;display:flex;flex-direction:column}
.dep-nome{font-weight:700;color:var(--tinta);font-size:.95rem}
.dep-papel{font-size:.86rem;color:var(--suave)}
.s-depoimentos--destaque .dep-lista{grid-template-columns:1fr}
.s-depoimentos--destaque .dep:first-child blockquote p{font-family:var(--f-titulo);font-size:clamp(1.35rem,2.6vw,2rem);line-height:1.4;font-weight:500}

/* ===== Contato ===== */
.ct-grade{display:grid;gap:clamp(2rem,5vw,4rem);align-items:start}
.s-contato--painel .ct-grade{grid-template-columns:1.25fr 1fr}
.ct-lista{display:grid;gap:1.4rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}
.ct-item dt{display:flex;align-items:center;gap:.5rem;font-size:.76rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--suave);margin-bottom:.35rem}
.ct-item dt i{color:var(--c1-texto);font-size:.9rem}
.ct-item dd{color:var(--tinta);font-size:1rem;overflow-wrap:anywhere}
.ct-item a{color:var(--c1-texto);text-decoration:none;font-weight:600}
.ct-item a:hover{text-decoration:underline}
.ct-painel{background:var(--c1);color:var(--c1-sobre);border-radius:var(--raio);padding:clamp(1.75rem,4vw,2.75rem)}
.ct-painel h3{color:inherit;font-size:1.55rem}
.ct-painel p{margin:.75rem 0 1.5rem;opacity:.9}
.ct-painel .btn-claro{color:var(--c1-texto)}
.ct-painel .btn-claro:hover{color:var(--c1-sobre);border-color:currentColor}
.s-contato--cards .ct-item{border:1px solid var(--linha);border-radius:var(--raio);padding:1.25rem 1.4rem;background:var(--fundo)}
@media (max-width:859px){.s-contato--painel .ct-grade{grid-template-columns:1fr}}

/* ===== Convite final ===== */
.s-convite{background:var(--c1-escura);color:#fff;padding-block:clamp(3.5rem,7vw,5.5rem);position:relative;isolation:isolate}
.s-convite--foto{background:linear-gradient(90deg,color-mix(in srgb,var(--c1-escura) 92%,transparent),color-mix(in srgb,var(--c1-escura) 70%,transparent)),var(--convite-img) center/cover no-repeat}
.s-convite-in{display:flex;align-items:center;justify-content:space-between;gap:2rem;flex-wrap:wrap}
.s-convite h2{color:#fff;font-size:clamp(1.75rem,3.4vw,2.6rem);max-width:640px}
.s-convite p{margin-top:.75rem;opacity:.88;max-width:560px}
.s-convite-acoes{display:flex;gap:.9rem;flex-wrap:wrap}

/* ===== Matricula (varias unidades) ===== */
.mat-lista{display:grid;gap:.75rem;grid-template-columns:repeat(auto-fit,minmax(260px,1fr))}
.mat-item{display:flex;align-items:center;justify-content:space-between;gap:1rem;min-height:64px;padding:1rem 1.25rem;border:1px solid var(--linha);border-radius:var(--raio);font-weight:600;color:var(--tinta);text-decoration:none;background:var(--fundo)}
.mat-item:hover{border-color:var(--c1);color:var(--c1-texto)}

/* ===== Instagram ===== */
.s-instagram{border-block:1px solid var(--linha);background:var(--fundo)}
.s-instagram-in{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;min-height:88px;padding-block:1.25rem}
.s-instagram p{font-weight:600;color:var(--tinta);display:flex;align-items:center;gap:.6rem}
.s-instagram p i{font-size:1.3rem;color:var(--c2-texto)}
.s-instagram-link{display:inline-flex;align-items:center;min-height:44px;font-weight:700;color:var(--c1-texto)}

/* ===== Rodape ===== */
.s-rodape{padding-top:clamp(3rem,6vw,4.5rem);font-size:.94rem}
.s-rodape--escuro{background:var(--c1-escura);color:rgba(255,255,255,.78)}
.s-rodape--claro{background:var(--superficie);color:var(--suave);border-top:1px solid var(--linha)}
.rod-grade{display:grid;grid-template-columns:1.5fr repeat(3,1fr);gap:2.5rem;padding-bottom:3rem}
.rod-nome{display:flex;align-items:center;gap:.8rem;font-family:var(--f-titulo);font-weight:700;font-size:1.2rem}
.rod-nome img{width:44px;height:44px;object-fit:contain;background:#fff;border-radius:var(--raio);padding:4px}
.s-rodape--escuro .rod-nome{color:#fff}
.s-rodape--claro .rod-nome{color:var(--tinta)}
.rod-tagline{margin-top:.9rem;max-width:320px}
.rod-titulo{font-family:var(--f-texto);font-size:.76rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;margin-bottom:1rem}
.s-rodape--escuro .rod-titulo{color:#fff}
.s-rodape--claro .rod-titulo{color:var(--tinta)}
.rod-col ul{display:grid;gap:.2rem}
.rod-col a{display:inline-flex;align-items:center;min-height:36px;text-decoration:none}
.rod-col a:hover{text-decoration:underline}
.s-rodape--escuro .rod-col a:hover{color:#fff}
.rod-col address p{margin-bottom:.35rem}
.rod-redes{display:flex;gap:.5rem;margin-top:.75rem}
.rod-redes a{width:44px;height:44px;justify-content:center;border:1px solid currentColor;border-radius:50%;font-size:1.05rem;opacity:.85}
.rod-legal{display:flex;justify-content:space-between;gap:.5rem 2rem;flex-wrap:wrap;padding-block:1.4rem;font-size:.84rem;border-top:1px solid rgba(127,127,127,.25)}
.s-rodape--escuro .rod-legal strong{color:#fff}
@media (max-width:959px){.rod-grade{grid-template-columns:1fr 1fr}.rod-marca{grid-column:1/-1}}
@media (max-width:559px){.rod-grade{grid-template-columns:1fr}}
