<div class="rs-grid">
    {{-- COLUNA PRINCIPAL --}}
    <div class="rs-main">

        {{-- Card: Rota da viagem --}}
        <div class="rs-card">
            <div class="rs-card__header">
                <i class="fas fa-route rs-card__icon"></i>
                <h2>Rota da viagem</h2>
            </div>
            <div class="rs-route">
                <div class="rs-route__stop rs-route__stop--origin">
                    <div class="rs-route__dot rs-route__dot--origin">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="rs-route__info">
                        <span class="rs-route__label">Retirada</span>
                        <strong class="rs-route__place">{{ $reserva->estacaoRetirada->nome ?? 'Não informado' }}</strong>
                        <div class="rs-route__datetime">
                            <span class="rs-route__date">{{ $inicioData }}</span>
                            @if($inicioHora)
                                <span class="rs-route__time">{{ $inicioHora }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rs-route__middle">
                    <div class="rs-route__line"></div>
                    <div class="rs-route__duration">
                        <i class="fas fa-moon"></i>
                        {{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }}
                    </div>
                    <div class="rs-route__line"></div>
                </div>

                <div class="rs-route__stop rs-route__stop--dest">
                    <div class="rs-route__dot rs-route__dot--dest">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div class="rs-route__info">
                        <span class="rs-route__label">Devolução</span>
                        <strong class="rs-route__place">{{ $reserva->estacaoDevolucao->nome ?? 'Não informado' }}</strong>
                        <div class="rs-route__datetime">
                            <span class="rs-route__date">{{ $fimData }}</span>
                            @if($fimHora)
                                <span class="rs-route__time">{{ $fimHora }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Veículo --}}
        <div class="rs-card rs-card--vehicle">
            <div class="rs-card__header">
                <i class="fas fa-car rs-card__icon"></i>
                <h2>Veículo reservado</h2>
            </div>
            <div class="rs-vehicle">
                <div class="rs-vehicle__media">
                    <img src="{{ $reserva->grupoFrota->imagem_url ?? asset('img/no-image.jpg') }}"
                         alt="{{ $reserva->grupoFrota->nome ?? 'Grupo' }}">
                </div>
                <div class="rs-vehicle__info">
                    @if(!empty($reserva->grupoFrota->nome))
                        <span class="rs-chip rs-chip--blue" style="cursor:pointer;font-size:.9rem;padding:6px 16px;display:inline-flex;align-items:center;gap:6px;" onclick="$('#modalGrupo').modal('show')">
                            <i class="fas fa-layer-group"></i>
                            {{ $reserva->grupoFrota->nome }}
                            <i class="fas fa-search-plus" style="font-size:.7rem;opacity:.6;"></i>
                        </span>
                    @endif
                    <div style="margin-top:8px;">
                        @if(!empty($reserva->modelos->transmissao))
                            <span class="rs-chip rs-chip--slate"><i class="fas fa-cogs"></i> {{ $reserva->modelos->transmissao }}</span>
                        @endif
                        @if(!empty($reserva->modelos->combustivel))
                            <span class="rs-chip rs-chip--green"><i class="fas fa-gas-pump"></i> {{ $reserva->modelos->combustivel }}</span>
                        @endif
                    </div>

                    <div class="rs-vehicle__pricing">
                        <div>
                            <span>Tarifa diaria</span>
                            <strong>R$ {{ number_format($tarifaBase, 2, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span>{{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }}</span>
                            <strong>R$ {{ number_format($valorTarifaBase, 2, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Opcionais --}}
        @if($reserva->opcionais->isNotEmpty())
            <div class="rs-card">
                <div class="rs-card__header">
                    <i class="fas fa-plus-circle rs-card__icon"></i>
                    <h2>Opcionais contratados</h2>
                    <span class="rs-card__badge">{{ $reserva->opcionais->count() }} item(s)</span>
                </div>
                <div class="rs-optionals">
                    @foreach($reserva->opcionais as $opcional)
                        @php
                            $qtd   = (int) ($opcional->pivot->quantidade ?? 0);
                            $preco = (float) ($opcional->pivot->preco_total ?? 0);
                            $total = $qtd * $preco;
                        @endphp
                        <div class="rs-optional-row">
                            <div class="rs-optional-row__info">
                                <strong>{{ $opcional->nome }}</strong>
                                <span>
                                    {{ $opcional->tipo_cobranca == 'por_dia' ? 'Cobrado por dia' : 'Valor integral' }}
                                    &middot; {{ $qtd }}x
                                </span>
                            </div>
                            <div class="rs-optional-row__price">
                                R$ {{ number_format($total, 2, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- SIDEBAR --}}
    <aside class="rs-sidebar">

        {{-- Card: Cliente --}}
        <div class="rs-card">
            <div class="rs-card__header">
                <i class="fas fa-user rs-card__icon"></i>
                <h2>Cliente</h2>
            </div>
            <div class="rs-client">
                <div class="rs-client__avatar">{{ $clienteIniciais }}</div>
                <div class="rs-client__main">
                    <strong>{{ $clienteNome }}</strong>
                    <span>{{ $reserva->usuario->email ?? 'E-mail não informado' }}</span>
                </div>
            </div>
            <div class="rs-info-rows">
                <div class="rs-info-row">
                    <span><i class="fas fa-phone-alt"></i>Telefone</span>
                    <strong>{{ $reserva->usuario->telefone ?? '-' }}</strong>
                </div>
                <div class="rs-info-row">
                    <span><i class="fas fa-id-card"></i>Documento</span>
                    <strong>{{ $reserva->usuario->numero_identificacao ?? '-' }}</strong>
                </div>
                <div class="rs-info-row">
                    <span><i class="fas fa-birthday-cake"></i>Nascimento</span>
                    <strong>{{ isset($reserva->usuario->data_nascimento) ? \Carbon\Carbon::parse($reserva->usuario->data_nascimento)->format('d/m/Y') : '-' }}</strong>
                </div>
            </div>
        </div>

        {{-- Card: Resumo financeiro --}}
        <div class="rs-card">
            <div class="rs-card__header">
                <i class="fas fa-file-invoice-dollar rs-card__icon"></i>
                <h2>Resumo financeiro</h2>
            </div>
            <div class="rs-summary">
                @if($grupoFrotaNome)
                    <div class="rs-summary__row" style="color:#6b7280;font-size:.83rem">
                        <span><i class="fas fa-layer-group mr-1"></i>Grupo</span>
                        <span style="font-weight:600;color:#374151">{{ $grupoFrotaNome }}</span>
                    </div>
                    <div class="rs-summary__divider"></div>
                @endif
                <div class="rs-summary__row">
                    <span>Tarifa diária</span>
                    <span>R$ {{ number_format($tarifaBase, 2, ',', '.') }}</span>
                </div>
                <div class="rs-summary__row">
                    <span>{{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }} &times; tarifa</span>
                    <span>R$ {{ number_format($valorTarifaBase, 2, ',', '.') }}</span>
                </div>
                @if($valorOpcionais > 0)
                    <div class="rs-summary__row">
                        <span>Opcionais</span>
                        <span>R$ {{ number_format($valorOpcionais, 2, ',', '.') }}</span>
                    </div>
                @endif
                @if($taxaColeta > 0)
                    <div class="rs-summary__row" style="color:#92400e;background:#fef3c7;border-radius:5px;padding:3px 6px;margin:2px 0">
                        <span><i class="fas fa-exclamation-triangle mr-1"></i>* Retirada fora do expediente</span>
                        <span>R$ {{ number_format($taxaColeta, 2, ',', '.') }}</span>
                    </div>
                @endif
                @if($taxaEntrega > 0)
                    <div class="rs-summary__row" style="color:#92400e;background:#fef3c7;border-radius:5px;padding:3px 6px;margin:2px 0">
                        <span><i class="fas fa-exclamation-triangle mr-1"></i>* Devolução fora do expediente</span>
                        <span>R$ {{ number_format($taxaEntrega, 2, ',', '.') }}</span>
                    </div>
                @endif
                @if($percentualDesconto > 0)
                    <div class="rs-summary__divider"></div>
                    <div class="rs-summary__row rs-summary__row--discount" style="background:#f0fdf4;border-radius:5px;padding:3px 6px;margin:2px 0;color:#16a34a">
                        <span>
                            <i class="fas fa-tag mr-1"></i>Desconto ({{ number_format($percentualDesconto, 0) }}%)
                            @if($cupomCodigo)
                                <span class="badge badge-xs ml-1" style="background:#16a34a;color:#fff;font-size:.68rem">{{ $cupomCodigo }}</span>
                            @endif
                        </span>
                        <span>&minus; R$ {{ number_format($valorDesconto, 2, ',', '.') }}</span>
                    </div>
                @endif
                <div class="rs-summary__total">
                    @if($percentualDesconto > 0)
                        <span style="text-decoration:line-through;color:#9ca3af;font-size:.8rem;font-weight:400;margin-right:6px">R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                    @endif
                    <span>Total</span>
                    <strong>R$ {{ number_format($valorTotal, 2, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        {{-- Card: Transferencias (resumo) --}}
        <div class="rs-card">
            <div class="rs-card__header">
                <i class="fas fa-exchange-alt rs-card__icon"></i>
                <h2>Transferencias</h2>
            </div>
            <div class="rs-finance-kpis">
                <div class="rs-fkpi rs-fkpi--green">
                    <i class="fas fa-arrow-down"></i>
                    <div>
                        <strong>R$ {{ number_format($totalPagamentos, 2, ',', '.') }}</strong>
                        <span>{{ $transacoesPagamento->count() }} pagamento(s)</span>
                    </div>
                </div>
                <div class="rs-fkpi rs-fkpi--red">
                    <i class="fas fa-arrow-up"></i>
                    <div>
                        <strong>R$ {{ number_format($totalEstornos, 2, ',', '.') }}</strong>
                        <span>{{ $transacoesEstorno->count() }} estorno(s)</span>
                    </div>
                </div>
                @if($transacoesPendentes->count() > 0)
                    <div class="rs-fkpi rs-fkpi--amber">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>{{ $transacoesPendentes->count() }}</strong>
                            <span>Pendente(s)</span>
                        </div>
                    </div>
                @endif
            </div>
            @if($transacoesReserva->isNotEmpty())
                <div class="rs-transactions">
                    @foreach($transacoesReserva->take(5) as $transacao)
                        @php
                            $valorTx  = (float) ($transacao->valor ?? 0);
                            $statusTx = strtolower((string) ($transacao->status ?? ''));
                            $isEstorno = $valorTx < 0 || in_array($statusTx, $statusPagamentoEstornado, true);
                            $metodoPag   = strtolower($transacao->metodo_pagamento ?? '');
                            $isDinheiro  = in_array($metodoPag, ['dinheiro','cash','especie','espécie']);
                            $isPendente  = in_array($statusTx, ['pending','pendente','aguardando']);
                            $jaConfirmado= !empty($transacao->confirmado_em);
                            $podeConfirmar = $isDinheiro && $isPendente && !$jaConfirmado;
                        @endphp
                        <div class="rs-tx">
                            <div class="rs-tx__icon {{ $isEstorno ? 'rs-tx__icon--red' : 'rs-tx__icon--green' }}">
                                <i class="fas {{ $isEstorno ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                            </div>
                            <div class="rs-tx__info">
                                <strong>{{ $transacao->metodo_pagamento_label }}</strong>
                                <span>{{ \Illuminate\Support\Carbon::parse($transacao->created_at)->format('d/m/Y H:i') }}</span>
                                @if($jaConfirmado)
                                    <span class="rs-tx__confirmed">
                                        <i class="fas fa-check-circle"></i>
                                        Confirmado {{ \Carbon\Carbon::parse($transacao->confirmado_em)->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            </div>
                            <div class="rs-tx__amount {{ $isEstorno ? 'rs-tx__amount--red' : 'rs-tx__amount--green' }}">
                                <strong>R$ {{ number_format($valorTx, 2, ',', '.') }}</strong>
                                <span>{{ $transacao->status ?? '-' }}</span>
                                @if($podeConfirmar)
                                    <button type="button"
                                        class="btn btn-xs btn-success mt-1 btn-confirmar-dinheiro"
                                        data-transacao-id="{{ $transacao->id }}"
                                        data-reserva-id="{{ $reserva->id }}"
                                        title="Confirmar recebimento em dinheiro">
                                        <i class="fas fa-check mr-1"></i>Confirmar recebimento
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($transacoesReserva->count() > 5)
                    <div class="text-center mt-2">
                        <a href="#pane-financeiro" class="rs-btn rs-btn--outline rs-tab-link" data-tab="tab-financeiro">
                            <i class="fas fa-list"></i> Ver todas ({{ $transacoesReserva->count() }})
                        </a>
                    </div>
                @endif
            @else
                <div class="rs-empty">
                    <i class="fas fa-inbox"></i>
                    <span>Nenhuma transferencia vinculada.</span>
                </div>
            @endif
        </div>

    </aside>
</div>
