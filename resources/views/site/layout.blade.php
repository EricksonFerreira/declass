{{-- Layout unico dos modelos do site publico: head (SEO, fontes, paleta), a11y base e menu mobile.
     Cada modelo define: modelo, fontes, css e corpo. Dados vem do SiteEmpresaService. --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['titulo'] }}</title>
    <meta name="description" content="{{ $seo['descricao'] }}">
    <meta name="robots" content="{{ $seo['robots'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="{{ $empresa->nome }}">
    <meta property="og:title" content="{{ $seo['titulo'] }}">
    <meta property="og:description" content="{{ $seo['descricao'] }}">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    @if($seo['imagem'])
        <meta property="og:image" content="{{ $seo['imagem'] }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif
    <meta name="theme-color" content="{{ $tema['c1'] }}">
    @if($empresa->logo_url)
        <link rel="icon" href="{{ $empresa->logo_url }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=@yield('fontes')&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>
    @if($imagemHero = trim($__env->yieldContent('preload')))
        <link rel="preload" as="image" href="{{ $imagemHero }}" fetchpriority="high">
    @endif
    <script type="application/ld+json">{!! json_encode($seo['jsonLd'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    <style>
        :root{
            --c1:{{ $tema['c1'] }};--c1-texto:{{ $tema['c1_texto'] }};--c1-sobre:{{ $tema['c1_sobre'] }};
            --c1-escura:{{ $tema['c1_escura'] }};--c1-suave:{{ $tema['c1_suave'] }};
            --c2:{{ $tema['c2'] }};--c2-texto:{{ $tema['c2_texto'] }};--c2-sobre:{{ $tema['c2_sobre'] }};--c2-suave:{{ $tema['c2_suave'] }};
        }
        @include('site.css.base')
        @yield('css')
    </style>
</head>
<body class="tpl tpl-@yield('modelo')">
    <a class="pular" href="#conteudo">Pular para o conteúdo</a>
    @yield('corpo')
    <script>
        (function () {
            var botao = document.querySelector('[data-menu-botao]');
            var menu = document.querySelector('[data-menu]');
            if (botao && menu) {
                var fechar = function () { botao.setAttribute('aria-expanded', 'false'); menu.classList.remove('aberto'); document.body.classList.remove('menu-aberto'); };
                botao.addEventListener('click', function () {
                    var abrir = botao.getAttribute('aria-expanded') !== 'true';
                    botao.setAttribute('aria-expanded', abrir ? 'true' : 'false');
                    menu.classList.toggle('aberto', abrir);
                    document.body.classList.toggle('menu-aberto', abrir);
                });
                menu.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', fechar); });
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { fechar(); botao.focus(); } });
            }
            var topo = document.querySelector('[data-topo-rolagem]');
            if (topo) {
                var marcar = function () { topo.classList.toggle('rolou', window.scrollY > 24); };
                marcar();
                window.addEventListener('scroll', marcar, { passive: true });
            }

            // Lightbox da galeria: clique na miniatura abre a foto inteira;
            // setas/teclado navegam, Esc/fechar sai. Sem JS o link abre a foto.
            var links = Array.prototype.slice.call(document.querySelectorAll('.js-lightbox'));
            if (links.length) {
                var lb = document.createElement('div');
                lb.className = 'lightbox';
                lb.setAttribute('role', 'dialog');
                lb.setAttribute('aria-modal', 'true');
                lb.setAttribute('aria-label', 'Foto ampliada');
                lb.innerHTML = '<img alt=""><button type="button" class="lb-fechar" aria-label="Fechar">&times;</button>' +
                    '<button type="button" class="lb-ant" aria-label="Foto anterior">&#10094;</button>' +
                    '<button type="button" class="lb-prox" aria-label="Próxima foto">&#10095;</button>' +
                    '<div class="lb-contador"></div>';
                document.body.appendChild(lb);

                var img = lb.querySelector('img'), contador = lb.querySelector('.lb-contador');
                var atual = 0, focoAnterior = null;

                var mostrar = function (i) {
                    atual = (i + links.length) % links.length;
                    var link = links[atual];
                    img.src = link.href;
                    img.alt = link.querySelector('img') ? link.querySelector('img').alt : '';
                    contador.textContent = (atual + 1) + ' / ' + links.length;
                };
                var abrir = function (i, el) {
                    focoAnterior = el;
                    mostrar(i);
                    lb.classList.add('aberto');
                    document.body.style.overflow = 'hidden';
                    lb.querySelector('.lb-fechar').focus();
                };
                var fecharLb = function () {
                    lb.classList.remove('aberto');
                    document.body.style.overflow = '';
                    img.removeAttribute('src');
                    if (focoAnterior) { focoAnterior.focus(); }
                };

                links.forEach(function (link, i) {
                    link.addEventListener('click', function (e) { e.preventDefault(); abrir(i, link); });
                });
                lb.querySelector('.lb-fechar').addEventListener('click', fecharLb);
                lb.querySelector('.lb-ant').addEventListener('click', function () { mostrar(atual - 1); });
                lb.querySelector('.lb-prox').addEventListener('click', function () { mostrar(atual + 1); });
                lb.addEventListener('click', function (e) { if (e.target === lb) { fecharLb(); } });
                document.addEventListener('keydown', function (e) {
                    if (! lb.classList.contains('aberto')) { return; }
                    if (e.key === 'Escape') { fecharLb(); }
                    if (e.key === 'ArrowLeft') { mostrar(atual - 1); }
                    if (e.key === 'ArrowRight') { mostrar(atual + 1); }
                });
            }
        })();
    </script>
</body>
</html>
