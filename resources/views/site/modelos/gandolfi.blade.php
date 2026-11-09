@extends('site.layout')

{{-- Gandolfi: institucional premium. Topbar de utilidades, nav sticky com CTA de acento, hero dividido
     com imagem sangrando ate a borda, servicos reais para familias, segmentos com faixa numerada. --}}
@section('modelo', 'gandolfi')
@section('fontes', 'dm-serif-display:400|dm-sans:400,500,700')
@section('preload', $empresa->site_banner_url ?? '')

@section('css')
    :root{--f-titulo:'DM Serif Display',Georgia,serif;--f-texto:'DM Sans',system-ui,sans-serif;--peso-titulo:400;--tracking-titulo:-.005em;
          --fundo:#ffffff;--superficie:#f4f5f7;--tinta:#101828;--texto:#3d4656;--suave:#5d6676;--linha:#e3e6ea;
          --raio:6px;--raio-img:6px;--btn-raio:6px;--btn-peso:700;--largura:1240px}
    .tpl-gandolfi .s-titulo{font-size:clamp(2.1rem,4.4vw,3.3rem)}
    .tpl-gandolfi .s-kicker{display:flex;align-items:center;gap:.7rem;color:var(--c1-texto)}
    .tpl-gandolfi .s-kicker::before{content:"";width:32px;height:2px;background:var(--c2)}

    .util{background:var(--c1-escura);color:rgba(255,255,255,.82);font-size:.84rem}
    .util-in{display:flex;justify-content:space-between;align-items:center;gap:1rem;min-height:42px}
    .util a{display:inline-flex;align-items:center;gap:.45rem;min-height:42px;color:inherit;text-decoration:none}
    .util a:hover{color:#fff}
    .util-grupo{display:flex;align-items:center;gap:1.6rem}
    .util-sep{width:1px;height:16px;background:rgba(255,255,255,.25)}
    .tpl-gandolfi .topo{position:sticky;top:0;box-shadow:0 1px 0 var(--linha)}
    .tpl-gandolfi .topo-in{min-height:84px}
    .tpl-gandolfi .marca-nome{font-size:1.4rem}
    .tpl-gandolfi .menu-itens a{font-weight:500}
    .tpl-gandolfi .menu-portal{display:none}
    .tpl-gandolfi .menu .btn-pri{background:var(--c2);border-color:var(--c2);color:var(--c2-sobre)}
    .tpl-gandolfi .menu .btn-pri:hover{filter:brightness(.93)}
    @media (max-width:1023px){.tpl-gandolfi .menu{inset:84px 0 0 0}.util-grupo--info{display:none}.util-in{justify-content:flex-end}}

    .h-gan{display:grid;grid-template-columns:1fr 1fr;min-height:min(78vh,720px);background:var(--superficie)}
    .h-gan-txt{display:flex;flex-direction:column;justify-content:center;padding:clamp(3rem,7vw,6rem) clamp(1.5rem,5vw,4.5rem) clamp(3rem,7vw,6rem) max(clamp(1.25rem,4vw,2.5rem),calc((100vw - var(--largura)) / 2 + clamp(1.25rem,4vw,2.5rem)))}
    .h-gan .h-kicker{display:flex;align-items:center;gap:.7rem;font-size:.8rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--c1-texto)}
    .h-gan .h-kicker::before{content:"";width:32px;height:2px;background:var(--c2)}
    .h-gan h1{font-size:clamp(2.6rem,5.4vw,4.4rem);line-height:1.05;margin-top:1.2rem;max-width:15ch}
    .h-gan .h-lead{margin-top:1.4rem;font-size:1.1rem;color:var(--suave);max-width:520px}
    .h-gan .h-acoes{display:flex;gap:.8rem;flex-wrap:wrap;margin-top:2.25rem}
    .h-gan .btn-pri{background:var(--c2);border-color:var(--c2);color:var(--c2-sobre)}
    .h-gan .btn-pri:hover{filter:brightness(.93)}
    .h-gan-img{position:relative;background:var(--c1)}
    .h-gan-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
    .h-gan-img.sem-foto{display:flex;align-items:center;justify-content:center}
    .h-gan-img.sem-foto img{position:static;width:clamp(140px,18vw,220px);height:auto;aspect-ratio:1;object-fit:contain;background:#fff;padding:1.4rem;border-radius:var(--raio)}

    .servicos{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));border:1px solid var(--linha);border-radius:var(--raio);overflow:hidden;background:var(--fundo)}
    .servico{display:flex;flex-direction:column;gap:.45rem;padding:1.6rem 1.5rem;text-decoration:none;border-right:1px solid var(--linha);min-height:100%}
    .servico:last-child{border-right:0}
    .servico i{font-size:1.25rem;color:var(--c1-texto);margin-bottom:.4rem}
    .servico strong{font-size:1.02rem;color:var(--tinta)}
    .servico span{font-size:.9rem;color:var(--suave)}
    .servico:hover{background:var(--c1-suave)}
    .faixa-servicos{margin-top:clamp(-3rem,-4vw,-2rem);position:relative;z-index:2}

    .tpl-gandolfi .s-segmentos--cards .seg{border-top:4px solid var(--c1);border-radius:0 0 var(--raio) var(--raio);padding-top:1.6rem}
    .tpl-gandolfi .s-segmentos--cards .seg:nth-child(even){border-top-color:var(--c2)}
    .tpl-gandolfi .s-segmentos--cards .seg-num{font-family:var(--f-titulo);font-size:2.2rem;font-weight:400;line-height:1;color:var(--c1-texto);opacity:.35}
    .tpl-gandolfi .s-segmentos{background:var(--superficie)}
    .sobre-gan{display:grid;grid-template-columns:1fr 1.1fr;gap:clamp(2rem,6vw,5rem);align-items:center}
    .sobre-gan img{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:var(--raio-img)}
    .sobre-gan .txt p{font-size:1.06rem}
    .sobre-gan .txt p+p{margin-top:1rem}
    .sobre-gan.sem-img{grid-template-columns:1fr;max-width:820px}
    .tpl-gandolfi .s-unidades--lista .uni-nome{font-size:1.5rem}
    .tpl-gandolfi .s-acontece{background:var(--superficie)}
    .tpl-gandolfi .s-acontece .ac-subtitulo{border-bottom-color:var(--c1)}
    .tpl-gandolfi .s-depoimentos .dep:first-child{border-left:3px solid var(--c2);padding-left:clamp(1.25rem,3vw,2.5rem)}
    .tpl-gandolfi .s-depoimentos .dep-lista{grid-template-columns:repeat(auto-fit,minmax(300px,1fr))}
    .tpl-gandolfi .s-depoimentos .dep:first-child{grid-column:1/-1}
    .tpl-gandolfi .gal-lista{grid-template-columns:repeat(3,1fr);gap:1rem}
    .tpl-gandolfi .gal-item img{aspect-ratio:16/11}
    @media (max-width:959px){
        .h-gan{grid-template-columns:1fr;min-height:0}
        .h-gan-txt{padding-inline:clamp(1.25rem,4vw,2.5rem)}
        .h-gan-img{min-height:300px}
        .h-gan-img.sem-foto{display:none}
        .servico{border-right:0;border-bottom:1px solid var(--linha)}
        .faixa-servicos{margin-top:0;padding-top:2.5rem}
        .sobre-gan{grid-template-columns:1fr}
    }
    @media (max-width:859px){.tpl-gandolfi .gal-lista{grid-template-columns:repeat(2,1fr)}}
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
            @if($links['instagram'] || $links['whatsapp'])<span class="util-sep" aria-hidden="true"></span>@endif
            @if($links['instagram'])<a href="{{ $links['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram (abre em nova aba)"><i class="fab fa-instagram" aria-hidden="true"></i></a>@endif
            @if($links['whatsapp'])<a href="{{ $links['whatsapp'] }}" target="_blank" rel="noopener" aria-label="WhatsApp (abre em nova aba)"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>@endif
        </div>
    </div>
