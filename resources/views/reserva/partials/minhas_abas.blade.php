<div class="rs-tabs-wrapper">
    <ul class="nav nav-tabs rs-nav-tabs" id="reservaMainTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#pane-visao-geral" role="tab">
                <i class="fas fa-home"></i> <span>Visão Geral</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#pane-checkin" role="tab">
                <i class="fas fa-file-signature"></i> <span>Check-in</span>
                @if($checkinExiste ?? false)
                    <span class="rs-tab-badge {{ ($checkinAssinado ?? false) ? 'rs-tab-badge--green' : 'rs-tab-badge--amber' }}">
                        {{ ($checkinAssinado ?? false) ? 'Assinado' : 'Pendente' }}
                    </span>
                @endif
            </a>
        </li>
        @if($checkoutExiste ?? false)
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#pane-checkout" role="tab">
                    <i class="fas fa-car-crash"></i> <span>Check-out</span>
                    @if($checkoutAssinado ?? false)
                        <span class="rs-tab-badge rs-tab-badge--green">Assinado</span>
                    @endif
                </a>
            </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#pane-financeiro" role="tab">
                <i class="fas fa-file-invoice-dollar"></i> <span>Financeiro</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#pane-danos" role="tab">
                <i class="fas fa-exclamation-triangle"></i> <span>Danos</span>
                @if(($totalDano ?? 0) > 0)
                    <span class="rs-tab-badge rs-tab-badge--red">{{ $totalDano }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#pane-sinistros" role="tab">
                <i class="fas fa-exclamation-circle"></i> <span>Sinistros</span>
                @if(($totalSinistros ?? 0) > 0)
                    <span class="rs-tab-badge rs-tab-badge--red">{{ $totalSinistros }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#pane-avaliacao" role="tab">
                <i class="fas fa-star"></i> <span>Sua avaliação</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#pane-historico" role="tab">
                <i class="fas fa-history"></i> <span>Histórico</span>
            </a>
        </li>
    </ul>

    <div class="tab-content rs-tab-content">
        <div class="tab-pane fade show active" id="pane-visao-geral" role="tabpanel">
            <div class="rs-card">
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
                    <div class="rs-actions" style="margin-top:12px;">
                        @if($podeExibirBotaoCancelar ?? false)
                            <button type="button" class="rs-btn" data-cancelar-reserva="1" data-action="{{ route('reserva.cancelar', $reserva->codigoReserva?->codigo ?? $reserva->id) }}">
                                <i class="fas fa-ban"></i> Cancelar reserva
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pane-checkin" role="tabpanel">
            <div class="rs-card">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-file-signature"></i></div>
                    <div>
                        <div class="rs-card__title">Check-in</div>
                        <div class="rs-card__sub">Contrato e assinatura de entrada</div>
                    </div>
                </div>
                <div class="rs-card__body">
                    @if($checkinExiste ?? false)
                        <div class="rs-note">
                            @if($checkinAssinado ?? false)
                                O check-in já foi assinado.
                            @else
                                O check-in está pendente de assinatura.
                            @endif
                        </div>
                    @else
                        <div class="rs-note">Nenhum check-in encontrado para esta reserva.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pane-checkout" role="tabpanel">
            <div class="rs-card">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-car-crash"></i></div>
                    <div>
                        <div class="rs-card__title">Check-out</div>
                        <div class="rs-card__sub">Resumo da devolução do veículo</div>
                    </div>
                </div>
                <div class="rs-card__body">
                    @if($checkoutExiste ?? false)
                        <div class="rs-note">
                            @if($checkoutAssinado ?? false)
                                O contrato de checkout já foi assinado.
                            @else
                                O checkout ainda não foi assinado.
                            @endif
                        </div>
                    @else
                        <div class="rs-note">Nenhum checkout encontrado para esta reserva.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pane-financeiro" role="tabpanel">
            <div class="rs-card">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div>
                        <div class="rs-card__title">Valores da reserva</div>
                        <div class="rs-card__sub">Resumo financeiro simples para você acompanhar</div>
                    </div>
                </div>
                <div class="rs-card__body">
                    <div class="rs-price">
                        <div class="rs-price__box">
                            <div class="rs-price__label">Total</div>
                            <div class="rs-price__value">R$ {{ number_format($valorTotal, 2, ',', '.') }}</div>
                        </div>
                        <div class="rs-price__box">
                            <div class="rs-price__label">Pago</div>
                            <div class="rs-price__value">R$ {{ number_format($totalPago, 2, ',', '.') }}</div>
                        </div>
                        <div class="rs-price__box">
                            <div class="rs-price__label">Saldo</div>
                            <div class="rs-price__value">R$ {{ number_format($saldoPendente, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pane-danos" role="tabpanel">
            @include('reserva.partials.common_danos')
        </div>

        <div class="tab-pane fade" id="pane-sinistros" role="tabpanel">
            <div class="rs-card">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div>
                        <div class="rs-card__title">Sinistros</div>
                        <div class="rs-card__sub">Ocorrências ligadas a esta reserva</div>
                    </div>
                </div>
                <div class="rs-card__body">
                    @if(($sinistrosCliente ?? collect())->isEmpty())
                        <div class="rs-note">Nenhum sinistro vinculado a esta reserva.</div>
                    @else
                        <div class="rs-list">
                            @foreach($sinistrosCliente as $sinistro)
                                <div class="rs-list__item">
                                    <div class="rs-list__label">{{ $sinistro->descricao ?? 'Sinistro' }}</div>
                                    <div class="rs-list__value">{{ $sinistro->created_at ? \Carbon\Carbon::parse($sinistro->created_at)->format('d/m/Y H:i') : '-' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pane-avaliacao" role="tabpanel">
            <div class="rs-card">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-star"></i></div>
                    <div>
                        <div class="rs-card__title">Sua avaliação</div>
                        <div class="rs-card__sub">Pode ser preenchida apenas uma vez</div>
                    </div>
                </div>
                <div class="rs-card__body">
                    @if($podeResponderSatisfacao ?? false)
                        <form method="POST" action="{{ route('reserva.minhas.satisfacao.store', $reserva->id) }}" class="rs-survey">
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
                                        <label class="rs-survey__option">
                                            <input type="radio" name="nps_nota" value="{{ $i }}" required>
                                            <span>{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            <div class="rs-survey__group mb-0">
                                <label class="rs-survey__label" for="comentario">O que podemos melhorar? <span style="font-weight:400;">(opcional)</span></label>
                                <textarea id="comentario" name="comentario" class="rs-survey__textarea"></textarea>
                            </div>
                            <div class="rs-actions" style="margin-top:14px;">
                                <button type="submit" class="rs-btn rs-btn--primary"><i class="fas fa-paper-plane"></i> Enviar avaliação</button>
                            </div>
                        </form>
                    @else
                        <div class="rs-note">
                            @if($jaSatisfacao ?? false)
                                Obrigado! Sua avaliação já foi enviada e não pode ser alterada.
                            @else
                                A avaliação ficará disponível após a assinatura do contrato de check-out.
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pane-historico" role="tabpanel">
            <div class="rs-card rs-historico-card">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-history"></i></div>
                    <div>
                        <div class="rs-card__title">Histórico da Reserva</div>
                        <div class="rs-card__sub">Linha do tempo com eventos da reserva</div>
                    </div>
                </div>
                <div class="rs-card__body">
                    <div id="historicoLoading" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Carregando histórico...
                    </div>
                    <div id="historicoTimeline" class="rs-timeline d-none"></div>
                </div>
            </div>
        </div>
    </div>
</div>
