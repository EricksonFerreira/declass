{{-- Galeria de fotos enviadas. Espera $empresa, $galeria; opcional $variante (grade|mosaico|faixa), $titulo, $kicker, $limite --}}
@if($galeria)
@php $variante = $variante ?? 'grade'; $fotos = array_slice($galeria, 0, $limite ?? 12); @endphp
<section id="galeria" class="sec s-galeria s-galeria--{{ $variante }}" aria-labelledby="galeria-titulo">
    <div class="wrap">
        <header class="s-head">
            <p class="s-kicker">{{ $kicker ?? 'Estrutura e dia a dia' }}</p>
            <h2 class="s-titulo" id="galeria-titulo">{{ $titulo ?? 'Galeria' }}</h2>
        </header>
        <ul class="gal-lista" role="list">
            @foreach($fotos as $i => $url)
                <li class="gal-item">
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="js-lightbox" data-grupo="galeria" aria-label="Ampliar foto {{ $i + 1 }} de {{ count($fotos) }}">
                        <img src="{{ $url }}" alt="Foto {{ $i + 1 }} de {{ count($fotos) }} — {{ $empresa->nome }}" loading="lazy" decoding="async">
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
