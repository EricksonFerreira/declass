{{-- Depoimentos cadastrados pela escola ("Nome — papel | Texto"). Espera $depoimentos; opcional $variante (grade|destaque), $titulo, $kicker --}}
@if($depoimentos)
@php $variante = $variante ?? 'grade'; @endphp
<section id="depoimentos" class="sec s-depoimentos s-depoimentos--{{ $variante }}" aria-labelledby="depoimentos-titulo">
    <div class="wrap">
        <header class="s-head">
            <p class="s-kicker">{{ $kicker ?? 'Depoimentos' }}</p>
            <h2 class="s-titulo" id="depoimentos-titulo">{{ $titulo ?? 'Quem faz parte da nossa comunidade' }}</h2>
        </header>
        <ul class="dep-lista" role="list">
            @foreach($depoimentos as $dep)
                <li class="dep">
                    <figure>
                        <blockquote><p>{{ $dep['texto'] }}</p></blockquote>
                        <figcaption>
                            <span class="dep-nome">{{ $dep['nome'] }}</span>
                            @if($dep['papel'])<span class="dep-papel">{{ $dep['papel'] }}</span>@endif
                        </figcaption>
                    </figure>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
