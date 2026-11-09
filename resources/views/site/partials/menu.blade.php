{{-- Navegacao principal: apenas secoes que existem na pagina. Espera $secoes, $links, $ctaPrincipal --}}
<button class="menu-botao" type="button" data-menu-botao aria-expanded="false" aria-controls="menu-principal">
    <span class="menu-botao-icone" aria-hidden="true"></span>
    <span class="sr">Abrir menu</span>
</button>
<nav class="menu" id="menu-principal" data-menu aria-label="Navegação principal">
    <ul class="menu-itens">
        @foreach($secoes as $id => $rotulo)
            <li><a href="#{{ $id }}">{{ $rotulo }}</a></li>
        @endforeach
    </ul>
    <div class="menu-acoes">
        @unless($semPortal ?? false)
            <a class="menu-portal" href="{{ $links['portal'] }}"><i class="fas fa-user-graduate" aria-hidden="true"></i> Portal do Aluno</a>
        @endunless
        @if($ctaPrincipal)
            @include('site.partials.cta', ['cta' => $ctaPrincipal, 'classe' => 'btn btn-pri'])
        @endif
    </div>
</nav>
