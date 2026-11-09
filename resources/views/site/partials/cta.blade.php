{{-- Link de acao. Espera $cta (rotulo, url, externo, icone?) e $classe --}}
<a href="{{ $cta['url'] }}" class="{{ $classe }}"@if($cta['externo'] ?? false) target="_blank" rel="noopener"@endif>
    @if(($comIcone ?? false) && ! empty($cta['icone']))<i class="{{ $cta['icone'] }}" aria-hidden="true"></i>@endif
    <span>{{ $cta['rotulo'] }}</span>
    @if($cta['externo'] ?? false)<span class="sr">(abre em nova aba)</span>@endif
</a>
