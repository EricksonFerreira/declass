<div class="rs-card mb-3">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-route"></i></div>
        <div>
            <div class="rs-card__title">Sua viagem</div>
            <div class="rs-card__sub">Retirada, devolução e período contratado</div>
        </div>
    </div>
    <div class="rs-card__body">
        <div class="rs-route">
            <div class="rs-stop">
                <div class="rs-dot rs-dot--a"></div>
                <div>
                    <div class="rs-label">Retirada</div>
                    <div class="rs-place">{{ $estacaoRetirada }}</div>
                    <div class="rs-date">{{ $inicioFormatado }}</div>
                </div>
            </div>
            <div class="rs-mid">
                <div class="rs-mid__line"></div>
                <div class="rs-mid__pill">{{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }}</div>
                <div class="rs-mid__line"></div>
            </div>
            <div class="rs-stop rs-stop--right">
                <div class="rs-dot rs-dot--b"></div>
                <div>
                    <div class="rs-label">Devolução</div>
                    <div class="rs-place">{{ $estacaoDevolucao }}</div>
                    <div class="rs-date">{{ $fimFormatado }}</div>
                </div>
            </div>
        </div>

        <div class="rs-actions" style="display:flex;gap:10px;flex-wrap:wrap;">
            @if($reserva->contratoCheckin && $reserva->contratoCheckin->assinado === 'S')
                <a
                    href="{{ route('reserva.minhas.vercontratocheckin', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) }}"
                    target="_blank"
                    class="rs-btn"
                    style="background:#ecfdf5;color:#047857;border-color:#a7f3d0;display:inline-flex;align-items:center;gap:6px;"
                >
                    <i class="fas fa-file-pdf"></i> Ver Contrato Check-in
                </a>
            @endif
            @if($checkoutExiste)
                <a
                    href="{{ $checkoutAssinado ? route('reserva.minhas.vercontratocheckout', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) : route('reserva.minhas.assinar-checkout', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) }}"
                    target="{{ $checkoutAssinado ? '_blank' : '_self' }}"
                    class="rs-btn"
                    style="background:{{ $checkoutAssinado ? '#eff6ff' : '#e0f2fe' }};color:{{ $checkoutAssinado ? '#1d4ed8' : '#0369a1' }};border-color:{{ $checkoutAssinado ? '#bfdbfe' : '#7dd3fc' }};display:inline-flex;align-items:center;gap:6px;"
                >
                    <i class="fas {{ $checkoutAssinado ? 'fa-file-pdf' : 'fa-signature' }}"></i>
                    {{ $checkoutAssinado ? 'Ver Contrato de Devolução' : 'Assinar Contrato de Devolução' }}
                </a>
            @endif
            @if($podeExibirBotaoCancelar)
                <button
                    type="button"
                    class="rs-btn"
                    data-cancelar-reserva="1"
                    data-action="{{ route('reserva.cancelar', $reserva->codigoReserva?->codigo ?? $reserva->id) }}"
                    data-pode-cancelar="{{ ($podeCancelarMinhaReserva || $podeCancelarReserva) ? '1' : '0' }}"
                    data-toggle="tooltip"
                    title="{{ $textoBotaoCancelar }}"
                    style="background:#fee2e2;color:#991b1b;border-color:#fecaca;{{ ($usuarioEhCriador && !$podeCancelarMinhaReserva) ? 'opacity:.7;' : '' }}"
                >
                    <i class="fas fa-ban"></i> Cancelar reserva
                </button>
            @endif
        </div>
    </div>
</div>

