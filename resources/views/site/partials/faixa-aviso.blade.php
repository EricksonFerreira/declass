{{-- Faixa de aviso: texto manual (site_aviso) ou periodo de matricula automatico. Espera $avisoSite --}}
@if($avisoSite)
    <div class="s-aviso{{ $avisoSite['tom'] === 'encerrada' ? ' s-aviso--encerrada' : '' }}" role="note">
        <div class="wrap s-aviso-in">
            <p>{{ $avisoSite['texto'] }}</p>
            @if($avisoSite['cta'])
                <a href="{{ $avisoSite['cta']['url'] }}" class="s-aviso-link"@if($avisoSite['cta']['externo']) target="_blank" rel="noopener"@endif>{{ $avisoSite['cta']['rotulo'] }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            @endif
        </div>
    </div>
@endif
