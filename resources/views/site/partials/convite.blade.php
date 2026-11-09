{{-- Chamada final para visita/matricula. Espera $empresa, $ctaPrincipal, $ctaSecundario; opcional $imagem, $titulo --}}
@if($ctaPrincipal)
<section class="s-convite{{ ! empty($imagem) ? ' s-convite--foto' : '' }}" aria-labelledby="convite-titulo"@if(! empty($imagem)) style="--convite-img:url('{{ $imagem }}')"@endif>
    <div class="wrap s-convite-in">
        <div>
            <h2 id="convite-titulo">{{ $titulo ?? 'O próximo passo é conhecer a escola' }}</h2>
            <p>Agende um horário com a secretaria e conheça de perto o dia a dia da nossa escola.</p>
        </div>
        <div class="s-convite-acoes">
            @include('site.partials.cta', ['cta' => $ctaPrincipal, 'classe' => 'btn btn-claro', 'comIcone' => true])
            @if($ctaSecundario && $ctaSecundario['url'] !== ($ctaPrincipal['url'] ?? null))
                @include('site.partials.cta', ['cta' => $ctaSecundario, 'classe' => 'btn btn-contorno-claro'])
            @endif
        </div>
    </div>
</section>
@endif
