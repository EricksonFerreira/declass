@extends('site.layout')

{{-- Hero: moderno e impactante. Foto em tela quase cheia com header transparente, titulo grande na base,
     faixa de dados reais, segmentos em blocos de cor, "sobre" sangrado, galeria em mosaico e convite com foto. --}}
@section('modelo', 'hero')
@section('fontes', 'plus-jakarta-sans:400,500,600,700,800')
@section('preload', $empresa->site_banner_url ?? '')

@section('css')
    :root{--f-titulo:'Plus Jakarta Sans',system-ui,sans-serif;--f-texto:'Plus Jakarta Sans',system-ui,sans-serif;--peso-titulo:800;--tracking-titulo:-.03em;
          --fundo:#ffffff;--superficie:#f3f4f6;--tinta:#0b0d12;--linha:#e5e7eb;--raio:14px;--raio-img:14px;--btn-raio:999px;--btn-peso:700;--largura:1280px}
    .tpl-hero .s-titulo{font-size:clamp(2rem,4.4vw,3.4rem)}
    .tpl-hero .s-kicker{color:var(--c1-texto)}

    .tpl-hero .topo{position:fixed;inset:0 0 auto 0;background:transparent;color:#fff}
    .tpl-hero .topo .marca-nome,.tpl-hero .topo .menu-itens a,.tpl-hero .topo .menu-portal{color:#fff}
    .tpl-hero .topo .marca img{background:#fff;border-radius:10px;padding:4px}
    .tpl-hero .topo .menu-botao{background:transparent;color:#fff;border-color:rgba(255,255,255,.45)}
    .tpl-hero .topo.rolou,.menu-aberto.tpl-hero .topo{background:#fff;box-shadow:0 1px 0 var(--linha)}
    .tpl-hero .topo.rolou .marca-nome,.tpl-hero .topo.rolou .menu-itens a,.tpl-hero .topo.rolou .menu-portal,
    .menu-aberto.tpl-hero .topo .marca-nome{color:var(--tinta)}
    .tpl-hero .topo.rolou .menu-botao,.menu-aberto.tpl-hero .topo .menu-botao{color:var(--tinta);border-color:var(--linha)}
    .tpl-hero .topo .btn-pri{background:#fff;color:var(--c1-texto);border-color:#fff}
    .tpl-hero .topo.rolou .btn-pri{background:var(--c1);color:var(--c1-sobre);border-color:var(--c1)}
    @media (max-width:1023px){.tpl-hero .menu .menu-itens a,.tpl-hero .menu .menu-portal{color:var(--tinta)!important}.tpl-hero .menu .btn-pri{background:var(--c1)!important;color:var(--c1-sobre)!important}}

    .h-hero{position:relative;min-height:min(92vh,880px);display:flex;align-items:flex-end;color:#fff;background:var(--c1-escura);isolation:isolate;overflow:hidden}
    .h-hero>img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:-2}
    .h-hero::after{content:"";position:absolute;inset:0;z-index:-1;background:linear-gradient(180deg,rgba(0,0,0,.55) 0%,rgba(0,0,0,.05) 30%,rgba(0,0,0,.25) 55%,rgba(0,0,0,.78) 100%)}
    .h-hero.sem-foto::after{background:radial-gradient(120% 90% at 85% 10%,color-mix(in srgb,var(--c1) 70%,transparent),transparent 60%)}
    .h-conteudo{width:100%;padding-top:140px;padding-bottom:clamp(2.5rem,6vw,4.5rem)}
    .h-kicker{display:inline-block;font-size:.82rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#fff;opacity:.9}
    .h-hero h1{color:#fff;font-size:clamp(2.6rem,7vw,5.6rem);line-height:1.02;max-width:14ch;margin-top:.9rem}
    .h-lead{margin-top:1.25rem;font-size:clamp(1.05rem,1.6vw,1.25rem);max-width:600px;color:rgba(255,255,255,.88)}
    .h-acoes{display:flex;gap:.8rem;flex-wrap:wrap;margin-top:2rem}
    .h-acoes .btn-pri{background:#fff;color:var(--c1-texto);border-color:#fff;min-height:54px;padding-inline:1.9rem}
    .h-acoes .btn-pri:hover{background:var(--c2);color:var(--c2-sobre);border-color:var(--c2)}
    .h-fatos{display:flex;flex-wrap:wrap;gap:1.25rem 3rem;margin-top:clamp(2.5rem,5vw,4rem);padding-top:1.5rem;border-top:1px solid rgba(255,255,255,.28)}
    .h-fatos div{display:flex;flex-direction:column}
    .h-fatos dt{font-size:.74rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;opacity:.75;order:2}
    .h-fatos dd{font-size:1.35rem;font-weight:800;letter-spacing:-.02em}

    .tpl-hero .s-aviso{background:var(--c2)}
    .sobre-split{display:grid;grid-template-columns:1fr 1fr;min-height:560px;background:var(--superficie)}
    .sobre-split.sem-img{grid-template-columns:1fr}
    .sobre-split .sobre-img img{width:100%;height:100%;object-fit:cover;border-radius:0}
    .sobre-split .sobre-txt{padding:clamp(3rem,7vw,6rem) clamp(1.5rem,6vw,5.5rem);align-self:center;max-width:680px}
    .sobre-split.sem-img .sobre-txt{margin-inline:auto;max-width:820px}
    .sobre-txt p{font-size:1.08rem}
    .sobre-txt p+p{margin-top:1rem}
    .tpl-hero .s-segmentos--blocos .seg{border-radius:0}
    .tpl-hero .s-segmentos--blocos .seg-lista{border-radius:var(--raio);overflow:hidden}
    .tpl-hero .s-segmentos--blocos .seg:nth-child(3n){background:var(--c2);color:var(--c2-sobre)}
    .tpl-hero .s-acontece{background:var(--superficie)}
    .tpl-hero .s-acontece--cards .com,.tpl-hero .s-acontece--cards .ag{border:0;box-shadow:0 1px 2px rgba(0,0,0,.04),0 8px 24px -12px rgba(0,0,0,.12)}
    .tpl-hero .s-acontece .ac-subtitulo{border-bottom:0;padding-bottom:0}
    .tpl-hero .s-depoimentos{background:var(--c1);color:var(--c1-sobre)}
    .tpl-hero .s-depoimentos *{color:inherit}
    .tpl-hero .s-depoimentos .s-kicker{opacity:.8}
    .tpl-hero .s-depoimentos .dep-lista{grid-template-columns:repeat(auto-fit,minmax(300px,1fr))}
    .tpl-hero .s-depoimentos .dep:first-child{grid-column:1/-1}
    .tpl-hero .s-depoimentos .dep:not(:first-child) blockquote p{opacity:.92}
    .tpl-hero .s-unidades .uni{background:var(--superficie);border:0}
    .tpl-hero .s-contato--cards .ct-item{background:var(--superficie);border:0}
    @media (max-width:859px){
        .sobre-split{grid-template-columns:1fr;min-height:0}
        .sobre-split .sobre-img img{aspect-ratio:4/3;height:auto}
        .h-hero{min-height:86vh}
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

<main id="conteudo">
    <section class="h-hero{{ $empresa->site_banner_url ? '' : ' sem-foto' }}" aria-labelledby="hero-titulo">
        @if($empresa->site_banner_url)
            <img src="{{ $empresa->site_banner_url }}" alt="" fetchpriority="high" decoding="async">
        @endif
        <div class="wrap h-conteudo">
            <p class="h-kicker">{{ $empresa->site_titulo ? $empresa->nome : ($localidade ?? $empresa->nome) }}</p>
            <h1 id="hero-titulo">{{ $empresa->site_titulo ?: $empresa->nome }}</h1>
            @if($heroLead)<p class="h-lead">{{ $heroLead }}</p>@endif
            @if($ctaPrincipal || $ctaSecundario)
                <div class="h-acoes">
                    @if($ctaPrincipal)@include('site.partials.cta', ['cta' => $ctaPrincipal, 'classe' => 'btn btn-pri', 'comIcone' => true])@endif
                    @if($ctaSecundario)@include('site.partials.cta', ['cta' => $ctaSecundario, 'classe' => 'btn btn-contorno-claro'])@endif
                </div>
            @endif
            @if($etapas->isNotEmpty() || $escolas->isNotEmpty())
                <dl class="h-fatos">
                    @if($etapas->isNotEmpty())<div><dt>{{ $etapas->count() === 1 ? 'Segmento' : 'Segmentos' }}</dt><dd>{{ $etapas->count() }}</dd></div>@endif
                    @if($escolas->count() > 1)<div><dt>Unidades</dt><dd>{{ $escolas->count() }}</dd></div>@endif
                    @if($localidade)<div><dt>Localização</dt><dd>{{ $localidade }}</dd></div>@endif
                </dl>
            @endif
        </div>
    </section>

    @include('site.partials.faixa-aviso')

    @include('site.partials.segmentos', ['variante' => 'blocos', 'kicker' => 'Ensino', 'titulo' => 'Uma escola para cada fase'])

    @if($temSobre)
        <section id="sobre" class="sobre-split{{ $empresa->site_imagem_sobre_url ? '' : ' sem-img' }}" aria-labelledby="sobre-titulo">
            @if($empresa->site_imagem_sobre_url)
                <figure class="sobre-img"><img src="{{ $empresa->site_imagem_sobre_url }}" alt="" loading="lazy" decoding="async"></figure>
            @endif
            <div class="sobre-txt">
                <header class="s-head">
                    <p class="s-kicker">A escola</p>
                    <h2 class="s-titulo" id="sobre-titulo">Nossa proposta</h2>
                </header>
                @foreach($sobreParagrafos as $paragrafo)<p>{{ $paragrafo }}</p>@endforeach
            </div>
        </section>
    @endif

    @include('site.partials.galeria', ['variante' => 'mosaico', 'kicker' => 'Vida escolar', 'titulo' => 'Nossos espaços'])
    @include('site.partials.unidades', ['variante' => 'cards'])
    @include('site.partials.matricula')
    @include('site.partials.acontece', ['variante' => 'cards'])
    @include('site.partials.depoimentos', ['variante' => 'destaque', 'kicker' => 'Depoimentos', 'titulo' => 'Quem vive a escola'])
    @include('site.partials.instagram')
    @include('site.partials.contato', ['variante' => 'cards'])
    @include('site.partials.convite', ['imagem' => $galeria[0] ?? $empresa->site_imagem_sobre_url, 'titulo' => 'Venha nos conhecer'])
</main>

@include('site.partials.footer', ['variante' => 'escuro'])
@endsection
