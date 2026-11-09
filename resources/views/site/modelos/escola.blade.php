@extends('site.layout')

{{-- Escola: editorial e acolhedor. Serifada suave, fundo quente, hero assimetrico com foto deslocada,
     declaracao em destaque, segmentos numerados em linhas editoriais e galeria em faixa rolavel. --}}
@section('modelo', 'escola')
@section('fontes', 'fraunces:500,600|nunito-sans:400,600,700,800')
@section('preload', $empresa->site_banner_url ?? '')

@section('css')
    :root{--f-titulo:'Fraunces',Georgia,serif;--f-texto:'Nunito Sans',system-ui,sans-serif;--peso-titulo:600;--tracking-titulo:-.02em;
          --fundo:#fdfbf7;--superficie:#f4efe6;--tinta:#1d1a16;--texto:#3e3a33;--suave:#6b655b;--linha:#e7e0d3;
          --raio:12px;--raio-img:20px;--btn-raio:10px;--btn-peso:700;--largura:1220px}
    .tpl-escola .s-titulo{font-size:clamp(2rem,4.2vw,3.1rem)}
    .tpl-escola .s-titulo em{font-style:italic;color:var(--c1-texto)}
    .tpl-escola .s-kicker{color:var(--c1-texto);letter-spacing:.1em}

    .tpl-escola .topo{background:var(--fundo);position:sticky;top:0;border-bottom:1px solid transparent}
    .tpl-escola .topo.rolou{border-bottom-color:var(--linha)}
    .tpl-escola .marca-nome{font-size:1.25rem}

    .h-esc{padding-block:clamp(2.5rem,6vw,5rem) clamp(4rem,8vw,6.5rem)}
    .h-esc-grade{display:grid;grid-template-columns:1.05fr 1fr;gap:clamp(2.5rem,6vw,5.5rem);align-items:center}
    .h-esc-grade.sem-foto{grid-template-columns:1fr;max-width:860px}
    .h-esc .h-kicker{display:inline-flex;align-items:center;gap:.6rem;font-size:.82rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--c1-texto)}
    .h-esc .h-kicker::before{content:"";width:10px;height:10px;border-radius:50%;background:var(--c2)}
    .h-esc h1{font-size:clamp(2.6rem,5.8vw,4.6rem);line-height:1.04;margin-top:1.1rem}
    .h-esc .h-lead{margin-top:1.35rem;font-size:1.15rem;color:var(--suave);max-width:520px}
    .h-esc .h-acoes{display:flex;gap:.8rem;flex-wrap:wrap;margin-top:2.1rem}
    .h-esc .h-foto{position:relative;isolation:isolate}
    .h-esc .h-foto img{width:100%;aspect-ratio:5/6;object-fit:cover;border-radius:var(--raio-img)}
    .h-esc .h-foto::before{content:"";position:absolute;z-index:-1;inset:-1.5rem 2.5rem 2.5rem -1.5rem;background:var(--c2-suave);border-radius:var(--raio-img)}
    .h-esc .h-legenda{position:absolute;left:-1.5rem;bottom:2rem;background:var(--fundo);border-radius:var(--raio);padding:1rem 1.25rem;box-shadow:0 18px 40px -22px rgba(29,26,22,.45);max-width:280px}
    .h-esc .h-legenda strong{display:block;font-family:var(--f-titulo);font-size:1.05rem;color:var(--tinta)}
    .h-esc .h-legenda span{font-size:.88rem;color:var(--suave)}

    .declara{background:var(--c1);color:var(--c1-sobre)}
    .declara .wrap{display:grid;grid-template-columns:1fr 2.2fr;gap:clamp(2rem,5vw,4rem);align-items:start}
    .declara .s-kicker{color:inherit;opacity:.8}
    .declara .frase{font-family:var(--f-titulo);font-size:clamp(1.55rem,3vw,2.35rem);line-height:1.3;font-weight:500;color:inherit;letter-spacing:-.015em}
    .declara .resto p{margin-top:1.1rem;opacity:.9;font-size:1.06rem;max-width:640px}
    .declara .img-sobre{margin-top:2rem}
    .declara .img-sobre img{width:100%;max-height:440px;object-fit:cover;border-radius:var(--raio-img)}

    .tpl-escola .s-segmentos--editorial .seg{border-top-color:var(--linha)}
    .tpl-escola .s-segmentos--editorial .seg:last-child{border-bottom:1px solid var(--linha)}
    .tpl-escola .s-segmentos--editorial .seg-num{font-size:1rem;color:var(--c1-texto)}
    .tpl-escola .s-segmentos--editorial .seg-num span{color:var(--suave)}
    .tpl-escola .s-galeria{background:var(--superficie)}
    .tpl-escola .s-galeria .gal-item a{border-radius:var(--raio-img)}
    .tpl-escola .s-acontece--cards .com,.tpl-escola .s-acontece--cards .ag{background:#fff;border-color:var(--linha)}
    .tpl-escola .s-depoimentos .dep{background:#fff;border:1px solid var(--linha);border-radius:var(--raio-img);padding:1.75rem}
    .tpl-escola .s-depoimentos .dep blockquote p{font-family:var(--f-titulo);font-size:1.12rem;font-weight:500}
    .tpl-escola .s-unidades .uni{background:#fff;border-radius:var(--raio-img)}
    .tpl-escola .s-contato--cards .ct-item{background:#fff;border-radius:var(--raio)}
    .tpl-escola .s-convite{background:var(--c1-escura)}
    .tpl-escola .s-convite h2{font-style:italic;font-weight:500}
    .tpl-escola .s-instagram{background:var(--fundo)}
    @media (max-width:859px){
        .h-esc-grade,.declara .wrap{grid-template-columns:1fr}
        .h-esc .h-foto img{aspect-ratio:4/3}
        .h-esc .h-foto::before{inset:-.75rem 1.25rem 1.25rem -.75rem}
        .h-esc .h-legenda{left:1rem;bottom:1rem;right:1rem;max-width:none}
    }
@endsection

@section('corpo')
<header class="topo" data-topo-rolagem>
    <div class="wrap topo-in">
        <a class="marca" href="#conteudo">
            @if($empresa->logo_url)<img src="{{ $empresa->logo_url }}" alt="" width="48" height="48">@endif
            <span class="marca-nome">{{ $empresa->nome }}</span>
        </a>
        @include('site.partials.menu')
    </div>
</header>

@include('site.partials.faixa-aviso')

<main id="conteudo">
    <section class="h-esc" aria-labelledby="hero-titulo">
        <div class="wrap h-esc-grade{{ $empresa->site_banner_url ? '' : ' sem-foto' }}">
            <div>
                <p class="h-kicker">{{ $localidade ? $empresa->nome.' · '.$localidade : $empresa->nome }}</p>
                <h1 id="hero-titulo">{{ $empresa->site_titulo ?: $empresa->nome }}</h1>
                @if($heroLead)<p class="h-lead">{{ $heroLead }}</p>@endif
                @if($ctaPrincipal || $ctaSecundario)
                    <div class="h-acoes">
                        @if($ctaPrincipal)@include('site.partials.cta', ['cta' => $ctaPrincipal, 'classe' => 'btn btn-pri', 'comIcone' => true])@endif
                        @if($ctaSecundario)@include('site.partials.cta', ['cta' => $ctaSecundario, 'classe' => 'btn btn-contorno'])@endif
                    </div>
                @endif
            </div>
            @if($empresa->site_banner_url)
                <figure class="h-foto">
                    <img src="{{ $empresa->site_banner_url }}" alt="{{ $empresa->nome }}" fetchpriority="high" decoding="async">
                    @if($escolas->count() > 1 || $etapas->isNotEmpty())
                        <figcaption class="h-legenda">
                            @if($etapas->isNotEmpty())
                                <strong>{{ $etapas->first()->nome }}@if($etapas->count() > 1) – {{ $etapas->last()->nome }}@endif</strong>
                            @endif
                            @if($escolas->count() > 1)<span>{{ $escolas->count() }} unidades</span>@endif
                        </figcaption>
                    @endif
                </figure>
            @endif
        </div>
    </section>

    @if($temSobre)
        <section id="sobre" class="sec declara" aria-labelledby="sobre-titulo">
            <div class="wrap">
                <header>
                    <p class="s-kicker">Nossa proposta</p>
                    <h2 class="sr" id="sobre-titulo">Sobre a escola</h2>
                </header>
                <div>
                    @if($sobreParagrafos)<p class="frase">{{ $sobreParagrafos[0] }}</p>@endif
                    <div class="resto">@foreach(array_slice($sobreParagrafos, 1) as $paragrafo)<p>{{ $paragrafo }}</p>@endforeach</div>
                    @if($empresa->site_imagem_sobre_url)
                        <figure class="img-sobre"><img src="{{ $empresa->site_imagem_sobre_url }}" alt="" loading="lazy" decoding="async"></figure>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @include('site.partials.segmentos', ['variante' => 'editorial', 'kicker' => 'Cada etapa importa', 'titulo' => 'Nosso ensino'])
    @include('site.partials.galeria', ['variante' => 'faixa', 'kicker' => 'Vida na escola', 'titulo' => 'Um lugar para aprender e conviver'])
    @include('site.partials.unidades', ['variante' => 'cards', 'kicker' => 'Onde estamos'])
    @include('site.partials.matricula')
    @include('site.partials.acontece', ['variante' => 'cards', 'kicker' => 'Acontece na escola'])
    @include('site.partials.depoimentos', ['variante' => 'grade', 'kicker' => 'Nossa comunidade', 'titulo' => 'O que dizem as famílias'])
    @include('site.partials.instagram')
    @include('site.partials.contato', ['variante' => 'cards'])
    @include('site.partials.convite', ['titulo' => 'O próximo passo começa com uma visita'])
</main>

@include('site.partials.footer', ['variante' => 'escuro'])
@endsection