</div>

<header class="topo">
    <div class="wrap topo-in">
        <a class="marca" href="#conteudo">
            @if($empresa->logo_url)<img src="{{ $empresa->logo_url }}" alt="" width="48" height="48">@endif
            <span class="marca-nome">{{ $empresa->nome }}</span>
        </a>
        @include('site.partials.menu', ['semPortal' => true])
    </div>
</header>

@include('site.partials.faixa-aviso')

<main id="conteudo">
    <section class="h-gan" aria-labelledby="hero-titulo">
        <div class="h-gan-txt">
            <p class="h-kicker">{{ $localidade ?: $empresa->nome }}</p>
            <h1 id="hero-titulo">{{ $empresa->site_titulo ?: $empresa->nome }}</h1>
            @if($heroLead)<p class="h-lead">{{ $heroLead }}</p>@endif
            @if($ctaPrincipal || $ctaSecundario)
                <div class="h-acoes">
                    @if($ctaPrincipal)@include('site.partials.cta', ['cta' => $ctaPrincipal, 'classe' => 'btn btn-pri', 'comIcone' => true])@endif
                    @if($ctaSecundario)@include('site.partials.cta', ['cta' => $ctaSecundario, 'classe' => 'btn btn-contorno'])@endif
                </div>
            @endif
        </div>
        <div class="h-gan-img{{ $empresa->site_banner_url ? '' : ' sem-foto' }}">
            @if($empresa->site_banner_url)
                <img src="{{ $empresa->site_banner_url }}" alt="{{ $empresa->nome }}" fetchpriority="high" decoding="async">
            @elseif($empresa->logo_url)
                <img src="{{ $empresa->logo_url }}" alt="">
            @endif
        </div>
    </section>

    <section class="faixa-servicos" aria-label="Para alunos e famílias">
        <div class="wrap">
            <div class="servicos">
                @foreach($servicos as $servico)
                    <a class="servico" href="{{ $servico['url'] }}"@if($servico['externo']) target="_blank" rel="noopener"@endif>
                        <i class="{{ $servico['icone'] }}" aria-hidden="true"></i>
                        <strong>{{ $servico['titulo'] }}</strong>
                        <span>{{ $servico['texto'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if($temSobre)
        <section id="sobre" class="sec" aria-labelledby="sobre-titulo">
            <div class="wrap sobre-gan{{ $empresa->site_imagem_sobre_url ? '' : ' sem-img' }}">
                @if($empresa->site_imagem_sobre_url)
                    <img src="{{ $empresa->site_imagem_sobre_url }}" alt="" loading="lazy" decoding="async">
                @endif
                <div class="txt">
                    <header class="s-head">
                        <p class="s-kicker">O colégio</p>
                        <h2 class="s-titulo" id="sobre-titulo">Nossa história e proposta</h2>
                    </header>
                    @foreach($sobreParagrafos as $paragrafo)<p>{{ $paragrafo }}</p>@endforeach
                </div>
            </div>
        </section>
    @endif

    @include('site.partials.segmentos', ['variante' => 'cards', 'kicker' => 'Ensino', 'titulo' => 'Segmentos de ensino'])
    @include('site.partials.unidades', ['variante' => 'lista'])
    @include('site.partials.matricula')
    @include('site.partials.acontece', ['variante' => 'colunas', 'kicker' => 'Fique por dentro'])
    @include('site.partials.galeria', ['variante' => 'grade', 'kicker' => 'Estrutura', 'titulo' => 'Conheça nossos espaços'])
    @include('site.partials.depoimentos', ['variante' => 'destaque', 'titulo' => 'Depoimentos'])
    @include('site.partials.instagram')
    @include('site.partials.contato', ['variante' => 'painel', 'titulo' => 'Fale conosco'])
</main>

@include('site.partials.footer', ['variante' => 'escuro'])
@endsection
