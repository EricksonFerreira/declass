{{-- Contato institucional. Espera $empresa, $mostrarContato, $telefone, $endereco, $whats, $insta, $links, $ctaPrincipal;
     opcional $variante (painel|cards|lista), $titulo, $kicker --}}
@if($mostrarContato)
@php $variante = $variante ?? 'painel'; @endphp
<section id="contato" class="sec s-contato s-contato--{{ $variante }}" aria-labelledby="contato-titulo">
    <div class="wrap">
        <div class="ct-grade">
            <div class="ct-info">
                <header class="s-head">
                    <p class="s-kicker">{{ $kicker ?? 'Contato' }}</p>
                    <h2 class="s-titulo" id="contato-titulo">{{ $titulo ?? 'Fale conosco' }}</h2>
                </header>
                <dl class="ct-lista">
                    @if($endereco)
                        <div class="ct-item">
                            <dt><i class="fas fa-location-dot" aria-hidden="true"></i> Endereço</dt>
                            <dd>{{ $endereco }}@if($links['mapa'])<br><a href="{{ $links['mapa'] }}" target="_blank" rel="noopener">Ver no mapa<span class="sr"> (abre em nova aba)</span></a>@endif</dd>
                        </div>
                    @endif
                    @if($telefone)
                        <div class="ct-item">
                            <dt><i class="fas fa-phone" aria-hidden="true"></i> Telefone</dt>
                            <dd><a href="tel:{{ preg_replace('/\D/', '', $telefone) }}">{{ $telefone }}</a></dd>
                        </div>
                    @endif
                    @if($links['whatsapp'])
                        <div class="ct-item">
                            <dt><i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp</dt>
                            <dd><a href="{{ $links['whatsapp'] }}" target="_blank" rel="noopener">{{ $empresa->site_whatsapp }}<span class="sr"> (abre em nova aba)</span></a></dd>
                        </div>
                    @endif
                    @if($empresa->email)
                        <div class="ct-item">
                            <dt><i class="fas fa-envelope" aria-hidden="true"></i> E-mail</dt>
                            <dd><a href="mailto:{{ $empresa->email }}">{{ $empresa->email }}</a></dd>
                        </div>
                    @endif
                    @if($links['instagram'])
                        <div class="ct-item">
                            <dt><i class="fab fa-instagram" aria-hidden="true"></i> Instagram</dt>
                            <dd><a href="{{ $links['instagram'] }}" target="_blank" rel="noopener">{{ '@'.$insta }}<span class="sr"> (abre em nova aba)</span></a></dd>
                        </div>
                    @endif
                </dl>
            </div>
            @if($ctaPrincipal && $variante === 'painel')
                <aside class="ct-painel" aria-label="Visite a escola">
                    <h3>Venha nos conhecer</h3>
                    <p>Converse com a secretaria, tire suas dúvidas e conheça de perto a proposta pedagógica e os espaços da escola.</p>
                    @include('site.partials.cta', ['cta' => $ctaPrincipal, 'classe' => 'btn btn-claro', 'comIcone' => true])
                </aside>
            @endif
        </div>
    </div>
</section>
@endif
