@extends('site.layout')

{{-- Classico: institucional e tradicional. Serifada, topbar de utilidades, hero com foto e painel sobreposto,
     titulos centralizados com filete, segmentos em lista e agenda/comunicados lado a lado. --}}
@section('modelo', 'classico')
@section('fontes', 'source-serif-4:600,700|source-sans-3:400,600,700')
@section('preload', $empresa->site_banner_url ?? '')

@section('css')
    :root{--f-titulo:'Source Serif 4',Georgia,serif;--f-texto:'Source Sans 3',system-ui,sans-serif;--peso-titulo:700;--tracking-titulo:-.01em;
          --fundo:#ffffff;--superficie:#f5f4ef;--linha:#e2e0d8;--raio:3px;--raio-img:2px;--btn-raio:3px;--largura:1180px}
    .tpl-classico .s-head{margin-inline:auto;text-align:center}
    .tpl-classico .s-titulo::after{content:"";display:block;width:56px;height:3px;background:var(--c2);margin:1.1rem auto 0}
    .tpl-classico .s-kicker{color:var(--c1-texto)}

    .util{background:var(--c1-escura);color:rgba(255,255,255,.85);font-size:.85rem}
    .util-in{display:flex;justify-content:space-between;align-items:center;gap:1rem;min-height:40px}
    .util a{color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;min-height:40px}
    .util a:hover{color:#fff;text-decoration:underline}
    .util-grupo{display:flex;gap:1.5rem;align-items:center}
    .tpl-classico .topo{border-bottom:1px solid var(--linha)}
    .tpl-classico .topo-in{min-height:88px}
    .tpl-classico .marca-nome{font-size:1.3rem}
    .tpl-classico .menu-itens a{font-weight:600;font-size:.95rem}
    .tpl-classico .menu-portal{display:none}

    .h-classico{position:relative;background:var(--superficie)}
    .h-foto{height:clamp(320px,48vw,600px);overflow:hidden;background:var(--c1-escura)}
    .h-foto img{width:100%;height:100%;object-fit:cover}
    .h-painel{position:relative;margin-top:clamp(-11rem,-14vw,-5rem);background:var(--fundo);max-width:700px;padding:clamp(1.75rem,4vw,3rem);border-top:4px solid var(--c2);box-shadow:0 24px 50px -30px rgba(20,20,20,.35)}
    .h-sem-foto{background:var(--c1);color:var(--c1-sobre);padding-block:clamp(4rem,9vw,7rem)}
    .h-sem-foto .h-painel{margin-top:0;background:transparent;box-shadow:none;border-top:0;padding:0;color:inherit}
    .h-sem-foto h1,.h-sem-foto .h-kicker,.h-sem-foto .h-lead{color:inherit}
    .h-sem-foto .h-grade{display:flex;align-items:center;justify-content:space-between;gap:3rem}
    .h-sem-foto .h-logo{width:clamp(120px,16vw,180px);aspect-ratio:1;object-fit:contain;background:#fff;padding:1.1rem;border-radius:var(--raio)}
    .h-kicker{font-size:.8rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--c1-texto)}
    .h-classico h1{font-size:clamp(2.1rem,4.6vw,3.4rem);margin-top:.6rem}
    .h-lead{margin-top:1rem;font-size:1.12rem;color:var(--suave);max-width:560px}
    .h-acoes{display:flex;gap:.8rem;flex-wrap:wrap;margin-top:1.75rem}
    .h-sem-foto .btn-pri{background:#fff;color:var(--c1-texto);border-color:#fff}
    .h-sem-foto .btn-contorno{color:#fff}
    .h-sem-foto .btn-contorno:hover{background:rgba(255,255,255,.12)}
    .h-classico:not(.h-sem-foto){padding-bottom:clamp(2.5rem,5vw,4rem)}

    .sobre-grade{display:grid;grid-template-columns:1fr 1fr;gap:clamp(2rem,6vw,5rem);align-items:center}
    .sobre-txt p+p{margin-top:1rem}
    .sobre-txt .s-head{text-align:left;margin-inline:0}
    .sobre-txt .s-titulo::after{margin-left:0}
    .sobre-img{position:relative}
    .sobre-img img{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:var(--raio-img)}
    .sobre-img::before{content:"";position:absolute;inset:1.25rem -1.25rem -1.25rem 1.25rem;border:2px solid var(--c2);z-index:-1}
    #sobre{position:relative;isolation:isolate}
    .tpl-classico .s-segmentos,.tpl-classico .s-acontece{background:var(--superficie)}
    .tpl-classico .s-segmentos--lista .seg-nome{font-size:1.45rem}
    .tpl-classico .dep blockquote p{font-family:var(--f-titulo);font-style:italic;font-size:1.12rem}
    .tpl-classico .dep{border-top:3px solid var(--c2);padding-top:1.5rem}
    .tpl-classico .s-contato{background:var(--superficie)}
    .tpl-classico .ct-info .s-head{text-align:left;margin-inline:0}
    .tpl-classico .ct-info .s-titulo::after{margin-left:0}
    @media (max-width:1023px){.util-grupo--info{display:none}.util-in{justify-content:flex-end}}
    @media (max-width:859px){
        .sobre-grade{grid-template-columns:1fr}
        .sobre-img::before{inset:.75rem -.75rem -.75rem .75rem}
        .h-sem-foto .h-logo{display:none}
    }
@endsection

@section('corpo')
<div class="util">
    <div class="wrap util-in">
        <div class="util-grupo util-grupo--info">
            @if($telefone)<a href="tel:{{ preg_replace('/\D/', '', $telefone) }}"><i class="fas fa-phone" aria-hidden="true"></i> {{ $telefone }}</a>@endif
            @if($empresa->email)<a href="mailto:{{ $empresa->email }}"><i class="fas fa-envelope" aria-hidden="true"></i> {{ $empresa->email }}</a>@endif
        </div>
        <div class="util-grupo">
            <a href="{{ $links['portal'] }}"><i class="fas fa-user-graduate" aria-hidden="true"></i> Portal do Aluno</a>
            @if($links['instagram'])<a href="{{ $links['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram (abre em nova aba)"><i class="fab fa-instagram" aria-hidden="true"></i></a>@endif
        </div>
    </div>
</div>

<header class="topo">
    <div class="wrap topo-in">
        <a class="marca" href="#conteudo">
            @if($empresa->logo_url)<img src="{{ $empresa->logo_url }}" alt="" width="48" height="48">@endif
            <span class="marca-nome">{{ $empresa->nome }}@if($localidade)<small>{{ $localidade }}</small>@endif</span>
        </a>
        @include('site.partials.menu', ['semPortal' => true])
    </div>
</header>

@include('site.partials.faixa-aviso')

<main id="conteudo">
    <section class="h-classico{{ $empresa->site_banner_url ? '' : ' h-sem-foto' }}" aria-labelledby="hero-titulo">
        @if($empresa->site_banner_url)
            <div class="h-foto"><img src="{{ $empresa->site_banner_url }}" alt="{{ $empresa->nome }}" fetchpriority="high" decoding="async"></div>
        @endif
        <div class="wrap">
            <div class="h-grade">
                <div class="h-painel">
                    <p class="h-kicker">{{ $empresa->site_titulo ? $empresa->nome : ($localidade ?: 'Bem-vindo') }}</p>
                    <h1 id="hero-titulo">{{ $empresa->site_titulo ?: $empresa->nome }}</h1>
                    @if($heroLead)<p class="h-lead">{{ $heroLead }}</p>@endif
                    @if($ctaPrincipal || $ctaSecundario)
                        <div class="h-acoes">
                            @if($ctaPrincipal)@include('site.partials.cta', ['cta' => $ctaPrincipal, 'classe' => 'btn btn-pri', 'comIcone' => true])@endif
                            @if($ctaSecundario)@include('site.partials.cta', ['cta' => $ctaSecundario, 'classe' => 'btn btn-contorno'])@endif
                        </div>
                    @endif
                </div>
                @if(! $empresa->site_banner_url && $empresa->logo_url)
                    <img class="h-logo" src="{{ $empresa->logo_url }}" alt="">
                @endif
            </div>
        </div>
    </section>

    @if($temSobre)
        <section id="sobre" class="sec" aria-labelledby="sobre-titulo">
            <div class="wrap sobre-grade">
                <div class="sobre-txt">
                    <header class="s-head">
                        <p class="s-kicker">A escola</p>
                        <h2 class="s-titulo" id="sobre-titulo">Nossa escola</h2>
                    </header>
                    @foreach($sobreParagrafos as $paragrafo)<p>{{ $paragrafo }}</p>@endforeach
                </div>
                @if($empresa->site_imagem_sobre_url)
                    <figure class="sobre-img"><img src="{{ $empresa->site_imagem_sobre_url }}" alt="" loading="lazy" decoding="async"></figure>
                @endif
            </div>
        </section>
    @endif

    @include('site.partials.segmentos', ['variante' => 'lista', 'kicker' => 'Ensino', 'titulo' => 'Etapas de ensino'])
    @include('site.partials.unidades', ['variante' => 'cards'])
    @include('site.partials.matricula')
    @include('site.partials.acontece', ['variante' => 'colunas', 'kicker' => 'Notícias', 'titulo' => 'Acontece na escola'])
    @include('site.partials.galeria', ['variante' => 'grade'])
    @include('site.partials.depoimentos', ['variante' => 'grade', 'titulo' => 'O que dizem as famílias'])
    @include('site.partials.instagram')
    @include('site.partials.contato', ['variante' => 'painel'])
</main>

@include('site.partials.footer', ['variante' => 'escuro'])
@endsection
