{{-- Grade de cards por secao. Espera $secoes = [['titulo'=>..., 'cards'=>[[nome, icone, rota, descricao, cor]]]] --}}
<div class="home-launcher">
    @foreach ($secoes as $secao)
        <div class="home-section">
            <div class="home-section-title">{{ $secao['titulo'] }}</div>
            <div class="row">
                @foreach ($secao['cards'] as $card)
                    <div class="col-6 col-md-4 col-lg-3 mb-2">
                        <a href="{{ route($card[2]) }}" class="home-card" style="--card-cor: {{ $card[4] }};">
                            <span class="home-card-icone"><i class="{{ $card[1] }}"></i></span>
                            <span class="home-card-texto">
                                <span class="home-card-nome">{{ $card[0] }}</span>
                                <p class="home-card-desc">{{ $card[3] }}</p>
                            </span>
                            <span class="home-card-seta"><i class="fas fa-arrow-right"></i></span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
