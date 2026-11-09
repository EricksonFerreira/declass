{{-- Agenda (eventos para familias) + comunicados publicados. Espera $eventos, $avisos;
     opcional $variante (colunas|cards|lista), $titulo, $kicker --}}
@if($eventos->isNotEmpty() || $avisos->isNotEmpty())
@php $variante = $variante ?? 'colunas'; $ambos = $eventos->isNotEmpty() && $avisos->isNotEmpty(); @endphp
<section id="acontece" class="sec s-acontece s-acontece--{{ $variante }}{{ $ambos ? ' s-acontece--ambos' : '' }}" aria-labelledby="acontece-titulo">
    <div class="wrap">
        <header class="s-head">
            <p class="s-kicker">{{ $kicker ?? 'Fique por dentro' }}</p>
            <h2 class="s-titulo" id="acontece-titulo">{{ $titulo ?? ($ambos ? 'Agenda e comunicados' : ($eventos->isNotEmpty() ? 'Agenda' : 'Comunicados')) }}</h2>
        </header>
        <div class="ac-grade">
            @if($eventos->isNotEmpty())
                <div class="ac-bloco ac-agenda" id="agenda">
                    @if($ambos)<h3 class="ac-subtitulo">Próximos eventos</h3>@endif
                    <ul class="ag-lista" role="list">
                        @foreach($eventos as $evento)
                            <li class="ag">
                                <time class="ag-data" datetime="{{ $evento->data_inicio->toDateString() }}">
                                    <span class="ag-dia">{{ $evento->data_inicio->format('d') }}</span>
                                    <span class="ag-mes">{{ $evento->data_inicio->translatedFormat('M') }}</span>
                                </time>
                                <div class="ag-corpo">
                                    <p class="ag-tipo">{{ \App\Models\Evento::TIPOS[$evento->tipo] ?? $evento->tipo }}</p>
                                    <h4 class="ag-titulo">{{ $evento->titulo }}</h4>
                                    <p class="ag-det">
                                        @if($evento->hora_inicio)<span>{{ substr($evento->hora_inicio, 0, 5) }}</span>@endif
                                        @if($evento->data_fim && $evento->data_fim->ne($evento->data_inicio))<span>até {{ $evento->data_fim->format('d/m') }}</span>@endif
                                        @if($evento->local)<span>{{ $evento->local }}</span>@endif
                                        @if($evento->unidade)<span>{{ $evento->unidade }}</span>@endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if($avisos->isNotEmpty())
                <div class="ac-bloco ac-comunicados" id="comunicados">
                    @if($ambos)<h3 class="ac-subtitulo">Comunicados</h3>@endif
                    <ul class="com-lista" role="list">
                        @foreach($avisos as $aviso)
                            <li class="com">
                                <article>
                                    <p class="com-meta">
                                        <time datetime="{{ $aviso->data_publicacao->toDateString() }}">{{ $aviso->data_publicacao->translatedFormat('d \d\e M \d\e Y') }}</time>
                                        @if($aviso->tipo === 'urgente')<span class="com-urgente">Importante</span>@endif
                                    </p>
                                    <h4 class="com-titulo">{{ $aviso->titulo }}</h4>
                                    @if($aviso->mensagem)
                                        <p class="com-resumo">{{ \Illuminate\Support\Str::limit(strip_tags($aviso->mensagem), 150) }}</p>
                                    @endif
                                </article>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</section>
@endif
