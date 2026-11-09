{{-- Etapas de ensino ativas. Espera $etapas, $links; opcional $variante (cards|lista|blocos|editorial), $titulo, $kicker, $intro --}}
@if($etapas->isNotEmpty())
@php $variante = $variante ?? 'cards'; $total = $etapas->count(); @endphp
<section id="segmentos" class="sec s-segmentos s-segmentos--{{ $variante }}" aria-labelledby="segmentos-titulo">
    <div class="wrap">
        <header class="s-head">
            <p class="s-kicker">{{ $kicker ?? 'Ensino' }}</p>
            <h2 class="s-titulo" id="segmentos-titulo">{{ $titulo ?? 'Segmentos' }}</h2>
            @if(! empty($intro))<p class="s-intro">{{ $intro }}</p>@endif
        </header>
        <ol class="seg-lista" role="list">
            @foreach($etapas as $i => $etapa)
                @php $rotuloSegmento = \App\Models\Etapa::SEGMENTOS[$etapa->segmento] ?? null; @endphp
                <li class="seg">
                    <span class="seg-num" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}@if($variante === 'editorial')<span> / {{ str_pad($total, 2, '0', STR_PAD_LEFT) }}</span>@endif</span>
                    <div class="seg-corpo">
                        @if($rotuloSegmento && $rotuloSegmento !== $etapa->nome)
                            <p class="seg-rotulo">{{ $rotuloSegmento }}</p>
                        @endif
                        <h3 class="seg-nome">{{ $etapa->nome }}</h3>
                        @if($etapa->descricao)
                            <p class="seg-desc">{{ \Illuminate\Support\Str::limit(strip_tags($etapa->descricao), 180) }}</p>
                        @endif
                    </div>
                    @if($links['matricula'])
                        <a class="seg-link" href="{{ $links['matricula'] }}" aria-label="Matrícula — {{ $etapa->nome }}">Matrícula <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
@endif
