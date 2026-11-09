@extends('site.layout')

{{-- Minimal: contemporaneo e limpo. Tipografia grande, muito espaco, filetes, secoes numeradas,
     tudo alinhado a esquerda e cor da marca apenas em detalhes. --}}
@section('modelo', 'minimal')
@section('fontes', 'inter:400,500,600,700')
@section('preload', $empresa->site_banner_url ?? '')

@section('css')
    :root{--f-titulo:'Inter',system-ui,sans-serif;--f-texto:'Inter',system-ui,sans-serif;--peso-titulo:600;--tracking-titulo:-.035em;
          --fundo:#ffffff;--superficie:#fafafa;--tinta:#0a0a0a;--texto:#404040;--suave:#737373;--linha:#e8e8e8;
          --raio:0;--raio-img:0;--btn-raio:0;--btn-peso:500;--largura:1240px;--sec:clamp(4.5rem,10vw,8rem)}
    body{font-size:1rem}
    .tpl-minimal main{counter-reset:secao}
    .tpl-minimal .sec{border-top:1px solid var(--linha);counter-increment:secao}
    .tpl-minimal .s-head{display:grid;grid-template-columns:minmax(160px,1fr) 3fr;gap:1rem 2rem;max-width:none;align-items:baseline}
    .tpl-minimal .s-kicker{grid-row:1/3;font-size:.8rem;font-weight:500;letter-spacing:.02em;text-transform:none;color:var(--suave);margin:0}
    .tpl-minimal .s-kicker::before{content:counter(secao,decimal-leading-zero) " — ";color:var(--tinta)}
    .tpl-minimal .s-titulo{font-size:clamp(1.9rem,4vw,3.1rem);font-weight:500}
    .tpl-minimal .s-intro{grid-column:2;margin:0}
    .tpl-minimal .s-head~*{margin-left:calc(25% + .5rem)}
    .tpl-minimal .btn-pri{background:var(--tinta);border-color:var(--tinta);color:#fff}
    .tpl-minimal .btn-pri:hover{background:var(--c1);border-color:var(--c1);color:var(--c1-sobre)}
    .tpl-minimal .btn-contorno{color:var(--tinta)}
    .tpl-minimal .btn-contorno:hover{background:var(--superficie)}

    .tpl-minimal .topo{border-bottom:1px solid var(--linha)}
    .tpl-minimal .topo-in{min-height:72px}
    .tpl-minimal .marca img{height:36px}
    .tpl-minimal .marca-nome{font-size:1rem;font-weight:600;letter-spacing:-.01em}
    .tpl-minimal .menu-itens a{font-size:.9rem;color:var(--suave)}
    .tpl-minimal .menu-itens a:hover{color:var(--tinta)}
    .tpl-minimal .menu-acoes .btn{min-height:42px;padding:.55rem 1.1rem;font-size:.88rem}
    .tpl-minimal .menu-portal{font-weight:500;font-size:.88rem}
    @media (max-width:1023px){.tpl-minimal .menu{inset:72px 0 0 0}}

    .h-min{padding-block:clamp(4rem,10vw,8.5rem) clamp(3.5rem,8vw,6.5rem)}
    .h-min-grade{display:grid;grid-template-columns:1.35fr 1fr;gap:clamp(2.5rem,6vw,6rem);align-items:end}
    .h-min-grade.sem-foto{grid-template-columns:1fr}
    .h-min .h-kicker{font-size:.85rem;color:var(--suave);display:flex;gap:.75rem;align-items:center}
    .h-min .h-kicker::before{content:"";width:28px;height:1px;background:var(--tinta)}
    .h-min h1{font-size:clamp(2.6rem,6.6vw,5.4rem);font-weight:500;letter-spacing:-.045em;line-height:1.02;margin-top:1.5rem;max-width:16ch}
    .h-min .h-lead{margin-top:1.75rem;font-size:1.12rem;color:var(--suave);max-width:520px}
    .h-min .h-acoes{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:2.5rem}
    .h-min .h-foto img{width:100%;aspect-ratio:4/5;object-fit:cover}

    .sobre-min{display:grid;grid-template-columns:1fr 1fr;gap:clamp(2rem,5vw,4rem)}
    .sobre-min p{font-size:1.12rem;color:var(--texto)}
    .sobre-min p:first-child{font-size:clamp(1.3rem,2.2vw,1.65rem);line-height:1.45;color:var(--tinta);letter-spacing:-.015em}
    .sobre-min p+p{margin-top:1rem}
    .sobre-min img{width:100%;aspect-ratio:3/2;object-fit:cover}
    .tpl-minimal .s-segmentos--lista .seg{grid-template-columns:48px 1fr auto}
    .tpl-minimal .s-segmentos--lista .seg-nome{font-size:1.5rem;font-weight:500}
    .tpl-minimal .seg-num{font-family:var(--f-texto);font-weight:500;color:var(--suave)}
    .tpl-minimal .seg-link,.tpl-minimal .uni-mapa,.tpl-minimal .ct-item a{color:var(--tinta);text-decoration:underline}
    .tpl-minimal .uni-cidade,.tpl-minimal .ag-tipo{color:var(--suave);letter-spacing:.04em;font-weight:500}
    .tpl-minimal .uni-nome,.tpl-minimal .ag-titulo,.tpl-minimal .com-titulo{font-weight:500}
    .tpl-minimal .ag-data{border:0;align-items:flex-start;height:auto;width:auto}
    .tpl-minimal .ag-dia{color:var(--tinta);font-weight:500}
    .tpl-minimal .ac-subtitulo{font-weight:500;border-bottom-width:1px}
    .tpl-minimal .gal-lista{gap:1.5rem;grid-template-columns:repeat(3,1fr)}
    .tpl-minimal .gal-item img{aspect-ratio:1}
    .tpl-minimal .dep blockquote p{font-size:1.2rem;line-height:1.55;letter-spacing:-.01em}
    .tpl-minimal .dep blockquote p::before,.tpl-minimal .dep blockquote p::after{content:none}
    .tpl-minimal .dep{border-left:1px solid var(--tinta);padding-left:1.5rem}
    .tpl-minimal .ct-lista{grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:2rem}
    .tpl-minimal .ct-item dt i{display:none}
    .tpl-minimal .ct-item dt{letter-spacing:.02em;text-transform:none;font-weight:500;font-size:.85rem}
    .tpl-minimal .ct-item dd{font-size:1.1rem}
    .cta-min{padding-block:clamp(4rem,9vw,7rem);border-top:1px solid var(--linha)}
    .cta-min h2{font-size:clamp(2rem,5vw,3.6rem);font-weight:500;letter-spacing:-.04em;max-width:18ch}
    .cta-min .h-acoes{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:2rem}
    @media (max-width:859px){
        .h-min-grade,.sobre-min{grid-template-columns:1fr}
        .h-min .h-foto img{aspect-ratio:4/3}
        .tpl-minimal .s-head{grid-template-columns:1fr}
        .tpl-minimal .s-kicker{grid-row:auto}
        .tpl-minimal .s-intro{grid-column:1}
        .tpl-minimal .s-head~*{margin-left:0}
        .tpl-minimal .gal-lista{grid-template-columns:repeat(2,1fr);gap:.75rem}
        .tpl-minimal .s-segmentos--lista .seg{grid-template-columns:1fr}
    }
@endsection

@section('corpo')
<header class="topo">
    <div class="wrap topo-in">
        <a class="marca" href="#conteudo">
            @if($empresa->logo_url)<img src="{{ $empresa->logo_url }}" alt="" width="36" height="36">@endif
            <span class="marca-nome">{{ $empresa->nome }}</span>
        </a>
        @include('site.partials.menu')
    </div>
</header>

@include('site.partials.faixa-aviso')

<main id="conteudo">
    <section class="h-min" aria-labelledby="hero-titulo">
        <div class="wrap h-min-grade{{ $empresa->site_banner_url ? '' : ' sem-foto' }}">
            <div>
                <p class="h-kicker">{{ $localidade ?: $empresa->nome }}</p>
                <h1 id="hero-titulo">{{ $empresa->site_titulo ?: $empresa->nome }}</h1>
                @if($heroLead)<p class="h-lead">{{ $heroLead }}</p>@endif
                @if($ctaPrincipal || $ctaSecundario)
                    <div class="h-acoes">
                        @if($ctaPrincipal)@include('site.partials.cta', ['cta' => $ctaPrincipal, 'classe' => 'btn btn-pri'])@endif
                        @if($ctaSecundario)@include('site.partials.cta', ['cta' => $ctaSecundario, 'classe' => 'btn btn-contorno'])@endif
                    </div>
                @endif
            </div>
            @if($empresa->site_banner_url)
                <figure class="h-foto"><img src="{{ $empresa->site_banner_url }}" alt="{{ $empresa->nome }}" fetchpriority="high" decoding="async"></figure>
            @endif
        </div>
    </section>

    @if($temSobre)
        <section id="sobre" class="sec" aria-labelledby="sobre-titulo">
            <div class="wrap">
                <header class="s-head">
                    <p class="s-kicker">A escola</p>
                    <h2 class="s-titulo" id="sobre-titulo">Sobre nós</h2>
                </header>
                <div class="sobre-min">
                    <div>@foreach($sobreParagrafos as $paragrafo)<p>{{ $paragrafo }}</p>@endforeach</div>
                    @if($empresa->site_imagem_sobre_url)
                        <img src="{{ $empresa->site_imagem_sobre_url }}" alt="" loading="lazy" decoding="async">
                    @endif
                </div>
            </div>
        </section>
    @endif

    @include('site.partials.segmentos', ['variante' => 'lista', 'kicker' => 'Ensino', 'titulo' => 'Segmentos'])
    @include('site.partials.unidades', ['variante' => 'lista', 'kicker' => 'Unidades'])
    @include('site.partials.matricula')
    @include('site.partials.galeria', ['variante' => 'grade', 'kicker' => 'Espaços', 'titulo' => 'Galeria', 'limite' => 9])
    @include('site.partials.acontece', ['variante' => 'colunas', 'kicker' => 'Agenda'])
    @include('site.partials.depoimentos', ['variante' => 'grade', 'kicker' => 'Depoimentos', 'titulo' => 'Famílias e alunos'])
    @include('site.partials.instagram')
    @include('site.partials.contato', ['variante' => 'lista', 'kicker' => 'Contato'])

    @if($ctaPrincipal)
        <section class="cta-min" aria-labelledby="cta-min-titulo">
            <div class="wrap">
                <h2 id="cta-min-titulo">Venha conhecer a escola pessoalmente.</h2>
                <div class="h-acoes">
                    @include('site.partials.cta', ['cta' => $ctaPrincipal, 'classe' => 'btn btn-pri'])
                    @include('site.partials.cta', ['cta' => ['rotulo' => 'Portal do Aluno', 'url' => $links['portal'], 'externo' => false], 'classe' => 'btn btn-contorno'])
                </div>
            </div>
        </section>
    @endif
</main>

@include('site.partials.footer', ['variante' => 'claro'])
@endsection
