{{-- Escolha da unidade para matricula — so quando ha varias escolas e a secao de unidades esta oculta. Espera $links, $mostrarUnidades --}}
@if($links['matriculas'] && count($links['matriculas']) > 1 && ! $mostrarUnidades)
<section id="matricula" class="sec s-matricula" aria-labelledby="matricula-titulo">
    <div class="wrap">
        <header class="s-head">
            <p class="s-kicker">Matrículas</p>
            <h2 class="s-titulo" id="matricula-titulo">Escolha a unidade</h2>
        </header>
        <ul class="mat-lista" role="list">
            @foreach($links['matriculas'] as $item)
                <li><a class="mat-item" href="{{ $item['url'] }}"><span>{{ $item['nome'] }}</span> <i class="fas fa-arrow-right" aria-hidden="true"></i></a></li>
            @endforeach
        </ul>
    </div>
</section>
@endif