<div class="rs-card mb-3">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-car"></i></div>
        <div>
            <div class="rs-card__title">Veículo reservado</div>
            <div class="rs-card__sub">Grupo, modelo e informações essenciais</div>
        </div>
    </div>
    <div class="rs-card__body">
        <div class="rs-vehicle">
            <div class="rs-vehicle__img">
                <img src="{{ $reserva->grupoFrota?->imagem_url ?: asset('assets/imgs/logo-sem-texto.png') }}" alt="{{ $grupoNome }}">
            </div>
            <div>
                <div class="rs-vehicle__badges">
                    <span class="rs-badge rs-badge--blue"><i class="fas fa-layer-group"></i> {{ $grupoNome }}</span>
                    <span class="rs-badge"><i class="fas fa-car-side"></i> {{ $modeloNome }}</span>
                    @if(!empty($reserva->modelos?->combustivel))
                        <span class="rs-badge rs-badge--green"><i class="fas fa-gas-pump"></i> {{ $reserva->modelos->combustivel }}</span>
                    @endif
                    @if(!empty($reserva->modelos?->transmissao))
                        <span class="rs-badge rs-badge--amber"><i class="fas fa-cogs"></i> {{ $reserva->modelos->transmissao }}</span>
                    @endif
                </div>
                <div class="rs-note">
                    A imagem acima representa o grupo/modelo reservado. Se houver substituição de veículo, a equipe pode informar no balcão de atendimento.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="rs-card" id="card-satisfacao">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-star"></i></div>
        <div>
            <div class="rs-card__title">Sua avaliação</div>
            <div class="rs-card__sub">Pode ser preenchida apenas uma vez</div>
        </div>
    </div>
    <div class="rs-card__body">
        @if($podeResponderSatisfacao)
            <form method="POST" action="{{ route('reserva.minhas.satisfacao.store', $reserva->codigoReserva?->codigo ?? $reserva->id) }}" class="rs-survey" onsubmit="var btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerHTML = '<i class=&quot;fas fa-spinner fa-spin mr-2&quot;></i> Processando...';">
                @csrf
                <div class="rs-survey__group">
                    <label class="rs-survey__label">1. Como foi sua experiência geral?</label>
                    <div class="rs-survey__options">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="rs-survey__option">
                                <input type="radio" name="satisfacao_geral" value="{{ $i }}" required>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                </div>

                <div class="rs-survey__group">
                    <label class="rs-survey__label">2. Foi fácil concluir a reserva?</label>
                    <div class="rs-survey__options">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="rs-survey__option">
                                <input type="radio" name="facilidade_reserva" value="{{ $i }}" required>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                </div>

                <div class="rs-survey__group">
                    <label class="rs-survey__label">3. De 0 a 10, quanto você recomendaria a empresa?</label>
                    <div class="rs-survey__options rs-survey__options--nps">
                        @for($i = 0; $i <= 10; $i++)
                            @php
                                $npsClass = match (true) {
                                    $i <= 2 => 'rs-nps rs-nps--red',
                                    $i <= 5 => 'rs-nps rs-nps--orange',
                                    $i <= 7 => 'rs-nps rs-nps--yellow',
                                    $i === 8 => 'rs-nps rs-nps--lightgreen',
                                    $i === 9 => 'rs-nps rs-nps--green',
                                    default => 'rs-nps rs-nps--darkgreen',
                                };
                            @endphp
                            <label class="rs-survey__option">
                                <input type="radio" name="nps_nota" value="{{ $i }}" required>
                                <span class="{{ $npsClass }}">{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                </div>

                <div class="rs-survey__group mb-0">
                    <label class="rs-survey__label" for="comentario">O que podemos melhorar? <span style="font-weight:400;">(opcional)</span></label>
                    <textarea id="comentario" name="comentario" class="rs-survey__textarea" placeholder="Deixe uma observação rápida."></textarea>
                </div>

                <div class="rs-actions" style="margin-top:14px;">
                    <button type="submit" class="rs-btn rs-btn--primary">
                        <i class="fas fa-paper-plane"></i> Enviar avaliação
                    </button>
                </div>
            </form>
        @else
            <div class="rs-note">
                @if($jaSatisfacao)
                    Obrigado! Sua avaliação já foi enviada e não pode ser alterada.
                @else
                    A avaliação ficará disponível após a assinatura do contrato de check-out.
                @endif
            </div>
        @endif
    </div>
</div>
