<div class="rs-card mb-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-file-invoice-dollar rs-card__icon"></i>
            <h2>Resumo financeiro</h2>
        </div>
    </div>
    <div class="rs-card__body">
        <div class="rs-finance-kpis">
            <div class="rs-fkpi rs-fkpi--default">
                <i class="fas fa-tag"></i>
                <div>
                    <strong>R$ {{ number_format($tarifaBase, 2, ',', '.') }}</strong>
                    <span>Tarifa diária</span>
                </div>
            </div>
            <div class="rs-fkpi rs-fkpi--default">
                <i class="fas fa-calendar-day"></i>
                <div>
                    <strong>R$ {{ number_format($valorTarifaBase, 2, ',', '.') }}</strong>
                    <span>{{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }} &times; tarifa</span>
                </div>
            </div>
            @if($valorOpcionais > 0)
            <div class="rs-fkpi rs-fkpi--default">
                <i class="fas fa-puzzle-piece"></i>
                <div>
                    <strong>R$ {{ number_format($valorOpcionais, 2, ',', '.') }}</strong>
                    <span>Opcionais</span>
                </div>
            </div>
            @endif
            @if($taxaColeta > 0)
            <div class="rs-fkpi rs-fkpi--amber">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>R$ {{ number_format($taxaColeta, 2, ',', '.') }}</strong>
                    <span>Retirada fora expediente</span>
                </div>
            </div>
            @endif
            @if($taxaEntrega > 0)
            <div class="rs-fkpi rs-fkpi--amber">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>R$ {{ number_format($taxaEntrega, 2, ',', '.') }}</strong>
                    <span>Devolução fora expediente</span>
                </div>
            </div>
            @endif
            @if($percentualDesconto > 0)
            <div class="rs-fkpi rs-fkpi--green">
                <i class="fas fa-percent"></i>
                <div>
                    <strong>&minus; R$ {{ number_format($valorDesconto, 2, ',', '.') }}</strong>
                    <span>Desconto ({{ number_format($percentualDesconto, 0) }}%)@if($cupomCodigo) &middot; {{ $cupomCodigo }}@endif</span>
                </div>
            </div>
            @endif
            <div class="rs-fkpi rs-fkpi--total">
                <i class="fas fa-coins"></i>
                <div>
                    <strong>R$ {{ number_format($valorTotal, 2, ',', '.') }}</strong>
                    <span>Total da reserva</span>
                </div>
            </div>
            <div class="rs-fkpi {{ $pagamentoEfetuado ? 'rs-fkpi--green' : 'rs-fkpi--amber' }}">
                <i class="fas {{ $pagamentoEfetuado ? 'fa-check-circle' : 'fa-clock' }}"></i>
                <div>
                    <strong>R$ {{ number_format($totalPagamentos, 2, ',', '.') }}</strong>
                    <span>{{ $pagamentoEfetuado ? 'Pago' : 'Total pago' }}</span>
                </div>
            </div>
            @if(!empty($calcao))
                @php
                    $valorCalcao = (float) ($calcao->valor_capturado ?? $calcao->valor_autorizado ?? $calcao->valor_calculado ?? 0);
                @endphp
                <div class="rs-fkpi rs-fkpi--amber">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <strong>R$ {{ number_format($valorCalcao, 2, ',', '.') }}</strong>
                        <span>Calção</span>
                    </div>
                </div>
                <div class="rs-fkpi rs-fkpi--default">
                    <i class="fas fa-calculator"></i>
                    <div>
                        <strong>R$ {{ number_format((float) ($calcao->valor_calculado ?? 0), 2, ',', '.') }}</strong>
                        <span>Valor calculado</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="rs-card">
    <div class="rs-card__header">
        <i class="fas fa-exchange-alt rs-card__icon"></i>
        <h2>Histórico de transferências</h2>
        <span class="rs-card__badge">{{ $transacoesReserva->count() }}</span>
    </div>
    <div class="rs-card__body p-0">
        @if($transacoesReserva->isEmpty())
            <div class="rs-card__body">
                <div class="rs-empty">
                    <i class="fas fa-inbox"></i>
                    <span>Nenhuma transferência vinculada.</span>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="rs-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Método</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transacoesReserva as $transacao)
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
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ \Illuminate\Support\Carbon::parse($transacao->created_at)->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>{{ $transacao->metodo_pagamento_label }}</td>
                                <td>
                                    <div class="font-weight-bold {{ $isEstorno ? 'text-danger' : 'text-success' }}">
                                        R$ {{ number_format($valorTx, 2, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $isEstorno ? 'danger' : ($isPendente ? 'warning' : 'success') }}">{{ $transacao->status ?? '-' }}</span>
                                    @if($jaConfirmado)
                                        <div class="small text-success mt-1">
                                            <i class="fas fa-check-circle"></i>
                                            {{ \Carbon\Carbon::parse($transacao->confirmado_em)->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($podeConfirmar)
                                        <button type="button"
                                            class="btn btn-xs btn-success btn-confirmar-dinheiro"
                                            data-transacao-id="{{ $transacao->id }}"
                                            data-reserva-id="{{ $reserva->id }}"
                                            title="Confirmar recebimento em dinheiro">
                                            <i class="fas fa-check mr-1"></i>Confirmar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if(!empty($calcao))
<div class="rs-card mt-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-shield-alt rs-card__icon"></i>
            <h2>Calção</h2>
        </div>
        <span class="rs-status-badge rs-status-badge--sm {{ in_array(strtolower((string) $calcao->status), ['autorizado', 'capturado', 'parcialmente_capturado'], true) ? 'rs-status-badge--green' : 'rs-status-badge--amber' }}">
            {{ $calcao->label_status ?? ucfirst((string) $calcao->status) }}
        </span>
    </div>
    <div class="rs-card__body">
        <div class="rs-finance-kpis">
            <div class="rs-fkpi rs-fkpi--default">
                <i class="fas fa-calculator"></i>
                <div>
                    <strong>R$ {{ number_format((float) ($calcao->valor_calculado ?? 0), 2, ',', '.') }}</strong>
                    <span>Valor calculado</span>
                </div>
            </div>
            <div class="rs-fkpi rs-fkpi--green">
                <i class="fas fa-lock"></i>
                <div>
                    <strong>R$ {{ number_format((float) ($calcao->valor_capturado ?? $calcao->valor_autorizado ?? 0), 2, ',', '.') }}</strong>
                    <span>Valor do calção</span>
                </div>
            </div>
            @if(!empty($calcao->data_hora_captura))
            <div class="rs-fkpi rs-fkpi--default">
                <i class="fas fa-clock"></i>
                <div>
                    <strong>{{ \Illuminate\Support\Carbon::parse($calcao->data_hora_captura)->format('d/m/Y H:i') }}</strong>
                    <span>Capturado em</span>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@if($moduloNfse)
<div class="rs-card mt-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-file-alt rs-card__icon"></i>
            <h2>Nota Fiscal (NFS-e)</h2>
        </div>
        <span class="rs-status-badge rs-status-badge--sm {{ $nfseBadgeClass }}">{{ $nfseStatusLabel }}</span>
    </div>
    <div class="rs-card__body">
        @if($nfseAtual)
            <div class="rs-list">
                <div class="rs-list__item">
                    <div class="rs-list__label">Número</div>
                    <div class="rs-list__value">{{ $nfseAtual->numero ?? '-' }}</div>
                </div>
                <div class="rs-list__item">
                    <div class="rs-list__label">Ambiente</div>
                    <div class="rs-list__value">{{ $nfseAtual->ambiente ?? '-' }}</div>
                </div>
            </div>

            @if($nfseErroDetalhado || $nfseMotivo)
                <div class="rs-nfse-error mt-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>{{ $nfseErroDetalhado ?? $nfseMotivo }}</p>
                </div>
            @endif

            @if(!empty($nfseAtual->nfse_id) && (auth()->user()?->hasPermissao('nfse.download') || auth()->user()?->isAdministrador()))
                <div class="rs-nfse-downloads mt-3">
                    <a href="{{ route('nfse.download', ['id' => $nfseAtual->id, 'tipo' => 'xml']) }}" class="rs-btn rs-btn--outline">
                        <i class="fas fa-download"></i>Baixar XML
                    </a>
                    <a href="{{ route('nfse.download', ['id' => $nfseAtual->id, 'tipo' => 'dps']) }}" class="rs-btn rs-btn--outline">
                        <i class="fas fa-file-alt"></i>Baixar DPS
                    </a>
                </div>
            @endif

            @if(($nfseAtual->status ?? '') !== 'emitida')
                <form method="POST" action="{{ route('reserva.emitirNfse', $reserva->id) }}" class="js-nfse-form mt-3">
                    @csrf
                    <button type="submit" class="rs-btn rs-btn--ghost rs-btn--full js-nfse-btn">
                        <i class="fas fa-redo"></i>Tentar emitir novamente
                    </button>
                </form>
            @endif
        @else
            <div class="rs-empty">
                <i class="fas fa-file-times"></i>
                <span>Nenhuma nota vinculada ainda.</span>
            </div>
            <form method="POST" action="{{ route('reserva.emitirNfse', $reserva->id) }}" class="js-nfse-form mt-3">
                @csrf
                <button type="submit" class="rs-btn rs-btn--ghost rs-btn--full js-nfse-btn">
                    <i class="fas fa-redo"></i>Emitir nota fiscal
                </button>
            </form>
        @endif
    </div>
</div>
@endif
