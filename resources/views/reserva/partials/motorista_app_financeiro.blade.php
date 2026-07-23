<div class="ma-card mb-4">
    <div class="ma-card__head">
        <div class="ma-card__icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div>
            <div class="ma-card__title">Resumo financeiro</div>
            <div class="ma-card__sub">Valor base aluguel e opcionais por ciclo</div>
        </div>
        @if($existeDetalheOpcionais)
            <div class="ml-auto">
                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modalDetalhesOpcionaisMotoristaApp">
                    <i class="fas fa-ellipsis-h mr-1"></i> Detalhes
                </button>
            </div>
        @endif
    </div>
    <div class="ma-card__body">
        <div class="ma-price">
            <div class="ma-price__box">
                <div class="ma-price__label">Valor por ciclo contratado</div>
                <div class="ma-price__value">R$ {{ number_format($valorPeriodoContratado, 2, ',', '.') }}</div>
                <div class="ma-price__hint">Plano: {{ $plano?->nome ?? '—' }} ({{ $periodoLabelContrato }})</div>
            </div>
            <div class="ma-price__box">
                <div class="ma-price__label">Caução (Bloqueio)</div>
                <div class="ma-price__value">R$ {{ number_format($calcao, 2, ',', '.') }}</div>
                <div class="ma-price__hint">Depositado / retido no inicio</div>
            </div>
            <div class="ma-price__box">
                <div class="ma-price__label">Opcionais selecionados</div>
                <div class="ma-price__value">R$ {{ number_format($valorOpcionais, 2, ',', '.') }}</div>
                <div class="ma-price__hint">Soma do pacote adicional</div>
            </div>
            <div class="ma-price__box ma-price__box--total">
                <div class="ma-price__label">Total por ciclo</div>
                <div class="ma-price__value">R$ {{ number_format($valorTotal, 2, ',', '.') }}</div>
                <div class="ma-price__hint">Plano + opcionais por ciclo</div>
            </div>
            @php
                $pagamentoPendenteAtual = $pagamentos->first(fn($p) => strtolower((string)($p->status ?? '')) === 'pendente');
                $valorPendenteAtual = $saldoPagamentoReserva;
            @endphp
            <div class="ma-price__box" style="border: 1px solid {{ $valorPendenteAtual > 0 ? '#fde68a' : '#bbf7d0' }}; background: {{ $valorPendenteAtual > 0 ? '#fffbeb' : '#f0fdf4' }};">
                <div class="ma-price__label {{ $valorPendenteAtual > 0 ? 'text-warning' : 'text-success' }} font-weight-bold">
                    <i class="fas {{ $valorPendenteAtual > 0 ? 'fa-clock' : 'fa-check-circle' }} mr-1"></i> Falta pagar
                </div>
                <div class="ma-price__value {{ $valorPendenteAtual > 0 ? 'text-warning' : 'text-success' }}">R$ {{ number_format($valorPendenteAtual, 2, ',', '.') }}</div>
                <div class="ma-price__hint">
                    Pago: R$ {{ number_format($totalPagoPagamentos, 2, ',', '.') }}
                </div>
                @if($valorPendenteAtual > 0)
                    <div class="mt-2">
                        <a href="{{ route('reserva.pagamento.efetuar', ['id' => $codigoReserva]) }}" class="btn btn-xs btn-warning font-weight-bold">
                            <i class="fas fa-credit-card mr-1"></i> Efetuar Pagamento
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@php
    $pagamentosQuitados = $pagamentos->filter(fn($p) => strtolower((string)($p->status ?? '')) === 'pago');
@endphp

<div class="ma-card" id="historico-pagamentos">
    <div class="ma-card__head">
        <div class="ma-card__icon"><i class="fas fa-receipt"></i></div>
        <div>
            <div class="ma-card__title">Historico de pagamentos</div>
            <div class="ma-card__sub">Cada renovacao quitada fica registrada aqui</div>
        </div>
    </div>
    <div class="ma-card__body p-0">
        @if($pagamentosQuitados->isEmpty())
            <div class="ma-card__body">
                <div class="ma-history-empty">Nenhum pagamento quitado ate o momento.</div>
            </div>
        @else
            <div class="table-responsive">
                <table class="ma-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Metodo</th>
                            <th>Referencia</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagamentosQuitados as $pagamentoItem)
                            @php
                                $metodoItem = (string) ($pagamentoItem->metodo_pagamento ?? 'cartao_credito');
                                $metodoItemLabel = match ($metodoItem) {
                                    'cartao_credito', 'cartao' => 'Cartão de crédito',
                                    'pix' => 'PIX',
                                    'boleto' => 'Boleto',
                                    default => ucfirst(str_replace('_', ' ', $metodoItem)),
                                };
                                $referenciaItem = (string) ($pagamentoItem->referencia_pagamento ?? data_get($pagamentoItem->metadados, 'gateway_transaction_id') ?? '-');
                                $valorItem = (float) ($pagamentoItem->valor_total > 0 ? $pagamentoItem->valor_total : ($pagamentoItem->valor_unitario ?? 0));
                                $dataItem = $pagamentoItem->pago_em
                                    ? \Carbon\Carbon::parse($pagamentoItem->pago_em)->format('d/m/Y H:i')
                                    : ($pagamentoItem->created_at ? \Carbon\Carbon::parse($pagamentoItem->created_at)->format('d/m/Y H:i') : '-');
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $dataItem }}</div>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark">
                                        R$ {{ number_format($valorItem, 2, ',', '.') }}
                                    </div>
                                </td>
                                <td>{{ $metodoItemLabel }}</td>
                                <td>
                                    <code class="text-muted">{{ $referenciaItem !== '' ? $referenciaItem : '-' }}</code>
                                </td>
                                <td>
                                    <span class="ma-pill ma-pill--success"><i class="fas fa-check-circle"></i> Pago</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
