{{-- Unidades escolares ativas. Espera $empresa, $escolas, $mostrarUnidades, $mostrarMatricula; opcional $variante (cards|lista), $titulo, $kicker --}}
@if($mostrarUnidades)
@php $variante = $variante ?? 'cards'; @endphp
<section id="unidades" class="sec s-unidades s-unidades--{{ $variante }}" aria-labelledby="unidades-titulo">
    <div class="wrap">
        <header class="s-head">
            <p class="s-kicker">{{ $kicker ?? 'Unidades' }}</p>
            <h2 class="s-titulo" id="unidades-titulo">{{ $titulo ?? 'Nossas unidades' }}</h2>
            <p class="s-intro">{{ $escolas->count() }} {{ $escolas->count() === 1 ? 'unidade' : 'unidades' }}{{ $localidade ? ' em '.$localidade : '' }}</p>
        </header>
        <ul class="uni-lista" role="list">
            @foreach($escolas as $escola)
                <li class="uni">
                    <div class="uni-topo">
                        @if($escola->cidade)
                            <p class="uni-cidade">{{ $escola->cidade }}{{ $escola->estado ? ' — '.$escola->estado : '' }}</p>
                        @endif
                        <h3 class="uni-nome">{{ $escola->nome }}</h3>
                    </div>
                    <dl class="uni-dados">
                        @if($escola->endereco)
                            <div><dt><i class="fas fa-location-dot" aria-hidden="true"></i><span class="sr">Endereço</span></dt><dd>{{ $escola->endereco }}</dd></div>
                        @endif
                        @if($escola->telefone)
                            <div><dt><i class="fas fa-phone" aria-hidden="true"></i><span class="sr">Telefone</span></dt><dd><a href="tel:{{ preg_replace('/\D/', '', $escola->telefone) }}">{{ $escola->telefone }}</a></dd></div>
                        @endif
                        @if($escola->email)
                            <div><dt><i class="fas fa-envelope" aria-hidden="true"></i><span class="sr">E-mail</span></dt><dd><a href="mailto:{{ $escola->email }}">{{ $escola->email }}</a></dd></div>
                        @endif
                    </dl>
                    @if($mostrarMatricula || $escola->endereco)
                        <div class="uni-acoes">
                            @if($mostrarMatricula)
                                <a href="{{ route('matricula_publica.create', [$empresa->slug, $escola->slug]) }}" class="btn btn-pri btn-sm">Matrícula</a>
                            @endif
                            @if($escola->endereco)
                                <a href="https://www.google.com/maps/search/?api=1&amp;query={{ rawurlencode($escola->endereco.($escola->cidade ? ', '.$escola->cidade : '')) }}" class="uni-mapa" target="_blank" rel="noopener">Como chegar<span class="sr"> (abre em nova aba)</span></a>
                            @endif
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
