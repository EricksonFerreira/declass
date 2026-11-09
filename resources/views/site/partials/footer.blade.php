{{-- Rodape institucional. Espera $empresa, $secoes, $links, $mostrarContato, $telefone, $endereco, $insta; opcional $variante (escuro|claro) --}}
<footer class="s-rodape s-rodape--{{ $variante ?? 'escuro' }}">
    <div class="wrap">
        <div class="rod-grade">
            <div class="rod-marca">
                <p class="rod-nome">
                    @if($empresa->logo_url)<img src="{{ $empresa->logo_url }}" alt="" width="44" height="44" loading="lazy">@endif
                    <span>{{ $empresa->nome }}</span>
                </p>
                @if($empresa->site_titulo)
                    <p class="rod-tagline">{{ $empresa->site_titulo }}</p>
                @endif
            </div>
            @if($secoes)
                <nav class="rod-col" aria-label="Seções do site">
                    <h2 class="rod-titulo">Navegue</h2>
                    <ul>
                        @foreach($secoes as $id => $rotulo)
                            <li><a href="#{{ $id }}">{{ $rotulo }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endif
            <nav class="rod-col" aria-label="Acesso rápido">
                <h2 class="rod-titulo">Acesso rápido</h2>
                <ul>
                    <li><a href="{{ $links['portal'] }}">Portal do Aluno</a></li>
                    @if($links['matricula'])<li><a href="{{ $links['matricula'] }}">Matrícula online</a></li>@endif
                    @if($links['visita'])<li><a href="{{ $links['visita'] }}" target="_blank" rel="noopener">Agende uma visita</a></li>@endif
                    @if($links['trabalhe'])<li><a href="{{ $links['trabalhe'] }}">Trabalhe conosco</a></li>@endif
                </ul>
            </nav>
            @if($mostrarContato)
                <div class="rod-col">
                    <h2 class="rod-titulo">Contato</h2>
                    <address>
                        @if($endereco)<p>{{ $endereco }}</p>@endif
                        @if($telefone)<p><a href="tel:{{ preg_replace('/\D/', '', $telefone) }}">{{ $telefone }}</a></p>@endif
                        @if($empresa->email)<p><a href="mailto:{{ $empresa->email }}">{{ $empresa->email }}</a></p>@endif
                    </address>
                    @if($links['instagram'] || $links['whatsapp'])
                        <p class="rod-redes">
                            @if($links['instagram'])<a href="{{ $links['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram (abre em nova aba)"><i class="fab fa-instagram" aria-hidden="true"></i></a>@endif
                            @if($links['whatsapp'])<a href="{{ $links['whatsapp'] }}" target="_blank" rel="noopener" aria-label="WhatsApp (abre em nova aba)"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>@endif
                        </p>
                    @endif
                </div>
            @endif
        </div>
        <div class="rod-legal">
            <p>© {{ date('Y') }} {{ $empresa->nome }}@if($empresa->cnpj) · CNPJ {{ $empresa->cnpj }}@endif</p>
            <p>Plataforma <strong>DeClass</strong> — Gestão Escolar</p>
        </div>
    </div>
</footer>
